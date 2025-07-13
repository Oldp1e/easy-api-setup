<?php

namespace Src\Services;

use Src\Core\Database;
use Src\Core\Config;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

/**
 * Authentication Service
 * 
 * Handles user authentication, JWT token generation,
 * session management, and password operations
 */
class AuthService
{
    private $db;
    private $config;

    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->config = Config::getInstance();
    }

    /**
     * Authenticate user with username/email and password
     */
    public function authenticate(string $identifier, string $password): ?array
    {
        // Try to find user by username or email
        $user = $this->db->fetch(
            "SELECT * FROM users WHERE username = ? OR mail = ?",
            [$identifier, $identifier]
        );

        if (!$user || !password_verify($password, $user['password'])) {
            return null;
        }

        // Remove password from returned data
        unset($user['password']);
        return $user;
    }

    /**
     * Generate JWT token for user
     */
    public function generateToken(array $user): string
    {
        $config = $this->config->get('jwt');
        
        $payload = [
            'iss' => $config['issuer'],
            'aud' => $config['audience'],
            'iat' => time(),
            'exp' => time() + $config['expires_in'],
            'user_id' => $user['id_user_pk'],
            'username' => $user['username'],
            'email' => $user['mail'],
            'permission_level' => $user['permission_level'] ?? 0
        ];

        return JWT::encode($payload, $config['secret'], $config['algorithm']);
    }

    /**
     * Verify and decode JWT token
     */
    public function verifyToken(string $token): ?array
    {
        try {
            $config = $this->config->get('jwt');
            $decoded = JWT::decode($token, new Key($config['secret'], $config['algorithm']));
            return (array) $decoded;
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Create session record
     */
    public function createSession(int $userId, string $token): bool
    {
        try {
            $this->db->insert('sessions', [
                'id_user_fk' => $userId,
                'session_token' => $token,
                'created_at' => date('Y-m-d H:i:s'),
                'expires_at' => date('Y-m-d H:i:s', time() + $this->config->get('jwt.expires_in'))
            ]);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Destroy session
     */
    public function destroySession(string $token): bool
    {
        try {
            $this->db->delete('sessions', 'session_token = ?', [$token]);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Destroy all user sessions
     */
    public function destroyAllUserSessions(int $userId): bool
    {
        try {
            $this->db->delete('sessions', 'id_user_fk = ?', [$userId]);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Check if session is valid
     */
    public function isSessionValid(string $token): bool
    {
        $session = $this->db->fetch(
            "SELECT * FROM sessions WHERE session_token = ? AND expires_at > NOW()",
            [$token]
        );
        return $session !== null;
    }

    /**
     * Register new user
     */
    public function register(array $userData): ?int
    {
        try {
            // Validate required fields
            $required = ['username', 'mail', 'password'];
            foreach ($required as $field) {
                if (empty($userData[$field])) {
                    throw new \InvalidArgumentException("Field '{$field}' is required");
                }
            }

            // Check if username or email already exists
            $existing = $this->db->fetch(
                "SELECT id_user_pk FROM users WHERE username = ? OR mail = ?",
                [$userData['username'], $userData['mail']]
            );

            if ($existing) {
                throw new \InvalidArgumentException("Username or email already exists");
            }

            // Hash password
            $userData['password'] = password_hash($userData['password'], PASSWORD_DEFAULT);
            
            // Set default values
            $userData['permission_level'] = $userData['permission_level'] ?? 0;
            $userData['created_at'] = date('Y-m-d H:i:s');
            $userData['updated_at'] = date('Y-m-d H:i:s');

            return $this->db->insert('users', $userData);
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Update user password
     */
    public function updatePassword(int $userId, string $newPassword): bool
    {
        try {
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            $updated = $this->db->update(
                'users',
                ['password' => $hashedPassword, 'updated_at' => date('Y-m-d H:i:s')],
                'id_user_pk = ?',
                [$userId]
            );
            return $updated > 0;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Generate password reset token
     */
    public function generatePasswordResetToken(string $email): ?string
    {
        try {
            // Check if user exists
            $user = $this->db->fetch("SELECT id_user_pk FROM users WHERE mail = ?", [$email]);
            if (!$user) {
                return null;
            }

            // Generate token
            $token = bin2hex(random_bytes(32));
            $expiresAt = date('Y-m-d H:i:s', time() + 3600); // 1 hour

            // Save reset token
            $this->db->insert('password_resets', [
                'email' => $email,
                'token' => $token,
                'created_at' => date('Y-m-d H:i:s'),
                'expires_at' => $expiresAt
            ]);

            return $token;
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Verify password reset token
     */
    public function verifyPasswordResetToken(string $token): ?string
    {
        $reset = $this->db->fetch(
            "SELECT email FROM password_resets WHERE token = ? AND expires_at > NOW()",
            [$token]
        );

        return $reset ? $reset['email'] : null;
    }

    /**
     * Reset password with token
     */
    public function resetPasswordWithToken(string $token, string $newPassword): bool
    {
        try {
            $email = $this->verifyPasswordResetToken($token);
            if (!$email) {
                return false;
            }

            // Update password
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            $updated = $this->db->update(
                'users',
                ['password' => $hashedPassword, 'updated_at' => date('Y-m-d H:i:s')],
                'mail = ?',
                [$email]
            );

            if ($updated > 0) {
                // Delete used reset token
                $this->db->delete('password_resets', 'token = ?', [$token]);
                return true;
            }

            return false;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Clean expired sessions
     */
    public function cleanExpiredSessions(): int
    {
        try {
            return $this->db->delete('sessions', 'expires_at < NOW()');
        } catch (\Exception $e) {
            return 0;
        }
    }

    /**
     * Clean expired password reset tokens
     */
    public function cleanExpiredResetTokens(): int
    {
        try {
            return $this->db->delete('password_resets', 'expires_at < NOW()');
        } catch (\Exception $e) {
            return 0;
        }
    }

    /**
     * Get user by ID
     */
    public function getUserById(int $userId): ?array
    {
        $user = $this->db->fetch(
            "SELECT id_user_pk, username, mail, mobile_phone, permission_level, created_at, updated_at FROM users WHERE id_user_pk = ?",
            [$userId]
        );

        return $user ?: null;
    }

    /**
     * Update user profile
     */
    public function updateUserProfile(int $userId, array $data): bool
    {
        try {
            // Remove sensitive fields that shouldn't be updated this way
            unset($data['password'], $data['id_user_pk'], $data['created_at']);
            
            if (empty($data)) {
                return false;
            }

            $data['updated_at'] = date('Y-m-d H:i:s');
            
            $updated = $this->db->update('users', $data, 'id_user_pk = ?', [$userId]);
            return $updated > 0;
        } catch (\Exception $e) {
            return false;
        }
    }
}
