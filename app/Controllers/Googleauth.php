<?php

namespace App\Controllers;

use App\Libraries\UsersType;
use App\Libraries\DeletedStatus;
use Exception;

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

        // trong admin thì luôn bật hiển thị lỗi cho dễ làm việc
        ini_set('display_errors', 1);
        error_reporting(E_ALL);
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
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            throw new Exception('Invalid request method');
        }

        if (
            empty($_SERVER['HTTP_REFERER']) ||
            parse_url($_SERVER['HTTP_REFERER'])['host'] !== $_SERVER['HTTP_HOST']
        ) {
            throw new Exception('Invalid referer');
        }
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

        // Decode payload
        $payload = $parts[1];
        $payload = str_replace(['-', '_'], ['+', '/'], $payload);
        $payload = base64_decode($payload);
        $data = json_decode($payload, true);

        if (!$data) {
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
        $required = ['sub', 'email', 'name', 'aud'];

        foreach ($required as $field) {
            if (!isset($data[$field])) {
                throw new Exception("Missing required field: {$field}");
            }
        }

        // Validate client ID
        if ($data['aud'] !== $this->googleClientId) {
            throw new Exception('Invalid audience');
        }

        // Check expiration
        if (isset($data['exp']) && time() > $data['exp']) {
            throw new Exception('Token expired');
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
        $context = stream_context_create([
            'http' => [
                'timeout' => 10,
                'user_agent' => 'GoogleAuth/1.0'
            ]
        ]);

        $result = file_get_contents($url, false, $context);

        return $result ? json_decode($result, true) : null;
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
        return [
            'user_email' => $googleData['email'],
            'email' => $googleData['email'],
            'display_name' => $googleData['name'],
            'user_login' => $this->generateUsername($googleData['email']),
            'member_type' => UsersType::GUEST,
            'avatar' => $googleData['picture'] ?? '',
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

        // Update avatar if empty
        if (!empty($googleData['picture'])) {
            $user = $this->base_model->select(
                'avatar',
                $this->user_model->table,
                ['ID' => $userId],
                ['limit' => 1]
            );

            if (empty($user['avatar'])) {
                $updates['avatar'] = $googleData['picture'];
            }
        }

        $this->user_model->update_member($userId, $updates);
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
                'callback_url' => base_url('googleauth/signin')
            ]
        ]);
    }
}
