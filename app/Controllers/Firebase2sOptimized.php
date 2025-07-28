<?php

namespace App\Controllers;

// Libraries
use App\Libraries\UsersType;
use App\Libraries\DeletedStatus;
// use App\Libraries\PHPMaillerSend;
// use App\Helpers\HtmlTemplate;
use Exception;
// use CodeIgniter\Log\Logger;

/**
 * Optimized Firebase Authentication Controller
 * 
 * Improvements:
 * - Separated concerns into smaller methods
 * - Better error handling
 * - Constants for magic values
 * - Improved security validation
 * - Better code organization
 */
class Firebase2sOptimized extends Firebases
{
    // Constants
    private const MIN_PHONE_LENGTH = 9;
    private const TOKEN_PARTS_COUNT = 3;
    private const UID_SUFFIX_LENGTH = 3;
    private const REQUIRED_JWT_FIELDS = ['user_id', 'aud'];

    // Configuration
    public $preload_header = false;

    // Validation rules
    private array $validationRules = [
        'id_token' => 'required',
        'uid' => 'required',
        'project_id' => 'required',
        'apikey' => 'required',
        'apiurl' => 'required'
    ];

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Main sign in success handler - now much cleaner
     */
    public function sign_in_success($callBack = true): void
    {
        try {
            // Step 1: Security validations
            $this->validateSecurityRequirements();

            // Step 2: Extract and validate input data  
            $inputData = $this->extractAndValidateInput();

            // Step 3: Handle token-only requests
            if ($this->isTokenRequest()) {
                $this->handleTokenRequest($inputData['fb_uid']);
                return;
            }

            // Step 4: Validate cache token
            $this->validateCacheToken($inputData['fb_uid']);

            // Step 5: Process authentication
            $this->processAuthentication($inputData, $callBack);
        } catch (Exception $e) {
            $this->handleException($e);
        }
    }

    /**
     * Validate security requirements (referer, config, method)
     */
    private function validateSecurityRequirements(): void
    {
        $this->validateReferer();
        $this->validateFirebaseConfig();
        $this->validateRequestMethod();
        $this->validateUrlExpires();
    }

    /**
     * Validate HTTP referer
     */
    private function validateReferer(): void
    {
        if (empty($_SERVER['HTTP_REFERER'])) {
            $this->throwError('referer', 'Cannot be determined referer!');
        }

        $refererParts = $this->parseReferer($_SERVER['HTTP_REFERER']);
        if ($refererParts['host'] !== $_SERVER['HTTP_HOST']) {
            $this->throwError('referer_host', 'Referer not suitable!');
        }
    }

    /**
     * Parse referer URL safely
     */
    private function parseReferer(string $referer): array
    {
        $parsed = parse_url($referer);

        if (!$parsed || empty($parsed['host'])) {
            $this->throwError('referer_https', 'Referer không hợp lệ');
        }

        return $parsed;
    }

    /**
     * Validate Firebase configuration
     */
    private function validateFirebaseConfig(): void
    {
        $config = trim($this->firebase_config->g_firebase_config ?? '');
        if (empty($config)) {
            $this->throwError('firebase_config', 'firebase_config chưa được thiết lập');
        }
    }

    /**
     * Validate request method
     */
    private function validateRequestMethod(): void
    {
        if ($_SERVER['REQUEST_METHOD'] != 'POST') {
            $this->result_json_type([
                'code' => __LINE__,
                'error' => 'Bad request!',
            ]);
        }
    }

    /**
     * Validate URL expires
     */
    private function validateUrlExpires(): void
    {
        $this->firebaseUrlExpires(
            $this->MY_post('expires_token'),
            $this->expires_time
        );
    }

    /**
     * Extract and validate all input data
     */
    private function extractAndValidateInput(): array
    {
        $data = [];

        // Validate required fields
        foreach ($this->validationRules as $field => $rule) {
            if ($rule === 'required') {
                $data[$field] = $this->validateRequiredField($field);
            }
        }

        // Extract user data
        $data = array_merge($data, $this->extractUserData());

        // Validate JWT token
        $this->validateJwtToken($data['id_token'], $data['uid']);

        // Validate config parameters
        $this->validateConfigParameters($data);

        return $data;
    }

    /**
     * Validate required field
     */
    private function validateRequiredField(string $field): string
    {
        $value = $this->MY_post($field);
        if (empty($value)) {
            $this->throwError($field, "{$field} is required");
        }
        return $value;
    }

    /**
     * Extract user data from POST
     */
    private function extractUserData(): array
    {
        return [
            'name' => $this->MY_post('name'),
            'email' => trim($this->MY_post('email')),
            'phone' => trim($this->MY_post('phone')),
            'photo' => $this->MY_post('photo'),
            'fb_uid' => $this->MY_post('uid')
        ];
    }

    /**
     * Validate JWT token structure and content
     */
    private function validateJwtToken(string $token, string $expectedUid): void
    {
        $decodedToken = $this->phpJwt($token, $expectedUid);

        // Additional JWT validations can be added here
        if (!$this->isValidJwtStructure($decodedToken)) {
            $this->throwError('jwt_invalid', 'Invalid JWT token structure');
        }
    }

    /**
     * Check if JWT has valid structure
     */
    private function isValidJwtStructure(array $token): bool
    {
        foreach (self::REQUIRED_JWT_FIELDS as $field) {
            if (!isset($token[$field])) {
                return false;
            }
        }
        return true;
    }

    /**
     * Validate configuration parameters
     */
    private function validateConfigParameters(array $data): void
    {
        $configFields = ['project_id', 'apikey', 'apiurl'];

        foreach ($configFields as $field) {
            $this->checkConfigParams($data[$field], [
                'code' => __LINE__,
                'error' => $this->firebaseLang($field, "{$field} not suitable!")
            ]);
        }
    }

    /**
     * Check if this is a token-only request
     */
    private function isTokenRequest(): bool
    {
        return !empty($this->MY_post('token_url'));
    }

    /**
     * Handle token-only requests
     */
    private function handleTokenRequest(string $uid): void
    {
        $this->sign_in_token($uid);
    }

    /**
     * Validate cache token
     */
    private function validateCacheToken(string $expectedUid): void
    {
        $cachedToken = $this->id_cache_token();

        if ($cachedToken !== $expectedUid) {
            $this->result_json_type([
                'code' => __LINE__,
                'auto_logout' => __LINE__,
                'fb_uid' => $expectedUid,
                'token' => $cachedToken,
                'error' => $this->firebaseLang('cache_token', 'Identity verification error! Please try again...')
            ]);
        }
    }

    /**
     * Process the main authentication logic
     */
    private function processAuthentication(array $inputData, bool $callBack): void
    {
        // Find existing user
        $existingUser = $this->findUserByEmailOrPhone(
            $inputData['email'],
            $inputData['phone']
        );

        if ($existingUser) {
            $this->handleExistingUser($existingUser, $inputData);
        } else {
            $this->handleNewUser($inputData, $callBack);
        }

        $this->result_json_type(['ok' => __LINE__]);
    }

    /**
     * Find user by email or phone
     */
    private function findUserByEmailOrPhone(string $email, string $phone): ?array
    {
        $where = ['is_deleted' => DeletedStatus::FOR_DEFAULT];
        $whereLike = [];

        if ($this->isValidEmail($email)) {
            $where['user_email'] = $email;
        } elseif ($this->isValidPhone($phone)) {
            $whereLike['user_phone'] = substr($phone, -self::MIN_PHONE_LENGTH);
        } else {
            $this->throwError('email_or_phone', 'Cannot determine Email or Phone number');
        }

        $user = $this->base_model->select(
            '*',
            $this->user_model->table,
            $where,
            [
                'like_before' => $whereLike,
                'order_by' => ['ID' => 'ASC'],
                'limit' => 1
            ]
        );

        return $user ?: null;
    }

    /**
     * Validate email format
     */
    private function isValidEmail(string $email): bool
    {
        return !empty($email) && strpos($email, '@') !== false && filter_var($email, FILTER_VALIDATE_EMAIL);
    }

    /**
     * Validate phone format
     */
    private function isValidPhone(string $phone): bool
    {
        return !empty($phone) && strlen($phone) > self::MIN_PHONE_LENGTH;
    }

    /**
     * Handle existing user authentication
     */
    private function handleExistingUser(array $user, array $inputData): void
    {
        if (empty($user['firebase_uid'])) {
            $this->handleFirstTimeFirebaseUser($user, $inputData);
        } else {
            $this->validateExistingFirebaseUser($user, $inputData['fb_uid']);
        }

        $this->finalizeUserLogin($user);
    }

    /**
     * Handle first-time Firebase user
     */
    private function handleFirstTimeFirebaseUser(array $user, array $inputData): void
    {
        $this->checkUid($inputData['fb_uid']);

        if ($this->shouldVerifyEmail()) {
            $this->handleEmailVerification($user, $inputData);
        } else {
            $this->updateFirebaseUid($user['ID'], $inputData['fb_uid']);
        }
    }

    /**
     * Check if email verification is required
     */
    private function shouldVerifyEmail(): bool
    {
        return $this->firebase_config->skipverify_firebase_email !== 'on';
    }

    /**
     * Handle email verification process
     */
    private function handleEmailVerification(array $user, array $inputData): void
    {
        if ($this->isValidEmail($inputData['email'])) {
            $this->reVerifyFirebaseEmail($user, ['uid' => $inputData['fb_uid']]);
        } elseif ($this->isValidPhone($inputData['phone'])) {
            $this->updateFirebaseUid($user['ID'], $inputData['fb_uid'], 'phone');
        } else {
            $this->throwError('empty_email', 'Tài khoản không thể kích hoạt vì thiếu email');
        }
    }

    /**
     * Update Firebase UID for user
     */
    private function updateFirebaseUid(int $userId, string $firebaseUid, string $source = 'system'): void
    {
        $this->user_model->update_member($userId, [
            'firebase_uid' => $this->base_model->mdnam($firebaseUid),
            'firebase_source_uid' => $this->generateSourceString($source)
        ]);
    }

    /**
     * Generate source string for tracking
     */
    private function generateSourceString(string $source): string
    {
        return date('r') . "|{$source}|" . __CLASS__ . '|' . debug_backtrace()[1]['function'] . ':' . __LINE__;
    }

    /**
     * Validate existing Firebase user
     */
    private function validateExistingFirebaseUser(array $user, string $firebaseUid): void
    {
        $hashedUid = $this->base_model->mdnam($firebaseUid);

        if ($user['firebase_uid'] !== $hashedUid) {
            if ($this->shouldVerifyEmail()) {
                $this->reVerifyFirebaseEmail($user, ['uid' => $firebaseUid]);
            }
            $this->throwError('user_id_mismatched', 'uid không đúng');
        }
    }

    /**
     * Finalize user login process
     */
    private function finalizeUserLogin(array $user): void
    {
        // Sync login data
        $user = $this->sync_login_data($user);

        // Update login information
        $this->updateLoginInfo($user['ID']);

        // Enable account if not permanently locked
        $this->enableUserAccount($user['ID']);

        // Update avatar if needed
        $this->updateUserAvatar($user);

        // Set login session
        $this->base_model->set_ses_login($user);
    }

    /**
     * Update user login information
     */
    private function updateLoginInfo(int $userId): void
    {
        $this->user_model->update_member($userId, [
            'last_login' => date(EBE_DATETIME_FORMAT),
            'login_type' => UsersType::FIREBASE,
            'member_verified' => UsersType::VERIFIED
        ]);
    }

    /**
     * Enable user account if conditions are met
     */
    private function enableUserAccount(int $userId): void
    {
        $this->user_model->update_member($userId, [
            'user_status' => UsersType::FOR_DEFAULT
        ], [
            'user_status !=' => UsersType::NO_LOGIN,
            'is_deleted' => DeletedStatus::FOR_DEFAULT
        ]);
    }

    /**
     * Update user avatar if empty
     */
    private function updateUserAvatar(array $user): void
    {
        if (empty($user['avatar']) && !empty($_POST['photo'])) {
            $this->user_model->update_member($user['ID'], [
                'avatar' => $_POST['photo']
            ]);
        }
    }

    /**
     * Handle new user registration
     */
    private function handleNewUser(array $inputData, bool $callBack): void
    {
        $this->checkUid($inputData['fb_uid']);

        $userData = $this->prepareNewUserData($inputData);
        $insertResult = $this->user_model->insert_member($userData);

        $this->handleInsertResult($insertResult, $callBack);
    }

    /**
     * Prepare data for new user creation
     */
    private function prepareNewUserData(array $inputData): array
    {
        $email = $inputData['email'];
        if (empty($email)) {
            $email = substr($inputData['phone'], -self::MIN_PHONE_LENGTH) . '@' . $_SERVER['HTTP_HOST'];
        }

        return [
            'email' => $email,
            'user_email' => $email,
            'display_name' => $inputData['name'],
            'user_phone' => $inputData['phone'],
            'member_type' => UsersType::GUEST,
            'avatar' => $inputData['photo'],
            'firebase_uid' => $this->base_model->mdnam($inputData['fb_uid']),
            'member_verified' => UsersType::VERIFIED
        ];
    }

    /**
     * Handle user insert result
     */
    private function handleInsertResult($insertResult, bool $callBack): void
    {
        if ($insertResult < 0) {
            $this->throwError('email_used', 'Email đã được sử dụng');
        } elseif ($insertResult !== false) {
            if ($callBack) {
                $this->sign_in_success(false);
                return;
            }
            $this->result_json_type(['ok' => 0]);
        } else {
            $this->throwError('error_create', 'Lỗi đăng ký tài khoản');
        }
    }

    /**
     * Improved JWT validation with better error handling
     */
    protected function phpJwt(string $jwt, string $uid = ''): array
    {
        $parts = explode('.', $jwt);

        if (count($parts) !== self::TOKEN_PARTS_COUNT) {
            $this->throwError('jwt_format', 'Invalid JWT format');
        }

        [$headersB64, $payloadB64, $sig] = $parts;

        $decoded = $this->decodeJwtPayload($payloadB64);

        $this->validateJwtContent($decoded, $uid);

        return $decoded;
    }

    /**
     * Decode JWT payload safely
     */
    private function decodeJwtPayload(string $payloadB64): array
    {
        // Fix base64 padding
        $payloadB64 = str_replace(['-', '_'], ['+', '/'], $payloadB64);

        $decoded = json_decode(base64_decode($payloadB64), true);

        if (!is_array($decoded)) {
            $this->throwError('decoded_array', 'Định dạng decoded không đúng');
        }

        return $decoded;
    }

    /**
     * Validate JWT content
     */
    private function validateJwtContent(array $decoded, string $uid): void
    {
        if (!isset($decoded['user_id'])) {
            $this->throwError('user_id_isset', 'Cannot be determined user_id');
        }

        if (!empty($uid) && $uid !== $decoded['user_id']) {
            $this->throwError('user_id_uid', 'uid không hợp lệ');
        }

        // Additional JWT validations can be added here
        $this->validateJwtExpiration($decoded);
    }

    /**
     * Validate JWT expiration
     */
    private function validateJwtExpiration(array $decoded): void
    {
        if (isset($decoded['exp']) && time() > $decoded['exp']) {
            $this->throwError('token_expired', 'Token has expired');
        }
    }

    /**
     * Improved error handling
     */
    private function throwError(string $key, string $defaultMessage, array $params = []): void
    {
        $this->result_json_type([
            'code' => __LINE__,
            'error' => $this->firebaseLang($key, $defaultMessage, $params)
        ]);
    }

    /**
     * Handle exceptions
     */
    private function handleException(Exception $e): void
    {
        // For now, just return error response
        // You can add proper logging later based on your logging setup
        $this->result_json_type([
            'code' => __LINE__,
            'error' => 'An unexpected error occurred. Please try again.',
            'debug' => $e->getMessage() // Remove this in production
        ]);
    }

    // Keep other existing methods...
    protected function checkUid($fb_uid)
    {
        $data = $this->base_model->select(
            'ID, user_email, user_login, user_phone, member_type',
            $this->user_model->table,
            ['firebase_uid' => $this->base_model->mdnam($fb_uid)],
            ['limit' => 1]
        );

        if (!empty($data)) {
            if ($this->isDeletedUser($data)) {
                $this->clearFirebaseUid($data['ID']);
                return true;
            }

            $this->throwError('check_uid', 'uid has been used by a {member_type} #{ID}', $data);
        }

        return true;
    }

    /**
     * Check if user is in deleted state
     */
    private function isDeletedUser(array $user): bool
    {
        $deletedMarker = DeletedStatus::FOR_TRASH;

        return strpos($user['user_email'], $deletedMarker) !== false ||
            strpos($user['user_login'], $deletedMarker) !== false ||
            strpos($user['user_phone'], $deletedMarker) !== false;
    }

    /**
     * Clear Firebase UID for deleted user
     */
    private function clearFirebaseUid(int $userId): void
    {
        $this->user_model->update_member($userId, [
            'firebase_uid' => '',
            'firebase_source_uid' => ''
        ]);
    }

    public function firebase_config()
    {
        if (!empty($this->firebase_config->firebase_json_config)) {
            $this->result_json_type([
                'ok' => __LINE__,
                'data' => json_decode($this->firebase_config->firebase_json_config)
            ]);
        }

        $this->result_json_type([
            'code' => __LINE__,
            'error' => 'No money no love!'
        ]);
    }
}
