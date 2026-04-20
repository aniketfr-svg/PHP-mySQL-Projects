<?php
include('config.php');
session_start();

// Check if professor is logged in
if (!isset($_SESSION['professor_logged_in']) || !isset($_SESSION['building_id'])) {
    header("Location: login.php");
    exit;
}

$building_id = $_SESSION['building_id']; // Department's building ID

// Days and time slots
$days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];
$time_slots = ['9:00-10:00', '10:00-11:00', '11:00-12:00', '12:00-1:00', '2:00-3:00', '3:00-4:00', '4:00-5:00'];

// Fetch classrooms in this building
$classrooms = [];
$classroom_query = $con->query("SELECT * FROM classrooms WHERE building_id = $building_id");
while ($row = $classroom_query->fetch_assoc()) {
    $classrooms[$row['id']] = $row;
}

// Fetch department courses
$course_query = $con->query("SELECT * FROM courses WHERE building_id = $building_id ORDER BY lecture_hours DESC, tutorial_hours DESC");

// Initialize schedules
$professor_schedule = [];
$group_schedule = [];
$classroom_schedule = [];
$allocated_courses_per_day = [];

// Clear previous schedule for department
$con->query("DELETE FROM department_schedule WHERE building_id = $building_id");
// Clear old timetable
$con->query("TRUNCATE TABLE department_schedule");
// Allocate timetable
foreach ($days as $day) {
    foreach ($time_slots as $time) {
        // Reset allocated courses for this time
        foreach ($course_query as $course) {
            $course_id = $course['id'];
            $professor_id = $course['professor_id'];
            $semester_id = $course['semester_id'];
            $students_enrolled = $course['students_enrolled'];
            $requires_projector = $course['requires_projector'];

            // Skip if already scheduled for this day
            if (isset($allocated_courses_per_day[$day][$course_id])) continue;

            foreach ($classrooms as $classroom_id => $classroom) {
                if (
                    $classroom['capacity'] >= $students_enrolled &&
                    (!$requires_projector || $classroom['has_projector']) &&
                    !isset($professor_schedule[$professor_id][$day][$time]) &&
                    !isset($group_schedule[$semester_id][$day][$time]) &&
                    !isset($classroom_schedule[$classroom_id][$day][$time])
                ) {
                    // Allocate the course
                    $stmt = $con->prepare("INSERT INTO department_schedule (course_id, classroom_id, day, time_slot, semester_id, building_id)
                                           VALUES (?, ?, ?, ?, ?, ?)");
                    $stmt->bind_param("iissii", $course_id, $classroom_id, $day, $time, $semester_id, $building_id);
                    $stmt->execute();

                    // Mark slot as taken
                    $professor_schedule[$professor_id][$day][$time] = true;
                    $group_schedule[$semester_id][$day][$time] = true;
                    $classroom_schedule[$classroom_id][$day][$time] = true;
                    $allocated_courses_per_day[$day][$course_id] = true;

                    break; // move to next course
                }
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Department Timetable Generation</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #e3f2fd;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .message-box {
            text-align: center;
            background: #fff;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 0 12px rgba(0, 0, 0, 0.15);
        }
        .message-box h1 {
            color: #2c3e50;
        }
        .message-box a {
            display: inline-block;
            margin: 15px 10px 0;
            padding: 10px 20px;
            background: #2196f3;
            color: #fff;
            border-radius: 6px;
            text-decoration: none;
        }
        .message-box a:hover {
            background: #1976d2;
        }
    </style>
</head>
<body>
<div class="message-box">
    <h1>Department Timetable Generated Successfully!</h1>
    <a href="view_department_timetable.php">View Timetable</a>
    <a href="professor_dashboard.php">Back to Dashboard</a>
</div>
</body>
</html>
