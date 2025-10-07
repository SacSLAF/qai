<?php
// Database Configuration

define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', 'Sac@123');
define('DB_NAME', 'cqai-j');

// Application Settings
define('SITE_NAME', 'Quality Inspectorate');
// define('SITE_URL', 'http://web/qai');
define('SITE_URL', 'http://localhost/qai');
// define('UPLOAD_DIR', __DIR__ . '../assets/uploads/');
define('UPLOAD_DIR', dirname(__DIR__) . '/assets/uploads/');
define('MAX_FILE_SIZE', 10 * 1024 * 1024); // 10MB
define('ALLOWED_FILE_TYPES', [
    'application/pdf',
    'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
    'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
]);

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start([
        'cookie_secure' => true, // Enable in production with HTTPS
        'cookie_httponly' => true,
        'use_strict_mode' => true
    ]);
}

// Database Connection
try {
    $db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    
    if ($db->connect_error) {
        throw new Exception("Database connection failed: " . $db->connect_error);
    }
    
    $db->set_charset("utf8mb4");
} catch (Exception $e) {
    error_log($e->getMessage());
    die("We're experiencing technical difficulties. Please try again later.");
}

// Error Reporting Configuration
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../error.log');

// Security Headers
header("X-Frame-Options: DENY");
header("X-Content-Type-Options: nosniff");
header("X-XSS-Protection: 1; mode=block");
header("Referrer-Policy: strict-origin-when-cross-origin");
// header("Content-Security-Policy: default-src 'self'; script-src 'self' https://docs.google.com; style-src 'self' 'unsafe-inline'; img-src 'self' data:;");
// Security Headers - Development version (less restrictive)
header("Content-Security-Policy: default-src 'self'; 
    script-src 'self' https://cdn.jsdelivr.net https://docs.google.com; 
    style-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com; 
    img-src 'self' data: https:;
    font-src 'self' https://cdnjs.cloudflare.com;
    frame-src https://docs.google.com");
?>