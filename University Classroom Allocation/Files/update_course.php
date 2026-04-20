<?php include('config.php'); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Course</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="navigation">
        <a href="index.php" class="btn-back">Back to Main Page</a>
    </div>
    <div class="container">
        <h1>Update Course</h1>

        <div class="form-card">
            <form method="POST" action="">
                <label>Select Department/Building</label>
                <select name="building_id" id="building_select" required>
                    <option value="">Select a Department/Building</option>
                    <?php
                    $buildings = $con->query("SELECT * FROM buildings");
                    while ($row = $buildings->fetch_assoc()) {
                        echo "<option value='{$row['id']}'>{$row['building_name']}</option>";
                    }
                    ?>
                </select>
                <label>Semester:</label>
                <select name="update_semester_id" id="update_semester_id" required>
                    <option value="">Select the Semester</option>
                    <?php for ($i = 1; $i <= 8; $i++): ?>
                        <option value="<?= $i ?>"><?= $i ?></option>
                    <?php endfor; ?>
                </select>
                
                <label>Select Course to Update:</label>
                <select name="update_course_id" id="update_course_id" required onchange="populateCourseDetails()">
                    <option value="" selected>Select a course</option>
                </select>

                 <label>Course Name:</label>
                <input type="text" name="update_course_name" id="update_course_name" placeholder="Enter Course Name" required>

                <label>Credits:</label>
                <input type="number" name="update_credits" id="update_credits" placeholder="Enter Credits" required>

                <label>Lecture Hours (L):</label>
                <input type="number" name="update_lecture_hours" id="update_lecture_hours" placeholder="Enter Lecture Hours" required>

                <label>Tutorial Hours (T):</label>
                <input type="number" name="update_tutorial_hours" id="update_tutorial_hours" placeholder="Enter Tutorial Hours" required>

                <label>Practical Hours (P):</label>
                <input type="number" name="update_practical_hours" id="update_practical_hours" placeholder="Enter Practical Hours" required>

                <label>Students Enrolled:</label>
                <input type="number" name="update_students_enrolled" id="update_students_enrolled" placeholder="Enter Number of Students" required>

                <label>Professor:</label>
                <select name="update_professor_id" id="update_professor_id" required>
                    <option value="">Select the Professor</option>
                    <?php
                    $professors = $con->query("SELECT * FROM professors");
                    while ($row = $professors->fetch_assoc()) {
                        echo "<option value='{$row['id']}'>{$row['name']}</option>";
                    }
                    ?>
                </select>

                

                <label>Requires Projector:</label>
                <select name="update_requires_projector" id="update_requires_projector" required>
                    <option value="1">Yes</option>
                    <option value="0">No</option>
                </select>

                <label>Course Type:</label>
                <div class="course-type-options">
                    <label><input type="radio" name="update_course_type" value="core" required> Core</label>
                    <label><input type="radio" name="update_course_type" value="elective"> Elective</label>
                    <label><input type="radio" name="update_course_type" value="open_elective"> Open Elective</label>
                </div>

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
                $students_enrolled = $_POST['update_students_enrolled'];
                $professor_id = $_POST['update_professor_id'];
                $semester_id = $_POST['update_semester_id'];
                $requires_projector = isset($_POST['update_requires_projector']) ? $_POST['update_requires_projector'] : 0;
                $course_type = $_POST['update_course_type'];

                $query = "UPDATE courses SET
                    name='$course_name',
                    credits='$credits',
                    lecture_hours='$lecture_hours',
                    tutorial_hours='$tutorial_hours',
                    practical_hours='$practical_hours',
                    students_enrolled='$students_enrolled',
                    professor_id='$professor_id',
                    semester_id='$semester_id',
                    requires_projector='$requires_projector',
                    course_type='$course_type'
                    WHERE id='$course_id'";

                if ($con->query($query)) {
                    echo "<p class='success-message'>Course updated successfully!</p>";
                } else {
                    echo "<p class='error-message'>Error updating course: " . $con->error . "</p>";
                }
            }
            ?>
        </div>
    </div>

    <script>
    function populateCourseDetails() {
        var courseId = document.getElementById('update_course_id').value;
        if (!courseId) return;

        fetch('get_course_details.php?id=' + courseId)
            .then(response => response.json())
            .then(data => {
                if (data.error) {
                    console.error('Error:', data.error);
                } else {
                    document.getElementById('update_course_name').value = data.name;
                    document.getElementById('update_credits').value = data.credits;
                    document.getElementById('update_lecture_hours').value = data.lecture_hours;
                    document.getElementById('update_tutorial_hours').value = data.tutorial_hours;
                    document.getElementById('update_practical_hours').value = data.practical_hours;
                    document.getElementById('update_students_enrolled').value = data.students_enrolled;
                    document.getElementById('update_professor_id').value = data.professor_id;
                    document.getElementById('update_semester_id').value = data.semester_id;
                    document.getElementById('update_requires_projector').value = data.requires_projector;

                    // Set course type radio button
                    document.querySelectorAll('input[name="update_course_type"]').forEach(radio => {
                        radio.checked = radio.value === data.course_type;
                    });
                }
            })
            .catch(error => {
                console.error('Error fetching course details:', error);
            });
    }

   document.getElementById("building_select").addEventListener("change", fetchCourses);
document.getElementById("update_semester_id").addEventListener("change", fetchCourses);

function fetchCourses() {
    const buildingId = document.getElementById("building_select").value;
    const semesterId = document.getElementById("update_semester_id").value;
    const courseSelect = document.getElementById("update_course_id");

    // Reset course dropdown
    courseSelect.innerHTML = '<option value="">Select a course</option>';

    if (buildingId && semesterId) {
        fetch(`get_course_details.php?building_id=${buildingId}&semester_id=${semesterId}`)
            .then(response => response.json())
            .then(data => {
                if (Array.isArray(data)) {
                    data.forEach(course => {
                        const option = document.createElement("option");
                        option.value = course.id;
                        option.textContent = course.name;
                        courseSelect.appendChild(option);
                    });
                } else {
                    console.error("Error fetching courses:", data.error);
                }
            })
            .catch(error => console.error("Fetch error:", error));
    }
}

    </script>
</body>
</html>
