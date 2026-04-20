<?php
include('config.php');

// If course ID is provided, return full details for that course
if (isset($_GET['id'])) {
    $course_id = $con->real_escape_string($_GET['id']);

    $query = "SELECT * FROM courses WHERE id='$course_id'";
    $result = $con->query($query);

    if ($result && $result->num_rows > 0) {
        $course = $result->fetch_assoc();

        echo json_encode([
            'id' => $course['id'],
            'name' => $course['name'],
            'building_id' => $course['building_id'],
            'credits' => $course['credits'],
            'lecture_hours' => $course['lecture_hours'],
            'tutorial_hours' => $course['tutorial_hours'],
            'practical_hours' => $course['practical_hours'],
            'students_enrolled' => $course['students_enrolled'],
            'professor_id' => $course['professor_id'],
            'semester_id' => $course['semester_id'],
            'requires_projector' => $course['requires_projector'],
            'course_type' => $course['course_type']
        ]);
    } else {
        echo json_encode(['error' => 'Course not found']);
    }

// If building_id and semester_id are provided, return matching course list
} elseif (isset($_GET['building_id']) && isset($_GET['semester_id'])) {
    $building_id = $con->real_escape_string($_GET['building_id']);
    $semester_id = $con->real_escape_string($_GET['semester_id']);

    $query = "SELECT id, name FROM courses WHERE building_id='$building_id' AND semester_id='$semester_id'";
    $result = $con->query($query);

    $courses = [];
    while ($row = $result->fetch_assoc()) {
        $courses[] = $row;
    }

    echo json_encode($courses);
    
} else {
    echo json_encode(['error' => 'Insufficient parameters provided']);
}
?>
