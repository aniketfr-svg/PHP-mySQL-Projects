<?php
session_start();
include('config.php');

if (!isset($_SESSION['building_id'])) {
    echo "Building ID not set in session.";
    exit;
}

$building_id = $_SESSION['building_id'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Department Timetable</title>
    <link rel="stylesheet" href="style.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            background-color: #f4f4f4;
        }
        .container {
            width: 90%;
            margin: auto;
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        h1 {
            text-align: center;
            font-size: 1.5em;
            margin-bottom: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.8em;
        }
        table thead {
            background-color: #007BFF;
            color: white;
        }
        table th, table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: center;
        }
        table tbody tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        table tbody tr:hover {
            background-color: #f1f1f1;
        }
        a {
            text-decoration: none;
            color: #007BFF;
            font-size: 0.9em;
        }
        a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
<div class="container">
    <h1>Department Timetable</h1>

    <table>
        <thead>
        <tr>
            <th>Day</th>
            <th>Semester</th>
            <th>9:00-10:00</th>
            <th>10:00-11:00</th>
            <th>11:00-12:00</th>
            <th>12:00-1:00</th>
            <th>2:00-3:00</th>
            <th>3:00-4:00</th>
            <th>4:00-5:00</th>
        </tr>
        </thead>
        <tbody>
        <?php
        $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];
        $time_slots = ['9:00-10:00', '10:00-11:00', '11:00-12:00', '12:00-1:00', '2:00-3:00', '3:00-4:00', '4:00-5:00'];

        $semesters_query = "
            SELECT DISTINCT s.id, s.name 
            FROM semester s
            JOIN courses c ON s.id = c.semester_id
            JOIN department_schedule ds ON c.id = ds.course_id
            WHERE ds.building_id = $building_id
        ";

        $semesters_result = $con->query($semesters_query);
        $semesters = [];
        while ($row = $semesters_result->fetch_assoc()) {
            $semesters[] = $row;
        }

        foreach ($days as $day) {
            $first_row = true;
            foreach ($semesters as $semester) {
                $semester_id = $semester['id'];
                $semester_name = $semester['name'];

                echo $first_row ? "<tr><td rowspan=\"" . count($semesters) . "\">$day</td>" : "<tr>";
                $first_row = false;

                echo "<td>$semester_name</td>";

                foreach ($time_slots as $slot) {
                    echo "<td>";
                    $query = "
                        SELECT ds.*, c.name AS course_name, cr.name AS classroom_name
                        FROM department_schedule ds
                        JOIN courses c ON ds.course_id = c.id
                        JOIN classrooms cr ON ds.classroom_id = cr.id
                        WHERE ds.day = '$day'
                          AND ds.time_slot = '$slot'
                          AND ds.building_id = $building_id
                          AND c.semester_id = $semester_id
                        LIMIT 1
                    ";
                    $result = $con->query($query);
                    if ($row = $result->fetch_assoc()) {
                        echo "<a href='#' class='classroom-link' 
                                 data-course-name='" . htmlspecialchars($row['course_name']) . "' 
                                 data-classroom-id='" . $row['classroom_id'] . "'>";
                        echo htmlspecialchars($row['course_name']);
                        echo "</a>";
                    } else {
                        echo "-";
                    }
                    echo "</td>";
                }

                echo "</tr>";
            }
        }
        ?>
        </tbody>
    </table>

    <a href="professor_dashboard.php" style="margin-top: 20px; display: block;">Back</a>
</div>

<!-- Modal for Classroom Details -->
<div id="modal" style="display:none; position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%); background-color: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1); z-index:1000;">
    <h2>Classroom Details</h2>
    <p><strong>Name: </strong><span id="modalClassroomName"></span></p>
    <p><strong>Projector Available: </strong><span id="modalProjector"></span></p>
    <p><strong>Building: </strong><span id="modalBuildingName"></span></p>
    <button onclick="document.getElementById('modal').style.display='none'">Close</button>
</div>

<script>
document.querySelectorAll('.classroom-link').forEach(item => {
    item.addEventListener('click', function(e) {
        e.preventDefault();
        var classroomId = this.getAttribute('data-classroom-id');

        fetch('get_classrooms_details.php?id=' + classroomId)
            .then(response => response.json())
            .then(data => {
                if (data.error) {
                    alert(data.error);
                } else {
                    document.getElementById('modalClassroomName').innerText = data.classroom_name;
                    document.getElementById('modalProjector').innerText = data.has_projector == 1 ? 'Yes' : 'No';
                    document.getElementById('modalBuildingName').innerText = data.building_name;
                    document.getElementById('modal').style.display = "block";
                }
            })
            .catch(err => {
                console.error('Error fetching classroom details:', err);
            });
    });
});
</script>
</body>
</html>
