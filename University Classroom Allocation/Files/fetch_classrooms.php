<?php
include('config.php');

if (isset($_GET['building_id']) && !empty($_GET['building_id'])) {
    $building_id = $_GET['building_id'];

    $stmt = $con->prepare("SELECT id, name FROM classrooms WHERE building_id = ?");
    $stmt->bind_param("i", $building_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        echo "<option value=''>Select a Classroom</option>";
        while ($row = $result->fetch_assoc()) {
            echo "<option value='{$row['id']}'>{$row['name']}</option>";
        }
    } else {
        echo "<option value=''>No classrooms found</option>";
    }

    $stmt->close();
}
?>
