<?php 
include 'config.php';

// Check if user is logged in
if (!isLoggedIn()) {
    redirect('login.php');
}

// Get progress data based on user type
if (isAdmin()) {
    $progressData = $conn->query("SELECT p.*, u.name as member_name 
                                FROM progress p
                                JOIN users u ON p.user_id = u.id
                                ORDER BY p.date_recorded DESC");
} elseif (isTrainer()) {
    $progressData = $conn->query("SELECT p.*, u.name as member_name 
                                FROM progress p
                                JOIN users u ON p.user_id = u.id
                                WHERE p.trainer_id = {$_SESSION['user_id']}
                                ORDER BY p.date_recorded DESC");
} else {
    $progressData = $conn->query("SELECT p.* 
                                FROM progress p
                                WHERE p.user_id = {$_SESSION['user_id']}
                                ORDER BY p.date_recorded DESC");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Progress Tracking | FitPro</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/chart.js@3.7.1/dist/chart.min.css">
    <style>
        /* Progress Tracking Specific Styles */
        .progress-container {
            background-color: white;
            border-radius: 10px;
            padding: 2rem;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
        }
        
        .progress-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
        }
        
        .progress-header h2 {
            font-size: 1.5rem;
        }
        
        .progress-actions {
            display: flex;
            align-items: center;
        }
        
        .search-box {
            position: relative;
            margin-right: 1rem;
        }
        
        .search-box input {
            padding: 0.5rem 1rem 0.5rem 2.5rem;
            border: 1px solid #ddd;
            border-radius: 5px;
            width: 250px;
        }
        
        .search-box i {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: #6c757d;
        }
        
        .btn-new-progress {
            background-color: var(--success);
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 5px;
            display: flex;
            align-items: center;
        }
        
        .btn-new-progress i {
            margin-right: 0.5rem;
        }
        
        .progress-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 2rem;
        }
        
        .progress-chart {
            background-color: white;
            border-radius: 10px;
            padding: 1.5rem;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            border: 1px solid #eee;
        }
        
        .chart-container {
            position: relative;
            height: 300px;
            width: 100%;
        }
        
        .progress-table-container {
            background-color: white;
            border-radius: 10px;
            padding: 1.5rem;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            border: 1px solid #eee;
        }
        
        .progress-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .progress-table th, .progress-table td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid #eee;
        }
        
        .progress-table th {
            font-weight: 600;
            color: var(--dark);
            background-color: #f8f9fa;
        }
        
        .progress-table tr:hover {
            background-color: #f8f9fa;
        }
        
        .progress-value {
            font-weight: 600;
        }
        
        .progress-improvement {
            display: inline-block;
            padding: 0.2rem 0.5rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 500;
        }
        
        .improvement-up {
            background-color: rgba(76, 201, 240, 0.1);
            color: var(--success);
        }
        
        .improvement-down {
            background-color: rgba(247, 37, 133, 0.1);
            color: var(--danger);
        }
        
        .improvement-neutral {
            background-color: rgba(108, 117, 125, 0.1);
            color: var(--gray);
        }
        
        .action-buttons {
            display: flex;
        }
        
        .btn-view {
            background-color: var(--primary);
            color: white;
            margin-right: 0.5rem;
        }
        
        .btn-delete {
            background-color: var(--danger);
            color: white;
        }
        
        .pagination {
            display: flex;
            justify-content: center;
            margin-top: 2rem;
        }
        
        .pagination a {
            padding: 0.5rem 1rem;
            margin: 0 0.3rem;
            border: 1px solid #ddd;
            border-radius: 5px;
            color: var(--dark);
            text-decoration: none;
        }
        
        .pagination a.active {
            background-color: var(--primary);
            color: white;
            border-color: var(--primary);
        }
        
        .pagination a:hover:not(.active) {
            background-color: #f1f1f1;
        }
        
        /* Responsive */
        @media (max-width: 992px) {
            .progress-grid {
                grid-template-columns: 1fr;
            }
        }
        
        @media (max-width: 768px) {
            .progress-header {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .progress-actions {
                width: 100%;
                margin-top: 1rem;
                justify-content: space-between;
            }
            
            .search-box input {
                width: 180px;
            }
            
            .progress-table {
                display: block;
                overflow-x: auto;
            }
        }
    </style>
</head>
<body>
    <?php 
    if (isAdmin()) {
        include 'dashboard.php';
    } elseif (isTrainer()) {
        include 'trainer_dashboard.php';
    } else {
        include 'member_dashboard.php';
    }
    ?>
    
    <div class="main-content">
        <div class="dashboard-header">
            <div class="dashboard-title">
                <h1>Progress Tracking</h1>
                <p>Track and analyze your fitness progress</p>
            </div>
            <div class="user-menu">
                <img src="assets/images/<?php echo isAdmin() ? 'admin' : (isTrainer() ? 'trainers' : 'members'); ?>/<?php echo $_SESSION['user_avatar'] ? $_SESSION['user_avatar'] : 'default.jpg'; ?>" alt="User Avatar">
                <span><?php echo $_SESSION['user_name']; ?></span>
            </div>
        </div>
        
        <?php if (isset($_SESSION['message'])): ?>
        <div class="alert alert-<?php echo $_SESSION['message_type']; ?>">
            <?php 
            echo $_SESSION['message']; 
            unset($_SESSION['message']);
            unset($_SESSION['message_type']);
            ?>
        </div>
        <?php endif; ?>
        
        <div class="progress-container">
            <div class="progress-header">
                <h2>Your Progress</h2>
                <div class="progress-actions">
                    <div class="search-box">
                        <i class="fas fa-search"></i>
                        <input type="text" placeholder="Search progress...">
                    </div>
                    <?php if (isAdmin() || isTrainer()): ?>
                    <a href="new_progress.php" class="btn-new-progress">
                        <i class="fas fa-plus"></i> New Entry
                    </a>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="progress-grid">
                <div class="progress-chart">
                    <h3>Weight Progress</h3>
                    <div class="chart-container">
                        <canvas id="weightChart"></canvas>
                    </div>
                </div>
                
                <div class="progress-chart">
                    <h3>Body Measurements</h3>
                    <div class="chart-container">
                        <canvas id="measurementsChart"></canvas>
                    </div>
                </div>
            </div>
            
            <div class="progress-table-container" style="margin-top: 2rem;">
                <h3>Recent Progress Entries</h3>
                <table class="progress-table">
                    <thead>
                        <tr>
                            <?php if (isAdmin() || isTrainer()): ?>
                            <th>Member</th>
                            <?php endif; ?>
                            <th>Date</th>
                            <th>Weight</th>
                            <th>Body Fat</th>
                            <th>Muscle Mass</th>
                            <th>Improvement</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $previousWeight = null;
                        while($progress = $progressData->fetch_assoc()): 
                            // Calculate improvement
                            $improvement = null;
                            if ($previousWeight !== null) {
                                $difference = $progress['weight'] - $previousWeight;
                                $improvement = $difference < 0 ? 'down' : ($difference > 0 ? 'up' : 'neutral');
                            }
                            $previousWeight = $progress['weight'];
                        ?>
                        <tr>
                            <?php if (isAdmin() || isTrainer()): ?>
                            <td><?php echo $progress['member_name']; ?></td>
                            <?php endif; ?>
                            <td><?php echo date('M d, Y', strtotime($progress['date_recorded'])); ?></td>
                            <td class="progress-value"><?php echo $progress['weight']; ?> kg</td>
                            <td class="progress-value"><?php echo $progress['body_fat']; ?>%</td>
                            <td class="progress-value"><?php echo $progress['muscle_mass']; ?> kg</td>
                            <td>
                                <?php if ($improvement): ?>
                                <span class="progress-improvement improvement-<?php echo $improvement; ?>">
                                    <?php echo ucfirst($improvement); ?>
                                </span>
                                <?php else: ?>
                                <span class="progress-improvement improvement-neutral">First Record</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="action-buttons">
                                    <a href="progress_details.php?id=<?php echo $progress['id']; ?>" class="btn-action btn-view"><i class="fas fa-eye"></i></a>
                                    <?php if (isAdmin() || isTrainer()): ?>
                                    <a href="delete_progress.php?id=<?php echo $progress['id']; ?>" class="btn-action btn-delete" onclick="return confirm('Are you sure you want to delete this progress entry?')"><i class="fas fa-trash"></i></a>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                        
                        <?php if ($progressData->num_rows === 0): ?>
                        <tr>
                            <td colspan="<?php echo isAdmin() || isTrainer() ? 7 : 6; ?>" style="text-align: center;">No progress data found</td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            
            <div class="pagination">
                <a href="#">&laquo;</a>
                <a href="#" class="active">1</a>
                <a href="#">2</a>
                <a href="#">3</a>
                <a href="#">&raquo;</a>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.7.1/dist/chart.min.js"></script>
    <script>
        // Sample data for charts - in a real app, you would fetch this from the server
        const weightData = {
            labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
            datasets: [{
                label: 'Weight (kg)',
                data: [85, 83, 81, 80, 79, 78],
                borderColor: 'rgb(75, 192, 192)',
                tension: 0.1,
                fill: false
            }]
        };
        
        const measurementsData = {
            labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
            datasets: [
                {
                    label: 'Chest (cm)',
                    data: [105, 104, 103, 102, 102, 101],
                    borderColor: 'rgb(255, 99, 132)',
                    tension: 0.1,
                    fill: false
                },
                {
                    label: 'Waist (cm)',
                    data: [95, 93, 91, 90, 89, 88],
                    borderColor: 'rgb(54, 162, 235)',
                    tension: 0.1,
                    fill: false
                },
                {
                    label: 'Arms (cm)',
                    data: [35, 35.5, 36, 36, 36.5, 36.5],
                    borderColor: 'rgb(255, 205, 86)',
                    tension: 0.1,
                    fill: false
                }
            ]
        };
        
        // Initialize charts
        document.addEventListener('DOMContentLoaded', function() {
            const weightCtx = document.getElementById('weightChart').getContext('2d');
            const measurementsCtx = document.getElementById('measurementsChart').getContext('2d');
            
            new Chart(weightCtx, {
                type: 'line',
                data: weightData,
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: false
                        }
                    }
                }
            });
            
            new Chart(measurementsCtx, {
                type: 'line',
                data: measurementsData,
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: false
                        }
                    }
                }
            });
        });
    </script>
</body>
</html>