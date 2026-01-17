<?php
/**
 * Environment Configuration
 * 
 * Set the application environment (development, staging, production)
 * This should be set in your server environment or .htaccess
 */

// Default to development if not set
if (!defined('ENVIRONMENT')) {
    // Try to get from Apache environment variable first
    $env = getenv('ENVIRONMENT');
    if ($env === false && isset($_SERVER['ENVIRONMENT'])) {
        $env = $_SERVER['ENVIRONMENT'];
    }
    define('ENVIRONMENT', $env !== false ? $env : 'development');
}

// Error reporting based on environment
if (ENVIRONMENT === 'development') {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED & ~E_STRICT);
    ini_set('display_errors', 0);
    ini_set('log_errors', 1);
}

// Security settings based on environment
if (ENVIRONMENT === 'production') {
    ini_set('session.cookie_httponly', 1);
    ini_set('session.cookie_secure', 1);
    ini_set('session.use_strict_mode', 1);
}
