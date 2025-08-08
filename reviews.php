<?php 
include 'config.php';

// Check if user is logged in
if (!isLoggedIn()) {
    redirect('login.php');
}

// Handle review submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_review'])) {
    $rating = $_POST['rating'];
    $comment = $_POST['comment'];
    $userId = $_SESSION['user_id'];
    
    $stmt = $conn->prepare("INSERT INTO reviews (user_id, rating, comment) VALUES (?, ?, ?)");
    $stmt->bind_param("iis", $userId, $rating, $comment);
    
    if ($stmt->execute()) {
        $_SESSION['message'] = "Review submitted successfully";
        $_SESSION['message_type'] = "success";
    } else {
        $_SESSION['message'] = "Error submitting review";
        $_SESSION['message_type'] = "danger";
    }
    
    $stmt->close();
    redirect('reviews.php');
}

// Handle review deletion (admin only)
if (isset($_GET['delete']) && isAdmin()) {
    $reviewId = $_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM reviews WHERE id = ?");
    $stmt->bind_param("i", $reviewId);
    
    if ($stmt->execute()) {
        $_SESSION['message'] = "Review deleted successfully";
        $_SESSION['message_type'] = "success";
    } else {
        $_SESSION['message'] = "Error deleting review";
        $_SESSION['message_type'] = "danger";
    }
    
    $stmt->close();
    redirect('reviews.php');
}

// Get all reviews
$reviews = $conn->query("SELECT r.*, u.name as user_name, u.avatar as user_avatar 
                        FROM reviews r
                        JOIN users u ON r.user_id = u.id
                        ORDER BY r.created_at DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reviews | FitPro</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    <style>
        /* Reviews Specific Styles */
        .reviews-container {
            background-color: white;
            border-radius: 10px;
            padding: 2rem;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
        }
        
        .reviews-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
        }
        
        .reviews-header h2 {
            font-size: 1.5rem;
        }
        
        .average-rating {
            display: flex;
            align-items: center;
            background-color: #f8f9fa;
            padding: 1rem;
            border-radius: 10px;
            margin-bottom: 2rem;
        }
        
        .average-rating-value {
            font-size: 3rem;
            font-weight: 700;
            color: var(--primary);
            margin-right: 1.5rem;
        }
        
        .average-rating-stars {
            margin-bottom: 0.5rem;
        }
        
        .average-rating-count {
            color: #6c757d;
            font-size: 0.9rem;
        }
        
        .reviews-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 2rem;
        }
        
        .review-card {
            display: grid;
            grid-template-columns: 100px 1fr;
            gap: 1.5rem;
            padding-bottom: 2rem;
            border-bottom: 1px solid #eee;
        }
        
        .review-card:last-child {
            border-bottom: none;
        }
        
        .review-user {
            text-align: center;
        }
        
        .review-avatar {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            object-fit: cover;
            margin-bottom: 0.5rem;
        }
        
        .review-user-name {
            font-weight: 600;
            font-size: 0.9rem;
        }
        
        .review-date {
            color: #6c757d;
            font-size: 0.8rem;
        }
        
        .review-content {
            display: flex;
            flex-direction: column;
        }
        
        .review-rating {
            color: var(--warning);
            margin-bottom: 0.5rem;
        }
        
        .review-comment {
            line-height: 1.6;
            margin-bottom: 1rem;
        }
        
        .review-actions {
            margin-top: auto;
        }
        
        .btn-delete-review {
            background-color: var(--danger);
            color: white;
            padding: 0.3rem 0.8rem;
            border-radius: 5px;
            font-size: 0.8rem;
        }
        
        .add-review {
            background-color: white;
            border-radius: 10px;
            padding: 2rem;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            margin-top: 2rem;
        }
        
        .add-review h3 {
            margin-bottom: 1.5rem;
        }
        
        .rating-input {
            display: flex;
            align-items: center;
            margin-bottom: 1.5rem;
        }
        
        .rating-input span {
            margin-right: 1rem;
            font-weight: 500;
        }
        
        .rating-stars {
            direction: rtl;
            unicode-bidi: bidi-override;
        }
        
        .rating-stars input {
            display: none;
        }
        
        .rating-stars label {
            color: #ddd;
            font-size: 1.5rem;
            padding: 0 0.2rem;
            cursor: pointer;
        }
        
        .rating-stars input:checked ~ label,
        .rating-stars label:hover,
        .rating-stars label:hover ~ label {
            color: var(--warning);
        }
        
        .form-group {
            margin-bottom: 1.5rem;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
        }
        
        .form-group textarea {
            width: 100%;
            padding: 0.8rem;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 1rem;
            min-height: 150px;
        }
        
        .btn-submit-review {
            background-color: var(--primary);
            color: white;
            padding: 0.8rem 1.8rem;
            border-radius: 5px;
            border: none;
            cursor: pointer;
            font-size: 1rem;
            font-weight: 500;
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
            .review-card {
                grid-template-columns: 1fr;
            }
            
            .review-user {
                display: flex;
                align-items: center;
                text-align: left;
            }
            
            .review-avatar {
                margin-right: 1rem;
                margin-bottom: 0;
                width: 60px;
                height: 60px;
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
                <h1>Reviews</h1>
                <p>See what our members say about us</p>
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
        
        <div class="reviews-container">
            <div class="reviews-header">
                <h2>Member Reviews</h2>
            </div>
            
            <!-- Average Rating -->
            <?php 
            $avgRating = $conn->query("SELECT AVG(rating) as avg_rating, COUNT(*) as count FROM reviews")->fetch_assoc();
            ?>
            <div class="average-rating">
                <div class="average-rating-value"><?php echo number_format($avgRating['avg_rating'], 1); ?></div>
                <div>
                    <div class="average-rating-stars">
                        <?php 
                        $fullStars = floor($avgRating['avg_rating']);
                        $halfStar = ($avgRating['avg_rating'] - $fullStars) >= 0.5;
                        $emptyStars = 5 - $fullStars - ($halfStar ? 1 : 0);
                        
                        for ($i = 0; $i < $fullStars; $i++) {
                            echo '<i class="fas fa-star"></i>';
                        }
                        if ($halfStar) {
                            echo '<i class="fas fa-star-half-alt"></i>';
                        }
                        for ($i = 0; $i < $emptyStars; $i++) {
                            echo '<i class="far fa-star"></i>';
                        }
                        ?>
                    </div>
                    <div class="average-rating-count">Based on <?php echo $avgRating['count']; ?> reviews</div>
                </div>
            </div>
            
            <!-- Reviews List -->
            <div class="reviews-grid">
                <?php while($review = $reviews->fetch_assoc()): ?>
                <div class="review-card">
                    <div class="review-user">
                        <img src="assets/images/members/<?php echo $review['user_avatar'] ? $review['user_avatar'] : 'default.jpg'; ?>" alt="<?php echo $review['user_name']; ?>" class="review-avatar">
                        <div class="review-user-name"><?php echo $review['user_name']; ?></div>
                        <div class="review-date"><?php echo date('M d, Y', strtotime($review['created_at'])); ?></div>
                    </div>
                    <div class="review-content">
                        <div class="review-rating">
                            <?php 
                            for ($i = 1; $i <= 5; $i++) {
                                if ($i <= $review['rating']) {
                                    echo '<i class="fas fa-star"></i>';
                                } else {
                                    echo '<i class="far fa-star"></i>';
                                }
                            }
                            ?>
                        </div>
                        <div class="review-comment"><?php echo nl2br($review['comment']); ?></div>
                        <?php if (isAdmin()): ?>
                        <div class="review-actions">
                            <a href="reviews.php?delete=<?php echo $review['id']; ?>" class="btn-delete-review" onclick="return confirm('Are you sure you want to delete this review?')"><i class="fas fa-trash"></i> Delete</a>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endwhile; ?>
                
                <?php if ($reviews->num_rows === 0): ?>
                <div class="no-reviews">
                    <p>No reviews found.</p>
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
            
            <!-- Add Review Form (for members) -->
            <?php if (isMember()): ?>
            <div class="add-review">
                <h3>Write a Review</h3>
                <form method="POST">
                    <div class="rating-input">
                        <span>Your Rating:</span>
                        <div class="rating-stars">
                            <input type="radio" id="star5" name="rating" value="5" required>
                            <label for="star5"><i class="fas fa-star"></i></label>
                            <input type="radio" id="star4" name="rating" value="4">
                            <label for="star4"><i class="fas fa-star"></i></label>
                            <input type="radio" id="star3" name="rating" value="3">
                            <label for="star3"><i class="fas fa-star"></i></label>
                            <input type="radio" id="star2" name="rating" value="2">
                            <label for="star2"><i class="fas fa-star"></i></label>
                            <input type="radio" id="star1" name="rating" value="1">
                            <label for="star1"><i class="fas fa-star"></i></label>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="comment">Your Review</label>
                        <textarea id="comment" name="comment" required placeholder="Share your experience with our gym..."></textarea>
                    </div>
                    <button type="submit" name="submit_review" class="btn-submit-review">Submit Review</button>
                </form>
            </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>