<?php
session_start();

// Database connection function
function db_connect() {
    require_once __DIR__ . '/../config/database.php';
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    return $conn;
}

// Sanitize input
function sanitize($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Translation function
function __($key) {
    $lang = $_SESSION['lang'] ?? 'en';
    include_once __DIR__ . "/../lang/{$lang}.php";
    return $translations[$key] ?? $key;
}

// Generate CSRF token
function generate_csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// Verify CSRF token
function verify_csrf_token($token) {
    if (!isset($_SESSION['csrf_token']) || $token !== $_SESSION['csrf_token']) {
        die('CSRF token validation failed');
    }
    return true;
}

// Format currency
function format_currency($amount) {
    return 'IDR ' . number_format($amount, 2, ',', '.');
}

// Check if user is logged in
function is_logged_in() {
    return isset($_SESSION['user_id']);
}

// Check if user is admin
function is_admin() {
    return isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1;
}

// Redirect with message
function redirect($url, $message = '', $type = 'success') {
    if ($message) {
        $_SESSION['message'] = $message;
        $_SESSION['message_type'] = $type;
    }
    header("Location: $url");
    exit();
}

// Get active announcement
function get_active_announcement() {
    $conn = db_connect();
    $sql = "SELECT message FROM announcements 
            WHERE start_date <= CURDATE() 
            AND end_date >= CURDATE() 
            ORDER BY id DESC LIMIT 1";
    $result = $conn->query($sql);
    $conn->close();
    return $result->fetch_assoc()['message'] ?? null;
}

// Get user balance
function get_user_balance($user_id) {
    $conn = db_connect();
    $stmt = $conn->prepare("SELECT balance FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $conn->close();
    return $result->fetch_assoc()['balance'] ?? 0;
}

// Validate promo code
function validate_promo_code($code) {
    $conn = db_connect();
    $stmt = $conn->prepare("
        SELECT * FROM promo_codes 
        WHERE code = ? 
        AND expiry_date >= CURDATE()
        AND (usage_limit = 0 OR usage_count < usage_limit)
    ");
    $stmt->bind_param("s", $code);
    $stmt->execute();
    $result = $stmt->get_result();
    $conn->close();
    return $result->fetch_assoc();
}

// Send email
function send_email($to, $subject, $message) {
    // TODO: Implement email sending functionality
    // For now, just return true
    return true;
}
?>
