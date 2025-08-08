<?php
include 'config.php';

// Redirect if already logged in
if (isLoggedIn()) {
    redirect('dashboard.php');
}

// Handle registration form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $type = $_POST['role']; // Get selected role from form

    // Check if email already exists
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $_SESSION['message'] = "Email already registered";
        $_SESSION['message_type'] = "danger";
    } else {
        // Insert new user
        $stmt = $conn->prepare("INSERT INTO users (name, email, password, type) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $name, $email, $password, $type);

        if ($stmt->execute()) {
            $_SESSION['message'] = "Registration successful! Please login.";
            $_SESSION['message_type'] = "success";
            redirect('login.php');
        } else {
            $_SESSION['message'] = "Error in registration";
            $_SESSION['message_type'] = "danger";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register | FitPro</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    <style>
        .auth-container {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            background-color: #f5f7fa;
        }
        .auth-card {
            background-color: white;
            border-radius: 10px;
            padding: 2rem;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 400px;
        }
        .auth-logo {
            text-align: center;
            margin-bottom: 1.5rem;
        }
        .auth-logo img {
            width: 80px;
            height: 80px;
        }
        .auth-title {
            font-size: 1.5rem;
            text-align: center;
            margin-bottom: 1.5rem;
            color: var(--dark);
        }
        .form-group {
            margin-bottom: 1.5rem;
        }
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
        }
        .form-group input, 
        .form-group select {
            width: 100%;
            padding: 0.8rem;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 1rem;
        }
        .role-options {
            display: flex;
            gap: 1rem;
            margin-bottom: 1.5rem;
        }
        .role-option {
            flex: 1;
            text-align: center;
            padding: 1rem;
            border: 2px solid #ddd;
            border-radius: 5px;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        .role-option:hover {
            border-color: var(--primary);
        }
        .role-option.selected {
            border-color: var(--primary);
            background-color: rgba(67, 97, 238, 0.1);
        }
        .role-option input[type="radio"] {
            display: none;
        }
        .btn-register {
            width: 100%;
            padding: 0.8rem;
            background-color: var(--primary);
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 1rem;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        .btn-register:hover {
            background-color: var(--secondary);
        }
        .auth-footer {
            text-align: center;
            margin-top: 1.5rem;
        }
        .auth-footer a {
            color: var(--primary);
            text-decoration: none;
        }
    </style>
</head>
<body>
    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-logo">
                <img src="assets/images/logo.png" alt="FitPro Logo">
            </div>
            <h2 class="auth-title">Create an Account</h2>

            <?php if (isset($_SESSION['message'])): ?>
                <div class="alert alert-<?php echo $_SESSION['message_type']; ?>">
                    <?php 
                    echo $_SESSION['message']; 
                    unset($_SESSION['message']);
                    unset($_SESSION['message_type']);
                    ?>
                </div>
            <?php endif; ?>

            <form method="POST">
                <div class="form-group">
                    <label for="name">Full Name</label>
                    <input type="text" id="name" name="name" required>
                </div>
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" required>
                </div>
                
                <!-- Role Selection -->
                <div class="form-group">
                    <label>Register As</label>
                    <div class="role-options">
                        <label class="role-option" onclick="selectRole(this)">
                            <input type="radio" name="role" value="member" checked>
                            <i class="fas fa-user" style="font-size: 1.5rem; margin-bottom: 0.5rem;"></i>
                            <div>Member</div>
                        </label>
                        <label class="role-option" onclick="selectRole(this)">
                            <input type="radio" name="role" value="trainer">
                            <i class="fas fa-dumbbell" style="font-size: 1.5rem; margin-bottom: 0.5rem;"></i>
                            <div>Trainer</div>
                        </label>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required minlength="6">
                </div>
                <div class="form-group">
                    <label for="confirm_password">Confirm Password</label>
                    <input type="password" id="confirm_password" name="confirm_password" required>
                </div>
                <button type="submit" class="btn-register">Register</button>
            </form>
            <div class="auth-footer">
                <p>Already have an account? <a href="login.php">Login here</a></p>
            </div>
        </div>
    </div>
    <script>
        // Password confirmation validation
        document.querySelector('form').addEventListener('submit', function(e) {
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm_password').value;

            if (password !== confirmPassword) {
                e.preventDefault();
                alert('Passwords do not match!');
            }
        });

        // Role selection
        function selectRole(element) {
            // Remove selected class from all options
            document.querySelectorAll('.role-option').forEach(opt => {
                opt.classList.remove('selected');
            });
            
            // Add selected class to clicked option
            element.classList.add('selected');
            
            // Check the corresponding radio button
            const radio = element.querySelector('input[type="radio"]');
            radio.checked = true;
        }

        // Initialize with member selected
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelector('.role-option').classList.add('selected');
        });
    </script>
</body>
</html>