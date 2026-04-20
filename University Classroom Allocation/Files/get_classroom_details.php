<?php
include('config.php');

if (isset($_GET['id'])) {
    $classroom_id = $_GET['id'];

    $result = $con->query("SELECT * FROM classrooms WHERE id = '$classroom_id'");
    if ($result->num_rows > 0) {
        $classroom = $result->fetch_assoc();
        echo json_encode($classroom);
    } else {
        echo json_encode(['error' => 'Classroom not found']);
    }
}
?>
