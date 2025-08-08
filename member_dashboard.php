<?php
include 'config.php';

// Check if user is logged in and is a member
if (!isLoggedIn() || !isMember()) {
    redirect('login.php');
}

// Get member data
$memberId = $_SESSION['user_id'];
$member = $conn->query("SELECT * FROM users WHERE id = $memberId")->fetch_assoc();

// Get upcoming bookings
$upcomingBookings = $conn->query("
    SELECT b.*, p.name as package_name, t.name as trainer_name 
    FROM bookings b
    JOIN packages p ON b.package_id = p.id
    LEFT JOIN users t ON b.trainer_id = t.id
    WHERE b.user_id = $memberId AND b.session_date >= CURDATE()
    ORDER BY b.session_date ASC
    LIMIT 3
");

// Get latest progress
$latestProgress = $conn->query("
    SELECT * FROM progress 
    WHERE user_id = $memberId 
    ORDER BY date_recorded DESC 
    LIMIT 1
")->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Member Dashboard | FitPro</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
    :root {
        --primary: #4361ee;
        --secondary: #3f37c9;
        --accent: #4895ef;
        --dark: #1b263b;
        --light: #f8f9fa;
        --success: #4cc9f0;
        --warning: #f8961e;
        --danger: #f72585;
        --gray: #6c757d;
        --light-gray: #e9ecef;
    }

    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
        font-family: 'Poppins', sans-serif;
    }

    body {
        background-color: #f5f7fa;
        color: var(--dark);
        line-height: 1.6;
    }

    /* Dashboard Layout */
    .dashboard {
        display: flex;
        min-height: 100vh;
    }

    /* Sidebar Styles */
    .sidebar {
        background: linear-gradient(180deg, var(--dark), var(--secondary));
        color: white;
        padding: 2rem 1.5rem;
        width: 280px;
        position: fixed;
        height: 100vh;
        display: flex;
        flex-direction: column;
    }

    .sidebar-header {
        text-align: center;
        margin-bottom: 2rem;
        padding-bottom: 1.5rem;
        border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    }

    .sidebar-header img {
        width: 80px;
        height: 80px;
        border-radius: 50%;
        object-fit: cover;
        border: 3px solid var(--accent);
        margin-bottom: 1rem;
    }

    .sidebar-header h3 {
        font-size: 1.2rem;
        margin-bottom: 0.3rem;
    }

    .sidebar-header p {
        font-size: 0.9rem;
        opacity: 0.8;
    }

    .sidebar-menu {
        list-style: none;
        margin-top: 1rem;
        flex-grow: 1;
    }

    .sidebar-menu li {
        margin-bottom: 0.5rem;
    }

    .sidebar-menu a {
        display: flex;
        align-items: center;
        color: white;
        padding: 0.8rem 1rem;
        border-radius: 5px;
        text-decoration: none;
        transition: all 0.3s ease;
    }

    .sidebar-menu a:hover, 
    .sidebar-menu a.active {
        background-color: rgba(255, 255, 255, 0.1);
    }

    .sidebar-menu a i {
        margin-right: 12px;
        width: 20px;
        text-align: center;
        font-size: 1.1rem;
    }

    /* Main Content Styles */
    .main-content {
        margin-left: 280px;
        padding: 2rem;
        width: calc(100% - 280px);
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
        margin-bottom: 0.5rem;
    }

    .dashboard-title p {
        color: var(--gray);
        font-size: 1rem;
    }

    .user-menu {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .user-menu img {
        width: 45px;
        height: 45px;
        border-radius: 50%;
        object-fit: cover;
        border: 2px solid var(--accent);
    }

    .user-menu span {
        font-weight: 500;
    }

    /* Stats Grid */
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 1.5rem;
        margin-bottom: 2rem;
    }

    .stat-card {
        background-color: white;
        border-radius: 10px;
        padding: 1.8rem 1.5rem;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
        text-align: center;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    .stat-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
    }

    .stat-icon {
        width: 60px;
        height: 60px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 1.2rem;
        font-size: 1.5rem;
    }

    .stat-1 { background-color: rgba(67, 97, 238, 0.1); color: var(--primary); }
    .stat-2 { background-color: rgba(248, 150, 30, 0.1); color: var(--warning); }
    .stat-3 { background-color: rgba(76, 201, 240, 0.1); color: var(--success); }

    .stat-card h3 {
        font-size: 1.8rem;
        margin-bottom: 0.5rem;
        color: var(--dark);
    }

    .stat-card p {
        color: var(--gray);
        font-size: 0.95rem;
    }

    /* Section Cards */
    .section-card {
        background-color: white;
        border-radius: 10px;
        padding: 1.8rem;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
        margin-bottom: 2rem;
    }

    .section-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1.8rem;
    }

    .section-header h2 {
        font-size: 1.4rem;
        color: var(--dark);
    }

    .section-header a {
        color: var(--primary);
        text-decoration: none;
        font-weight: 500;
        display: flex;
        align-items: center;
        gap: 6px;
        transition: color 0.3s ease;
    }

    .section-header a:hover {
        color: var(--secondary);
    }

    .section-header a i {
        font-size: 0.8rem;
    }

    /* Bookings List */
    .bookings-list {
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }

    .booking-card {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 1.2rem;
        border-radius: 8px;
        background-color: var(--light);
        transition: all 0.3s ease;
    }

    .booking-card:hover {
        background-color: #e9ecef;
        transform: translateY(-2px);
    }

    .booking-info h4 {
        font-size: 1.1rem;
        margin-bottom: 0.5rem;
        color: var(--dark);
    }

    .booking-date {
        color: var(--gray);
        font-size: 0.9rem;
    }

    .booking-status {
        padding: 0.4rem 0.9rem;
        border-radius: 20px;
        font-size: 0.85rem;
        font-weight: 500;
    }

    .status-confirmed {
        background-color: rgba(67, 97, 238, 0.1);
        color: var(--primary);
    }

    .status-pending {
        background-color: rgba(248, 150, 30, 0.1);
        color: var(--warning);
    }

    /* Progress Section */
    .progress-item {
        margin-bottom: 1.8rem;
    }

    .progress-label {
        display: flex;
        justify-content: space-between;
        margin-bottom: 0.8rem;
        font-weight: 500;
    }

    .progress-bar {
        height: 10px;
        background-color: var(--light-gray);
        border-radius: 5px;
        overflow: hidden;
    }

    .progress-fill {
        height: 100%;
        background: linear-gradient(90deg, var(--primary), var(--accent));
        border-radius: 5px;
    }

    /* Quick Actions */
    .quick-actions {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
        gap: 1.5rem;
    }

    .quick-action {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        padding: 1.8rem 1rem;
        background-color: white;
        border-radius: 10px;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
        text-decoration: none;
        color: var(--dark);
        transition: all 0.3s ease;
        border: 1px solid #eee;
    }

    .quick-action:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        border-color: var(--primary);
    }

    .quick-action i {
        font-size: 2.2rem;
        margin-bottom: 1.2rem;
        color: var(--primary);
    }

    .quick-action span {
        font-weight: 500;
        font-size: 1rem;
    }

    /* Empty State */
    .empty-state {
        text-align: center;
        padding: 3rem 2rem;
        color: var(--gray);
    }

    .empty-state i {
        font-size: 3rem;
        margin-bottom: 1.5rem;
        color: #adb5bd;
    }

    .empty-state p {
        margin-bottom: 1.5rem;
        font-size: 1.1rem;
    }

    .btn {
        display: inline-block;
        padding: 0.7rem 1.5rem;
        background-color: var(--primary);
        color: white;
        border-radius: 5px;
        text-decoration: none;
        font-weight: 500;
        transition: all 0.3s ease;
    }

    .btn:hover {
        background-color: var(--secondary);
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
    }

    /* Responsive Adjustments */
    @media (max-width: 1200px) {
        .sidebar {
            width: 250px;
        }
        .main-content {
            margin-left: 250px;
            width: calc(100% - 250px);
        }
    }

    @media (max-width: 992px) {
        .stats-grid {
            grid-template-columns: repeat(2, 1fr);
        }
        .quick-actions {
            grid-template-columns: repeat(2, 1fr);
        }
    }

    @media (max-width: 768px) {
        .dashboard {
            flex-direction: column;
        }
        .sidebar {
            position: relative;
            width: 100%;
            height: auto;
        }
        .main-content {
            margin-left: 0;
            width: 100%;
            padding: 1.5rem;
        }
        .stats-grid {
            grid-template-columns: 1fr;
        }
        .dashboard-header {
            flex-direction: column;
            align-items: flex-start;
            gap: 1rem;
        }
        .user-menu {
            width: 100%;
            justify-content: flex-end;
        }
        .quick-actions {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 576px) {
        .main-content {
            padding: 1rem;
        }
        .section-card {
            padding: 1.5rem 1.2rem;
        }
        .booking-card {
            flex-direction: column;
            align-items: flex-start;
            gap: 1rem;
        }
    }
    </style>
</head>
<body>
    <div class="dashboard">
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="sidebar-header">
                <img src="assets/images/members/<?php echo $member['avatar'] ? $member['avatar'] : 'default.jpg'; ?>" alt="Member Avatar">
                <h3><?php echo htmlspecialchars($member['name']); ?></h3>
                <p>Member</p>
            </div>
            <ul class="sidebar-menu">
                <li><a href="member_dashboard.php" class="active"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                <li><a href="bookings.php"><i class="fas fa-calendar-check"></i> My Bookings</a></li>
                <li><a href="plans.php"><i class="fas fa-clipboard-list"></i> Training Plans</a></li>
                <li><a href="progress.php"><i class="fas fa-chart-line"></i> Progress</a></li>
                <li><a href="supplements.php"><i class="fas fa-pills"></i> Supplements</a></li>
                <li><a href="profile.php"><i class="fas fa-user"></i> Profile</a></li>
                <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            </ul>
        </div>
        
        <!-- Main Content -->
        <div class="main-content">
            <div class="dashboard-header">
                <div class="dashboard-title">
                    <h1>Member Dashboard</h1>
                    <p>Welcome back, <?php echo htmlspecialchars($member['name']); ?></p>
                </div>
                <div class="user-menu">
                    <img src="assets/images/members/<?php echo $member['avatar'] ? $member['avatar'] : 'default.jpg'; ?>" alt="User Avatar">
                    <span><?php echo htmlspecialchars($member['name']); ?></span>
                </div>
            </div>
            
            <!-- Stats Cards -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon stat-1">
                        <i class="fas fa-calendar-check"></i>
                    </div>
                    <h3>
                        <?php 
                        $bookingCount = $conn->query("SELECT COUNT(*) as count FROM bookings WHERE user_id = $memberId")->fetch_assoc()['count'];
                        echo $bookingCount;
                        ?>
                    </h3>
                    <p>Total Bookings</p>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon stat-2">
                        <i class="fas fa-dumbbell"></i>
                    </div>
                    <h3>
                        <?php 
                        $planCount = $conn->query("SELECT COUNT(*) as count FROM training_plans WHERE member_id = $memberId")->fetch_assoc()['count'];
                        echo $planCount;
                        ?>
                    </h3>
                    <p>Training Plans</p>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon stat-3">
                        <i class="fas fa-weight"></i>
                    </div>
                    <h3>
                        <?php 
                        if ($latestProgress) {
                            echo htmlspecialchars($latestProgress['weight']) . ' kg';
                        } else {
                            echo 'N/A';
                        }
                        ?>
                    </h3>
                    <p>Current Weight</p>
                </div>
            </div>
            
            <!-- Upcoming Bookings -->
            <div class="section-card">
                <div class="section-header">
                    <h2>Upcoming Sessions</h2>
                    <a href="bookings.php">View All <i class="fas fa-chevron-right"></i></a>
                </div>
                
                <?php if ($upcomingBookings->num_rows > 0): ?>
                    <div class="bookings-list">
                        <?php while($booking = $upcomingBookings->fetch_assoc()): ?>
                        <div class="booking-card">
                            <div class="booking-info">
                                <h4><?php echo htmlspecialchars($booking['package_name']); ?></h4>
                                <p class="booking-date">
                                    <?php echo date('D, M j', strtotime($booking['session_date'])); ?> 
                                    at <?php echo date('g:i A', strtotime($booking['session_time'])); ?>
                                </p>
                                <?php if ($booking['trainer_name']): ?>
                                    <p>With <?php echo htmlspecialchars($booking['trainer_name']); ?></p>
                                <?php endif; ?>
                            </div>
                            <span class="booking-status status-<?php echo htmlspecialchars($booking['status']); ?>">
                                <?php echo ucfirst(htmlspecialchars($booking['status'])); ?>
                            </span>
                        </div>
                        <?php endwhile; ?>
                    </div>
                <?php else: ?>
                    <div class="empty-state">
                        <i class="fas fa-calendar-times"></i>
                        <p>No upcoming sessions.</p>
                        <a href="new_booking.php" class="btn">Book a session now!</a>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- Progress Overview -->
            <div class="section-card">
                <div class="section-header">
                    <h2>Your Progress</h2>
                    <a href="progress.php">View Details <i class="fas fa-chevron-right"></i></a>
                </div>
                
                <?php if ($latestProgress): ?>
                    <div class="progress-item">
                        <div class="progress-label">
                            <span>Weight</span>
                            <span><?php echo htmlspecialchars($latestProgress['weight']); ?> kg</span>
                        </div>
                        <div class="progress-bar">
                            <div class="progress-fill" style="width: <?php echo min(100, ($latestProgress['weight'] / 120) * 100); ?>%"></div>
                        </div>
                    </div>
                    
                    <div class="progress-item">
                        <div class="progress-label">
                            <span>Body Fat %</span>
                            <span><?php echo htmlspecialchars($latestProgress['body_fat']); ?>%</span>
                        </div>
                        <div class="progress-bar">
                            <div class="progress-fill" style="width: <?php echo min(100, $latestProgress['body_fat']); ?>%"></div>
                        </div>
                    </div>
                    
                    <div class="progress-item">
                        <div class="progress-label">
                            <span>Muscle Mass</span>
                            <span><?php echo htmlspecialchars($latestProgress['muscle_mass']); ?> kg</span>
                        </div>
                        <div class="progress-bar">
                            <div class="progress-fill" style="width: <?php echo min(100, ($latestProgress['muscle_mass'] / 60) * 100); ?>%"></div>
                        </div>
                    </div>
                    
                    <div class="progress-item">
                        <div class="progress-label">
                            <span>Last Recorded</span>
                            <span><?php echo date('M j, Y', strtotime($latestProgress['date_recorded'])); ?></span>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="empty-state">
                        <i class="fas fa-chart-line"></i>
                        <p>No progress recorded yet.</p>
                        <a href="add_progress.php" class="btn">Add your first progress entry!</a>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- Quick Actions -->
            <div class="section-card">
                <div class="section-header">
                    <h2>Quick Actions</h2>
                </div>
                
                <div class="quick-actions">
                    <a href="new_booking.php" class="quick-action">
                        <i class="fas fa-calendar-plus"></i>
                        <span>Book a Session</span>
                    </a>
                    
                    <a href="add_progress.php" class="quick-action">
                        <i class="fas fa-plus-circle"></i>
                        <span>Add Progress</span>
                    </a>
                    
                    <a href="plans.php" class="quick-action">
                        <i class="fas fa-clipboard-list"></i>
                        <span>View Plans</span>
                    </a>
                    
                    <a href="profile.php" class="quick-action">
                        <i class="fas fa-user-edit"></i>
                        <span>Edit Profile</span>
                    </a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>