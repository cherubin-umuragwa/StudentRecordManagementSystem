<?php
/**
 * Application Configuration
 * Central configuration file for the application
 */

// Session configuration (must be set BEFORE session_start)
$sessionLifetime = getenv('SESSION_LIFETIME') ?: 3600;
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.gc_maxlifetime', $sessionLifetime);
    ini_set('session.cookie_lifetime', $sessionLifetime);
    session_start();
}

// Load database connection
require_once __DIR__ . '/../config/database.php';

// Application constants
define('APP_NAME', getenv('APP_NAME') ?: 'Student Grade Management System');
define('APP_URL', getenv('APP_URL') ?: 'http://localhost');
define('UPLOAD_MAX_SIZE', getenv('UPLOAD_MAX_SIZE') ?: 5242880); // 5MB default
define('ALLOWED_EXTENSIONS', explode(',', getenv('ALLOWED_EXTENSIONS') ?: 'jpg,jpeg,png,pdf'));

// Upload directories
define('UPLOAD_DIR', dirname(__DIR__) . '/uploads/');
define('UPLOAD_DOCUMENTS', UPLOAD_DIR . 'documents/');
define('UPLOAD_PHOTOS', UPLOAD_DIR . 'photos/');

// Create upload directories if they don't exist
if (!file_exists(UPLOAD_DOCUMENTS)) {
    mkdir(UPLOAD_DOCUMENTS, 0755, true);
}
if (!file_exists(UPLOAD_PHOTOS)) {
    mkdir(UPLOAD_PHOTOS, 0755, true);
}
?>
