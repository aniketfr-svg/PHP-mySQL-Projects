<?php
include('config.php');

// Initialize search variables
$search_professor = isset($_GET['professor']) ? $_GET['professor'] : '';
$search_course = isset($_GET['course']) ? $_GET['course'] : '';
$search_building = isset($_GET['building']) ? $_GET['building'] : '';
$search_username = isset($_GET['username']) ? $_GET['username'] : '';

// Build the search query with proper joins
$whereConditions = [];
$params = [];
$types = '';

if (!empty($search_professor)) {
    $whereConditions[] = "p.name LIKE ?";
    $params[] = "%$search_professor%";
    $types .= 's';
}

if (!empty($search_course)) {
    $whereConditions[] = "c.name LIKE ?";
    $params[] = "%$search_course%";
    $types .= 's';
}

if (!empty($search_building)) {
    $whereConditions[] = "b.building_name LIKE ?";
    $params[] = "%$search_building%";
    $types .= 's';
}

if (!empty($search_username)) {
    $whereConditions[] = "p.username LIKE ?";
    $params[] = "%$search_username%";
    $types .= 's';
}

$whereClause = '';
if (!empty($whereConditions)) {
    $whereClause = 'WHERE ' . implode(' AND ', $whereConditions);
}

// Main query with proper joins
$query = "SELECT p.*, 
                 b.building_name,
                 c.name as course_name,
                 c.id as course_id
          FROM professors p 
          LEFT JOIN buildings b ON p.building_id = b.id 
          LEFT JOIN courses c ON c.professor_id = p.id
          $whereClause 
          ORDER BY p.name ASC";

$stmt = $con->prepare($query);

if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$resultProfessors = $stmt->get_result();

// Get distinct values for dropdown filters
$buildingsQuery = $con->query("SELECT DISTINCT b.id, b.building_name FROM buildings b 
                               INNER JOIN professors p ON b.id = p.building_id 
                               ORDER BY b.building_name");

$coursesQuery = $con->query("SELECT DISTINCT c.name FROM courses c 
                            ORDER BY c.name");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Professor Management System</title>
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
            background: linear-gradient(135deg, #e67e22 0%, #f39c12 100%);
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
            padding: 40px;
            background: #f8f9fa;
            border-bottom: 1px solid #e9ecef;
        }

        .search-form {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 20px;
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
            border-color: #e67e22;
            box-shadow: 0 0 0 3px rgba(230, 126, 34, 0.1);
        }

        .btn-group {
            display: flex;
            gap: 15px;
            justify-content: center;
            margin-top: 20px;
        }

        .btn {
            padding: 12px 30px;
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
        }

        .btn-primary {
            background: linear-gradient(135deg, #e67e22, #d35400);
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(230, 126, 34, 0.3);
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
            padding: 40px;
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
            background: linear-gradient(135deg, #e67e22, #f39c12);
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
            background: linear-gradient(135deg, #e67e22, #f39c12);
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

        @media (max-width: 768px) {
            .container {
                margin: 10px;
                border-radius: 15px;
            }
            
            .header {
                padding: 20px;
            }
            
            .header h1 {
                font-size: 2rem;
            }
            
            .search-section, .table-section {
                padding: 20px;
            }
            
            .search-form {
                grid-template-columns: 1fr;
            }
            
            .btn-group {
                flex-direction: column;
            }
            
            .stats-bar {
                flex-direction: column;
                gap: 10px;
                text-align: center;
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
            <h1><i class="fas fa-chalkboard-teacher"></i> Professor List</h1>
            <p>Comprehensive faculty directory and course assignments</p>
        </div>

        <div class="search-section">
            <form method="GET" class="search-form">
                <div class="form-group">
                    <label for="professor"><i class="fas fa-user"></i> Professor Name</label>
                    <input type="text" id="professor" name="professor" class="form-control" 
                           placeholder="Search by professor name..." value="<?php echo htmlspecialchars($search_professor); ?>">
                </div>

                <div class="form-group">
                    <label for="username"><i class="fas fa-at"></i> Username</label>
                    <input type="text" id="username" name="username" class="form-control" 
                           placeholder="Search by username..." value="<?php echo htmlspecialchars($search_username); ?>">
                </div>

                <div class="form-group">
                    <label for="course"><i class="fas fa-book"></i> Course</label>
                    <select id="course" name="course" class="form-control">
                        <option value="">All Courses</option>
                        <?php 
                        if ($coursesQuery && $coursesQuery->num_rows > 0) {
                            while ($course = $coursesQuery->fetch_assoc()) : ?>
                                <option value="<?php echo htmlspecialchars($course['name']); ?>" 
                                        <?php echo ($search_course == $course['name']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($course['name']); ?>
                                </option>
                            <?php endwhile;
                        } ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="building"><i class="fas fa-building"></i> Building</label>
                    <select id="building" name="building" class="form-control">
                        <option value="">All Buildings</option>
                        <?php 
                        if ($buildingsQuery && $buildingsQuery->num_rows > 0) {
                            while ($building = $buildingsQuery->fetch_assoc()) : ?>
                                <option value="<?php echo htmlspecialchars($building['building_name']); ?>" 
                                        <?php echo ($search_building == $building['building_name']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($building['building_name']); ?>
                                </option>
                            <?php endwhile;
                        } ?>
                    </select>
                </div>

                <div class="btn-group">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search"></i> Search Professors
                    </button>
                    <a href="<?php echo $_SERVER['PHP_SELF']; ?>" class="btn btn-secondary">
                        <i class="fas fa-refresh"></i> Clear Filters
                    </a>
                </div>
            </form>
        </div>

        <div class="table-section">
            <?php 
            if (!$resultProfessors) {
                echo '<div class="error-message">Error: Unable to fetch professors. Please check your database connection and table structure.</div>';
            } else {
                // Group professors and their courses
                $professorsData = [];
                while ($row = $resultProfessors->fetch_assoc()) {
                    $profId = $row['id'];
                    if (!isset($professorsData[$profId])) {
                        $professorsData[$profId] = [
                            'id' => $row['id'],
                            'name' => $row['name'],
                            'username' => $row['username'],
                            'building_name' => $row['building_name'],
                            'courses' => []
                        ];
                    }
                    if ($row['course_name']) {
                        $professorsData[$profId]['courses'][] = $row['course_name'];
                    }
                }
                
                $totalProfessors = count($professorsData);
                $totalCourses = 0;
                $professorsWithCourses = 0;
                $buildingCount = [];
                
                foreach ($professorsData as $prof) {
                    $totalCourses += count($prof['courses']);
                    if (count($prof['courses']) > 0) $professorsWithCourses++;
                    if ($prof['building_name']) {
                        $buildingCount[$prof['building_name']] = ($buildingCount[$prof['building_name']] ?? 0) + 1;
                    }
                }
                
                $avgCoursesPerProf = $totalProfessors > 0 ? round($totalCourses / $totalProfessors, 1) : 0;
                $uniqueBuildings = count($buildingCount);
            ?>
            
            <div class="stats-cards">
                <div class="stat-card">
                    <i class="fas fa-chalkboard-teacher" style="color: #e67e22;"></i>
                    <div class="number"><?php echo $totalProfessors; ?></div>
                    <div class="label">Total Professors</div>
                </div>
                <div class="stat-card">
                    <i class="fas fa-book" style="color: #27ae60;"></i>
                    <div class="number"><?php echo $totalCourses; ?></div>
                    <div class="label">Total Courses</div>
                </div>
                <div class="stat-card">
                    <i class="fas fa-building" style="color: #3498db;"></i>
                    <div class="number"><?php echo $uniqueBuildings; ?></div>
                    <div class="label">Buildings</div>
                </div>
                <div class="stat-card">
                    <i class="fas fa-chart-line" style="color: #9b59b6;"></i>
                    <div class="number"><?php echo $avgCoursesPerProf; ?></div>
                    <div class="label">Avg Courses/Prof</div>
                </div>
            </div>

            <div class="stats-bar">
                <div class="stats-info">
                    <i class="fas fa-chart-bar"></i> 
                    Showing <?php echo $totalProfessors; ?> professor(s)
                </div>
                <div class="stats-info">
                    <i class="fas fa-filter"></i> 
                    <?php 
                    $activeFilters = 0;
                    if (!empty($search_professor)) $activeFilters++;
                    if (!empty($search_username)) $activeFilters++;
                    if (!empty($search_course)) $activeFilters++;
                    if (!empty($search_building)) $activeFilters++;
                    echo $activeFilters > 0 ? "$activeFilters filter(s) active" : "No filters applied";
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
                                <th><i class="fas fa-building"></i> Building</th>
                                <th><i class="fas fa-book"></i> Courses</th>
                                <th><i class="fas fa-chart-bar"></i> Course Count</th>
                                <th><i class="fas fa-tag"></i> Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($professorsData as $professor) : ?>
                                <tr>
                                    <td>
                                        <div class="professor-info">
                                            <div class="professor-avatar">
                                                <?php echo strtoupper(substr($professor['name'], 0, 1)); ?>
                                            </div>
                                            <div class="professor-details">
                                                <div class="professor-name"><?php echo htmlspecialchars($professor['name']); ?></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge badge-info">
                                            @<?php echo htmlspecialchars($professor['username']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge badge-primary">
                                            <?php echo htmlspecialchars($professor['building_name'] ?? 'N/A'); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="course-list">
                                            <?php if (!empty($professor['courses'])) : ?>
                                                <?php foreach ($professor['courses'] as $course) : ?>
                                                    <span class="course-badge"><?php echo htmlspecialchars($course); ?></span>
                                                <?php endforeach; ?>
                                            <?php else : ?>
                                                <span class="badge badge-warning">No courses assigned</span>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge badge-orange">
                                            <?php echo count($professor['courses']); ?> course(s)
                                        </span>
                                    </td>
                                    <td>
                                        <?php 
                                        $courseCount = count($professor['courses']);
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
            <?php } ?>
        </div>
    </div>

    <script>
        // Add smooth scrolling and enhanced interactions
        document.addEventListener('DOMContentLoaded', function() {
            // Auto-focus on professor search input
            const professorInput = document.getElementById('professor');
            if (professorInput && !professorInput.value) {
                professorInput.focus();
            }

            // Add loading state to search button
            const searchForm = document.querySelector('.search-form');
            const searchBtn = document.querySelector('.btn-primary');
            
            if (searchForm && searchBtn) {
                searchForm.addEventListener('submit', function() {
                    searchBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Searching...';
                    searchBtn.disabled = true;
                });
            }

            // Add keyboard shortcut for search (Ctrl+Enter)
            document.addEventListener('keydown', function(e) {
                if (e.ctrlKey && e.key === 'Enter') {
                    searchForm.submit();
                }
            });

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

<?php 
if (isset($stmt)) {
    $stmt->close();
}
$con->close(); 
?>