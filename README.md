# 🚀 Generic API Template

![API Version](https://img.shields.io/badge/version-1.0.0-blue.svg)
![PHP Version](https://img.shields.io/badge/php-%3E%3D8.0-green.svg)
![License](https://img.shields.io/badge/license-MIT-yellow.svg)

**A complete, production-ready API template** built with PHP 8.0+ and modern development patterns. This template provides everything you need to quickly bootstrap robust REST APIs for any domain.

## 🌟 Template Features

This template provides a solid foundation for building REST APIs with:

- ✅ **JWT Authentication System** with session management
- ✅ **Generic Controllers & Services** ready to customize
- ✅ **Database Abstraction Layer** with multiple database support
- ✅ **Configuration Management** via environment variables
- ✅ **Automatic Swagger Documentation** generation
- ✅ **File Upload Handling** with validation
- ✅ **Input Validation & Sanitization** built-in
- ✅ **Error Handling & Logging** system
- ✅ **CORS Configuration** for different environments
- ✅ **Migration & Seeding System** with Phinx
- ✅ **Multi-environment Support** (dev/staging/prod)
- ✅ **Rate Limiting Ready** configuration
- ✅ **Cache Layer Ready** configuration

## 📋 Table of Contents

- [Quick Start](#-quick-start)
- [Template Structure](#-template-structure)
- [Core Components](#-core-components)
- [Customization Guide](#-customization-guide)
- [Authentication System](#-authentication-system)
- [Database Setup](#-database-setup)
- [API Documentation](#-api-documentation)
- [Environment Configuration](#-environment-configuration)
- [Deployment](#-deployment)
- [Contributing](#-contributing)

## 🚀 Quick Start

### 1. Clone the Template
```bash
git clone <your-template-url> my-new-api
cd my-new-api
```

### 2. Install Dependencies
```bash
composer install
```

### 3. Configure Environment
```bash
cp .env.example .env
# Edit .env with your configuration
```

### 4. Setup Database
```bash
# Run migrations
composer migrate

# Seed with example data (optional)
composer db:seed
```

### 5. Start Development Server
```bash
composer start
```

Your API will be available at `http://localhost:8000`
Access Swagger docs at: `http://localhost:8000/docs`

## 🏗️ Template Structure

```
src/
├── Controllers/          # API Controllers
│   ├── GenericAuthController.php    # Authentication endpoints
│   └── [YourDomainControllers].php  # Your custom controllers
├── Core/                # Core Framework Classes
│   ├── BaseController.php          # Base controller with utilities
│   ├── Config.php                  # Configuration manager
│   ├── Database.php                # Database connection manager
│   ├── Router.php                  # HTTP router
│   └── Cors.php                    # CORS handler
├── Services/            # Business Logic Services
│   ├── AuthService.php             # Authentication service
│   └── [YourServices].php          # Your custom services
├── Routes/              # Route Definitions
│   └── web.php                     # HTTP routes
├── Helpers/             # Utility Classes
└── Middlewares/         # Custom Middlewares

database/
├── migrations/          # Database Migrations
└── seeds/              # Database Seeders

public/
├── index.php           # Application entry point
├── swagger.json        # Auto-generated API docs
└── docs/              # Swagger UI
```

## 🔧 Core Components

### BaseController
All controllers extend `BaseController` which provides:
- **JSON Response helpers** (`success()`, `error()`)
- **Request data parsing** (JSON/Form data)
- **Input validation & sanitization**
- **Authentication checks** (`requireAuth()`)
- **File upload handling**
- **Pagination utilities**
- **Activity logging**

### Database Class
Singleton database manager with:
- **Multiple database support** (MySQL, PostgreSQL, SQLite)
- **Query builder methods** (`insert()`, `update()`, `delete()`)
- **Transaction support**
- **Prepared statements** for security

### Config Class
Centralized configuration management:
- **Environment-based settings**
- **Nested configuration access** (`config.get('database.host')`)
- **Runtime configuration updates**

### AuthService
Complete authentication system:
- **JWT token generation/validation**
- **Session management**
- **Password hashing/verification**
- **Password reset functionality**
- **User registration**

## 📝 Customization Guide

### 1. Creating Your Domain Controllers

```php
<?php
namespace Src\Controllers;

use Src\Core\BaseController;

class ProductController extends BaseController
{
    public function getAll()
    {
        // Your authentication check (if needed)
        // $user = $this->requireAuth();
        
        // Get request data
        $data = $this->getRequestData();
        
        // Pagination
        $page = $data['page'] ?? 1;
        $result = $this->paginate("SELECT * FROM products", [], $page);
        
        $this->success($result);
    }
    
    public function create()
    {
        $user = $this->requireAuth(); // Require authentication
        $data = $this->getRequestData();
        
        // Validate
        $errors = $this->validateRequired($data, ['name', 'price']);
        if (!empty($errors)) {
            $this->error('Validation failed', 400, $errors);
        }
        
        // Sanitize
        $sanitized = $this->sanitizeArray($data);
        
        // Save to database
        $id = $this->db->insert('products', $sanitized);
        
        $this->success(['id' => $id], 'Product created', 201);
    }
}
```

### 2. Adding Routes

Edit `src/Routes/web.php`:

```php
// Public routes
$router->get('/products', 'ProductController@getAll');
$router->get('/products/{id}', 'ProductController@getById');

// Protected routes (require authentication)
$router->post('/products', 'ProductController@create');
$router->put('/products/{id}', 'ProductController@update');
$router->delete('/products/{id}', 'ProductController@delete');
```

### 3. Creating Services

```php
<?php
namespace Src\Services;

use Src\Core\Database;

class ProductService
{
    private $db;
    
    public function __construct()
    {
        $this->db = Database::getInstance();
    }
    
    public function findById(int $id): ?array
    {
        return $this->db->fetch("SELECT * FROM products WHERE id = ?", [$id]);
    }
    
    public function create(array $data): int
    {
        return $this->db->insert('products', $data);
    }
}
```

### 4. Database Migrations

Create new migration:
```bash
composer create CreateProductsTable
```

Edit the generated migration file:
```php
public function change(): void
{
    $this->table('products')
        ->addColumn('name', 'string', ['limit' => 255])
        ->addColumn('description', 'text', ['null' => true])
        ->addColumn('price', 'decimal', ['precision' => 10, 'scale' => 2])
        ->addColumn('created_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
        ->create();
}
```

Run migration:
```bash
composer migrate
```

## � Authentication System

The template includes a complete JWT-based authentication system:

### Default Endpoints

| Method | Endpoint | Description | Auth Required |
|--------|----------|-------------|---------------|
| POST | `/auth/login` | User login | ❌ |
| POST | `/auth/register` | User registration | ❌ |
| POST | `/auth/logout` | User logout | ✅ |
| GET | `/auth/me` | Get current user | ✅ |
| PUT | `/auth/profile` | Update profile | ✅ |
| POST | `/auth/request-reset` | Request password reset | ❌ |
| POST | `/auth/reset-password` | Reset password with token | ❌ |
| POST | `/auth/change-password` | Change password | ✅ |

### Usage Examples

**Login:**
```bash
curl -X POST http://localhost:8000/auth/login \
  -H "Content-Type: application/json" \
  -d '{"identifier": "username", "password": "password123"}'
```

**Authenticated Request:**
```bash
curl -X GET http://localhost:8000/auth/me \
  -H "Authorization: Bearer YOUR_JWT_TOKEN"
```

### Protecting Your Routes

```php
// In your controller
public function protectedMethod()
{
    $user = $this->requireAuth(); // Throws 401 if not authenticated
    // $user contains: user_id, username, email, permission_level
    
    // Your protected logic here
}
```

## 💾 Database Setup

### Supported Databases
- **MySQL** (default)
- **PostgreSQL** 
- **SQLite**

### Default Tables
The template includes these base tables:
- **users** - User accounts
- **sessions** - JWT session management
- **password_resets** - Password reset tokens

### Migration Commands
```bash
# Create new migration
composer create MyNewTable

# Run migrations
composer migrate

# Rollback last migration
composer rollback

# Reset database (rollback all + migrate + seed)
composer reset-db
```

### Creating Seeders
```bash
composer create-seed MySeeder
```

Example seeder:
```php
use Phinx\Seed\AbstractSeed;

class MySeeder extends AbstractSeed
{
    public function run()
    {
        $data = [
            ['name' => 'Sample Product', 'price' => 29.99],
            ['name' => 'Another Product', 'price' => 49.99],
        ];
        
        $this->table('products')->insert($data)->saveData();
    }
}
```

## 📖 API Documentation

### Swagger/OpenAPI Integration

The template automatically generates interactive API documentation:

**Access:** `http://localhost:8000/docs`

### Adding Documentation to Your Controllers

```php
/**
 * @OA\Get(
 *     path="/products",
 *     summary="Get all products",
 *     tags={"Products"},
 *     @OA\Parameter(
 *         name="page",
 *         in="query",
 *         description="Page number",
 *         @OA\Schema(type="integer")
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="List of products",
 *         @OA\JsonContent(
 *             @OA\Property(property="success", type="boolean"),
 *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/Product"))
 *         )
 *     )
 * )
 */
public function getAll() { ... }
```

### Generate Documentation
```bash
composer generate-swagger-json
```

## ⚙️ Environment Configuration

The template uses a comprehensive configuration system:

### Configuration Categories

**Application Settings:**
```env
APP_NAME="My API"
APP_VERSION="1.0.0"
APP_ENV=development
APP_DEBUG=true
APP_URL=http://localhost:8000
```

**Database:**
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_NAME=my_api_db
DB_USER=root
DB_PASS=password
```

**JWT & Security:**
```env
JWT_SECRET=your_super_secure_secret
JWT_EXPIRES_IN=86400
```

**Email Configuration:**
```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=465
MAIL_USERNAME=your_email@gmail.com
MAIL_PASSWORD=your_app_password
```

**CORS:**
```env
CORS_ALLOWED_ORIGINS=http://localhost:3000,https://yourapp.com
CORS_ALLOWED_METHODS=GET,POST,PUT,DELETE,OPTIONS
```

**File Storage:**
```env
STORAGE_DRIVER=local
UPLOAD_MAX_SIZE=10485760
ALLOWED_EXTENSIONS=jpg,jpeg,png,pdf
```

### Accessing Configuration

```php
$config = Config::getInstance();

// Get single value
$dbHost = $config->get('database.host');

// Get with default
$cacheDriver = $config->get('cache.driver', 'file');

// Check if exists
if ($config->has('external_apis.stripe.key')) {
    // Stripe is configured
}
```

## � Deployment

### Production Environment Setup

1. **Environment Configuration**
```env
APP_ENV=production
APP_DEBUG=false
JWT_SECRET=your_super_secure_production_secret_32_chars_min
DB_HOST=your_production_db_host
DB_NAME=your_production_db_name
# ... other production settings
```

2. **Server Requirements**
- PHP 8.0+
- MySQL 5.7+ / PostgreSQL 10+ / SQLite 3
- Composer
- Web server (Nginx/Apache)

3. **Deployment Steps**
```bash
# Clone and setup
git clone <your-repo> production-api
cd production-api
composer install --no-dev --optimize-autoloader

# Configure environment
cp .env.example .env
# Edit .env with production values

# Setup database
composer migrate
composer db:seed

# Set permissions
chmod -R 755 storage/
chmod -R 755 cache/
```

### Web Server Configuration

**Nginx:**
```nginx
server {
    listen 80;
    server_name your-api.com;
    root /path/to/api/public;
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

**Apache (.htaccess in public/):**
```apache
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php [QSA,L]
```

## 🎯 Available Scripts

### Development
```bash
composer start              # Start development server (localhost only)
composer start:host         # Start server accessible from network
composer setup              # Initial project setup
```

### Database Management
```bash
composer migrate            # Run pending migrations
composer rollback          # Rollback last migration
composer db:fresh          # Fresh database (rollback all + migrate)
composer db:seed            # Run seeders
composer reset-db           # Complete database reset
composer create MyTable     # Create new migration
composer create-seed MySeed # Create new seeder
```

### Documentation
```bash
composer generate-swagger-json  # Generate API documentation
```

### Testing
```bash
composer test               # Run test suite (when implemented)
```

## 📚 Example Usage

### Authentication Flow
```bash
# Register a new user
curl -X POST http://localhost:8000/auth/register \
  -H "Content-Type: application/json" \
  -d '{
    "username": "newuser",
    "email": "user@example.com", 
    "password": "password123"
  }'

# Login
curl -X POST http://localhost:8000/auth/login \
  -H "Content-Type: application/json" \
  -d '{
    "identifier": "user@example.com",
    "password": "password123"
  }'

# Use the returned token for authenticated requests
curl -X GET http://localhost:8000/auth/me \
  -H "Authorization: Bearer YOUR_JWT_TOKEN"
```

### Default Credentials
After running `composer db:seed`:
- **Admin:** admin@example.com / admin123!
- **User:** user@example.com / user123!

## 🤝 Contributing

### Development Workflow

1. **Fork & Clone**
```bash
git clone <your-fork>
cd generic-api-template
```

2. **Setup Development Environment**
```bash
composer install
cp .env.example .env
composer migrate
composer db:seed
```

3. **Make Changes**
- Follow PSR-4 standards
- Add Swagger documentation
- Include tests
- Update documentation

4. **Test Changes**
```bash
composer test
# Test API endpoints manually
```

5. **Submit Pull Request**

### Coding Standards
- **PSR-4** autoloading
- **PSR-12** coding style
- **Semantic versioning**
- **Comprehensive documentation**

### File Structure Guidelines
- Controllers: Business logic entry points
- Services: Business logic implementation  
- Core: Framework components
- Models: Data structures (if using)
- Helpers: Utility functions
- Middlewares: Request/response processing

## 📈 Roadmap

### Current Version (1.0.0)
- ✅ JWT Authentication
- ✅ Database abstraction
- ✅ Swagger documentation
- ✅ File upload handling
- ✅ Configuration management
- ✅ Basic CRUD operations

### Planned Features
- 🔄 Rate limiting middleware
- 🔄 Caching layer
- 🔄 Event system
- 🔄 Queue system
- 🔄 API versioning
- 🔄 Advanced logging
- 🔄 Metrics and monitoring
- 🔄 GraphQL support

## 🆘 Support & Community

- **Issues:** [GitHub Issues](https://github.com/yourrepo/issues)
- **Discussions:** [GitHub Discussions](https://github.com/yourrepo/discussions)
- **Documentation:** [Full Documentation](./CUSTOMIZATION.md)

## 📄 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## 🙏 Acknowledgments

Built with these amazing open-source packages:
- [Firebase JWT](https://github.com/firebase/php-jwt)
- [PHPDotEnv](https://github.com/vlucas/phpdotenv)
- [Phinx](https://github.com/cakephp/phinx)
- [PHPMailer](https://github.com/PHPMailer/PHPMailer)
- [Swagger PHP](https://github.com/zircote/swagger-php)

---

**Happy coding!** 🚀 

If you find this template useful, please ⭐ star the repository and share it with others!

## ⚙️ Configuração

### Variáveis de Ambiente (.env)

```bash
# Application Settings
APP_NAME="Generic API Template"
APP_VERSION="2.0.0"
APP_ENV=development
APP_DEBUG=true
APP_URL=http://localhost:8000

# Security & JWT
JWT_SECRET=your_super_secure_jwt_secret_key_here_change_this
JWT_EXPIRES_IN=86400

# Database Configuration
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_NAME=generic_api_db
DB_USER=root
DB_PASS=

# Email Configuration (SMTP)
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=465
MAIL_USERNAME=your_email@gmail.com
MAIL_PASSWORD=your_app_password
MAIL_ENCRYPTION=ssl
MAIL_FROM_ADDRESS=noreply@example.com
MAIL_FROM_NAME="Generic API"

# CORS Configuration
CORS_ALLOWED_ORIGINS=http://localhost:3000,http://localhost:8080
CORS_ALLOWED_METHODS=GET,POST,PUT,DELETE,OPTIONS
CORS_ALLOWED_HEADERS=Content-Type,Authorization

# File Storage
STORAGE_DRIVER=local
STORAGE_PATH=storage/uploads
UPLOAD_MAX_SIZE=10485760
ALLOWED_EXTENSIONS=jpg,jpeg,png,gif,pdf,doc,docx

# Logging
LOG_LEVEL=debug
LOG_FILE=storage/logs/app.log

# Cache (for future implementation)
CACHE_DRIVER=file
CACHE_PREFIX=api_cache
```

### Ambientes Suportados
- **development/local** - Desenvolvimento local com debug ativo
- **staging** - Ambiente de testes 
- **production/prod** - Produção com configurações otimizadas

## 📖 API Documentation

### Swagger UI
Acesse a documentação interativa em: `http://localhost:8000/docs`

### Geração da Documentação
```bash
composer generate-swagger-json
```

A documentação é gerada automaticamente através de annotations nos controllers:

```php
/**
 * @OA\Post(
 *     path="/auth/login",
 *     summary="User login",
 *     tags={"Authentication"},
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"identifier", "password"},
 *             @OA\Property(property="identifier", type="string", example="admin@example.com"),
 *             @OA\Property(property="password", type="string", example="password123")
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Login successful",
 *         @OA\JsonContent(ref="#/components/schemas/SuccessResponse")
 *     )
 * )
 */
```

## 🗄️ Estrutura do Banco de Dados

### Principais Tabelas

1. **users** - Usuários do sistema
2. **user_types** - Tipos/perfis de usuário
3. **sessions** - Sessões ativas (JWT tokens)
4. **categories** - Categorias hierárquicas para organização
5. **items** - Itens/conteúdo genérico (posts, produtos, etc.)
6. **tags** - Tags para organização de conteúdo
7. **item_tags** - Relacionamento many-to-many entre items e tags
8. **notifications** - Sistema de notificações
9. **password_resets** - Tokens de recuperação de senha

### Relacionamentos
- **Users** N:1 **UserTypes**
- **Users** 1:N **Items** (criador)
- **Categories** 1:N **Categories** (hierárquica)
- **Categories** 1:N **Items**
- **Items** N:M **Tags** (através de item_tags)
- **Users** 1:N **Notifications**

## 🛣 Endpoints Disponíveis

### 🔐 Autenticação
| Método | Endpoint | Descrição | Auth |
|--------|----------|-----------|------|
| POST | `/auth/login` | Login do usuário | ❌ |
| POST | `/auth/register` | Registro de usuário | ❌ |
| POST | `/auth/logout` | Logout do usuário | ✅ |
| GET | `/auth/me` | Dados do usuário atual | ✅ |
| PUT | `/auth/profile` | Atualizar perfil | ✅ |
| POST | `/auth/request-reset` | Solicitar reset de senha | ❌ |
| POST | `/auth/reset-password` | Reset com token | ❌ |
| POST | `/auth/change-password` | Alterar senha | ✅ |

### � Categorias
| Método | Endpoint | Descrição | Auth |
|--------|----------|-----------|------|
| GET | `/categories` | Listar categorias | ❌ |
| GET | `/categories/tree` | Árvore hierárquica | ❌ |
| GET | `/categories/{id}` | Detalhes da categoria | ❌ |
| POST | `/categories` | Criar categoria | ✅ |
| PUT | `/categories/{id}` | Atualizar categoria | ✅ |
| DELETE | `/categories/{id}` | Excluir categoria | ✅ |

### 📝 Itens/Conteúdo
| Método | Endpoint | Descrição | Auth |
|--------|----------|-----------|------|
| GET | `/items` | Listar itens | ❌ |
| GET | `/items/{id}` | Detalhes do item | ❌ |
| GET | `/items/{id}/related` | Itens relacionados | ❌ |
| POST | `/items` | Criar item | ✅ |
| PUT | `/items/{id}` | Atualizar item | ✅ |
| DELETE | `/items/{id}` | Excluir item | ✅ |
| POST | `/items/{id}/like` | Curtir item | ✅ |
| POST | `/items/{id}/share` | Compartilhar item | ✅ |

### 🏷️ Tags
| Método | Endpoint | Descrição | Auth |
|--------|----------|-----------|------|
| GET | `/tags` | Listar tags | ❌ |
| GET | `/tags/{id}` | Detalhes da tag | ❌ |
| GET | `/tags/popular` | Tags mais populares | ❌ |
| POST | `/tags` | Criar tag | ✅ |
| PUT | `/tags/{id}` | Atualizar tag | ✅ |
| DELETE | `/tags/{id}` | Excluir tag | ✅ |

### 🔔 Notificações
| Método | Endpoint | Descrição | Auth |
|--------|----------|-----------|------|
| GET | `/notifications` | Listar notificações | ✅ |
| GET | `/notifications/{id}` | Detalhes da notificação | ✅ |
| PUT | `/notifications/{id}/read` | Marcar como lida | ✅ |
| PUT | `/notifications/read-all` | Marcar todas como lidas | ✅ |
| DELETE | `/notifications/{id}` | Excluir notificação | ✅ |

### 👥 Usuários (Admin)
| Método | Endpoint | Descrição | Auth |
|--------|----------|-----------|------|
| GET | `/users` | Listar usuários | ✅ |
| GET | `/users/{id}` | Detalhes do usuário | ✅ |
| PUT | `/users/{id}` | Atualizar usuário | ✅ |
| DELETE | `/users/{id}` | Excluir usuário | ✅ |
| PUT | `/users/{id}/activate` | Ativar usuário | ✅ |
| PUT | `/users/{id}/deactivate` | Desativar usuário | ✅ |

### 🛠️ Utilitários
| Método | Endpoint | Descrição | Auth |
|--------|----------|-----------|------|
| GET | `/health` | Status da API | ❌ |
| GET | `/info` | Informações da API | ❌ |

## 💡 Exemplos de Uso

### Autenticação
```bash
# Login
curl -X POST http://localhost:8000/auth/login \
  -H "Content-Type: application/json" \
  -d '{"identifier": "admin@example.com", "password": "admin123!"}'

# Resposta
{
  "success": true,
  "message": "Login successful",
  "data": {
    "token": "eyJ0eXAiOiJKV1QiLCJhbGc...",
    "user": {
      "id": 1,
      "name": "Administrator",
      "email": "admin@example.com"
    }
  }
}
```

### Criar uma Categoria
```bash
curl -X POST http://localhost:8000/categories \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer YOUR_JWT_TOKEN" \
  -d '{
    "name": "Technology",
    "slug": "technology",
    "description": "Technology related content",
    "is_active": true,
    "metadata": {
      "color": "#007bff",
      "icon": "fas fa-laptop"
    }
  }'
```

### Criar um Item/Conteúdo
```bash
curl -X POST http://localhost:8000/items \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer YOUR_JWT_TOKEN" \
  -d '{
    "title": "Getting Started with APIs",
    "slug": "getting-started-with-apis",
    "description": "A comprehensive guide to API development",
    "type": "blog_post",
    "status": "published",
    "category_id": 1,
    "content": {
      "excerpt": "Learn API fundamentals...",
      "reading_time": 15,
      "difficulty": "beginner"
    }
  }'
```

### Listar Categorias em Árvore
```bash
curl -X GET http://localhost:8000/categories/tree

# Resposta
{
  "success": true,
  "data": [
    {
      "id": 1,
      "name": "Technology",
      "slug": "technology",
      "children": [
        {
          "id": 2,
          "name": "Programming",
          "slug": "programming",
          "children": []
        }
      ]
    }
  ]
}
```

## 🎯 Scripts Disponíveis

```bash
# Servidor de desenvolvimento
composer start              # Localhost apenas
composer start:host         # Aceita conexões externas

# Banco de dados
composer migrate            # Executa migrations
composer rollback          # Reverte última migration
composer db:seed            # Popula com dados de exemplo
composer reset-db           # Reset completo do banco

# Documentação
composer generate-swagger-json  # Gera swagger.json

# Utilitários
composer create MyMigration  # Cria nova migration
composer create-seed MySeed  # Cria novo seeder
```

## 🤝 Contribuição

### Estrutura para Novas Features

1. **Controller**: Adicione em `src/Controllers/`
2. **Routes**: Registre em `src/Routes/web.php`
3. **Migration**: Crie com `composer create NomeDaMigration`
4. **Documentation**: Adicione annotations Swagger
5. **Tests**: Execute testes de endpoints

### Padrões de Código

- Siga PSR-4 para namespaces
- Adicione documentação Swagger em todos os endpoints
- Use prepared statements para queries
- Valide dados de entrada
- Mantenha controllers enxutos

---

## 👥 Autores

- **Samuel Jr.** - *samuel lima06091999@gmail.com*

---

## 📄 Licença

Este projeto está sob a licença MIT. Veja o arquivo [LICENSE](LICENSE) para detalhes.

---

## 🏆 Features Principais

- ✅ API RESTful completa
- ✅ Autenticação JWT com sessões
- ✅ Documentação Swagger interativa  
- ✅ Sistema de migrations robusto
- ✅ CORS configurável
- ✅ Recuperação de senha via email
- ✅ Arquitetura MVC organizada
- ✅ Padrões PSR-4
- ✅ Ambiente multi-stage (dev/prod)
