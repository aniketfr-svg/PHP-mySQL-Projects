<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit;
}

// Database connection
$host = 'localhost';
$db_name = 'timetable';
$db_user = 'root';
$db_password = '';

try {
    $conn = new PDO("mysql:host=$host;dbname=$db_name", $db_user, $db_password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Get ID from URL
$id = isset($_GET['id']) && is_numeric($_GET['id']) ? $_GET['id'] : null;

if ($id) {
    // Optional: Check if building exists
    $check = $conn->prepare("SELECT * FROM buildings WHERE id = :id");
    $check->bindParam(':id', $id);
    $check->execute();

    if ($check->rowCount() > 0) {
        $stmt = $conn->prepare("DELETE FROM buildings WHERE id = :id");
        $stmt->bindParam(':id', $id);
        if ($stmt->execute()) {
            $_SESSION['message'] = "Building deleted successfully.";
        } else {
            $_SESSION['message'] = "Failed to delete building.";
        }
    } else {
        $_SESSION['message'] = "Building not found.";
    }
} else {
    $_SESSION['message'] = "Invalid ID.";
}

header("Location: manage_buildings.php");
exit;
