<?php
include('config.php');

$courseId = $_GET['id'] ?? null;

if ($courseId) {
    $result = $con->query("SELECT * FROM courses WHERE id = '$courseId'");
    $course = $result->fetch_assoc();
} else {
    echo "Course not found.";
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Course Details</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f5f5f5;
            padding: 50px;
            text-align: center;
        }
        .course-details {
            background: #ffffff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 8px 15px rgba(0, 0, 0, 0.1);
            display: inline-block;
        }
        .course-details h2 {
            margin-bottom: 20px;
            color: #2c3e50;
        }
        .course-details p {
            font-size: 1.2rem;
            color: #7f8c8d;
        }
    </style>
</head>
<body>
    <div class="course-details">
        <h2><?php echo htmlspecialchars($course['name']); ?></h2>
        <p><strong>Course Code:</strong> <?php echo htmlspecialchars($course['code']); ?></p>
        <p><strong>Description:</strong> <?php echo htmlspecialchars($course['description']); ?></p>
    </div>
</body>
</html>
