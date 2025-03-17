<?php
/**
 * Main Configuration File
 */

// Define website info
define('SITE_NAME', 'NewsHub');
define('SITE_URL', 'http://localhost:8000'); // Corrected URL syntax

// Database configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'news_website');
define('DB_USER', 'root'); // Update with your database username
define('DB_PASS', ''); // Update with your database password

// Default settings
define('DEFAULT_LANG', 'en');
define('DEFAULT_TIMEZONE', 'UTC'); // Update with your timezone
date_default_timezone_set(DEFAULT_TIMEZONE);

// Upload directories
define('UPLOAD_DIR', 'uploads/');
define('NEWS_IMAGES_DIR', UPLOAD_DIR . 'news-images/');
define('CATEGORY_ICONS_DIR', UPLOAD_DIR . 'category-icons/');
define('USER_AVATARS_DIR', UPLOAD_DIR . 'user-avatars/');
define('EBOOKS_DIR', UPLOAD_DIR . 'ebooks/');

// Authentication settings
define('SESSION_LIFETIME', 3600); // 1 hour in seconds
define('REMEMBER_ME_LIFETIME', 86400 * 30); // 30 days in seconds

// Set error reporting
if ($_SERVER['SERVER_NAME'] === 'localhost' || $_SERVER['SERVER_ADDR'] === '127.0.0.1') {
    // Development environment
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
} else {
    // Production environment
    ini_set('display_errors', 0);
    error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED & ~E_STRICT);
}

// Include functions
require_once 'database.php';