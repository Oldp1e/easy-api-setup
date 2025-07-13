<?php

use Src\Core\Router;

/**
 * ===========================================
 * GENERIC API TEMPLATE - ROUTES DEFINITION
 * ===========================================
 * 
 * This file defines all HTTP routes for your API.
 * Routes are grouped by functionality for better organization.
 * 
 * Route Parameters:
 * - {id} = numeric ID
 * - {slug} = alphanumeric slug
 * - Use descriptive parameter names for clarity
 */

// ===========================================
// AUTHENTICATION ROUTES
// ===========================================
// These routes handle user authentication and account management

$router->post('/auth/login', 'GenericAuthController@login');
$router->post('/auth/register', 'GenericAuthController@register');
$router->post('/auth/logout', 'GenericAuthController@logout');
$router->get('/auth/me', 'GenericAuthController@me');
$router->put('/auth/profile', 'GenericAuthController@updateProfile');
$router->post('/auth/request-reset', 'GenericAuthController@requestPasswordReset');
$router->post('/auth/reset-password', 'GenericAuthController@resetPassword');
$router->post('/auth/change-password', 'GenericAuthController@changePassword');

// ===========================================
// GENERIC CONTENT MANAGEMENT ROUTES
// ===========================================
// Generic routes for managing content in any domain

// Categories (Public read, Auth required for write)
$router->get('/categories', 'CategoryController@index');
$router->get('/categories/tree', 'CategoryController@tree');
$router->get('/categories/{id}', 'CategoryController@show');
$router->post('/categories', 'CategoryController@store');
$router->put('/categories/{id}', 'CategoryController@update');
$router->delete('/categories/{id}', 'CategoryController@destroy');

// Items/Content (Public read, Auth required for write)
$router->get('/items', 'ItemController@index');
$router->get('/items/{id}', 'ItemController@show');
$router->get('/items/{id}/related', 'ItemController@related');
$router->post('/items', 'ItemController@store');
$router->put('/items/{id}', 'ItemController@update');
$router->delete('/items/{id}', 'ItemController@destroy');
$router->post('/items/{id}/like', 'ItemController@like');
$router->post('/items/{id}/share', 'ItemController@share');

// Tags (Public read, Auth required for write)
$router->get('/tags', 'TagController@index');
$router->get('/tags/{id}', 'TagController@show');
$router->get('/tags/popular', 'TagController@popular');
$router->post('/tags', 'TagController@store');
$router->put('/tags/{id}', 'TagController@update');
$router->delete('/tags/{id}', 'TagController@destroy');

// Notifications (Auth required)
$router->get('/notifications', 'NotificationController@index');
$router->get('/notifications/{id}', 'NotificationController@show');
$router->put('/notifications/{id}/read', 'NotificationController@markAsRead');
$router->put('/notifications/read-all', 'NotificationController@markAllAsRead');
$router->delete('/notifications/{id}', 'NotificationController@destroy');

// User Management (Auth required)
$router->get('/users', 'UserController@index');
$router->get('/users/{id}', 'UserController@show');
$router->put('/users/{id}', 'UserController@update');
$router->delete('/users/{id}', 'UserController@destroy');
$router->put('/users/{id}/activate', 'UserController@activate');
$router->put('/users/{id}/deactivate', 'UserController@deactivate');

// ===========================================
// EXAMPLE DOMAIN ROUTES
// ===========================================
// Replace these with your actual domain-specific routes
// These are just examples to show the pattern

// Public routes (no authentication required)
// $router->get('/products', 'ProductController@getAll');
// $router->get('/products/{id}', 'ProductController@getById');
// $router->get('/categories', 'CategoryController@getAll');

// Protected routes (authentication required)
// $router->post('/products', 'ProductController@create');
// $router->put('/products/{id}', 'ProductController@update');
// $router->delete('/products/{id}', 'ProductController@delete');

// ===========================================
// ADMIN ROUTES (Higher permission level)
// ===========================================
// Routes that require special permissions
// Implement permission checking in your controllers

// $router->get('/admin/users', 'AdminController@getUsers');
// $router->put('/admin/users/{id}/permissions', 'AdminController@updatePermissions');
// $router->get('/admin/analytics', 'AdminController@getAnalytics');

// ===========================================
// UTILITY ROUTES
// ===========================================
// Useful routes for application management

// Health check
$router->get('/health', function() {
    http_response_code(200);
    echo json_encode([
        'status' => 'healthy',
        'timestamp' => date('Y-m-d H:i:s'),
        'version' => $_ENV['APP_VERSION'] ?? '1.0.0'
    ]);
    exit;
});

// API Info
$router->get('/info', function() {
    $config = \Src\Core\Config::getInstance();
    http_response_code(200);
    echo json_encode([
        'name' => $config->get('app.name'),
        'version' => $config->get('app.version'),
        'environment' => $config->get('app.env'),
        'endpoints' => [
            'documentation' => '/docs',
            'authentication' => '/auth/*',
            'health_check' => '/health'
        ]
    ]);
    exit;
});

// ===========================================
// CUSTOM ROUTES SECTION
// ===========================================
// Add your custom routes below this line

/* 
 * Example route structure:
 * 
 * // Resource routes (RESTful pattern)
 * $router->get('/resource', 'ResourceController@index');          // GET /resource - List all
 * $router->post('/resource', 'ResourceController@store');         // POST /resource - Create new
 * $router->get('/resource/{id}', 'ResourceController@show');      // GET /resource/1 - Show specific
 * $router->put('/resource/{id}', 'ResourceController@update');    // PUT /resource/1 - Update specific
 * $router->delete('/resource/{id}', 'ResourceController@destroy'); // DELETE /resource/1 - Delete specific
 * 
 * // Nested resource routes
 * $router->get('/users/{userId}/posts', 'PostController@getUserPosts');
 * $router->post('/users/{userId}/posts', 'PostController@createUserPost');
 * 
 * // Custom action routes
 * $router->post('/users/{id}/activate', 'UserController@activate');
 * $router->post('/posts/{id}/publish', 'PostController@publish');
 */

?>

<?php
/**
 * ===========================================
 * ROUTE ORGANIZATION TIPS
 * ===========================================
 * 
 * 1. Group related routes together
 * 2. Use consistent naming conventions
 * 3. Order routes from most specific to least specific
 * 4. Use descriptive parameter names
 * 5. Add comments to explain complex routes
 * 6. Consider using route prefixes for versions (e.g., /v1/users)
 * 
 * ===========================================
 * AUTHENTICATION REQUIREMENTS
 * ===========================================
 * 
 * The router automatically handles authentication based on:
 * - Public routes: Listed in Router.php $publicRoutes array
 * - Private GET routes: Listed in Router.php $privateGetRoutes array
 * - All PUT/DELETE routes require authentication by default
 * 
 * To make a GET route public, ensure it's NOT in the $privateGetRoutes array
 * To make a POST route public, add it to the $publicRoutes array
 * 
 * ===========================================
 * ROUTE PARAMETERS
 * ===========================================
 * 
 * Available parameter patterns:
 * - {id} - matches numbers and alphanumeric
 * - {slug} - matches alphanumeric with dashes
 * - {any} - matches any characters
 * 
 * Access parameters in controllers:
 * public function show($id) {
 *     // $id contains the route parameter
 * }
 */
?>