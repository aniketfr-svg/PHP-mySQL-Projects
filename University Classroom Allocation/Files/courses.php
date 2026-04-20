<?php include('config.php'); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Courses</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="navigation">
        <a href="index.php" class="btn-back">Back to Main Page</a>
    </div>
    <div class="container">
        <h1>Manage Courses</h1>

        <!-- Add Course -->
        <div class="form-card">
            <h3>Add Course</h3>
            <form method="POST">
                <label>Course Name:</label>
                <input type="text" name="course_name" required>
                <label>Credits:</label>
                <input type="number" name="credits" required>
                <label>Lecture Hours (L):</label>
                <input type="number" name="lecture_hours" required>
                <label>Tutorial Hours (T):</label>
                <input type="number" name="tutorial_hours" required>
                <label>Practical Hours (P):</label>
                <input type="number" name="practical_hours" required>
                <label>Students Enrolled:</label>
                <input type="number" name="students_enrolled" required value="0">
                <label>Professor:</label>
                <select name="professor_id" required>
                    <?php
                    $result = $con->query("SELECT * FROM professors");
                    while ($row = $result->fetch_assoc()) {
                        echo "<option value='{$row['id']}'>{$row['name']}</option>";
                    }
                    ?>
                </select>
                <label>Semester:</label>
                <select name="semester_id" required>
                    <?php
                    $result = $con->query("SELECT * FROM semester");
                    while ($row = $result->fetch_assoc()) {
                        echo "<option value='{$row['id']}'>{$row['name']}</option>";
                    }
                    ?>
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

                $con->query("INSERT INTO courses (name, credits, lecture_hours, tutorial_hours, practical_hours, students_enrolled, professor_id, semester_id)
                            VALUES ('$course_name', '$credits', '$lecture_hours', '$tutorial_hours', '$practical_hours', '$students_enrolled', '$professor_id', '$semester_id')");
                echo "<p class='success-message'>Course added successfully!</p>";
            }
            ?>
        </div>

        <!-- Other forms for updating and deleting courses can go here -->
        <!-- Update Course Form -->
        <div class="form-card">
                <h3>Update Course</h3>
                <form method="POST" action="">
                    <label>Select Course to Update:</label>
                    <select name="update_course_id" id="update_course_id" required onchange="populateCourseDetails()">
                        <option value="">Select a course</option>
                        <?php
                        $result = $con->query("SELECT * FROM courses");
                        while ($row = $result->fetch_assoc()) {
                            echo "<option value='{$row['id']}'>{$row['name']}</option>";
                        }
                        ?>
                    </select>

                    <label>Course Name:</label>
                    <input type="text" name="update_course_name" id="update_course_name" required>

                    <label>Credits:</label>
                    <input type="number" name="update_credits" id="update_credits" required>

                    <label>Lecture Hours (L):</label>
                    <input type="number" name="update_lecture_hours" id="update_lecture_hours" required>

                    <label>Tutorial Hours (T):</label>
                    <input type="number" name="update_tutorial_hours" id="update_tutorial_hours" required>

                    <label>Practical Hours (P):</label>
                    <input type="number" name="update_practical_hours" id="update_practical_hours" required>

                    <label>Students Enrolled:</label>
                    <input type="number" name="update_students_enrolled" id="update_students_enrolled" required> <!-- New input field -->

                    <label>Professor:</label>
                    <select name="update_professor_id" id="update_professor_id" required>
                        <?php
                        $result = $con->query("SELECT * FROM professors");
                        while ($row = $result->fetch_assoc()) {
                            echo "<option value='{$row['id']}'>{$row['name']}</option>";
                        }
                        ?>
                    </select>

                    <label>Student Group:</label>
                    <select name="update_student_group" id="update_student_group" required>
                        <?php for ($i = 1; $i <= 8; $i++): ?>
                            <option value="<?= $i ?>"><?= $i ?></option>
                        <?php endfor; ?>
                    </select>

                    <input type="submit" name="update_course" value="Update Course" class="btn-submit">
                </form>

                <?php
                if (isset($_POST['update_course'])) {
                    $course_id = $_POST['update_course_id'];
                    $course_name = $_POST['update_course_name'];
                    $credits = $_POST['update_credits'];
                    $lecture_hours = $_POST['update_lecture_hours'];
                    $tutorial_hours = $_POST['update_tutorial_hours'];
                    $practical_hours = $_POST['update_practical_hours'];
                    $students_enrolled = $_POST['update_students_enrolled']; // Capture updated students enrolled
                    $professor_id = $_POST['update_professor_id'];
                    $student_group = $_POST['update_student_group'];

                    // Update course in the database
                    $con->query("UPDATE courses SET 
                                name='$course_name', 
                                credits='$credits', 
                                lecture_hours='$lecture_hours', 
                                tutorial_hours='$tutorial_hours', 
                                practical_hours='$practical_hours', 
                                students_enrolled='$students_enrolled', 
                                professor_id='$professor_id', 
                                student_group='$student_group' 
                                WHERE id='$course_id'");
                    echo "<p class='success-message'>Course updated!</p>";
                }
                ?>
            </div>

            <!-- Delete Course Form -->
            <div class="form-card">
                <h3>Delete Course</h3>
                <form method="POST" action="">
                    <label>Select Course to Delete:</label>
                    <select name="delete_course_id" required>
                        <?php
                        $result = $con->query("SELECT * FROM courses");
                        while ($row = $result->fetch_assoc()) {
                            echo "<option value='{$row['id']}'>{$row['name']}</option>";
                        }
                        ?>
                    </select>
                    <input type="submit" name="delete_course" value="Delete Course" class="btn-submit">
                </form>

                <?php
                if (isset($_POST['delete_course'])) {
                    $course_id = $_POST['delete_course_id'];
                    $con->query("DELETE FROM courses WHERE id='$course_id'");
                    echo "<p class='success-message'>Course deleted!</p>";
                }
                ?>
            </div>
        </div>
    </div>
    <script>
        function populateCourseDetails() {
            var courseId = document.getElementById('update_course_id').value;
            // Fetch course details using AJAX or other methods
            // Populate the respective fields
        }
    </script>
</body>
</html>
