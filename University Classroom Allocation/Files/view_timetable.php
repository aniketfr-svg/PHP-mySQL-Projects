<?php include('config.php');// Get filter parameters
$semester_filter = isset($_GET['semester']) ? $_GET['semester'] : '';
$building_filter = isset($_GET['building']) ? $_GET['building'] : ''; ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Academic Timetable - Management System</title>
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
            background: #f8fafc;
            color: #1a202c;
            line-height: 1.6;
            min-height: 100vh;
        }

        .header {
            background: linear-gradient(135deg, #1e40af 0%, #3730a3 100%);
            color: white;
            padding: 2rem 0;
            box-shadow: 0 4px 20px rgba(30, 64, 175, 0.15);
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

        .header-title {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .header-title h1 {
            font-size: 1.75rem;
            font-weight: 700;
            margin: 0;
        }

        .header-icon {
            width: 50px;
            height: 50px;
            background: rgba(255, 255, 255, 0.15);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
        }

        .header-actions {
            display: flex;
            gap: 0.75rem;
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

        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 2rem 1.5rem;
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
            min-width: 120px;
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

        table th:first-child,
        table th:nth-child(2) {
            background: linear-gradient(135deg, #1e3a8a 0%, #312e81 100%);
            position: sticky;
            left: 0;
            z-index: 11;
        }

        table th:nth-child(2) {
            left: 80px;
        }

        table td {
            padding: 0.75rem 0.5rem;
            text-align: left;
            border: 1px solid #e5e7eb;
            vertical-align: top;
            position: relative;
            min-width: 150px;
            min-height: 60px;
        }

        table tbody tr:nth-child(odd) {
            background: #fafbfc;
        }

        table tbody tr:hover {
            background: #f1f5f9;
        }

        table td:first-child,
        table td:nth-child(2) {
            background: #f8fafc;
            font-weight: 600;
            color: #374151;
            border-right: 2px solid #d1d5db;
            position: sticky;
            left: 0;
            z-index: 5;
            vertical-align: middle;
            text-align: center;
        }

        table td:nth-child(2) {
            left: 80px;
        }

        .course-list {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
            font-size: 0.8rem;
        }

        .course-item {
            display: block;
            color: #374151;
            text-decoration: none;
            padding: 0.25rem 0;
            border-left: 3px solid transparent;
            padding-left: 0.5rem;
            transition: all 0.2s ease;
            cursor: pointer;
            line-height: 1.3;
        }

        .course-item:hover {
            background: #f3f4f6;
            border-left-color: #3b82f6;
            transform: translateX(2px);
        }

        .course-name {
            font-weight: 600;
            color: #1f2937;
            display: block;
            margin-bottom: 0.1rem;
        }

        .course-details {
            font-size: 0.7rem;
            color: #6b7280;
            display: block;
        }

        .course-room {
            font-weight: 500;
            color: #059669;
        }

        .course-professor {
            color: #7c3aed;
        }

        .multiple-courses-indicator {
            position: absolute;
            top: 0.25rem;
            right: 0.25rem;
            background: #ef4444;
            color: white;
            font-size: 0.65rem;
            font-weight: 600;
            padding: 0.15rem 0.4rem;
            border-radius: 12px;
            line-height: 1;
        }

        .empty-slot {
            color: #9ca3af;
            font-size: 0.75rem;
            font-style: italic;
            text-align: center;
            padding: 1rem;
            display: flex;
            align-items: center;
            justify-content: center;
            height: 60px;
        }

        .capacity-warning {
            color: #dc2626;
            font-weight: 600;
        }

        .capacity-ok {
            color: #059669;
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
            max-width: 700px;
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
            position: sticky;
            top: 0;
            z-index: 10;
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

        .course-details-list {
            display: flex;
            flex-direction: column;
            gap: 1.25rem;
        }

        .course-detail-card {
            background: #f8fafc;
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            padding: 1.25rem;
            position: relative;
            transition: all 0.3s ease;
        }

        .course-detail-card:hover {
            border-color: #3b82f6;
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.1);
        }

        .course-detail-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 1rem;
            flex-wrap: wrap;
            gap: 0.5rem;
        }

        .course-title {
            font-weight: 600;
            font-size: 1.1rem;
            color: #1a202c;
        }

        .course-badge {
            background: #3b82f6;
            color: white;
            padding: 0.3rem 0.6rem;
            border-radius: 8px;
            font-size: 0.75rem;
            font-weight: 600;
        }

        .detail-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 0.75rem;
        }

        .detail-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.5rem 0;
            border-bottom: 1px solid #e5e7eb;
        }

        .detail-item:last-child {
            border-bottom: none;
        }

        .detail-label {
            font-weight: 500;
            color: #374151;
            font-size: 0.875rem;
        }

        .detail-value {
            font-weight: 600;
            color: #1a202c;
            font-size: 0.875rem;
        }

        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.25rem;
            padding: 0.25rem 0.5rem;
            border-radius: 6px;
            font-size: 0.75rem;
            font-weight: 500;
        }

        .status-available {
            background: #dcfce7;
            color: #166534;
        }

        .status-overcapacity {
            background: #fee2e2;
            color: #991b1b;
        }

        .stats-bar {
            background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%);
            border: 1px solid #bae6fd;
            border-radius: 12px;
            padding: 1rem;
            margin-bottom: 1.5rem;
            display: flex;
            justify-content: space-around;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .stat-item {
            text-align: center;
            flex: 1;
            min-width: 120px;
        }

        .stat-number {
            font-size: 1.5rem;
            font-weight: 700;
            color: #0369a1;
            display: block;
        }

        .stat-label {
            font-size: 0.75rem;
            color: #0369a1;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            font-weight: 500;
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

            .controls-header {
                flex-direction: column;
                align-items: stretch;
            }

            .filter-controls {
                justify-content: center;
            }

            table th:first-child,
            table td:first-child {
                min-width: 70px;
            }

            table th:nth-child(2),
            table td:nth-child(2) {
                left: 70px;
                min-width: 100px;
            }

            .modal-content {
                width: 95%;
                margin: 2% auto;
            }

            .course-list {
                font-size: 0.75rem;
            }
        }
    </style>
</head>
<body>
    <header class="header">
        <div class="header-content">
            <div class="header-title">
                <div class="header-icon">
                    <i class="fas fa-calendar-alt"></i>
                </div>
                <div>
                    <h1>Academic Timetable</h1>
                    <p style="margin: 0; opacity: 0.9; font-size: 0.9rem;">Comprehensive Course Schedule Management</p>
                </div>
            </div>
            <div class="header-actions">
                <a href="generate_timetable.php" class="btn btn-light">
                    <i class="fas fa-sync-alt"></i>
                    Regenerate
                </a>
                <a href="index.php" class="btn btn-light">
                    <i class="fas fa-home"></i>
                    Dashboard
                </a>
            </div>
        </div>
    </header>

    <div class="container">
        <div class="controls-section">
            <div class="controls-header">
                <h3 class="controls-title">
                    <i class="fas fa-filter" style="margin-right: 0.5rem; color: #3b82f6;"></i>
                    Timetable Filters
                </h3>
                <div class="filter-controls">
                    <div class="filter-group">
                        <label class="filter-label">Semester</label>
                        <select class="filter-select" id="semesterFilter">
                            <option value="">All Semesters</option>
                            <?php
                            $semesters_query = "SELECT DISTINCT semester.id, semester.name FROM semester JOIN courses ON semester.id = courses.semester_id JOIN schedule ON courses.id = schedule.course_id ORDER BY semester.name";
                            $semesters_result = $con->query($semesters_query);
                            while ($semester_row = $semesters_result->fetch_assoc()) {
                                echo "<option value='{$semester_row['id']}'>{$semester_row['name']}</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div class="filter-group">
                        <label class="filter-label">Building</label>
                        <select class="filter-select" id="buildingFilter">
                            <option value="">All Buildings</option>
                            <?php
                            $buildings_query = "SELECT DISTINCT buildings.id, buildings.building_name FROM buildings JOIN classrooms ON buildings.id = classrooms.building_id JOIN schedule ON classrooms.id = schedule.classroom_id ORDER BY buildings.building_name";
                            $buildings_result = $con->query($buildings_query);
                            while ($building_row = $buildings_result->fetch_assoc()) {
                                echo "<option value='{$building_row['id']}'>{$building_row['building_name']}</option>";
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

            <?php
            $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];
            $time_slots = ['9:00-10:00', '10:00-11:00', '11:00-12:00', '12:00-1:00', '2:00-3:00', '3:00-4:00', '4:00-5:00'];
            
            // Get comprehensive statistics
            $total_classes = $con->query("SELECT COUNT(*) as count FROM schedule")->fetch_assoc()['count'];
            $total_courses = $con->query("SELECT COUNT(DISTINCT course_id) as count FROM schedule")->fetch_assoc()['count'];
            $total_classrooms = $con->query("SELECT COUNT(DISTINCT classroom_id) as count FROM schedule")->fetch_assoc()['count'];
            
            // Calculate overlapping courses with better logic
            $overlap_query = "
                SELECT day, time_slot, COUNT(*) as overlap_count
                FROM schedule 
                GROUP BY day, time_slot 
                HAVING COUNT(*) > 1
            ";
            $overlap_result = $con->query($overlap_query);
            $overlapping_slots = 0;
            $total_overlaps = 0;
            while ($row = $overlap_result->fetch_assoc()) {
                $overlapping_slots++;
                $total_overlaps += $row['overlap_count'] - 1; // Count extra courses beyond the first
            }
            ?>

            <div class="stats-bar">
                <div class="stat-item">
                    <span class="stat-number"><?php echo $total_classes; ?></span>
                    <span class="stat-label">Total Classes</span>
                </div>
                <div class="stat-item">
                    <span class="stat-number"><?php echo $total_courses; ?></span>
                    <span class="stat-label">Active Courses</span>
                </div>
                <div class="stat-item">
                    <span class="stat-number"><?php echo $total_classrooms; ?></span>
                    <span class="stat-label">Classrooms Used</span>
                </div>
                <div class="stat-item">
                    <span class="stat-number"><?php echo $overlapping_slots; ?></span>
                    <span class="stat-label">Overlapping Slots</span>
                </div>
                <div class="stat-item">
                    <span class="stat-number"><?php echo $total_overlaps; ?></span>
                    <span class="stat-label">Conflicts</span>
                </div>
            </div>
            <!-- Quick Actions Panel -->
<div class="quick-actions">
    <div class="actions-header">
        <h3><i class="fas fa-bolt"></i> Quick Actions</h3>
    </div>
    
    <div class="export-dropdown" style="position: relative;">
        <button class="action-item" onclick="toggleExportMenu()" id="exportBtn">
            <i class="fas fa-download"></i>
            <span>Export Schedule</span>
            <i class="fas fa-chevron-down" style="margin-left: 5px; font-size: 0.8em;"></i>
        </button>
        
        <div class="export-menu" id="exportMenu" style="display: none;">
            <a onclick="exportToPDF()" class="export-option">
                <i class="fas fa-file-pdf"></i>
                <span>Export as PDF</span>
            </a>
            <a onclick="exportToExcel()" class="export-option">
                <i class="fas fa-file-excel"></i>
                <span>Export as Excel</span>
            </a>
            <a onclick="printTimetable()" class="export-option">
                <i class="fas fa-print"></i>
                <span>Print Timetable</span>
            </a>
        </div>
    </div>
</div>
        </div>

        <div class="timetable-container">
            <div class="timetable-header">
                <h2>
                    <i class="fas fa-table" style="margin-right: 0.5rem; color: #3b82f6;"></i>
                    Weekly Academic Schedule - Compact View
                </h2>
            </div>

            <div class="table-wrapper">
                <table id="timetableTable">
                    <thead>
                        <tr>
                            <th style="min-width: 80px;">Day</th>
                            <th style="min-width: 120px;">Semester</th>
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
                        // Get all semesters that have scheduled courses
                        $semesters_query = "
    SELECT DISTINCT semester.id, semester.name 
    FROM semester
    JOIN courses ON semester.id = courses.semester_id
    JOIN schedule ON courses.id = schedule.course_id";

// Add semester filter if selected
if (!empty($semester_filter)) {
    $semesters_query .= " WHERE semester.id = " . intval($semester_filter);
}

$semesters_query .= " ORDER BY semester.name";
                        $semesters_result = $con->query($semesters_query);
                        $semesters = [];
                        while ($semester_row = $semesters_result->fetch_assoc()) {
                            $semesters[] = $semester_row;
                        }

                        foreach ($days as $day) {
                            $first_row = true;
                            foreach ($semesters as $semester_row) {
                                $semester_id = $semester_row['id'];
                                $semester_name = $semester_row['name'];

                                echo $first_row ? "<tr><td rowspan=\"" . count($semesters) . "\" style='vertical-align: middle; font-weight: 600;'>$day</td>" : "<tr>";
                                $first_row = false;

                                echo "<td style='font-weight: 600; color: #374151;'>$semester_name</td>";

                                foreach ($time_slots as $time) {
                                    echo "<td>";
                                    
                                    // Get all courses for this specific time slot, day, and semester
                                    // Get all courses for this specific time slot, day, and semester with filters
$schedule_query = "
    SELECT 
        schedule.id as schedule_id,
        schedule.day,
        schedule.time_slot,
        courses.id as course_id,
        courses.name AS course_name, 
        courses.students_enrolled,
        classrooms.id as classroom_id,
        classrooms.name AS classroom_name, 
        classrooms.capacity,
        classrooms.has_projector,
        COALESCE(buildings.building_name, 'N/A') AS building_name,
        CONCAT(professors.name, ' ') AS professor_name
    FROM schedule 
    JOIN courses ON schedule.course_id = courses.id 
    JOIN classrooms ON schedule.classroom_id = classrooms.id 
    LEFT JOIN buildings ON classrooms.building_id = buildings.id
    LEFT JOIN professors ON courses.professor_id = professors.id
    WHERE schedule.day = ? AND schedule.time_slot = ? AND courses.semester_id = ?";

// Add building filter if selected
if (!empty($building_filter)) {
    $schedule_query .= " AND classrooms.building_id = ?";
}

$schedule_query .= " ORDER BY courses.name";

$stmt = $con->prepare($schedule_query);

// Bind parameters based on whether building filter is applied
if (!empty($building_filter)) {
    $stmt->bind_param("ssii", $day, $time, $semester_id, $building_filter);
} else {
    $stmt->bind_param("ssi", $day, $time, $semester_id);
}
                                    $stmt->execute();
                                    $result = $stmt->get_result();
                                    
                                    $courses = [];
                                    while ($row = $result->fetch_assoc()) {
                                        $courses[] = $row;
                                    }

                                    if (count($courses) > 0) {
                                        // Show indicator for multiple courses
                                        if (count($courses) > 1) {
                                            echo "<div class='multiple-courses-indicator'>" . count($courses) . " courses</div>";
                                        }
                                        
                                        echo "<div class='course-list'>";
                                        
                                        // Display each course in compact text format
                                        // Display only course names in compact format
foreach ($courses as $course) {
    echo "<a href='#' class='course-item' onclick='showCourseDetails(" . json_encode($course) . ")'>";
    echo "<span class='course-name'>{$course['course_name']}</span>";
    echo "</a>";
}
                                        
                                        echo "</div>";
                                    } else {
                                        echo "<div class='empty-slot'>No classes scheduled</div>";
                                    }
                                    
                                    echo "</td>";
                                }
                                echo "</tr>";
                            }
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
        // Course details modal functionality
        function showCourseDetails(course) {
            const modal = document.getElementById('courseModal');
            const modalBody = document.getElementById('modalBody');
            
            const isOvercapacity = course.students_enrolled > course.capacity;
            const utilizationPercentage = ((course.students_enrolled / course.capacity) * 100).toFixed(1);
            
            modalBody.innerHTML = `
                <div class="course-details-list">
                    <div class="course-detail-card">
                        <div class="course-detail-header">
                            <div class="course-title">${course.course_name}</div>
                            <div class="course-badge">${course.course_code || 'N/A'}</div>
                        </div>
                        
                        <div class="detail-grid">
                            <div class="detail-item">
                                <span class="detail-label">Schedule</span>
                                <span class="detail-value">${course.day} ${course.time_slot}</span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label">Professor</span>
                                <span class="detail-value">${course.professor_name}</span>
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
                                <span class="detail-value">${course.capacity} students</span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label">Enrolled</span>
                                <span class="detail-value ${isOvercapacity ? 'capacity-warning' : 'capacity-ok'}">${course.students_enrolled} students</span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label">Utilization</span>
                                <span class="detail-value">${utilizationPercentage}%</span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label">Status</span>
                                <span class="status-badge ${isOvercapacity ? 'status-overcapacity' : 'status-available'}">
                                    <i class="fas ${isOvercapacity ? 'fa-exclamation-triangle' : 'fa-check-circle'}"></i>
                                    ${isOvercapacity ? 'Over Capacity' : 'Available'}
                                </span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label">Projector</span>
                                <span class="detail-value">
                                    <i class="fas ${course.has_projector == 1 ? 'fa-check text-green-600' : 'fa-times text-red-600'}"></i>
                                    ${course.has_projector == 1 ? 'Available' : 'Not Available'}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            
            modal.style.display = 'block';
        }

        function closeCourseModal() {
            document.getElementById('courseModal').style.display = 'none';
        }

        // Close modal when clicking outside of it
        window.onclick = function(event) {
            const modal = document.getElementById('courseModal');
            if (event.target == modal) {
                modal.style.display = 'none';
            }
        }

        // Enhanced filter functionality
        function applyFilters() {
    const semesterFilter = document.getElementById('semesterFilter').value;
    const buildingFilter = document.getElementById('buildingFilter').value;
    
    let params = new URLSearchParams();
    if (semesterFilter) params.append('semester', semesterFilter);
    if (buildingFilter) params.append('building', buildingFilter);
    
    // Show loading state
    const filterBtn = document.querySelector('button[onclick="applyFilters()"]');
    const originalText = filterBtn.innerHTML;
    filterBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Applying...';
    filterBtn.disabled = true;
    
    // Reload page with filters
    const newUrl = params.toString() ? window.location.pathname + '?' + params.toString() : window.location.pathname;
    window.location.href = newUrl;
}

        
        // Set filter values from URL parameters
        document.addEventListener('DOMContentLoaded', function() {
            const urlParams = new URLSearchParams(window.location.search);
            const semesterParam = urlParams.get('semester');
            const buildingParam = urlParams.get('building');
            
            if (semesterParam) {
                document.getElementById('semesterFilter').value = semesterParam;
            }
            if (buildingParam) {
                document.getElementById('buildingFilter').value = buildingParam;
            }
        });

        // Keyboard navigation for modal
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                closeCourseModal();
            }
        });

        // Enhanced table interactions
        document.querySelectorAll('.course-item').forEach(item => {
            item.addEventListener('keydown', function(event) {
                if (event.key === 'Enter' || event.key === ' ') {
                    event.preventDefault();
                    this.click();
                }
            });
        });

        // Add loading state for filters
        function showLoadingState() {
            const filterBtn = document.querySelector('button[onclick="applyFilters()"]');
            const originalText = filterBtn.innerHTML;
            filterBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Applying...';
            filterBtn.disabled = true;
            
            setTimeout(() => {
                filterBtn.innerHTML = originalText;
                filterBtn.disabled = false;
            }, 2000);
        }

        // Update course display to be more compact - replace the existing course display section
        function updateCourseDisplay() {
            // This will be handled by the PHP backend changes
            console.log('Course display updated to compact format');
        }

        // CSS Updates for more compact course display
        const compactStyles = `
            .course-list {
                display: block;
                font-size: 0.8rem;
                line-height: 1.3;
            }

            .course-item {
                display: block;
                color: #374151;
                text-decoration: none;
                padding: 0.4rem 0.5rem;
                border: 2px solid #e5e7eb;
                border-radius: 6px;
                transition: all 0.15s ease;
                cursor: pointer;
                margin-bottom: 0.25rem;
                background: transparent;
            }

            .course-item:hover {
                border-color: #3b82f6;
                color: #1f2937;
                transform: translateY(-1px);
                box-shadow: 0 2px 4px rgba(59, 130, 246, 0.1);
            }

            .course-name {
                font-weight: 600;
                color: #1f2937;
                display: block;
                font-size: 0.8rem;
                line-height: 1.2;
            }

            .multiple-courses-indicator {
                position: absolute;
                top: 0.2rem;
                right: 0.2rem;
                background: #ef4444;
                color: white;
                font-size: 0.6rem;
                font-weight: 600;
                padding: 0.1rem 0.3rem;
                border-radius: 8px;
                line-height: 1;
                z-index: 5;
            }

            table td {
                padding: 0.5rem 0.3rem;
                min-height: 50px;
                vertical-align: top;
            }

            .empty-slot {
                color: #9ca3af;
                font-size: 0.7rem;
                font-style: italic;
                text-align: center;
                padding: 0.5rem;
                height: 40px;
                display: flex;
                align-items: center;
                justify-content: center;
            }
        `;

        // Add the compact styles to the page
        const styleElement = document.createElement('style');
        styleElement.textContent = compactStyles;
        document.head.appendChild(styleElement);
    </script>
</body>
</html>