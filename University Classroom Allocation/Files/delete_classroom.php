<?php
include('config.php');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delete Classroom</title>
    <link rel="stylesheet" href="style.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            $("#building_select").change(function() {
                var building_id = $(this).val();
                
                $.ajax({
                    url: "fetch_classrooms.php",
                    type: "GET",
                    data: { building_id: building_id },
                    success: function(data) {
                        $("#classroom_select").html(data);
                    },
                    error: function(xhr, status, error) {
                        console.error("AJAX Error:", status, error);
                    }
                });
            });
        });
    </script>
</head>
<body>
    <div class="navigation">
        <a href="index.php" class="btn-back">Back to Main Page</a>
    </div>
    <div class="container">
        <h1>Delete Classroom</h1>

        <div class="form-card">
            <form method="POST">
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

                <label>Select Classroom to Delete</label>
                <select name="delete_classroom_id" id="classroom_select" required>
                    <option value="">Select a Classroom</option>
                </select>

                <input type="submit" name="delete_classroom" value="Delete Classroom" class="btn-submit">
            </form>

            <?php
            if (isset($_POST['delete_classroom'])) {
                $classroom_id = $_POST['delete_classroom_id'];
            
                // First, delete all schedule records linked to this classroom
                $con->query("DELETE FROM schedule WHERE classroom_id='$classroom_id'");
            
                // Then delete the classroom itself
                $con->query("DELETE FROM classrooms WHERE id='$classroom_id'");
            
                echo "<p class='success-message'>Classroom deleted successfully!</p>";
            }
            
            ?>
        </div>
    </div>
</body>
</html>
