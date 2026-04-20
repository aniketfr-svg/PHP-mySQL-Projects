<?php include('config.php'); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Course</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="navigation">
        <a href="index.php" class="btn-back">Back to Main Page</a>
    </div>
    <div class="container">
        <h1>Delete Course</h1>
            <!-- Delete Course Form -->
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

                    <label>Select Course to Delete:</label>
                    <select name="delete_course_id" required>
                    <option value="" selected>Select a course</option>
                       
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

<?php
            if (isset($_POST['delete_course'])) {
                $classroom_id = $_POST['delete_course_id'];
            
                // First, delete all schedule records linked to this classroom
                $con->query("DELETE FROM schedule WHERE course_id='$course_id'");
            
            }
            
            ?>
            </div>
        </div>
    </div>
    <script>
        document.getElementById("building_select").addEventListener("change", function () {
    var buildingId = this.value;
    var courseSelect = document.querySelector("select[name='delete_course_id']"); // ✅ Correct dropdown selector

    // Clear previous options
    courseSelect.innerHTML = '<option value="">Select a course</option>';

    if (buildingId) {
        fetch("fetch_courses.php?building_id=" + buildingId)
            .then(response => response.json())
            .then(data => {
                data.forEach(course => {
                    var option = document.createElement("option");
                    option.value = course.id;
                    option.textContent = course.name;
                    courseSelect.appendChild(option);
                });
            })
            .catch(error => console.error("Error fetching courses:", error));
    }
});

</script>
</body>
</html>
