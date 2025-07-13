# 🛠️ Generic API Template - Customization Guide

This guide will help you customize this template for your specific use case.

## 📋 Table of Contents

1. [Quick Setup](#quick-setup)
2. [Customizing for Your Domain](#customizing-for-your-domain)
3. [Database Design](#database-design)
4. [Creating Controllers](#creating-controllers)
5. [Adding Business Logic](#adding-business-logic)
6. [Authentication & Permissions](#authentication--permissions)
7. [File Uploads](#file-uploads)
8. [Email Integration](#email-integration)
9. [Testing](#testing)
10. [Deployment](#deployment)

## 🚀 Quick Setup

### 1. Clone and Install
```bash
git clone <template-url> my-api-project
cd my-api-project
composer install
```

### 2. Environment Configuration
```bash
cp .env.example .env
# Edit .env with your settings
```

### 3. Database Setup
```bash
composer migrate
composer db:seed
```

### 4. Test the Template
```bash
composer start
curl http://localhost:8000/health
```

## 🎯 Customizing for Your Domain

### Step 1: Update Application Info

Edit `.env`:
```env
APP_NAME="My Awesome API"
APP_VERSION="1.0.0"
APP_URL=https://my-api.com
```

Edit `composer.json`:
```json
{
  "name": "mycompany/my-api",
  "description": "My awesome API description",
  "authors": [
    {
      "name": "Your Name",
      "email": "your.email@example.com"
    }
  ]
}
```

### Step 2: Define Your Data Models

Think about your domain entities. For example, if you're building an e-commerce API:
- Products
- Categories
- Orders
- Customers
- Inventory

### Step 3: Create Database Migrations

```bash
# Create migration for each entity
composer create CreateProductsTable
composer create CreateCategoriesTable
composer create CreateOrdersTable
```

Example migration (`database/migrations/create_products_table.php`):
```php
public function change(): void
{
    $this->table('products')
        ->addColumn('name', 'string', ['limit' => 255])
        ->addColumn('description', 'text', ['null' => true])
        ->addColumn('price', 'decimal', ['precision' => 10, 'scale' => 2])
        ->addColumn('category_id', 'integer', ['signed' => false])
        ->addColumn('sku', 'string', ['limit' => 100])
        ->addColumn('stock_quantity', 'integer', ['default' => 0])
        ->addColumn('is_active', 'boolean', ['default' => true])
        ->addColumn('created_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
        ->addColumn('updated_at', 'timestamp', ['null' => true])
        ->addIndex(['sku'], ['unique' => true])
        ->addIndex(['category_id'])
        ->addForeignKey('category_id', 'categories', 'id', ['delete' => 'RESTRICT'])
        ->create();
}
```

## 🎮 Creating Controllers

### Step 1: Create Your Domain Controllers

```php
<?php
// src/Controllers/ProductController.php

namespace Src\Controllers;

use Src\Core\BaseController;
use Src\Services\ProductService;

/**
 * @OA\Tag(
 *     name="Products",
 *     description="Product management endpoints"
 * )
 */
class ProductController extends BaseController
{
    private ProductService $productService;

    public function __construct()
    {
        parent::__construct();
        $this->productService = new ProductService();
    }

    /**
     * @OA\Get(
     *     path="/products",
     *     summary="Get all products",
     *     tags={"Products"},
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Page number",
     *         @OA\Schema(type="integer", minimum=1)
     *     ),
     *     @OA\Parameter(
     *         name="category_id",
     *         in="query",
     *         description="Filter by category",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Products retrieved successfully"
     *     )
     * )
     */
    public function index()
    {
        $data = $this->getRequestData();
        $page = (int)($data['page'] ?? 1);
        $categoryId = $data['category_id'] ?? null;
        
        $products = $this->productService->getAll($page, $categoryId);
        $this->success($products);
    }

    /**
     * @OA\Post(
     *     path="/products",
     *     summary="Create a new product",
     *     tags={"Products"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name", "price", "category_id", "sku"},
     *             @OA\Property(property="name", type="string"),
     *             @OA\Property(property="description", type="string"),
     *             @OA\Property(property="price", type="number", format="float"),
     *             @OA\Property(property="category_id", type="integer"),
     *             @OA\Property(property="sku", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Product created successfully"
     *     )
     * )
     */
    public function store()
    {
        // Require authentication
        $user = $this->requireAuth();
        
        // Get and validate data
        $data = $this->getRequestData();
        $errors = $this->validateRequired($data, ['name', 'price', 'category_id', 'sku']);
        
        if (!empty($errors)) {
            $this->error('Validation failed', 400, $errors);
        }
        
        // Additional validation
        if ($data['price'] <= 0) {
            $this->error('Price must be greater than 0', 400);
        }
        
        // Sanitize data
        $sanitizedData = $this->sanitizeArray($data);
        
        // Create product
        $productId = $this->productService->create($sanitizedData);
        
        if (!$productId) {
            $this->error('Failed to create product', 500);
        }
        
        $this->logActivity('product_created', ['product_id' => $productId, 'user_id' => $user['user_id']]);
        $this->success(['id' => $productId], 'Product created successfully', 201);
    }

    /**
     * @OA\Get(
     *     path="/products/{id}",
     *     summary="Get product by ID",
     *     tags={"Products"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Product details"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Product not found"
     *     )
     * )
     */
    public function show($id)
    {
        $product = $this->productService->findById((int)$id);
        
        if (!$product) {
            $this->error('Product not found', 404);
        }
        
        $this->success($product);
    }

    /**
     * @OA\Put(
     *     path="/products/{id}",
     *     summary="Update product",
     *     tags={"Products"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string"),
     *             @OA\Property(property="description", type="string"),
     *             @OA\Property(property="price", type="number", format="float")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Product updated successfully"
     *     )
     * )
     */
    public function update($id)
    {
        $user = $this->requireAuth();
        $data = $this->getRequestData();
        
        // Check if product exists
        $product = $this->productService->findById((int)$id);
        if (!$product) {
            $this->error('Product not found', 404);
        }
        
        // Validate price if provided
        if (isset($data['price']) && $data['price'] <= 0) {
            $this->error('Price must be greater than 0', 400);
        }
        
        // Sanitize data
        $sanitizedData = $this->sanitizeArray($data);
        
        // Update product
        $updated = $this->productService->update((int)$id, $sanitizedData);
        
        if (!$updated) {
            $this->error('Failed to update product', 500);
        }
        
        $this->logActivity('product_updated', ['product_id' => $id, 'user_id' => $user['user_id']]);
        $this->success(null, 'Product updated successfully');
    }

    /**
     * @OA\Delete(
     *     path="/products/{id}",
     *     summary="Delete product",
     *     tags={"Products"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Product deleted successfully"
     *     )
     * )
     */
    public function destroy($id)
    {
        $user = $this->requireAuth();
        
        // Check if product exists
        $product = $this->productService->findById((int)$id);
        if (!$product) {
            $this->error('Product not found', 404);
        }
        
        // Delete product
        $deleted = $this->productService->delete((int)$id);
        
        if (!$deleted) {
            $this->error('Failed to delete product', 500);
        }
        
        $this->logActivity('product_deleted', ['product_id' => $id, 'user_id' => $user['user_id']]);
        $this->success(null, 'Product deleted successfully');
    }
}
```

## 🔧 Adding Business Logic (Services)

Create service classes to handle business logic:

```php
<?php
// src/Services/ProductService.php

namespace Src\Services;

use Src\Core\Database;

class ProductService
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function getAll(int $page = 1, ?int $categoryId = null): array
    {
        $conditions = [];
        $params = [];
        
        if ($categoryId) {
            $conditions[] = "category_id = ?";
            $params[] = $categoryId;
        }
        
        $whereClause = !empty($conditions) ? "WHERE " . implode(" AND ", $conditions) : "";
        $sql = "SELECT p.*, c.name as category_name 
                FROM products p 
                LEFT JOIN categories c ON p.category_id = c.id 
                {$whereClause} 
                ORDER BY p.created_at DESC";
        
        return $this->db->paginate($sql, $params, $page);
    }

    public function findById(int $id): ?array
    {
        $sql = "SELECT p.*, c.name as category_name 
                FROM products p 
                LEFT JOIN categories c ON p.category_id = c.id 
                WHERE p.id = ?";
        
        return $this->db->fetch($sql, [$id]);
    }

    public function create(array $data): ?int
    {
        try {
            // Add timestamps
            $data['created_at'] = date('Y-m-d H:i:s');
            $data['updated_at'] = date('Y-m-d H:i:s');
            
            return $this->db->insert('products', $data);
        } catch (\Exception $e) {
            return null;
        }
    }

    public function update(int $id, array $data): bool
    {
        try {
            $data['updated_at'] = date('Y-m-d H:i:s');
            $updated = $this->db->update('products', $data, 'id = ?', [$id]);
            return $updated > 0;
        } catch (\Exception $e) {
            return false;
        }
    }

    public function delete(int $id): bool
    {
        try {
            $deleted = $this->db->delete('products', 'id = ?', [$id]);
            return $deleted > 0;
        } catch (\Exception $e) {
            return false;
        }
    }

    public function findBySku(string $sku): ?array
    {
        return $this->db->fetch("SELECT * FROM products WHERE sku = ?", [$sku]);
    }

    public function updateStock(int $id, int $quantity): bool
    {
        try {
            $updated = $this->db->update(
                'products', 
                ['stock_quantity' => $quantity, 'updated_at' => date('Y-m-d H:i:s')], 
                'id = ?', 
                [$id]
            );
            return $updated > 0;
        } catch (\Exception $e) {
            return false;
        }
    }
}
```

## 🛣️ Adding Routes

Update `src/Routes/web.php`:

```php
// Product routes
$router->get('/products', 'ProductController@index');
$router->get('/products/{id}', 'ProductController@show');
$router->post('/products', 'ProductController@store');
$router->put('/products/{id}', 'ProductController@update');
$router->delete('/products/{id}', 'ProductController@destroy');

// Category routes
$router->get('/categories', 'CategoryController@index');
$router->post('/categories', 'CategoryController@store');

// Order routes (protected)
$router->get('/orders', 'OrderController@index');
$router->post('/orders', 'OrderController@store');
$router->get('/orders/{id}', 'OrderController@show');
```

## 🔐 Advanced Authentication

### Role-Based Access Control

Create a middleware for role checking:

```php
<?php
// src/Middlewares/RoleMiddleware.php

namespace Src\Middlewares;

use Src\Core\BaseController;

class RoleMiddleware extends BaseController
{
    public function requireRole(int $minLevel): array
    {
        $user = $this->requireAuth();
        
        if ($user['permission_level'] < $minLevel) {
            $this->error('Insufficient permissions', 403);
        }
        
        return $user;
    }
    
    public function requireAdmin(): array
    {
        return $this->requireRole(1);
    }
}
```

Use in controllers:
```php
public function adminOnlyAction()
{
    $middleware = new RoleMiddleware();
    $user = $middleware->requireAdmin();
    
    // Admin-only logic here
}
```

## 📁 File Upload Example

```php
public function uploadProductImage($id)
{
    $user = $this->requireAuth();
    
    // Check if product exists
    $product = $this->productService->findById((int)$id);
    if (!$product) {
        $this->error('Product not found', 404);
    }
    
    // Handle file upload
    $allowedTypes = ['jpg', 'jpeg', 'png', 'webp'];
    $fileName = $this->handleFileUpload('image', $allowedTypes);
    
    if (!$fileName) {
        $this->error('Failed to upload image', 400);
    }
    
    // Update product with image
    $this->productService->update((int)$id, ['image' => $fileName]);
    
    $this->success(['image' => $fileName], 'Image uploaded successfully');
}
```

## ✉️ Email Integration

```php
<?php
// src/Services/EmailService.php

namespace Src\Services;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;
use Src\Core\Config;

class EmailService
{
    private Config $config;

    public function __construct()
    {
        $this->config = Config::getInstance();
    }

    public function sendWelcomeEmail(string $email, string $name): bool
    {
        $subject = "Welcome to " . $this->config->get('app.name');
        $body = "
            <h1>Welcome, {$name}!</h1>
            <p>Thank you for registering with us.</p>
        ";
        
        return $this->sendEmail($email, $subject, $body);
    }

    public function sendPasswordResetEmail(string $email, string $token): bool
    {
        $resetUrl = $this->config->get('app.url') . "/reset-password?token={$token}";
        $subject = "Password Reset Request";
        $body = "
            <h1>Password Reset</h1>
            <p>Click the link below to reset your password:</p>
            <a href='{$resetUrl}'>Reset Password</a>
            <p>This link expires in 1 hour.</p>
        ";
        
        return $this->sendEmail($email, $subject, $body);
    }

    private function sendEmail(string $to, string $subject, string $body): bool
    {
        try {
            $mail = new PHPMailer(true);
            $mailConfig = $this->config->get('mail');

            // Server settings
            $mail->isSMTP();
            $mail->Host = $mailConfig['host'];
            $mail->SMTPAuth = true;
            $mail->Username = $mailConfig['username'];
            $mail->Password = $mailConfig['password'];
            $mail->SMTPSecure = $mailConfig['encryption'];
            $mail->Port = $mailConfig['port'];

            // Recipients
            $mail->setFrom($mailConfig['from_address'], $mailConfig['from_name']);
            
            // Use force_to for development
            $recipient = $mailConfig['force_to'] ?? $to;
            $mail->addAddress($recipient);

            // Content
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body = $body;

            $mail->send();
            return true;
        } catch (Exception $e) {
            error_log("Email error: " . $e->getMessage());
            return false;
        }
    }
}
```

## 🧪 Testing

Create a simple test structure:

```php
<?php
// tests/AuthTest.php

use PHPUnit\Framework\TestCase;

class AuthTest extends TestCase
{
    public function testLogin()
    {
        // Mock a login request
        $data = [
            'identifier' => 'admin@example.com',
            'password' => 'admin123!'
        ];
        
        // Test your login logic
        $this->assertTrue(true); // Replace with actual test
    }
}
```

Run tests:
```bash
composer test
```

## 🚀 Deployment

### Environment Setup
```bash
# Production environment
APP_ENV=production
APP_DEBUG=false
JWT_SECRET=your_super_secure_production_secret
```

### Nginx Configuration
```nginx
server {
    listen 80;
    server_name your-api.com;
    root /path/to/your/api/public;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.0-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }
}
```

### Production Checklist
- [ ] Update all environment variables
- [ ] Change default passwords
- [ ] Configure proper database credentials
- [ ] Set up SSL certificate
- [ ] Configure email settings
- [ ] Set up monitoring and logging
- [ ] Create database backups
- [ ] Test all endpoints

## 📚 Additional Resources

- [PHP PSR Standards](https://www.php-fig.org/psr/)
- [JWT.io](https://jwt.io/) - JWT documentation
- [Swagger/OpenAPI](https://swagger.io/docs/) - API documentation
- [Phinx Documentation](https://phinx.org/) - Database migrations

Happy coding! 🎉
