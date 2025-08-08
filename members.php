<?php 
include 'config.php';

// Check if user is logged in and is admin
if (!isLoggedIn() || !isAdmin()) {
    redirect('index.php');
}

// Initialize variables
$members = null;
$error = null;

// Handle member deletion
if (isset($_GET['delete'])) {
    $memberId = (int)$_GET['delete'];
    try {
        $stmt = $conn->prepare("DELETE FROM users WHERE id = ? AND type = 'member'");
        $stmt->bind_param("i", $memberId);
        
        if ($stmt->execute()) {
            $_SESSION['message'] = "Member deleted successfully";
            $_SESSION['message_type'] = "success";
        } else {
            throw new Exception("Delete failed: " . $stmt->error);
        }
    } catch (Exception $e) {
        $_SESSION['message'] = "Error: " . $e->getMessage();
        $_SESSION['message_type'] = "danger";
    } finally {
        if (isset($stmt)) $stmt->close();
        redirect('members.php');
    }
}

// Get all members with robust error handling
try {
    $sql = "SELECT * FROM users WHERE type = 'member' ORDER BY created_at DESC";
    $result = $conn->query($sql);
    
    if ($result === false) {
        throw new Exception("Query failed: " . $conn->error);
    }
    
    $members = $result;
} catch (Exception $e) {
    $error = $e->getMessage();
    error_log("Members query error: " . $error);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <!-- [Previous head content remains the same] -->
    <style>
        .error-message {
            color: #dc3545;
            background-color: #f8d7da;
            border: 1px solid #f5c6cb;
            padding: 10px;
            border-radius: 4px;
            margin: 10px 0;
            text-align: center;
        }
    </style>
</head>
<body>
   
    
    <div class="main-content">
        <!-- [Previous header content remains the same] -->
        
        <?php if (isset($_SESSION['message'])): ?>
            <div class="alert alert-<?php echo $_SESSION['message_type']; ?>">
                <?php 
                echo htmlspecialchars($_SESSION['message']); 
                unset($_SESSION['message']);
                unset($_SESSION['message_type']);
                ?>
            </div>
        <?php endif; ?>
        
        <div class="members-container">
            <!-- [Previous members header content remains the same] -->
            
            <?php if ($error): ?>
                <div class="error-message">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>
            
            <table class="members-table">
                <thead>
                    <tr>
                        <th>Member</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Join Date</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($members && $members instanceof mysqli_result): ?>
                        <?php if ($members->num_rows > 0): ?>
                            <?php while($member = $members->fetch_assoc()): ?>
                                <tr>
                                    <!-- [Previous member row content remains the same] -->
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" style="text-align: center;">No members found</td>
                            </tr>
                        <?php endif; ?>
                    <?php endif; ?>
                </tbody>
            </table>
            
            <!-- [Previous pagination content remains the same] -->
        </div>
    </div>
    
    <!-- [Previous modal and script content remains the same] -->
</body>
</html>