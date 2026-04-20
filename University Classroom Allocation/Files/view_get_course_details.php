<?php
include('config.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $day = $_POST['day'] ?? '';
    $time_slot = $_POST['time_slot'] ?? '';
    $semester_id = $_POST['semester_id'] ?? '';

    if (empty($day) || empty($time_slot) || empty($semester_id)) {
        echo '<div style="text-align: center; padding: 2rem; color: #ef4444;">Invalid request parameters.</div>';
        exit;
    }

    // Fetch detailed course information
    $query = "
        SELECT 
            schedule.id as schedule_id,
            schedule.day,
            schedule.time_slot,
            courses.id as course_id,
            
            courses.name AS course_name,
            
            courses.credits,
            courses.students_enrolled,
            courses.max_students,
            classrooms.id as classroom_id,
            classrooms.name AS classroom_name,
            classrooms.capacity,
            classrooms.has_projector,
            classrooms.has_whiteboard,
            classrooms.has_computer,
            COALESCE(buildings.building_name, 'N/A') AS building_name,
            COALESCE(buildings.address, 'N/A') AS building_address,
            COALESCE(professors.name, 'N/A') AS professor_name,
            COALESCE(professors.email, 'N/A') AS professor_email,
            COALESCE(professors.phone, 'N/A') AS professor_phone,
            semester.name AS semester_name
        FROM schedule 
        JOIN courses ON schedule.course_id = courses.id 
        JOIN classrooms ON schedule.classroom_id = classrooms.id 
        LEFT JOIN buildings ON classrooms.building_id = buildings.id
        LEFT JOIN professors ON courses.professor_id = professors.id
        JOIN semester ON courses.semester_id = semester.id
        WHERE schedule.day = ? AND schedule.time_slot = ? AND courses.semester_id = ?
        ORDER BY courses.name
    ";
    
    $stmt = $con->prepare($query);
    $stmt->bind_param("ssi", $day, $time_slot, $semester_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $courses = [];
    while ($row = $result->fetch_assoc()) {
        $courses[] = $row;
    }

    if (empty($courses)) {
        echo '<div style="text-align: center; padding: 2rem; color: #6b7280;">
                <i class="fas fa-calendar-times" style="font-size: 3rem; margin-bottom: 1rem; opacity: 0.5;"></i>
                <h3 style="margin: 0; color: #374151;">No Classes Scheduled</h3>
                <p style="margin: 0.5rem 0 0 0; opacity: 0.7;">This time slot is currently free.</p>
              </div>';
        exit;
    }

    echo '<div class="course-details-list">';
    
    foreach ($courses as $course) {
        $capacity_percentage = round(($course['students_enrolled'] / $course['capacity']) * 100);
        $enrollment_percentage = $course['max_students'] > 0 ? round(($course['students_enrolled'] / $course['max_students']) * 100) : 0;
        
        // Determine status
        $status_class = 'status-available';
        $status_text = 'Available';
        $status_icon = 'fa-check-circle';
        
        if ($course['students_enrolled'] >= $course['max_students']) {
            $status_class = 'status-overcapacity';
            $status_text = 'Full';
            $status_icon = 'fa-exclamation-circle';
        } elseif ($enrollment_percentage > 90) {
            $status_class = 'status-warning';
            $status_text = 'Nearly Full';
            $status_icon = 'fa-exclamation-triangle';
        }
        
        echo '<div class="course-detail-card">';
        
        // Course header
        echo '<div class="course-detail-header">';
        echo '<div class="course-title">' . htmlspecialchars($course['course_name']) . '</div>';
       
        echo '</div>';
        
        // Course description
       // if (!empty($course['description'])) {
        //    echo '<div style="margin-bottom: 1rem; padding: 0.75rem; background: #f1f5f9; border-radius: 8px; font-size: 0.9rem; color: #475569; line-height: 1.5;">';
        //    echo htmlspecialchars($course['description']);
         //   echo '</div>';
       // }
        
        // Details grid
        echo '<div class="detail-grid">';
        
        // Basic course info
        echo '<div>';
        echo '<div class="detail-item">';
        echo '<span class="detail-label">Professor</span>';
        echo '<span class="detail-value">' . htmlspecialchars($course['professor_name']) . '</span>';
        echo '</div>';
        echo '<div class="detail-item">';
        echo '<span class="detail-label">Credits</span>';
        echo '<span class="detail-value">' . $course['credits'] . '</span>';
        echo '</div>';
        echo '<div class="detail-item">';
        echo '<span class="detail-label">Semester</span>';
        echo '<span class="detail-value">' . htmlspecialchars($course['semester_name']) . '</span>';
        echo '</div>';
        echo '</div>';
        
        // Location info
        echo '<div>';
        echo '<div class="detail-item">';
        echo '<span class="detail-label">Classroom</span>';
        echo '<span class="detail-value">' . htmlspecialchars($course['classroom_name']) . '</span>';
        echo '</div>';
        echo '<div class="detail-item">';
        echo '<span class="detail-label">Building</span>';
        echo '<span class="detail-value">' . htmlspecialchars($course['building_name']) . '</span>';
        echo '</div>';
        echo '<div class="detail-item">';
        echo '<span class="detail-label">Room Capacity</span>';
        echo '<span class="detail-value">' . $course['capacity'] . ' seats</span>';
        echo '</div>';
        echo '</div>';
        
        echo '</div>'; // End detail-grid
        
        // Enrollment status
        echo '<div style="margin-top: 1rem; padding: 0.75rem; background: #f8fafc; border-radius: 8px; border-left: 4px solid #3b82f6;">';
        echo '<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">';
        echo '<span style="font-weight: 600; color: #1a202c;">Enrollment Status</span>';
        echo '<span class="status-badge ' . $status_class . '">';
        echo '<i class="fas ' . $status_icon . '"></i> ' . $status_text;
        echo '</span>';
        echo '</div>';
        
        // Enrollment bar
        echo '<div style="display: flex; align-items: center; gap: 0.75rem;">';
        echo '<span style="font-size: 0.875rem; color: #6b7280; min-width: 120px;">';
        echo $course['students_enrolled'] . ' / ' . $course['max_students'] . ' students';
        echo '</span>';
        echo '<div style="flex: 1; height: 8px; background: #e5e7eb; border-radius: 4px; overflow: hidden;">';
        echo '<div style="height: 100%; background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%); width: ' . min($enrollment_percentage, 100) . '%; transition: width 0.3s ease;"></div>';
        echo '</div>';
        echo '<span style="font-size: 0.875rem; font-weight: 600; color: #1a202c; min-width: 40px;">';
        echo $enrollment_percentage . '%';
        echo '</span>';
        echo '</div>';
        
        // Classroom utilization
        if ($course['capacity'] > 0) {
            echo '<div style="display: flex; align-items: center; gap: 0.75rem; margin-top: 0.5rem;">';
            echo '<span style="font-size: 0.875rem; color: #6b7280; min-width: 120px;">Room Usage</span>';
            echo '<div style="flex: 1; height: 6px; background: #e5e7eb; border-radius: 3px; overflow: hidden;">';
            $room_color = $capacity_percentage > 100 ? '#ef4444' : ($capacity_percentage > 90 ? '#f59e0b' : '#10b981');
            echo '<div style="height: 100%; background: ' . $room_color . '; width: ' . min($capacity_percentage, 100) . '%; transition: width 0.3s ease;"></div>';
            echo '</div>';
            echo '<span style="font-size: 0.875rem; font-weight: 600; color: #1a202c; min-width: 40px;">';
            echo $capacity_percentage . '%';
            echo '</span>';
            echo '</div>';
        }
        echo '</div>';
        
        // Classroom facilities
        $facilities = [];
        if ($course['has_projector']) $facilities[] = '<i class="fas fa-video" title="Projector"></i> Projector';
        if ($course['has_whiteboard']) $facilities[] = '<i class="fas fa-chalkboard" title="Whiteboard"></i> Whiteboard';
        if ($course['has_computer']) $facilities[] = '<i class="fas fa-desktop" title="Computer"></i> Computer';
        
        if (!empty($facilities)) {
            echo '<div style="margin-top: 1rem; padding: 0.5rem 0; border-top: 1px solid #e5e7eb;">';
            echo '<span style="font-size: 0.75rem; color: #6b7280; text-transform: uppercase; font-weight: 600; letter-spacing: 0.05em;">Facilities:</span> ';
            echo '<div style="margin-top: 0.25rem; display: flex; gap: 1rem; flex-wrap: wrap;">';
            foreach ($facilities as $facility) {
                echo '<span style="font-size: 0.8rem; color: #059669; display: flex; align-items: center; gap: 0.25rem;">' . $facility . '</span>';
            }
            echo '</div>';
            echo '</div>';
        }
        
        // Contact information
        if ($course['professor_email'] !== 'N/A' || $course['professor_phone'] !== 'N/A') {
            echo '<div style="margin-top: 1rem; padding: 0.5rem 0; border-top: 1px solid #e5e7eb;">';
            echo '<span style="font-size: 0.75rem; color: #6b7280; text-transform: uppercase; font-weight: 600; letter-spacing: 0.05em;">Contact:</span>';
            echo '<div style="margin-top: 0.25rem; font-size: 0.8rem; color: #374151;">';
            if ($course['professor_email'] !== 'N/A') {
                echo '<div><i class="fas fa-envelope" style="width: 16px; color: #6b7280;"></i> ' . htmlspecialchars($course['professor_email']) . '</div>';
            }
            if ($course['professor_phone'] !== 'N/A') {
                echo '<div><i class="fas fa-phone" style="width: 16px; color: #6b7280;"></i> ' . htmlspecialchars($course['professor_phone']) . '</div>';
            }
            echo '</div>';
            echo '</div>';
        }
        
        echo '</div>'; // End course-detail-card
    }
    
    echo '</div>'; // End course-details-list
    
    // Add some additional styling for this modal content
    echo '<style>
        .status-warning {
            background: #fef3c7;
            color: #92400e;
        }
        
        .course-detail-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(59, 130, 246, 0.15);
        }
        
        .detail-item:hover {
            background: #f8fafc;
            margin: 0 -0.5rem;
            padding: 0.5rem;
            border-radius: 6px;
        }
    </style>';
    
} else {
    echo '<div style="text-align: center; padding: 2rem; color: #ef4444;">Invalid request method.</div>';
}
?>