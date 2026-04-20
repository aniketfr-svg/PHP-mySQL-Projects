


<?php
session_start();
if (!isset($_SESSION['professor_logged_in'])) {
    header("Location: professor_login.php");
    exit;
}
include('config.php');

$building_id = $_SESSION['building_id'];
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

// Fetch the department/building name
$building_stmt = $con->prepare("SELECT building_name FROM buildings WHERE id = ?");
$building_stmt->bind_param("i", $building_id);
$building_stmt->execute();
$building_result = $building_stmt->get_result()->fetch_assoc();
$building_name = $building_result ? $building_result['building_name'] : "Department";

// Fetch professors with their courses (without pagination)
$stmt = $con->prepare("
    SELECT p.id, p.name, p.username, GROUP_CONCAT(c.name SEPARATOR ', ') AS courses
    FROM professors p
    LEFT JOIN courses c ON p.id = c.professor_id
    WHERE p.building_id = ? AND p.name LIKE ?
    GROUP BY p.id
");
$likeSearch = "%$search%";
$stmt->bind_param("is", $building_id, $likeSearch);
$stmt->execute();
$result = $stmt->get_result();

// Prepare statistics data
$professors = [];
$totalCourses = 0;
$professorsWithCourses = 0;

while ($row = $result->fetch_assoc()) {
    $professors[] = $row;
    $courseCount = $row['courses'] ? count(explode(', ', $row['courses'])) : 0;
    $totalCourses += $courseCount;
    if ($courseCount > 0) $professorsWithCourses++;
}

$avgCoursesPerProf = $professors ? round($totalCourses / count($professors), 1) : 0;
$totalProfessors = count($professors);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($building_name) ?> Professors</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
            color: #333;
        }

        .container {
            max-width: 1400px;
            margin: 0 auto;
            background: rgba(255, 255, 255, 0.95);
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.1);
            backdrop-filter: blur(10px);
            overflow: hidden;
        }

        .header {
            background: linear-gradient(135deg, #3498db 0%, #2c3e50 100%);
            color: white;
            padding: 30px 40px;
            text-align: center;
        }

        .header h1 {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 10px;
            text-shadow: 0 2px 4px rgba(0,0,0,0.3);
        }

        .header p {
            font-size: 1.1rem;
            opacity: 0.9;
        }

        .search-section {
            padding: 30px;
            background: #f8f9fa;
            border-bottom: 1px solid #e9ecef;
        }

        .search-form {
            display: grid;
            grid-template-columns: 1fr auto auto;
            gap: 15px;
            align-items: center;
        }

        .form-group {
            position: relative;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: #495057;
            font-size: 0.9rem;
        }

        .form-control {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid #e9ecef;
            border-radius: 10px;
            font-size: 1rem;
            transition: all 0.3s ease;
            background: white;
        }

        .form-control:focus {
            outline: none;
            border-color: #3498db;
            box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.1);
        }

        .btn {
            padding: 12px 20px;
            border: none;
            border-radius: 10px;
            font-size: 1rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            height: 46px;
            margin-top: 24px;
        }

        .btn-primary {
            background: linear-gradient(135deg, #3498db, #2980b9);
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(52, 152, 219, 0.3);
        }

        .btn-secondary {
            background: #6c757d;
            color: white;
        }

        .btn-secondary:hover {
            background: #5a6268;
            transform: translateY(-2px);
        }

        .table-section {
            padding: 30px;
        }

        .stats-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: linear-gradient(135deg, #fff, #f8f9fa);
            padding: 20px;
            border-radius: 15px;
            text-align: center;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
            border: 1px solid #e9ecef;
        }

        .stat-card i {
            font-size: 2.5rem;
            margin-bottom: 10px;
        }

        .stat-card .number {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 5px;
        }

        .stat-card .label {
            color: #6c757d;
            font-size: 0.9rem;
        }

        .stats-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding: 20px;
            background: linear-gradient(135deg, #f8f9fa, #e9ecef);
            border-radius: 15px;
        }

        .stats-info {
            font-size: 1.1rem;
            font-weight: 500;
            color: #495057;
        }

        .table-container {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }

        .table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.95rem;
        }

        .table th {
            background: linear-gradient(135deg, #3498db, #2980b9);
            color: white;
            padding: 18px 15px;
            text-align: left;
            font-weight: 600;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .table td {
            padding: 15px;
            border-bottom: 1px solid #f1f3f4;
            vertical-align: middle;
        }

        .table tbody tr {
            transition: all 0.2s ease;
        }

        .table tbody tr:hover {
            background: linear-gradient(135deg, #f8f9fa, #e9ecef);
            transform: scale(1.01);
        }

        .badge {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 500;
            text-align: center;
        }

        .badge-primary {
            background: #e3f2fd;
            color: #1976d2;
        }

        .badge-success {
            background: #e8f5e8;
            color: #2e7d32;
        }

        .badge-warning {
            background: #fff3e0;
            color: #f57c00;
        }

        .badge-info {
            background: #e0f2f1;
            color: #00695c;
        }

        .badge-orange {
            background: #fff3e0;
            color: #e65100;
        }

        .professor-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(135deg, #3498db, #2980b9);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
            font-size: 1.1rem;
            margin-right: 10px;
        }

        .professor-info {
            display: flex;
            align-items: center;
        }

        .professor-details {
            display: flex;
            flex-direction: column;
        }

        .professor-name {
            font-weight: 600;
            color: #2c3e50;
        }

        .professor-username {
            font-size: 0.8rem;
            color: #7f8c8d;
        }

        .course-list {
            display: flex;
            flex-wrap: wrap;
            gap: 5px;
        }

        .course-badge {
            background: #f8f9fa;
            border: 1px solid #e9ecef;
            padding: 4px 8px;
            border-radius: 15px;
            font-size: 0.75rem;
            color: #495057;
        }

        .no-results {
            text-align: center;
            padding: 60px 20px;
            color: #6c757d;
        }

        .no-results i {
            font-size: 4rem;
            margin-bottom: 20px;
            opacity: 0.3;
        }

        .pagination {
            margin-top: 30px;
            display: flex;
            justify-content: center;
        }

        .pagination .page-item {
            margin: 0 5px;
        }

        .pagination .page-link {
            padding: 8px 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
            color: #3498db;
            text-decoration: none;
            transition: all 0.3s;
        }

        .pagination .page-item.active .page-link {
            background: #3498db;
            color: white;
            border-color: #3498db;
        }

        .pagination .page-link:hover {
            background: #f8f9fa;
        }

        @media (max-width: 768px) {
            .container {
                margin: 10px;
                border-radius: 15px;
            }
            
            .header {
                padding: 20px;
            }
            
            .header h1 {
                font-size: 1.8rem;
            }
            
            .search-section, .table-section {
                padding: 20px;
            }
            
            .search-form {
                grid-template-columns: 1fr;
            }
            
            .stats-cards {
                grid-template-columns: 1fr;
            }
            
            .table-container {
                overflow-x: auto;
            }
            
            .table {
                min-width: 700px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1><i class="fas fa-chalkboard-teacher"></i> <?= htmlspecialchars($building_name) ?> Professors</h1>
            <p>Department faculty directory and course assignments</p>
        </div>

        <div class="search-section">
            <form method="GET" class="search-form">
                <div class="form-group">
                    <label for="search"><i class="fas fa-search"></i> Search Professors</label>
                    <input type="text" id="search" name="search" class="form-control" 
                           placeholder="Search by professor name..." value="<?= htmlspecialchars($search); ?>">
                </div>
                
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-search"></i> Search
                </button>
                
                <a href="professors_view.php" class="btn btn-secondary">
                    <i class="fas fa-refresh"></i> Reset
                </a>
            </form>
        </div>

        <div class="table-section">
            <div class="stats-cards">
                <div class="stat-card">
                    <i class="fas fa-chalkboard-teacher" style="color: #3498db;"></i>
                    <div class="number"><?= $totalProfessors ?></div>
                    <div class="label">Total Professors</div>
                </div>
                
                <div class="stat-card">
                    <i class="fas fa-book" style="color: #2ecc71;"></i>
                    <div class="number"><?= $totalCourses ?></div>
                    <div class="label">Total Courses</div>
                </div>
                
                <div class="stat-card">
                    <i class="fas fa-user-graduate" style="color: #9b59b6;"></i>
                    <div class="number"><?= $professorsWithCourses ?></div>
                    <div class="label">Professors with Courses</div>
                </div>
                
                <div class="stat-card">
                    <i class="fas fa-chart-line" style="color: #f1c40f;"></i>
                    <div class="number"><?= $avgCoursesPerProf ?></div>
                    <div class="label">Avg Courses/Prof</div>
                </div>
            </div>

            <div class="stats-bar">
                <div class="stats-info">
                    <i class="fas fa-building"></i> 
                    Showing professors from <?= htmlspecialchars($building_name) ?>
                </div>
                <div class="stats-info">
                    <i class="fas fa-filter"></i> 
                    <?php 
                    $activeFilters = $search ? 1 : 0;
                    echo $activeFilters ? "$activeFilters filter(s) active" : "No filters applied";
                    ?>
                </div>
            </div>

            <div class="table-container">
                <?php if ($totalProfessors > 0) : ?>
                    <table class="table">
                        <thead>
                            <tr>
                                <th><i class="fas fa-user"></i> Professor</th>
                                <th><i class="fas fa-at"></i> Username</th>
                                <th><i class="fas fa-book"></i> Courses</th>
                                <th><i class="fas fa-chart-bar"></i> Course Count</th>
                                <th><i class="fas fa-tag"></i> Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($professors as $professor) : 
                                $courseCount = $professor['courses'] ? count(explode(', ', $professor['courses'])) : 0;
                            ?>
                                <tr>
                                    <td>
                                        <div class="professor-info">
                                            <div class="professor-avatar">
                                                <?= strtoupper(substr($professor['name'], 0, 1)) ?>
                                            </div>
                                            <div class="professor-details">
                                                <div class="professor-name"><?= htmlspecialchars($professor['name']) ?></div>
                                                <div class="professor-username">ID: <?= $professor['id'] ?></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge badge-info">
                                            @<?= htmlspecialchars($professor['username']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="course-list">
                                            <?php if ($professor['courses']) : ?>
                                                <?php foreach (explode(', ', $professor['courses']) as $course) : ?>
                                                    <span class="course-badge"><?= htmlspecialchars($course) ?></span>
                                                <?php endforeach; ?>
                                            <?php else : ?>
                                                <span class="badge badge-warning">No courses assigned</span>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge badge-orange">
                                            <?= $courseCount ?> course(s)
                                        </span>
                                    </td>
                                    <td>
                                        <?php 
                                        if ($courseCount == 0) {
                                            echo '<span class="badge badge-warning">Inactive</span>';
                                        } elseif ($courseCount <= 2) {
                                            echo '<span class="badge badge-success">Active</span>';
                                        } else {
                                            echo '<span class="badge badge-primary">Highly Active</span>';
                                        }
                                        ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else : ?>
                    <div class="no-results">
                        <i class="fas fa-user-slash"></i>
                        <h3>No professors found</h3>
                        <p>Try adjusting your search criteria or clear the filters to see all professors.</p>
                    </div>
                <?php endif; ?>
            </div>
            
            <div style="text-align: center; margin-top: 30px;">
                <a href="professor_dashboard.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back to Dashboard
                </a>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Add loading state to search button
            const searchForm = document.querySelector('.search-form');
            const searchBtn = document.querySelector('.btn-primary');
            
            if (searchForm && searchBtn) {
                searchForm.addEventListener('submit', function() {
                    searchBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Searching...';
                    searchBtn.disabled = true;
                });
            }

            // Animate stats cards on load
            const statCards = document.querySelectorAll('.stat-card');
            statCards.forEach((card, index) => {
                card.style.opacity = '0';
                card.style.transform = 'translateY(20px)';
                setTimeout(() => {
                    card.style.transition = 'all 0.5s ease';
                    card.style.opacity = '1';
                    card.style.transform = 'translateY(0)';
                }, index * 100);
            });
        });
    </script>
</body>
</html>