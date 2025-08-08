<?php 
include 'config.php';

// Check if user is logged in and is admin
if (!isLoggedIn() || !isAdmin()) {
    redirect('index.php');
}

// Get report data
$membersCount = $conn->query("SELECT COUNT(*) as count FROM users WHERE type = 'member'")->fetch_assoc()['count'];
$trainersCount = $conn->query("SELECT COUNT(*) as count FROM users WHERE type = 'trainer'")->fetch_assoc()['count'];
$activePackages = $conn->query("SELECT COUNT(*) as count FROM packages WHERE active = 1")->fetch_assoc()['count'];
$totalRevenue = $conn->query("SELECT SUM(amount) as total FROM payments")->fetch_assoc()['total'];
$monthlyRevenue = $conn->query("SELECT DATE_FORMAT(payment_date, '%Y-%m') as month, SUM(amount) as total 
                               FROM payments 
                               GROUP BY DATE_FORMAT(payment_date, '%Y-%m') 
                               ORDER BY month DESC 
                               LIMIT 6")->fetch_all(MYSQLI_ASSOC);
$popularPackages = $conn->query("SELECT p.name, COUNT(b.id) as bookings 
                                FROM packages p 
                                LEFT JOIN bookings b ON p.id = b.package_id 
                                GROUP BY p.id 
                                ORDER BY bookings DESC 
                                LIMIT 5")->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports | FitPro</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/chart.js@3.7.1/dist/chart.min.css">
    <style>
        /* Reports Specific Styles */
        .reports-container {
            background-color: white;
            border-radius: 10px;
            padding: 2rem;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
        }
        
        .reports-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
        }
        
        .reports-header h2 {
            font-size: 1.5rem;
        }
        
        .report-actions {
            display: flex;
            align-items: center;
        }
        
        .btn-export {
            background-color: var(--success);
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 5px;
            display: flex;
            align-items: center;
        }
        
        .btn-export i {
            margin-right: 0.5rem;
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
            display: flex;
            align-items: center;
        }
        
        .stat-icon {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 1rem;
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
            background-color: rgba(247, 37, 133, 0.1);
            color: var(--danger);
        }
        
        .stat-info h3 {
            font-size: 1.8rem;
            margin-bottom: 0.2rem;
        }
        
        .stat-info p {
            color: #6c757d;
            font-size: 0.9rem;
        }
        
        .charts-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 2rem;
            margin-bottom: 2rem;
        }
        
        .chart-container {
            background-color: white;
            border-radius: 10px;
            padding: 1.5rem;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
        }
        
        .chart-title {
            font-size: 1.2rem;
            margin-bottom: 1.5rem;
        }
        
        .chart-wrapper {
            position: relative;
            height: 300px;
            width: 100%;
        }
        
        .popular-packages {
            background-color: white;
            border-radius: 10px;
            padding: 1.5rem;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
        }
        
        .popular-packages h3 {
            font-size: 1.2rem;
            margin-bottom: 1.5rem;
        }
        
        .package-item {
            display: flex;
            justify-content: space-between;
            padding: 0.8rem 0;
            border-bottom: 1px solid #eee;
        }
        
        .package-item:last-child {
            border-bottom: none;
        }
        
        .package-name {
            font-weight: 500;
        }
        
        .package-bookings {
            color: var(--primary);
            font-weight: 600;
        }
        
        /* Responsive */
        @media (max-width: 1200px) {
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }
            
            .charts-grid {
                grid-template-columns: 1fr;
            }
        }
        
        @media (max-width: 768px) {
            .stats-grid {
                grid-template-columns: 1fr;
            }
            
            .reports-header {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .report-actions {
                width: 100%;
                margin-top: 1rem;
                justify-content: flex-end;
            }
        }
    </style>
</head>
<body>
   
    
    <div class="main-content">
        <div class="dashboard-header">
            <div class="dashboard-title">
                <h1>Reports</h1>
                <p>Analyze your gym's performance and metrics</p>
            </div>
            <div class="user-menu">
                <img src="assets/images/admin-avatar.jpg" alt="User Avatar">
                <span><?php echo $_SESSION['user_name']; ?></span>
            </div>
        </div>
        
        <div class="reports-container">
            <div class="reports-header">
                <h2>Gym Analytics</h2>
                <div class="report-actions">
                    <a href="export_reports.php" class="btn-export">
                        <i class="fas fa-file-export"></i> Export Reports
                    </a>
                </div>
            </div>
            
            <!-- Stats Cards -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon stat-1">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo $membersCount; ?></h3>
                        <p>Total Members</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon stat-2">
                        <i class="fas fa-user-tie"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo $trainersCount; ?></h3>
                        <p>Total Trainers</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon stat-3">
                        <i class="fas fa-box-open"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo $activePackages; ?></h3>
                        <p>Active Packages</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon stat-4">
                        <i class="fas fa-dollar-sign"></i>
                    </div>
                    <div class="stat-info">
                        <h3>$<?php echo number_format($totalRevenue, 2); ?></h3>
                        <p>Total Revenue</p>
                    </div>
                </div>
            </div>
            
            <!-- Charts -->
            <div class="charts-grid">
                <div class="chart-container">
                    <h3 class="chart-title">Monthly Revenue</h3>
                    <div class="chart-wrapper">
                        <canvas id="revenueChart"></canvas>
                    </div>
                </div>
                
                <div class="popular-packages">
                    <h3>Popular Packages</h3>
                    <?php foreach ($popularPackages as $package): ?>
                    <div class="package-item">
                        <span class="package-name"><?php echo $package['name']; ?></span>
                        <span class="package-bookings"><?php echo $package['bookings']; ?> bookings</span>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.7.1/dist/chart.min.js"></script>
    <script>
        // Revenue Chart
        document.addEventListener('DOMContentLoaded', function() {
            const revenueCtx = document.getElementById('revenueChart').getContext('2d');
            
            // Sample data - in a real app, you would use data from the server
            const revenueData = {
                labels: [
                    <?php 
                    $labels = [];
                    foreach (array_reverse($monthlyRevenue) as $month) {
                        $labels[] = "'" . date('M Y', strtotime($month['month'] . '-01')) . "'";
                    }
                    echo implode(', ', $labels);
                    ?>
                ],
                datasets: [{
                    label: 'Revenue',
                    data: [
                        <?php 
                        $data = [];
                        foreach (array_reverse($monthlyRevenue) as $month) {
                            $data[] = $month['total'];
                        }
                        echo implode(', ', $data);
                        ?>
                    ],
                    backgroundColor: 'rgba(67, 97, 238, 0.2)',
                    borderColor: 'rgba(67, 97, 238, 1)',
                    borderWidth: 2,
                    tension: 0.1,
                    fill: true
                }]
            };
            
            new Chart(revenueCtx, {
                type: 'line',
                data: revenueData,
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return '$' + value;
                                }
                            }
                        }
                    },
                    plugins: {
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return '$' + context.raw.toFixed(2);
                                }
                            }
                        }
                    }
                }
            });
        });
    </script>
</body>
</html>