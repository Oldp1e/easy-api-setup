<?php

/**
 * ===========================================
 * GENERIC API TEMPLATE - APPLICATION BOOTSTRAP
 * ===========================================
 * 
 * This file bootstraps the application and handles all HTTP requests.
 * It loads dependencies, configures the environment, and dispatches routes.
 */

// Load Composer autoloader
require_once __DIR__ . '/../vendor/autoload.php';

// Import required classes
use Src\Core\Router;
use Src\Core\Cors;
use Src\Core\Config;

// Load environment variables
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

// Initialize configuration
$config = Config::getInstance();

// Initialize router
$router = new Router();

// Configure CORS
$corsOrigins = $config->get('cors.allowed_origins');
$corsOrigin = is_array($corsOrigins) ? $corsOrigins[0] : $corsOrigins;

Cors::initCors([
    "origin" => $corsOrigin,
    "methods" => implode(", ", $config->get('cors.allowed_methods')),
    "headers.allow" => implode(", ", $config->get('cors.allowed_headers')),
    "headers.expose" => implode(", ", $config->get('cors.exposed_headers')),
    "credentials" => $config->get('cors.credentials'),
    "cache" => $config->get('cors.max_age'),
]);

// Set error reporting based on environment
if ($config->get('app.debug')) {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
} else {
    error_reporting(0);
    ini_set('display_errors', '0');
}

// Set timezone
date_default_timezone_set($config->get('app.timezone', 'UTC'));

// Load routes
require_once __DIR__ . '/../src/Routes/web.php';

// Handle request
$router->dispatch();

?>