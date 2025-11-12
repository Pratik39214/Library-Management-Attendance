<?php
/**
 * Database Configuration File
 * Contains database connection settings and helper functions
 */

// Database configuration constants
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'library_system');

// Application settings
define('SITE_NAME', 'Library Management System');
define('BOOKS_PER_PAGE', 10);
define('ISSUE_DURATION_DAYS', 14); // Default book issue duration
define('FINE_PER_DAY', 5); // Fine amount per day for overdue books

// Session configuration
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Create database connection
 * @return mysqli Database connection object
 */
function getDBConnection() {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    
    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    
    // Set charset to utf8
    $conn->set_charset("utf8");
    
    return $conn;
}

/**
 * Close database connection
 * @param mysqli $conn Database connection object
 */
function closeDBConnection($conn) {
    if ($conn) {
        $conn->close();
    }
}

/**
 * Sanitize input data
 * @param string $data Input data to sanitize
 * @return string Sanitized data
 */
function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

/**
 * Check if user is logged in
 * @return bool True if logged in, false otherwise
 */
function isLoggedIn() {
    return isset($_SESSION['admin_id']) && !empty($_SESSION['admin_id']);
}

/**
 * Redirect to login page if not logged in
 */
function requireLogin() {
    if (!isLoggedIn()) {
        header("Location: login.php");
        exit();
    }
}

/**
 * Display success message
 * @param string $message Success message
 */
function showSuccess($message) {
    $_SESSION['success_message'] = $message;
}

/**
 * Display error message
 * @param string $message Error message
 */
function showError($message) {
    $_SESSION['error_message'] = $message;
}

/**
 * Get and clear success message
 * @return string Success message or empty string
 */
function getSuccessMessage() {
    if (isset($_SESSION['success_message'])) {
        $message = $_SESSION['success_message'];
        unset($_SESSION['success_message']);
        return $message;
    }
    return '';
}

/**
 * Get and clear error message
 * @return string Error message or empty string
 */
function getErrorMessage() {
    if (isset($_SESSION['error_message'])) {
        $message = $_SESSION['error_message'];
        unset($_SESSION['error_message']);
        return $message;
    }
    return '';
}

/**
 * Format date for display
 * @param string $date Date string
 * @return string Formatted date
 */
function formatDate($date) {
    if (empty($date) || $date == '0000-00-00') {
        return 'N/A';
    }
    return date('d M Y', strtotime($date));
}

/**
 * Calculate fine for overdue books
 * @param string $dueDate Due date
 * @return float Fine amount
 */
function calculateFine($dueDate) {
    $due = strtotime($dueDate);
    $today = strtotime(date('Y-m-d'));
    
    if ($today > $due) {
        $daysOverdue = floor(($today - $due) / (60 * 60 * 24));
        return $daysOverdue * FINE_PER_DAY;
    }
    
    return 0;
}
?>
