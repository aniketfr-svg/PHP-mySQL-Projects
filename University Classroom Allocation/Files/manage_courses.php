<?php 
include('config.php');
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit;
}

// Handle AJAX requests
if (isset($_GET['ajax'])) {
    header('Content-Type: application/json');
    
    if ($_GET['ajax'] === 'get_professors' && isset($_GET['building_id'])) {
        $building_id = intval($_GET['building_id']);
        $query = "SELECT id, name FROM professors WHERE building_id = $building_id ORDER BY name";
        $result = $con->query($query);
        $professors = [];
        while ($row = $result->fetch_assoc()) {
            $professors[] = $row;
        }
        echo json_encode($professors);
        exit;
    }
    
    if ($_GET['ajax'] === 'get_courses') {
        $building_id = intval($_GET['building_id']);
        $where_clause = "WHERE building_id = $building_id";
        
        if (isset($_GET['semester_id']) && $_GET['semester_id'] !== '') {
            $semester_id = intval($_GET['semester_id']);
            $where_clause .= " AND semester_id = $semester_id";
        }
        
        $query = "SELECT id, name FROM courses $where_clause ORDER BY name";
        $result = $con->query($query);
        $courses = [];
        while ($row = $result->fetch_assoc()) {
            $courses[] = $row;
        }
        echo json_encode($courses);
        exit;
    }
    
    if ($_GET['ajax'] === 'get_course_details' && isset($_GET['course_id'])) {
        $course_id = intval($_GET['course_id']);
        $query = "SELECT * FROM courses WHERE id = $course_id";
        $result = $con->query($query);
        $course = $result->fetch_assoc();
        echo json_encode($course);
        exit;
    }
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_course'])) {
        $course_name = $_POST['course_name'];
        $building_id = $_POST['building_id'];
        $credits = $_POST['credits'];
        $lecture_hours = $_POST['lecture_hours'];
        $tutorial_hours = $_POST['tutorial_hours'];
        $practical_hours = $_POST['practical_hours'];
        $students_enrolled = $_POST['students_enrolled'];
        $professor_id = $_POST['professor_id'];
        $semester_id = $_POST['semester_id'];
        $requires_projector = $_POST['requires_projector'];
        $course_type = $_POST['course_type'];

        $query = "INSERT INTO courses 
        (name, building_id, credits, lecture_hours, tutorial_hours, practical_hours, students_enrolled, professor_id, semester_id, requires_projector, course_type)
        VALUES 
        ('$course_name', '$building_id', '$credits', '$lecture_hours', '$tutorial_hours', '$practical_hours', '$students_enrolled', '$professor_id', '$semester_id', '$requires_projector', '$course_type')";

        if($con->query($query)) {
            $success_message = "Course added successfully!";
        } else {
            $error_message = "Failed to add course: " . $con->error;
        }
    }

    if (isset($_POST['update_course'])) {
        $course_id = $_POST['update_course_id'];
        $course_name = $_POST['update_course_name'];
        $credits = $_POST['update_credits'];
        $lecture_hours = $_POST['update_lecture_hours'];
        $tutorial_hours = $_POST['update_tutorial_hours'];
        $practical_hours = $_POST['update_practical_hours'];
        $students_enrolled = $_POST['update_students_enrolled'];
        $professor_id = $_POST['update_professor_id'];
        $semester_id = $_POST['update_semester_id'];
        $requires_projector = $_POST['update_requires_projector'];
        $course_type = $_POST['update_course_type'];

        $query = "UPDATE courses SET
            name='$course_name',
            credits='$credits',
            lecture_hours='$lecture_hours',
            tutorial_hours='$tutorial_hours',
            practical_hours='$practical_hours',
            students_enrolled='$students_enrolled',
            professor_id='$professor_id',
            semester_id='$semester_id',
            requires_projector='$requires_projector',
            course_type='$course_type'
            WHERE id='$course_id'";

        if ($con->query($query)) {
            $success_message = "Course updated successfully!";
        } else {
            $error_message = "Error updating course: " . $con->error;
        }
    }

    if (isset($_POST['delete_course'])) {
        $course_id = $_POST['delete_course_id'];
        
        // Delete associated schedule records first
        $con->query("DELETE FROM schedule WHERE course_id='$course_id'");
        
        // Delete the course
        if($con->query("DELETE FROM courses WHERE id='$course_id'")) {
            $success_message = "Course deleted successfully!";
        } else {
            $error_message = "Error deleting course: " . $con->error;
        }
    }
}

// Fetch buildings for dropdowns
$buildings_query = $con->query("SELECT * FROM buildings ORDER BY building_name");
$buildings = [];
while ($row = $buildings_query->fetch_assoc()) {
    $buildings[] = $row;
}

// Fetch professors for dropdowns
$professors_query = $con->query("SELECT * FROM professors ORDER BY name");
$professors = [];
while ($row = $professors_query->fetch_assoc()) {
    $professors[] = $row;
}

// Fetch courses for display
$courses_query = $con->query("
    SELECT c.*, b.building_name, p.name as professor_name, s.name as semester_name
    FROM courses c 
    LEFT JOIN buildings b ON c.building_id = b.id 
    LEFT JOIN professors p ON c.professor_id = p.id
    LEFT JOIN semester s ON c.semester_id = s.id
    ORDER BY b.building_name, c.name
");
$courses = [];
while ($row = $courses_query->fetch_assoc()) {
    $courses[] = $row;
}
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

        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 1.5rem;
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

        .radio-group {
            display: flex;
            gap: 1.5rem;
            margin-top: 0.5rem;
            flex-wrap: wrap;
        }

        .radio-group label {
            display: flex;
            align-items: center;
            cursor: pointer;
            font-weight: 400;
            margin-bottom: 0;
            gap: 0.5rem;
        }

        .radio-group input[type="radio"] {
            width: auto;
            margin: 0;
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

        .badge-primary {
            background: #dbeafe;
            color: #1d4ed8;
        }

        .badge-warning {
            background: #fef3c7;
            color: #d97706;
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

            .form-row, .form-grid {
                grid-template-columns: 1fr;
            }

            .container {
                padding: 1rem;
            }

            .radio-group {
                flex-direction: column;
                gap: 0.75rem;
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
            <h1><i class="fas fa-graduation-cap"></i> Course Management</h1>
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
                <i class="fas fa-plus"></i> Add Course
            </button>
            <button class="tab" onclick="showTab('update')">
                <i class="fas fa-edit"></i> Update Course
            </button>
            <button class="tab" onclick="showTab('delete')">
                <i class="fas fa-trash"></i> Delete Course
            </button>
            <button class="tab" onclick="showTab('view')">
                <i class="fas fa-list"></i> View All Courses
            </button>
        </div>

        <!-- Add Course Tab -->
        <div id="add-tab" class="tab-content active">
            <div class="form-card">
                <h2><i class="fas fa-plus-circle"></i> Add New Course</h2>
                <form method="POST">
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="course_name"><i class="fas fa-book"></i> Course Name</label>
                            <input type="text" id="course_name" name="course_name" placeholder="Enter Course Name" required>
                        </div>

                        <div class="form-group">
                            <label for="add_building_select"><i class="fas fa-building"></i> Department/Building</label>
                            <select id="add_building_select" name="building_id" required>
                                <option value="">Select a Department/Building</option>
                                <?php foreach ($buildings as $building): ?>
                                    <option value="<?php echo $building['id']; ?>"><?php echo htmlspecialchars($building['building_name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="credits"><i class="fas fa-calculator"></i> Credits</label>
                            <input type="number" id="credits" name="credits" placeholder="Enter Credits" required>
                        </div>

                        <div class="form-group">
                            <label for="semester_id"><i class="fas fa-calendar-alt"></i> Semester</label>
                            <select id="semester_id" name="semester_id" required>
                                <option value="">Select the Semester</option>
                                <?php
                                $result = $con->query("SELECT * FROM semester");
                                while ($row = $result->fetch_assoc()) {
                                    echo "<option value='{$row['id']}'>{$row['name']}</option>";
                                }
                                ?>
                            </select>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="lecture_hours"><i class="fas fa-chalkboard-teacher"></i> Lecture Hours (L)</label>
                            <input type="number" id="lecture_hours" name="lecture_hours" placeholder="Enter Lecture Hours" required>
                        </div>
                        <div class="form-group">
                            <label for="tutorial_hours"><i class="fas fa-users"></i> Tutorial Hours (T)</label>
                            <input type="number" id="tutorial_hours" name="tutorial_hours" placeholder="Enter Tutorial Hours" required>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="practical_hours"><i class="fas fa-flask"></i> Practical Hours (P)</label>
                            <input type="number" id="practical_hours" name="practical_hours" placeholder="Enter Practical Hours" required>
                        </div>
                        <div class="form-group">
                            <label for="students_enrolled"><i class="fas fa-user-graduate"></i> Students Enrolled</label>
                            <input type="number" id="students_enrolled" name="students_enrolled" placeholder="Enter Number of Students" required>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="add_professor_select"><i class="fas fa-user-tie"></i> Professor</label>
                            <select id="add_professor_select" name="professor_id" required>
                                <option value="">Select the Professor</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="requires_projector"><i class="fas fa-video"></i> Requires Projector</label>
                            <select id="requires_projector" name="requires_projector" required>
                                <option value="1">Yes</option>
                                <option value="0">No</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label><i class="fas fa-tags"></i> Course Type</label>
                        <div class="radio-group">
                            <label><input type="radio" name="course_type" value="Core" required> Core</label>
                            <label><input type="radio" name="course_type" value="Elective"> Elective</label>
                            <label><input type="radio" name="course_type" value="Open Elective"> Open Elective</label>
                        </div>
                    </div>

                    <button type="submit" name="add_course" class="btn">
                        <i class="fas fa-plus"></i> Add Course
                    </button>
                </form>
            </div>
        </div>

        <!-- Update Course Tab -->
        <div id="update-tab" class="tab-content">
            <div class="form-card">
                <h2><i class="fas fa-edit"></i> Update Course</h2>
                <form method="POST">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="update_building_select"><i class="fas fa-building"></i> Department/Building</label>
                            <select id="update_building_select" name="building_id" required>
                                <option value="">Select a Department/Building</option>
                                <?php foreach ($buildings as $building): ?>
                                    <option value="<?php echo $building['id']; ?>"><?php echo htmlspecialchars($building['building_name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="update_semester_id"><i class="fas fa-calendar-alt"></i> Semester</label>
                            <select name="update_semester_id" id="update_semester_id" required>
                                <option value="">Select the Semester</option>
                                <?php
                                $result = $con->query("SELECT * FROM semester");
                                while ($row = $result->fetch_assoc()) {
                                    echo "<option value='{$row['id']}'>{$row['name']}</option>";
                                }
                                ?>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="update_course_id"><i class="fas fa-book"></i> Select Course to Update</label>
                        <select name="update_course_id" id="update_course_id" required onchange="populateCourseDetails()">
                            <option value="">Select a course</option>
                        </select>
                    </div>

                    <div class="form-grid">
                        <div class="form-group">
                            <label for="update_course_name"><i class="fas fa-book"></i> Course Name</label>
                            <input type="text" name="update_course_name" id="update_course_name" placeholder="Enter Course Name" required>
                        </div>

                        <div class="form-group">
                            <label for="update_credits"><i class="fas fa-calculator"></i> Credits</label>
                            <input type="number" name="update_credits" id="update_credits" placeholder="Enter Credits" required>
                        </div>

                        <div class="form-group">
                            <label for="update_lecture_hours"><i class="fas fa-chalkboard-teacher"></i> Lecture Hours (L)</label>
                            <input type="number" name="update_lecture_hours" id="update_lecture_hours" placeholder="Enter Lecture Hours" required>
                        </div>

                        <div class="form-group">
                            <label for="update_tutorial_hours"><i class="fas fa-users"></i> Tutorial Hours (T)</label>
                            <input type="number" name="update_tutorial_hours" id="update_tutorial_hours" placeholder="Enter Tutorial Hours" required>
                        </div>

                        <div class="form-group">
                            <label for="update_practical_hours"><i class="fas fa-flask"></i> Practical Hours (P)</label>
                            <input type="number" name="update_practical_hours" id="update_practical_hours" placeholder="Enter Practical Hours" required>
                        </div>

                        <div class="form-group">
                            <label for="update_students_enrolled"><i class="fas fa-user-graduate"></i> Students Enrolled</label>
                            <input type="number" name="update_students_enrolled" id="update_students_enrolled" placeholder="Enter Number of Students" required>
                        </div>

                        <div class="form-group">
                            <label for="update_professor_id"><i class="fas fa-user-tie"></i> Professor</label>
                            <select name="update_professor_id" id="update_professor_id" required>
                                <option value="">Select the Professor</option>
                                <?php foreach ($professors as $professor): ?>
                                    <option value="<?php echo $professor['id']; ?>"><?php echo htmlspecialchars($professor['name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="update_requires_projector"><i class="fas fa-video"></i> Requires Projector</label>
                            <select name="update_requires_projector" id="update_requires_projector" required>
                                <option value="1">Yes</option>
                                <option value="0">No</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label><i class="fas fa-tags"></i> Course Type</label>
                        <div class="radio-group">
                            <label><input type="radio" name="update_course_type" value="Core" required> Core</label>
                            <label><input type="radio" name="update_course_type" value="Elective"> Elective</label>
                            <label><input type="radio" name="update_course_type" value="Open Elective"> Open Elective</label>
                        </div>
                    </div>

                    <button type="submit" name="update_course" class="btn btn-warning">
                        <i class="fas fa-save"></i> Update Course
                    </button>
                </form>
            </div>
        </div>

        <!-- Delete Course Tab -->
        <div id="delete-tab" class="tab-content">
            <div class="form-card">
                <h2><i class="fas fa-trash-alt"></i> Delete Course</h2>
                <p style="color: var(--danger); margin-bottom: 1.5rem;">
                    <i class="fas fa-exclamation-triangle"></i>
                    <strong>Warning:</strong> This action cannot be undone. All associated schedules will also be deleted.
                </p>
                <form method="POST" onsubmit="return confirm('Are you sure you want to delete this course? This action cannot be undone.')">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="delete_building_select"><i class="fas fa-building"></i> Department/Building</label>
                            <select name="building_id" id="delete_building_select" required>
                                <option value="">Select a Department/Building</option>
                                <?php foreach ($buildings as $building): ?>
                                    <option value="<?php echo $building['id']; ?>"><?php echo htmlspecialchars($building['building_name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="delete_course_select"><i class="fas fa-book"></i> Select Course to Delete</label>
                            <select name="delete_course_id" id="delete_course_select" required>
                                <option value="">Select a course</option>
                            </select>
                        </div>
                    </div>

                    <button type="submit" name="delete_course" class="btn btn-danger">
                        <i class="fas<i" class="fas fa-trash"></i> Delete Course
                    </button>
                </form>
            </div>
        </div>

        <!-- View All Courses Tab -->
        <div id="view-tab" class="tab-content">
            <div class="table-container">
                <div class="table-header">
                    <h3><i class="fas fa-list"></i> All Courses</h3>
                </div>
                <?php if (empty($courses)): ?>
                    <div class="empty-state">
                        <i class="fas fa-graduation-cap"></i>
                        <h3>No Courses Found</h3>
                        <p>Add your first course to get started.</p>
                    </div>
                <?php else: ?>
                    <table>
                        <thead>
                            <tr>
                                <th>Course Name</th>
                                <th>Department</th>
                                <th>Professor</th>
                                <th>Semester</th>
                                <th>Credits</th>
                                <th>L-T-P</th>
                                <th>Students</th>
                                <th>Type</th>
                                <th>Projector</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($courses as $course): ?>
                                <tr>
                                    <td><strong><?php echo htmlspecialchars($course['name']); ?></strong></td>
                                    <td><?php echo htmlspecialchars($course['building_name']); ?></td>
                                    <td><?php echo htmlspecialchars($course['professor_name']); ?></td>
                                    <td><?php echo htmlspecialchars($course['semester_name']); ?></td>
                                    <td><span class="badge badge-primary"><?php echo $course['credits']; ?></span></td>
                                    <td><?php echo $course['lecture_hours'] . '-' . $course['tutorial_hours'] . '-' . $course['practical_hours']; ?></td>
                                    <td><?php echo $course['students_enrolled']; ?></td>
                                    <td>
                                        <?php 
                                        $type_class = $course['course_type'] === 'Core' ? 'badge-success' : 
                                                     ($course['course_type'] === 'Elective' ? 'badge-warning' : 'badge-primary');
                                        ?>
                                        <span class="badge <?php echo $type_class; ?>"><?php echo $course['course_type']; ?></span>
                                    </td>
                                    <td>
                                        <span class="badge <?php echo $course['requires_projector'] ? 'badge-success' : 'badge-danger'; ?>">
                                            <?php echo $course['requires_projector'] ? 'Yes' : 'No'; ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
        // Tab switching functionality
        function showTab(tabName) {
            // Hide all tab contents
            document.querySelectorAll('.tab-content').forEach(content => {
                content.classList.remove('active');
            });
            
            // Remove active class from all tabs
            document.querySelectorAll('.tab').forEach(tab => {
                tab.classList.remove('active');
            });
            
            // Show selected tab content
            document.getElementById(tabName + '-tab').classList.add('active');
            
            // Add active class to clicked tab
            event.target.classList.add('active');
        }

        // Fetch professors based on building selection for Add Course
        document.getElementById('add_building_select').addEventListener('change', function() {
            const buildingId = this.value;
            const professorSelect = document.getElementById('add_professor_select');
            
            // Clear existing options
            professorSelect.innerHTML = '<option value="">Select the Professor</option>';
            
            if (buildingId) {
                fetch(`?ajax=get_professors&building_id=${buildingId}`)
                    .then(response => response.json())
                    .then(data => {
                        data.forEach(professor => {
                            const option = document.createElement('option');
                            option.value = professor.id;
                            option.textContent = professor.name;
                            professorSelect.appendChild(option);
                        });
                    })
                    .catch(error => {
                        console.error('Error fetching professors:', error);
                    });
            }
        });

        // Fetch professors and courses based on building selection for Update Course
        document.getElementById('update_building_select').addEventListener('change', function() {
            const buildingId = this.value;
            const courseSelect = document.getElementById('update_course_id');
            
            // Clear existing course options
            courseSelect.innerHTML = '<option value="">Select a course</option>';
            
            if (buildingId) {
                const semesterId = document.getElementById('update_semester_id').value;
                let url = `?ajax=get_courses&building_id=${buildingId}`;
                if (semesterId) {
                    url += `&semester_id=${semesterId}`;
                }
                
                fetch(url)
                    .then(response => response.json())
                    .then(data => {
                        data.forEach(course => {
                            const option = document.createElement('option');
                            option.value = course.id;
                            option.textContent = course.name;
                            courseSelect.appendChild(option);
                        });
                    })
                    .catch(error => {
                        console.error('Error fetching courses:', error);
                    });
            }
        });

        // Fetch courses when semester changes for Update Course
        document.getElementById('update_semester_id').addEventListener('change', function() {
            const semesterId = this.value;
            const buildingId = document.getElementById('update_building_select').value;
            const courseSelect = document.getElementById('update_course_id');
            
            // Clear existing course options
            courseSelect.innerHTML = '<option value="">Select a course</option>';
            
            if (buildingId) {
                let url = `?ajax=get_courses&building_id=${buildingId}`;
                if (semesterId) {
                    url += `&semester_id=${semesterId}`;
                }
                
                fetch(url)
                    .then(response => response.json())
                    .then(data => {
                        data.forEach(course => {
                            const option = document.createElement('option');
                            option.value = course.id;
                            option.textContent = course.name;
                            courseSelect.appendChild(option);
                        });
                    })
                    .catch(error => {
                        console.error('Error fetching courses:', error);
                    });
            }
        });

        // Populate course details when course is selected for update
        function populateCourseDetails() {
            const courseId = document.getElementById('update_course_id').value;
            
            if (courseId) {
                fetch(`?ajax=get_course_details&course_id=${courseId}`)
                    .then(response => response.json())
                    .then(course => {
                        if (course) {
                            document.getElementById('update_course_name').value = course.name || '';
                            document.getElementById('update_credits').value = course.credits || '';
                            document.getElementById('update_lecture_hours').value = course.lecture_hours || '';
                            document.getElementById('update_tutorial_hours').value = course.tutorial_hours || '';
                            document.getElementById('update_practical_hours').value = course.practical_hours || '';
                            document.getElementById('update_students_enrolled').value = course.students_enrolled || '';
                            document.getElementById('update_professor_id').value = course.professor_id || '';
                            document.getElementById('update_requires_projector').value = course.requires_projector || '';
                            
                            // Set radio button for course type
                            const courseTypeRadios = document.querySelectorAll('input[name="update_course_type"]');
                            courseTypeRadios.forEach(radio => {
                                if (radio.value === course.course_type) {
                                    radio.checked = true;
                                }
                            });
                        }
                    })
                    .catch(error => {
                        console.error('Error fetching course details:', error);
                    });
            }
        }

        // Fetch courses based on building selection for Delete Course
        document.getElementById('delete_building_select').addEventListener('change', function() {
            const buildingId = this.value;
            const courseSelect = document.getElementById('delete_course_select');
            
            // Clear existing options
            courseSelect.innerHTML = '<option value="">Select a course</option>';
            
            if (buildingId) {
                fetch(`?ajax=get_courses&building_id=${buildingId}`)
                    .then(response => response.json())
                    .then(data => {
                        data.forEach(course => {
                            const option = document.createElement('option');
                            option.value = course.id;
                            option.textContent = course.name;
                            courseSelect.appendChild(option);
                        });
                    })
                    .catch(error => {
                        console.error('Error fetching courses:', error);
                    });
            }
        });

        // Add event listener for course selection in update form
        document.getElementById('update_course_id').addEventListener('change', populateCourseDetails);
    </script>
</body>
</html>