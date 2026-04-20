<?php
include('config.php');

// Initialize search variables
$search_building = isset($_GET['building']) ? $_GET['building'] : '';
$search_course = isset($_GET['course']) ? $_GET['course'] : '';
$search_semester = isset($_GET['semester']) ? $_GET['semester'] : '';

// Build the search query with proper joins
$whereConditions = [];
$params = [];
$types = '';

if (!empty($search_building)) {
    $whereConditions[] = "b.building_name LIKE ?";
    $params[] = "%$search_building%";
    $types .= 's';
}

if (!empty($search_course)) {
    $whereConditions[] = "c.name LIKE ?";
    $params[] = "%$search_course%";
    $types .= 's';
}

if (!empty($search_semester)) {
    $whereConditions[] = "s.name LIKE ?";
    $params[] = "%$search_semester%";
    $types .= 's';
}

$whereClause = '';
if (!empty($whereConditions)) {
    $whereClause = 'WHERE ' . implode(' AND ', $whereConditions);
}

// Main query with proper joins
$query = "SELECT c.*, 
                 b.building_name, 
                 s.name as semester_name,
                 p.name as professor_name
          FROM courses c 
          LEFT JOIN buildings b ON c.building_id = b.id 
          LEFT JOIN semester s ON c.semester_id = s.id
          LEFT JOIN professors p ON c.professor_id = p.id
          $whereClause 
          ORDER BY c.name ASC";

$stmt = $con->prepare($query);

if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$resultCourses = $stmt->get_result();

// Get distinct values for dropdown filters
$buildingsQuery = $con->query("SELECT DISTINCT b.id, b.building_name FROM buildings b 
                               INNER JOIN courses c ON b.id = c.building_id 
                               ORDER BY b.building_name");

$semestersQuery = $con->query("SELECT DISTINCT s.id, s.name FROM semester s 
                               INNER JOIN courses c ON s.id = c.semester_id 
                               ORDER BY s.name");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Course Management System</title>
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
            background: linear-gradient(135deg, #2c3e50 0%, #3498db 100%);
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
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
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
            border-color: #3498db;
            box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.1);
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
            background: linear-gradient(135deg, #2c3e50, #34495e);
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
            vertical-align: top;
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
            padding: 4px 12px;
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

        .projector-icon {
            color: #28a745;
            margin-left: 5px;
        }

        .error-message {
            background: #f8d7da;
            color: #721c24;
            padding: 15px;
            border-radius: 10px;
            margin: 20px;
            border: 1px solid #f5c6cb;
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
            
            .table-container {
                overflow-x: auto;
            }
            
            .table {
                min-width: 800px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1><i class="fas fa-graduation-cap"></i> Course list</h1>
            <p>Comprehensive course catalog and search interface</p>
        </div>

        <div class="search-section">
            <form method="GET" class="search-form">
                <div class="form-group">
                    <label for="course"><i class="fas fa-book"></i> Course Name</label>
                    <input type="text" id="course" name="course" class="form-control" 
                           placeholder="Search by course name..." value="<?php echo htmlspecialchars($search_course); ?>">
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

                <div class="form-group">
                    <label for="semester"><i class="fas fa-calendar-alt"></i> Semester</label>
                    <select id="semester" name="semester" class="form-control">
                        <option value="">All Semesters</option>
                        <?php 
                        if ($semestersQuery && $semestersQuery->num_rows > 0) {
                            while ($semester = $semestersQuery->fetch_assoc()) : ?>
                                <option value="<?php echo htmlspecialchars($semester['name']); ?>" 
                                        <?php echo ($search_semester == $semester['name']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($semester['name']); ?>
                                </option>
                            <?php endwhile;
                        } ?>
                    </select>
                </div>

                <div class="btn-group">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search"></i> Search Courses
                    </button>
                    <a href="<?php echo $_SERVER['PHP_SELF']; ?>" class="btn btn-secondary">
                        <i class="fas fa-refresh"></i> Clear Filters
                    </a>
                </div>
            </form>
        </div>

        <div class="table-section">
            <?php 
            if (!$resultCourses) {
                echo '<div class="error-message">Error: Unable to fetch courses. Please check your database connection and table structure.</div>';
            } else {
                $totalCourses = $resultCourses->num_rows;
            ?>
            <div class="stats-bar">
                <div class="stats-info">
                    <i class="fas fa-chart-bar"></i> 
                    Showing <?php echo $totalCourses; ?> course(s)
                </div>
                <div class="stats-info">
                    <i class="fas fa-filter"></i> 
                    <?php 
                    $activeFilters = 0;
                    if (!empty($search_course)) $activeFilters++;
                    if (!empty($search_building)) $activeFilters++;
                    if (!empty($search_semester)) $activeFilters++;
                    echo $activeFilters > 0 ? "$activeFilters filter(s) active" : "No filters applied";
                    ?>
                </div>
            </div>

            <div class="table-container">
                <?php if ($totalCourses > 0) : ?>
                    <table class="table">
                        <thead>
                            <tr>
                                <th><i class="fas fa-book"></i> Course Name</th>
                                <th><i class="fas fa-star"></i> Credits</th>
                                <th><i class="fas fa-clock"></i> Hours</th>
                                <th><i class="fas fa-user-tie"></i> Professor</th>
                                <th><i class="fas fa-calendar"></i> Semester</th>
                                <th><i class="fas fa-building"></i> Building</th>
                                <th><i class="fas fa-users"></i> Enrolled</th>
                                <th><i class="fas fa-tag"></i> Type</th>
                                <th><i class="fas fa-video"></i> Equipment</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($course = $resultCourses->fetch_assoc()) : ?>
                                <tr>
                                    <td>
                                        <strong><?php echo htmlspecialchars($course['name']); ?></strong>
                                    </td>
                                    <td>
                                        <span class="badge badge-primary">
                                            <?php echo htmlspecialchars($course['credits']); ?> Credits
                                        </span>
                                    </td>
                                    <td>
                                        <small>
                                            L: <?php echo htmlspecialchars($course['lecture_hours'] ?? '0'); ?> | 
                                            T: <?php echo htmlspecialchars($course['tutorial_hours'] ?? '0'); ?> | 
                                            P: <?php echo htmlspecialchars($course['practical_hours'] ?? '0'); ?>
                                        </small>
                                    </td>
                                    <td><?php echo htmlspecialchars($course['professor_name'] ?? 'TBA'); ?></td>
                                    <td>
                                        <span class="badge badge-info">
                                            <?php echo htmlspecialchars($course['semester_name'] ?? 'N/A'); ?>
                                        </span>
                                    </td>
                                    <td><?php echo htmlspecialchars($course['building_name'] ?? 'N/A'); ?></td>
                                    <td>
                                        <span class="badge badge-success">
                                            <?php echo htmlspecialchars($course['students_enrolled'] ?? '0'); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge badge-warning">
                                            <?php echo htmlspecialchars($course['course_type'] ?? 'General'); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if (isset($course['requires_projector']) && $course['requires_projector'] == 1) : ?>
                                            <i class="fas fa-video projector-icon" title="Projector Required"></i>
                                        <?php else : ?>
                                            <i class="fas fa-times" style="color: #dc3545;" title="No Projector Required"></i>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                <?php else : ?>
                    <div class="no-results">
                        <i class="fas fa-search"></i>
                        <h3>No courses found</h3>
                        <p>Try adjusting your search criteria or clear the filters to see all courses.</p>
                    </div>
                <?php endif; ?>
            </div>
            <?php } ?>
        </div>
    </div>

    <script>
        // Add smooth scrolling and enhanced interactions
        document.addEventListener('DOMContentLoaded', function() {
            // Auto-focus on course search input
            const courseInput = document.getElementById('course');
            if (courseInput && !courseInput.value) {
                courseInput.focus();
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