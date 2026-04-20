// Function to populate the course details when updating a course
function populateCourseDetails() {
    const courseId = document.getElementById('update_course_id').value;

    if (courseId) {
        // Fetch course details from the server using AJAX
        fetch('get_course_details.php?id=' + courseId)
            .then(response => response.json())
            .then(data => {
                document.getElementById('update_course_name').value = data.name;
                document.getElementById('update_credits').value = data.credits;
                document.getElementById('update_lecture_hours').value = data.lecture_hours;
                document.getElementById('update_tutorial_hours').value = data.tutorial_hours;
                document.getElementById('update_practical_hours').value = data.practical_hours;
                document.getElementById('update_students_enrolled').value = data.students_enrolled;
                document.getElementById('update_professor_id').value = data.professor_id;
                document.getElementById('update_semester_id').value = data.semester_id;
            })
            .catch(error => console.error('Error fetching course details:', error));
    }
}

// Function to populate the classroom details when updating a classroom
function populateClassroomDetails() {
    const classroomId = document.getElementById('update_classroom_id').value;

    if (classroomId) {
        // Fetch classroom details from the server using AJAX
        fetch('get_classroom_details.php?id=' + classroomId)
            .then(response => response.json())
            .then(data => {
                document.getElementById('update_classroom_name').value = data.name;
                document.getElementById('update_capacity').value = data.capacity;
            })
            .catch(error => console.error('Error fetching classroom details:', error));
    }
}

document.getElementById("update_course_form").addEventListener("submit", function(e) {
    const courseName = document.getElementById("update_course_name").value;
    if (!courseName) {
        alert("Course name cannot be empty");
        e.preventDefault();  // Prevent form submission
    }
});



