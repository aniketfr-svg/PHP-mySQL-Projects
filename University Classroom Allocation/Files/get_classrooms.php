<?php
include('config.php');

if (isset($_GET['building_id'])) {
    $building_id = $_GET['building_id'];
    $result = $con->query("SELECT * FROM classrooms WHERE building_id = '$building_id'");
    
    $classrooms = [];
    while ($row = $result->fetch_assoc()) {
        $classrooms[] = $row;
    }

    echo json_encode($classrooms);
}
?>
