<?php
session_start();
require_once('config.php');

// Security headers
header("X-Frame-Options: DENY");
header("X-Content-Type-Options: nosniff");
header("X-XSS-Protection: 1; mode=block");

// Check authentication
if (!isset($_SESSION['professor_id'])) {
    $_SESSION['redirect_url'] = $_SERVER['REQUEST_URI'];
    $_SESSION['flash_message'] = "Please log in to access this page.";
    header("Location: login.php");
    exit;
}

$professor_id = $_SESSION['professor_id'];

// Handle AJAX requests first
if (isset($_GET['action'])) {
    header('Content-Type: application/json');
    
    try {
        if ($_GET['action'] == 'fetch_courses') {
            // Validate inputs
            if (!isset($_GET['building_id']) || !isset($_GET['semester_id'])) {
                throw new Exception("Missing required parameters");
            }
            
            $building_id = intval($_GET['building_id']);
            $semester_id = intval($_GET['semester_id']);
            
            // Use prepared statement
            $stmt = $con->prepare("
                SELECT c.id, c.name, s.name AS semester_name 
                FROM courses c 
                JOIN semester s ON c.semester_id = s.id
                WHERE c.building_id = ? 
                AND c.semester_id = ?
                AND c.professor_id = ?
            ");
            $stmt->bind_param("iii", $building_id, $semester_id, $professor_id);
            $stmt->execute();
            $result = $stmt->get_result();
            
            $courses = [];
            while ($row = $result->fetch_assoc()) {
                $courses[] = $row;
            }
            
            echo json_encode([
                'success' => true,
                'data' => $courses
            ]);
            exit;
        }
        elseif ($_GET['action'] == 'fetch_classrooms') {
            // Validate inputs
            if (!isset($_GET['building_id'])) {
                throw new Exception("Missing building parameter");
            }
            
            $building_id = intval($_GET['building_id']);
            $has_projector = isset($_GET['has_projector']) ? intval($_GET['has_projector']) : null;
            
            // Use prepared statement
            $query = "SELECT id, name, has_projector FROM classrooms WHERE building_id = ?";
            $params = [$building_id];
            $types = "i";
            
            if ($has_projector !== null) {
                $query .= " AND has_projector = ?";
                $params[] = $has_projector;
                $types .= "i";
            }
            
            $stmt = $con->prepare($query);
            $stmt->bind_param($types, ...$params);
            $stmt->execute();
            $result = $stmt->get_result();
            
            $classrooms = [];
            while ($row = $result->fetch_assoc()) {
                $classrooms[] = $row;
            }
            
            echo json_encode([
                'success' => true,
                'data' => $classrooms
            ]);
            exit;
        }
    } catch (Exception $e) {
        error_log("AJAX Error: " . $e->getMessage());
        echo json_encode([
            'success' => false,
            'error' => 'An error occurred while processing your request'
        ]);
        exit;
    }
}

// Fetch buildings where professor teaches
$buildings = [];
$stmt = $con->prepare("
    SELECT b.id, b.building_name 
    FROM buildings b
    JOIN professors p ON p.building_id = b.id
    WHERE p.id = ?
");
$stmt->bind_param("i", $professor_id);
$stmt->execute();
$buildings_result = $stmt->get_result();
while ($row = $buildings_result->fetch_assoc()) {
    $buildings[] = $row;
}

// Fetch all semesters
$semesters = [];
$semester_result = $con->query("SELECT id, name FROM semester ORDER BY name");
while ($row = $semester_result->fetch_assoc()) {
    $semesters[] = $row;
}

// Constants
define('DAYS', ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday']);
define('TIME_SLOTS', [
    '9:00-10:00', '10:00-11:00', '11:00-12:00', 
    '12:00-1:00', '2:00-3:00', '3:00-4:00', '4:00-5:00'
]);

// Handle edit mode
$edit_data = null;
if (isset($_GET['edit_id'])) {
    $edit_id = intval($_GET['edit_id']);
    
    $stmt = $con->prepare("
        SELECT ds.*, c.building_id, c.semester_id, cl.has_projector 
        FROM department_schedule ds
        JOIN courses c ON ds.course_id = c.id
        JOIN classrooms cl ON ds.classroom_id = cl.id
        WHERE ds.id = ? AND ds.is_manual = 1 AND c.professor_id = ?
    ");
    $stmt->bind_param("ii", $edit_id, $professor_id);
    $stmt->execute();
    $edit_result = $stmt->get_result();
    
    if ($edit_result->num_rows > 0) {
        $edit_data = $edit_result->fetch_assoc();
    } else {
        $_SESSION['flash_message'] = "Schedule not found or you don't have permission to edit it.";
        header("Location: manual_department_schedule.php");
        exit;
    }
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        // Validate CSRF token
        if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
            throw new Exception("Invalid CSRF token");
        }
        
        // Validate inputs
        $required_fields = ['course_id', 'day', 'time_slot', 'classroom_id'];
        foreach ($required_fields as $field) {
            if (empty($_POST[$field])) {
                throw new Exception("Missing required field: $field");
            }
        }
        
        $course_id = intval($_POST['course_id']);
        $day = htmlspecialchars($_POST['day']);
        $time_slot = htmlspecialchars($_POST['time_slot']);
        $classroom_id = intval($_POST['classroom_id']);
        
        // Verify course belongs to professor
        $stmt = $con->prepare("SELECT semester_id FROM courses WHERE id = ? AND professor_id = ?");
        $stmt->bind_param("ii", $course_id, $professor_id);
        $stmt->execute();
        $course_result = $stmt->get_result();
        
        if ($course_result->num_rows === 0) {
            throw new Exception("Invalid course selection");
        }
        
        $course = $course_result->fetch_assoc();
        $semester_id = $course['semester_id'];
        
        // Begin transaction
        $con->begin_transaction();
        
        try {
            if (isset($_POST['update_schedule_id'])) {
                $update_id = intval($_POST['update_schedule_id']);
                
                // Delete conflicting schedules
                $con->query("
                    DELETE FROM department_schedule 
                    WHERE (
                        classroom_id = $classroom_id OR 
                        (course_id IN (SELECT id FROM courses WHERE professor_id = $professor_id) OR 
                        semester_id = $semester_id
                    ) 
                    AND day = '$day' AND time_slot = '$time_slot'
                    AND id != $update_id
                ");
                
                // Update schedule
                $con->query("
                    UPDATE department_schedule SET 
                    course_id = $course_id,
                    classroom_id = $classroom_id,
                    day = '$day',
                    time_slot = '$time_slot',
                    semester_id = $semester_id
                    WHERE id = $update_id
                ");
                
                $message = "Schedule updated successfully!";
            } else {
                // Delete conflicting schedules
                $con->query("
                    DELETE FROM department_schedule 
                    WHERE (
                        classroom_id = $classroom_id OR 
                        (course_id IN (SELECT id FROM courses WHERE professor_id = $professor_id) OR 
                        semester_id = $semester_id
                    ) 
                    AND day = '$day' AND time_slot = '$time_slot'
                ");
                
                // Insert new schedule
                $con->query("
                    INSERT INTO department_schedule 
                    (course_id, classroom_id, day, time_slot, semester_id, is_manual)
                    VALUES ($course_id, $classroom_id, '$day', '$time_slot', $semester_id, 1)
                ");
                
                $message = "Schedule added successfully!";
            }
            
            $con->commit();
            $_SESSION['flash_message'] = $message;
            $_SESSION['flash_type'] = "success";
            header("Location: manual_department_schedule.php");
            exit;
        } catch (Exception $e) {
            $con->rollback();
            throw $e;
        }
    } catch (Exception $e) {
        error_log("Form Error: " . $e->getMessage());
        $_SESSION['flash_message'] = "Error: " . $e->getMessage();
        $_SESSION['flash_type'] = "error";
        header("Location: manual_department_schedule.php");
        exit;
    }
}

// Handle delete
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_schedule_id'])) {
    try {
        // Validate CSRF token
        if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
            throw new Exception("Invalid CSRF token");
        }
        
        $delete_id = intval($_POST['delete_schedule_id']);
        
        // Verify the schedule belongs to this professor
        $stmt = $con->prepare("
            DELETE ds FROM department_schedule ds
            JOIN courses c ON ds.course_id = c.id
            WHERE ds.id = ? AND ds.is_manual = 1 AND c.professor_id = ?
        ");
        $stmt->bind_param("ii", $delete_id, $professor_id);
        $stmt->execute();
        
        if ($stmt->affected_rows > 0) {
            $_SESSION['flash_message'] = "Schedule deleted successfully.";
            $_SESSION['flash_type'] = "success";
        } else {
            $_SESSION['flash_message'] = "Schedule not found or you don't have permission to delete it.";
            $_SESSION['flash_type'] = "error";
        }
        
        header("Location: manual_department_schedule.php");
        exit;
    } catch (Exception $e) {
        error_log("Delete Error: " . $e->getMessage());
        $_SESSION['flash_message'] = "Error deleting schedule.";
        $_SESSION['flash_type'] = "error";
        header("Location: manual_department_schedule.php");
        exit;
    }
}

// Fetch all manual schedules for this professor
$schedules = [];
$stmt = $con->prepare("
    SELECT sc.id, sc.day, sc.time_slot, sc.classroom_id, cr.name AS course_name, 
           cr.semester_id, cr.building_id, cl.name AS classroom_name, 
           s.name AS semester_name, p.name AS professor_name
    FROM department_schedule sc
    JOIN courses cr ON sc.course_id = cr.id
    JOIN classrooms cl ON sc.classroom_id = cl.id
    JOIN semester s ON sc.semester_id = s.id
    JOIN professors p ON cr.professor_id = p.id
    WHERE sc.is_manual = 1 AND cr.professor_id = ?
    ORDER BY 
        FIELD(sc.day, 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'),
        sc.time_slot
");
$stmt->bind_param("i", $professor_id);
$stmt->execute();
$schedules_result = $stmt->get_result();
while ($row = $schedules_result->fetch_assoc()) {
    $schedules[] = $row;
}

// Generate CSRF token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manual Schedule | Professor Portal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
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
    </style>
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="header-content">
            <div class="logo">
                <i class="fas fa-calendar-alt"></i>
                Department Scheduler
            </div>
            <nav class="nav-links">
                <a href="professor_dashboard.php" class="btn-back">
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
                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                    
                    <?php if ($edit_data): ?>
                        <input type="hidden"
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
                                <?php foreach ($buildings as $b): ?>
                                    <option value="<?= $b['id'] ?>" 
                                        <?= ($edit_data && $edit_data['building_id'] == $b['id']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($b['building_name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="semester_id">
                                <i class="fas fa-calendar-week"></i>
                                Semester
                            </label>
                            <select name="semester_id" id="semester_id" required>
                                <option value="">Select Semester</option>
                                <?php foreach ($semesters as $s): ?>
                                    <option value="<?= $s['id'] ?>" 
                                        <?= ($edit_data && $edit_data['semester_id'] == $s['id']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($s['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="course_id">
                                <i class="fas fa-book"></i>
                                Course
                            </label>
                            <select name="course_id" id="course_id" required>
                                <option value="">Select Building and Semester first</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="has_projector">
                                <i class="fas fa-projector"></i>
                                Projector Requirement
                            </label>
                            <select name="has_projector" id="has_projector">
                                <option value="">Any</option>
                                <option value="1" <?= ($edit_data && $edit_data['has_projector'] == 1) ? 'selected' : '' ?>>Yes</option>
                                <option value="0" <?= ($edit_data && $edit_data['has_projector'] == 0) ? 'selected' : '' ?>>No</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="classroom_id">
                                <i class="fas fa-door-open"></i>
                                Classroom
                            </label>
                            <select name="classroom_id" id="classroom_id" required>
                                <option value="">Select Building and Projector requirement first</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="day">
                                <i class="fas fa-calendar-day"></i>
                                Day
                            </label>
                            <select name="day" id="day" required>
                                <?php foreach (DAYS as $d): ?>
                                    <option value="<?= $d ?>" 
                                        <?= ($edit_data && $edit_data['day'] == $d) ? 'selected' : '' ?>>
                                        <?= $d ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="time_slot">
                                <i class="fas fa-clock"></i>
                                Time Slot
                            </label>
                            <select name="time_slot" id="time_slot" required>
                                <?php foreach (TIME_SLOTS as $slot): ?>
                                    <option value="<?= $slot ?>" 
                                        <?= ($edit_data && $edit_data['time_slot'] == $slot) ? 'selected' : '' ?>>
                                        <?= $slot ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="form-actions">
                        <?php if ($edit_data): ?>
                            <a href="manual_department_schedule.php" class="btn btn-secondary">
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
                    Your Scheduled Courses
                </h2>
                <div class="schedule-stats">
                    <span><i class="fas fa-calendar-check"></i> <?= count($schedules) ?> Total Schedules</span>
                </div>
            </div>

            <div class="table-container">
                <?php if (!empty($schedules)): ?>
                    <table class="schedule-table">
                        <thead>
                            <tr>
                                <th><i class="fas fa-calendar-day"></i> Day</th>
                                <th><i class="fas fa-clock"></i> Time</th>
                                <th><i class="fas fa-book"></i> Course</th>
                                <th><i class="fas fa-door-open"></i> Classroom</th>
                                <th><i class="fas fa-cog"></i> Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($schedules as $row): ?>
                                <tr>
                                    <td>
                                        <span class="day-badge"><?= $row['day'] ?></span>
                                    </td>
                                    <td>
                                        <span class="time-badge"><?= $row['time_slot'] ?></span>
                                    </td>
                                    <td>
                                        <div class="course-info"><?= htmlspecialchars($row['course_name']) ?></div>
                                        <div class="professor-info"><?= htmlspecialchars($row['semester_name']) ?></div>
                                    </td>
                                    <td>
                                        <span class="classroom-info">
                                            <i class="fas fa-map-marker-alt"></i>
                                            <?= htmlspecialchars($row['classroom_name']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="action-buttons">
                                            <a href="manual_department_schedule.php?edit_id=<?= $row['id'] ?>" 
                                               class="btn btn-success btn-sm">
                                                <i class="fas fa-edit"></i>
                                                Edit
                                            </a>
                                            <form method="POST" style="display: inline;" 
                                                  onsubmit="return confirm('Are you sure you want to delete this schedule?')">
                                                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                                                <input type="hidden" name="delete_schedule_id" value="<?= $row['id'] ?>">
                                                <button type="submit" class="btn btn-danger btn-sm">
                                                    <i class="fas fa-trash"></i>
                                                    Delete
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
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
                        <h3><?= count($schedules) ?></h3>
                        <p>Total Schedules</p>
                    </div>
                </div>
                
                <?php
                // Get additional statistics
                $days_count = count(array_unique(array_column($schedules, 'day')));
                $courses_count = count(array_unique(array_column($schedules, 'course_id')));
                $classrooms_count = count(array_unique(array_column($schedules, 'classroom_id')));
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
                <a class="action-item" onclick="window.print()">
                    <i class="fas fa-download"></i>
                    <span>Export Schedule</span>
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

    <!-- JavaScript Libraries -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize form elements
        const buildingSelect = document.getElementById('building_id');
        const semesterSelect = document.getElementById('semester_id');
        const projectorSelect = document.getElementById('has_projector');
        const courseSelect = document.getElementById('course_id');
        const classroomSelect = document.getElementById('classroom_id');
        const scheduleForm = document.getElementById('scheduleForm');
        const loadingOverlay = document.getElementById('loadingOverlay');
        
        // Initialize Select2 for better UX
        $(buildingSelect).select2();
        $(semesterSelect).select2();
        $(projectorSelect).select2();
        $(courseSelect).select2();
        $(classroomSelect).select2();
        $('#day').select2();
        $('#time_slot').select2();
        
        // Fetch courses based on building and semester
        async function fetchCourses() {
            const buildingId = buildingSelect.value;
            const semesterId = semesterSelect.value;
            
            if (!buildingId || !semesterId) {
                courseSelect.innerHTML = '<option value="">Select Building and Semester first</option>';
                $(courseSelect).val('').trigger('change');
                return;
            }
            
            // Show loading state
            courseSelect.innerHTML = '<option value="">Loading courses...</option>';
            $(courseSelect).val('').trigger('change');
            showLoading();
            
            try {
                const response = await fetch(`manual_department_schedule.php?action=fetch_courses&building_id=${buildingId}&semester_id=${semesterId}`);
                
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                
                const data = await response.json();
                
                if (!data.success || !data.data) {
                    throw new Error('Invalid data received');
                }
                
                // Populate courses
                courseSelect.innerHTML = '<option value="">Select Course</option>';
                data.data.forEach(course => {
                    const option = document.createElement('option');
                    option.value = course.id;
                    option.textContent = `${course.name} (${course.semester_name})`;
                    courseSelect.appendChild(option);
                });
                
                // If editing, set the selected course
                <?php if ($edit_data): ?>
                if (courseSelect.querySelector(`option[value="<?= $edit_data['course_id'] ?>"]`)) {
                    $(courseSelect).val("<?= $edit_data['course_id'] ?>").trigger('change');
                }
                <?php endif; ?>
                
            } catch (error) {
                console.error('Error fetching courses:', error);
                courseSelect.innerHTML = '<option value="">Error loading courses</option>';
                $(courseSelect).val('').trigger('change');
                
                // Show error toast
                showToast('Error loading courses. Please try again.', 'error');
            } finally {
                hideLoading();
            }
        }
        
        // Fetch classrooms based on building and projector requirement
        async function fetchClassrooms() {
            const buildingId = buildingSelect.value;
            const hasProjector = projectorSelect.value;
            
            if (!buildingId) {
                classroomSelect.innerHTML = '<option value="">Select Building first</option>';
                $(classroomSelect).val('').trigger('change');
                return;
            }
            
            // Show loading state
            classroomSelect.innerHTML = '<option value="">Loading classrooms...</option>';
            $(classroomSelect).val('').trigger('change');
            showLoading();
            
            try {
                const response = await fetch(`manual_department_schedule.php?action=fetch_classrooms&building_id=${buildingId}&has_projector=${hasProjector}`);
                
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                
                const data = await response.json();
                
                if (!data.success || !data.data) {
                    throw new Error('Invalid data received');
                }
                
                // Populate classrooms
                classroomSelect.innerHTML = '<option value="">Select Classroom</option>';
                data.data.forEach(classroom => {
                    const option = document.createElement('option');
                    option.value = classroom.id;
                    option.textContent = `${classroom.name} ${classroom.has_projector ? '(Projector)' : ''}`;
                    classroomSelect.appendChild(option);
                });
                
                // If editing, set the selected classroom
                <?php if ($edit_data): ?>
                if (classroomSelect.querySelector(`option[value="<?= $edit_data['classroom_id'] ?>"]`)) {
                    $(classroomSelect).val("<?= $edit_data['classroom_id'] ?>").trigger('change');
                }
                <?php endif; ?>
                
            } catch (error) {
                console.error('Error fetching classrooms:', error);
                classroomSelect.innerHTML = '<option value="">Error loading classrooms</option>';
                $(classroomSelect).val('').trigger('change');
                
                // Show error toast
                showToast('Error loading classrooms. Please try again.', 'error');
            } finally {
                hideLoading();
            }
        }
        
        // Event listeners
        buildingSelect.addEventListener('change', function() {
            fetchCourses();
            fetchClassrooms();
        });
        
        semesterSelect.addEventListener('change', fetchCourses);
        projectorSelect.addEventListener('change', fetchClassrooms);
        
        // Form validation
        scheduleForm.addEventListener('submit', function(e) {
            let isValid = true;
            
            // Check all required fields
            const requiredFields = scheduleForm.querySelectorAll('[required]');
            requiredFields.forEach(field => {
                if (!field.value) {
                    field.classList.add('is-invalid');
                    isValid = false;
                } else {
                    field.classList.remove('is-invalid');
                }
            });
            
            if (!isValid) {
                e.preventDefault();
                showToast('Please fill all required fields', 'warning');
            } else {
                showLoading();
            }
        });
        
        // Initialize if editing
        <?php if ($edit_data): ?>
        if (buildingSelect.value) {
            // Trigger change events to load dependent dropdowns
            $(buildingSelect).trigger('change');
            $(semesterSelect).trigger('change');
            $(projectorSelect).trigger('change');
        }
        <?php endif; ?>
        
        // Helper function to show toast messages
        function showToast(message, type = 'success') {
            const toast = document.getElementById('toast');
            toast.textContent = message;
            toast.className = `toast toast-${type} show`;
            
            setTimeout(() => {
                toast.classList.remove('show');
            }, 3000);
        }
        
        // Show loading overlay
        function showLoading() {
            loadingOverlay.style.display = 'flex';
        }
        
        // Hide loading overlay
        function hideLoading() {
            loadingOverlay.style.display = 'none';
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
                window.location.href = 'manual_department_schedule.php';
                <?php else: ?>
                scheduleForm.reset();
                fetchCourses();
                fetchClassrooms();
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
    });
    </script>
</body>
</html>