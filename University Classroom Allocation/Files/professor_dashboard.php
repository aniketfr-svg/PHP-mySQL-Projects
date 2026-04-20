<?php
session_start();
if (!isset($_SESSION['professor_logged_in'])) {
    header("Location: login.php");
    exit;
}
include('config.php');

$building_id = $_SESSION['building_id'];

// Fetch number of courses in department
$courses = $con->query("SELECT * FROM courses WHERE building_id = $building_id");
$totalCourses = $courses->num_rows;

// Fetch professors in department
$profs = $con->query("SELECT * FROM professors WHERE building_id = $building_id");
$totalProfs = $profs->num_rows;
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Professor Dashboard | University Classroom Allocation</title>
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&family=Open+Sans:wght@400;600;700&display=swap" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <!-- Ionicons -->
    <script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>
    <!-- Custom CSS -->
    <style>
        :root {
            --primary-color: #0056b3;
            --secondary-color: #003366;
            --accent-color: #ff6b00;
            --light-color: #f8f9fa;
            --dark-color: #212529;
            --success-color: #28a745;
            --danger-color: #dc3545;
            --warning-color: #ffc107;
            --info-color: #17a2b8;
            --border-radius: 4px;
            --box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            --transition: all 0.3s ease;
        }

        /* Base Styles */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Open Sans', sans-serif;
            line-height: 1.6;
            color: #333;
            background-color: #f5f5f5;
        }

        a {
            text-decoration: none;
            color: var(--primary-color);
            transition: var(--transition);
        }

        a:hover {
            color: var(--secondary-color);
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 15px;
        }

        /* Header Styles */
        .gov-header {
            background-color: var(--secondary-color);
            color: white;
            padding: 15px 0;
            border-bottom: 5px solid var(--accent-color);
        }

        .header-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo {
            display: flex;
            align-items: center;
        }

        .logo img {
            height: 50px;
            margin-right: 15px;
        }

        .logo-text h1 {
            font-size: 1.8rem;
            font-weight: 700;
            margin-bottom: 5px;
            font-family: 'Roboto', sans-serif;
        }

        .logo-text p {
            font-size: 0.9rem;
            opacity: 0.9;
        }

        .user-controls {
            display: flex;
            align-items: center;
        }

        .user-info {
            margin-right: 20px;
            text-align: right;
        }

        .user-name {
            font-weight: 600;
        }

        .user-role {
            font-size: 0.8rem;
            opacity: 0.8;
        }

        .logout-btn {
            background-color: var(--accent-color);
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: var(--border-radius);
            cursor: pointer;
            font-weight: 600;
            transition: var(--transition);
        }

        .logout-btn:hover {
            background-color: #e05d00;
        }

        /* Main Content */
        .main-content {
            padding: 30px 0;
            background-color: white;
            min-height: calc(100vh - 120px);
            box-shadow: var(--box-shadow);
        }

        .page-header {
            margin-bottom: 30px;
            padding-bottom: 15px;
            border-bottom: 1px solid #ddd;
        }

        .page-header h2 {
            color: var(--secondary-color);
            font-weight: 700;
            font-size: 1.8rem;
        }

        .greeting {
            font-size: 1.1rem;
            color: #555;
            margin-top: 5px;
        }

        /* Dashboard Cards */
        .dashboard-cards {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .card {
            background-color: white;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            overflow: hidden;
            transition: var(--transition);
            border: 1px solid #ddd;
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        .card-header {
            background-color: var(--primary-color);
            color: white;
            padding: 15px;
            font-weight: 600;
            display: flex;
            align-items: center;
        }

        .card-header i {
            margin-right: 10px;
            font-size: 1.2rem;
        }

        .card-body {
            padding: 20px;
        }

        .card-value {
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--secondary-color);
            margin-bottom: 10px;
        }

        .card-text {
            color: #666;
            font-size: 0.9rem;
        }

        .card-link {
            display: inline-block;
            margin-top: 15px;
            color: var(--primary-color);
            font-weight: 600;
        }

        .card-link:hover {
            text-decoration: underline;
        }

        /* Quick Actions */
        .quick-actions {
            margin-bottom: 30px;
        }

        .section-title {
            font-size: 1.4rem;
            color: var(--secondary-color);
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
            font-weight: 600;
        }

        .action-buttons {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 15px;
        }

        .action-btn {
            background-color: white;
            border: 1px solid #ddd;
            border-radius: var(--border-radius);
            padding: 15px;
            text-align: center;
            transition: var(--transition);
            box-shadow: var(--box-shadow);
        }

        .action-btn:hover {
            background-color: var(--primary-color);
            color: white;
            border-color: var(--primary-color);
        }

        .action-btn i {
            font-size: 2rem;
            margin-bottom: 10px;
            display: block;
            color: var(--primary-color);
        }

        .action-btn:hover i {
            color: white;
        }

        .action-btn span {
            font-weight: 600;
            display: block;
        }

        /* Announcements */
        .announcements {
            background-color: #f8f9fa;
            border-radius: var(--border-radius);
            padding: 20px;
            margin-bottom: 30px;
            border: 1px solid #ddd;
        }

        .announcement-item {
            padding: 10px 0;
            border-bottom: 1px solid #eee;
        }

        .announcement-item:last-child {
            border-bottom: none;
        }

        .announcement-date {
            font-size: 0.8rem;
            color: #666;
            margin-bottom: 5px;
        }

        .announcement-title {
            font-weight: 600;
            margin-bottom: 5px;
        }

        .announcement-text {
            font-size: 0.9rem;
            color: #555;
        }

        /* Footer */
        .gov-footer {
            background-color: var(--secondary-color);
            color: white;
            padding: 20px 0;
            text-align: center;
            font-size: 0.9rem;
        }

        .footer-links {
            display: flex;
            justify-content: center;
            margin-bottom: 15px;
            flex-wrap: wrap;
        }

        .footer-link {
            color: white;
            margin: 0 15px;
            padding: 5px 0;
        }

        .footer-link:hover {
            color: var(--accent-color);
        }

        .copyright {
            opacity: 0.8;
        }

        /* Responsive Adjustments */
        @media (max-width: 768px) {
            .header-container {
                flex-direction: column;
                text-align: center;
            }

            .user-controls {
                margin-top: 15px;
                flex-direction: column;
            }

            .user-info {
                text-align: center;
                margin-right: 0;
                margin-bottom: 10px;
            }

            .dashboard-cards {
                grid-template-columns: 1fr;
            }
            
            .action-buttons {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>

<body>
    <!-- Government Style Header -->
    <header class="gov-header">
        <div class="container header-container">
            <div class="logo">
                <img src="./backround/images.png" alt="University Logo">
                <div class="logo-text">
                    <h1>University Classroom Allocation</h1>
                    <p>Professor Dashboard</p>
                </div>
            </div>
            <div class="user-controls">
                <div class="user-info">
                    <div class="user-name">Professor</div>
                    <div class="user-role">Department View</div>
                </div>
                <a href="logout.php" class="logout-btn">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="main-content">
        <div class="container">
            <!-- Page Header -->
            <div class="page-header">
                <h2>Professor Dashboard</h2>
                <div class="greeting">
                    <?php
                    $hour = date("H");
                    if ($hour < 12) {
                        echo "Good Morning!";
                    } elseif ($hour < 18) {
                        echo "Good Afternoon!";
                    } else {
                        echo "Good Evening!";
                    }
                    ?>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="quick-actions">
                <h3 class="section-title">Quick Actions</h3>
                <div class="action-buttons">
                    <a href="generate_timetable.php" class="action-btn">
                        <i class="fas fa-clock"></i>
                        <span>Generate Timetable</span>
                    </a>
                    <a href="view_timetable.php" class="action-btn">
                        <i class="fas fa-calendar-alt"></i>
                        <span>View Timetable</span>
                    </a>
                    <a href="professor_timetable.php" class="action-btn">
                        <i class="fas fa-chalkboard-teacher"></i>
                        <span>Professor Timetable</span>
                    </a>
                    <a href="professors_manage_courses.php" class="action-btn">
                        <i class="fas fa-book"></i>
                        <span>Manage Department Courses</span>
                    </a>
                    <a href="professors_view.php" class="action-btn">
                        <i class="fas fa-users"></i>
                        <span>View Professors</span>
                    </a>
                    <a href="manual_department_schedule.php" class="action-btn">
                        <i class="fas fa-calendar-plus"></i>
                        <span>Manual Schedule</span>
                    </a>
                </div>
            </div>

            <!-- Statistics Cards -->
            <div class="dashboard-cards">
                <div class="card">
                    <div class="card-header">
                        <i class="fas fa-book"></i> Department Courses
                    </div>
                    <div class="card-body">
                        <div class="card-value"><?php echo $totalCourses; ?></div>
                        <p class="card-text">Currently registered in department</p>
                        <a href="professors_manage_courses.php" class="card-link">Manage Courses <i class="fas fa-arrow-right"></i></a>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <i class="fas fa-users"></i> Department Professors
                    </div>
                    <div class="card-body">
                        <div class="card-value"><?php echo $totalProfs; ?></div>
                        <p class="card-text">Teaching in department</p>
                        <a href="professors_view.php" class="card-link">View Professors <i class="fas fa-arrow-right"></i></a>
                    </div>
                </div>
            </div>

            <!-- Announcements -->
            <div class="announcements">
                <h3 class="section-title">Department Announcements</h3>
                <div class="announcement-item">
                    <div class="announcement-date">May 15, 2024</div>
                    <div class="announcement-title">New Semester Schedule</div>
                    <div class="announcement-text">The fall semester schedule will be available for review on June 1st. Please prepare your course requirements.</div>
                </div>
                <div class="announcement-item">
                    <div class="announcement-date">May 10, 2024</div>
                    <div class="announcement-title">Department Meeting</div>
                    <div class="announcement-text">Next department meeting scheduled for May 25th at 10:00 AM in Building A Auditorium.</div>
                </div>
            </div>
        </div>
    </main>

    <!-- Government Style Footer -->
    <footer class="gov-footer">
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
    </footer>

    <!-- Scripts -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            console.log('Professor dashboard loaded');
        });
    </script>
</body>

</html>