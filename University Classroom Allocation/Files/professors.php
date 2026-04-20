<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit;
}
include('config.php');

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Add Professor
    if (isset($_POST['add_professor'])) {
        $professor_name = mysqli_real_escape_string($con, $_POST['professor_name']);
        $building_id = (int)$_POST['building_id'];
        
        if ($con->query("INSERT INTO professors (name, building_id) VALUES ('$professor_name', '$building_id')")) {
            $success_message = "Professor added successfully!";
        } else {
            $error_message = "Error adding professor: " . $con->error;
        }
    }

    // Update Professor
    if (isset($_POST['update_professor'])) {
        $professor_id = (int)$_POST['update_professor_id'];
        $professor_name = mysqli_real_escape_string($con, $_POST['update_professor_name']);
        
        if ($con->query("UPDATE professors SET name='$professor_name' WHERE id='$professor_id'")) {
            $success_message = "Professor updated successfully!";
        } else {
            $error_message = "Error updating professor: " . $con->error;
        }
    }

    // Delete Professor
    if (isset($_POST['delete_professor'])) {
        $professor_id = (int)$_POST['delete_professor_id'];
        
        // First, delete all schedule records linked to this professor
        $con->query("DELETE FROM schedule WHERE professor_id='$professor_id'");
        
        // Then delete the professor
        if ($con->query("DELETE FROM professors WHERE id='$professor_id'")) {
            $success_message = "Professor deleted successfully!";
        } else {
            $error_message = "Error deleting professor: " . $con->error;
        }
    }
}

// Fetch buildings for dropdowns
$buildings_query = $con->query("SELECT * FROM buildings ORDER BY building_name");
$buildings = [];
while ($row = $buildings_query->fetch_assoc()) {
    $buildings[] = $row;
}

// Fetch professors for display
$professors_query = $con->query("
    SELECT p.*, b.building_name 
    FROM professors p 
    LEFT JOIN buildings b ON p.building_id = b.id 
    ORDER BY b.building_name, p.name
");
$professors = [];
while ($row = $professors_query->fetch_assoc()) {
    $professors[] = $row;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Professor Management System</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        :root {
            --primary: #2563eb;
            --primary-dark: #1d4ed8;
            --secondary: #64748b;
            --success: #10b981;
            --danger: #ef4444;
            --warning: #f59e0b;
            --background: #f8fafc;
            --surface: #ffffff;
            --border: #e2e8f0;
            --text-primary: #1e293b;
            --text-secondary: #64748b;
            --shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
            --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--background);
            color: var(--text-primary);
            line-height: 1.6;
        }

        .header {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            color: white;
            padding: 2rem 0;
            box-shadow: var(--shadow-lg);
        }

        .header-content {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .header h1 {
            font-size: 2rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .btn-back {
            background: rgba(255, 255, 255, 0.2);
            color: white;
            text-decoration: none;
            padding: 0.75rem 1.5rem;
            border-radius: 0.5rem;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-weight: 500;
        }

        .btn-back:hover {
            background: rgba(255, 255, 255, 0.3);
            transform: translateY(-2px);
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem;
        }

        .tabs {
            display: flex;
            background: var(--surface);
            border-radius: 0.75rem;
            padding: 0.5rem;
            margin-bottom: 2rem;
            box-shadow: var(--shadow);
            gap: 0.5rem;
        }

        .tab {
            flex: 1;
            padding: 1rem;
            text-align: center;
            background: transparent;
            border: none;
            border-radius: 0.5rem;
            cursor: pointer;
            transition: all 0.3s ease;
            font-weight: 500;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }

        .tab.active {
            background: var(--primary);
            color: white;
            box-shadow: var(--shadow);
        }

        .tab:not(.active):hover {
            background: var(--background);
        }

        .tab-content {
            display: none;
        }

        .tab-content.active {
            display: block;
        }

        .form-card {
            background: var(--surface);
            padding: 2rem;
            border-radius: 1rem;
            box-shadow: var(--shadow);
            margin-bottom: 2rem;
        }

        .form-card h2 {
            margin-bottom: 1.5rem;
            color: var(--text-primary);
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1.5rem;
        }

        label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: var(--text-primary);
        }

        input, select {
            width: 100%;
            padding: 0.75rem;
            border: 2px solid var(--border);
            border-radius: 0.5rem;
            font-size: 1rem;
            transition: all 0.3s ease;
            background: var(--surface);
        }

        input:focus, select:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        }

        input:disabled, select:disabled {
            background-color: #f9fafb;
            color: var(--text-secondary);
            cursor: not-allowed;
        }

        .btn {
            background: var(--primary);
            color: white;
            border: none;
            padding: 0.75rem 2rem;
            border-radius: 0.5rem;
            cursor: pointer;
            font-size: 1rem;
            font-weight: 500;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .btn:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
        }

        .btn-danger {
            background: var(--danger);
        }

        .btn-danger:hover {
            background: #dc2626;
        }

        .btn-warning {
            background: var(--warning);
        }

        .btn-warning:hover {
            background: #d97706;
        }

        .alert {
            padding: 1rem;
            border-radius: 0.5rem;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-weight: 500;
        }

        .alert-success {
            background: #dcfce7;
            color: #166534;
            border: 1px solid #bbf7d0;
        }

        .alert-error {
            background: #fef2f2;
            color: #991b1b;
            border: 1px solid #fecaca;
        }

        .table-container {
            background: var(--surface);
            border-radius: 1rem;
            overflow: hidden;
            box-shadow: var(--shadow);
        }

        .table-header {
            background: var(--background);
            padding: 1.5rem;
            border-bottom: 1px solid var(--border);
        }

        .table-header h3 {
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid var(--border);
        }

        th {
            background: var(--background);
            font-weight: 600;
            color: var(--text-primary);
        }

        tr:hover {
            background: var(--background);
        }

        .empty-state {
            text-align: center;
            padding: 3rem;
            color: var(--text-secondary);
        }

        .empty-state i {
            font-size: 3rem;
            margin-bottom: 1rem;
            opacity: 0.5;
        }

        .loading {
            display: none;
            text-align: center;
            color: var(--text-secondary);
            font-style: italic;
            margin-top: 0.5rem;
        }

        @media (max-width: 768px) {
            .header-content {
                flex-direction: column;
                gap: 1rem;
                text-align: center;
            }

            .tabs {
                flex-direction: column;
            }

            .form-row {
                grid-template-columns: 1fr;
            }

            .container {
                padding: 1rem;
            }

            table {
                font-size: 0.875rem;
            }

            th, td {
                padding: 0.75rem 0.5rem;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="header-content">
            <h1><i class="fas fa-chalkboard-teacher"></i> Professor Management </h1>
            <a href="index.php" class="btn-back">
                <i class="fas fa-arrow-left"></i> Back to Main Page
            </a>
        </div>
    </div>

    <div class="container">
        <?php if (isset($success_message)): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i>
                <?php echo $success_message; ?>
            </div>
        <?php endif; ?>

        <?php if (isset($error_message)): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i>
                <?php echo $error_message; ?>
            </div>
        <?php endif; ?>

        <div class="tabs">
            <button class="tab active" onclick="showTab('add')">
                <i class="fas fa-plus"></i> Add Professor
            </button>
            <button class="tab" onclick="showTab('update')">
                <i class="fas fa-edit"></i> Update Professor
            </button>
            <button class="tab" onclick="showTab('delete')">
                <i class="fas fa-trash"></i> Delete Professor
            </button>
            <button class="tab" onclick="showTab('view')">
                <i class="fas fa-list"></i> View All Professors
            </button>
        </div>

        <!-- Add Professor Tab -->
        <div id="add-tab" class="tab-content active">
            <div class="form-card">
                <h2><i class="fas fa-plus-circle"></i> Add New Professor</h2>
                <form method="POST">
                    <div class="form-group">
                        <label for="professor_name">Professor Name</label>
                        <input type="text" id="professor_name" name="professor_name" placeholder="Enter professor's full name" required>
                    </div>
                    <div class="form-group">
                        <label for="building_id">Department/Building</label>
                        <select id="building_id" name="building_id" required>
                            <option value="">Select a Department/Building</option>
                            <?php foreach ($buildings as $building): ?>
                                <option value="<?php echo $building['id']; ?>"><?php echo htmlspecialchars($building['building_name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <button type="submit" name="add_professor" class="btn">
                        <i class="fas fa-plus"></i> Add Professor
                    </button>
                </form>
            </div>
        </div>

        <!-- Update Professor Tab -->
        <div id="update-tab" class="tab-content">
            <div class="form-card">
                <h2><i class="fas fa-edit"></i> Update Professor</h2>
                <form method="POST">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="update_building">Department/Building</label>
                            <select id="update_building" class="form-control" required>
                                <option value="">Select Department/Building</option>
                                <?php foreach ($buildings as $building): ?>
                                    <option value="<?php echo $building['id']; ?>"><?php echo htmlspecialchars($building['building_name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="update_professor">Select Professor</label>
                            <select name="update_professor_id" id="update_professor" class="form-control" required>
                                <option value="">First select a department</option>
                            </select>
                            <div class="loading" id="update_loading">Loading professors...</div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="update_professor_name">New Professor Name</label>
                        <input type="text" id="update_professor_name" name="update_professor_name" placeholder="Enter new professor name" required>
                    </div>
                    <button type="submit" name="update_professor" class="btn btn-warning">
                        <i class="fas fa-save"></i> Update Professor
                    </button>
                </form>
            </div>
        </div>

        <!-- Delete Professor Tab -->
        <div id="delete-tab" class="tab-content">
            <div class="form-card">
                <h2><i class="fas fa-trash-alt"></i> Delete Professor</h2>
                <p style="color: var(--danger); margin-bottom: 1.5rem;">
                    <i class="fas fa-exclamation-triangle"></i>
                    <strong>Warning:</strong> This action cannot be undone. All associated schedules will also be deleted.
                </p>
                <form method="POST" onsubmit="return confirm('Are you sure you want to delete this professor? This action cannot be undone.')">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="delete_building">Department/Building</label>
                            <select id="delete_building" class="form-control" required>
                                <option value="">Select Department/Building</option>
                                <?php foreach ($buildings as $building): ?>
                                    <option value="<?php echo $building['id']; ?>"><?php echo htmlspecialchars($building['building_name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="delete_professor">Select Professor</label>
                            <select name="delete_professor_id" id="delete_professor" class="form-control" required>
                                <option value="">First select a department</option>
                            </select>
                            <div class="loading" id="delete_loading">Loading professors...</div>
                        </div>
                    </div>
                    <button type="submit" name="delete_professor" class="btn btn-danger">
                        <i class="fas fa-trash"></i> Delete Professor
                    </button>
                </form>
            </div>
        </div>

        <!-- View All Professors Tab -->
        <div id="view-tab" class="tab-content">
            <div class="table-container">
                <div class="table-header">
                    <h3><i class="fas fa-list"></i> All Professors</h3>
                </div>
                <?php if (count($professors) > 0): ?>
                    <table>
                        <thead>
                            <tr>
                                <th>Professor Name</th>
                                <th>Department/Building</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($professors as $professor): ?>
                                <tr>
                                    <td><strong><?php echo htmlspecialchars($professor['name']); ?></strong></td>
                                    <td><?php echo htmlspecialchars($professor['building_name']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <div class="empty-state">
                        <i class="fas fa-chalkboard-teacher"></i>
                        <h3>No Professors Found</h3>
                        <p>Start by adding your first professor using the "Add Professor" tab.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
        function showTab(tabName) {
            // Hide all tab contents
            const tabContents = document.querySelectorAll('.tab-content');
            tabContents.forEach(content => content.classList.remove('active'));
            
            // Remove active class from all tabs
            const tabs = document.querySelectorAll('.tab');
            tabs.forEach(tab => tab.classList.remove('active'));
            
            // Show selected tab content
            document.getElementById(tabName + '-tab').classList.add('active');
            
            // Add active class to clicked tab
            event.target.classList.add('active');
        }

        $(document).ready(function() {
            $('#update_building, #delete_building').change(function() {
                var buildingId = $(this).val();
                var targetSelect = $(this).attr('id') === 'update_building' ? '#update_professor' : '#delete_professor';
                var loadingElement = $(this).attr('id') === 'update_building' ? '#update_loading' : '#delete_loading';
                
                if (buildingId) {
                    $(loadingElement).show();
                    $(targetSelect).prop('disabled', true);
                    
                    $.ajax({
                        type: 'POST',
                        url: 'fetch_professors.php',
                        data: { building_id: buildingId },
                        success: function(response) {
                            $(targetSelect).html(response);
                            $(targetSelect).prop('disabled', false);
                            $(loadingElement).hide();
                        },
                        error: function() {
                            $(targetSelect).html('<option value="">Error loading professors</option>');
                            $(targetSelect).prop('disabled', false);
                            $(loadingElement).hide();
                        }
                    });
                } else {
                    $(targetSelect).html('<option value="">First select a department</option>');
                    $(loadingElement).hide();
                }
            });
        });

        // Auto-hide alerts after 5 seconds
        setTimeout(() => {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                alert.style.opacity = '0';
                alert.style.transform = 'translateY(-20px)';
                setTimeout(() => alert.remove(), 300);
            });
        }, 5000);
    </script>
</body>
</html>