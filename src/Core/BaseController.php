<?php

namespace Src\Core;

use Src\Core\Database;
use Src\Core\Config;

/**
 * Base Controller Class
 * 
 * Provides common functionality for all controllers including
 * database access, response handling, validation, and utilities
 */
abstract class BaseController
{
    protected $db;
    protected $config;

    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->config = Config::getInstance();
    }

    /**
     * Send JSON response
     */
    protected function jsonResponse(array $data, int $statusCode = 200): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }

    /**
     * Send success response
     */
    protected function success($data = null, string $message = 'Success', int $statusCode = 200): void
    {
        $response = [
            'success' => true,
            'message' => $message,
        ];

        if ($data !== null) {
            $response['data'] = $data;
        }

        $this->jsonResponse($response, $statusCode);
    }

    /**
     * Send error response
     */
    protected function error(string $message, int $statusCode = 400, array $errors = []): void
    {
        $response = [
            'success' => false,
            'message' => $message,
        ];

        if (!empty($errors)) {
            $response['errors'] = $errors;
        }

        $this->jsonResponse($response, $statusCode);
    }

    /**
     * Get request data (JSON or Form)
     */
    protected function getRequestData(): array
    {
        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
        
        if (strpos($contentType, 'application/json') !== false) {
            $input = file_get_contents('php://input');
            $data = json_decode($input, true);
            return $data ?? [];
        }
        
        return array_merge($_GET, $_POST);
    }

    /**
     * Validate required fields
     */
    protected function validateRequired(array $data, array $required): array
    {
        $errors = [];
        
        foreach ($required as $field) {
            if (!isset($data[$field]) || empty($data[$field])) {
                $errors[] = "Field '{$field}' is required";
            }
        }
        
        return $errors;
    }

    /**
     * Validate email format
     */
    protected function validateEmail(string $email): bool
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    /**
     * Sanitize string
     */
    protected function sanitizeString(string $input): string
    {
        return htmlspecialchars(strip_tags(trim($input)), ENT_QUOTES, 'UTF-8');
    }

    /**
     * Sanitize array recursively
     */
    protected function sanitizeArray(array $data): array
    {
        $sanitized = [];
        
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $sanitized[$key] = $this->sanitizeArray($value);
            } elseif (is_string($value)) {
                $sanitized[$key] = $this->sanitizeString($value);
            } else {
                $sanitized[$key] = $value;
            }
        }
        
        return $sanitized;
    }

    /**
     * Paginate results
     */
    protected function paginate(string $sql, array $params = [], int $page = 1, int $perPage = 20): array
    {
        $page = max(1, $page);
        $offset = ($page - 1) * $perPage;
        
        // Get total count
        $countSql = "SELECT COUNT(*) as total FROM ({$sql}) as count_query";
        $total = $this->db->fetch($countSql, $params)['total'];
        
        // Get paginated results
        $paginatedSql = "{$sql} LIMIT {$perPage} OFFSET {$offset}";
        $data = $this->db->fetchAll($paginatedSql, $params);
        
        return [
            'data' => $data,
            'pagination' => [
                'current_page' => $page,
                'per_page' => $perPage,
                'total' => (int) $total,
                'last_page' => ceil($total / $perPage),
                'from' => $offset + 1,
                'to' => min($offset + $perPage, $total)
            ]
        ];
    }

    /**
     * Check if user is authenticated
     */
    protected function requireAuth(): array
    {
        $headers = getallheaders();
        $authHeader = $headers['Authorization'] ?? null;

        if (!$authHeader || !preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
            $this->error('Unauthorized: Missing or invalid token', 401);
            return []; // This will never be reached due to exit in error method
        }

        $jwt = $matches[1];
        
        try {
            $decoded = \Firebase\JWT\JWT::decode(
                $jwt, 
                new \Firebase\JWT\Key($this->config->get('jwt.secret'), $this->config->get('jwt.algorithm'))
            );
            
            // Check if session is active
            $session = $this->db->fetch(
                "SELECT * FROM sessions WHERE id_user_fk = ? AND session_token = ?",
                [$decoded->user_id, $jwt]
            );

            if (!$session) {
                $this->error('Unauthorized: Invalid session', 401);
                return []; // This will never be reached due to exit in error method
            }

            return (array) $decoded;
        } catch (\Firebase\JWT\ExpiredException $e) {
            // Delete expired session
            $this->db->delete('sessions', 'session_token = ?', [$jwt]);
            $this->error('Unauthorized: Token expired', 401);
            return []; // This will never be reached due to exit in error method
        } catch (\Exception $e) {
            $this->error('Unauthorized: Invalid token', 401);
            return []; // This will never be reached due to exit in error method
        }
    }

    /**
     * Log activity
     */
    protected function logActivity(string $action, array $data = []): void
    {
        if ($this->config->get('logging.enabled', true)) {
            $logData = [
                'timestamp' => date('Y-m-d H:i:s'),
                'action' => $action,
                'data' => $data,
                'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
            ];
            
            // You can implement file logging, database logging, etc.
            error_log(json_encode($logData));
        }
    }

    /**
     * Handle file upload
     */
    protected function handleFileUpload(string $fieldName, array $allowedTypes = []): ?string
    {
        if (!isset($_FILES[$fieldName]) || $_FILES[$fieldName]['error'] !== UPLOAD_ERR_OK) {
            return null;
        }

        $file = $_FILES[$fieldName];
        $maxSize = $this->config->get('storage.max_file_size');
        
        if ($file['size'] > $maxSize) {
            $this->error("File size exceeds maximum allowed size");
        }

        if (!empty($allowedTypes)) {
            $fileType = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            if (!in_array($fileType, $allowedTypes)) {
                $this->error("File type not allowed");
            }
        }

        $uploadPath = $this->config->get('storage.upload_path');
        $fileName = uniqid() . '_' . basename($file['name']);
        $targetPath = $uploadPath . $fileName;

        if (!is_dir($uploadPath)) {
            mkdir($uploadPath, 0755, true);
        }

        if (move_uploaded_file($file['tmp_name'], $targetPath)) {
            return $fileName;
        }

        return null;
    }
}
