<?php 
include 'config.php';

// Check if user is logged in
if (!isLoggedIn()) {
    redirect('login.php');
}

// Handle add to cart
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_to_cart'])) {
    $supplementId = $_POST['supplement_id'];
    $userId = $_SESSION['user_id'];
    
    // Check if item already in cart
    $check = $conn->prepare("SELECT * FROM cart WHERE user_id = ? AND supplement_id = ?");
    $check->bind_param("ii", $userId, $supplementId);
    $check->execute();
    $result = $check->get_result();
    
    if ($result->num_rows > 0) {
        // Update quantity
        $stmt = $conn->prepare("UPDATE cart SET quantity = quantity + 1 WHERE user_id = ? AND supplement_id = ?");
    } else {
        // Add new item
        $stmt = $conn->prepare("INSERT INTO cart (user_id, supplement_id, quantity) VALUES (?, ?, 1)");
    }
    
    $stmt->bind_param("ii", $userId, $supplementId);
    
    if ($stmt->execute()) {
        $_SESSION['message'] = "Item added to cart successfully";
        $_SESSION['message_type'] = "success";
    } else {
        $_SESSION['message'] = "Error adding item to cart";
        $_SESSION['message_type'] = "danger";
    }
    
    $stmt->close();
    redirect('supplements.php');
}

// Get all supplements
$supplements = $conn->query("SELECT * FROM supplements WHERE stock > 0 ORDER BY created_at DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Supplement Store | FitPro</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    <style>
        /* Supplement Store Specific Styles */
        .supplements-store {
            background-color: white;
            border-radius: 10px;
            padding: 2rem;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
        }
        
        .store-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
        }
        
        .store-header h2 {
            font-size: 1.5rem;
        }
        
        .store-actions {
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
        
        .btn-cart {
            background-color: var(--primary);
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 5px;
            display: flex;
            align-items: center;
            position: relative;
        }
        
        .btn-cart i {
            margin-right: 0.5rem;
        }
        
        .cart-count {
            position: absolute;
            top: -8px;
            right: -8px;
            background-color: var(--danger);
            color: white;
            width: 20px;
            height: 20px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.7rem;
            font-weight: bold;
        }
        
        .supplements-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 2rem;
        }
        
        .supplement-card {
            background-color: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            transition: all 0.3s ease;
            border: 1px solid #eee;
        }
        
        .supplement-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        }
        
        .supplement-image {
            height: 200px;
            overflow: hidden;
            position: relative;
        }
        
        .supplement-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s ease;
        }
        
        .supplement-card:hover .supplement-image img {
            transform: scale(1.1);
        }
        
        .supplement-badge {
            position: absolute;
            top: 10px;
            right: 10px;
            background-color: var(--danger);
            color: white;
            padding: 0.3rem 0.8rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 500;
        }
        
        .supplement-info {
            padding: 1.5rem;
        }
        
        .supplement-info h3 {
            font-size: 1.2rem;
            margin-bottom: 0.5rem;
        }
        
        .supplement-category {
            color: var(--gray);
            font-size: 0.9rem;
            margin-bottom: 1rem;
        }
        
        .supplement-price {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--primary);
            margin-bottom: 1.5rem;
        }
        
        .supplement-stock {
            font-size: 0.9rem;
            color: var(--gray);
            margin-bottom: 1.5rem;
        }
        
        .supplement-actions {
            display: flex;
            justify-content: space-between;
        }
        
        .btn-view {
            background-color: var(--primary);
            color: white;
        }
        
        .btn-view:hover {
            background-color: var(--secondary);
        }
        
        .btn-add-cart {
            background-color: var(--success);
            color: white;
        }
        
        .btn-add-cart:hover {
            background-color: #3aa8d8;
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
        
        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 1001;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0, 0, 0, 0.5);
        }
        
        .modal-content {
            background-color: white;
            margin: 5% auto;
            padding: 2rem;
            border-radius: 10px;
            width: 80%;
            max-width: 800px;
            box-shadow: 0 5px 30px rgba(0, 0, 0, 0.3);
            animation: modalopen 0.3s;
        }
        
        @keyframes modalopen {
            from { opacity: 0; transform: translateY(-50px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid #eee;
        }
        
        .modal-header h3 {
            font-size: 1.5rem;
        }
        
        .close-modal {
            font-size: 1.8rem;
            font-weight: bold;
            color: #6c757d;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .close-modal:hover {
            color: var(--danger);
        }
        
        .modal-body {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 2rem;
        }
        
        .supplement-modal-image {
            height: 300px;
            overflow: hidden;
            border-radius: 10px;
        }
        
        .supplement-modal-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .supplement-modal-info {
            display: flex;
            flex-direction: column;
        }
        
        .supplement-modal-price {
            font-size: 2rem;
            font-weight: 700;
            color: var(--primary);
            margin: 1rem 0;
        }
        
        .supplement-modal-stock {
            color: var(--gray);
            margin-bottom: 1rem;
        }
        
        .supplement-modal-description {
            margin-bottom: 1.5rem;
            line-height: 1.6;
        }
        
        .supplement-details {
            margin-bottom: 1.5rem;
        }
        
        .supplement-details p {
            margin-bottom: 0.5rem;
        }
        
        .supplement-modal-actions {
            margin-top: auto;
            display: flex;
            gap: 1rem;
        }
        
        .quantity-selector {
            display: flex;
            align-items: center;
            margin-bottom: 1rem;
        }
        
        .quantity-selector button {
            width: 30px;
            height: 30px;
            border: 1px solid #ddd;
            background-color: #f8f9fa;
            font-size: 1rem;
            cursor: pointer;
        }
        
        .quantity-selector input {
            width: 50px;
            height: 30px;
            text-align: center;
            border-top: 1px solid #ddd;
            border-bottom: 1px solid #ddd;
            border-left: none;
            border-right: none;
        }
        
        /* Responsive */
        @media (max-width: 992px) {
            .modal-body {
                grid-template-columns: 1fr;
            }
            
            .supplement-modal-image {
                height: 200px;
            }
        }
        
        @media (max-width: 768px) {
            .store-header {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .store-actions {
                width: 100%;
                margin-top: 1rem;
                justify-content: space-between;
            }
            
            .search-box input {
                width: 180px;
            }
            
            .modal-content {
                width: 95%;
                margin: 10% auto;
            }
        }
    </style>
</head>
<body>
    <?php 
    if (isAdmin()) {
        include 'dashboard.php';
    } else {
        include 'member_dashboard.php';
    }
    ?>
    
    <div class="main-content">
        <div class="dashboard-header">
            <div class="dashboard-title">
                <h1>Supplement Store</h1>
                <p>Browse and purchase high-quality supplements</p>
            </div>
            <div class="user-menu">
                <img src="assets/images/members/<?php echo $_SESSION['user_avatar'] ? $_SESSION['user_avatar'] : 'default.jpg'; ?>" alt="User Avatar">
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
        
        <div class="supplements-store">
            <div class="store-header">
                <h2>All Supplements</h2>
                <div class="store-actions">
                    <div class="search-box">
                        <i class="fas fa-search"></i>
                        <input type="text" placeholder="Search supplements...">
                    </div>
                    <a href="cart.php" class="btn-cart">
                        <i class="fas fa-shopping-cart"></i> Cart
                        <span class="cart-count">
                            <?php 
                            $cartCount = $conn->query("SELECT COUNT(*) as count FROM cart WHERE user_id = {$_SESSION['user_id']}")->fetch_assoc()['count'];
                            echo $cartCount;
                            ?>
                        </span>
                    </a>
                </div>
            </div>
            
            <div class="supplements-grid">
                <?php while($supplement = $supplements->fetch_assoc()): ?>
                <div class="supplement-card">
                    <div class="supplement-image">
                        <img src="assets/images/supplements/<?php echo $supplement['image'] ? $supplement['image'] : 'default.jpg'; ?>" alt="<?php echo $supplement['name']; ?>">
                        <?php if ($supplement['stock'] < 10): ?>
                        <span class="supplement-badge">Low Stock</span>
                        <?php endif; ?>
                    </div>
                    <div class="supplement-info">
                        <h3><?php echo $supplement['name']; ?></h3>
                        <p class="supplement-category"><?php echo $supplement['category']; ?></p>
                        <div class="supplement-price">$<?php echo $supplement['price']; ?></div>
                        <p class="supplement-stock"><?php echo $supplement['stock']; ?> in stock</p>
                        <div class="supplement-actions">
                            <button class="btn btn-view view-supplement" data-id="<?php echo $supplement['id']; ?>">View</button>
                            <form method="POST" style="display: inline;">
                                <input type="hidden" name="supplement_id" value="<?php echo $supplement['id']; ?>">
                                <button type="submit" name="add_to_cart" class="btn btn-add-cart">Add to Cart</button>
                            </form>
                        </div>
                    </div>
                </div>
                <?php endwhile; ?>
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
    
    <!-- Supplement Modal -->
    <div id="supplement-modal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 id="modal-supplement-name"></h3>
                <span class="close-modal">&times;</span>
            </div>
            <div class="modal-body">
                <div class="supplement-modal-image">
                    <img id="modal-supplement-image" src="" alt="">
                </div>
                <div class="supplement-modal-info">
                    <p class="supplement-category" id="modal-supplement-category"></p>
                    <div class="supplement-modal-price" id="modal-supplement-price"></div>
                    <p class="supplement-modal-stock" id="modal-supplement-stock"></p>
                    <p class="supplement-modal-description" id="modal-supplement-description"></p>
                    
                    <div class="supplement-details">
                        <p><strong>Ingredients:</strong> <span id="modal-supplement-ingredients"></span></p>
                        <p><strong>Benefits:</strong> <span id="modal-supplement-benefits"></span></p>
                        <p><strong>Usage:</strong> <span id="modal-supplement-usage"></span></p>
                    </div>
                    
                    <form method="POST" class="supplement-modal-actions">
                        <input type="hidden" name="supplement_id" id="modal-supplement-id">
                        <div class="quantity-selector">
                            <button type="button" id="decrease-qty">-</button>
                            <input type="number" name="quantity" id="quantity" value="1" min="1" max="10">
                            <button type="button" id="increase-qty">+</button>
                        </div>
                        <button type="submit" name="add_to_cart" class="btn btn-add-cart">Add to Cart</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        // Supplement Modal
        const supplementModal = document.getElementById('supplement-modal');
        const viewSupplementBtns = document.querySelectorAll('.view-supplement');
        const closeModalBtn = document.querySelector('.close-modal');
        
        // View supplement details
        viewSupplementBtns.forEach(btn => {
            btn.addEventListener('click', function() {
                const supplementId = this.getAttribute('data-id');
                fetchSupplementDetails(supplementId);
            });
        });
        
        // Close modal
        closeModalBtn.addEventListener('click', function() {
            supplementModal.style.display = 'none';
        });
        
        // Close when clicking outside
        window.addEventListener('click', function(e) {
            if (e.target === supplementModal) {
                supplementModal.style.display = 'none';
            }
        });
        
        // Quantity selector
        const decreaseQty = document.getElementById('decrease-qty');
        const increaseQty = document.getElementById('increase-qty');
        const quantityInput = document.getElementById('quantity');
        
        if (decreaseQty && increaseQty && quantityInput) {
            decreaseQty.addEventListener('click', function() {
                let value = parseInt(quantityInput.value);
                if (value > 1) {
                    quantityInput.value = value - 1;
                }
            });
            
            increaseQty.addEventListener('click', function() {
                let value = parseInt(quantityInput.value);
                if (value < 10) {
                    quantityInput.value = value + 1;
                }
            });
        }
        
        // Fetch supplement details
        function fetchSupplementDetails(id) {
            fetch(`get_supplement.php?id=${id}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const supplement = data.supplement;
                        
                        // Populate modal
                        document.getElementById('modal-supplement-name').textContent = supplement.name;
                        document.getElementById('modal-supplement-image').src = `assets/images/supplements/${supplement.image}`;
                        document.getElementById('modal-supplement-image').alt = supplement.name;
                        document.getElementById('modal-supplement-category').textContent = supplement.category;
                        document.getElementById('modal-supplement-price').textContent = `$${supplement.price}`;
                        document.getElementById('modal-supplement-stock').textContent = `${supplement.stock} in stock`;
                        document.getElementById('modal-supplement-description').textContent = supplement.description;
                        document.getElementById('modal-supplement-ingredients').textContent = supplement.ingredients;
                        document.getElementById('modal-supplement-benefits').textContent = supplement.benefits;
                        document.getElementById('modal-supplement-usage').textContent = supplement.usage;
                        document.getElementById('modal-supplement-id').value = supplement.id;
                        
                        // Set max quantity
                        document.getElementById('quantity').max = supplement.stock > 10 ? 10 : supplement.stock;
                        
                        // Show modal
                        supplementModal.style.display = 'block';
                    } else {
                        alert('Error loading supplement details.');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error loading supplement details.');
                });
        }
    </script>
</body>
</html>