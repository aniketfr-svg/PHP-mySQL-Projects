<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Academic Portal - Login</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #2563eb;
            --primary-hover: #1d4ed8;
            --secondary-color: #64748b;
            --success-color: #10b981;
            --error-color: #ef4444;
            --warning-color: #f59e0b;
            --background-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --card-background: rgba(255, 255, 255, 0.95);
            --text-primary: #1e293b;
            --text-secondary: #64748b;
            --border-color: #e2e8f0;
            --shadow-primary: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
            --shadow-secondary: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: var(--background-gradient);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            position: relative;
            overflow-x: hidden;
        }

        body::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grid" width="10" height="10" patternUnits="userSpaceOnUse"><path d="M 10 0 L 0 0 0 10" fill="none" stroke="rgba(255,255,255,0.05)" stroke-width="1"/></pattern></defs><rect width="100" height="100" fill="url(%23grid)"/></svg>');
            z-index: 1;
        }

        .login-wrapper {
            position: relative;
            z-index: 2;
            width: 100%;
            max-width: 900px;
            display: grid;
            grid-template-columns: 1fr 1fr;
            background: var(--card-background);
            border-radius: 24px;
            box-shadow: var(--shadow-primary);
            overflow: hidden;
            backdrop-filter: blur(10px);
        }

        .login-info {
            background: linear-gradient(135deg, var(--primary-color), var(--primary-hover));
            padding: 60px 40px;
            color: white;
            display: flex;
            flex-direction: column;
            justify-content: center;
            position: relative;
        }

        .login-info::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><circle cx="20" cy="20" r="2" fill="rgba(255,255,255,0.1)"/><circle cx="80" cy="40" r="3" fill="rgba(255,255,255,0.05)"/><circle cx="40" cy="80" r="2" fill="rgba(255,255,255,0.1)"/></svg>');
            opacity: 0.6;
        }

        .login-info-content {
            position: relative;
            z-index: 1;
        }

        .logo {
            display: flex;
            align-items: center;
            margin-bottom: 40px;
        }

        .logo i {
            font-size: 2.5rem;
            margin-right: 15px;
            background: rgba(255, 255, 255, 0.2);
            padding: 15px;
            border-radius: 12px;
        }

        .logo h1 {
            font-size: 1.75rem;
            font-weight: 700;
            line-height: 1.2;
        }

        .welcome-text h2 {
            font-size: 2.25rem;
            font-weight: 600;
            margin-bottom: 20px;
            line-height: 1.2;
        }

        .welcome-text p {
            font-size: 1.1rem;
            opacity: 0.9;
            line-height: 1.6;
            margin-bottom: 30px;
        }

        .features {
            list-style: none;
        }

        .features li {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
            font-size: 0.95rem;
        }

        .features li i {
            margin-right: 12px;
            font-size: 1.1rem;
            opacity: 0.8;
        }

        .login-form-container {
            padding: 60px 40px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .form-header {
            text-align: center;
            margin-bottom: 40px;
        }

        .form-header h3 {
            font-size: 1.875rem;
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: 8px;
        }

        .form-header p {
            color: var(--text-secondary);
            font-size: 0.95rem;
        }

        .alert {
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 0.875rem;
            font-weight: 500;
            display: flex;
            align-items: center;
        }

        .alert i {
            margin-right: 8px;
            font-size: 1rem;
        }

        .alert-error {
            background: rgba(239, 68, 68, 0.1);
            color: var(--error-color);
            border: 1px solid rgba(239, 68, 68, 0.2);
        }

        .alert-success {
            background: rgba(16, 185, 129, 0.1);
            color: var(--success-color);
            border: 1px solid rgba(16, 185, 129, 0.2);
        }

        .form-group {
            margin-bottom: 24px;
            position: relative;
        }

        .form-group label {
            display: block;
            font-weight: 500;
            color: var(--text-primary);
            margin-bottom: 8px;
            font-size: 0.875rem;
        }

        .input-wrapper {
            position: relative;
        }

        .input-wrapper i {
            position: absolute;
            left: 16px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-secondary);
            font-size: 1rem;
        }

        .form-control {
            width: 100%;
            padding: 14px 16px 14px 48px;
            border: 2px solid var(--border-color);
            border-radius: 12px;
            font-size: 0.95rem;
            background: white;
            color: var(--text-primary);
            transition: all 0.2s ease;
            font-family: inherit;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        }

        .form-control::placeholder {
            color: var(--text-secondary);
        }

        select.form-control {
            padding-left: 48px;
            cursor: pointer;
        }

        .btn {
            width: 100%;
            padding: 14px 24px;
            border: none;
            border-radius: 12px;
            font-size: 0.95rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: inherit;
        }

        .btn-primary {
            background: var(--primary-color);
            color: white;
        }

        .btn-primary:hover {
            background: var(--primary-hover);
            transform: translateY(-1px);
            box-shadow: var(--shadow-secondary);
        }

        .btn i {
            margin-right: 8px;
        }

        .register-link {
            text-align: center;
            margin-top: 32px;
            padding-top: 24px;
            border-top: 1px solid var(--border-color);
        }

        .register-link p {
            color: var(--text-secondary);
            font-size: 0.9rem;
            margin-bottom: 12px;
        }

        .register-link a {
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 600;
            font-size: 0.9rem;
            padding: 8px 16px;
            border-radius: 8px;
            transition: all 0.2s ease;
            display: inline-block;
        }

        .register-link a:hover {
            background: rgba(37, 99, 235, 0.1);
        }

        /* Modal Styles */
        .modal-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            background: rgba(0, 0, 0, 0.6);
            backdrop-filter: blur(4px);
            z-index: 1000;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .modal {
            background: white;
            border-radius: 20px;
            width: 100%;
            max-width: 480px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
            position: relative;
            max-height: 90vh;
            overflow-y: auto;
        }

        .modal-header {
            background: linear-gradient(135deg, var(--primary-color), var(--primary-hover));
            color: white;
            padding: 32px 32px 24px;
            border-radius: 20px 20px 0 0;
            text-align: center;
            position: relative;
        }

        .modal-close {
            position: absolute;
            top: 16px;
            right: 16px;
            background: rgba(255, 255, 255, 0.2);
            border: none;
            color: white;
            width: 36px;
            height: 36px;
            border-radius: 50%;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            transition: all 0.2s ease;
        }

        .modal-close:hover {
            background: rgba(255, 255, 255, 0.3);
            transform: scale(1.1);
        }

        .modal-avatar {
            width: 80px;
            height: 80px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            margin: 0 auto 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.5rem;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
        }

        .modal-header h3 {
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 8px;
        }

        .modal-header p {
            opacity: 0.9;
            font-size: 0.95rem;
        }

        .modal-body {
            padding: 32px;
        }

        .modal .form-group {
            margin-bottom: 20px;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .login-wrapper {
                grid-template-columns: 1fr;
                margin: 20px;
                max-width: 500px;
            }

            .login-info {
                padding: 40px 30px;
                text-align: center;
            }

            .login-form-container {
                padding: 40px 30px;
            }

            .welcome-text h2 {
                font-size: 1.75rem;
            }

            .modal-body {
                padding: 24px;
            }
        }

        @media (max-width: 480px) {
            body {
                padding: 10px;
            }

            .login-wrapper {
                margin: 10px;
            }

            .login-info, .login-form-container {
                padding: 30px 20px;
            }

            .modal-header {
                padding: 24px 20px 20px;
            }

            .modal-body {
                padding: 20px;
            }
        }

        /* Animation */
        .login-wrapper {
            animation: slideUp 0.6s ease;
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .modal {
            animation: modalSlideIn 0.3s ease;
        }

        @keyframes modalSlideIn {
            from {
                opacity: 0;
                transform: scale(0.9) translateY(-10px);
            }
            to {
                opacity: 1;
                transform: scale(1) translateY(0);
            }
        }
    </style>
</head>
<body>
    <div class="login-wrapper">
        <!-- Left Side - Info Panel -->
        <div class="login-info">
            <div class="login-info-content">
                <div class="logo">
                    <i class="fas fa-graduation-cap"></i>
                    <h1>Academic<br>Portal</h1>
                </div>
                
                <div class="welcome-text">
                    <h2>Welcome Back!</h2>
                    <p>Access your academic dashboard and manage your institutional activities with ease.</p>
                </div>

                <ul class="features">
                    <li><i class="fas fa-shield-alt"></i> Secure Authentication</li>
                    <li><i class="fas fa-users"></i> Multi-Role Access</li>
                    <li><i class="fas fa-chart-line"></i> Real-time Analytics</li>
                    <li><i class="fas fa-mobile-alt"></i> Mobile Responsive</li>
                </ul>
            </div>
        </div>

        <!-- Right Side - Login Form -->
        <div class="login-form-container">
            <div class="form-header">
                <h3>Sign In</h3>
                <p>Please enter your credentials to continue</p>
            </div>

            <!-- Error/Success Messages -->
            <div id="errorMessage" class="alert alert-error" style="display: none;">
                <i class="fas fa-exclamation-circle"></i>
                <span id="errorText">Invalid credentials</span>
            </div>

            <div id="successMessage" class="alert alert-success" style="display: none;">
                <i class="fas fa-check-circle"></i>
                <span>Professor registered successfully!</span>
            </div>

            <form id="loginForm" method="POST">
                <div class="form-group">
                    <label for="role">Account Type</label>
                    <div class="input-wrapper">
                        <i class="fas fa-user-tag"></i>
                        <select name="role" id="role" class="form-control" required>
                            <option value="">Select your role</option>
                            <option value="admin">Administrator</option>
                            <option value="professor">Professor</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label for="username">Username</label>
                    <div class="input-wrapper">
                        <i class="fas fa-user"></i>
                        <input type="text" name="username" id="username" class="form-control" 
                               placeholder="Enter your username" required>
                    </div>
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <div class="input-wrapper">
                        <i class="fas fa-lock"></i>
                        <input type="password" name="password" id="password" class="form-control" 
                               placeholder="Enter your password" required>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-sign-in-alt"></i>
                    Sign In
                </button>
            </form>

            <div class="register-link">
                <p>Don't have an account?</p>
                <a href="#" onclick="openRegistrationModal()">
                    <i class="fas fa-user-plus"></i>
                    Register as Professor
                </a>
            </div>
        </div>
    </div>

    <!-- Registration Modal -->
    <div class="modal-overlay" id="registrationModal">
        <div class="modal">
            <div class="modal-header">
                <button class="modal-close" onclick="closeRegistrationModal()">
                    <i class="fas fa-times"></i>
                </button>
                <div class="modal-avatar">👨‍🏫</div>
                <h3>Professor Registration</h3>
                <p>Create your academic account</p>
            </div>
            
            <div class="modal-body">
                <form id="registrationForm" method="POST">
                    <input type="hidden" name="action" value="register">
                    
                    <div class="form-group">
                        <label for="fullName">Full Name</label>
                        <div class="input-wrapper">
                            <i class="fas fa-id-card"></i>
                            <input type="text" name="name" id="fullName" class="form-control" 
                                   placeholder="Enter your full name" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="regUsername">Username</label>
                        <div class="input-wrapper">
                            <i class="fas fa-user"></i>
                            <input type="text" name="reg_username" id="regUsername" class="form-control" 
                                   placeholder="Choose a username" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="regPassword">Password</label>
                        <div class="input-wrapper">
                            <i class="fas fa-lock"></i>
                            <input type="password" name="reg_password" id="regPassword" class="form-control" 
                                   placeholder="Create a password" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="buildingId">Building</label>
                        <div class="input-wrapper">
                            <i class="fas fa-building"></i>
                            <select name="building_id" id="buildingId" class="form-control" required>
                                <option value="">Select your building</option>
                                <option value="1">Science Building</option>
                                <option value="2">Engineering Building</option>
                                <option value="3">Arts & Humanities</option>
                                <option value="4">Business School</option>
                                <option value="5">Medical Center</option>
                            </select>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-user-plus"></i>
                        Create Account
                    </button>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Modal Functions
        function openRegistrationModal() {
            document.getElementById('registrationModal').style.display = 'flex';
            document.body.style.overflow = 'hidden';
        }

        function closeRegistrationModal() {
            document.getElementById('registrationModal').style.display = 'none';
            document.body.style.overflow = 'auto';
        }

        // Close modal when clicking outside
        document.getElementById('registrationModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeRegistrationModal();
            }
        });

        // Form Validation and Demo Login
        document.getElementById('loginForm').addEventListener('submit', function(e) {
            const role = document.getElementById('role').value;
            const username = document.getElementById('username').value;
            const password = document.getElementById('password').value;

            if (!role || !username || !password) {
                e.preventDefault();
                showError('Please fill in all required fields');
                return;
            }

            // Demo login for testing (remove this in production)
            if (role === 'admin' && username === 'admin' && password === 'admin123') {
                alert('Admin login successful! (This is a demo)');
                e.preventDefault();
                return;
            }
            
            if (role === 'professor' && username === 'prof1' && password === 'prof123') {
                alert('Professor login successful! (This is a demo)');
                e.preventDefault();
                return;
            }
        });

        document.getElementById('registrationForm').addEventListener('submit', function(e) {
            const name = document.getElementById('fullName').value;
            const username = document.getElementById('regUsername').value;
            const password = document.getElementById('regPassword').value;
            const building = document.getElementById('buildingId').value;

            if (!name || !username || !password || !building) {
                e.preventDefault();
                showError('Please fill in all required fields');
                return;
            }

            if (password.length < 6) {
                e.preventDefault();
                showError('Password must be at least 6 characters long');
                return;
            }
        });

        function showError(message) {
            const errorDiv = document.getElementById('errorMessage');
            const errorText = document.getElementById('errorText');
            errorText.textContent = message;
            errorDiv.style.display = 'flex';
            
            setTimeout(() => {
                errorDiv.style.display = 'none';
            }, 5000);
        }

        function showSuccess(message) {
            const successDiv = document.getElementById('successMessage');
            successDiv.style.display = 'flex';
            
            setTimeout(() => {
                successDiv.style.display = 'none';
            }, 5000);
        }

        // Keyboard navigation
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeRegistrationModal();
            }
        });

        // Auto-hide alerts after page load
        window.addEventListener('load', function() {
            // Simulate PHP error/success messages
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.get('error')) {
                showError(urlParams.get('error'));
            }
            if (urlParams.get('success')) {
                showSuccess('Registration completed successfully!');
            }
        });
    </script>
</body>
</html>