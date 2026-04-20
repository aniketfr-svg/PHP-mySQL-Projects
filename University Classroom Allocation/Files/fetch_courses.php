<?php
include('config.php');

if (isset($_GET['building_id'])) {
    $building_id = $_GET['building_id'];

    $result = $con->query("SELECT id, name FROM courses WHERE building_id = '$building_id'");

    $courses = [];
    while ($row = $result->fetch_assoc()) {
        $courses[] = $row;
    }

    echo json_encode($courses);
}
?>
