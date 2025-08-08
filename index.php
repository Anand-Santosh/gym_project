<?php include 'config.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FitPro Gym Management</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <!-- Navigation -->
    <nav>
        <div class="logo">
            <img src="assets/images/logo.png" alt="FitPro Logo">
            <h1>FitPro</h1>
        </div>
        <ul class="nav-links">
            <li><a href="#home"><i class="fas fa-home"></i> Home</a></li>
            <li><a href="#features"><i class="fas fa-dumbbell"></i> Features</a></li>
            <li><a href="#packages"><i class="fas fa-box-open"></i> Packages</a></li>
            <li><a href="#supplements"><i class="fas fa-pills"></i> Supplements</a></li>
          
        </ul>
        <div class="auth-buttons">
    <?php if (isLoggedIn()): ?>
        <?php if (isAdmin()): ?>
            <a href="admin_dashboard.php" class="btn btn-signup">
                <i class="fas fa-tachometer-alt"></i> Admin Dashboard
            </a>
        <?php elseif (isTrainer()): ?>
            <a href="trainer_dashboard.php" class="btn btn-signup">
                <i class="fas fa-tachometer-alt"></i> Trainer Dashboard
            </a>
        <?php elseif (isMember()): ?>
            <a href="member_dashboard.php" class="btn btn-signup">
                <i class="fas fa-tachometer-alt"></i> Member Dashboard
            </a>
        <?php endif; ?>
        <a href="logout.php" class="btn btn-login">
            <i class="fas fa-sign-out-alt"></i> Logout
        </a>
    <?php else: ?>
        <a href="login.php" class="btn btn-login">
            <i class="fas fa-sign-in-alt"></i> Login
        </a>
        <a href="register.php" class="btn btn-signup">
            <i class="fas fa-user-plus"></i> Register
        </a>
    <?php endif; ?>
</div>
    </nav>

    <!-- Hero Section -->
    <section class="hero" id="home">
        <div class="hero-content">
            <h2>Transform Your Fitness Journey</h2>
            <p>Join our state-of-the-art gym with personalized training programs, premium supplements, and expert trainers to help you achieve your fitness goals.</p>
            <div class="hero-buttons">
                <a href="#packages" class="btn-hero">View Packages <i class="fas fa-arrow-right"></i></a>
                <a href="#supplements" class="btn-hero btn-hero-outline">Shop Supplements <i class="fas fa-shopping-cart"></i></a>
            </div>
        </div>
        <div class="hero-image">
            <img src="assets/images/hero-image.png" alt="Fitness Model">
        </div>
    </section>

    <!-- Features Section -->
    <section class="features" id="features">
        <h2 class="section-title">Why Choose FitPro?</h2>
        <p class="section-subtitle">We offer everything you need for your fitness transformation</p>
        
        <div class="features-grid">
            <div class="feature-card">
                <div class="feature-icon feature-1">
                    <i class="fas fa-calendar-alt"></i>
                </div>
                <h3>Flexible Booking</h3>
                <p>Book your training sessions anytime with our easy-to-use online booking system.</p>
            </div>
            
            <div class="feature-card">
                <div class="feature-icon feature-2">
                    <i class="fas fa-user-tie"></i>
                </div>
                <h3>Expert Trainers</h3>
                <p>Certified trainers to guide you through personalized workout programs.</p>
            </div>
            
            <div class="feature-card">
                <div class="feature-icon feature-3">
                    <i class="fas fa-chart-line"></i>
                </div>
                <h3>Progress Tracking</h3>
                <p>Monitor your fitness journey with detailed analytics and progress reports.</p>
            </div>
            
            <div class="feature-card">
                <div class="feature-icon feature-4">
                    <i class="fas fa-pills"></i>
                </div>
                <h3>Premium Supplements</h3>
                <p>High-quality supplements to support your training and nutrition goals.</p>
            </div>
            
            <div class="feature-card">
                <div class="feature-icon feature-5">
                    <i class="fas fa-mobile-alt"></i>
                </div>
                <h3>Mobile Friendly</h3>
                <p>Access your account and book sessions from anywhere with our mobile app.</p>
            </div>
            
            <div class="feature-card">
                <div class="feature-icon feature-6">
                    <i class="fas fa-heartbeat"></i>
                </div>
                <h3>Health Monitoring</h3>
                <p>Regular health checkups to ensure safe and effective training.</p>
            </div>
        </div>
    </section>

    <!-- Packages Section -->
    <section class="packages" id="packages">
        <h2 class="section-title">Our Membership Packages</h2>
        <p class="section-subtitle">Choose the perfect plan for your fitness journey</p>
        
        <div class="packages-grid">
            <?php
            $sql = "SELECT * FROM packages WHERE active = 1";
            $result = $conn->query($sql);
            
            if ($result->num_rows > 0) {
                while($row = $result->fetch_assoc()) {
                    echo '<div class="package-card">';
                    echo '<div class="package-header">';
                    echo '<h3>' . $row['name'] . '</h3>';
                    echo '<div class="package-price">Rs' . $row['price'] . '<span>/' . $row['duration'] . '</span></div>';
                    echo '</div>';
                    echo '<div class="package-features">';
                    $features = explode(',', $row['features']);
                    foreach ($features as $feature) {
                        echo '<p><i class="fas fa-check"></i> ' . trim($feature) . '</p>';
                    }
                    echo '</div>';
                    echo '<a href="register.php?package=' . $row['id'] . '" class="btn-package">Get Started</a>';
                    echo '</div>';
                }
            } else {
                echo '<p>No packages available at the moment.</p>';
            }
            ?>
        </div>
    </section>

    <!-- Supplements Section -->
    <section class="supplements" id="supplements">
        <h2 class="section-title">Premium Supplements</h2>
        <p class="section-subtitle">Fuel your performance with our high-quality supplements</p>
        
        <div class="supplements-grid">
            <?php
            $sql = "SELECT * FROM supplements WHERE stock > 0 LIMIT 6";
            $result = $conn->query($sql);
            
            if ($result->num_rows > 0) {
                while($row = $result->fetch_assoc()) {
                    echo '<div class="supplement-card">';
                    echo '<div class="supplement-image">';
                    echo '<img src="assets/images/supplements/' . $row['image'] . '" alt="' . $row['name'] . '">';
                    echo '</div>';
                    echo '<div class="supplement-info">';
                    echo '<h3>' . $row['name'] . '</h3>';
                    echo '<p class="supplement-category">' . $row['category'] . '</p>';
                    echo '<div class="supplement-price">Rs' . $row['price'] . '</div>';
                    echo '<div class="supplement-actions">';
                    echo '<button class="btn-supplement btn-view" data-id="' . $row['id'] . '">View Details</button>';
                    if (isLoggedIn()) {
                        echo '<button class="btn-supplement btn-cart" data-id="' . $row['id'] . '">Add to Cart</button>';
                    } else {
                        echo '<button class="btn-supplement btn-login-required">Login to Book</button>';
                    }
                    echo '</div>';
                    echo '</div>';
                    echo '</div>';
                }
            } else {
                echo '<p>No supplements available at the moment.</p>';
            }
            ?>
        </div>
        
        <div class="supplements-cta">
            <a href="supplements.php" class="btn-cta">View All Supplements <i class="fas fa-arrow-right"></i></a>
        </div>
    </section>

    

    <!-- Footer -->
    <footer>
        <div class="footer-bottom">
            <p>&copy; 2023 FitPro Gym Management System. All rights reserved.</p>
            <div class="footer-legal">
                <a href="#">Privacy Policy</a>
                <a href="#">Terms of Service</a>
                <a href="#">Cookie Policy</a>
            </div>
        </div>
        
    </footer>

    <script src="script.js"></script>
</body>
</html>