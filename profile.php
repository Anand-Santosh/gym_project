<?php
include 'config.php';

// Check if user is logged in
if (!isLoggedIn()) {
    redirect('login.php');
}

$userId = $_SESSION['user_id'];
$userType = $_SESSION['user_type'];

// Get user data
$user = $conn->query("SELECT * FROM users WHERE id = $userId")->fetch_assoc();

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] == 'POST' && !isset($_POST['change_password'])) {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $address = $_POST['address'];
    $bio = $_POST['bio'];
    
    // Handle avatar upload
    $avatar = $user['avatar'];
    if ($_FILES['avatar']['size'] > 0) {
        $targetDir = "assets/images/$userType/";
        $fileName = basename($_FILES['avatar']['name']);
        $targetFile = $targetDir . $fileName;
        $uploadOk = 1;
        $imageFileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));
        
        // Check if image file is a actual image
        $check = getimagesize($_FILES['avatar']['tmp_name']);
        if ($check === false) {
            $_SESSION['message'] = "File is not an image.";
            $_SESSION['message_type'] = "danger";
            $uploadOk = 0;
        }
        
        // Check file size
        if ($_FILES['avatar']['size'] > 500000) {
            $_SESSION['message'] = "Sorry, your file is too large.";
            $_SESSION['message_type'] = "danger";
            $uploadOk = 0;
        }
        
        // Allow certain file formats
        if ($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg" && $imageFileType != "gif") {
            $_SESSION['message'] = "Sorry, only JPG, JPEG, PNG & GIF files are allowed.";
            $_SESSION['message_type'] = "danger";
            $uploadOk = 0;
        }
        
        // Upload file if everything is ok
        if ($uploadOk == 1) {
            if (move_uploaded_file($_FILES['avatar']['tmp_name'], $targetFile)) {
                $avatar = $fileName;
            } else {
                $_SESSION['message'] = "Sorry, there was an error uploading your file.";
                $_SESSION['message_type'] = "danger";
            }
        }
    }
    
    // Update user data
    $stmt = $conn->prepare("UPDATE users SET name = ?, email = ?, phone = ?, address = ?, bio = ?, avatar = ? WHERE id = ?");
    $stmt->bind_param("ssssssi", $name, $email, $phone, $address, $bio, $avatar, $userId);
    
    if ($stmt->execute()) {
        $_SESSION['user_name'] = $name;
        $_SESSION['user_email'] = $email;
        $_SESSION['user_avatar'] = $avatar;
        
        $_SESSION['message'] = "Profile updated successfully!";
        $_SESSION['message_type'] = "success";
    } else {
        $_SESSION['message'] = "Error updating profile.";
        $_SESSION['message_type'] = "danger";
    }
}

// Handle password change
if (isset($_POST['change_password'])) {
    $currentPassword = $_POST['current_password'];
    $newPassword = $_POST['new_password'];
    $confirmPassword = $_POST['confirm_password'];
    
    // Verify current password
    if (password_verify($currentPassword, $user['password'])) {
        if ($newPassword == $confirmPassword) {
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            $conn->query("UPDATE users SET password = '$hashedPassword' WHERE id = $userId");
            
            $_SESSION['message'] = "Password changed successfully!";
            $_SESSION['message_type'] = "success";
        } else {
            $_SESSION['message'] = "New passwords do not match!";
            $_SESSION['message_type'] = "danger";
        }
    } else {
        $_SESSION['message'] = "Current password is incorrect!";
        $_SESSION['message_type'] = "danger";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile | FitPro</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    <style>
        /* Header Styles (formerly in header.php) */
        header {
            background-color: #2c3e50;
            color: white;
            padding: 1rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .logo {
            font-size: 1.5rem;
            font-weight: bold;
        }
        nav ul {
            display: flex;
            list-style: none;
        }
        nav ul li {
            margin-left: 1.5rem;
        }
        nav ul li a {
            color: white;
            text-decoration: none;
            transition: color 0.3s;
        }
        nav ul li a:hover {
            color: #3498db;
        }
        
        /* Your existing profile styles */
        .profile-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem;
        }
        /* ... (keep all your existing profile styles) ... */
    </style>
</head>
<body>
    <!-- Header Content (formerly in header.php) -->
    <header>
        <div class="logo">FitPro</div>
        <nav>
            <ul>
                <li><a href="index.php">Home</a></li>
                <li><a href="profile.php" class="active">Profile</a></li>
                <li><a href="logout.php">Logout</a></li>
            </ul>
        </nav>
    </header>
    
    <div class="profile-container">
        <!-- Your existing profile content -->
        <div class="profile-header">
            <img src="assets/images/<?php echo $userType; ?>s/<?php echo $user['avatar'] ? $user['avatar'] : 'default.jpg'; ?>" alt="Profile Avatar" class="profile-avatar">
            <div class="profile-info">
                <h1><?php echo $user['name']; ?></h1>
                <p><?php echo ucfirst($userType); ?></p>
                <p><i class="fas fa-envelope"></i> <?php echo $user['email']; ?></p>
                <?php if ($user['phone']): ?>
                    <p><i class="fas fa-phone"></i> <?php echo $user['phone']; ?></p>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Rest of your profile page content ... -->
        
    </div>
    
    <!-- Footer Content (formerly in footer.php) -->
    <footer style="background-color: #f8f9fa; padding: 2rem; text-align: center;">
        <p>&copy; <?php echo date('Y'); ?> FitPro Gym. All rights reserved.</p>
    </footer>
    
    <script>
        // Preview avatar before upload
        document.getElementById('avatarInput').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('avatarPreview').src = e.target.result;
                }
                reader.readAsDataURL(file);
            }
        });
    </script>
</body>
</html>