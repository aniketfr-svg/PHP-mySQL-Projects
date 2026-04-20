<?php include('config.php'); ?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Management</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
</head>

<body>
    <div class="navigation">
        <a href="index.php" class="btn-back">Back to Main Page</a> <!-- Back link added here -->
    </div>
    <div class="container">
        <h1>Data Management</h1>

        <!-- Section for Courses -->
        <div class="form-section">
            <h2><i class="fas fa-book"></i> Courses</h2>

            <!-- Add Course Form -->
            <div class="form-card">
                <h3>Add Course</h3>
                <form method="POST" action="">
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
                    <input type="number" name="students_enrolled" required value="0"> <!-- New input field -->

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
                    // Insert course into the database
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
                    echo "<p class='success-message'>Course added!</p>";
                }
                ?>
            </div>


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

        <!-- Section for Classrooms -->
        <div class="form-section">
            <h2><i class="fas fa-chalkboard"></i> Classrooms</h2>

            <!-- Add Classroom Form -->
            <div class="form-card">
                <h3>Add Classroom</h3>
                <form method="POST" action="">
                    <label>Classroom Name:</label>
                    <input type="text" name="classroom_name" required>

                    <label>Capacity:</label>
                    <input type="number" name="capacity" required>

                    <label>Projector Available:</label>
                    <select name="projector" required>
                        <option value="1">Yes</option>
                        <option value="0">No</option>
                    </select>

                    <input type="submit" name="add_classroom" value="Add Classroom" class="btn-submit">
                </form>

                <?php
                if (isset($_POST['add_classroom'])) {
                    $classroom_name = $_POST['classroom_name'];
                    $capacity = $_POST['capacity'];
                    $projector = $_POST['projector']; // Fetch projector availability

                    // Insert into the classrooms table
                    $con->query("INSERT INTO classrooms (name, capacity, projector) VALUES ('$classroom_name', '$capacity', '$projector')");
                    echo "<p class='success-message'>Classroom added!</p>";
                }
                ?>
            </div>

            <!-- Update Classroom Form -->
            <div class="form-card">
                <h3>Update Classroom</h3>
                <form method="POST" action="">
                    <label>Select Classroom to Update:</label>
                    <select name="update_classroom_id" required onchange="populateClassroomDetails()">
                        <option value="">Select a classroom</option>
                        <?php
                        $result = $con->query("SELECT * FROM classrooms");
                        while ($row = $result->fetch_assoc()) {
                            echo "<option value='{$row['id']}'>{$row['name']}</option>";
                        }
                        ?>
                    </select>

                    <label>Classroom Name:</label>
                    <input type="text" name="update_classroom_name" id="update_classroom_name" required>

                    <label>Capacity:</label>
                    <input type="number" name="update_capacity" id="update_capacity" required>

                    <label>Projector Available:</label>
                    <select name="update_projector" id="update_projector" required>
                        <option value="1">Yes</option>
                        <option value="0">No</option>
                    </select>

                    <input type="submit" name="update_classroom" value="Update Classroom" class="btn-submit">
                </form>

                <?php
                if (isset($_POST['update_classroom'])) {
                    $classroom_id = $_POST['update_classroom_id'];
                    $classroom_name = $_POST['update_classroom_name'];
                    $capacity = $_POST['update_capacity'];
                    $projector = $_POST['update_projector'];

                    // Update the classrooms table
                    $con->query("UPDATE classrooms SET name='$classroom_name', capacity='$capacity', projector='$projector' WHERE id='$classroom_id'");
                    echo "<p class='success-message'>Classroom updated!</p>";
                }
                ?>
            </div>


            <!-- Delete Classroom Form -->
            <div class="form-card">
                <h3>Delete Classroom</h3>
                <form method="POST" action="">
                    <label>Select Classroom to Delete:</label>
                    <select name="delete_classroom_id" required>
                        <?php
                        $result = $con->query("SELECT * FROM classrooms");
                        while ($row = $result->fetch_assoc()) {
                            echo "<option value='{$row['id']}'>{$row['name']}</option>";
                        }
                        ?>
                    </select>
                    <input type="submit" name="delete_classroom" value="Delete Classroom" class="btn-submit">
                </form>

                <?php
                if (isset($_POST['delete_classroom'])) {
                    $classroom_id = $_POST['delete_classroom_id'];
                    $con->query("DELETE FROM classrooms WHERE id='$classroom_id'");
                    echo "<p class='success-message'>Classroom deleted!</p>";
                }
                ?>
            </div>
        </div>

        <!-- Section for Professors -->
        <div class="form-section">
            <h2><i class="fas fa-user-tie"></i> Professors</h2>

            <!-- Add Professor Form -->
            <div class="form-card">
                <h3>Add Professor</h3>
                <form method="POST" action="">
                    <label>Professor Name:</label>
                    <input type="text" name="professor_name" required>
                    <input type="submit" name="add_professor" value="Add Professor" class="btn-submit">
                </form>

                <?php
                if (isset($_POST['add_professor'])) {
                    $professor_name = $_POST['professor_name'];
                    $con->query("INSERT INTO professors (name) VALUES ('$professor_name')");
                    echo "<p class='success-message'>Professor added!</p>";
                }
                ?>
            </div>

            <!-- Update Professor Form -->
            <div class="form-card">
                <h3>Update Professor</h3>
                <form method="POST" action="">
                    <label>Select Professor to Update:</label>
                    <select name="update_professor_id" required onchange="populateProfessorDetails()">
                        <option value="">Select a professor</option>
                        <?php
                        $result = $con->query("SELECT * FROM professors");
                        while ($row = $result->fetch_assoc()) {
                            echo "<option value='{$row['id']}'>{$row['name']}</option>";
                        }
                        ?>
                    </select>

                    <label>Professor Name:</label>
                    <input type="text" name="update_professor_name" id="update_professor_name" required>
                    <input type="submit" name="update_professor" value="Update Professor" class="btn-submit">
                </form>

                <?php
                if (isset($_POST['update_professor'])) {
                    $professor_id = $_POST['update_professor_id'];
                    $professor_name = $_POST['update_professor_name'];
                    $con->query("UPDATE professors SET name='$professor_name' WHERE id='$professor_id'");
                    echo "<p class='success-message'>Professor updated!</p>";
                }
                ?>
            </div>

            <!-- Delete Professor Form -->
            <div class="form-card">
                <h3>Delete Professor</h3>
                <form method="POST" action="">
                    <label>Select Professor to Delete:</label>
                    <select name="delete_professor_id" required>
                        <?php
                        $result = $con->query("SELECT * FROM professors");
                        while ($row = $result->fetch_assoc()) {
                            echo "<option value='{$row['id']}'>{$row['name']}</option>";
                        }
                        ?>
                    </select>
                    <input type="submit" name="delete_professor" value="Delete Professor" class="btn-submit">
                </form>

                <?php
                if (isset($_POST['delete_professor'])) {
                    $professor_id = $_POST['delete_professor_id'];
                    $con->query("DELETE FROM professors WHERE id='$professor_id'");
                    echo "<p class='success-message'>Professor deleted!</p>";
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

        function populateClassroomDetails() {
            var classroomId = document.getElementById('update_classroom_id').value;
            // Fetch classroom details using AJAX or other methods
            // Populate the respective fields
        }

        function populateProfessorDetails() {
            var professorId = document.getElementById('update_professor_id').value;
            // Fetch professor details using AJAX or other methods
            // Populate the respective fields
        }
    </script>
</body>

</html>