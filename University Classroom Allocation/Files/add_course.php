<?php include('config.php'); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Course</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* Reuse the same styles from manage_courses.php */
        :root {
            --primary-color: #3498db;
            --secondary-color: #2c3e50;
            --accent-color: #e74c3c;
            --light-color: #ecf0f1;
            --dark-color: #34495e;
            --success-color: #2ecc71;
            --warning-color: #f39c12;
            --danger-color: #e74c3c;
            --border-radius: 8px;
            --box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            --transition: all 0.3s ease;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f5f7fa;
            color: var(--dark-color);
            line-height: 1.6;
        }

        .admin-container {
            display: flex;
            min-height: 100vh;
        }

        /* Sidebar styles same as before */
        .sidebar {
            width: 250px;
            background-color: var(--secondary-color);
            color: white;
            padding: 20px 0;
            transition: var(--transition);
            position: fixed;
            height: 100vh;
            z-index: 100;
        }

        /* Main content styles same as before */
        .main-content {
            flex: 1;
            margin-left: 250px;
            padding: 30px;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 1px solid #e0e0e0;
        }

        /* Form container */
        .form-container {
            max-width: 800px;
            margin: 0 auto;
            background-color: white;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            padding: 30px;
        }

        .form-title {
            color: var(--secondary-color);
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 1px solid #eee;
        }

        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: var(--secondary-color);
        }

        .form-control {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #ddd;
            border-radius: var(--border-radius);
            font-family: 'Poppins', sans-serif;
            font-size: 1rem;
            transition: var(--transition);
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.2);
        }

        select.form-control {
            appearance: none;
            background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3e%3cpolyline points='6 9 12 15 18 9'%3e%3c/polyline%3e%3c/svg%3e");
            background-repeat: no-repeat;
            background-position: right 10px center;
            background-size: 1em;
        }

        .radio-group {
            display: flex;
            gap: 20px;
            margin-top: 10px;
        }

        .radio-option {
            display: flex;
            align-items: center;
        }

        .radio-option input {
            margin-right: 8px;
        }

        .btn {
            display: inline-block;
            padding: 12px 25px;
            background-color: var(--primary-color);
            color: white;
            border: none;
            border-radius: var(--border-radius);
            cursor: pointer;
            font-weight: 500;
            transition: var(--transition);
            text-decoration: none;
            text-align: center;
            font-size: 1rem;
            margin-top: 10px;
        }

        .btn:hover {
            background-color: #2980b9;
            color: white;
        }

        .btn-block {
            display: block;
            width: 100%;
        }

        .alert {
            padding: 15px;
            border-radius: var(--border-radius);
            margin-bottom: 20px;
            font-weight: 500;
        }

        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .alert-danger {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        @media (max-width: 768px) {
            .form-grid {
                grid-template-columns: 1fr;
            }
            
            .sidebar {
                width: 70px;
                overflow: hidden;
            }
            
            .sidebar-header h3, .sidebar-menu span {
                display: none;
            }
            
            .main-content {
                margin-left: 70px;
            }
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <!-- Sidebar Navigation (same as before) -->
        <div class="sidebar">
            <div class="sidebar-header">
                <h3>University Admin</h3>
            </div>
            <ul class="sidebar-menu">
                <li><a href="index.php"><i class="fas fa-tachometer-alt"></i> <span>Dashboard</span></a></li>
                <li><a href="manage_courses.php"><i class="fas fa-book"></i> <span>Courses</span></a></li>
                <li><a href="manage_classrooms.php"><i class="fas fa-school"></i> <span>Classrooms</span></a></li>
                <li><a href="professors.php"><i class="fas fa-chalkboard-teacher"></i> <span>Professors</span></a></li>
                <li><a href="manage_buildings.php"><i class="fas fa-building"></i> <span>Buildings</span></a></li>
                <li><a href="generate_timetable.php"><i class="fas fa-calendar-alt"></i> <span>Timetable</span></a></li>
                <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> <span>Logout</span></a></li>
            </ul>
        </div>

        <!-- Main Content Area -->
        <div class="main-content">
            <div class="header">
                <h1>Add New Course</h1>
                <div class="user-profile">
                    <img src="https://ui-avatars.com/api/?name=Admin&background=3498db&color=fff" alt="Admin">
                    <div class="user-info">
                        <strong>Administrator</strong>
                        <small>System Admin</small>
                    </div>
                </div>
            </div>

            <div class="form-container">
                <h2 class="form-title"><i class="fas fa-book"></i> Course Information</h2>
                
                <?php
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
                        echo '<div class="alert alert-success"><i class="fas fa-check-circle"></i> Course added successfully!</div>';
                    } else {
                        echo '<div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i> Failed to add course: ' . $con->error . '</div>';
                    }
                }
                ?>

                <form method="POST">
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="course_name">Course Name</label>
                            <input type="text" id="course_name" name="course_name" class="form-control" placeholder="Enter Course Name" required>
                        </div>

                        <div class="form-group">
                            <label for="building_id">Department/Building</label>
                            <select name="building_id" id="building_id" class="form-control" required>
                                <option value="">Select a Department/Building</option>
                                <?php
                                $buildings = $con->query("SELECT * FROM buildings");
                                while ($row = $buildings->fetch_assoc()) {
                                    echo "<option value='{$row['id']}'>{$row['building_name']}</option>";
                                }
                                ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="credits">Credits</label>
                            <input type="number" id="credits" name="credits" class="form-control" placeholder="Enter Credits" required>
                        </div>

                        <div class="form-group">
                            <label for="lecture_hours">Lecture Hours (L)</label>
                            <input type="number" id="lecture_hours" name="lecture_hours" class="form-control" placeholder="Enter Lecture Hours" required>
                        </div>

                        <div class="form-group">
                            <label for="tutorial_hours">Tutorial Hours (T)</label>
                            <input type="number" id="tutorial_hours" name="tutorial_hours" class="form-control" placeholder="Enter Tutorial Hours" required>
                        </div>

                        <div class="form-group">
                            <label for="practical_hours">Practical Hours (P)</label>
                            <input type="number" id="practical_hours" name="practical_hours" class="form-control" placeholder="Enter Practical Hours" required>
                        </div>

                        <div class="form-group">
                            <label for="students_enrolled">Students Enrolled</label>
                            <input type="number" id="students_enrolled" name="students_enrolled" class="form-control" placeholder="Enter Number of Students" required>
                        </div>

                        <div class="form-group">
                            <label for="professor_id">Professor</label>
                            <select name="professor_id" id="professor_id" class="form-control" required>
                                <option value="">Select the Professor</option>
                                <?php
                                $professors = $con->query("SELECT * FROM professors");
                                while ($row = $professors->fetch_assoc()) {
                                    echo "<option value='{$row['id']}'>{$row['name']}</option>";
                                }
                                ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="semester_id">Semester</label>
                            <select name="semester_id" id="semester_id" class="form-control" required>
                                <option value="">Select the Semester</option>
                                <?php
                                $result = $con->query("SELECT * FROM semester");
                                while ($row = $result->fetch_assoc()) {
                                    echo "<option value='{$row['id']}'>{$row['name']}</option>";
                                }
                                ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="requires_projector">Requires Projector</label>
                            <select name="requires_projector" id="requires_projector" class="form-control" required>
                                <option value="1">Yes</option>
                                <option value="0">No</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>Course Type</label>
                            <div class="radio-group">
                                <label class="radio-option">
                                    <input type="radio" name="course_type" value="Core" required> Core
                                </label>
                                <label class="radio-option">
                                    <input type="radio" name="course_type" value="Elective"> Elective
                                </label>
                                <label class="radio-option">
                                    <input type="radio" name="course_type" value="Open Elective"> Open Elective
                                </label>
                            </div>
                        </div>
                    </div>

                    <button type="submit" name="add_course" class="btn btn-block">
                        <i class="fas fa-save"></i> Add Course
                    </button>
                </form>
            </div>
        </div>
    </div>

    <script>
    document.getElementById("building_id").addEventListener("change", function () {
        var buildingId = this.value;
        var professorSelect = document.getElementById("professor_id");
        professorSelect.innerHTML = '<option value="">Select the Professor</option>';

        if (buildingId) {
            fetch("fetch_professors.php?building_id=" + buildingId)
                .then(response => response.json())
                .then(data => {
                    data.forEach(professor => {
                        var option = document.createElement("option");
                        option.value = professor.id;
                        option.textContent = professor.name;
                        professorSelect.appendChild(option);
                    });
                })
                .catch(error => console.error("Error fetching professors:", error));
        }
    });
    </script>
</body>
</html>