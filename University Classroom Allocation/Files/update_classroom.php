<?php
include('config.php');

if (isset($_GET['id'])) {
    $classroom_id = $_GET['id'];
    $result = $con->query("SELECT * FROM classrooms WHERE id = '$classroom_id'");
    $classroom = $result->fetch_assoc();
    echo json_encode($classroom);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Classroom</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="navigation">
        <a href="index.php" class="btn-back">Back to Main Page</a>
    </div>
    <div class="container">
        <h1>Update Classroom</h1>

        <div class="form-card">
            <form method="POST" action="">
            <label>Department/Building:</label>
                <select name="update_building" id="update_building" required>
                    <option value="">Select Department/Building</option>
                    <?php
                    $buildings = $con->query("SELECT * FROM buildings");
                    while ($row = $buildings->fetch_assoc()) {
                        echo "<option value='{$row['id']}'>{$row['building_name']}</option>";
                    }
                    ?>
                </select>

                <label>Select Classroom to Update:</label>
                <select name="update_classroom_id" id="update_classroom_id" required >
                    <option value="">Select a classroom</option>
                    
                </select>

                <label>Classroom Name:</label>
                <input type="text" name="update_classroom_name" id="update_classroom_name" placeholder="Enter New Classroom Name" required>

                <label>Capacity:</label>
                <input type="number" name="update_capacity" id="update_capacity" placeholder="Enter New Capacity" required>


                <label>Projector Available:</label>
                <select name="update_has_projector" id="update_has_projector" required>
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
                $building_id = $_POST['update_building'];  // Fetching selected building
                $has_projector = $_POST['update_has_projector'];

                // Update the classrooms table
                $query = "UPDATE classrooms 
                          SET name='$classroom_name', capacity='$capacity', building_id='$building_id', has_projector='$has_projector' 
                          WHERE id='$classroom_id'";

                if ($con->query($query)) {
                    echo "<p class='success-message'>Classroom updated successfully!</p>";
                } else {
                    echo "<p class='error-message'>Error updating classroom: " . $con->error . "</p>";
                }
            }
            ?>

            <?php
            if (isset($_POST['update_classroom'])) {
                $classroom_id = $_POST['update_classroom_id'];  // Old professor ID
                $new_classroom_name = $_POST['update_classroom_name'];
            
                // Update classroom's name in the classrooms table
                $con->query("UPDATE classrooms SET name='$new_classroom_name' WHERE id='$classroom_id'");
            
                // Update schedule table (assuming the classroom's ID remains the same)
                $con->query("UPDATE schedule SET classroom_id='$classroom_id' WHERE classroom_id='$classroom_id'");
            
                
            } 
            ?>
        </div>
    </div>

    <script>
    document.getElementById('update_building').addEventListener('change', function() {
        var buildingId = this.value;
        var classroomDropdown = document.getElementById('update_classroom_id');

        // Clear previous options
        classroomDropdown.innerHTML = '<option value="">Select a classroom</option>';

        if (buildingId) {
            var xhr = new XMLHttpRequest();
            xhr.open('GET', 'get_classrooms.php?building_id=' + buildingId, true);
            xhr.onload = function() {
                if (xhr.status == 200) {
                    var classrooms = JSON.parse(xhr.responseText);
                    classrooms.forEach(function(classroom) {
                        var option = document.createElement('option');
                        option.value = classroom.id;
                        option.textContent = classroom.name;
                        classroomDropdown.appendChild(option);
                    });
                }
            };
            xhr.send();
        }
    });

    document.getElementById('update_classroom_id').addEventListener('change', function() {
        var classroomId = this.value;
        if (classroomId) {
            var xhr = new XMLHttpRequest();
            xhr.open('GET', 'get_classroom_details.php?id=' + classroomId, true);
            xhr.onload = function() {
                if (xhr.status == 200) {
                    var classroom = JSON.parse(xhr.responseText);
                    document.getElementById('update_classroom_name').value = classroom.name;
                    document.getElementById('update_capacity').value = classroom.capacity;
                    document.getElementById('update_has_projector').value = classroom.has_projector;
                }
            };
            xhr.send();
        }
    });
</script>


    <!--<script>
        function populateClassroomDetails() {
            var classroomId = document.getElementById('update_classroom_id').value;
            if (classroomId) {
                var xhr = new XMLHttpRequest();
                xhr.open('GET', 'get_classroom_details.php?id=' + classroomId, true);
                xhr.onload = function() {
                    if (xhr.status == 200) {
                        var classroom = JSON.parse(xhr.responseText);
                        document.getElementById('update_classroom_name').value = classroom.name;
                        document.getElementById('update_capacity').value = classroom.capacity;
                        document.getElementById('update_has_projector').value = classroom.has_projector;
                        document.getElementById('update_building').value = classroom.building_id; // Set building
                    }
                };
                xhr.send();
            }
        }
    </script>-->
</body>
</html>
