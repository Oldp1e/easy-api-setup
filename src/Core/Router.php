<?php

namespace Src\Core;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class Router
{
    private $routes = [];

    public function get($uri, $controller)
    {
        // Certifique-se de que o roteador processa parâmetros dinâmicos corretamente
        if (preg_match('/\{([a-zA-Z_][a-zA-Z0-9_]*)\}/', $uri)) {
            // Lógica para capturar parâmetros dinâmicos
        }
        $this->routes['GET'][$uri] = $controller;
    }

    public function post($uri, $controller)
    {
        $this->routes['POST'][$uri] = $controller;
    }

    public function put($uri, $controller)
    {
        $this->routes['PUT'][$uri] = $controller;
    }

    public function delete($uri, $controller)
    {
        $this->routes['DELETE'][$uri] = $controller;
    }

    public function options($uri, $controller)
    {
        $this->routes['OPTIONS'][$uri] = $controller;
    }

    private function getRequestUri()
    {
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

        $appEnv = $_ENV['APP_ENV'] ?? 'development';

        switch ($appEnv) {
            case 'production':
            case 'prod':
                $basePath = '/api';
                break;

            case 'local':
            case 'development':
            default:
                $basePath = '';
                break;
        }

        if ($basePath !== '' && strpos($uri, $basePath) === 0) {
            $uri = substr($uri, strlen($basePath));
            $uri = $uri ?: '/';
        }

        return $uri;
    }

    public function dispatch()
    {
        $uri = $this->getRequestUri();

        $method = $_SERVER['REQUEST_METHOD'];

        // Tratamento especial para OPTIONS (preflight CORS)
        if ($method === 'OPTIONS') {
            // Se houver uma rota específica para OPTIONS, use-a
            if (isset($this->routes['OPTIONS'][$uri])) {
                $route = $this->routes['OPTIONS'][$uri];
                [$class, $method] = explode('@', $route);
                $controller = "Src\\Controllers\\$class";
                $controllerInstance = new $controller;
                $controllerInstance->$method();
            } else {
                // Caso contrário, apenas retorne 200 OK (os cabeçalhos CORS já foram configurados)
                http_response_code(200);
                exit;
            }
            return;
        }

        // Define public routes (no authentication required)
        $publicRoutes = [
            '/auth/login',
            '/auth/register',
            '/auth/request-reset',
            '/auth/reset-password',
            '/health',
            '/info',
        ];

        // Define private GET routes (require authentication)
        $privateGetRoutes = [
            '/auth/me',
            '/auth/profile',
            // Add your private GET routes here
            // Example: '/admin/users', '/user/profile', etc.
        ];

        // Determine if the route is public
        $isPublicRoute = false;
        if ($method === 'GET') {
            $isPublicRoute = true;

            // Check if the GET route is in the private list
            foreach ($privateGetRoutes as $route) {
                $pattern = preg_replace('/\{[a-zA-Z_][a-zA-Z0-9_]*\}/', '([a-zA-Z0-9_-]+)', $route);
                $pattern = "#^" . $pattern . "$#";
                if (preg_match($pattern, $uri)) {
                    $isPublicRoute = false;
                    break;
                }
            }
        } elseif (in_array($uri, $publicRoutes)) {
            $isPublicRoute = true;
        }

        // Require authentication for non-public routes or for PUT/DELETE methods
        if (!$isPublicRoute || in_array($method, ['PUT', 'DELETE'])) {
            $headers = getallheaders();
            $authHeader = $headers['Authorization'] ?? null;

            if (!$authHeader || !preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
                http_response_code(401);
                echo json_encode(['message' => 'Unauthorized: Missing or invalid token']);
                return;
            }

            $jwt = $matches[1];

            try {
                $decoded = JWT::decode($jwt, new Key($_ENV['JWT_SECRET'], 'HS256'));
                $userId = $decoded->user_id;

                // Check if the session is active
                $db = new \PDO(
                    "mysql:host={$_ENV['DB_HOST']};dbname={$_ENV['DB_NAME']};port={$_ENV['DB_PORT']}",
                    $_ENV['DB_USER'],
                    $_ENV['DB_PASS']
                );
                $stmt = $db->prepare("SELECT * FROM sessions WHERE id_user_fk = ? AND session_token = ?");
                $stmt->execute([$userId, $jwt]);

                $session = $stmt->fetch(\PDO::FETCH_ASSOC);

                if (!$session) {
                    http_response_code(401);
                    echo json_encode(['message' => 'Unauthorized: Invalid session']);
                    return;
                }
            } catch (\Firebase\JWT\ExpiredException $e) {
                // Delete the expired session
                $db = new \PDO(
                    "mysql:host={$_ENV['DB_HOST']};dbname={$_ENV['DB_NAME']};port={$_ENV['DB_PORT']}",
                    $_ENV['DB_USER'],
                    $_ENV['DB_PASS']
                );
                $stmt = $db->prepare("DELETE FROM sessions WHERE session_token = ?");
                $stmt->execute([$jwt]);

                http_response_code(401);
                echo json_encode(['message' => 'Unauthorized: JWT Invalid or Expired']);
                return;
            } catch (\Exception $e) {
                http_response_code(401);
                echo json_encode(['message' => 'Unauthorized: Invalid token', 'error' => $e->getMessage()]);
                return;
            }
        }

        // Tratamento para rotas dinâmicas
        foreach ($this->routes[$method] as $route => $controller) {
            $pattern = preg_replace('/\{[a-zA-Z_][a-zA-Z0-9_]*\}/', '([a-zA-Z0-9_-]+)', $route);
            $pattern = "#^" . $pattern . "$#";

            if (preg_match($pattern, $uri, $matches)) {
                array_shift($matches); // Remove o primeiro elemento (URI completa)
                [$class, $method] = explode('@', $controller);
                $controller = "Src\\Controllers\\$class";
                $controllerInstance = new $controller;
                $controllerInstance->$method(...$matches);
                return;
            }
        }

        // Rota não encontrada
        http_response_code(404);
        echo json_encode(['message' => 'Route not found']);
    }
}