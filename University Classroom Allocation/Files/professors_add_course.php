<?php
include('config.php');
session_start();

// Ensure professor is logged in
if (!isset($_SESSION['professor_logged_in'])) {
    header("Location: login.php");
    exit;
}

$building_id = $_SESSION['building_id'];
$success_message = "";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Course - Professor</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="navigation">
    <a href="professors_manage_courses.php" class="btn-back">Back to Dashboard</a>
</div>
<div class="container">
    <h1>Add Course</h1>

    <div class="form-card">
        <?php if (!empty($success_message)): ?>
            <p class='success-message'><?= $success_message ?></p>
        <?php endif; ?>

        <form method="POST">
            <label>Course Name:</label>
            <input type="text" name="course_name" placeholder="Enter Course Name" required>

            <!-- Hidden building ID -->
            <input type="hidden" name="building_id" value="<?= $building_id ?>">

            <label>Credits:</label>
            <input type="number" name="credits" placeholder="Enter Credits" required>

            <label>Lecture Hours (L):</label>
            <input type="number" name="lecture_hours" placeholder="Enter Lecture Hours" required>

            <label>Tutorial Hours (T):</label>
            <input type="number" name="tutorial_hours" placeholder="Enter Tutorial Hours" required>

            <label>Practical Hours (P):</label>
            <input type="number" name="practical_hours" placeholder="Enter Practical Hours" required>

            <label>Students Enrolled:</label>
            <input type="number" name="students_enrolled" placeholder="Enter Number of Students" required>

            <label>Professor:</label>
            <select name="professor_id" required>
                <option value="" selected>Select the Professor</option>
                <?php
                $professors = $con->query("SELECT id, name FROM professors WHERE building_id = $building_id");
                while ($row = $professors->fetch_assoc()) {
                    echo "<option value='{$row['id']}'>{$row['name']}</option>";
                }
                ?>
            </select>

            <label>Semester:</label>
            <select name="semester_id" required>
                <option value="" selected>Select the Semester</option>
                <?php
                $semesters = $con->query("SELECT * FROM semester");
                while ($row = $semesters->fetch_assoc()) {
                    echo "<option value='{$row['id']}'>{$row['name']}</option>";
                }
                ?>
            </select>

            <label>Requires Projector</label>
            <select name="requires_projector" required>
                <option value="1">Yes</option>
                <option value="0">No</option>
            </select>

            <input type="submit" name="add_course" value="Add Course" class="btn-submit">
        </form>

        <?php
        if (isset($_POST['add_course'])) {
            $course_name = $_POST['course_name'];
            $credits = $_POST['credits'];
            $lecture_hours = $_POST['lecture_hours'];
            $tutorial_hours = $_POST['tutorial_hours'];
            $practical_hours = $_POST['practical_hours'];
            $students_enrolled = $_POST['students_enrolled'];
            $professor_id = $_POST['professor_id'];
            $semester_id = $_POST['semester_id'];
            $requires_projector = $_POST['requires_projector'];

            $query = "INSERT INTO courses (name, building_id, credits, lecture_hours, tutorial_hours, practical_hours, students_enrolled, professor_id, semester_id, requires_projector)
                      VALUES ('$course_name', '$building_id', '$credits', '$lecture_hours', '$tutorial_hours', '$practical_hours', '$students_enrolled', '$professor_id', '$semester_id', '$requires_projector')";

            if ($con->query($query)) {
                echo "<p class='success-message'>Course added successfully!</p>";
            } else {
                echo "<p class='error-message'>Error: {$con->error}</p>";
            }
        }
        ?>
    </div>
</div>
</body>
</html>
