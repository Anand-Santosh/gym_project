<?php 
include 'config.php';

// Check if user is logged in
if (!isLoggedIn()) {
    redirect('login.php');
}

// Initialize variables
$bookings = null;
$error = null;

// Handle booking cancellation
if (isset($_GET['cancel'])) {
    $bookingId = (int)$_GET['cancel'];
    
    try {
        // Check if user owns this booking (unless admin)
        if (!isAdmin()) {
            $check = $conn->prepare("SELECT id FROM bookings WHERE id = ? AND user_id = ?");
            $check->bind_param("ii", $bookingId, $_SESSION['user_id']);
            $check->execute();
            $result = $check->get_result();
            
            if ($result->num_rows === 0) {
                throw new Exception("You can only cancel your own bookings");
            }
        }
        
        $stmt = $conn->prepare("UPDATE bookings SET status = 'cancelled' WHERE id = ?");
        $stmt->bind_param("i", $bookingId);
        
        if ($stmt->execute()) {
            $_SESSION['message'] = "Booking cancelled successfully";
            $_SESSION['message_type'] = "success";
        } else {
            throw new Exception("Error cancelling booking: " . $stmt->error);
        }
    } catch (Exception $e) {
        $_SESSION['message'] = $e->getMessage();
        $_SESSION['message_type'] = "danger";
    } finally {
        if (isset($stmt)) $stmt->close();
        if (isset($check)) $check->close();
        redirect('bookings.php');
    }
}

// Get bookings based on user type with proper error handling
try {
    if (isAdmin()) {
        $stmt = $conn->prepare("SELECT b.*, u.name as member_name, p.name as package_name 
                               FROM bookings b 
                               JOIN users u ON b.user_id = u.id 
                               JOIN packages p ON b.package_id = p.id 
                               ORDER BY b.session_date DESC");
    } elseif (isTrainer()) {
        $stmt = $conn->prepare("SELECT b.*, u.name as member_name, p.name as package_name 
                               FROM bookings b 
                               JOIN users u ON b.user_id = u.id 
                               JOIN packages p ON b.package_id = p.id 
                               WHERE b.trainer_id = ? 
                               ORDER BY b.session_date DESC");
        $stmt->bind_param("i", $_SESSION['user_id']);
    } else {
        $stmt = $conn->prepare("SELECT b.*, p.name as package_name 
                               FROM bookings b 
                               JOIN packages p ON b.package_id = p.id 
                               WHERE b.user_id = ? 
                               ORDER BY b.session_date DESC");
        $stmt->bind_param("i", $_SESSION['user_id']);
    }

    if ($stmt->execute()) {
        $bookings = $stmt->get_result();
    } else {
        throw new Exception("Failed to load bookings: " . $stmt->error);
    }
} catch (Exception $e) {
    $error = $e->getMessage();
    error_log("Booking error: " . $error);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Management | FitPro</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    <style>
        /* [All your existing CSS remains exactly the same] */
        .error-message {
            color: #dc3545;
            padding: 15px;
            background-color: #f8d7da;
            border-radius: 5px;
            margin-bottom: 20px;
            text-align: center;
        }
    </style>
</head>
<body>
    
    
    <div class="main-content">
        <div class="dashboard-header">
            <div class="dashboard-title">
                <h1>Booking Management</h1>
                <p>View and manage your training sessions</p>
            </div>
            <div class="user-menu">
                <img src="assets/images/<?php echo isAdmin() ? 'admin' : (isTrainer() ? 'trainers' : 'members'); ?>/<?php echo htmlspecialchars($_SESSION['user_avatar'] ?? 'default.jpg'); ?>" alt="User Avatar">
                <span><?php echo htmlspecialchars($_SESSION['user_name']); ?></span>
            </div>
        </div>
        
        <?php if (isset($_SESSION['message'])): ?>
        <div class="alert alert-<?php echo htmlspecialchars($_SESSION['message_type']); ?>">
            <?php 
            echo htmlspecialchars($_SESSION['message']); 
            unset($_SESSION['message']);
            unset($_SESSION['message_type']);
            ?>
        </div>
        <?php endif; ?>
        
        <?php if ($error): ?>
        <div class="error-message">
            <?php echo htmlspecialchars($error); ?>
        </div>
        <?php endif; ?>
        
        <div class="bookings-container">
            <div class="bookings-header">
                <h2>Your Bookings</h2>
                <div class="booking-actions">
                    <div class="search-box">
                        <i class="fas fa-search"></i>
                        <input type="text" placeholder="Search bookings...">
                    </div>
                    <a href="new_booking.php" class="btn-new-booking">
                        <i class="fas fa-plus"></i> New Booking
                    </a>
                </div>
            </div>
            
            <table class="bookings-table">
                <thead>
                    <tr>
                        <?php if (isAdmin() || isTrainer()): ?>
                        <th>Member</th>
                        <?php endif; ?>
                        <th>Package</th>
                        <th>Booking Date</th>
                        <th>Session Date</th>
                        <th>Time</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($bookings && $bookings->num_rows > 0): ?>
                        <?php while($booking = $bookings->fetch_assoc()): ?>
                        <tr>
                            <?php if (isAdmin() || isTrainer()): ?>
                            <td><?php echo htmlspecialchars($booking['member_name']); ?></td>
                            <?php endif; ?>
                            <td><?php echo htmlspecialchars($booking['package_name']); ?></td>
                            <td><?php echo date('M d, Y', strtotime($booking['booking_date'])); ?></td>
                            <td><?php echo date('M d, Y', strtotime($booking['session_date'])); ?></td>
                            <td><?php echo date('h:i A', strtotime($booking['session_time'])); ?></td>
                            <td>
                                <?php 
                                $statusClass = '';
                                if ($booking['status'] == 'pending') $statusClass = 'status-pending';
                                elseif ($booking['status'] == 'confirmed') $statusClass = 'status-confirmed';
                                elseif ($booking['status'] == 'completed') $statusClass = 'status-completed';
                                else $statusClass = 'status-cancelled';
                                ?>
                                <span class="booking-status <?php echo $statusClass; ?>"><?php echo ucfirst($booking['status']); ?></span>
                            </td>
                            <td>
                                <div class="action-buttons">
                                    <a href="booking_details.php?id=<?php echo (int)$booking['id']; ?>" class="btn-action btn-view"><i class="fas fa-eye"></i></a>
                                    <?php if ($booking['status'] != 'cancelled' && $booking['status'] != 'completed'): ?>
                                    <a href="bookings.php?cancel=<?php echo (int)$booking['id']; ?>" class="btn-action btn-cancel" onclick="return confirm('Are you sure you want to cancel this booking?')"><i class="fas fa-times"></i></a>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="<?php echo (isAdmin() || isTrainer()) ? 7 : 6; ?>" style="text-align: center;">
                                No bookings found
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
            
            <!-- [Rest of your HTML and JavaScript remains exactly the same] -->
        </div>
    </div>
    
    <script>
        // [Your existing JavaScript remains exactly the same]
    </script>
</body>
</html>