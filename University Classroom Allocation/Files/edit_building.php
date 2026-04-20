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

// Get the department ID from URL
$id = isset($_GET['id']) && is_numeric($_GET['id']) ? $_GET['id'] : null;
if (!$id) {
    header('Location: manage_buildings.php');
    exit;
}

// Fetch current building info
$stmt = $conn->prepare("SELECT * FROM buildings WHERE id = :id");
$stmt->bindParam(':id', $id);
$stmt->execute();
$building = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$building) {
    die("Building not found.");
}

// Handle form submission
$success = "";
$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $new_name = trim($_POST['building_name']);

    if (!empty($new_name)) {
        $update_stmt = $conn->prepare("UPDATE buildings SET building_name = :name WHERE id = :id");
        $update_stmt->bindParam(':name', $new_name);
        $update_stmt->bindParam(':id', $id);
        if ($update_stmt->execute()) {
            $success = "Building updated successfully!";
            $building['building_name'] = $new_name;
        } else {
            $error = "Failed to update building.";
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
    <title>Edit Department/Building</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .container { margin-top: 50px; max-width: 500px; }
        .card { padding: 20px; }
    </style>
</head>
<body>

<div class="container">
    <div class="card shadow">
        <h3 class="text-center mb-4">Edit Department / Building</h3>

        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="mb-3">
                <label for="building_name" class="form-label">Department/Building Name</label>
                <input type="text" class="form-control" id="building_name" name="building_name" value="<?php echo htmlspecialchars($building['building_name']); ?>" required>
            </div>
            <button type="submit" class="btn btn-primary w-100">💾 Save Changes</button>
            <a href="manage_buildings.php" class="btn btn-secondary w-100 mt-2">🔙 Back to Dashboard</a>
        </form>
    </div>
</div>

</body>
</html>
