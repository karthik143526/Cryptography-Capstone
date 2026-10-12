<?php
/**
 * Database Configuration File
 * This file should be placed outside the web root in production
 */

// Database Configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'crypto');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');
define('DB_PORT', 3306);

// Application Configuration
define('APP_NAME', 'Secure File Storage');
define('APP_VERSION', '1.0.0');
define('APP_ENV', 'development'); // Change to 'production' in live environment

// Security Configuration
define('SESSION_TIMEOUT', 3600); // 1 hour in seconds
define('SESSION_SECURE', false); // Set to true in production with HTTPS
define('SESSION_HTTPONLY', true); // Prevent JavaScript access to session cookie
define('SESSION_SAMESITE', 'Lax'); // CSRF protection

// File Upload Configuration
define('UPLOAD_MAX_SIZE', 50 * 1024 * 1024); // 50MB
define('UPLOAD_DIR', __DIR__ . '/../uploads/');
define('ALLOWED_EXTENSIONS', ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'txt', 'jpg', 'jpeg', 'png', 'gif', 'zip']);

// Encryption Configuration
define('ENCRYPTION_METHOD', 'AES-256-CBC');
define('ENCRYPTION_KEY_FILE', __DIR__ . '/../.env.key'); // Should be outside web root

// Pagination
define('ITEMS_PER_PAGE', 20);

// Email Configuration (for notifications)
define('MAIL_FROM', 'no-reply@securefilestorage.local');
define('MAIL_FROM_NAME', 'Secure File Storage');
define('MAIL_HOST', 'localhost'); // Use your SMTP server

// Error Reporting
if (APP_ENV === 'production') {
    error_reporting(E_ALL);
    ini_set('display_errors', '0');
    ini_set('log_errors', '1');
} else {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
}

// Log directory
define('LOG_DIR', __DIR__ . '/../logs/');

// Ensure log directory exists
if (!is_dir(LOG_DIR)) {
    mkdir(LOG_DIR, 0755, true);
}
?>
