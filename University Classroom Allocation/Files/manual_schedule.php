<?php
session_start(); // Must be the first thing
include('config.php');

// Fetch course + classroom via AJAX
if (isset($_GET['action']) && $_GET['action'] == 'fetch') {
    $building_id = $_GET['building_id'] ?? '';
    $semester_id = $_GET['semester_id'] ?? '';

    $courses = [];
    $classrooms = [];

    if ($building_id && $semester_id) {
        $courses_result = $con->query("
            SELECT c.id, c.name, s.name AS semester_name 
            FROM courses c 
            JOIN semester s ON c.semester_id = s.id
            WHERE c.building_id = '$building_id' AND c.semester_id = '$semester_id'
        ");
        while ($row = $courses_result->fetch_assoc()) {
            $courses[] = $row;
        }

        $classroom_result = $con->query("SELECT id, name FROM classrooms WHERE building_id = '$building_id'");
        while ($row = $classroom_result->fetch_assoc()) {
            $classrooms[] = $row;
        }
    }

    header('Content-Type: application/json');
    echo json_encode(['courses' => $courses, 'classrooms' => $classrooms]);
    exit;
}

// Fetch buildings and semesters
$buildings = $con->query("SELECT id, building_name FROM buildings");
$semester = $con->query("SELECT id, name FROM semester");

$days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];
$time_slots = ['9:00-10:00', '10:00-11:00', '11:00-12:00', '12:00-1:00', '2:00-3:00', '3:00-4:00', '4:00-5:00'];

$edit_data = null;

// Handle edit pre-fill
if (isset($_GET['edit_id'])) {
    $edit_id = $_GET['edit_id'];
    $edit_result = $con->query("SELECT * FROM schedule WHERE id = '$edit_id' AND is_manual = 1");
    if ($edit_result->num_rows > 0) {
        $edit_data = $edit_result->fetch_assoc();
    }
}

// Handle insert or update
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $course_id = $_POST['course_id'];
    $day = $_POST['day'];
    $time_slot = $_POST['time_slot'];
    $classroom_id = $_POST['classroom_id'];

    $course_result = $con->query("SELECT semester_id, professor_id FROM courses WHERE id = '$course_id'");
    if ($course = $course_result->fetch_assoc()) {
        $semester_id = $course['semester_id'];
        $professor_id = $course['professor_id'];

        if (isset($_POST['update_schedule_id'])) {
            $update_id = $_POST['update_schedule_id'];

            // ✅ This version excludes the current schedule from deletion
            $delete_conflict_query_update = "
                DELETE FROM schedule 
                WHERE (
                    classroom_id = '$classroom_id' OR 
                    course_id IN (SELECT id FROM courses WHERE professor_id = '$professor_id') OR 
                    semester_id = '$semester_id'
                ) 
                AND day = '$day' AND time_slot = '$time_slot'
                AND id != '$update_id'
            ";
            $con->query($delete_conflict_query_update);

            // ✅ Now safe to update the schedule
            $con->query("UPDATE schedule SET 
                course_id = '$course_id',
                classroom_id = '$classroom_id',
                day = '$day',
                time_slot = '$time_slot',
                semester_id = '$semester_id'
                WHERE id = '$update_id'");

            $_SESSION['flash_message'] = "Manual schedule updated successfully!";
            $_SESSION['flash_type'] = "success";
            header("Location: manual_schedule.php");
            exit;

        } else {
            // ✅ This is okay for new inserts
            $con->query("
                DELETE FROM schedule 
                WHERE (
                    classroom_id = '$classroom_id' OR 
                    course_id IN (SELECT id FROM courses WHERE professor_id = '$professor_id') OR 
                    semester_id = '$semester_id'
                ) 
                AND day = '$day' AND time_slot = '$time_slot'
            ");

            $con->query("INSERT INTO schedule (course_id, classroom_id, day, time_slot, semester_id, is_manual)
                         VALUES ('$course_id', '$classroom_id', '$day', '$time_slot', '$semester_id', 1)");
            $_SESSION['flash_message'] = "Manual schedule added successfully!";
            $_SESSION['flash_type'] = "success";
            header("Location: manual_schedule.php");
            exit;
        }
    } else {
        $_SESSION['flash_message'] = "Invalid course selected.";
        $_SESSION['flash_type'] = "error";
    }
}

// Handle delete
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_schedule_id'])) {
    $delete_id = $_POST['delete_schedule_id'];
    $con->query("DELETE FROM schedule WHERE id = '$delete_id' AND is_manual = 1");
    $_SESSION['flash_message'] = "Schedule deleted successfully.";
    $_SESSION['flash_type'] = "success";
    header("Location: manual_schedule.php");
    exit;
}

// Fetch all manual schedules
$schedules = $con->query("
    SELECT sc.id, sc.day, sc.time_slot, sc.classroom_id, cr.name AS course_name, 
           cr.semester_id, cr.building_id, cl.name AS classroom_name, 
           s.name AS semester_name, p.name AS professor_name
    FROM schedule sc
    JOIN courses cr ON sc.course_id = cr.id
    JOIN classrooms cl ON sc.classroom_id = cl.id
    JOIN semester s ON sc.semester_id = s.id
    JOIN professors p ON cr.professor_id = p.id
    WHERE sc.is_manual = 1
    ORDER BY 
        CASE sc.day
            WHEN 'Monday' THEN 1
            WHEN 'Tuesday' THEN 2
            WHEN 'Wednesday' THEN 3
            WHEN 'Thursday' THEN 4
            WHEN 'Friday' THEN 5
        END,
        sc.time_slot
");

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manual Schedule Management | Academic System</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        :root {
            --primary-color: #2563eb;
            --primary-dark: #1d4ed8;
            --secondary-color: #64748b;
            --success-color: #059669;
            --warning-color: #d97706;
            --danger-color: #dc2626;
            --light-bg: #f8fafc;
            --white: #ffffff;
            --gray-100: #f1f5f9;
            --gray-200: #e2e8f0;
            --gray-300: #cbd5e1;
            --gray-600: #475569;
            --gray-800: #1e293b;
            --shadow-sm: 0 1px 2px 0 rgb(0 0 0 / 0.05);
            --shadow-md: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
            --shadow-lg: 0 10px 15px -3px rgb(0 0 0 / 0.1), 0 4px 6px -4px rgb(0 0 0 / 0.1);
            --shadow-xl: 0 20px 25px -5px rgb(0 0 0 / 0.1), 0 8px 10px -6px rgb(0 0 0 / 0.1);
            --border-radius: 8px;
            --border-radius-lg: 12px;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, var(--light-bg) 0%, #e0f2fe 100%);
            min-height: 100vh;
            color: var(--gray-800);
            line-height: 1.6;
        }

        /* Header */
        .header {
            background: var(--white);
            padding: 1rem 0;
            box-shadow: var(--shadow-sm);
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .header-content {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--primary-color);
        }

        .nav-links {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .btn-back {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem 1.5rem;
            background: var(--gray-100);
            color: var(--gray-600);
            text-decoration: none;
            border-radius: var(--border-radius);
            font-weight: 500;
            transition: all 0.2s ease;
        }

        .btn-back:hover {
            background: var(--gray-200);
            color: var(--gray-800);
            transform: translateY(-1px);
        }

        /* Main Container */
        .main-container {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 0 2rem;
        }

        /* Alert Messages */
        .alert {
            padding: 1rem 1.5rem;
            margin-bottom: 2rem;
            border-radius: var(--border-radius);
            display: flex;
            align-items: center;
            gap: 0.75rem;
            animation: slideDown 0.3s ease;
        }

        .alert.success {
            background: #dcfce7;
            border: 1px solid #bbf7d0;
            color: #166534;
        }

        .alert.error {
            background: #fef2f2;
            border: 1px solid #fecaca;
            color: #991b1b;
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Form Section */
        .form-section {
            background: var(--white);
            border-radius: var(--border-radius-lg);
            box-shadow: var(--shadow-lg);
            overflow: hidden;
            margin-bottom: 3rem;
        }

        .form-header {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%);
            color: var(--white);
            padding: 2rem;
            text-align: center;
        }

        .form-header h1 {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.75rem;
        }

        .form-header p {
            opacity: 0.9;
            font-size: 1.1rem;
        }

        .form-body {
            padding: 2.5rem;
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 2rem;
            margin-bottom: 2rem;
        }

        .form-group {
            display: flex;
            flex-direction: column;
        }

        .form-group label {
            font-weight: 600;
            color: var(--gray-800);
            margin-bottom: 0.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .form-group select {
            padding: 0.875rem 1rem;
            border: 2px solid var(--gray-200);
            border-radius: var(--border-radius);
            background: var(--white);
            font-size: 1rem;
            transition: all 0.2s ease;
            appearance: none;
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='m6 8 4 4 4-4'/%3e%3c/svg%3e");
            background-position: right 0.5rem center;
            background-repeat: no-repeat;
            background-size: 1.5em 1.5em;
            padding-right: 2.5rem;
        }

        .form-group select:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgb(37 99 235 / 0.1);
        }

        .form-group select:disabled {
            background-color: var(--gray-100);
            cursor: not-allowed;
        }

        .form-actions {
            display: flex;
            gap: 1rem;
            justify-content: flex-end;
            margin-top: 2rem;
            padding-top: 2rem;
            border-top: 1px solid var(--gray-200);
        }

        /* Buttons */
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.875rem 1.5rem;
            font-weight: 600;
            border-radius: var(--border-radius);
            text-decoration: none;
            border: none;
            cursor: pointer;
            transition: all 0.2s ease;
            font-size: 0.95rem;
        }

        .btn-primary {
            background: var(--primary-color);
            color: var(--white);
        }

        .btn-primary:hover {
            background: var(--primary-dark);
            transform: translateY(-1px);
            box-shadow: var(--shadow-md);
        }

        .btn-secondary {
            background: var(--gray-100);
            color: var(--gray-600);
        }

        .btn-secondary:hover {
            background: var(--gray-200);
            color: var(--gray-800);
        }

        .btn-success {
            background: var(--success-color);
            color: var(--white);
        }

        .btn-success:hover {
            background: #047857;
            transform: translateY(-1px);
        }

        .btn-danger {
            background: var(--danger-color);
            color: var(--white);
        }

        .btn-danger:hover {
            background: #b91c1c;
            transform: translateY(-1px);
        }

        .btn-sm {
            padding: 0.5rem 1rem;
            font-size: 0.875rem;
        }

        /* Table Section */
        .table-section {
            background: var(--white);
            border-radius: var(--border-radius-lg);
            box-shadow: var(--shadow-lg);
            overflow: hidden;
        }

        .table-header {
            background: var(--gray-800);
            color: var(--white);
            padding: 1.5rem 2rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .table-header h2 {
            font-size: 1.5rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .schedule-stats {
            display: flex;
            align-items: center;
            gap: 1rem;
            font-size: 0.9rem;
            opacity: 0.9;
        }

        .table-container {
            overflow-x: auto;
        }

        .schedule-table {
            width: 100%;
            border-collapse: collapse;
        }

        .schedule-table th {
            background: var(--gray-100);
            color: var(--gray-800);
            padding: 1rem 1.5rem;
            text-align: left;
            font-weight: 600;
            font-size: 0.875rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            border-bottom: 2px solid var(--gray-200);
        }

        .schedule-table td {
            padding: 1rem 1.5rem;
            border-bottom: 1px solid var(--gray-200);
            vertical-align: middle;
        }

        .schedule-table tbody tr {
            transition: all 0.2s ease;
        }

        .schedule-table tbody tr:hover {
            background: var(--gray-100);
        }

        .day-badge {
            display: inline-block;
            padding: 0.25rem 0.75rem;
            background: var(--primary-color);
            color: var(--white);
            border-radius: 999px;
            font-size: 0.75rem;
            font-weight: 600;
        }

        .time-badge {
            display: inline-block;
            padding: 0.25rem 0.75rem;
            background: var(--warning-color);
            color: var(--white);
            border-radius: var(--border-radius);
            font-size: 0.75rem;
            font-weight: 600;
        }

        .course-info {
            font-weight: 600;
            color: var(--gray-800);
        }

        .professor-info {
            color: var(--gray-600);
            font-size: 0.9rem;
        }

        .classroom-info {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.25rem 0.75rem;
            background: var(--gray-100);
            border-radius: var(--border-radius);
            font-size: 0.875rem;
            font-weight: 500;
        }

        .action-buttons {
            display: flex;
            gap: 0.5rem;
            align-items: center;
        }

        .empty-state {
            text-align: center;
            padding: 4rem 2rem;
            color: var(--gray-600);
        }

        .empty-state i {
            font-size: 4rem;
            margin-bottom: 1rem;
            opacity: 0.5;
        }

        .empty-state h3 {
            font-size: 1.5rem;
            margin-bottom: 0.5rem;
            color: var(--gray-800);
        }

        /* Loading State */
        .loading {
            position: relative;
            opacity: 0.6;
            pointer-events: none;
        }

        .loading::after {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 20px;
            height: 20px;
            margin: -10px 0 0 -10px;
            border: 2px solid var(--primary-color);
            border-radius: 50%;
            border-top-color: transparent;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .main-container {
                padding: 0 1rem;
            }

            .form-grid {
                grid-template-columns: 1fr;
                gap: 1.5rem;
            }

            .form-actions {
                flex-direction: column;
            }

            .schedule-table {
                font-size: 0.875rem;
            }

            .schedule-table th,
            .schedule-table td {
                padding: 0.75rem 1rem;
            }

            .action-buttons {
                flex-direction: column;
                gap: 0.25rem;
            }

            .btn-sm {
                padding: 0.375rem 0.75rem;
                font-size: 0.8rem;
            }
        }

        /* Custom Scrollbar */
        .table-container::-webkit-scrollbar {
            height: 8px;
        }

        .table-container::-webkit-scrollbar-track {
            background: var(--gray-100);
        }

        .table-container::-webkit-scrollbar-thumb {
            background: var(--gray-300);
            border-radius: 4px;
        }

        .table-container::-webkit-scrollbar-thumb:hover {
            background: var(--gray-600);
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="header-content">
            <div class="logo">
                <i class="fas fa-calendar-alt"></i>
                Manual Scheduler
            </div>
            <nav class="nav-links">
                <a href="index.php" class="btn-back">
                    <i class="fas fa-arrow-left"></i>
                    Back to Dashboard
                </a>
            </nav>
        </div>
    </header>

    <!-- Main Container -->
    <div class="main-container">
        <!-- Alert Messages -->
        <?php if (isset($_SESSION['flash_message'])): ?>
            <div class="alert <?= $_SESSION['flash_type'] ?? 'success' ?>">
                <i class="fas fa-<?= ($_SESSION['flash_type'] ?? 'success') === 'success' ? 'check-circle' : 'exclamation-triangle' ?>"></i>
                <?= $_SESSION['flash_message'] ?>
            </div>
            <?php 
            unset($_SESSION['flash_message']); 
            unset($_SESSION['flash_type']);
            ?>
        <?php endif; ?>

        <!-- Form Section -->
        <div class="form-section">
            <div class="form-header">
                <h1>
                    <i class="fas fa-<?= $edit_data ? 'edit' : 'plus-circle' ?>"></i>
                    <?= $edit_data ? 'Edit Schedule' : 'Create New Schedule' ?>
                </h1>
                <p><?= $edit_data ? 'Modify the existing schedule entry' : 'Add a new manual schedule entry to the system' ?></p>
            </div>

            <div class="form-body">
                <form method="POST" id="scheduleForm">
                    <?php if ($edit_data): ?>
                        <input type="hidden" name="update_schedule_id" value="<?= $edit_data['id'] ?>">
                    <?php endif; ?>

                    <div class="form-grid">
                        <div class="form-group">
                            <label for="building_id">
                                <i class="fas fa-building"></i>
                                Building
                            </label>
                            <select name="building_id" id="building_id" required>
                                <option value="">Select Building</option>
                                <?php 
                                $buildings->data_seek(0);
                                while ($b = $buildings->fetch_assoc()): 
                                ?>
                                    <option value="<?= $b['id'] ?>"><?= htmlspecialchars($b['building_name']) ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="semester_id">
                                <i class="fas fa-calendar-week"></i>
                                Semester
                            </label>
                            <select name="semester_id" id="semester_id" required>
                                <option value="">Select Semester</option>
                                <?php 
                                $semester->data_seek(0);
                                while ($s = $semester->fetch_assoc()): 
                                ?>
                                    <option value="<?= $s['id'] ?>"><?= htmlspecialchars($s['name']) ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="course_id">
                                <i class="fas fa-book"></i>
                                Course
                            </label>
                            <select name="course_id" id="course_id" required>
                                <option value="">Select Course</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="classroom_id">
                                <i class="fas fa-door-open"></i>
                                Classroom
                            </label>
                            <select name="classroom_id" id="classroom_id" required>
                                <option value="">Select Classroom</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="day">
                                <i class="fas fa-calendar-day"></i>
                                Day
                            </label>
                            <select name="day" id="day" required>
                                <?php foreach ($days as $d): ?>
                                    <option value="<?= $d ?>" <?= ($edit_data && $edit_data['day'] == $d) ? 'selected' : '' ?>><?= $d ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="time_slot">
                                <i class="fas fa-clock"></i>
                                Time Slot
                            </label>
                            <select name="time_slot" id="time_slot" required>
                                <?php foreach ($time_slots as $slot): ?>
                                    <option value="<?= $slot ?>" <?= ($edit_data && $edit_data['time_slot'] == $slot) ? 'selected' : '' ?>><?= $slot ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="form-actions">
                        <?php if ($edit_data): ?>
                            <a href="manual_schedule.php" class="btn btn-secondary">
                                <i class="fas fa-times"></i>
                                Cancel
                            </a>
                        <?php endif; ?>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-<?= $edit_data ? 'save' : 'plus' ?>"></i>
                            <?= $edit_data ? 'Update Schedule' : 'Create Schedule' ?>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Table Section -->
        <div class="table-section">
            <div class="table-header">
                <h2>
                    <i class="fas fa-list"></i>
                    Schedule Overview
                </h2>
                <div class="schedule-stats">
                    <span><i class="fas fa-calendar-check"></i> <?= $schedules->num_rows ?> Total Schedules</span>
                </div>
            </div>

            <div class="table-container">
                <?php if ($schedules->num_rows > 0): ?>
                    <table class="schedule-table">
                        <thead>
                            <tr>
                                <th><i class="fas fa-calendar-day"></i> Day</th>
                                <th><i class="fas fa-clock"></i> Time</th>
                                <th><i class="fas fa-book"></i> Course</th>
                                <th><i class="fas fa-user-tie"></i> Professor</th>
                                <th><i class="fas fa-door-open"></i> Classroom</th>
                                <th><i class="fas fa-cog"></i> Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = $schedules->fetch_assoc()): ?>
                                <tr>
                                    <td>
                                        <span class="day-badge"><?= $row['day'] ?></span>
                                    </td>
                                    <td>
                                        <span class="time-badge"><?= $row['time_slot'] ?></span>
                                    </td>
                                    <td>
                                        <div class="course-info"><?= htmlspecialchars($row['course_name']) ?></div>
                                    </td>
                                    <td>
                                        <div class="professor-info"><?= htmlspecialchars($row['professor_name']) ?></div>
                                    </td>
                                    <td>
                                        <span class="classroom-info">
                                            <i class="fas fa-map-marker-alt"></i>
                                            <?= htmlspecialchars($row['classroom_name']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="action-buttons">
                                            <a href="manual_schedule.php?edit_id=<?= $row['id'] ?>&building_id=<?= $row['building_id'] ?>&semester_id=<?= $row['semester_id'] ?>" 
                                               class="btn btn-success btn-sm">
                                                <i class="fas fa-edit"></i>
                                                Edit
                                            </a>
                                            <form method="POST" style="display: inline;" 
                                                  onsubmit="return confirm('Are you sure you want to delete this schedule?')">
                                                <input type="hidden" name="delete_schedule_id" value="<?= $row['id'] ?>">
                                                <button type="submit" class="btn btn-danger btn-sm">
                                                    
                                                    <i class="fas fa-trash"></i>
                                                    Delete
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <div class="empty-state">
                        <i class="fas fa-calendar-times"></i>
                        <h3>No Schedules Found</h3>
                        <p>Start by creating your first manual schedule entry above.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="stats-section">
            <div class="stats-grid">
                <div class="stat-card primary">
                    <div class="stat-icon">
                        <i class="fas fa-calendar-check"></i>
                    </div>
                    <div class="stat-content">
                        <h3><?= $schedules->num_rows ?></h3>
                        <p>Total Schedules</p>
                    </div>
                </div>
                
                <?php
                // Get additional statistics
                $days_count = $con->query("SELECT COUNT(DISTINCT day) as count FROM schedule WHERE is_manual = 1")->fetch_assoc()['count'];
                $courses_count = $con->query("SELECT COUNT(DISTINCT course_id) as count FROM schedule WHERE is_manual = 1")->fetch_assoc()['count'];
                $classrooms_count = $con->query("SELECT COUNT(DISTINCT classroom_id) as count FROM schedule WHERE is_manual = 1")->fetch_assoc()['count'];
                ?>
                
                <div class="stat-card success">
                    <div class="stat-icon">
                        <i class="fas fa-calendar-day"></i>
                    </div>
                    <div class="stat-content">
                        <h3><?= $days_count ?></h3>
                        <p>Active Days</p>
                    </div>
                </div>
                
                <div class="stat-card warning">
                    <div class="stat-icon">
                        <i class="fas fa-book"></i>
                    </div>
                    <div class="stat-content">
                        <h3><?= $courses_count ?></h3>
                        <p>Courses Scheduled</p>
                    </div>
                </div>
                
                <div class="stat-card info">
                    <div class="stat-icon">
                        <i class="fas fa-door-open"></i>
                    </div>
                    <div class="stat-content">
                        <h3><?= $classrooms_count ?></h3>
                        <p>Classrooms Used</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions Panel -->
        <div class="quick-actions">
            <div class="actions-header">
                <h3><i class="fas fa-bolt"></i> Quick Actions</h3>
            </div>
            <div class="actions-grid">
                <a href="view_timetable.php" class="action-item">
                    <i class="fas fa-eye"></i>
                    <span>View All Schedules</span>
                </a>
                <a href="generate_timetable.php" class="action-item">
                    <i class="fas fa-magic"></i>
                    <span>Auto Generate</span>
                </a>
                <!--<a href="export_schedule.php" class="action-item">
                    <i class="fas fa-download"></i>
                    <span>Export Schedule</span>
                </a>-->
                 <a class="action-item" onclick="window.print()">
                    <i class="fas fa-download"></i>
                    <span>Export Schedule</spam>
                                
                </a>
            </div>
        </div>
    </div>

    <!-- Toast Notification -->
    <div id="toast" class="toast"></div>

    <!-- Loading Overlay -->
    <div id="loadingOverlay" class="loading-overlay">
        <div class="spinner"></div>
        <p>Processing...</p>
    </div>

    <script>
        // DOM Elements
        const buildingSelect = document.getElementById('building_id');
        const semesterSelect = document.getElementById('semester_id');
        const courseSelect = document.getElementById('course_id');
        const classroomSelect = document.getElementById('classroom_id');
        const scheduleForm = document.getElementById('scheduleForm');
        const loadingOverlay = document.getElementById('loadingOverlay');

        // Initialize edit data if exists
        <?php if ($edit_data): ?>
        const editData = {
            building_id: '<?= $edit_data['building_id'] ?? '' ?>',
            semester_id: '<?= $edit_data['semester_id'] ?? '' ?>',
            course_id: '<?= $edit_data['course_id'] ?? '' ?>',
            classroom_id: '<?= $edit_data['classroom_id'] ?? '' ?>'
        };
        
        // Pre-select edit values
        setTimeout(() => {
            if (editData.building_id) buildingSelect.value = editData.building_id;
            if (editData.semester_id) semesterSelect.value = editData.semester_id;
            
            if (editData.building_id && editData.semester_id) {
                fetchCoursesAndClassrooms(editData.building_id, editData.semester_id, () => {
                    if (editData.course_id) courseSelect.value = editData.course_id;
                    if (editData.classroom_id) classroomSelect.value = editData.classroom_id;
                });
            }
        }, 100);
        <?php endif; ?>

        // Event Listeners
        buildingSelect.addEventListener('change', handleBuildingSemesterChange);
        semesterSelect.addEventListener('change', handleBuildingSemesterChange);
        scheduleForm.addEventListener('submit', handleFormSubmit);

        // Show loading overlay
        function showLoading() {
            loadingOverlay.style.display = 'flex';
        }

        // Hide loading overlay
        function hideLoading() {
            loadingOverlay.style.display = 'none';
        }

        // Show toast notification
        function showToast(message, type = 'success') {
            const toast = document.getElementById('toast');
            toast.textContent = message;
            toast.className = `toast toast-${type} show`;
            
            setTimeout(() => {
                toast.classList.remove('show');
            }, 3000);
        }

        // Handle building/semester change
        function handleBuildingSemesterChange() {
            const buildingId = buildingSelect.value;
            const semesterId = semesterSelect.value;

            // Reset dependent dropdowns
            courseSelect.innerHTML = '<option value="">Select Course</option>';
            classroomSelect.innerHTML = '<option value="">Select Classroom</option>';
            
            courseSelect.disabled = true;
            classroomSelect.disabled = true;

            if (buildingId && semesterId) {
                fetchCoursesAndClassrooms(buildingId, semesterId);
            }
        }

        // Fetch courses and classrooms via AJAX
        function fetchCoursesAndClassrooms(buildingId, semesterId, callback = null) {
            showLoading();
            
            fetch(`?action=fetch&building_id=${buildingId}&semester_id=${semesterId}`)
                .then(response => response.json())
                .then(data => {
                    // Populate courses
                    courseSelect.innerHTML = '<option value="">Select Course</option>';
                    data.courses.forEach(course => {
                        const option = document.createElement('option');
                        option.value = course.id;
                        option.textContent = course.name;
                        courseSelect.appendChild(option);
                    });

                    // Populate classrooms
                    classroomSelect.innerHTML = '<option value="">Select Classroom</option>';
                    data.classrooms.forEach(classroom => {
                        const option = document.createElement('option');
                        option.value = classroom.id;
                        option.textContent = classroom.name;
                        classroomSelect.appendChild(option);
                    });

                    // Enable dropdowns
                    courseSelect.disabled = false;
                    classroomSelect.disabled = false;

                    if (callback) callback();
                })
                .catch(error => {
                    console.error('Error:', error);
                    showToast('Error loading data. Please try again.', 'error');
                })
                .finally(() => {
                    hideLoading();
                });
        }

        // Handle form submission
        function handleFormSubmit(e) {
            e.preventDefault();
            showLoading();
            
            // Add small delay to show loading effect
            setTimeout(() => {
                e.target.submit();
            }, 500);
        }

        // Add smooth scrolling to form on edit
        <?php if ($edit_data): ?>
        document.querySelector('.form-section').scrollIntoView({
            behavior: 'smooth',
            block: 'start'
        });
        <?php endif; ?>

        // Auto-hide alerts after 5 seconds
        const alerts = document.querySelectorAll('.alert');
        alerts.forEach(alert => {
            setTimeout(() => {
                alert.style.animation = 'slideUp 0.3s ease forwards';
                setTimeout(() => alert.remove(), 300);
            }, 5000);
        });

        // Add keyboard shortcuts
        document.addEventListener('keydown', (e) => {
            // Ctrl/Cmd + S to submit form
            if ((e.ctrlKey || e.metaKey) && e.key === 's') {
                e.preventDefault();
                scheduleForm.dispatchEvent(new Event('submit'));
            }
            
            // Escape to clear form or cancel edit
            if (e.key === 'Escape') {
                <?php if ($edit_data): ?>
                window.location.href = 'manual_schedule.php';
                <?php else: ?>
                scheduleForm.reset();
                handleBuildingSemesterChange();
                <?php endif; ?>
            }
        });

        // Add table row highlighting
        const tableRows = document.querySelectorAll('.schedule-table tbody tr');
        tableRows.forEach(row => {
            row.addEventListener('mouseenter', () => {
                row.style.transform = 'scale(1.02)';
                row.style.boxShadow = '0 4px 12px rgba(0,0,0,0.1)';
            });
            
            row.addEventListener('mouseleave', () => {
                row.style.transform = 'scale(1)';
                row.style.boxShadow = 'none';
            });
        });
    </script>

    <style>
        /* Additional Professional Styles */
        
        /* Statistics Section */
        .stats-section {
            margin: 3rem 0;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
        }

        .stat-card {
            background: var(--white);
            border-radius: var(--border-radius-lg);
            padding: 2rem;
            box-shadow: var(--shadow-lg);
            display: flex;
            align-items: center;
            gap: 1.5rem;
            transition: all 0.3s ease;
            border-left: 4px solid transparent;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-xl);
        }

        .stat-card.primary { border-left-color: var(--primary-color); }
        .stat-card.success { border-left-color: var(--success-color); }
        .stat-card.warning { border-left-color: var(--warning-color); }
        .stat-card.info { border-left-color: #0ea5e9; }

        .stat-icon {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            color: var(--white);
        }

        .stat-card.primary .stat-icon { background: var(--primary-color); }
        .stat-card.success .stat-icon { background: var(--success-color); }
        .stat-card.warning .stat-icon { background: var(--warning-color); }
        .stat-card.info .stat-icon { background: #0ea5e9; }

        .stat-content h3 {
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--gray-800);
            margin: 0;
            line-height: 1;
        }

        .stat-content p {
            color: var(--gray-600);
            margin: 0.5rem 0 0 0;
            font-weight: 500;
        }

        /* Quick Actions */
        .quick-actions {
            background: var(--white);
            border-radius: var(--border-radius-lg);
            box-shadow: var(--shadow-lg);
            overflow: hidden;
            margin-top: 3rem;
        }

        .actions-header {
            background: linear-gradient(135deg, var(--gray-800) 0%, var(--gray-600) 100%);
            color: var(--white);
            padding: 1.5rem 2rem;
        }

        .actions-header h3 {
            margin: 0;
            font-size: 1.25rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .actions-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 0;
        }

        .action-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 0.75rem;
            padding: 2rem 1.5rem;
            text-decoration: none;
            color: var(--gray-800);
            transition: all 0.3s ease;
            border-right: 1px solid var(--gray-200);
            border-bottom: 1px solid var(--gray-200);
        }

        .action-item:hover {
            background: var(--primary-color);
            color: var(--white);
            transform: translateY(-2px);
        }

        .action-item i {
            font-size: 2rem;
            opacity: 0.8;
        }

        .action-item span {
            font-weight: 600;
            font-size: 0.95rem;
        }

        /* Toast Notifications */
        .toast {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 1rem 1.5rem;
            border-radius: var(--border-radius);
            color: var(--white);
            font-weight: 600;
            transform: translateX(400px);
            transition: transform 0.3s ease;
            z-index: 1000;
            box-shadow: var(--shadow-lg);
        }

        .toast.show {
            transform: translateX(0);
        }

        .toast.toast-success { background: var(--success-color); }
        .toast.toast-error { background: var(--danger-color); }
        .toast.toast-warning { background: var(--warning-color); }

        /* Loading Overlay */
        .loading-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.7);
            display: none;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            z-index: 2000;
            color: var(--white);
        }

        .spinner {
            width: 50px;
            height: 50px;
            border: 4px solid rgba(255, 255, 255, 0.3);
            border-top: 4px solid var(--white);
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin-bottom: 1rem;
        }

        /* Enhanced Animations */
        @keyframes slideUp {
            from {
                opacity: 1;
                transform: translateY(0);
            }
            to {
                opacity: 0;
                transform: translateY(-20px);
            }
        }

        /* Enhanced Table Styles */
        .schedule-table tbody tr {
            transition: all 0.2s ease;
        }

        .schedule-table tbody tr:nth-child(even) {
            background: rgba(248, 250, 252, 0.5);
        }

        /* Form Enhancements */
        .form-group select:focus {
            transform: translateY(-1px);
        }

        .btn {
            position: relative;
            overflow: hidden;
        }

        .btn::after {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 0;
            height: 0;
            background: rgba(255, 255, 255, 0.3);
            border-radius: 50%;
            transform: translate(-50%, -50%);
            transition: width 0.3s, height 0.3s;
        }

        .btn:active::after {
            width: 300px;
            height: 300px;
        }

        /* Mobile Enhancements */
        @media (max-width: 768px) {
            .stats-grid {
                grid-template-columns: 1fr;
            }
            
            .actions-grid {
                grid-template-columns: repeat(2, 1fr);
            }
            
            .action-item {
                border-right: none;
                padding: 1.5rem 1rem;
            }
            
            .stat-card {
                padding: 1.5rem;
                gap: 1rem;
            }
            
            .stat-icon {
                width: 50px;
                height: 50px;
                font-size: 1.25rem;
            }
            
            .stat-content h3 {
                font-size: 2rem;
            }
        }

        /* Print Styles */
        @media print {
            .header, .form-section, .stats-section, .quick-actions {
                display: none;
            }
            
            .table-section {
                box-shadow: none;
                border: 1px solid #000;
            }
            
            .schedule-table th,
            .schedule-table td {
                border: 1px solid #000;
                padding: 0.5rem;
            }
        }

        /* Dark Mode Support */
        /*@media (prefers-color-scheme: dark) {
            :root {
                --white: #1f2937;
                --light-bg: #111827;
                --gray-100: #374151;
                --gray-200: #4b5563;
                --gray-300: #6b7280;
                --gray-600: #d1d5db;
                --gray-800: #f9fafb;
            }
            
            body {
                background: var(--light-bg);
                color: var(--gray-800);
            }
        }*/
    </style>

</body>
</html>