-- Create database
CREATE DATABASE IF NOT EXISTS Fitgym;
USE Fitgym;

-- Users table
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    phone VARCHAR(20),
    dob DATE,
    gender ENUM('male', 'female', 'other'),
    avatar VARCHAR(255),
    address TEXT,
    type ENUM('admin', 'trainer', 'member') NOT NULL,
    active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Trainer details table
CREATE TABLE trainer_details (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    specialty VARCHAR(100),
    certification TEXT,
    experience INT COMMENT 'Years of experience',
    bio TEXT,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Packages table
CREATE TABLE packages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    price DECIMAL(10,2) NOT NULL,
    duration VARCHAR(50) NOT NULL COMMENT 'e.g., 1 month, 3 months',
    features TEXT COMMENT 'Comma-separated list of features',
    image VARCHAR(255),
    active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Bookings table
CREATE TABLE bookings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    trainer_id INT,
    package_id INT NOT NULL,
    booking_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    session_date DATE NOT NULL,
    session_time TIME NOT NULL,
    status ENUM('pending', 'confirmed', 'completed', 'cancelled') DEFAULT 'pending',
    notes TEXT,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (trainer_id) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (package_id) REFERENCES packages(id) ON DELETE CASCADE
);

-- Supplements table
CREATE TABLE supplements (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    category VARCHAR(50) NOT NULL COMMENT 'e.g., Protein, Pre-workout',
    ingredients TEXT,
    benefits TEXT,
    `usage` TEXT,
    price DECIMAL(10,2) NOT NULL,
    stock INT NOT NULL DEFAULT 0,
    image VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
-- Cart table
CREATE TABLE cart (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    supplement_id INT NOT NULL,
    quantity INT NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (supplement_id) REFERENCES supplements(id) ON DELETE CASCADE,
    UNIQUE KEY (user_id, supplement_id)
);

-- Orders table
CREATE TABLE orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    order_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    total_amount DECIMAL(10,2) NOT NULL,
    status ENUM('pending', 'processing', 'shipped', 'delivered', 'cancelled') DEFAULT 'pending',
    payment_method VARCHAR(50),
    shipping_address TEXT,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Order items table
CREATE TABLE order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    supplement_id INT NOT NULL,
    quantity INT NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (supplement_id) REFERENCES supplements(id) ON DELETE CASCADE
);

-- Payments table
CREATE TABLE payments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    booking_id INT,
    order_id INT,
    amount DECIMAL(10,2) NOT NULL,
    payment_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    payment_method VARCHAR(50) NOT NULL,
    transaction_id VARCHAR(100),
    status ENUM('pending', 'completed', 'failed', 'refunded') DEFAULT 'pending',
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE SET NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE SET NULL
);

-- Training plans table
CREATE TABLE training_plans (
    id INT AUTO_INCREMENT PRIMARY KEY,
    member_id INT NOT NULL,
    trainer_id INT NOT NULL,
    title VARCHAR(100) NOT NULL,
    goal TEXT,
    start_date DATE,
    end_date DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (member_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (trainer_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Plan exercises table
CREATE TABLE plan_exercises (
    id INT AUTO_INCREMENT PRIMARY KEY,
    plan_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    sets INT,
    reps INT,
    duration VARCHAR(50) COMMENT 'e.g., 30 seconds, 1 minute',
    day ENUM('monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'),
    FOREIGN KEY (plan_id) REFERENCES training_plans(id) ON DELETE CASCADE
);

-- Progress tracking table
CREATE TABLE progress (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    trainer_id INT,
    date_recorded DATE NOT NULL,
    weight DECIMAL(5,2) COMMENT 'in kg',
    height DECIMAL(5,2) COMMENT 'in cm',
    body_fat DECIMAL(5,2) COMMENT 'percentage',
    muscle_mass DECIMAL(5,2) COMMENT 'in kg',
    chest DECIMAL(5,2) COMMENT 'in cm',
    waist DECIMAL(5,2) COMMENT 'in cm',
    arms DECIMAL(5,2) COMMENT 'in cm',
    thighs DECIMAL(5,2) COMMENT 'in cm',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (trainer_id) REFERENCES users(id) ON DELETE SET NULL
);

-- Reviews table
CREATE TABLE reviews (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    rating INT NOT NULL CHECK (rating BETWEEN 1 AND 5),
    comment TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Insert sample admin user
INSERT INTO users (name, email, password, type) 
VALUES ('Admin User', 'admin@fitpro.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');

-- Insert sample packages
INSERT INTO packages (name, description, price, duration, features, active) VALUES
('Basic Membership', 'Access to gym facilities during standard hours', 29.99, '1 month', 'Gym access, Locker room, Standard equipment', TRUE),
('Premium Membership', 'Full access to all facilities including premium areas', 59.99, '1 month', 'Gym access, Premium equipment, Sauna, Pool, Classes', TRUE),
('Personal Training', 'One-on-one sessions with certified trainers', 199.99, '1 month', '4 sessions/month, Personalized program, Progress tracking', TRUE);

-- Insert sample supplements
INSERT INTO supplements 
(name, description, category, ingredients, benefits, `usage`, price, stock, image) 
VALUES
('Whey Protein', 'High-quality whey protein for muscle recovery', 'Protein', 
 'Whey protein concentrate, natural flavors, stevia', 
 'Muscle growth, Recovery, Strength', 
 'Mix 1 scoop with water or milk after workout', 
 29.99, 50, 'whey-protein.jpg'),

('Pre-Workout', 'Energy booster for intense workouts', 'Pre-Workout', 
 'Caffeine, Beta-Alanine, Creatine, BCAAs', 
 'Energy, Focus, Endurance', 
 'Take 1 scoop 30 minutes before workout', 
 24.99, 30, 'pre-workout.jpg'),

('Multivitamin', 'Complete daily vitamin complex', 'Vitamins', 
 'Vitamin A, B, C, D, E, Minerals', 
 'Overall health, Immunity, Energy', 
 'Take 1 tablet daily with meal', 
 19.99, 100, 'multivitamin.jpg');