<?php
include 'config.php';

// Check if user is logged in and is a trainer
if (!isLoggedIn() || !isTrainer()) {
    redirect('login.php');
}

// Get trainer data
$trainerId = $_SESSION['user_id'];
$trainer = $conn->query("SELECT * FROM users WHERE id = $trainerId")->fetch_assoc();

// Get upcoming sessions
$upcomingSessions = $conn->query("
    SELECT b.*, u.name as member_name, p.name as package_name 
    FROM bookings b
    JOIN users u ON b.user_id = u.id
    JOIN packages p ON b.package_id = p.id
    WHERE b.trainer_id = $trainerId AND b.session_date >= CURDATE()
    ORDER BY b.session_date ASC
    LIMIT 3
");

// Get total clients
$totalClients = $conn->query("
    SELECT COUNT(DISTINCT user_id) as count 
    FROM bookings 
    WHERE trainer_id = $trainerId
")->fetch_assoc()['count'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trainer Dashboard | FitPro</title>
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
            grid-template-columns: repeat(3, 1fr);
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
        .session-card {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem;
            border-bottom: 1px solid #eee;
        }
        .session-card:last-child {
            border-bottom: none;
        }
        .session-info h4 {
            margin-bottom: 0.5rem;
        }
        .session-date {
            color: #6c757d;
            font-size: 0.9rem;
        }
        .session-status {
            padding: 0.3rem 0.8rem;
            border-radius: 20px;
            font-size: 0.8rem;
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
                <img src="assets/images/trainers/<?php echo $trainer['avatar'] ? $trainer['avatar'] : 'default.jpg'; ?>" alt="Trainer Avatar">
                <h3><?php echo $trainer['name']; ?></h3>
                <p>Trainer</p>
            </div>
            <ul class="sidebar-menu">
                <li><a href="trainer_dashboard.php" class="active"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                <li><a href="trainer_sessions.php"><i class="fas fa-calendar-check"></i> My Sessions</a></li>
                <li><a href="trainer_clients.php"><i class="fas fa-users"></i> My Clients</a></li>
                <li><a href="trainer_plans.php"><i class="fas fa-clipboard-list"></i> Training Plans</a></li>
                <li><a href="trainer_profile.php"><i class="fas fa-user"></i> Profile</a></li>
                <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            </ul>
        </div>
        
        <!-- Main Content -->
        <div class="main-content">
            <div class="dashboard-header">
                <div class="dashboard-title">
                    <h1>Trainer Dashboard</h1>
                    <p>Welcome back, <?php echo $trainer['name']; ?></p>
                </div>
                <div class="user-menu">
                    <img src="assets/images/trainers/<?php echo $trainer['avatar'] ? $trainer['avatar'] : 'default.jpg'; ?>" alt="User Avatar">
                    <span><?php echo $trainer['name']; ?></span>
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
                        $sessionCount = $conn->query("SELECT COUNT(*) as count FROM bookings WHERE trainer_id = $trainerId AND session_date >= CURDATE()")->fetch_assoc()['count'];
                        echo $sessionCount;
                        ?>
                    </h3>
                    <p>Upcoming Sessions</p>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon stat-2">
                        <i class="fas fa-users"></i>
                    </div>
                    <h3><?php echo $totalClients; ?></h3>
                    <p>Total Clients</p>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon stat-3">
                        <i class="fas fa-star"></i>
                    </div>
                    <h3>
                        <?php 
                        $rating = $conn->query("SELECT AVG(rating) as avg FROM trainer_ratings WHERE trainer_id = $trainerId")->fetch_assoc()['avg'];
                        echo $rating ? number_format($rating, 1) : 'N/A';
                        ?>
                    </h3>
                    <p>Average Rating</p>
                </div>
            </div>
            
            <!-- Upcoming Sessions -->
            <div class="section-card">
                <div class="section-header">
                    <h2>Upcoming Sessions</h2>
                    <a href="trainer_sessions.php">View All</a>
                </div>
                
                <?php if ($upcomingSessions->num_rows > 0): ?>
                    <?php while($session = $upcomingSessions->fetch_assoc()): ?>
                    <div class="session-card">
                        <div class="session-info">
                            <h4><?php echo $session['member_name']; ?> - <?php echo $session['package_name']; ?></h4>
                            <p class="session-date">
                                <?php echo date('D, M j', strtotime($session['session_date'])); ?> 
                                at <?php echo date('g:i A', strtotime($session['session_time'])); ?>
                            </p>
                        </div>
                        <span class="session-status status-<?php echo $session['status']; ?>">
                            <?php echo ucfirst($session['status']); ?>
                        </span>
                    </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p>No upcoming sessions scheduled.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>