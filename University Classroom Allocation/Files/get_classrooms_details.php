<?php
include('config.php');

if (isset($_GET['id'])) {
    $classroom_id = $_GET['id'];

    // Query to get the classroom details
    $query = "SELECT classrooms.name AS classroom_name, 
                     COALESCE(buildings.building_name, 'N/A') AS building_name, 
                     classrooms.has_projector
              FROM classrooms
              LEFT JOIN buildings ON classrooms.building_id = buildings.id
              WHERE classrooms.id = ?";
    
    // Prepare the statement
    $stmt = $con->prepare($query);
    $stmt->bind_param("i", $classroom_id);
    $stmt->execute();
    $result = $stmt->get_result();

    // Fetch the data and return as JSON
    if ($row = $result->fetch_assoc()) {
        echo json_encode($row);
    } else {
        echo json_encode(['error' => 'Classroom not found']);
    }

    $stmt->close();
} else {
    echo json_encode(['error' => 'No classroom ID provided']);
}
?>
