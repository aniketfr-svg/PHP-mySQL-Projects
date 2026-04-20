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

// Handle form submission
$success = "";
$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $building_name = trim($_POST['building_name']);

    if (!empty($building_name)) {
        $stmt = $conn->prepare("INSERT INTO buildings (building_name) VALUES (:building_name)");
        $stmt->bindParam(':building_name', $building_name);
        if ($stmt->execute()) {
            $success = "Department/Building added successfully!";
        } else {
            $error = "Failed to add department/building.";
        }
    } else {
        $error = "Building name cannot be empty.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Department/Building</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f2f2f2;
        }
        .container {
            margin-top: 50px;
            max-width: 500px;
        }
        .card {
            padding: 20px;
            border-radius: 10px;
        }
    </style>
</head>
<body>

<div class="container">
    <div class="card shadow">
        <h3 class="text-center mb-4">Add Department / Building</h3>

        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="mb-3">
                <label for="building_name" class="form-label">Department/Building Name</label>
                <input type="text" class="form-control" id="building_name" name="building_name" required>
            </div>
            <button type="submit" class="btn btn-primary w-100">➕ Add</button>
            <a href="manage_buildings.php" class="btn btn-secondary w-100 mt-2">🔙 Back to Dashboard</a>
        </form>
    </div>
</div>

</body>
</html>
