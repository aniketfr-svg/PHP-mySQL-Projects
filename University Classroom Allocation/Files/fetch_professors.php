<?php
include('config.php');

if (isset($_POST['building_id'])) {
    $building_id = $_POST['building_id'];
    $result = $con->query("SELECT * FROM professors WHERE building_id = '$building_id'");
    
    echo "<option value=''>Select a Professor</option>";
    while ($row = $result->fetch_assoc()) {
        echo "<option value='{$row['id']}'>{$row['name']}</option>";
    }
}
?>

<?php
include('config.php');

if (isset($_GET['building_id'])) {
    $building_id = $_GET['building_id'];

    // Fetch only professors from the selected building/department
    $result = $con->query("SELECT id, name FROM professors WHERE building_id = '$building_id'");

    $professors = [];
    while ($row = $result->fetch_assoc()) {
        $professors[] = $row;
    }

    echo json_encode($professors);
}
?>
