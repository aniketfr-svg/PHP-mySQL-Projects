<?php
include('config.php');

// Days and time slots available
$days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];
$time_slots = ['9:00-10:00', '10:00-11:00', '11:00-12:00', '12:00-1:00', '2:00-3:00', '3:00-4:00', '4:00-5:00'];

// Clear old auto-generated timetable - preserve manual entries
$con->query("DELETE FROM schedule WHERE is_manual = 0");

// Fetch courses ordered by priority
$courses = $con->query("
    SELECT * FROM courses
    ORDER BY 
        CASE course_type
            WHEN 'Core' THEN 1
            WHEN 'Elective' THEN 2
            WHEN 'Open Elective' THEN 3
            ELSE 4
        END
");

$professor_schedule = [];
$group_schedule = [];  // Now using (semester_building) as key
$classroom_schedule = [];
$course_allocations = []; // Track allocated slots per course

// Load manual entries to block those time slots from auto allocation
$manual_entries = $con->query("SELECT * FROM schedule WHERE is_manual = 1");
while ($row = $manual_entries->fetch_assoc()) {
    $course_id = $row['course_id'];
    $classroom_id = $row['classroom_id'];
    $day = $row['day'];
    $time = $row['time_slot'];
    $semester_id = $row['semester_id'];
    $building_id = $row['building_id'];

    // Get professor_id for this course
    $course_result = $con->query("SELECT professor_id FROM courses WHERE id = '$course_id'");
    if ($course_row = $course_result->fetch_assoc()) {
        $professor_id = $course_row['professor_id'];
        $group_key = $semester_id . '_' . $building_id;

        // Reserve slot in constraint trackers
        $professor_schedule[$professor_id][$day][$time] = true;
        $classroom_schedule[$classroom_id][$day][$time] = true;
        $group_schedule[$group_key][$day][$time] = true;
        
        // Count manual allocations
        if (!isset($course_allocations[$course_id])) {
            $course_allocations[$course_id] = 0;
        }
        $course_allocations[$course_id]++;
    }
}

while ($course = $courses->fetch_assoc()) {
    $lecture_hours = $course['lecture_hours'];
    $tutorial_hours = $course['tutorial_hours'];
    $total_slots = $lecture_hours + $tutorial_hours;

    $course_id = $course['id'];
    $professor_id = $course['professor_id'];
    $semester_id = $course['semester_id'];
    $building_id = $course['building_id'];  // This will act like department
    $students_enrolled = $course['students_enrolled'];
    $requires_projector = $course['requires_projector'];

    $group_key = $semester_id . '_' . $building_id;  // Unique key per department-semester
    
    // Initialize course allocation tracking if not set
    if (!isset($course_allocations[$course_id])) {
        $course_allocations[$course_id] = 0;
    }
    
    // Calculate slots still needed (considering manual entries)
    $slots_needed = $total_slots - $course_allocations[$course_id];
    $slots_assigned = 0;
    $days_used = [];  // Track which days already used for this course

    // Outer loop: time slots → inner loop: days (from pasted code)
    foreach ($time_slots as $time) {
        foreach ($days as $day) {
            if ($slots_assigned >= $slots_needed) break;

            // Skip this day if already used for this course (to promote spreading across days)
            if (in_array($day, $days_used)) continue;

            // Try same-building classrooms first
            $classrooms = $con->query("SELECT * FROM classrooms WHERE building_id = $building_id ORDER BY capacity ASC");
            $found = false;

            while ($classroom = $classrooms->fetch_assoc()) {
                $classroom_id = $classroom['id'];
                $capacity = $classroom['capacity'];

                if ($capacity >= $students_enrolled &&
                    (!$requires_projector || $classroom['has_projector']) &&
                    !isset($professor_schedule[$professor_id][$day][$time]) &&
                    !isset($group_schedule[$group_key][$day][$time]) &&
                    !isset($classroom_schedule[$classroom_id][$day][$time])) {

                    $professor_schedule[$professor_id][$day][$time] = true;
                    $group_schedule[$group_key][$day][$time] = true;
                    $classroom_schedule[$classroom_id][$day][$time] = true;

                    $con->query("INSERT INTO schedule (course_id, classroom_id, day, time_slot, semester_id, building_id, professor_id, is_manual)
                        VALUES ('$course_id', '$classroom_id', '$day', '$time', '$semester_id', '$building_id', '$professor_id', 0)");

                    $slots_assigned++;
                    $course_allocations[$course_id]++;
                    $days_used[] = $day;
                    $found = true;
                    break;
                }
            }

            // Fallback to any building
            if (!$found) {
                $classrooms = $con->query("SELECT * FROM classrooms WHERE capacity >= $students_enrolled ORDER BY capacity ASC");

                while ($classroom = $classrooms->fetch_assoc()) {
                    $classroom_id = $classroom['id'];
                    $building_id_alt = $classroom['building_id'];

                    if ((!$requires_projector || $classroom['has_projector']) &&
                        !isset($professor_schedule[$professor_id][$day][$time]) &&
                        !isset($group_schedule[$group_key][$day][$time]) &&
                        !isset($classroom_schedule[$classroom_id][$day][$time])) {

                        $professor_schedule[$professor_id][$day][$time] = true;
                        $group_schedule[$group_key][$day][$time] = true;
                        $classroom_schedule[$classroom_id][$day][$time] = true;

                        $con->query("INSERT INTO schedule (course_id, classroom_id, day, time_slot, semester_id, building_id, professor_id, is_manual)
                            VALUES ('$course_id', '$classroom_id', '$day', '$time', '$semester_id', '$building_id_alt', '$professor_id', 0)");

                        $slots_assigned++;
                        $course_allocations[$course_id]++;
                        $days_used[] = $day;
                        break;
                    }
                }
            }
        }

        // After trying all days, if not enough slots were assigned, allow reuse of used days
        if ($slots_assigned < $slots_needed) {
            foreach ($days as $day) {
                if ($slots_assigned >= $slots_needed) break;

                // This time allow reused days
                $classrooms = $con->query("SELECT * FROM classrooms WHERE building_id = $building_id ORDER BY capacity ASC");
                $found = false;

                while ($classroom = $classrooms->fetch_assoc()) {
                    $classroom_id = $classroom['id'];
                    $capacity = $classroom['capacity'];

                    if ($capacity >= $students_enrolled &&
                        (!$requires_projector || $classroom['has_projector']) &&
                        !isset($professor_schedule[$professor_id][$day][$time]) &&
                        !isset($group_schedule[$group_key][$day][$time]) &&
                        !isset($classroom_schedule[$classroom_id][$day][$time])) {

                        $professor_schedule[$professor_id][$day][$time] = true;
                        $group_schedule[$group_key][$day][$time] = true;
                        $classroom_schedule[$classroom_id][$day][$time] = true;

                        $con->query("INSERT INTO schedule (course_id, classroom_id, day, time_slot, semester_id, building_id, professor_id, is_manual)
                            VALUES ('$course_id', '$classroom_id', '$day', '$time', '$semester_id', '$building_id', '$professor_id', 0)");

                        $slots_assigned++;
                        $course_allocations[$course_id]++;
                        $found = true;
                        break;
                    }
                }

                if (!$found) {
                    $classrooms = $con->query("SELECT * FROM classrooms WHERE capacity >= $students_enrolled ORDER BY capacity ASC");
                    while ($classroom = $classrooms->fetch_assoc()) {
                        $classroom_id = $classroom['id'];
                        $building_id_alt = $classroom['building_id'];

                        if ((!$requires_projector || $classroom['has_projector']) &&
                            !isset($professor_schedule[$professor_id][$day][$time]) &&
                            !isset($group_schedule[$group_key][$day][$time]) &&
                            !isset($classroom_schedule[$classroom_id][$day][$time])) {

                            $professor_schedule[$professor_id][$day][$time] = true;
                            $group_schedule[$group_key][$day][$time] = true;
                            $classroom_schedule[$classroom_id][$day][$time] = true;

                            $con->query("INSERT INTO schedule (course_id, classroom_id, day, time_slot, semester_id, building_id, professor_id, is_manual)
                                VALUES ('$course_id', '$classroom_id', '$day', '$time', '$semester_id', '$building_id_alt', '$professor_id', 0)");

                            $slots_assigned++;
                            $course_allocations[$course_id]++;
                            break;
                        }
                    }
                }
            }
        }

        if ($slots_assigned >= $slots_needed) break;
    }
}

// Generate allocation summary for logging
$total_courses = 0;
$fully_allocated = 0;
$partially_allocated = 0;
$unallocated = 0;

// Get all courses to calculate summary
$all_courses = $con->query("SELECT id, lecture_hours, tutorial_hours FROM courses");
while ($course_row = $all_courses->fetch_assoc()) {
    $course_id = $course_row['id'];
    $required_slots = $course_row['lecture_hours'] + $course_row['tutorial_hours'];
    $allocated_slots = isset($course_allocations[$course_id]) ? $course_allocations[$course_id] : 0;
    
    $total_courses++;
    
    if ($allocated_slots == $required_slots) {
        $fully_allocated++;
    } elseif ($allocated_slots > 0) {
        $partially_allocated++;
    } else {
        $unallocated++;
    }
}

error_log("Allocation Summary - Total: $total_courses, Fully: $fully_allocated, Partial: $partially_allocated, Unallocated: $unallocated");
?>

<!-- Enhanced Success Message UI with Statistics -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Enhanced Timetable Generated - Academic Management System</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #2d3748;
            line-height: 1.6;
        }

        .success-container {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 20px;
            padding: 3rem;
            text-align: center;
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.15);
            max-width: 600px;
            width: 90%;
            position: relative;
            overflow: hidden;
        }

        .success-container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #10b981, #059669);
        }

        .success-icon {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #10b981, #059669);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem auto;
            color: white;
            font-size: 2rem;
            animation: successPulse 2s ease-in-out infinite;
        }

        @keyframes successPulse {
            0%, 100% { box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.4); }
            50% { box-shadow: 0 0 0 20px rgba(16, 185, 129, 0); }
        }

        .success-title {
            font-size: 1.75rem;
            font-weight: 700;
            color: #1a202c;
            margin-bottom: 0.75rem;
        }

        .success-message {
            font-size: 1rem;
            color: #4a5568;
            margin-bottom: 2rem;
            line-height: 1.5;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
            gap: 1rem;
            margin: 1.5rem 0;
        }

        .stat-card {
            background: rgba(59, 130, 246, 0.1);
            border-radius: 12px;
            padding: 1rem;
            border: 1px solid rgba(59, 130, 246, 0.2);
            text-align: center;
        }

        .stat-number {
            font-size: 1.5rem;
            font-weight: 700;
            color: #1e40af;
            display: block;
        }

        .stat-label {
            font-size: 0.75rem;
            color: #1e40af;
            margin-top: 0.25rem;
        }

        .features-list {
            background: rgba(16, 185, 129, 0.1);
            border-radius: 12px;
            padding: 1.5rem;
            margin: 1.5rem 0;
            border: 1px solid rgba(16, 185, 129, 0.2);
            text-align: left;
        }

        .features-title {
            font-size: 1rem;
            font-weight: 600;
            color: #059669;
            margin-bottom: 1rem;
            text-align: center;
        }

        .feature-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 0.5rem;
            font-size: 0.875rem;
            color: #059669;
        }

        .feature-item:last-child {
            margin-bottom: 0;
        }

        .feature-icon {
            width: 16px;
            text-align: center;
        }

        .action-buttons {
            display: flex;
            gap: 1rem;
            justify-content: center;
            flex-wrap: wrap;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.875rem 1.75rem;
            font-size: 0.95rem;
            font-weight: 500;
            text-decoration: none;
            border-radius: 12px;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
            min-width: 140px;
            justify-content: center;
        }

        .btn-primary {
            background: linear-gradient(135deg, #3b82f6, #1d4ed8);
            color: white;
            box-shadow: 0 4px 15px rgba(59, 130, 246, 0.4);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(59, 130, 246, 0.6);
        }

        .btn-secondary {
            background: rgba(107, 114, 128, 0.1);
            color: #374151;
            border: 2px solid rgba(107, 114, 128, 0.2);
        }

        .btn-secondary:hover {
            background: rgba(107, 114, 128, 0.15);
            border-color: rgba(107, 114, 128, 0.3);
            transform: translateY(-1px);
        }

        @media (max-width: 480px) {
            .success-container {
                padding: 2rem;
                margin: 1rem;
            }
            
            .action-buttons {
                flex-direction: column;
                align-items: center;
            }
            
            .btn {
                width: 100%;
                max-width: 200px;
            }

            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        .loading-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, 0.9);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 1000;
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s ease;
        }

        .loading-overlay.active {
            opacity: 1;
            visibility: visible;
        }

        .spinner {
            width: 40px;
            height: 40px;
            border: 4px solid #e5e7eb;
            border-top: 4px solid #3b82f6;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
</head>
<body>
    <div class="loading-overlay" id="loadingOverlay">
        <div class="spinner"></div>
    </div>

    <div class="success-container">
        <div class="success-icon">
            <i class="fas fa-check"></i>
        </div>
        
        <h1 class="success-title">Timetable Generated Successfully!</h1>
        <p class="success-message">
            Your academic timetable has been generated using priority-based allocation with optimized scheduling constraints and improved day distribution.
        </p>

        <div class="stats-grid">
            <div class="stat-card">
                <span class="stat-number"><?php echo $total_courses; ?></span>
                <div class="stat-label">Total Courses</div>
            </div>
            <div class="stat-card">
                <span class="stat-number"><?php echo $fully_allocated; ?></span>
                <div class="stat-label">Fully Scheduled</div>
            </div>
            <div class="stat-card">
                <span class="stat-number"><?php echo $partially_allocated; ?></span>
                <div class="stat-label">Partial Schedule</div>
            </div>
            <div class="stat-card">
                <span class="stat-number"><?php echo round(($fully_allocated / max($total_courses, 1)) * 100); ?>%</span>
                <div class="stat-label">Success Rate</div>
            </div>
        </div>

        <div class="features-list">
            <div class="features-title">Applied Scheduling Constraints</div>
            <div class="feature-item">
                <i class="fas fa-trophy feature-icon"></i>
                <span>Priority-based allocation (Core → Elective → Open Elective)</span>
            </div>
            <div class="feature-item">
                <i class="fas fa-user-tie feature-icon"></i>
                <span>Professor conflict prevention</span>
            </div>
            <div class="feature-item">
                <i class="fas fa-users feature-icon"></i>
                <span>Group/Semester conflict prevention</span>
            </div>
            <div class="feature-item">
                <i class="fas fa-door-open feature-icon"></i>
                <span>Classroom capacity & projector matching</span>
            </div>
            <div class="feature-item">
                <i class="fas fa-building feature-icon"></i>
                <span>Building preference with cross-building fallback</span>
            </div>
            <div class="feature-item">
                <i class="fas fa-edit feature-icon"></i>
                <span>Manual entry preservation</span>
            </div>
            <div class="feature-item">
                <i class="fas fa-calendar-week feature-icon"></i>
                <span>Optimized day distribution across weekdays</span>
            </div>
            <div class="feature-item">
                <i class="fas fa-clock feature-icon"></i>
                <span>Complete lecture + tutorial hour coverage</span>
            </div>
        </div>

        <div class="action-buttons">
            <a href="view_timetable.php" class="btn btn-primary" onclick="showLoading()">
                <i class="fas fa-calendar-alt"></i>
                View Timetable
            </a>
            <a href="index.php" class="btn btn-secondary">
                <i class="fas fa-home"></i>
                Dashboard
            </a>
        </div>
    </div>

    <script>
        function showLoading() {
            document.getElementById('loadingOverlay').classList.add('active');
        }

        // Add entrance animation
        document.addEventListener('DOMContentLoaded', function() {
            const container = document.querySelector('.success-container');
            container.style.opacity = '0';
            container.style.transform = 'translateY(30px)';
            
            setTimeout(() => {
                container.style.transition = 'all 0.6s cubic-bezier(0.4, 0, 0.2, 1)';
                container.style.opacity = '1';
                container.style.transform = 'translateY(0)';
            }, 100);
        });
    </script>
</body>
</html>