<?php

namespace App\Controllers;

use App\Libraries\UsersType;
use App\Libraries\DeletedStatus;
use Exception;
use Config\Services;

/**
 * Google Identity Services Authentication Controller
 * Đăng nhập trực tiếp bằng Google Sign-In API (không qua Firebase)
 */
class Googleauth extends Guest
{
    // Constants
    private const GOOGLE_API_URL = 'https://www.googleapis.com/oauth2/v3/tokeninfo';
    private const MIN_PHONE_LENGTH = 9;
    private const TOKEN_CACHE_TIME = 3600; // 1 hour

    // Google Client ID from config
    private string $googleClientId;

    public function __construct()
    {
        parent::__construct();
        $this->googleClientId = $this->getGoogleClientId();

        // SECURITY: Never enable display_errors in production!
        // ini_set('display_errors', 1);
        // error_reporting(E_ALL);
    }

    /**
     * Main Google Sign-In endpoint
     */
    public function signIn(): void
    {
        try {
            // Validate request
            $this->validateRequest();

            // Get and validate token
            $idToken = $this->getIdToken();
            $userData = $this->validateGoogleToken($idToken);

            // Process user authentication
            $this->processGoogleAuth($userData);

            $this->result_json_type(['ok' => __LINE__]);
        } catch (Exception $e) {
            $this->handleError($e->getMessage());
        }
    }

    /**
     * Validate request basics
     */
    private function validateRequest(): void
    {
        // Check request method
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            throw new Exception('Invalid request method');
        }

        // Validate referer (can be spoofed, additional checks needed)
        if (empty($_SERVER['HTTP_REFERER'])) {
            throw new Exception('Missing referer');
        }

        $refererHost = parse_url($_SERVER['HTTP_REFERER'], PHP_URL_HOST);
        if ($refererHost !== $_SERVER['HTTP_HOST']) {
            throw new Exception('Invalid referer');
        }

        // SECURITY: Rate limiting check
        $this->checkRateLimit();
    }

    /**
     * Check rate limiting to prevent brute force attacks
     */
    private function checkRateLimit(): void
    {
        $ip = $this->getClientIP();
        $cacheKey = 'google_auth_attempts_' . md5($ip);

        // Use session-based rate limiting as fallback
        if (!isset($_SESSION['google_auth_attempts'])) {
            $_SESSION['google_auth_attempts'] = [];
        }

        $attempts = $_SESSION['google_auth_attempts'][$ip] ?? 0;

        if ($attempts > 10) { // Max 10 attempts per session
            throw new Exception('Too many login attempts. Please try again later.');
        }

        $_SESSION['google_auth_attempts'][$ip] = $attempts + 1;
    }

    /**
     * Get client IP address safely
     */
    private function getClientIP(): string
    {
        $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';

        // Check for proxy headers (be careful with these as they can be spoofed)
        if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ips = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
            $ip = trim($ips[0]);
        }

        return filter_var($ip, FILTER_VALIDATE_IP) ? $ip : '0.0.0.0';
    }

    /**
     * Get ID token from request
     */
    private function getIdToken(): string
    {
        $token = $this->MY_post('credential') ?: $this->MY_post('id_token');

        if (empty($token)) {
            throw new Exception('Missing Google credential token');
        }

        return $token;
    }

    /**
     * Validate Google token and get user data
     */
    private function validateGoogleToken(string $idToken): array
    {
        // First, try to decode JWT locally (faster)
        $userData = $this->decodeGoogleJWT($idToken);

        // Verify with Google API for security
        $this->verifyWithGoogleAPI($idToken);

        return $userData;
    }

    /**
     * Decode Google JWT token locally
     */
    private function decodeGoogleJWT(string $idToken): array
    {
        $parts = explode('.', $idToken);

        if (count($parts) !== 3) {
            throw new Exception('Invalid JWT format');
        }

        // SECURITY: Validate token length to prevent DoS
        if (strlen($idToken) > 2048) {
            throw new Exception('Token too long');
        }

        // Decode payload
        $payload = $parts[1];
        $payload = str_replace(['-', '_'], ['+', '/'], $payload);
        $decoded = base64_decode($payload, true);

        if ($decoded === false) {
            throw new Exception('Invalid base64 encoding');
        }

        $data = json_decode($decoded, true);

        if (!is_array($data)) {
            throw new Exception('Invalid JWT payload');
        }

        // Validate required fields
        $this->validateGoogleUserData($data);

        return $data;
    }

    /**
     * Validate Google user data
     */
    private function validateGoogleUserData(array $data): void
    {
        $required = ['sub', 'email', 'name', 'aud', 'iss'];

        foreach ($required as $field) {
            if (!isset($data[$field]) || empty($data[$field])) {
                throw new Exception("Missing required field: {$field}");
            }
        }

        // SECURITY: Validate issuer (must be Google)
        $validIssuers = ['https://accounts.google.com', 'accounts.google.com'];
        if (!in_array($data['iss'], $validIssuers)) {
            throw new Exception('Invalid token issuer');
        }

        // SECURITY: Validate client ID (audience)
        if ($data['aud'] !== $this->googleClientId) {
            throw new Exception('Invalid audience');
        }

        // SECURITY: Validate email format
        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            throw new Exception('Invalid email format');
        }

        // SECURITY: Check email verified status
        if (isset($data['email_verified']) && $data['email_verified'] !== true) {
            throw new Exception('Email not verified by Google');
        }

        // SECURITY: Check token expiration with buffer
        if (!isset($data['exp']) || !is_numeric($data['exp'])) {
            throw new Exception('Missing or invalid expiration time');
        }

        if (time() > $data['exp']) {
            throw new Exception('Token expired');
        }

        // SECURITY: Check issued at time (prevent future tokens)
        if (isset($data['iat']) && $data['iat'] > time() + 60) {
            throw new Exception('Token issued in the future');
        }

        // SECURITY: Validate sub (Google user ID) format
        if (!preg_match('/^[0-9]+$/', $data['sub'])) {
            throw new Exception('Invalid Google user ID format');
        }
    }

    /**
     * Verify token with Google API
     */
    private function verifyWithGoogleAPI(string $idToken): void
    {
        $url = self::GOOGLE_API_URL . '?id_token=' . urlencode($idToken);

        $response = $this->makeHttpRequest($url);

        if (!$response || isset($response['error'])) {
            throw new Exception('Google token verification failed');
        }
    }

    /**
     * Make HTTP request to Google API
     */
    private function makeHttpRequest(string $url): ?array
    {
        // SECURITY: Validate URL is Google's domain
        $parsedUrl = parse_url($url);
        if ($parsedUrl['host'] !== 'www.googleapis.com') {
            throw new Exception('Invalid API URL');
        }

        // SECURITY: Use HTTPS only
        if ($parsedUrl['scheme'] !== 'https') {
            throw new Exception('HTTPS required for API calls');
        }

        $context = stream_context_create([
            'http' => [
                'timeout' => 10,
                'user_agent' => 'GoogleAuth/1.0',
                'method' => 'GET',
                'ignore_errors' => false
            ],
            'ssl' => [
                'verify_peer' => true,
                'verify_peer_name' => true,
                'allow_self_signed' => false
            ]
        ]);

        $result = @file_get_contents($url, false, $context);

        if ($result === false) {
            throw new Exception('Failed to verify token with Google API');
        }

        $data = json_decode($result, true);

        if (!is_array($data)) {
            throw new Exception('Invalid API response');
        }

        return $data;
    }

    /**
     * Process Google authentication
     */
    private function processGoogleAuth(array $googleData): void
    {
        $email = $googleData['email'];
        $googleId = $googleData['sub'];

        // Find existing user
        $existingUser = $this->findUserByEmail($email);

        if ($existingUser) {
            $this->handleExistingGoogleUser($existingUser, $googleData);
        } else {
            $this->handleNewGoogleUser($googleData);
        }
    }

    /**
     * Find user by email
     */
    private function findUserByEmail(string $email): ?array
    {
        $user = $this->base_model->select(
            '*',
            $this->user_model->table,
            [
                'user_email' => $email,
                'is_deleted' => DeletedStatus::FOR_DEFAULT
            ],
            ['limit' => 1]
        );

        return $user ?: null;
    }

    /**
     * Handle existing Google user
     */
    private function handleExistingGoogleUser(array $user, array $googleData): void
    {
        // Update Google ID if not set
        if (empty($user['google_id'])) {
            $this->updateGoogleId($user['ID'], $googleData['sub']);
        } else if ($user['google_id'] !== $this->hashGoogleId($googleData['sub'])) {
            throw new Exception('Google ID mismatch');
        }

        // Update user info
        $this->updateUserInfo($user['ID'], $googleData);

        // Finalize login
        $this->finalizeLogin($user);
    }

    /**
     * Handle new Google user
     */
    private function handleNewGoogleUser(array $googleData): void
    {
        $userData = $this->prepareGoogleUserData($googleData);
        $userId = $this->user_model->insert_member($userData);

        if ($userId <= 0) {
            throw new Exception('Failed to create user account');
        }

        // Get created user and login
        $user = $this->findUserByEmail($googleData['email']);
        $this->finalizeLogin($user);
    }

    /**
     * Prepare user data for Google registration
     */
    private function prepareGoogleUserData(array $googleData): array
    {
        // SECURITY: Sanitize all user inputs
        $email = filter_var($googleData['email'], FILTER_SANITIZE_EMAIL);
        $name = htmlspecialchars(strip_tags($googleData['name']), ENT_QUOTES, 'UTF-8');
        $picture = filter_var($googleData['picture'] ?? '', FILTER_SANITIZE_URL);

        // SECURITY: Validate picture URL is from Google
        if (!empty($picture) && strpos($picture, 'https://lh3.googleusercontent.com') !== 0) {
            $picture = ''; // Only accept Google profile pictures
        }

        return [
            'user_email' => $email,
            'email' => $email,
            'display_name' => substr($name, 0, 100), // Limit length
            'user_login' => $this->generateUsername($email),
            'member_type' => UsersType::GUEST,
            'avatar' => $picture,
            'google_id' => $this->hashGoogleId($googleData['sub']),
            'member_verified' => UsersType::VERIFIED,
            'user_status' => UsersType::FOR_DEFAULT,
            'google_source' => $this->generateSourceString()
        ];
    }

    /**
     * Generate unique username from email
     */
    private function generateUsername(string $email): string
    {
        $base = explode('@', $email)[0];
        $base = preg_replace('/[^a-zA-Z0-9]/', '', $base);

        // Check if username exists
        $counter = 0;
        $username = $base;

        while ($this->usernameExists($username)) {
            $counter++;
            $username = $base . $counter;
        }

        return $username;
    }

    /**
     * Check if username exists
     */
    private function usernameExists(string $username): bool
    {
        $user = $this->base_model->select(
            'ID',
            $this->user_model->table,
            ['user_login' => $username],
            ['limit' => 1]
        );

        return !empty($user);
    }

    /**
     * Update Google ID for existing user
     */
    private function updateGoogleId(int $userId, string $googleId): void
    {
        $this->user_model->update_member($userId, [
            'google_id' => $this->hashGoogleId($googleId),
            'google_source' => $this->generateSourceString()
        ]);
    }

    /**
     * Hash Google ID for storage
     */
    private function hashGoogleId(string $googleId): string
    {
        return $this->base_model->mdnam($googleId);
    }

    /**
     * Update user information
     */
    private function updateUserInfo(int $userId, array $googleData): void
    {
        $updates = [
            'last_login' => date('Y-m-d H:i:s'),
            'login_type' => 'google',
            'member_verified' => UsersType::VERIFIED
        ];

        // SECURITY: Sanitize and validate avatar URL
        if (!empty($googleData['picture'])) {
            $picture = filter_var($googleData['picture'], FILTER_SANITIZE_URL);

            // Only accept Google profile pictures
            if (strpos($picture, 'https://lh3.googleusercontent.com') === 0) {
                $user = $this->base_model->select(
                    'avatar',
                    $this->user_model->table,
                    ['ID' => $userId],
                    ['limit' => 1]
                );

                if (empty($user['avatar'])) {
                    $updates['avatar'] = $picture;
                }
            }
        }

        $this->user_model->update_member($userId, $updates);

        // SECURITY: Log login activity
        $this->logLoginActivity($userId, 'google', true);
    }

    /**
     * Log login activity for security audit
     */
    private function logLoginActivity(int $userId, string $provider, bool $success): void
    {
        $logData = [
            'user_id' => $userId,
            'provider' => $provider,
            'success' => $success,
            'ip_address' => $this->getClientIP(),
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown',
            'timestamp' => date('Y-m-d H:i:s')
        ];

        // Log to file or database
        // log_message('info', 'Google Auth: ' . json_encode($logData));
        error_log('Google Auth: ' . json_encode($logData));
    }

    /**
     * Finalize user login
     */
    private function finalizeLogin(array $user): void
    {
        // Sync login data
        $user = $this->sync_login_data($user);

        // Set session
        $this->base_model->set_ses_login($user);

        // Enable account if needed
        $this->user_model->update_member($user['ID'], [
            'user_status' => UsersType::FOR_DEFAULT
        ], [
            'user_status !=' => UsersType::NO_LOGIN,
            'is_deleted' => DeletedStatus::FOR_DEFAULT
        ]);
    }

    /**
     * Generate source tracking string
     */
    private function generateSourceString(): string
    {
        return date('r') . '|google|' . __CLASS__ . ':' . __LINE__;
    }

    /**
     * Get Google Client ID from config
     */
    private function getGoogleClientId(): string
    {
        // You can get this from your config or database
        return $this->firebase_config->google_client_id ?? '';
    }

    /**
     * Handle errors
     */
    private function handleError(string $message): void
    {
        $this->result_json_type([
            'error' => $message,
            'code' => __LINE__
        ]);
    }

    /**
     * Get configuration for frontend
     */
    public function getConfig(): void
    {
        if (empty($this->googleClientId)) {
            $this->result_json_type([
                'error' => 'Google Client ID not configured'
            ]);
        }

        $this->result_json_type([
            'ok' => __LINE__,
            'data' => [
                'client_id' => $this->googleClientId,
                'callback_url' => DYNAMIC_BASE_URL . 'googleauth/signin'
            ]
        ]);
    }
}
