<?php
include 'config.php';

// Redirect if already logged in
if (isLoggedIn()) {
    redirect('dashboard.php');
}

// Check if token is valid
$token = $_GET['token'] ?? '';
$validToken = false;

if ($token) {
    $stmt = $conn->prepare("SELECT id FROM users WHERE reset_token = ? AND reset_expires > NOW()");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();

    $validToken = ($result->num_rows == 1);
    $user = $result->fetch_assoc();
}

// Handle password reset
if ($_SERVER['REQUEST_METHOD'] == 'POST' && $validToken) {
    $newPassword = password_hash($_POST['password'], PASSWORD_DEFAULT);

    // Update password and clear token
    $stmt = $conn->prepare("UPDATE users SET password = ?, reset_token = NULL, reset_expires = NULL WHERE id = ?");
    $stmt->bind_param("si", $newPassword, $user['id']);

    if ($stmt->execute()) {
        $_SESSION['message'] = "Password updated successfully! Please login.";
        $_SESSION['message_type'] = "success";
        redirect('login.php');
    } else {
        $_SESSION['message'] = "Error updating password";
        $_SESSION['message_type'] = "danger";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password | FitPro</title>
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
        .btn-reset {
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
        .btn-reset:hover {
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
            <h2 class="auth-title">Set New Password</h2>

            <?php if (!$validToken): ?>
                <div class="alert alert-danger">
                    Invalid or expired password reset link.
                </div>
                <div class="auth-footer">
                    <p><a href="forgot_password.php">Request a new reset link</a></p>
                </div>
            <?php else: ?>
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
                        <label for="password">New Password</label>
                        <input type="password" id="password" name="password" required minlength="6">
                    </div>
                    <div class="form-group">
                        <label for="confirm_password">Confirm New Password</label>
                        <input type="password" id="confirm_password" name="confirm_password" required>
                    </div>
                    <button type="submit" class="btn-reset">Update Password</button>
                </form>
            <?php endif; ?>
        </div>
    </div>
    <script>
        // Password confirmation validation
        document.querySelector('form')?.addEventListener('submit', function(e) {
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm_password').value;

            if (password !== confirmPassword) {
                e.preventDefault();
                alert('Passwords do not match!');
            }
        });
    </script>
</body>
</html>