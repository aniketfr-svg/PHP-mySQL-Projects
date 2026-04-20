<?php
session_start();
if (!isset($_SESSION['professor_logged_in'])) {
    header("Location: professor_login.php");
    exit;
}
include('config.php');

$building_id = $_SESSION['building_id'];
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$limit = 5;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$start = ($page - 1) * $limit;
$likeSearch = "%$search%";

// Count total matching courses
$countStmt = $con->prepare("
    SELECT COUNT(*) 
    FROM courses 
    WHERE building_id = ? AND name LIKE ?
");
$countStmt->bind_param("is", $building_id, $likeSearch);
$countStmt->execute();
$countResult = $countStmt->get_result()->fetch_row();
$total = $countResult[0];
$pages = ceil($total / $limit);

// Fetch course data with semester names using JOIN
$stmt = $con->prepare("
    SELECT courses.id, courses.name AS course_name, semester.name AS semester_name
    FROM courses
    JOIN semester ON courses.semester_id = semester.id
    WHERE courses.building_id = ? AND courses.name LIKE ?
    LIMIT ?, ?
");
$stmt->bind_param("isii", $building_id, $likeSearch, $start, $limit);
$stmt->execute();
$result = $stmt->get_result();

// Fetch the department/building name
$building_stmt = $con->prepare("SELECT building_name FROM buildings WHERE id = ?");
$building_stmt->bind_param("i", $building_id);
$building_stmt->execute();
$building_result = $building_stmt->get_result()->fetch_assoc();
$building_name = $building_result ? $building_result['building_name'] : "Department";

?>

<!-- HTML starts -->
<!DOCTYPE html>
<html>
<head>
    <title>Manage Courses</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body>
<div class="container mt-4">
<h2 class="text-center"><?= htmlspecialchars($building_name) ?> Courses</h2>


    <form class="input-group mb-3" method="GET">
        <input type="text" name="search" class="form-control" placeholder="Search course..." value="<?= htmlspecialchars($search); ?>">
        <button class="btn btn-primary">Search</button>
        <a href="professors_manage_courses.php" class="btn btn-secondary ms-2">Reset</a>
    </form>

    <a href="professors_add_course.php" class="btn btn-success mb-3">Add New Course</a>

    <table class="table table-bordered">
        <thead class="table-dark">
            <tr>
                <th>ID</th>
                <th>Course Name</th>
                <th>Semester</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?= $row['id']; ?></td>
                <td><?= htmlspecialchars($row['course_name']); ?></td>
                <td><?= htmlspecialchars($row['semester_name']); ?></td>
                <td>
                    <a href="professors_edit_course.php?id=<?= $row['id']; ?>" class="btn btn-warning btn-sm">Edit</a>
                    <a href="professors_delete_course.php?id=<?= $row['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure?')">Delete</a>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
    <a href="professor_dashboard.php" style="display: block; margin-top:30px; margin-left:50%;">Back</a>
    <!-- Pagination -->
    <?php if ($pages > 1): ?>
    <nav>
        <ul class="pagination justify-content-center">
            <?php for ($i = 1; $i <= $pages; $i++): ?>
                <li class="page-item <?= ($i == $page) ? 'active' : ''; ?>">
                    <a class="page-link" href="?search=<?= urlencode($search); ?>&page=<?= $i; ?>"><?= $i; ?></a>
                </li>
            <?php endfor; ?>
        </ul>
    </nav>
    <?php endif; ?>
</div>
</body>
</html>
