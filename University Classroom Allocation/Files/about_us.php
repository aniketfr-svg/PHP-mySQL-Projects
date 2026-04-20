<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>University Classroom Allocation</title>
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <!-- Ionicons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons.js"></script>
    <!-- Custom CSS -->
    <style>
        /* Basic Reset */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f7f9fb;
            color: #333;
            padding: 0;
        }

        h1, h2, h3 {
            color: #2c3e50;
        }

        h1 {
            font-size: 36px;
            margin-bottom: 15px;
            text-align: center;
        }

        h2 {
            font-size: 28px;
            margin-top: 20px;
            text-align: center;
        }

        h3 {
            font-size: 22px;
            color: #555;
        }

        p, ul {
            font-size: 16px;
            line-height: 1.8;
            color: #555;
        }

        ul {
            margin-top: 10px;
        }

        /* Container */
        .about-us-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 30px;
            background-color: #fff;
            box-shadow: 0px 10px 30px rgba(0, 0, 0, 0.1);
            border-radius: 10px;
        }

        /* Header */
        .about-header {
            text-align: center;
            margin-bottom: 40px;
        }

        /* Mission Section */
        .about-mission {
            background-color: #ffffff;
            padding: 30px;
            border-radius: 10px;
            margin-bottom: 30px;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
        }

        .about-mission ul {
            margin-top: 10px;
            color: #333;
        }

        /* Why Choose Us */
        .about-why-choose-us {
            margin-top: 40px;
        }

        .feature-box {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
        }

        .feature {
            background-color: #ecf0f1;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
            text-align: center;
            transition: transform 0.3s;
        }

        .feature h3 {
            margin-bottom: 10px;
            color: #2c3e50;
        }

        .feature p {
            color: #7f8c8d;
        }

        .feature:hover {
            transform: translateY(-5px);
        }

        /* Footer */
        .footer {
            background-color: #2c3e50;
            color: #fff;
            padding: 10px 20px;
            text-align: center;
            border-radius: 10px 10px 0 0;
            margin-top: 30px;
        }

        .footer a {
            color: #3498db;
            text-decoration: none;
        }

        .footer .social-icons {
            margin-top: 10px;
        }

        .footer .social-icons a {
            margin: 0 8px;
            font-size: 20px;
            transition: color 0.3s;
        }

        .footer .social-icons a:hover {
            color: #ecf0f1;
        }

        /* Contact Us Section */
        .about-contact {
            padding: 10px;
            border-radius: 8px;
            margin-top: 10px;
            text-align: center;
        }

        .about-contact h2 {
            color: #fff;
            font-size: 20px;
            margin-bottom: 8px;
        }

        .about-contact p {
            color: #ecf0f1;
            font-size: 14px;
        }

        .about-contact a {
            color: #fff;
            text-decoration: underline;
            font-weight: bold;
        }

        /* Copyright Text */
        .footer p {
            font-size: 15px;
            margin-top: 15px;
            color: #dcdcdc; /* Light Gray Color without bold */
        }
    </style>
</head>

<body>
    <div class="about-us-container">
        <section class="about-header">
            <h1>About Us</h1>
            <p>Our project aims to provide an efficient and automated solution for managing classroom schedules at universities. Designed to address the complexities of course scheduling, room allocation, and faculty management, this system ensures that students and faculty members have access to well-organized timetables that minimize conflicts and optimize resource utilization.</p>
        </section>

        <section class="about-mission">
            <h2>Our Mission</h2>
            <ul>
                <li><strong>Simplify scheduling:</strong> Automate and optimize the scheduling process for universities.</li>
                <li><strong>Maximize classroom utilization:</strong> Ensure efficient use of space and resources.</li>
                <li><strong>Resolve timetable conflicts:</strong> Prevent any class overlap for students or faculty.</li>
            </ul>
        </section>

        <section class="about-why-choose-us">
            <h2>Why Choose Us?</h2>
            <div class="feature-box">
                <div class="feature">
                    <h3>Automated Timetable Generation</h3>
                    <p>Our system generates timetables based on credits, availability, and room capacity with no manual effort.</p>
                </div>
                <div class="feature">
                    <h3>Conflict-Free Schedules</h3>
                    <p>We ensure there are no scheduling conflicts for both students and faculty.</p>
                </div>
                <div class="feature">
                    <h3>Smart Room Allocation</h3>
                    <p>Classrooms are allocated based on size, capacity, and course requirements, ensuring optimal usage.</p>
                </div>
                <div class="feature">
                    <h3>Scalable Solution</h3>
                    <p>Whether you have hundreds or thousands of students, our system adapts to your institution's needs.</p>
                </div>
            </div>
        </section>
    </div>

    <!-- Footer -->
    <div class="footer">
        <!-- Contact Us Section -->
        <section class="about-contact">
            <h2>Contact Us</h2>
        </section>
        
        <p>&copy; 2024 University Classroom Allotment. All Rights Reserved.</p>
        <div class="social-icons">
            <a href="#" class="fab fa-facebook"></a>
            <a href="#" class="fab fa-twitter"></a>
            <a href="#" class="fab fa-linkedin"></a>
            <a href="#" class="fab fa-instagram"></a>
        </div>
    </div>
</body>

</html>
