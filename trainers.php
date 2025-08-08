<?php 
// Use require_once with absolute path to prevent multiple inclusions
require_once __DIR__ . '/config.php';

// Verify essential functions exist
if (!function_exists('isLoggedIn') || !function_exists('isAdmin') || !function_exists('redirect')) {
    die('System configuration error - please contact administrator');
}

// Check if user is logged in and is admin
if (!isLoggedIn() || !isAdmin()) {
    redirect('index.php');
}

// Handle trainer deletion
if (isset($_GET['delete'])) {
    $trainerId = (int)$_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM users WHERE id = ? AND type = 'trainer'");
    $stmt->bind_param("i", $trainerId);
    
    if ($stmt->execute()) {
        $_SESSION['message'] = "Trainer deleted successfully";
        $_SESSION['message_type'] = "success";
    } else {
        $_SESSION['message'] = "Error deleting trainer: " . $conn->error;
        $_SESSION['message_type'] = "danger";
    }
    
    $stmt->close();
    redirect('trainers.php');
}

// Get all trainers with pagination
$limit = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

$trainers = $conn->query("SELECT * FROM users WHERE type = 'trainer' ORDER BY created_at DESC LIMIT $limit OFFSET $offset");
$totalTrainers = $conn->query("SELECT COUNT(*) as count FROM users WHERE type = 'trainer'")->fetch_assoc()['count'];
$totalPages = ceil($totalTrainers / $limit);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trainer Management | FitPro</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .trainers-container {
            background-color: white;
            border-radius: 10px;
            padding: 2rem;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            margin-bottom: 2rem;
        }
        
        .trainers-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            flex-wrap: wrap;
            gap: 1rem;
        }
        
        .search-box {
            position: relative;
            flex-grow: 1;
            max-width: 400px;
        }
        
        .search-box input {
            padding: 0.5rem 1rem 0.5rem 2.5rem;
            border: 1px solid #ddd;
            border-radius: 5px;
            width: 100%;
        }
        
        .btn-action {
            padding: 0.5rem;
            border-radius: 5px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 32px;
            height: 32px;
        }
        
        @media (max-width: 768px) {
            .trainers-header {
                flex-direction: column;
                align-items: stretch;
            }
            
            .search-box {
                max-width: 100%;
            }
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <main class="main-content">
        <div class="dashboard-header">
            <div class="dashboard-title">
                <h1>Trainer Management</h1>
                <p>Manage all gym trainers and their accounts</p>
            </div>
            <?php include 'includes/user-menu.php'; ?>
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
        
        <div class="trainers-container">
            <div class="trainers-header">
                <h2>All Trainers</h2>
                <div class="d-flex align-items-center gap-3">
                    <div class="search-box">
                        <i class="fas fa-search position-absolute" style="left: 1rem; top: 50%; transform: translateY(-50%);"></i>
                        <input type="text" placeholder="Search trainers..." id="trainerSearch">
                    </div>
                    <a href="add_trainer.php" class="btn btn-success">
                        <i class="fas fa-plus me-2"></i> Add Trainer
                    </a>
                </div>
            </div>
            
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>Name</th>
                            <th>Specialty</th>
                            <th>Contact</th>
                            <th>Join Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($trainer = $trainers->fetch_assoc()): 
                            $specialty = $conn->query("SELECT specialty FROM trainer_details WHERE user_id = {$trainer['id']}")->fetch_assoc()['specialty'] ?? 'General';
                        ?>
                        <tr>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <img src="assets/images/trainers/<?php echo htmlspecialchars($trainer['avatar'] ?: 'default.jpg'); ?>" 
                                         alt="<?php echo htmlspecialchars($trainer['name']); ?>" 
                                         class="rounded-circle" width="40" height="40">
                                    <?php echo htmlspecialchars($trainer['name']); ?>
                                </div>
                            </td>
                            <td><span class="badge bg-primary"><?php echo htmlspecialchars($specialty); ?></span></td>
                            <td>
                                <div><?php echo htmlspecialchars($trainer['email']); ?></div>
                                <small class="text-muted"><?php echo htmlspecialchars($trainer['phone'] ?: 'N/A'); ?></small>
                            </td>
                            <td><?php echo date('M d, Y', strtotime($trainer['created_at'])); ?></td>
                            <td>
                                <div class="d-flex gap-2">
                                    <a href="edit_trainer.php?id=<?php echo (int)$trainer['id']; ?>" 
                                       class="btn-action btn-warning"
                                       title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="trainers.php?delete=<?php echo (int)$trainer['id']; ?>" 
                                       class="btn-action btn-danger"
                                       title="Delete"
                                       onclick="return confirm('Are you sure you want to delete this trainer?')">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
            
            <?php if ($totalPages > 1): ?>
            <nav class="mt-4">
                <ul class="pagination justify-content-center">
                    <li class="page-item <?php echo $page <= 1 ? 'disabled' : ''; ?>">
                        <a class="page-link" href="trainers.php?page=<?php echo $page-1; ?>">Previous</a>
                    </li>
                    
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                        <a class="page-link" href="trainers.php?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                    </li>
                    <?php endfor; ?>
                    
                    <li class="page-item <?php echo $page >= $totalPages ? 'disabled' : ''; ?>">
                        <a class="page-link" href="trainers.php?page=<?php echo $page+1; ?>">Next</a>
                    </li>
                </ul>
            </nav>
            <?php endif; ?>
        </div>
    </main>

    <?php include 'includes/footer.php'; ?>
    
    <script>
    // Simple search functionality
    document.getElementById('trainerSearch').addEventListener('input', function() {
        const searchTerm = this.value.toLowerCase();
        const rows = document.querySelectorAll('tbody tr');
        
        rows.forEach(row => {
            const text = row.textContent.toLowerCase();
            row.style.display = text.includes(searchTerm) ? '' : 'none';
        });
    });
    </script>
</body>
</html>