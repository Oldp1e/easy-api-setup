<?php

namespace Src\Controllers;

use Src\Core\BaseController;
use Src\Services\AuthService;

/**
 * @OA\Info(
 *     title="Generic API Template",
 *     version="1.0.0",
 *     description="Generic API template with authentication and common functionality"
 * )
 * @OA\SecurityScheme(
 *     securityScheme="bearerAuth",
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="JWT"
 * )
 */
class GenericAuthController extends BaseController
{
    private $authService;

    public function __construct()
    {
        parent::__construct();
        $this->authService = new AuthService();
    }

    /**
     * @OA\Post(
     *     path="/auth/login",
     *     summary="User login",
     *     tags={"Authentication"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"identifier", "password"},
     *             @OA\Property(property="identifier", type="string", description="Username or email"),
     *             @OA\Property(property="password", type="string", description="User password")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Login successful",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean"),
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="token", type="string"),
     *                 @OA\Property(property="user", type="object")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=400, description="Invalid input"),
     *     @OA\Response(response=401, description="Invalid credentials")
     * )
     */
    public function login()
    {
        $data = $this->getRequestData();
        
        // Validate required fields
        $errors = $this->validateRequired($data, ['identifier', 'password']);
        if (!empty($errors)) {
            $this->error('Validation failed', 400, $errors);
        }

        // Authenticate user
        $user = $this->authService->authenticate($data['identifier'], $data['password']);
        if (!$user) {
            $this->error('Invalid credentials', 401);
        }

        // Generate JWT token
        $token = $this->authService->generateToken($user);
        
        // Create session
        if (!$this->authService->createSession($user['id_user_pk'], $token)) {
            $this->error('Failed to create session', 500);
        }

        $this->logActivity('user_login', ['user_id' => $user['id_user_pk']]);

        $this->success([
            'token' => $token,
            'user' => $user
        ], 'Login successful');
    }

    /**
     * @OA\Post(
     *     path="/auth/register",
     *     summary="User registration",
     *     tags={"Authentication"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"username", "email", "password"},
     *             @OA\Property(property="username", type="string"),
     *             @OA\Property(property="email", type="string", format="email"),
     *             @OA\Property(property="password", type="string", minLength=6),
     *             @OA\Property(property="mobile_phone", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="User registered successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean"),
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="user_id", type="integer")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=400, description="Invalid input or user already exists")
     * )
     */
    public function register()
    {
        $data = $this->getRequestData();
        
        // Validate required fields
        $errors = $this->validateRequired($data, ['username', 'email', 'password']);
        if (!empty($errors)) {
            $this->error('Validation failed', 400, $errors);
        }

        // Validate email format
        if (!$this->validateEmail($data['email'])) {
            $this->error('Invalid email format', 400);
        }

        // Validate password strength
        if (strlen($data['password']) < 6) {
            $this->error('Password must be at least 6 characters long', 400);
        }

        // Sanitize data
        $userData = $this->sanitizeArray([
            'username' => $data['username'],
            'mail' => $data['email'],
            'password' => $data['password'],
            'mobile_phone' => $data['mobile_phone'] ?? null
        ]);

        // Register user
        $userId = $this->authService->register($userData);
        if (!$userId) {
            $this->error('Failed to register user. Username or email may already exist.', 400);
        }

        $this->logActivity('user_register', ['user_id' => $userId]);

        $this->success([
            'user_id' => $userId
        ], 'User registered successfully', 201);
    }

    /**
     * @OA\Post(
     *     path="/auth/logout",
     *     summary="User logout",
     *     tags={"Authentication"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Logout successful"
     *     ),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */
    public function logout()
    {
        $userInfo = $this->requireAuth();
        
        // Get token from header
        $headers = getallheaders();
        $authHeader = $headers['Authorization'] ?? '';
        preg_match('/Bearer\s(\S+)/', $authHeader, $matches);
        $token = $matches[1] ?? '';

        if ($token && $this->authService->destroySession($token)) {
            $this->logActivity('user_logout', ['user_id' => $userInfo['user_id']]);
            $this->success(null, 'Logout successful');
        }

        $this->error('Failed to logout', 500);
    }

    /**
     * @OA\Get(
     *     path="/auth/me",
     *     summary="Get current user profile",
     *     tags={"Authentication"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="User profile data",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */
    public function me()
    {
        $userInfo = $this->requireAuth();
        $user = $this->authService->getUserById($userInfo['user_id']);
        
        if (!$user) {
            $this->error('User not found', 404);
        }

        $this->success($user);
    }

    /**
     * @OA\Put(
     *     path="/auth/profile",
     *     summary="Update user profile",
     *     tags={"Authentication"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="username", type="string"),
     *             @OA\Property(property="email", type="string", format="email"),
     *             @OA\Property(property="mobile_phone", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Profile updated successfully"
     *     ),
     *     @OA\Response(response=400, description="Invalid input"),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */
    public function updateProfile()
    {
        $userInfo = $this->requireAuth();
        $data = $this->getRequestData();

        // Validate email if provided
        if (isset($data['email']) && !$this->validateEmail($data['email'])) {
            $this->error('Invalid email format', 400);
        }

        // Sanitize data
        $updateData = [];
        $allowedFields = ['username', 'mail', 'mobile_phone'];
        
        foreach ($allowedFields as $field) {
            if (isset($data[$field])) {
                $updateData[$field] = $this->sanitizeString($data[$field]);
            }
        }

        // Map email field
        if (isset($data['email'])) {
            $updateData['mail'] = $this->sanitizeString($data['email']);
            unset($updateData['email']);
        }

        if (empty($updateData)) {
            $this->error('No valid fields to update', 400);
        }

        if ($this->authService->updateUserProfile($userInfo['user_id'], $updateData)) {
            $this->logActivity('profile_update', ['user_id' => $userInfo['user_id']]);
            $this->success(null, 'Profile updated successfully');
        }

        $this->error('Failed to update profile', 500);
    }

    /**
     * @OA\Post(
     *     path="/auth/request-reset",
     *     summary="Request password reset",
     *     tags={"Authentication"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email"},
     *             @OA\Property(property="email", type="string", format="email")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Reset email sent (if email exists)"
     *     ),
     *     @OA\Response(response=400, description="Invalid input")
     * )
     */
    public function requestPasswordReset()
    {
        $data = $this->getRequestData();
        
        // Validate required fields
        $errors = $this->validateRequired($data, ['email']);
        if (!empty($errors)) {
            $this->error('Validation failed', 400, $errors);
        }

        if (!$this->validateEmail($data['email'])) {
            $this->error('Invalid email format', 400);
        }

        // Generate reset token (always return success for security)
        $token = $this->authService->generatePasswordResetToken($data['email']);
        
        if ($token) {
            // Here you would send the reset email
            // For now, we'll just log it
            $this->logActivity('password_reset_requested', ['email' => $data['email']]);
        }

        // Always return success to prevent email enumeration
        $this->success(null, 'If the email exists, a reset link has been sent');
    }

    /**
     * @OA\Post(
     *     path="/auth/reset-password",
     *     summary="Reset password with token",
     *     tags={"Authentication"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"token", "password"},
     *             @OA\Property(property="token", type="string"),
     *             @OA\Property(property="password", type="string", minLength=6)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Password reset successful"
     *     ),
     *     @OA\Response(response=400, description="Invalid input or token")
     * )
     */
    public function resetPassword()
    {
        $data = $this->getRequestData();
        
        // Validate required fields
        $errors = $this->validateRequired($data, ['token', 'password']);
        if (!empty($errors)) {
            $this->error('Validation failed', 400, $errors);
        }

        // Validate password strength
        if (strlen($data['password']) < 6) {
            $this->error('Password must be at least 6 characters long', 400);
        }

        if ($this->authService->resetPasswordWithToken($data['token'], $data['password'])) {
            $this->logActivity('password_reset_completed', ['token' => substr($data['token'], 0, 8) . '...']);
            $this->success(null, 'Password reset successful');
        }

        $this->error('Invalid or expired reset token', 400);
    }

    /**
     * @OA\Post(
     *     path="/auth/change-password",
     *     summary="Change password",
     *     tags={"Authentication"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"current_password", "new_password"},
     *             @OA\Property(property="current_password", type="string"),
     *             @OA\Property(property="new_password", type="string", minLength=6)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Password changed successfully"
     *     ),
     *     @OA\Response(response=400, description="Invalid input"),
     *     @OA\Response(response=401, description="Invalid current password")
     * )
     */
    public function changePassword()
    {
        $userInfo = $this->requireAuth();
        $data = $this->getRequestData();
        
        // Validate required fields
        $errors = $this->validateRequired($data, ['current_password', 'new_password']);
        if (!empty($errors)) {
            $this->error('Validation failed', 400, $errors);
        }

        // Validate new password strength
        if (strlen($data['new_password']) < 6) {
            $this->error('New password must be at least 6 characters long', 400);
        }

        // Get current user data
        $user = $this->authService->getUserById($userInfo['user_id']);
        if (!$user) {
            $this->error('User not found', 404);
        }

        // Verify current password
        $fullUser = $this->db->fetch("SELECT password FROM users WHERE id_user_pk = ?", [$userInfo['user_id']]);
        if (!password_verify($data['current_password'], $fullUser['password'])) {
            $this->error('Current password is incorrect', 401);
        }

        if ($this->authService->updatePassword($userInfo['user_id'], $data['new_password'])) {
            $this->logActivity('password_changed', ['user_id' => $userInfo['user_id']]);
            $this->success(null, 'Password changed successfully');
        }

        $this->error('Failed to change password', 500);
    }
}
