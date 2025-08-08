<?php 
include 'config.php';

// Check if user is logged in
if (!isLoggedIn()) {
    redirect('login.php');
}

// Get training plans based on user type
if (isAdmin()) {
    $plans = $conn->query("SELECT tp.*, u.name as member_name, t.name as trainer_name 
                          FROM training_plans tp
                          LEFT JOIN users u ON tp.member_id = u.id
                          LEFT JOIN users t ON tp.trainer_id = t.id
                          ORDER BY tp.created_at DESC");
} elseif (isTrainer()) {
    $plans = $conn->query("SELECT tp.*, u.name as member_name 
                          FROM training_plans tp
                          JOIN users u ON tp.member_id = u.id
                          WHERE tp.trainer_id = {$_SESSION['user_id']}
                          ORDER BY tp.created_at DESC");
} else {
    $plans = $conn->query("SELECT tp.*, u.name as trainer_name 
                          FROM training_plans tp
                          JOIN users u ON tp.trainer_id = u.id
                          WHERE tp.member_id = {$_SESSION['user_id']}
                          ORDER BY tp.created_at DESC");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Training Plans | FitPro</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    <style>
        /* Training Plans Specific Styles */
        .plans-container {
            background-color: white;
            border-radius: 10px;
            padding: 2rem;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
        }
        
        .plans-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
        }
        
        .plans-header h2 {
            font-size: 1.5rem;
        }
        
        .plan-actions {
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
        
        .btn-new-plan {
            background-color: var(--success);
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 5px;
            display: flex;
            align-items: center;
        }
        
        .btn-new-plan i {
            margin-right: 0.5rem;
        }
        
        .plans-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 2rem;
        }
        
        .plan-card {
            background-color: white;
            border-radius: 10px;
            padding: 1.5rem;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            border: 1px solid #eee;
            transition: all 0.3s ease;
        }
        
        .plan-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        }
        
        .plan-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid #eee;
        }
        
        .plan-title {
            font-size: 1.2rem;
            font-weight: 600;
        }
        
        .plan-date {
            font-size: 0.9rem;
            color: #6c757d;
        }
        
        .plan-meta {
            display: flex;
            justify-content: space-between;
            margin-bottom: 1rem;
        }
        
        .plan-member, .plan-trainer {
            font-size: 0.9rem;
        }
        
        .plan-member strong, .plan-trainer strong {
            color: var(--dark);
        }
        
        .plan-goal {
            margin-bottom: 1rem;
            padding: 0.8rem;
            background-color: #f8f9fa;
            border-radius: 5px;
        }
        
        .plan-goal h4 {
            font-size: 0.9rem;
            margin-bottom: 0.5rem;
            color: var(--dark);
        }
        
        .plan-exercises h4 {
            font-size: 0.9rem;
            margin-bottom: 0.5rem;
            color: var(--dark);
        }
        
        .exercise-list {
            list-style: none;
        }
        
        .exercise-item {
            display: flex;
            justify-content: space-between;
            padding: 0.5rem 0;
            border-bottom: 1px solid #eee;
        }
        
        .exercise-name {
            flex: 1;
        }
        
        .exercise-sets {
            color: #6c757d;
            font-size: 0.9rem;
        }
        
        .plan-footer {
            display: flex;
            justify-content: flex-end;
            margin-top: 1rem;
            padding-top: 1rem;
            border-top: 1px solid #eee;
        }
        
        .btn-view-plan {
            background-color: var(--primary);
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 5px;
            font-size: 0.9rem;
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
        @media (max-width: 768px) {
            .plans-header {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .plan-actions {
                width: 100%;
                margin-top: 1rem;
                justify-content: space-between;
            }
            
            .search-box input {
                width: 180px;
            }
            
            .plans-grid {
                grid-template-columns: 1fr;
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
                <h1>Training Plans</h1>
                <p>View and manage personalized training plans</p>
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
        
        <div class="plans-container">
            <div class="plans-header">
                <h2>Your Training Plans</h2>
                <div class="plan-actions">
                    <div class="search-box">
                        <i class="fas fa-search"></i>
                        <input type="text" placeholder="Search plans...">
                    </div>
                    <?php if (isAdmin() || isTrainer()): ?>
                    <a href="new_plan.php" class="btn-new-plan">
                        <i class="fas fa-plus"></i> New Plan
                    </a>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="plans-grid">
                <?php while($plan = $plans->fetch_assoc()): 
                    // Get exercises for this plan
                    $exercises = $conn->query("SELECT * FROM plan_exercises WHERE plan_id = {$plan['id']} LIMIT 3");
                ?>
                <div class="plan-card">
                    <div class="plan-header">
                        <h3 class="plan-title"><?php echo $plan['title']; ?></h3>
                        <span class="plan-date"><?php echo date('M d, Y', strtotime($plan['created_at'])); ?></span>
                    </div>
                    
                    <div class="plan-meta">
                        <?php if (isAdmin() || isTrainer()): ?>
                        <div class="plan-member">
                            <strong>Member:</strong> <?php echo $plan['member_name']; ?>
                        </div>
                        <?php endif; ?>
                        
                        <?php if (isAdmin() || isMember()): ?>
                        <div class="plan-trainer">
                            <strong>Trainer:</strong> <?php echo $plan['trainer_name']; ?>
                        </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="plan-goal">
                        <h4>Goal</h4>
                        <p><?php echo $plan['goal'] ? $plan['goal'] : 'No specific goal set'; ?></p>
                    </div>
                    
                    <div class="plan-exercises">
                        <h4>Exercises</h4>
                        <ul class="exercise-list">
                            <?php while($exercise = $exercises->fetch_assoc()): ?>
                            <li class="exercise-item">
                                <span class="exercise-name"><?php echo $exercise['name']; ?></span>
                                <span class="exercise-sets"><?php echo $exercise['sets']; ?> sets × <?php echo $exercise['reps']; ?> reps</span>
                            </li>
                            <?php endwhile; ?>
                            
                            <?php if ($exercises->num_rows === 0): ?>
                            <li>No exercises added yet</li>
                            <?php endif; ?>
                        </ul>
                    </div>
                    
                    <div class="plan-footer">
                        <a href="plan_details.php?id=<?php echo $plan['id']; ?>" class="btn-view-plan">View Full Plan</a>
                    </div>
                </div>
                <?php endwhile; ?>
                
                <?php if ($plans->num_rows === 0): ?>
                <div class="no-plans">
                    <p>No training plans found.</p>
                    <?php if (isAdmin() || isTrainer()): ?>
                    <a href="new_plan.php" class="btn-new-plan">
                        <i class="fas fa-plus"></i> Create Your First Plan
                    </a>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
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
</body>
</html>