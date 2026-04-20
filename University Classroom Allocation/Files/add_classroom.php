<?php include('config.php'); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Classroom</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="navigation">
        <a href="index.php" class="btn-back">Back to Main Page</a>
    </div>
    <div class="container">
        <h1>Add Classroom</h1>

        <div class="form-card">
            <form method="POST">
                <label>Classroom Name:</label>
                <input type="text" name="classroom_name" placeholder="Enter Classroom Name" required>
                
                <label>Capacity:</label>
                <input type="number" name="capacity" placeholder="Enter Capacity" required>
                
                <label>Department/Building:</label>
                <select name="building" required>
                    <option value="" selected>Select Department/Building</option>
                    <?php
                    $result = $con->query("SELECT * FROM buildings"); // Assuming a 'buildings' table exists
                    while ($row = $result->fetch_assoc()) {
                        echo "<option value='{$row['id']}'>{$row['building_name']}</option>";
                    }
                    ?>
                </select>

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
                $building_id = $_POST['building'];  // Get the selected building
                $has_projector = $_POST['projector'];

                // Insert into the classrooms table
                $query = "INSERT INTO classrooms (name, capacity, building_id, has_projector) 
                          VALUES ('$classroom_name', '$capacity', '$building_id', '$has_projector')";
                
                if ($con->query($query)) {
                    echo "<p class='success-message'>Classroom added successfully!</p>";
                } else {
                    echo "<p class='error-message'>Error adding classroom: " . $con->error . "</p>";
                }
            }
            ?>
        </div>
    </div>
</body>
</html>


        <!-- Other forms for updating and deleting classrooms can go here -->
        <!-- Update Classroom Form -->
        <!--<div class="form-card">
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
            <!--<div class="form-card">
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
            </div>-->
        </div>
    </div>
    <script>
        function populateClassroomDetails() {
            var classroomId = document.getElementById('update_classroom_id').value;
            // Fetch classroom details using AJAX or other methods
            // Populate the respective fields
        }
    </script>
</body>
</html>
