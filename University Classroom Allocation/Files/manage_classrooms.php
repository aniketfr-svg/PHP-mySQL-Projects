<?php
session_start();
include('config.php');

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit;
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Add Classroom
    if (isset($_POST['add_classroom'])) {
        $classroom_name = mysqli_real_escape_string($con, $_POST['classroom_name']);
        $capacity = (int)$_POST['capacity'];
        $building_id = (int)$_POST['building'];
        $has_projector = (int)$_POST['projector'];

        $query = "INSERT INTO classrooms (name, capacity, building_id, has_projector) 
                  VALUES ('$classroom_name', '$capacity', '$building_id', '$has_projector')";
        
        if ($con->query($query)) {
            $success_message = "Classroom added successfully!";
        } else {
            $error_message = "Error adding classroom: " . $con->error;
        }
    }

    // Update Classroom
    if (isset($_POST['update_classroom'])) {
        $classroom_id = (int)$_POST['update_classroom_id'];
        $classroom_name = mysqli_real_escape_string($con, $_POST['update_classroom_name']);
        $capacity = (int)$_POST['update_capacity'];
        $building_id = (int)$_POST['update_building'];
        $has_projector = (int)$_POST['update_projector'];

        $query = "UPDATE classrooms SET name='$classroom_name', capacity='$capacity', 
                  building_id='$building_id', has_projector='$has_projector' WHERE id='$classroom_id'";
        
        if ($con->query($query)) {
            $success_message = "Classroom updated successfully!";
        } else {
            $error_message = "Error updating classroom: " . $con->error;
        }
    }

    // Delete Classroom
    if (isset($_POST['delete_classroom'])) {
        $classroom_id = (int)$_POST['delete_classroom_id'];
        
        // First, delete all schedule records linked to this classroom
        $con->query("DELETE FROM schedule WHERE classroom_id='$classroom_id'");
        
        // Then delete the classroom itself
        if ($con->query("DELETE FROM classrooms WHERE id='$classroom_id'")) {
            $success_message = "Classroom deleted successfully!";
        } else {
            $error_message = "Error deleting classroom: " . $con->error;
        }
    }
}

// Fetch buildings for dropdowns
$buildings_query = $con->query("SELECT * FROM buildings ORDER BY building_name");
$buildings = [];
while ($row = $buildings_query->fetch_assoc()) {
    $buildings[] = $row;
}

// Fetch classrooms for display
$classrooms_query = $con->query("
    SELECT c.*, b.building_name 
    FROM classrooms c 
    LEFT JOIN buildings b ON c.building_id = b.id 
    ORDER BY b.building_name, c.name
");
$classrooms = [];
while ($row = $classrooms_query->fetch_assoc()) {
    $classrooms[] = $row;
}
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

        .badge {
            padding: 0.25rem 0.5rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 500;
        }

        .badge-success {
            background: #dcfce7;
            color: #166534;
        }

        .badge-danger {
            background: #fef2f2;
            color: #991b1b;
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
            <h1><i class="fas fa-school"></i> Classroom Management </h1>
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
                <i class="fas fa-plus"></i> Add Classroom
            </button>
            <button class="tab" onclick="showTab('update')">
                <i class="fas fa-edit"></i> Update Classroom
            </button>
            <button class="tab" onclick="showTab('delete')">
                <i class="fas fa-trash"></i> Delete Classroom
            </button>
            <button class="tab" onclick="showTab('view')">
                <i class="fas fa-list"></i> View All Classrooms
            </button>
        </div>

        <!-- Add Classroom Tab -->
        <div id="add-tab" class="tab-content active">
            <div class="form-card">
                <h2><i class="fas fa-plus-circle"></i> Add New Classroom</h2>
                <form method="POST">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="classroom_name">Classroom Name</label>
                            <input type="text" id="classroom_name" name="classroom_name" placeholder="Enter classroom name" required>
                        </div>
                        <div class="form-group">
                            <label for="capacity">Capacity</label>
                            <input type="number" id="capacity" name="capacity" placeholder="Enter capacity" min="1" required>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="building">Department/Building</label>
                            <select id="building" name="building" required>
                                <option value="">Select Department/Building</option>
                                <?php foreach ($buildings as $building): ?>
                                    <option value="<?php echo $building['id']; ?>"><?php echo htmlspecialchars($building['building_name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="projector">Projector Available</label>
                            <select id="projector" name="projector" required>
                                <option value="1">Yes</option>
                                <option value="0">No</option>
                            </select>
                        </div>
                    </div>
                    <button type="submit" name="add_classroom" class="btn">
                        <i class="fas fa-plus"></i> Add Classroom
                    </button>
                </form>
            </div>
        </div>

        <!-- Update Classroom Tab -->
        <div id="update-tab" class="tab-content">
            <div class="form-card">
                <h2><i class="fas fa-edit"></i> Update Classroom</h2>
                <form method="POST">
                    <div class="form-group">
                        <label for="update_building_select">Select Department/Building</label>
                        <select id="update_building_select" name="update_building_select" onchange="loadClassroomsForUpdate()" required>
                            <option value="">Select a Department/Building</option>
                            <?php foreach ($buildings as $building): ?>
                                <option value="<?php echo $building['id']; ?>"><?php echo htmlspecialchars($building['building_name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="update_classroom_id">Select Classroom to Update</label>
                        <select id="update_classroom_id" name="update_classroom_id" required onchange="populateUpdateForm()">
                            <option value="">Select a classroom</option>
                        </select>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="update_classroom_name">Classroom Name</label>
                            <input type="text" id="update_classroom_name" name="update_classroom_name" required>
                        </div>
                        <div class="form-group">
                            <label for="update_capacity">Capacity</label>
                            <input type="number" id="update_capacity" name="update_capacity" min="1" required>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="update_building">Department/Building</label>
                            <select id="update_building" name="update_building" required>
                                <option value="">Select Department/Building</option>
                                <?php foreach ($buildings as $building): ?>
                                    <option value="<?php echo $building['id']; ?>"><?php echo htmlspecialchars($building['building_name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="update_projector">Projector Available</label>
                            <select id="update_projector" name="update_projector" required>
                                <option value="1">Yes</option>
                                <option value="0">No</option>
                            </select>
                        </div>
                    </div>
                    <button type="submit" name="update_classroom" class="btn btn-warning">
                        <i class="fas fa-save"></i> Update Classroom
                    </button>
                </form>
            </div>
        </div>

        <!-- Delete Classroom Tab -->
        <div id="delete-tab" class="tab-content">
            <div class="form-card">
                <h2><i class="fas fa-trash-alt"></i> Delete Classroom</h2>
                <p style="color: var(--danger); margin-bottom: 1.5rem;">
                    <i class="fas fa-exclamation-triangle"></i>
                    <strong>Warning:</strong> This action cannot be undone. All associated schedules will also be deleted.
                </p>
                <form method="POST" onsubmit="return confirm('Are you sure you want to delete this classroom? This action cannot be undone.')">
                    <div class="form-group">
                        <label for="delete_building_id">Select Department/Building</label>
                        <select id="delete_building_id" name="delete_building_id" onchange="loadClassroomsByBuilding()" required>
                            <option value="">Select a Department/Building</option>
                            <?php foreach ($buildings as $building): ?>
                                <option value="<?php echo $building['id']; ?>"><?php echo htmlspecialchars($building['building_name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="delete_classroom_id">Select Classroom to Delete</label>
                        <select id="delete_classroom_id" name="delete_classroom_id" required>
                            <option value="">Select a Classroom</option>
                        </select>
                    </div>
                    <button type="submit" name="delete_classroom" class="btn btn-danger">
                        <i class="fas fa-trash"></i> Delete Classroom
                    </button>
                </form>
            </div>
        </div>

        <!-- View All Classrooms Tab -->
        <div id="view-tab" class="tab-content">
            <div class="table-container">
                <div class="table-header">
                    <h3><i class="fas fa-list"></i> All Classrooms</h3>
                </div>
                <?php if (count($classrooms) > 0): ?>
                    <table>
                        <thead>
                            <tr>
                                <th>Classroom Name</th>
                                <th>Building</th>
                                <th>Capacity</th>
                                <th>Projector</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($classrooms as $classroom): ?>
                                <tr>
                                    <td><strong><?php echo htmlspecialchars($classroom['name']); ?></strong></td>
                                    <td><?php echo htmlspecialchars($classroom['building_name']); ?></td>
                                    <td><?php echo $classroom['capacity']; ?> students</td>
                                    <td>
                                        <?php if ($classroom['has_projector']): ?>
                                            <span class="badge badge-success"><i class="fas fa-check"></i> Available</span>
                                        <?php else: ?>
                                            <span class="badge badge-danger"><i class="fas fa-times"></i> Not Available</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <div class="empty-state">
                        <i class="fas fa-school"></i>
                        <h3>No Classrooms Found</h3>
                        <p>Start by adding your first classroom using the "Add Classroom" tab.</p>
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

        function populateUpdateForm() {
            const select = document.getElementById('update_classroom_id');
            const selectedOption = select.options[select.selectedIndex];
            
            if (selectedOption.value) {
                document.getElementById('update_classroom_name').value = selectedOption.dataset.name;
                document.getElementById('update_capacity').value = selectedOption.dataset.capacity;
                document.getElementById('update_building').value = selectedOption.dataset.building;
                document.getElementById('update_projector').value = selectedOption.dataset.projector;
            } else {
                document.getElementById('update_classroom_name').value = '';
                document.getElementById('update_capacity').value = '';
                document.getElementById('update_building').value = '';
                document.getElementById('update_projector').value = '';
            }
        }

        function loadClassroomsForUpdate() {
            const buildingId = document.getElementById('update_building_select').value;
            const classroomSelect = document.getElementById('update_classroom_id');
            
            // Clear current options and reset form
            classroomSelect.innerHTML = '<option value="">Select a Classroom</option>';
            clearUpdateForm();
            
            if (buildingId) {
                // Filter classrooms by building
                const classrooms = <?php echo json_encode($classrooms); ?>;
                const filteredClassrooms = classrooms.filter(classroom => classroom.building_id == buildingId);
                
                filteredClassrooms.forEach(classroom => {
                    const option = document.createElement('option');
                    option.value = classroom.id;
                    option.textContent = classroom.name;
                    option.dataset.name = classroom.name;
                    option.dataset.capacity = classroom.capacity;
                    option.dataset.building = classroom.building_id;
                    option.dataset.projector = classroom.has_projector;
                    classroomSelect.appendChild(option);
                });
            }
        }

        function clearUpdateForm() {
            document.getElementById('update_classroom_name').value = '';
            document.getElementById('update_capacity').value = '';
            document.getElementById('update_building').value = '';
            document.getElementById('update_projector').value = '';
        }

        function loadClassroomsByBuilding() {
            const buildingId = document.getElementById('delete_building_id').value;
            const classroomSelect = document.getElementById('delete_classroom_id');
            
            // Clear current options
            classroomSelect.innerHTML = '<option value="">Select a Classroom</option>';
            
            if (buildingId) {
                // Filter classrooms by building
                const classrooms = <?php echo json_encode($classrooms); ?>;
                const filteredClassrooms = classrooms.filter(classroom => classroom.building_id == buildingId);
                
                filteredClassrooms.forEach(classroom => {
                    const option = document.createElement('option');
                    option.value = classroom.id;
                    option.textContent = classroom.name;
                    classroomSelect.appendChild(option);
                });
            }
        }

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