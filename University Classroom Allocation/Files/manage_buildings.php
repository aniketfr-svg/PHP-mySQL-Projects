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

// Handle operations
$message = "";
$message_type = "";

// Handle Add Building
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] == 'add') {
    $building_name = trim($_POST['building_name']);
    
    if (!empty($building_name)) {
        try {
            $stmt = $conn->prepare("INSERT INTO buildings (building_name) VALUES (:building_name)");
            $stmt->bindParam(':building_name', $building_name);
            if ($stmt->execute()) {
                $message = "Department/Building added successfully!";
                $message_type = "success";
            }
        } catch (Exception $e) {
            $message = "Error: Unable to add department/building.";
            $message_type = "danger";
        }
    } else {
        $message = "Error: Department name is required.";
        $message_type = "danger";
    }
}

// Handle Edit Building
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] == 'edit') {
    $building_id = $_POST['building_id'];
    $building_name = trim($_POST['building_name']);
    
    if (!empty($building_name)) {
        try {
            $stmt = $conn->prepare("UPDATE buildings SET building_name = :name WHERE id = :id");
            $stmt->bindParam(':name', $building_name);
            $stmt->bindParam(':id', $building_id);
            if ($stmt->execute()) {
                $message = "Department/Building updated successfully!";
                $message_type = "success";
            }
        } catch (Exception $e) {
            $message = "Error: Unable to update department/building.";
            $message_type = "danger";
        }
    } else {
        $message = "Error: Department name is required.";
        $message_type = "danger";
    }
}

// Handle Delete Building
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $id = $_GET['delete'];
    try {
        $stmt = $conn->prepare("DELETE FROM buildings WHERE id = :id");
        $stmt->bindParam(':id', $id);
        if ($stmt->execute()) {
            $message = "Department/Building deleted successfully!";
            $message_type = "success";
        }
    } catch (Exception $e) {
        $message = "Error: Unable to delete department/building.";
        $message_type = "danger";
    }
}

// Search and Pagination
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$limit = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$start = ($page - 1) * $limit;

// Count total records
$count_stmt = $conn->prepare("SELECT COUNT(*) FROM buildings WHERE building_name LIKE :search");
$count_stmt->execute([':search' => "%$search%"]);
$total = $count_stmt->fetchColumn();
$pages = ceil($total / $limit);

// Fetch paginated records
$stmt = $conn->prepare("SELECT * FROM buildings WHERE building_name LIKE :search ORDER BY id DESC LIMIT $start, $limit");
$stmt->execute([':search' => "%$search%"]);
$buildings = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get building for editing
$edit_building = null;
if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
    $edit_id = $_GET['edit'];
    $stmt = $conn->prepare("SELECT * FROM buildings WHERE id = :id");
    $stmt->bindParam(':id', $edit_id);
    $stmt->execute();
    $edit_building = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Department Management System - Government Portal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        /* Government Color Scheme */
        :root {
            --gov-primary: #1e3a8a;
            --gov-secondary: #1e40af;
            --gov-accent: #dc2626;
            --gov-success: #059669;
            --gov-warning: #d97706;
            --gov-light: #f8fafc;
            --gov-dark: #1f2937;
            --gov-border: #d1d5db;
            --gov-text: #374151;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f3f4f6;
            color: var(--gov-text);
            line-height: 1.6;
        }

        /* Header Styles */
        .gov-header {
            background: linear-gradient(135deg, var(--gov-primary) 0%, var(--gov-secondary) 100%);
            color: white;
            padding: 0;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            border-bottom: 3px solid var(--gov-accent);
        }

        .gov-header-top {
            background: rgba(0, 0, 0, 0.1);
            padding: 8px 0;
            font-size: 0.875rem;
        }

        .gov-header-main {
            padding: 1.5rem 0;
        }

        .gov-logo {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .gov-logo-icon {
            width: 60px;
            height: 60px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
        }

        .gov-title {
            font-size: 1.75rem;
            font-weight: 700;
            margin: 0;
        }

        .gov-subtitle {
            font-size: 0.95rem;
            opacity: 0.9;
            margin: 0;
        }

        /* Navigation */
        .gov-nav {
            background: rgba(255, 255, 255, 0.1);
            padding: 0.75rem 0;
            border-top: 1px solid rgba(255, 255, 255, 0.2);
        }

        .breadcrumb {
            background: none;
            margin: 0;
            padding: 0;
        }

        .breadcrumb-item + .breadcrumb-item::before {
            content: "›";
            color: rgba(255, 255, 255, 0.7);
        }

        .breadcrumb-item a {
            color: rgba(255, 255, 255, 0.9);
            text-decoration: none;
        }

        .breadcrumb-item.active {
            color: white;
        }

        /* Main Content */
        .main-content {
            padding: 2rem 0;
        }

        .section-header {
            background: white;
            border-left: 4px solid var(--gov-primary);
            padding: 1rem 1.5rem;
            margin-bottom: 1.5rem;
            border-radius: 0 8px 8px 0;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .section-title {
            color: var(--gov-primary);
            font-size: 1.5rem;
            font-weight: 600;
            margin: 0;
        }

        .section-description {
            color: var(--gov-text);
            margin: 0.5rem 0 0 0;
            font-size: 0.95rem;
        }

        /* Cards */
        .gov-card {
            background: white;
            border: 1px solid var(--gov-border);
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            margin-bottom: 1.5rem;
        }

        .gov-card-header {
            background: #f8fafc;
            border-bottom: 1px solid var(--gov-border);
            padding: 1rem 1.5rem;
            font-weight: 600;
            color: var(--gov-primary);
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .gov-card-body {
            padding: 1.5rem;
        }

        /* Forms */
        .form-group {
            margin-bottom: 1.25rem;
        }

        .form-label {
            font-weight: 500;
            color: var(--gov-dark);
            margin-bottom: 0.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .form-control {
            border: 1px solid var(--gov-border);
            border-radius: 6px;
            padding: 0.75rem;
            font-size: 0.95rem;
            transition: all 0.2s;
        }

        .form-control:focus {
            border-color: var(--gov-primary);
            box-shadow: 0 0 0 3px rgba(30, 58, 138, 0.1);
        }

        /* Buttons */
        .btn {
            border-radius: 6px;
            font-weight: 500;
            padding: 0.75rem 1.5rem;
            border: none;
            cursor: pointer;
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            text-decoration: none;
        }

        .btn-primary {
            background: var(--gov-primary);
            color: white;
        }

        .btn-primary:hover {
            background: var(--gov-secondary);
            color: white;
        }

        .btn-success {
            background: var(--gov-success);
            color: white;
        }

        .btn-warning {
            background: var(--gov-warning);
            color: white;
        }

        .btn-danger {
            background: var(--gov-accent);
            color: white;
        }

        .btn-outline-primary {
            border: 1px solid var(--gov-primary);
            color: var(--gov-primary);
            background: transparent;
        }

        .btn-outline-primary:hover {
            background: var(--gov-primary);
            color: white;
        }

        /* Alerts */
        .alert {
            border: none;
            border-radius: 6px;
            padding: 1rem 1.25rem;
            margin-bottom: 1.5rem;
            border-left: 4px solid;
        }

        .alert-success {
            background: #ecfdf5;
            border-left-color: var(--gov-success);
            color: var(--gov-success);
        }

        .alert-danger {
            background: #fef2f2;
            border-left-color: var(--gov-accent);
            color: var(--gov-accent);
        }

        /* Table */
        .table-container {
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        }

        .table {
            margin: 0;
            font-size: 0.95rem;
        }

        .table thead th {
            background: var(--gov-primary);
            color: white;
            font-weight: 600;
            border: none;
            padding: 1rem;
            text-align: center;
        }

        .table tbody td {
            padding: 1rem;
            border-color: var(--gov-border);
            text-align: center;
            vertical-align: middle;
        }

        .table tbody tr:nth-child(even) {
            background: #f9fafb;
        }

        .table tbody tr:hover {
            background: #f3f4f6;
        }

        /* Statistics */
        .stats-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: white;
            border: 1px solid var(--gov-border);
            border-radius: 8px;
            padding: 1.5rem;
            text-align: center;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            border-left: 4px solid var(--gov-primary);
        }

        .stat-number {
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--gov-primary);
            margin-bottom: 0.25rem;
        }

        .stat-label {
            color: var(--gov-text);
            font-weight: 500;
            text-transform: uppercase;
            font-size: 0.875rem;
            letter-spacing: 0.5px;
        }

        /* Search */
        .search-container {
            background: white;
            border: 1px solid var(--gov-border);
            border-radius: 8px;
            padding: 1rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        }

        .search-form {
            display: flex;
            gap: 0.5rem;
            align-items: center;
        }

        /* Pagination */
        .pagination {
            justify-content: center;
            margin-top: 1.5rem;
        }

        .page-link {
            color: var(--gov-primary);
            border: 1px solid var(--gov-border);
            padding: 0.5rem 0.75rem;
            margin: 0 2px;
            border-radius: 4px;
        }

        .page-item.active .page-link {
            background: var(--gov-primary);
            border-color: var(--gov-primary);
            color: white;
        }

        /* Footer */
        .gov-footer {
            background: var(--gov-dark);
            color: white;
            padding: 2rem 0;
            margin-top: 3rem;
        }

        .footer-links {
            display: flex;
            justify-content: center;
            gap: 2rem;
            margin-bottom: 1rem;
        }

        .footer-links a {
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            font-size: 0.9rem;
        }

        .footer-links a:hover {
            color: white;
        }

        .footer-text {
            text-align: center;
            font-size: 0.875rem;
            opacity: 0.8;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .gov-header-main {
                text-align: center;
            }
            
            .gov-logo {
                justify-content: center;
            }
            
            .stats-container {
                grid-template-columns: 1fr;
            }
            
            .search-form {
                flex-direction: column;
                align-items: stretch;
            }
            
            .table-responsive {
                font-size: 0.85rem;
            }
        }

        /* Badge Styles */
        .badge {
            font-size: 0.75rem;
            padding: 0.35em 0.65em;
            border-radius: 0.375rem;
        }

        .badge-primary {
            background: var(--gov-primary);
            color: white;
        }

        /* Action Buttons */
        .action-buttons {
            display: flex;
            gap: 0.25rem;
            justify-content: center;
        }

        .btn-sm {
            padding: 0.375rem 0.75rem;
            font-size: 0.875rem;
        }
    </style>
</head>
<body>
    <!-- Government Header -->
    <header class="gov-header">
        <div class="gov-header-top">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-md-6">
                        <small><i class="fas fa-phone"></i> Helpline: 1800-XXX-XXXX</small>
                    </div>
                    <div class="col-md-6 text-end">
                        <small> <i class="fas fa-user"></i> Welcome, <?php echo $_SESSION['username'] ?? 'Administrator'; ?></small>
                    </div>
                </div>
            </div>
        </div>
        
        
        <nav class="gov-nav">
            <div class="container-fluid">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                        <li class="breadcrumb-item"><a href="#">Administration</a></li>
                        <li class="breadcrumb-item active">Department Management</li>
                    </ol>
                </nav>
            </div>
           <!-- <div class="col-md-4 text-end">
                        <div class="text-white">
                            <small><i class="fas fa-calendar"></i> <?php echo date('d M Y, l'); ?></small><br>
                            <small><i class="fas fa-clock"></i> <?php echo date('h:i A'); ?></small>
                        </div>
                    </div>-->
        </nav>
    </header>

    <!-- Main Content -->
    <main class="main-content">
        <div class="container-fluid">
            <!-- Section Header -->
            <div class="section-header">
                <h2 class="section-title">
                    <i class="fas fa-building"></i>
                    Department & Building Management
                </h2>
                <p class="section-description">
                    Manage and maintain the registry of all academic departments and buildings within the institution.
                </p>
            </div>

            <!-- Statistics -->
            <div class="stats-container">
                <div class="stat-card">
                    <div class="stat-number"><?php echo $total; ?></div>
                    <div class="stat-label">Total Departments</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo count($buildings); ?></div>
                    <div class="stat-label">Records Displayed</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo $pages; ?></div>
                    <div class="stat-label">Total Pages</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo $page; ?></div>
                    <div class="stat-label">Current Page</div>
                </div>
            </div>

            <!-- Messages -->
            <?php if (!empty($message)): ?>
                <div class="alert alert-<?php echo $message_type; ?>">
                    <i class="fas fa-<?php echo $message_type == 'success' ? 'check-circle' : 'exclamation-triangle'; ?>"></i>
                    <strong><?php echo $message_type == 'success' ? 'Success:' : 'Error:'; ?></strong> <?php echo $message; ?>
                </div>
            <?php endif; ?>

            <div class="row">
                <!-- Form Section -->
                <div class="col-lg-4">
                    <div class="gov-card">
                        <div class="gov-card-header">
                            <i class="fas fa-<?php echo $edit_building ? 'edit' : 'plus-circle'; ?>"></i>
                            <?php echo $edit_building ? 'Edit Department' : 'Add New Department'; ?>
                        </div>
                        <div class="gov-card-body">
                            <form method="POST" action="">
                                <?php if ($edit_building): ?>
                                    <input type="hidden" name="action" value="edit">
                                    <input type="hidden" name="building_id" value="<?php echo $edit_building['id']; ?>">
                                <?php else: ?>
                                    <input type="hidden" name="action" value="add">
                                <?php endif; ?>

                                <div class="form-group">
                                    <label for="building_name" class="form-label">
                                        <i class="fas fa-building"></i>
                                        Department/Building Name *
                                    </label>
                                    <input 
                                        type="text" 
                                        class="form-control" 
                                        id="building_name" 
                                        name="building_name" 
                                        value="<?php echo $edit_building ? htmlspecialchars($edit_building['building_name']) : ''; ?>"
                                        placeholder="Enter department or building name"
                                        required
                                        maxlength="100"
                                    >
                                    <small class="form-text text-muted">Maximum 100 characters allowed</small>
                                </div>

                                <div class="d-grid gap-2">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-<?php echo $edit_building ? 'save' : 'plus'; ?>"></i>
                                        <?php echo $edit_building ? 'Update Department' : 'Add Department'; ?>
                                    </button>

                                    <?php if ($edit_building): ?>
                                        <a href="?" class="btn btn-outline-primary">
                                            <i class="fas fa-times"></i>
                                            Cancel Edit
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Quick Actions -->
                    <div class="gov-card">
                        <div class="gov-card-header">
                            <i class="fas fa-tools"></i>
                            Quick Actions
                        </div>
                        <div class="gov-card-body">
                            <div class="d-grid gap-2">
                                <a href="index.php" class="btn btn-outline-primary btn-sm">
                                    <i class="fas fa-home"></i>
                                    Back to Dashboard
                                </a>
                                <!--<button class="btn btn-outline-primary btn-sm" onclick="window.print()">
                                    <i class="fas fa-print"></i>
                                    Print Report
                                </button>-->
                                <a href="?search=" class="btn btn-outline-primary btn-sm">
                                    <i class="fas fa-refresh"></i>
                                    Refresh List
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- List Section -->
                <div class="col-lg-8">
                    <div class="gov-card">
                        <div class="gov-card-header">
                            <i class="fas fa-list"></i>
                            Department Registry
                        </div>
                        <div class="gov-card-body">
                            <!-- Search Section -->
                            <div class="search-container">
                                <form method="GET" class="search-form">
                                    <div class="flex-grow-1">
                                        <input 
                                            type="text" 
                                            name="search" 
                                            class="form-control" 
                                            placeholder="Search departments by name..." 
                                            value="<?php echo htmlspecialchars($search); ?>"
                                        >
                                    </div>
                                    <button class="btn btn-primary" type="submit">
                                        <i class="fas fa-search"></i>
                                        Search
                                    </button>
                                    <?php if (!empty($search)): ?>
                                        <a href="?" class="btn btn-outline-primary">
                                            <i class="fas fa-times"></i>
                                            Clear
                                        </a>
                                    <?php endif; ?>
                                </form>
                            </div>

                            <!-- Table -->
                            <div class="table-container">
                                <div class="table-responsive">
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th width="10%">S.No.</th>
                                                <th width="15%">Dept. ID</th>
                                                <th width="50%">Department/Building Name</th>
                                                <th width="25%">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (empty($buildings)): ?>
                                                <tr>
                                                    <td colspan="4" class="text-center py-5">
                                                        <div class="text-muted">
                                                            <i class="fas fa-inbox fa-3x mb-3"></i>
                                                            <h5>No Departments Found</h5>
                                                            <p>No departments match your search criteria.<br>Try adjusting your search terms or add a new department.</p>
                                                        </div>
                                                    </td>
                                                </tr>
                                            <?php else: ?>
                                                <?php 
                                                $serial = $start + 1;
                                                foreach ($buildings as $building): 
                                                ?>
                                                    <tr>
                                                        <td><strong><?php echo $serial++; ?></strong></td>
                                                        <td>
                                                            <span class="badge badge-primary">DEPT-<?php echo str_pad($building['id'], 4, '0', STR_PAD_LEFT); ?></span>
                                                        </td>
                                                        <td>
                                                            <strong><?php echo htmlspecialchars($building['building_name']); ?></strong>
                                                        </td>
                                                        <td>
                                                            <div class="action-buttons">
                                                                <a href="?edit=<?php echo $building['id']; ?>" 
                                                                   class="btnn btnn-edit btn-sm" 
                                                                   title="Edit Department">
                                                                    <i class="fas fa-edit"></i>
                                                                    Edit
                                                                </a>
                                                                <a href="?delete=<?php echo $building['id']; ?>" 
                                                                   class="btnn btnn-delete btn-sm"
                                                                   title="Delete Department"
                                                                   onclick="return confirm('Are you sure you want to delete this department?\n\nDepartment: <?php echo htmlspecialchars($building['building_name']); ?>\n\nThis action cannot be undone.');">
                                                                    <i class="fas fa-trash"></i>
                                                                    Delete
                                                                </a>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <!-- Pagination -->
                            <?php if ($pages > 1): ?>
                                <nav aria-label="Page navigation">
                                    <ul class="pagination">
                                        <?php if ($page > 1): ?>
                                            <li class="page-item">
                                                <a class="page-link" href="?search=<?php echo urlencode($search); ?>&page=<?php echo $page - 1; ?>" aria-label="Previous">
                                                    <span aria-hidden="true">&laquo;</span>
                                                </a>
                                            </li>
                                        <?php endif; ?>

                                        <?php for ($i = 1; $i <= $pages; $i++): ?>
                                            <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                                                <a class="page-link" href="?search=<?php echo urlencode($search); ?>&page=<?php echo $i; ?>">
                                                    <?php echo $i; ?>
                                                </a>
                                            </li>
                                        <?php endfor; ?>

                                        <?php if ($page < $pages): ?>
                                            <li class="page-item">
                                                <a class="page-link" href="?search=<?php echo urlencode($search); ?>&page=<?php echo $page + 1; ?>" aria-label="Next">
                                                    <span aria-hidden="true">&raquo;</span>
                                                </a>
                                            </li>
                                        <?php endif; ?>
                                    </ul>
                                </nav>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Government Footer -->
    
    

    <!-- Government Information Banner -->
    <div class="gov-info-banner">
         <div class="container">
            <div class="footer-links">
                <a href="about_us.php" class="footer-link">About Us</a>
                <a href="contact.php" class="footer-link">Contact</a>
                <a href="privacy.php" class="footer-link">Privacy Policy</a>
                <a href="terms.php" class="footer-link">Terms of Service</a>
                <a href="help.php" class="footer-link">Help Center</a>
            </div>
            <div class="copyright">
                &copy; 2024 University Classroom Allocation System. All rights reserved.
            </div>
        </div>
    </div>

    <!-- Bootstrap JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Custom JavaScript -->
    <script>
        // Auto-hide alerts
        document.addEventListener('DOMContentLoaded', function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(function(alert) {
                setTimeout(function() {
                    alert.style.opacity = '0';
                    setTimeout(function() {
                        alert.remove();
                    }, 300);
                }, 5000);
            });
        });

        // Confirmation for delete actions
        function confirmDelete(departmentName) {
            return confirm('Are you sure you want to delete this department?\n\nDepartment: ' + departmentName + '\n\nThis action cannot be undone and may affect related records.');
        }

        // Print functionality
        function printReport() {
            window.print();
        }

        // Form validation
        document.getElementById('building_name').addEventListener('input', function() {
            const value = this.value.trim();
            const submitBtn = document.querySelector('button[type="submit"]');
            
            if (value.length < 3) {
                this.classList.add('is-invalid');
                submitBtn.disabled = true;
            } else {
                this.classList.remove('is-invalid');
                this.classList.add('is-valid');
                submitBtn.disabled = false;
            }
        });

        // Real-time clock
        function updateClock() {
            const now = new Date();
            const timeString = now.toLocaleTimeString('en-IN', {
                hour12: true,
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit'
            });
            const clockElement = document.querySelector('.real-time-clock');
            if (clockElement) {
                clockElement.textContent = timeString;
            }
        }

        setInterval(updateClock, 1000);
        updateClock();
    </script>
<style>
        /* Simplified and improved button styles */
        .action-buttons {
            display: flex;
            gap: 8px;
            justify-content: center;
            align-items: center;
        }
.copyright {
        text-align: center;
        }

        .btnn-action {
            padding: 8px 16px;
            border-radius: 6px;
            font-weight: 500;
            font-size: 0.85rem;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: all 0.2s ease;
            border: none;
            cursor: pointer;
            min-width: 80px;
            justify-content: center;
        }

        .btnn-edit {
            background-color: #2563eb;
            color: white;
            box-shadow: 0 2px 4px rgba(37, 99, 235, 0.2);
        }

        .btnn-edit:hover {
            background-color: #1d4ed8;
            color: white;
            transform: translateY(-1px);
            box-shadow: 0 4px 8px rgba(37, 99, 235, 0.3);
        }

        .btnn-delete {
            background-color: #dc2626;
            color: white;
            box-shadow: 0 2px 4px rgba(220, 38, 38, 0.2);
        }

        .btnn-delete:hover {
            background-color: #b91c1c;
            color: white;
            transform: translateY(-1px);
            box-shadow: 0 4px 8px rgba(220, 38, 38, 0.3);
        }

        .btn-action i {
            font-size: 0.8rem;
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .action-buttons {
                flex-direction: column;
                gap: 4px;
            }
            
            .btn-action {
                width: 100%;
                min-width: auto;
            }
        }

        /* Remove unnecessary decorative elements */
        .btn-action::before {
            display: none;
        }

        /* Simplified hover effects */
        .btn-action:active {
            transform: translateY(0);
        }
    </style>
    <style>
        /* Enhanced Professional Government Styling */
        
        /* Footer Enhancements */
        .gov-footer {
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
            color: #ffffff;
            padding: 3rem 0 1rem 0;
            margin-top: 4rem;
            border-top: 4px solid #ff6b35;
        }

        .footer-section {
            margin-bottom: 2rem;
        }

        .footer-title {
            color: #ff6b35;
            font-weight: 700;
            font-size: 1.1rem;
            margin-bottom: 1rem;
            padding-bottom: 0.5rem;
            border-bottom: 2px solid #ff6b35;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .footer-links {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .footer-links li {
            margin-bottom: 0.5rem;
        }

        .footer-links a {
            color: #b8c5d1;
            text-decoration: none;
            font-size: 0.9rem;
            transition: all 0.3s ease;
            display: block;
            padding: 0.25rem 0;
        }

        .footer-links a:hover {
            color: #ff6b35;
            padding-left: 0.5rem;
        }

        .footer-contact {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .footer-contact li {
            color: #b8c5d1;
            margin-bottom: 0.75rem;
            font-size: 0.9rem;
            display: flex;
            align-items: flex-start;
            gap: 0.5rem;
        }

        .footer-contact i {
            color: #ff6b35;
            margin-top: 0.2rem;
            width: 16px;
        }

        .gov-initiatives {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
        }

        .initiative-link {
            display: block;
            transition: transform 0.3s ease;
        }

        .initiative-link:hover {
            transform: scale(1.05);
        }

        .initiative-logo {
            border-radius: 4px;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .footer-divider {
            border-color: rgba(255, 255, 255, 0.2);
            margin: 2rem 0 1.5rem 0;
        }

        .footer-bottom-links {
            display: flex;
            flex-wrap: wrap;
            gap: 1.5rem;
        }

        .footer-bottom-links a {
            color: #b8c5d1;
            text-decoration: none;
            font-size: 0.85rem;
            transition: color 0.3s ease;
        }

        .footer-bottom-links a:hover {
            color: #ff6b35;
        }

        .footer-social {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .social-label {
            color: #b8c5d1;
            font-size: 0.9rem;
        }

        .social-link {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 36px;
            height: 36px;
            background: rgba(255, 255, 255, 0.1);
            color: #b8c5d1;
            border-radius: 50%;
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .social-link:hover {
            background: #ff6b35;
            color: white;
            transform: translateY(-2px);
        }

        .footer-copyright {
            background: rgba(0, 0, 0, 0.3);
            padding: 1rem;
            border-radius: 8px;
            margin-top: 1rem;
        }

        .copyright-text {
            margin: 0;
            color: #b8c5d1;
            font-size: 0.9rem;
        }

        .last-updated {
            color: #8fa8b8;
        }

        /* Government Information Banner */
        .gov-info-banner {
            background: linear-gradient(135deg, #0f4c75 0%, #3282b8 100%);
            color: white;
            padding: 1rem 0;
            border-top: 3px solid #bbe1fa;
        }

        .banner-content {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .gov-emblem {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            border: 3px solid white;
        }

        .banner-text {
            display: flex;
            flex-direction: column;
        }

        .banner-text strong {
            font-size: 1.1rem;
            font-weight: 700;
            margin-bottom: 0.25rem;
        }

        .banner-text span {
            font-size: 0.9rem;
            opacity: 0.9;
        }

        .security-badge {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            background: rgba(255, 255, 255, 0.2);
            padding: 0.5rem 1rem;
            border-radius: 25px;
            font-size: 0.9rem;
        }

        .security-badge i {
            color: #4ade80;
        }

        /* Enhanced Header Styling */
        .gov-header {
            background: linear-gradient(135deg, #0f3460 0%, #1e40af 50%, #0369a1 100%);
            position: relative;
            overflow: hidden;
        }

        .gov-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grid" width="10" height="10" patternUnits="userSpaceOnUse"><path d="M 10 0 L 0 0 0 10" fill="none" stroke="rgba(255,255,255,0.05)" stroke-width="1"/></pattern></defs><rect width="100" height="100" fill="url(%23grid)"/></svg>');
            opacity: 0.3;
        }

        .gov-header > * {
            position: relative;
            z-index: 1;
        }

        .gov-header-top {
            background: rgba(0, 0, 0, 0.2);
            padding: 0.75rem 0;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .gov-header-main {
            padding: 2rem 0;
        }

        .gov-logo-icon {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #ff6b35 0%, #f7931e 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 32px;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.3);
            border: 4px solid rgba(255, 255, 255, 0.2);
        }

        .gov-title {
            font-size: 2.25rem;
            font-weight: 800;
            margin: 0;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
            letter-spacing: -0.5px;
        }

        .gov-subtitle {
            font-size: 1.1rem;
            opacity: 0.95;
            margin: 0.5rem 0 0 0;
            font-weight: 500;
        }

        /* Enhanced Card Styling */
        .gov-card {
            background: white;
            border: none;
            border-radius: 12px;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.08);
            margin-bottom: 2rem;
            overflow: hidden;
            transition: all 0.3s ease;
        }

        .gov-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 35px rgba(0, 0, 0, 0.12);
        }

        .gov-card-header {
            background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
            border-bottom: 2px solid #e2e8f0;
            padding: 1.25rem 1.5rem;
            font-weight: 700;
            color: var(--gov-primary);
            font-size: 1.1rem;
        }

        /* Enhanced Form Controls */
        .form-control {
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            padding: 0.875rem 1rem;
            font-size: 0.95rem;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            border-color: var(--gov-primary);
            box-shadow: 0 0 0 4px rgba(30, 58, 138, 0.1);
            outline: none;
        }

        .form-control.is-valid {
            border-color: #10b981;
        }

        .form-control.is-invalid {
            border-color: #ef4444;
        }

        /* Enhanced Button Styling */
        .btn {
            border-radius: 8px;
            font-weight: 600;
            padding: 0.875rem 1.75rem;
            border: none;
            cursor: pointer;
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-size: 0.875rem;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--gov-primary) 0%, #1e40af 100%);
            box-shadow: 0 4px 12px rgba(30, 58, 138, 0.3);
        }

        .btn-primary:hover {
            background: linear-gradient(135deg, #1e40af 0%, var(--gov-primary) 100%);
            transform: translateY(-1px);
            box-shadow: 0 6px 16px rgba(30, 58, 138, 0.4);
        }

        /* Professional Table Styling */
        .table-container {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
            border: 1px solid #e2e8f0;
        }

        .table thead th {
            background: linear-gradient(135deg, var(--gov-primary) 0%, #1e40af 100%);
            color: white;
            font-weight: 700;
            border: none;
            padding: 1.25rem 1rem;
            text-align: center;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .table tbody td {
            padding: 1.25rem 1rem;
            border-color: #f1f5f9;
            text-align: center;
            vertical-align: middle;
            font-size: 0.95rem;
        }

        .table tbody tr:hover {
            background: #f8fafc;
            transform: scale(1.001);
            transition: all 0.2s ease;
        }

        /* Enhanced Statistics Cards */
        .stat-card {
            background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            padding: 2rem 1.5rem;
            text-align: center;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
            border-left: 6px solid var(--gov-primary);
            transition: all 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.12);
            border-left-color: #ff6b35;
        }

        .stat-number {
            font-size: 3rem;
            font-weight: 800;
            color: var(--gov-primary);
            margin-bottom: 0.5rem;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.1);
        }

        .stat-label {
            color: var(--gov-text);
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.85rem;
            letter-spacing: 1px;
        }

        /* Enhanced Alert Styling */
        .alert {
            border: none;
            border-radius: 10px;
            padding: 1.25rem 1.5rem;
            margin-bottom: 1.5rem;
            border-left: 5px solid;
            font-weight: 500;
            position: relative;
            overflow: hidden;
        }

        .alert::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: linear-gradient(90deg, transparent 0%, currentColor 50%, transparent 100%);
        }

        .alert-success {
            background: linear-gradient(135deg, #ecfdf5 0%, #d1fae5 100%);
            border-left-color: var(--gov-success);
            color: #065f46;
        }

        .alert-danger {
            background: linear-gradient(135deg, #fef2f2 0%, #fecaca 100%);
            border-left-color: var(--gov-accent);
            color: #991b1b;
        }

        /* Responsive Enhancements */
        @media (max-width: 768px) {
            .gov-title {
                font-size: 1.75rem;
            }
            
            .gov-logo-icon {
                width: 60px;
                height: 60px;
                font-size: 24px;
            }
            
            .stat-number {
                font-size: 2.25rem;
            }
            
            .footer-bottom-links {
                flex-direction: column;
                gap: 0.5rem;
            }
            
            .footer-social {
                justify-content: center;
                margin-top: 1rem;
            }
            
            .banner-content {
                text-align: center;
                flex-direction: column;
            }
            
            .security-badge {
                justify-content: center;
                margin-top: 1rem;
            }
        }

        /* Loading Animation */
        .loading {
            opacity: 0.7;
            pointer-events: none;
        }

        .loading::after {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 20px;
            height: 20px;
            margin: -10px 0 0 -10px;
            border: 2px solid #f3f3f3;
            border-top: 2px solid var(--gov-primary);
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* Print Styles */
        @media print {
            .gov-header-top,
            .gov-nav,
            .footer,
            .gov-footer,
            .gov-info-banner,
            .btn,
            .action-buttons {
                display: none !important;
            }
            
            .main-content {
                padding-top: 0;
            }
            
            .gov-card {
                box-shadow: none;
                border: 1px solid #ddd;
            }
        }
    </style>
</body>
</html>