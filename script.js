// Mobile menu toggle
document.addEventListener('DOMContentLoaded', function() {
    // Mobile menu toggle
    const mobileMenuBtn = document.getElementById('mobile-menu-btn');
    const mobileMenu = document.getElementById('mobile-menu');
    
    if (mobileMenuBtn && mobileMenu) {
        mobileMenuBtn.addEventListener('click', function() {
            mobileMenu.classList.toggle('hidden');
        });
    }
    
    // Smooth scrolling for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            e.preventDefault();
            
            const targetId = this.getAttribute('href');
            if (targetId === '#') return;
            
            const targetElement = document.querySelector(targetId);
            if (targetElement) {
                window.scrollTo({
                    top: targetElement.offsetTop - 80,
                    behavior: 'smooth'
                });
            }
        });
    });
    
    // Supplement modal
    const supplementModal = document.getElementById('supplement-modal');
    const supplementModalContent = document.getElementById('supplement-modal-content');
    const closeModalBtn = document.getElementById('close-modal');
    const supplementViewBtns = document.querySelectorAll('.btn-view');
    
    if (supplementModal) {
        // Open modal
        supplementViewBtns.forEach(btn => {
            btn.addEventListener('click', function() {
                const supplementId = this.getAttribute('data-id');
                fetchSupplementDetails(supplementId);
            });
        });
        
        // Close modal
        closeModalBtn.addEventListener('click', function() {
            supplementModal.classList.add('hidden');
        });
        
        // Close when clicking outside
        supplementModal.addEventListener('click', function(e) {
            if (e.target === supplementModal) {
                supplementModal.classList.add('hidden');
            }
        });
    }
    
    // Add to cart
    const addToCartBtns = document.querySelectorAll('.btn-cart');
    addToCartBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const supplementId = this.getAttribute('data-id');
            addToCart(supplementId);
        });
    });
    
    // Login required buttons
    const loginRequiredBtns = document.querySelectorAll('.btn-login-required');
    loginRequiredBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            window.location.href = 'login.php?redirect=' + encodeURIComponent(window.location.pathname);
        });
    });
});

// Fetch supplement details for modal
function fetchSupplementDetails(id) {
    fetch(`get_supplement.php?id=${id}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const supplement = data.supplement;
                const modalContent = document.getElementById('supplement-modal-content');
                
                modalContent.innerHTML = `
                    <div class="modal-header">
                        <h2>${supplement.name}</h2>
                        <button id="close-modal">&times;</button>
                    </div>
                    <div class="modal-body">
                        <div class="supplement-modal-image">
                            <img src="assets/images/supplements/${supplement.image}" alt="${supplement.name}">
                        </div>
                        <div class="supplement-modal-info">
                            <p class="supplement-category">${supplement.category}</p>
                            <p class="supplement-description">${supplement.description}</p>
                            <div class="supplement-details">
                                <p><strong>Price:</strong> $${supplement.price}</p>
                                <p><strong>Stock:</strong> ${supplement.stock} available</p>
                                <p><strong>Ingredients:</strong> ${supplement.ingredients}</p>
                                <p><strong>Benefits:</strong> ${supplement.benefits}</p>
                            </div>
                            <div class="supplement-modal-actions">
                                <button class="btn-supplement btn-cart" data-id="${supplement.id}">Add to Cart</button>
                            </div>
                        </div>
                    </div>
                `;
                
                document.getElementById('supplement-modal').classList.remove('hidden');
                
                // Re-attach event listeners
                document.getElementById('close-modal').addEventListener('click', function() {
                    document.getElementById('supplement-modal').classList.add('hidden');
                });
                
                document.querySelector('.btn-cart').addEventListener('click', function() {
                    addToCart(supplement.id);
                });
            } else {
                alert('Error loading supplement details.');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error loading supplement details.');
        });
}

// Add item to cart
function addToCart(supplementId) {
    fetch('add_to_cart.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `supplement_id=${supplementId}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Item added to cart successfully!');
            updateCartCount(data.cartCount);
        } else {
            alert(data.message || 'Error adding item to cart.');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error adding item to cart.');
    });
}

// Update cart count in navbar
function updateCartCount(count) {
    const cartCountElements = document.querySelectorAll('.cart-count');
    cartCountElements.forEach(element => {
        element.textContent = count;
    });
}

// Form validation
function validateForm(form) {
    let isValid = true;
    const inputs = form.querySelectorAll('input[required], textarea[required]');
    
    inputs.forEach(input => {
        if (!input.value.trim()) {
            input.classList.add('error');
            isValid = false;
        } else {
            input.classList.remove('error');
        }
    });
    
    return isValid;
}

// Initialize any forms
document.querySelectorAll('form').forEach(form => {
    form.addEventListener('submit', function(e) {
        if (!validateForm(this)) {
            e.preventDefault();
            alert('Please fill in all required fields.');
        }
    });
});