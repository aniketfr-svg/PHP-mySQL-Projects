<?php
include('config.php');
session_start();
if (!isset($_SESSION['professor_logged_in'])) {
    header("Location: professor_login.php");
    exit;
}

$course_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Fetch course details
$stmt = $con->prepare("SELECT * FROM courses WHERE id = ?");
$stmt->bind_param("i", $course_id);
$stmt->execute();
$result = $stmt->get_result();
$course = $result->fetch_assoc();

if (!$course) {
    echo "<p>Course not found.</p>";
    exit;
}

// Process form update
if (isset($_POST['update_course'])) {
    $name = $_POST['name'];
    $credits = $_POST['credits'];
    $lecture_hours = $_POST['lecture_hours'];
    $tutorial_hours = $_POST['tutorial_hours'];
    $practical_hours = $_POST['practical_hours'];
    $students_enrolled = $_POST['students_enrolled'];
    $professor_id = $_POST['professor_id'];
    $semester_id = $_POST['semester_id'];
    $requires_projector = isset($_POST['requires_projector']) ? $_POST['requires_projector'] : 0;

    $updateStmt = $con->prepare("UPDATE courses SET name=?, credits=?, lecture_hours=?, tutorial_hours=?, practical_hours=?, students_enrolled=?, professor_id=?, semester_id=?, requires_projector=? WHERE id=?");
    $updateStmt->bind_param("siiiiiiiii", $name, $credits, $lecture_hours, $tutorial_hours, $practical_hours, $students_enrolled, $professor_id, $semester_id, $requires_projector, $course_id);
    
    if ($updateStmt->execute()) {
        echo "<p class='success-message'>Course updated successfully!</p>";
    } else {
        echo "<p class='error-message'>Failed to update course: " . $con->error . "</p>";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Course</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="navigation">
    <a href="professors_manage_courses.php" class="btn-back">← Back to Manage Courses</a>
</div>
<div class="container">
    <h1>Edit Course</h1>
    <div class="form-card">
        <form method="POST">
            <label>Course Name:</label>
            <input type="text" name="name" value="<?= htmlspecialchars($course['name']) ?>" required>

            <label>Credits:</label>
            <input type="number" name="credits" value="<?= $course['credits'] ?>" required>

            <label>Lecture Hours (L):</label>
            <input type="number" name="lecture_hours" value="<?= $course['lecture_hours'] ?>" required>

            <label>Tutorial Hours (T):</label>
            <input type="number" name="tutorial_hours" value="<?= $course['tutorial_hours'] ?>" required>

            <label>Practical Hours (P):</label>
            <input type="number" name="practical_hours" value="<?= $course['practical_hours'] ?>" required>

            <label>Students Enrolled:</label>
            <input type="number" name="students_enrolled" value="<?= $course['students_enrolled'] ?>" required>

            <label>Professor:</label>
            <select name="professor_id" required>
                <option value="">Select Professor</option>
                <?php
                $professors = $con->query("SELECT * FROM professors WHERE building_id = " . $_SESSION['building_id']);
                while ($prof = $professors->fetch_assoc()) {
                    $selected = ($prof['id'] == $course['professor_id']) ? "selected" : "";
                    echo "<option value='{$prof['id']}' $selected>{$prof['name']}</option>";
                }
                ?>
            </select>

            <label>Semester:</label>
            <select name="semester_id" required>
                <option value="">Select Semester</option>
                <?php
                $semesters = $con->query("SELECT * FROM semester");
                while ($sem = $semesters->fetch_assoc()) {
                    $selected = ($sem['id'] == $course['semester_id']) ? "selected" : "";
                    echo "<option value='{$sem['id']}' $selected>{$sem['name']}</option>";
                }
                ?>
            </select>

            <label>Requires Projector:</label>
            <select name="requires_projector" required>
                <option value="1" <?= ($course['requires_projector'] == 1 ? 'selected' : '') ?>>Yes</option>
                <option value="0" <?= ($course['requires_projector'] == 0 ? 'selected' : '') ?>>No</option>
            </select>

            <input type="submit" name="update_course" value="Update Course" class="btn-submit">
        </form>
    </div>
</div>
</body>
</html>
