<?php
include 'config.php';

// Check if user is logged in and is admin
if (!isLoggedIn() || !isAdmin()) {
    header("Location: login.php");
    exit();
}

// Get admin data
$adminId = $_SESSION['user_id'];
$admin = $conn->query("SELECT * FROM users WHERE id = $adminId")->fetch_assoc();

// Get stats
$totalMembers = $conn->query("SELECT COUNT(*) as count FROM users WHERE type = 'member'")->fetch_assoc()['count'];
$totalTrainers = $conn->query("SELECT COUNT(*) as count FROM users WHERE type = 'trainer'")->fetch_assoc()['count'];
$totalBookings = $conn->query("SELECT COUNT(*) as count FROM bookings")->fetch_assoc()['count'];
$revenue = $conn->query("SELECT SUM(amount) as total FROM payments WHERE status = 'completed'")->fetch_assoc()['total'];

// Get recent members
$recentMembers = $conn->query("
    SELECT * FROM users 
    WHERE type = 'member' 
    ORDER BY created_at DESC 
    LIMIT 5
");

// Get recent bookings - FIXED: Changed b.created_at to b.session_date
$recentBookings = $conn->query("
    SELECT b.*, u.name as member_name, p.name as package_name 
    FROM bookings b
    JOIN users u ON b.user_id = u.id
    JOIN packages p ON b.package_id = p.id
    ORDER BY b.session_date DESC
    LIMIT 5
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | FitPro</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    <style>
        .dashboard {
            display: grid;
            grid-template-columns: 250px 1fr;
            min-height: 100vh;
        }
        .sidebar {
            background: linear-gradient(180deg, var(--dark), var(--secondary));
            color: white;
            padding: 2rem 1rem;
        }
        .sidebar-header {
            text-align: center;
            margin-bottom: 2rem;
        }
        .sidebar-header img {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid var(--accent);
        }
        .sidebar-header h3 {
            margin-top: 1rem;
            font-size: 1.2rem;
        }
        .sidebar-header p {
            font-size: 0.9rem;
            opacity: 0.8;
        }
        .sidebar-menu {
            list-style: none;
        }
        .sidebar-menu li {
            margin-bottom: 0.5rem;
        }
        .sidebar-menu a {
            display: block;
            color: white;
            padding: 0.8rem 1rem;
            border-radius: 5px;
            text-decoration: none;
            transition: all 0.3s ease;
        }
        .sidebar-menu a:hover, .sidebar-menu a.active {
            background-color: rgba(255, 255, 255, 0.1);
        }
        .sidebar-menu a i {
            margin-right: 10px;
            width: 20px;
            text-align: center;
        }
        .main-content {
            padding: 2rem;
            background-color: #f5f7fa;
        }
        .dashboard-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
        }
        .dashboard-title h1 {
            font-size: 1.8rem;
            color: var(--dark);
        }
        .dashboard-title p {
            color: #6c757d;
        }
        .user-menu {
            display: flex;
            align-items: center;
        }
        .user-menu img {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            margin-right: 10px;
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        .stat-card {
            background-color: white;
            border-radius: 10px;
            padding: 1.5rem;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
        }
        .stat-icon {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1rem;
            font-size: 1.5rem;
        }
        .stat-1 {
            background-color: rgba(67, 97, 238, 0.1);
            color: var(--primary);
        }
        .stat-2 {
            background-color: rgba(248, 150, 30, 0.1);
            color: var(--warning);
        }
        .stat-3 {
            background-color: rgba(76, 201, 240, 0.1);
            color: var(--success);
        }
        .stat-4 {
            background-color: rgba(108, 92, 231, 0.1);
            color: var(--purple);
        }
        .stat-card h3 {
            font-size: 1.5rem;
            margin-bottom: 0.5rem;
        }
        .stat-card p {
            color: #6c757d;
            font-size: 0.9rem;
        }
        .section-card {
            background-color: white;
            border-radius: 10px;
            padding: 1.5rem;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            margin-bottom: 2rem;
        }
        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
        }
        .section-header h2 {
            font-size: 1.3rem;
        }
        .section-header a {
            color: var(--primary);
            text-decoration: none;
            font-weight: 500;
        }
        .table {
            width: 100%;
            border-collapse: collapse;
        }
        .table th, .table td {
            padding: 0.75rem;
            text-align: left;
            border-bottom: 1px solid #dee2e6;
        }
        .table th {
            font-weight: 600;
            background-color: #f8f9fa;
        }
        .badge {
            padding: 0.35em 0.65em;
            font-size: 0.75em;
            font-weight: 700;
            line-height: 1;
            text-align: center;
            white-space: nowrap;
            vertical-align: baseline;
            border-radius: 0.25rem;
        }
        .badge-success {
            color: #fff;
            background-color: var(--success);
        }
        .badge-warning {
            color: #fff;
            background-color: var(--warning);
        }
        .badge-danger {
            color: #fff;
            background-color: var(--danger);
        }
        @media (max-width: 1200px) {
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }
        @media (max-width: 768px) {
            .dashboard {
                grid-template-columns: 1fr;
            }
            .sidebar {
                display: none;
            }
            .stats-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="dashboard">
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="sidebar-header">
                <img src="assets/images/admins/<?php echo $admin['avatar'] ? $admin['avatar'] : 'default.jpg'; ?>" alt="Admin Avatar">
                <h3><?php echo $admin['name']; ?></h3>
                <p>Administrator</p>
            </div>
            <ul class="sidebar-menu">
                <li><a href="admin_dashboard.php" class="active"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                <li><a href="members.php"><i class="fas fa-users"></i> Members</a></li>
                <li><a href="trainers.php"><i class="fas fa-dumbbell"></i> Trainers</a></li>
                <li><a href="packages.php"><i class="fas fa-box-open"></i> Packages</a></li>
                <li><a href="bookings.php"><i class="fas fa-calendar-check"></i> Bookings</a></li>
                
                <li><a href="reports.php"><i class="fas fa-chart-bar"></i> Reports</a></li>
                
                <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            </ul>
        </div>
        
        <!-- Main Content -->
        <div class="main-content">
            <div class="dashboard-header">
                <div class="dashboard-title">
                    <h1>Admin Dashboard</h1>
                    <p>Welcome back, <?php echo $admin['name']; ?></p>
                </div>
                <div class="user-menu">
                    <img src="assets/images/admins/<?php echo $admin['avatar'] ? $admin['avatar'] : 'default.jpg'; ?>" alt="User Avatar">
                    <span><?php echo $admin['name']; ?></span>
                </div>
            </div>
            
            <!-- Stats Cards -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon stat-1">
                        <i class="fas fa-users"></i>
                    </div>
                    <h3><?php echo $totalMembers; ?></h3>
                    <p>Total Members</p>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon stat-2">
                        <i class="fas fa-dumbbell"></i>
                    </div>
                    <h3><?php echo $totalTrainers; ?></h3>
                    <p>Total Trainers</p>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon stat-3">
                        <i class="fas fa-calendar-check"></i>
                    </div>
                    <h3><?php echo $totalBookings; ?></h3>
                    <p>Total Bookings</p>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon stat-4">
                        <i class="fas fa-dollar-sign"></i>
                    </div>
                    <h3>$<?php echo number_format($revenue, 2); ?></h3>
                    <p>Total Revenue</p>
                </div>
            </div>
            
            <!-- Recent Members -->
            <div class="section-card">
                <div class="section-header">
                    <h2>Recent Members</h2>
                    <a href="members.php">View All</a>
                </div>
                
                <table class="table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Joined</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($recentMembers->num_rows > 0): ?>
                            <?php while($member = $recentMembers->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo $member['name']; ?></td>
                                <td><?php echo $member['email']; ?></td>
                                <td><?php echo date('M j, Y', strtotime($member['created_at'])); ?></td>
                                <td><span class="badge badge-success">Active</span></td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4">No members found</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            
            <!-- Recent Bookings -->
            <div class="section-card">
                <div class="section-header">
                    <h2>Recent Bookings</h2>
                    <a href="bookings.php">View All</a>
                </div>
                
                <table class="table">
                    <thead>
                        <tr>
                            <th>Member</th>
                            <th>Package</th>
                            <th>Date</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($recentBookings->num_rows > 0): ?>
                            <?php while($booking = $recentBookings->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo $booking['member_name']; ?></td>
                                <td><?php echo $booking['package_name']; ?></td>
                                <td><?php echo date('M j, Y', strtotime($booking['session_date'])); ?></td>
                                <td>
                                    <?php if ($booking['status'] == 'confirmed'): ?>
                                        <span class="badge badge-success">Confirmed</span>
                                    <?php elseif ($booking['status'] == 'pending'): ?>
                                        <span class="badge badge-warning">Pending</span>
                                    <?php else: ?>
                                        <span class="badge badge-danger">Cancelled</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4">No bookings found</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>