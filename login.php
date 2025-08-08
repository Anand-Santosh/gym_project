<?php
include 'config.php';

// Redirect if already logged in
if (isLoggedIn()) {
// Redirect based on user type
            if ($user['type'] == 'admin') {
                redirect('admin_dashboard.php');
            } elseif ($user['type'] == 'trainer') {
                redirect('trainer_dashboard.php');
            } else {
                redirect('member_dashboard.php');
            };
}

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Check if user exists
    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $user = $result->fetch_assoc();

        // Verify password
        if (password_verify($password, $user['password'])) {
            // Set session variables
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_type'] = $user['type'];
            $_SESSION['user_avatar'] = $user['avatar'];

            // Redirect based on user type
            if ($user['type'] == 'admin') {
                redirect('admin_dashboard.php');
            } elseif ($user['type'] == 'trainer') {
                redirect('trainer_dashboard.php');
            } else {
                redirect('member_dashboard.php');
            }
        } else {
            $_SESSION['message'] = "Invalid email or password";
            $_SESSION['message_type'] = "danger";
        }
    } else {
        $_SESSION['message'] = "User not found";
        $_SESSION['message_type'] = "danger";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | FitPro</title>
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
        .form-group input {
            width: 100%;
            padding: 0.8rem;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 1rem;
        }
        .btn-login {
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
        .btn-login:hover {
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
            <h2 class="auth-title">Login to Your Account</h2>

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
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" required>
                </div>
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required>
                </div>
                <button type="submit" class="btn-login">Login</button>
            </form>
            <div class="auth-footer">
                <p>Don't have an account? <a href="register.php">Register here</a></p>
                <p><a href="forgot_password.php">Forgot password?</a></p>
            </div>
        </div>
    </div>
</body>
</html>