<?php
include('config.php');
session_start();

// Check professor login
if (!isset($_SESSION['professor_logged_in'])) {
    header("Location: professor_login.php");
    exit;
}

$course_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$building_id = $_SESSION['building_id'];

$message = '';
$error = '';

// First, validate that the course belongs to the professor's department
$checkStmt = $con->prepare("SELECT id, name FROM courses WHERE id = ? AND building_id = ?");
$checkStmt->bind_param("ii", $course_id, $building_id);
$checkStmt->execute();
$checkResult = $checkStmt->get_result();

if ($checkResult->num_rows === 0) {
    $error = "Invalid course ID or unauthorized access.";
} else {
    // Delete from schedule (if applicable)
    $deleteSchedule = $con->prepare("DELETE FROM schedule WHERE course_id = ?");
    $deleteSchedule->bind_param("i", $course_id);
    $deleteSchedule->execute();

    // Delete course
    $deleteStmt = $con->prepare("DELETE FROM courses WHERE id = ?");
    $deleteStmt->bind_param("i", $course_id);
    if ($deleteStmt->execute()) {
        $message = "Course deleted successfully.";
    } else {
        $error = "Failed to delete course: " . $con->error;
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Delete Course</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body>
<div class="container mt-5">
    <h3>Delete Course</h3>

    <?php if ($message): ?>
        <div class="alert alert-success"><?= $message ?></div>
        <a href="professors_manage_courses.php" class="btn btn-primary">Back to Manage Courses</a>
    <?php elseif ($error): ?>
        <div class="alert alert-danger"><?= $error ?></div>
        <a href="professors_manage_courses.php" class="btn btn-secondary">Back</a>
    <?php endif; ?>
</div>
</body>
</html>
