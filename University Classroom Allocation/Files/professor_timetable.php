<?php
session_start();
include('config.php');

// Check if professor is logged in
if (!isset($_SESSION['professor_id'])) {
    header("Location: professor_login.php");
    exit();
}

$professor_id = $_SESSION['professor_id'];

// Get professor details
$professor_query = "SELECT * FROM professors WHERE id = ?";
$stmt = $con->prepare($professor_query);
$stmt->bind_param("i", $professor_id);
$stmt->execute();
$professor_result = $stmt->get_result();
$professor = $professor_result->fetch_assoc();

if (!$professor) {
    session_destroy();
    header("Location: professor_login.php");
    exit();
}

// Get filter parameters
$semester_filter = isset($_GET['semester']) ? $_GET['semester'] : '';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Teaching Schedule - <?php echo htmlspecialchars($professor['name']); ?></title>
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
            background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
            color: #1a202c;
            line-height: 1.6;
            min-height: 100vh;
        }

        .header {
            background: linear-gradient(135deg, #1e40af 0%, #3730a3 100%);
            color: white;
            padding: 2rem 0;
            box-shadow: 0 8px 32px rgba(30, 64, 175, 0.2);
        }

        .header-content {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 1.5rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .professor-info {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .professor-avatar {
            width: 60px;
            height: 60px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            font-weight: 700;
            border: 3px solid rgba(255, 255, 255, 0.3);
        }

        .professor-details h1 {
            font-size: 1.75rem;
            font-weight: 700;
            margin: 0;
        }

        .professor-details p {
            margin: 0;
            opacity: 0.9;
            font-size: 0.9rem;
        }

        .header-actions {
            display: flex;
            gap: 0.75rem;
            align-items: center;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem 1.25rem;
            font-size: 0.875rem;
            font-weight: 500;
            text-decoration: none;
            border-radius: 8px;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
        }

        .btn-light {
            background: rgba(255, 255, 255, 0.15);
            color: white;
            backdrop-filter: blur(10px);
        }

        .btn-light:hover {
            background: rgba(255, 255, 255, 0.25);
            transform: translateY(-1px);
        }

        .btn-danger {
            background: #dc2626;
            color: white;
        }

        .btn-danger:hover {
            background: #b91c1c;
            transform: translateY(-1px);
        }

        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 2rem 1.5rem;
        }

        .welcome-section {
            background: white;
            border-radius: 16px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            border: 1px solid #e2e8f0;
        }

        .welcome-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 1.5rem;
        }

        .welcome-text h2 {
            color: #1a202c;
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }

        .welcome-text p {
            color: #6b7280;
            font-size: 1rem;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            border: 1px solid #e2e8f0;
            text-align: center;
            transition: transform 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-2px);
        }

        .stat-icon {
            width: 50px;
            height: 50px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
            margin: 0 auto 1rem;
        }

        .stat-icon.blue {
            background: linear-gradient(135deg, #3b82f6, #1d4ed8);
            color: white;
        }

        .stat-icon.green {
            background: linear-gradient(135deg, #10b981, #047857);
            color: white;
        }

        .stat-icon.purple {
            background: linear-gradient(135deg, #8b5cf6, #7c3aed);
            color: white;
        }

        .stat-icon.orange {
            background: linear-gradient(135deg, #f59e0b, #d97706);
            color: white;
        }

        .stat-number {
            font-size: 2rem;
            font-weight: 700;
            color: #1a202c;
            display: block;
            margin-bottom: 0.25rem;
        }

        .stat-label {
            font-size: 0.875rem;
            color: #6b7280;
            font-weight: 500;
        }

        .controls-section {
            background: white;
            border-radius: 16px;
            padding: 1.5rem;
            margin-bottom: 2rem;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            border: 1px solid #e2e8f0;
        }

        .controls-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 1rem;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .controls-title {
            font-size: 1.125rem;
            font-weight: 600;
            color: #1a202c;
        }

        .filter-controls {
            display: flex;
            gap: 1rem;
            align-items: center;
            flex-wrap: wrap;
        }

        .filter-group {
            display: flex;
            flex-direction: column;
            gap: 0.25rem;
        }

        .filter-label {
            font-size: 0.75rem;
            font-weight: 500;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .filter-select {
            padding: 0.5rem 0.75rem;
            border: 2px solid #e5e7eb;
            border-radius: 8px;
            font-size: 0.875rem;
            background: white;
            min-width: 150px;
            transition: all 0.2s ease;
        }

        .filter-select:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }

        .timetable-container {
            background: white;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            border: 1px solid #e2e8f0;
        }

        .timetable-header {
            background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
            padding: 1.5rem;
            border-bottom: 1px solid #e2e8f0;
        }

        .timetable-header h2 {
            font-size: 1.25rem;
            font-weight: 600;
            color: #1a202c;
            margin: 0;
        }

        .table-wrapper {
            overflow-x: auto;
            background: white;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.875rem;
        }

        table thead {
            background: linear-gradient(135deg, #1e40af 0%, #3730a3 100%);
            color: white;
            position: sticky;
            top: 0;
            z-index: 10;
        }

        table th {
            padding: 1rem 0.75rem;
            text-align: center;
            font-weight: 600;
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            border-right: 1px solid rgba(255, 255, 255, 0.1);
        }

        table th:first-child {
            background: linear-gradient(135deg, #1e3a8a 0%, #312e81 100%);
            position: sticky;
            left: 0;
            z-index: 11;
            min-width: 100px;
        }

        table td {
            padding: 1rem 0.75rem;
            text-align: left;
            border: 1px solid #e5e7eb;
            vertical-align: top;
            position: relative;
            min-width: 180px;
            min-height: 80px;
        }

        table tbody tr:nth-child(odd) {
            background: #fafbfc;
        }

        table tbody tr:hover {
            background: #f1f5f9;
        }

        table td:first-child {
            background: #f8fafc;
            font-weight: 600;
            color: #374151;
            border-right: 2px solid #d1d5db;
            position: sticky;
            left: 0;
            z-index: 5;
            vertical-align: middle;
            text-align: center;
            min-width: 100px;
        }

        .course-card {
            background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
            color: white;
            padding: 1rem;
            border-radius: 12px;
            margin-bottom: 0.5rem;
            cursor: pointer;
            transition: all 0.3s ease;
            border: 2px solid transparent;
            box-shadow: 0 2px 8px rgba(59, 130, 246, 0.2);
        }

        .course-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 20px rgba(59, 130, 246, 0.3);
            border-color: rgba(255, 255, 255, 0.3);
        }

        .course-name {
            font-weight: 600;
            font-size: 0.9rem;
            margin-bottom: 0.5rem;
            line-height: 1.3;
        }

        .course-details {
            font-size: 0.75rem;
            opacity: 0.9;
            display: flex;
            flex-direction: column;
            gap: 0.25rem;
        }

        .course-info {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .course-info i {
            width: 12px;
            text-align: center;
        }

        .empty-slot {
            color: #9ca3af;
            font-size: 0.875rem;
            font-style: italic;
            text-align: center;
            padding: 2rem 1rem;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }

        .empty-slot i {
            font-size: 2rem;
            opacity: 0.3;
        }

        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.6);
            backdrop-filter: blur(4px);
            animation: fadeIn 0.3s ease;
        }

        .modal-content {
            background: white;
            margin: 3% auto;
            padding: 0;
            border-radius: 16px;
            width: 90%;
            max-width: 600px;
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.25);
            animation: slideIn 0.3s ease;
            overflow: hidden;
            max-height: 85vh;
            overflow-y: auto;
        }

        .modal-header {
            background: linear-gradient(135deg, #1e40af 0%, #3730a3 100%);
            color: white;
            padding: 1.5rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .modal-header h3 {
            margin: 0;
            font-size: 1.25rem;
            font-weight: 600;
        }

        .modal-close {
            background: none;
            border: none;
            color: white;
            font-size: 1.5rem;
            cursor: pointer;
            padding: 0.25rem;
            border-radius: 4px;
            transition: background 0.2s ease;
        }

        .modal-close:hover {
            background: rgba(255, 255, 255, 0.1);
        }

        .modal-body {
            padding: 1.5rem;
        }

        .course-detail-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 1.5rem;
        }

        .detail-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.75rem;
            background: #f8fafc;
            border-radius: 8px;
            border: 1px solid #e2e8f0;
        }

        .detail-label {
            font-weight: 500;
            color: #6b7280;
            font-size: 0.875rem;
        }

        .detail-value {
            font-weight: 600;
            color: #1a202c;
            font-size: 0.875rem;
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        @keyframes slideIn {
            from { transform: translateY(-50px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }

        @media (max-width: 768px) {
            .header-content {
                flex-direction: column;
                text-align: center;
            }

            .welcome-content {
                flex-direction: column;
                text-align: center;
            }

            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }

            .controls-header {
                flex-direction: column;
                align-items: stretch;
            }

            .filter-controls {
                justify-content: center;
            }

            table th:first-child,
            table td:first-child {
                min-width: 80px;
            }

            .modal-content {
                width: 95%;
                margin: 2% auto;
            }

            .course-detail-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <header class="header">
        <div class="header-content">
            <div class="professor-info">
                <div class="professor-avatar">
                    <?php echo strtoupper(substr($professor['name'], 0, 1)); ?>
                </div>
                <div class="professor-details">
                    <h1>Welcome, <?php echo htmlspecialchars($professor['name']); ?></h1>
                    <p>Your Teaching Schedule Dashboard</p>
                </div>
            </div>
            <div class="header-actions">
                <a href="professor_profile.php" class="btn btn-light">
                    <i class="fas fa-user"></i>
                    Profile
                </a>
                <a href="logout.php" class="btn btn-danger">
                    <i class="fas fa-sign-out-alt"></i>
                    Logout
                </a>
            </div>
        </div>
    </header>

    <div class="container">
        <?php
        // Get professor's statistics
        $total_courses_query = "SELECT COUNT(DISTINCT course_id) as count FROM schedule WHERE professor_id = ?";
        $stmt = $con->prepare($total_courses_query);
        $stmt->bind_param("i", $professor_id);
        $stmt->execute();
        $total_courses = $stmt->get_result()->fetch_assoc()['count'];

        $total_classes_query = "SELECT COUNT(*) as count FROM schedule WHERE professor_id = ?";
        $stmt = $con->prepare($total_classes_query);
        $stmt->bind_param("i", $professor_id);
        $stmt->execute();
        $total_classes = $stmt->get_result()->fetch_assoc()['count'];

        $total_students_query = "SELECT SUM(c.students_enrolled) as total FROM schedule s JOIN courses c ON s.course_id = c.id WHERE s.professor_id = ?";
        $stmt = $con->prepare($total_students_query);
        $stmt->bind_param("i", $professor_id);
        $stmt->execute();
        $total_students_result = $stmt->get_result()->fetch_assoc();
        $total_students = $total_students_result['total'] ?? 0;

        $unique_classrooms_query = "SELECT COUNT(DISTINCT classroom_id) as count FROM schedule WHERE professor_id = ?";
        $stmt = $con->prepare($unique_classrooms_query);
        $stmt->bind_param("i", $professor_id);
        $stmt->execute();
        $unique_classrooms = $stmt->get_result()->fetch_assoc()['count'];
        ?>

        <div class="welcome-section">
            <div class="welcome-content">
                <div class="welcome-text">
                    <h2>Your Teaching Overview</h2>
                    <p>Manage your classes and view your weekly schedule at a glance</p>
                </div>
                <div style="color: #6b7280; font-size: 0.9rem;">
                    <i class="fas fa-calendar-alt"></i>
                    Last updated: <?php echo date('M d, Y - H:i'); ?>
                </div>
            </div>
        </div>

        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon blue">
                    <i class="fas fa-book"></i>
                </div>
                <span class="stat-number"><?php echo $total_courses; ?></span>
                <span class="stat-label">Courses Teaching</span>
            </div>
            <div class="stat-card">
                <div class="stat-icon green">
                    <i class="fas fa-chalkboard-teacher"></i>
                </div>
                <span class="stat-number"><?php echo $total_classes; ?></span>
                <span class="stat-label">Total Classes</span>
            </div>
            <div class="stat-card">
                <div class="stat-icon purple">
                    <i class="fas fa-users"></i>
                </div>
                <span class="stat-number"><?php echo $total_students; ?></span>
                <span class="stat-label">Students Enrolled</span>
            </div>
            <div class="stat-card">
                <div class="stat-icon orange">
                    <i class="fas fa-door-open"></i>
                </div>
                <span class="stat-number"><?php echo $unique_classrooms; ?></span>
                <span class="stat-label">Classrooms Used</span>
            </div>
        </div>

        <div class="controls-section">
            <div class="controls-header">
                <h3 class="controls-title">
                    <i class="fas fa-filter" style="margin-right: 0.5rem; color: #3b82f6;"></i>
                    Filter Your Schedule
                </h3>
                <div class="filter-controls">
                    <div class="filter-group">
                        <label class="filter-label">Semester</label>
                        <select class="filter-select" id="semesterFilter">
                            <option value="">All Semesters</option>
                            <?php
                            $semesters_query = "SELECT DISTINCT s.id, s.name FROM semester s 
                                              JOIN courses c ON s.id = c.semester_id 
                                              JOIN schedule sc ON c.id = sc.course_id 
                                              WHERE sc.professor_id = ? 
                                              ORDER BY s.name";
                            $stmt = $con->prepare($semesters_query);
                            $stmt->bind_param("i", $professor_id);
                            $stmt->execute();
                            $semesters_result = $stmt->get_result();
                            while ($semester_row = $semesters_result->fetch_assoc()) {
                                $selected = ($semester_filter == $semester_row['id']) ? 'selected' : '';
                                echo "<option value='{$semester_row['id']}' {$selected}>{$semester_row['name']}</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <button class="btn" style="background: #3b82f6; color: white;" onclick="applyFilters()">
                        <i class="fas fa-search"></i>
                        Apply Filters
                    </button>
                </div>
            </div>
        </div>

        <div class="timetable-container">
            <div class="timetable-header">
                <h2>
                    <i class="fas fa-calendar-week" style="margin-right: 0.5rem; color: #3b82f6;"></i>
                    My Weekly Teaching Schedule
                </h2>
            </div>

            <div class="table-wrapper">
                <table id="professorTimetable">
                    <thead>
                        <tr>
                            <th>Day</th>
                            <th>9:00-10:00</th>
                            <th>10:00-11:00</th>
                            <th>11:00-12:00</th>
                            <th>12:00-1:00</th>
                            <th>2:00-3:00</th>
                            <th>3:00-4:00</th>
                            <th>4:00-5:00</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];
                        $time_slots = ['9:00-10:00', '10:00-11:00', '11:00-12:00', '12:00-1:00', '2:00-3:00', '3:00-4:00', '4:00-5:00'];

                        foreach ($days as $day) {
                            echo "<tr>";
                            echo "<td>$day</td>";

                            foreach ($time_slots as $time) {
                                echo "<td>";

                                // Get professor's courses for this time slot
                                $schedule_query = "
                                    SELECT 
                                        s.id as schedule_id,
                                        s.day,
                                        s.time_slot,
                                        c.id as course_id,
                                        c.name AS course_name,
                                        
                                        c.students_enrolled,
                                        c.credits,
                                        cl.id as classroom_id,
                                        cl.name AS classroom_name,
                                        cl.capacity,
                                        cl.has_projector,
                                        COALESCE(b.building_name, 'N/A') AS building_name,
                                        sem.name as semester_name
                                    FROM schedule s
                                    JOIN courses c ON s.course_id = c.id
                                    JOIN classrooms cl ON s.classroom_id = cl.id
                                    LEFT JOIN buildings b ON cl.building_id = b.id
                                    JOIN semester sem ON c.semester_id = sem.id
                                    WHERE s.professor_id = ? AND s.day = ? AND s.time_slot = ?";

                                if (!empty($semester_filter)) {
                                    $schedule_query .= " AND c.semester_id = ?";
                                }

                                $schedule_query .= " ORDER BY c.name";

                                $stmt = $con->prepare($schedule_query);
                                
                                if (!empty($semester_filter)) {
                                    $stmt->bind_param("issi", $professor_id, $day, $time, $semester_filter);
                                } else {
                                    $stmt->bind_param("iss", $professor_id, $day, $time);
                                }
                                
                                $stmt->execute();
                                $result = $stmt->get_result();
                                $courses = [];
                                
                                while ($row = $result->fetch_assoc()) {
                                    $courses[] = $row;
                                }

                                if (count($courses) > 0) {
                                    foreach ($courses as $course) {
                                        echo "<div class='course-card' onclick='showCourseDetails(" . json_encode($course) . ")'>";
                                        echo "<div class='course-name'>{$course['course_name']}</div>";
                                        echo "<div class='course-details'>";
                                        /*echo "<div class='course-info'><i class='fas fa-door-open'></i> {$course['classroom_name']}</div>";
                                        echo "<div class='course-info'><i class='fas fa-building'></i> {$course['building_name']}</div>";
                                        echo "<div class='course-info'><i class='fas fa-users'></i> {$course['students_enrolled']} students</div>";
                                        echo "<div class='course-info'><i class='fas fa-graduation-cap'></i> {$course['semester_name']}</div>";*/
                                        echo "</div>";
                                        echo "</div>";
                                    }
                                } else {
                                    echo "<div class='empty-slot'>";
                                    echo "<i class='fas fa-coffee'></i>";
                                    echo "<span>Free Time</span>";
                                    echo "</div>";
                                }

                                echo "</td>";
                            }
                            echo "</tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Course Details Modal -->
    <div id="courseModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Course Details</h3>
                <button class="modal-close" onclick="closeCourseModal()">&times;</button>
            </div>
            <div class="modal-body" id="modalBody">
                <!-- Course details will be populated here -->
            </div>
        </div>
    </div>

    <script>
        function showCourseDetails(course) {
            const modal = document.getElementById('courseModal');
            const modalBody = document.getElementById('modalBody');
            
            const utilizationPercentage = ((course.students_enrolled / course.capacity) * 100).toFixed(1);
            const isOvercapacity = course.students_enrolled > course.capacity;
            
            modalBody.innerHTML = `
                <div style="margin-bottom: 1.5rem;">
                    <h4 style="color: #1a202c; font-size: 1.25rem; font-weight: 600; margin-bottom: 0.5rem;">
                        ${course.course_name}
                    </h4>
                   
                </div>
                
                <div class="course-detail-grid">
                    <div class="detail-item">
                        <span class="detail-label">Schedule</span>
                        <span class="detail
                        <span class="detail-value">${course.day} ${course.time_slot}</span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Classroom</span>
                        <span class="detail-value">${course.classroom_name}</span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Building</span>
                        <span class="detail-value">${course.building_name}</span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Capacity</span>
                        <span class="detail-value">${course.capacity} seats</span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Enrolled</span>
                        <span class="detail-value" style="color: ${isOvercapacity ? '#dc2626' : '#10b981'};">
                            ${course.students_enrolled} students
                        </span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Utilization</span>
                        <span class="detail-value" style="color: ${isOvercapacity ? '#dc2626' : '#10b981'};">
                            ${utilizationPercentage}%
                        </span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Credits</span>
                        <span class="detail-value">${course.credits}</span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Semester</span>
                        <span class="detail-value">${course.semester_name}</span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Projector</span>
                        <span class="detail-value">
                            ${course.has_projector ? 
                                '<i class="fas fa-check" style="color: #10b981;"></i> Available' : 
                                '<i class="fas fa-times" style="color: #dc2626;"></i> Not Available'
                            }
                        </span>
                    </div>
                </div>
                
                ${isOvercapacity ? `
                    <div style="background: #fef2f2; border: 1px solid #fecaca; border-radius: 8px; padding: 1rem; margin-top: 1rem;">
                        <div style="display: flex; align-items: center; gap: 0.5rem; color: #dc2626;">
                            <i class="fas fa-exclamation-triangle"></i>
                            <strong>Overcapacity Warning</strong>
                        </div>
                        <p style="margin: 0.5rem 0 0 0; color: #991b1b; font-size: 0.875rem;">
                            This class has ${course.students_enrolled - course.capacity} more students than the classroom capacity.
                        </p>
                    </div>
                ` : ''}
                
                <div style="margin-top: 1.5rem; padding-top: 1.5rem; border-top: 1px solid #e5e7eb;">
                    <div style="display: flex; justify-content: space-between; align-items: center; gap: 1rem;">
                        <div style="color: #6b7280; font-size: 0.875rem;">
                            <i class="fas fa-info-circle"></i>
                            Course assigned to ${course.classroom_name} in ${course.building_name}
                        </div>
                        <div style="display: flex; gap: 0.5rem;">
                            <button class="btn" style="background: #10b981; color: white; font-size: 0.875rem; padding: 0.5rem 1rem;" 
                                    onclick="printCourseDetails()">
                                <i class="fas fa-print"></i>
                                Print
                            </button>
                        </div>
                    </div>
                </div>
            `;
            
            // Store current course data for printing
            window.currentCourseDetails = course;
            
            modal.style.display = 'block';
            
            // Close modal when clicking outside
            modal.onclick = function(event) {
                if (event.target === modal) {
                    closeCourseModal();
                }
            }
        }

        function closeCourseModal() {
            const modal = document.getElementById('courseModal');
            modal.style.display = 'none';
        }

        function printCourseDetails() {
            if (!window.currentCourseDetails) return;
            
            const course = window.currentCourseDetails;
            const printWindow = window.open('', '_blank');
            const utilizationPercentage = ((course.students_enrolled / course.capacity) * 100).toFixed(1);
            
            printWindow.document.write(`
                <!DOCTYPE html>
                <html>
                <head>
                    <title>Course Details - ${course.course_name}</title>
                    <style>
                        body { font-family: Arial, sans-serif; margin: 40px; }
                        .header { text-align: center; margin-bottom: 30px; }
                        .details { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; }
                        .detail-row { display: flex; justify-content: space-between; padding: 10px; border-bottom: 1px solid #eee; }
                        .label { font-weight: bold; }
                    </style>
                </head>
                <body>
                    <div class="header">
                        <h1>${course.course_name}</h1>
                       
                    </div>
                    <div class="details">
                        <div class="detail-row">
                            <span class="label">Schedule:</span>
                            <span>${course.day} ${course.time_slot}</span>
                        </div>
                        <div class="detail-row">
                            <span class="label">Classroom:</span>
                            <span>${course.classroom_name}</span>
                        </div>
                        <div class="detail-row">
                            <span class="label">Building:</span>
                            <span>${course.building_name}</span>
                        </div>
                        <div class="detail-row">
                            <span class="label">Capacity:</span>
                            <span>${course.capacity} seats</span>
                        </div>
                        <div class="detail-row">
                            <span class="label">Enrolled:</span>
                            <span>${course.students_enrolled} students</span>
                        </div>
                        <div class="detail-row">
                            <span class="label">Utilization:</span>
                            <span>${utilizationPercentage}%</span>
                        </div>
                        <div class="detail-row">
                            <span class="label">Credits:</span>
                            <span>${course.credits}</span>
                        </div>
                        <div class="detail-row">
                            <span class="label">Semester:</span>
                            <span>${course.semester_name}</span>
                        </div>
                        <div class="detail-row">
                            <span class="label">Projector:</span>
                            <span>${course.has_projector ? 'Available' : 'Not Available'}</span>
                        </div>
                    </div>
                    <div style="margin-top: 30px; text-align: center; color: #666;">
                        <p>Generated on ${new Date().toLocaleDateString()}</p>
                    </div>
                </body>
                </html>
            `);
            
            printWindow.document.close();
            printWindow.print();
        }

        function applyFilters() {
            const semesterFilter = document.getElementById('semesterFilter').value;
            const currentUrl = new URL(window.location.href);
            
            // Update URL parameters
            if (semesterFilter) {
                currentUrl.searchParams.set('semester', semesterFilter);
            } else {
                currentUrl.searchParams.delete('semester');
            }
            
            // Reload page with new filters
            window.location.href = currentUrl.toString();
        }

        // Close modal when pressing Escape key
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                closeCourseModal();
            }
        });

        // Add loading state to filter button
        function showLoadingState() {
            const filterBtn = document.querySelector('button[onclick="applyFilters()"]');
            if (filterBtn) {
                filterBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Loading...';
                filterBtn.disabled = true;
            }
        }

        // Enhanced filter functionality
        document.getElementById('semesterFilter').addEventListener('change', function() {
            if (this.value !== '') {
                const filterBtn = document.querySelector('button[onclick="applyFilters()"]');
                if (filterBtn) {
                    filterBtn.style.background = '#059669';
                    filterBtn.innerHTML = '<i class="fas fa-check"></i> Apply Filters';
                }
            }
        });

        // Add smooth scroll for better UX
        function scrollToTimetable() {
            document.querySelector('.timetable-container').scrollIntoView({
                behavior: 'smooth'
            });
        }

        // Add keyboard navigation for course cards
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Enter' && event.target.classList.contains('course-card')) {
                event.target.click();
            }
        });

        // Make course cards focusable for accessibility
        document.addEventListener('DOMContentLoaded', function() {
            const courseCards = document.querySelectorAll('.course-card');
            courseCards.forEach(card => {
                card.setAttribute('tabindex', '0');
                card.setAttribute('role', 'button');
                card.setAttribute('aria-label', 'View course details');
            });
        });

        // Add print functionality for entire schedule
        function printSchedule() {
            const printWindow = window.open('', '_blank');
            const professorName = '<?php echo htmlspecialchars($professor['name']); ?>';
            const currentDate = new Date().toLocaleDateString();
            
            // Get timetable HTML
            const timetableHTML = document.querySelector('#professorTimetable').outerHTML;
            
            printWindow.document.write(`
                <!DOCTYPE html>
                <html>
                <head>
                    <title>Teaching Schedule - ${professorName}</title>
                    <style>
                        body { font-family: Arial, sans-serif; margin: 20px; }
                        .header { text-align: center; margin-bottom: 30px; }
                        table { width: 100%; border-collapse: collapse; font-size: 12px; }
                        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
                        th { background-color: #f2f2f2; font-weight: bold; }
                        .course-card { background: #f8f9fa; padding: 5px; margin: 2px 0; border-radius: 4px; }
                        .course-name { font-weight: bold; font-size: 11px; }
                        .course-details { font-size: 10px; color: #666; }
                        @media print { body { margin: 0; } }
                    </style>
                </head>
                <body>
                    <div class="header">
                        <h1>Teaching Schedule</h1>
                        <h2>${professorName}</h2>
                        <p>Generated on ${currentDate}</p>
                    </div>
                    ${timetableHTML}
                </body>
                </html>
            `);
            
            printWindow.document.close();
            printWindow.print();
        }

        // Add export functionality
        function exportSchedule() {
            const professorName = '<?php echo htmlspecialchars($professor['name']); ?>';
            const courses = [];
            
            // Collect all course data
            document.querySelectorAll('.course-card').forEach(card => {
                const courseName = card.querySelector('.course-name').textContent;
                const courseDetails = Array.from(card.querySelectorAll('.course-info')).map(info => info.textContent);
                courses.push({
                    name: courseName,
                    details: courseDetails
                });
            });
            
            // Create CSV content
            let csvContent = `Professor Teaching Schedule - ${professorName}\n`;
            csvContent += `Generated on: ${new Date().toLocaleDateString()}\n\n`;
            csvContent += `Course Name,Day,Time,Classroom,Building,Students,Semester\n`;
            
            // This would need to be enhanced to actually parse the course data properly
            // For now, just show the concept
            
            const blob = new Blob([csvContent], { type: 'text/csv' });
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = `${professorName.replace(/\s+/g, '_')}_schedule.csv`;
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
            window.URL.revokeObjectURL(url);
        }

        // Add these buttons to the controls section via JavaScript
        document.addEventListener('DOMContentLoaded', function() {
            const controlsHeader = document.querySelector('.controls-header .filter-controls');
            if (controlsHeader) {
                const exportButtons = document.createElement('div');
                exportButtons.style.display = 'flex';
                exportButtons.style.gap = '0.5rem';
                exportButtons.innerHTML = `
                    <button class="btn" style="background: #6366f1; color: white;" onclick="printSchedule()">
                        <i class="fas fa-print"></i>
                        Print Schedule
                    </button>
                `;
                controlsHeader.appendChild(exportButtons);
            }
        });

    </script>
</body>
</html>