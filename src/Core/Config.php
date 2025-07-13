<?php

namespace Src\Core;

/**
 * Central Configuration Manager
 * 
 * This class manages all application configurations and provides
 * a centralized way to access environment variables and settings
 */
class Config
{
    private static $instance = null;
    private $config = [];

    private function __construct()
    {
        $this->loadConfig();
    }

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function loadConfig(): void
    {
        $this->config = [
            'app' => [
                'name' => $_ENV['APP_NAME'] ?? 'Generic API',
                'version' => $_ENV['APP_VERSION'] ?? '1.0.0',
                'env' => $_ENV['APP_ENV'] ?? 'development',
                'debug' => filter_var($_ENV['APP_DEBUG'] ?? false, FILTER_VALIDATE_BOOLEAN),
                'url' => $_ENV['APP_URL'] ?? 'http://localhost:8000',
                'timezone' => $_ENV['APP_TIMEZONE'] ?? 'UTC',
            ],

            'database' => [
                'connection' => $_ENV['DB_CONNECTION'] ?? 'mysql',
                'host' => $_ENV['DB_HOST'] ?? '127.0.0.1',
                'port' => $_ENV['DB_PORT'] ?? 3306,
                'name' => $_ENV['DB_NAME'] ?? 'generic_api_db',
                'user' => $_ENV['DB_USER'] ?? 'root',
                'password' => $_ENV['DB_PASS'] ?? '',
                'charset' => $_ENV['DB_CHARSET'] ?? 'utf8mb4',
                'options' => [
                    \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                    \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
                    \PDO::ATTR_EMULATE_PREPARES => false,
                ]
            ],

            'jwt' => [
                'secret' => $_ENV['JWT_SECRET'] ?? 'default_secret_change_this',
                'algorithm' => $_ENV['JWT_ALGORITHM'] ?? 'HS256',
                'expires_in' => (int)($_ENV['JWT_EXPIRES_IN'] ?? 86400), // 24 hours
                'issuer' => $_ENV['JWT_ISSUER'] ?? 'generic-api',
                'audience' => $_ENV['JWT_AUDIENCE'] ?? 'generic-api-users',
            ],

            'mail' => [
                'mailer' => $_ENV['MAIL_MAILER'] ?? 'smtp',
                'host' => $_ENV['MAIL_HOST'] ?? 'smtp.gmail.com',
                'port' => (int)($_ENV['MAIL_PORT'] ?? 465),
                'username' => $_ENV['MAIL_USERNAME'] ?? '',
                'password' => $_ENV['MAIL_PASSWORD'] ?? '',
                'encryption' => $_ENV['MAIL_ENCRYPTION'] ?? 'ssl',
                'from_address' => $_ENV['MAIL_FROM_ADDRESS'] ?? 'noreply@example.com',
                'from_name' => $_ENV['MAIL_FROM_NAME'] ?? 'Generic API',
                'force_to' => $_ENV['FORCE_MAIL_TO'] ?? null,
            ],

            'cors' => [
                'allowed_origins' => explode(',', $_ENV['CORS_ALLOWED_ORIGINS'] ?? $_ENV['CROSS_ORIGIN_ACCEPTED_URL'] ?? 'http://localhost:3000'),
                'allowed_methods' => explode(',', $_ENV['CORS_ALLOWED_METHODS'] ?? 'GET,POST,PUT,DELETE,OPTIONS'),
                'allowed_headers' => explode(',', $_ENV['CORS_ALLOWED_HEADERS'] ?? 'Content-Type,Authorization,X-Requested-With'),
                'exposed_headers' => explode(',', $_ENV['CORS_EXPOSED_HEADERS'] ?? ''),
                'max_age' => (int)($_ENV['CORS_MAX_AGE'] ?? 3600),
                'credentials' => filter_var($_ENV['CORS_CREDENTIALS'] ?? false, FILTER_VALIDATE_BOOLEAN),
            ],

            'storage' => [
                'driver' => $_ENV['STORAGE_DRIVER'] ?? 'local',
                'upload_path' => $_ENV['UPLOAD_PATH'] ?? 'uploads/',
                'max_file_size' => (int)($_ENV['UPLOAD_MAX_SIZE'] ?? 10485760), // 10MB
                'allowed_extensions' => explode(',', $_ENV['ALLOWED_EXTENSIONS'] ?? 'jpg,jpeg,png,gif,pdf,doc,docx'),
            ],

            'cache' => [
                'driver' => $_ENV['CACHE_DRIVER'] ?? 'file',
                'ttl' => (int)($_ENV['CACHE_TTL'] ?? 3600),
                'path' => $_ENV['CACHE_PATH'] ?? 'cache/',
            ],

            'rate_limit' => [
                'enabled' => filter_var($_ENV['RATE_LIMIT_ENABLED'] ?? true, FILTER_VALIDATE_BOOLEAN),
                'max_requests' => (int)($_ENV['RATE_LIMIT_MAX_REQUESTS'] ?? 100),
                'window_minutes' => (int)($_ENV['RATE_LIMIT_WINDOW_MINUTES'] ?? 60),
            ],

            'logging' => [
                'level' => $_ENV['LOG_LEVEL'] ?? 'debug',
                'channel' => $_ENV['LOG_CHANNEL'] ?? 'file',
                'path' => $_ENV['LOG_PATH'] ?? 'logs/',
            ],

            'external_apis' => [
                'stripe' => [
                    'key' => $_ENV['STRIPE_KEY'] ?? '',
                    'secret' => $_ENV['STRIPE_SECRET'] ?? '',
                ],
                'paypal' => [
                    'client_id' => $_ENV['PAYPAL_CLIENT_ID'] ?? '',
                    'client_secret' => $_ENV['PAYPAL_CLIENT_SECRET'] ?? '',
                    'mode' => $_ENV['PAYPAL_MODE'] ?? 'sandbox', // sandbox or live
                ],
            ],
        ];
    }

    public function get(string $key, $default = null)
    {
        return $this->getNestedValue($this->config, $key, $default);
    }

    public function set(string $key, $value): void
    {
        $this->setNestedValue($this->config, $key, $value);
    }

    public function has(string $key): bool
    {
        return $this->getNestedValue($this->config, $key) !== null;
    }

    public function all(): array
    {
        return $this->config;
    }

    private function getNestedValue(array $array, string $key, $default = null)
    {
        if (strpos($key, '.') === false) {
            return $array[$key] ?? $default;
        }

        $keys = explode('.', $key);
        $value = $array;

        foreach ($keys as $k) {
            if (!is_array($value) || !array_key_exists($k, $value)) {
                return $default;
            }
            $value = $value[$k];
        }

        return $value;
    }

    private function setNestedValue(array &$array, string $key, $value): void
    {
        if (strpos($key, '.') === false) {
            $array[$key] = $value;
            return;
        }

        $keys = explode('.', $key);
        $current = &$array;

        foreach ($keys as $k) {
            if (!isset($current[$k]) || !is_array($current[$k])) {
                $current[$k] = [];
            }
            $current = &$current[$k];
        }

        $current = $value;
    }
}
