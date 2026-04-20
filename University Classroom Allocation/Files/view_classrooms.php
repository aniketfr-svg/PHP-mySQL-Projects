<?php
include('config.php');

// Initialize search variables
$search_building = isset($_GET['building']) ? $_GET['building'] : '';
$search_classroom = isset($_GET['classroom']) ? $_GET['classroom'] : '';
$search_capacity = isset($_GET['capacity']) ? $_GET['capacity'] : '';
$search_projector = isset($_GET['projector']) ? $_GET['projector'] : '';

// Build the search query with proper joins
$whereConditions = [];
$params = [];
$types = '';

if (!empty($search_building)) {
    $whereConditions[] = "b.building_name LIKE ?";
    $params[] = "%$search_building%";
    $types .= 's';
}

if (!empty($search_classroom)) {
    $whereConditions[] = "c.name LIKE ?";
    $params[] = "%$search_classroom%";
    $types .= 's';
}

if (!empty($search_capacity)) {
    $whereConditions[] = "c.capacity >= ?";
    $params[] = $search_capacity;
    $types .= 'i';
}

if (!empty($search_projector) && $search_projector !== 'all') {
    $whereConditions[] = "c.has_projector = ?";
    $params[] = ($search_projector === 'yes') ? 1 : 0;
    $types .= 'i';
}

$whereClause = '';
if (!empty($whereConditions)) {
    $whereClause = 'WHERE ' . implode(' AND ', $whereConditions);
}

// Main query with proper joins
$query = "SELECT c.*, 
                 b.building_name
          FROM classrooms c 
          LEFT JOIN buildings b ON c.building_id = b.id 
          $whereClause 
          ORDER BY c.name ASC";

$stmt = $con->prepare($query);

if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$resultClassrooms = $stmt->get_result();

// Get distinct values for dropdown filters
$buildingsQuery = $con->query("SELECT DISTINCT b.id, b.building_name FROM buildings b 
                               INNER JOIN classrooms c ON b.id = c.building_id 
                               ORDER BY b.building_name");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Classroom Management System</title>
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
            background: linear-gradient(135deg, #8e44ad 0%, #9b59b6 100%);
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
            border-color: #8e44ad;
            box-shadow: 0 0 0 3px rgba(142, 68, 173, 0.1);
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
            background: linear-gradient(135deg, #8e44ad, #7d3c98);
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(142, 68, 173, 0.3);
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
            background: linear-gradient(135deg, #8e44ad, #9b59b6);
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

        .badge-purple {
            background: #f3e5f5;
            color: #7b1fa2;
        }

        .capacity-bar {
            width: 100%;
            height: 8px;
            background: #e9ecef;
            border-radius: 4px;
            overflow: hidden;
            margin-top: 5px;
        }

        .capacity-fill {
            height: 100%;
            background: linear-gradient(90deg, #28a745, #20c997);
            border-radius: 4px;
            transition: width 0.3s ease;
        }

        .projector-status {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .projector-yes {
            color: #28a745;
        }

        .projector-no {
            color: #dc3545;
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
                min-width: 600px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1><i class="fas fa-door-open"></i> Classroom List</h1>
            <p>Comprehensive classroom directory and availability tracker</p>
        </div>

        <div class="search-section">
            <form method="GET" class="search-form">
                <div class="form-group">
                    <label for="classroom"><i class="fas fa-door-open"></i> Classroom Name</label>
                    <input type="text" id="classroom" name="classroom" class="form-control" 
                           placeholder="Search by classroom name..." value="<?php echo htmlspecialchars($search_classroom); ?>">
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
                    <label for="capacity"><i class="fas fa-users"></i> Min Capacity</label>
                    <input type="number" id="capacity" name="capacity" class="form-control" 
                           placeholder="Minimum capacity..." value="<?php echo htmlspecialchars($search_capacity); ?>" min="1">
                </div>

                <div class="form-group">
                    <label for="projector"><i class="fas fa-video"></i> Projector</label>
                    <select id="projector" name="projector" class="form-control">
                        <option value="">All Rooms</option>
                        <option value="yes" <?php echo ($search_projector == 'yes') ? 'selected' : ''; ?>>With Projector</option>
                        <option value="no" <?php echo ($search_projector == 'no') ? 'selected' : ''; ?>>Without Projector</option>
                    </select>
                </div>

                <div class="btn-group">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search"></i> Search Classrooms
                    </button>
                    <a href="<?php echo $_SERVER['PHP_SELF']; ?>" class="btn btn-secondary">
                        <i class="fas fa-refresh"></i> Clear Filters
                    </a>
                </div>
            </form>
        </div>

        <div class="table-section">
            <?php 
            if (!$resultClassrooms) {
                echo '<div class="error-message">Error: Unable to fetch classrooms. Please check your database connection and table structure.</div>';
            } else {
                $totalClassrooms = $resultClassrooms->num_rows;
                
                // Calculate statistics
                $totalCapacity = 0;
                $withProjector = 0;
                $maxCapacity = 0;
                
                // Store results in array for statistics calculation
                $classrooms = [];
                while ($classroom = $resultClassrooms->fetch_assoc()) {
                    $classrooms[] = $classroom;
                    $totalCapacity += $classroom['capacity'];
                    if ($classroom['has_projector']) $withProjector++;
                    if ($classroom['capacity'] > $maxCapacity) $maxCapacity = $classroom['capacity'];
                }
                
                $avgCapacity = $totalClassrooms > 0 ? round($totalCapacity / $totalClassrooms) : 0;
            ?>
            
            <div class="stats-cards">
                <div class="stat-card">
                    <i class="fas fa-door-open" style="color: #8e44ad;"></i>
                    <div class="number"><?php echo $totalClassrooms; ?></div>
                    <div class="label">Total Classrooms</div>
                </div>
                <div class="stat-card">
                    <i class="fas fa-users" style="color: #28a745;"></i>
                    <div class="number"><?php echo $totalCapacity; ?></div>
                    <div class="label">Total Capacity</div>
                </div>
                <div class="stat-card">
                    <i class="fas fa-video" style="color: #17a2b8;"></i>
                    <div class="number"><?php echo $withProjector; ?></div>
                    <div class="label">With Projector</div>
                </div>
                <div class="stat-card">
                    <i class="fas fa-chart-line" style="color: #ffc107;"></i>
                    <div class="number"><?php echo $avgCapacity; ?></div>
                    <div class="label">Avg Capacity</div>
                </div>
            </div>

            <div class="stats-bar">
                <div class="stats-info">
                    <i class="fas fa-chart-bar"></i> 
                    Showing <?php echo $totalClassrooms; ?> classroom(s)
                </div>
                <div class="stats-info">
                    <i class="fas fa-filter"></i> 
                    <?php 
                    $activeFilters = 0;
                    if (!empty($search_classroom)) $activeFilters++;
                    if (!empty($search_building)) $activeFilters++;
                    if (!empty($search_capacity)) $activeFilters++;
                    if (!empty($search_projector)) $activeFilters++;
                    echo $activeFilters > 0 ? "$activeFilters filter(s) active" : "No filters applied";
                    ?>
                </div>
            </div>

            <div class="table-container">
                <?php if ($totalClassrooms > 0) : ?>
                    <table class="table">
                        <thead>
                            <tr>
                                <th><i class="fas fa-door-open"></i> Classroom Name</th>
                                <th><i class="fas fa-building"></i> Building</th>
                                <th><i class="fas fa-users"></i> Capacity</th>
                                <th><i class="fas fa-chart-bar"></i> Capacity Visual</th>
                                <th><i class="fas fa-video"></i> Projector</th>
                                <th><i class="fas fa-tag"></i> Size Category</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($classrooms as $classroom) : ?>
                                <tr>
                                    <td>
                                        <strong><?php echo htmlspecialchars($classroom['name']); ?></strong>
                                    </td>
                                    <td>
                                        <span class="badge badge-info">
                                            <?php echo htmlspecialchars($classroom['building_name'] ?? 'N/A'); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge badge-primary">
                                            <?php echo htmlspecialchars($classroom['capacity']); ?> seats
                                        </span>
                                    </td>
                                    <td>
                                        <div class="capacity-bar">
                                            <div class="capacity-fill" style="width: <?php echo min(100, ($classroom['capacity'] / $maxCapacity) * 100); ?>%"></div>
                                        </div>
                                        <small><?php echo round(($classroom['capacity'] / $maxCapacity) * 100); ?>% of max</small>
                                    </td>
                                    <td>
                                        <div class="projector-status">
                                            <?php if ($classroom['has_projector']) : ?>
                                                <i class="fas fa-check-circle projector-yes"></i>
                                                <span class="projector-yes">Available</span>
                                            <?php else : ?>
                                                <i class="fas fa-times-circle projector-no"></i>
                                                <span class="projector-no">Not Available</span>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td>
                                        <?php 
                                        $capacity = $classroom['capacity'];
                                        if ($capacity <= 20) {
                                            echo '<span class="badge badge-warning">Small</span>';
                                        } elseif ($capacity <= 50) {
                                            echo '<span class="badge badge-success">Medium</span>';
                                        } elseif ($capacity <= 100) {
                                            echo '<span class="badge badge-primary">Large</span>';
                                        } else {
                                            echo '<span class="badge badge-purple">Auditorium</span>';
                                        }
                                        ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else : ?>
                    <div class="no-results">
                        <i class="fas fa-search"></i>
                        <h3>No classrooms found</h3>
                        <p>Try adjusting your search criteria or clear the filters to see all classrooms.</p>
                    </div>
                <?php endif; ?>
            </div>
            <?php } ?>
        </div>
    </div>

    <script>
        // Add smooth scrolling and enhanced interactions
        document.addEventListener('DOMContentLoaded', function() {
            // Auto-focus on classroom search input
            const classroomInput = document.getElementById('classroom');
            if (classroomInput && !classroomInput.value) {
                classroomInput.focus();
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

            // Animate capacity bars on load
            const capacityBars = document.querySelectorAll('.capacity-fill');
            capacityBars.forEach(bar => {
                const width = bar.style.width;
                bar.style.width = '0%';
                setTimeout(() => {
                    bar.style.width = width;
                }, 500);
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