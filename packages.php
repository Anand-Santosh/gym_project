<?php 
include 'config.php';

// Check if user is logged in and is admin
if (!isLoggedIn() || !isAdmin()) {
    redirect('index.php');
}

// Handle package deletion
if (isset($_GET['delete'])) {
    $packageId = $_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM packages WHERE id = ?");
    $stmt->bind_param("i", $packageId);
    
    if ($stmt->execute()) {
        $_SESSION['message'] = "Package deleted successfully";
        $_SESSION['message_type'] = "success";
    } else {
        $_SESSION['message'] = "Error deleting package";
        $_SESSION['message_type'] = "danger";
    }
    
    $stmt->close();
    redirect('packages.php');
}

// Get all packages
$packages = $conn->query("SELECT * FROM packages ORDER BY created_at DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Package Management | FitPro</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    <style>
        /* Package Management Specific Styles */
        .packages-container {
            background-color: white;
            border-radius: 10px;
            padding: 2rem;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
        }
        
        .packages-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
        }
        
        .packages-header h2 {
            font-size: 1.5rem;
        }
        
        .search-add {
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
        
        .btn-add {
            background-color: var(--success);
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 5px;
            display: flex;
            align-items: center;
        }
        
        .btn-add i {
            margin-right: 0.5rem;
        }
        
        .packages-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .packages-table th, .packages-table td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid #eee;
        }
        
        .packages-table th {
            font-weight: 600;
            color: var(--dark);
            background-color: #f8f9fa;
        }
        
        .packages-table tr:hover {
            background-color: #f8f9fa;
        }
        
        .package-image {
            width: 60px;
            height: 60px;
            border-radius: 5px;
            object-fit: cover;
        }
        
        .package-status {
            padding: 0.3rem 0.8rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 500;
        }
        
        .status-active {
            background-color: rgba(76, 201, 240, 0.1);
            color: var(--success);
        }
        
        .status-inactive {
            background-color: rgba(108, 117, 125, 0.1);
            color: var(--gray);
        }
        
        .action-buttons {
            display: flex;
        }
        
        .btn-edit {
            background-color: var(--warning);
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
        @media (max-width: 768px) {
            .packages-header {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .search-add {
                width: 100%;
                margin-top: 1rem;
                justify-content: space-between;
            }
            
            .search-box input {
                width: 180px;
            }
            
            .packages-table {
                display: block;
                overflow-x: auto;
            }
        }
    </style>
</head>
<body>
    
    
    <div class="main-content">
        <div class="dashboard-header">
            <div class="dashboard-title">
                <h1>Package Management</h1>
                <p>Manage all gym membership packages</p>
            </div>
            <div class="user-menu">
                <img src="assets/images/admin-avatar.jpg" alt="User Avatar">
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
        
        <div class="packages-container">
            <div class="packages-header">
                <h2>All Packages</h2>
                <div class="search-add">
                    <div class="search-box">
                        <i class="fas fa-search"></i>
                        <input type="text" placeholder="Search packages...">
                    </div>
                    <a href="add_package.php" class="btn-add">
                        <i class="fas fa-plus"></i> Add Package
                    </a>
                </div>
            </div>
            
            <table class="packages-table">
                <thead>
                    <tr>
                        <th>Image</th>
                        <th>Package Name</th>
                        <th>Price</th>
                        <th>Duration</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($package = $packages->fetch_assoc()): ?>
                    <tr>
                        <td>
                            <img src="assets/images/packages/<?php echo $package['image'] ? $package['image'] : 'default.jpg'; ?>" alt="<?php echo $package['name']; ?>" class="package-image">
                        </td>
                        <td><?php echo $package['name']; ?></td>
                        <td>$<?php echo $package['price']; ?></td>
                        <td><?php echo $package['duration']; ?></td>
                        <td>
                            <span class="package-status <?php echo $package['active'] ? 'status-active' : 'status-inactive'; ?>">
                                <?php echo $package['active'] ? 'Active' : 'Inactive'; ?>
                            </span>
                        </td>
                        <td>
                            <div class="action-buttons">
                                <a href="edit_package.php?id=<?php echo $package['id']; ?>" class="btn-action btn-edit"><i class="fas fa-edit"></i></a>
                                <a href="packages.php?delete=<?php echo $package['id']; ?>" class="btn-action btn-delete" onclick="return confirm('Are you sure you want to delete this package?')"><i class="fas fa-trash"></i></a>
                            </div>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
            
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