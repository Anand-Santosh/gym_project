<?php
// Prevent multiple inclusions
if (!defined('GYM_CONFIG_LOADED')) {
    define('GYM_CONFIG_LOADED', true);

    // Database configuration
    define('DB_HOST', 'localhost');
    define('DB_USER', 'root');
    define('DB_PASS', '');
    define('DB_NAME', 'Fitgym');

    // Create connection
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Start session
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    // Check if user is logged in
    if (!function_exists('isLoggedIn')) {
        function isLoggedIn() {
            return isset($_SESSION['user_id']);
        }
    }

    // Check if user is admin
    if (!function_exists('isAdmin')) {
        function isAdmin() {
            return isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'admin';
        }
    }

    // Check if user is trainer
    if (!function_exists('isTrainer')) {
        function isTrainer() {
            return isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'trainer';
        }
    }

    // Check if user is member
    if (!function_exists('isMember')) {
        function isMember() {
            return isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'member';
        }
    }

    // Redirect function
    if (!function_exists('redirect')) {
        function redirect($url) {
            header("Location: $url");
            exit();
        }
    }
}
?>