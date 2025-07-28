# Hướng dẫn Implementation Firebase Optimization

## 1. Files đã được tối ưu hóa

### Backend (PHP):

- `Firebase2sOptimized.php` - Controller tối ưu hóa thay thế cho `Firebase2s.php`

### Frontend (JavaScript):

- `functions_optimized.js` - JavaScript functions tối ưu hóa
- `config_unified.js` - Configuration đơn giản hóa
- `base64.js` - Giữ nguyên (đã tối ưu)
- `app.js` - Có thể giữ nguyên hoặc thay thế bằng functions_optimized.js

## 2. Cách triển khai (Implementation)

### Bước 1: Backup files hiện tại

```bash
# Backup controller
cp app/Controllers/Firebase2s.php app/Controllers/Firebase2s_backup.php

# Backup JavaScript files
cp public/wp-includes/javascript/firebasejs/functions.js public/wp-includes/javascript/firebasejs/functions_backup.js
cp public/wp-includes/javascript/firebasejs/app.js public/wp-includes/javascript/firebasejs/app_backup.js
```

### Bước 2: Option A - Thay thế hoàn toàn (Recommended)

#### 2.1 Thay thế Controller

```php
// Rename Firebase2s.php to Firebase2s_old.php
// Rename Firebase2sOptimized.php to Firebase2s.php
```

#### 2.2 Thay thế JavaScript

Trong view files (VD: `app/Views/phone_auth_view.php`), thay đổi:

```html
<!-- Cũ -->
<script src="<?= base_url('wp-includes/javascript/firebasejs/functions.js') ?>"></script>
<script src="<?= base_url('wp-includes/javascript/firebasejs/app.js') ?>"></script>
<script src="<?= base_url('wp-includes/javascript/firebasejs/phone_auth.js') ?>"></script>
<script src="<?= base_url('wp-includes/javascript/firebasejs/dynamic_auth.js') ?>"></script>

<!-- Mới -->
<script src="<?= base_url('wp-includes/javascript/firebasejs/base64.js') ?>"></script>
<script src="<?= base_url('wp-includes/javascript/firebasejs/functions_optimized.js') ?>"></script>
<script src="<?= base_url('wp-includes/javascript/firebasejs/config_unified.js') ?>"></script>
```

### Bước 3: Option B - Sử dụng song song (Safe approach)

#### 3.1 Tạo route mới

```php
// Trong app/Config/Routes.php
$routes->post('firebase2s-opt/sign_in_success', 'Firebase2sOptimized::sign_in_success');
$routes->get('firebase2s-opt/verify_email', 'Firebase2sOptimized::verify_email');
$routes->get('firebase2s-opt/firebase_config', 'Firebase2sOptimized::firebase_config');
```

#### 3.2 Tạo view mới cho testing

```php
// Copy phone_auth_view.php thành phone_auth_optimized_view.php
// Update JavaScript includes như trong Option A
```

#### 3.3 Tạo method mới trong Firebases.php

```php
public function phone_auth_optimized()
{
    $this->teamplate['main'] = view(
        'phone_auth_optimized_view',
        array(
            // ... same parameters as phone_auth
        )
    );
    return view('layout_view', $this->teamplate);
}
```

## 3. Testing

### 3.1 Test Cases cần kiểm tra

1. **Đăng nhập bằng số điện thoại**

   - Nhập số điện thoại hợp lệ
   - Nhập số điện thoại không hợp lệ
   - Xác thực OTP

2. **Đăng nhập bằng email**

   - Email mới (chưa có tài khoản)
   - Email đã tồn tại
   - Email verification

3. **Đăng nhập social**

   - Google, Facebook, etc.
   - Tài khoản mới vs tài khoản cũ

4. **Security tests**

   - Invalid tokens
   - Expired tokens
   - CSRF protection
   - Referer validation

5. **Error handling**
   - Network errors
   - Server errors
   - Invalid configurations

### 3.2 Test Script mẫu

```javascript
// Test trong browser console
async function testFirebaseAuth() {
	try {
		console.log("Testing Firebase configuration...");

		// Test utils
		const phone = window.FirebaseAuth.Utils.normalizePhoneNumber("0123456789");
		console.log("Normalized phone:", phone);

		// Test config building
		const config = window.FirebaseAuth.ConfigBuilder.getUiConfig();
		console.log("UI Config:", config);

		console.log("All tests passed!");
	} catch (error) {
		console.error("Test failed:", error);
	}
}

// Chạy test
testFirebaseAuth();
```

## 4. Monitoring và Debugging

### 4.1 Enable Debug Mode

```javascript
// Thêm vào đầu functions_optimized.js
const DEBUG_MODE = true; // Set to false in production

if (DEBUG_MODE) {
	window.FirebaseDebug = {
		logAuthState: true,
		logTokens: true,
		logErrors: true,
	};
}
```

### 4.2 Check Console Logs

Kiểm tra browser console cho:

- Authentication state changes
- Token validation
- Error messages
- Performance metrics

### 4.3 Server Logs

Kiểm tra CodeIgniter logs cho:

- Authentication attempts
- Token validation errors
- Database operations

## 5. Performance Optimization

### 5.1 JavaScript Loading

```html
<!-- Load Firebase scripts async -->
<script async src="path/to/firebase-app.js"></script>
<script async src="path/to/firebase-auth.js"></script>
<script async src="path/to/firebaseui.js"></script>

<!-- Load custom scripts after Firebase -->
<script
	defer
	src="<?= base_url('wp-includes/javascript/firebasejs/functions_optimized.js') ?>"
></script>
```

### 5.2 Caching Strategy

```php
// Trong Controller
public function firebase_config()
{
    // Add caching
    $cacheKey = 'firebase_config_' . md5($this->firebase_config->firebase_json_config);
    $cached = cache()->get($cacheKey);

    if ($cached) {
        $this->result_json_type($cached);
    }

    // Generate config
    $config = [
        'ok' => __LINE__,
        'data' => json_decode($this->firebase_config->firebase_json_config)
    ];

    // Cache for 1 hour
    cache()->save($cacheKey, $config, 3600);

    $this->result_json_type($config);
}
```

## 6. Security Enhancements

### 6.1 Rate Limiting

```php
// Thêm vào Firebase2sOptimized.php
private function checkRateLimit(): void
{
    $key = 'firebase_attempts_' . $this->getClientIP();
    $attempts = cache()->get($key) ?? 0;

    if ($attempts > 10) { // Max 10 attempts per hour
        $this->throwError('rate_limit', 'Too many attempts. Please try again later.');
    }

    cache()->save($key, $attempts + 1, 3600);
}
```

### 6.2 Enhanced Token Validation

```php
private function validateTokenSecurity(array $decoded): void
{
    // Check token age
    if (isset($decoded['iat']) && (time() - $decoded['iat']) > 3600) {
        $this->throwError('token_too_old', 'Token is too old');
    }

    // Check audience
    if ($decoded['aud'] !== $this->firebase_config->project_id) {
        $this->throwError('invalid_audience', 'Invalid token audience');
    }

    // Check issuer
    $expectedIssuer = 'https://securetoken.google.com/' . $this->firebase_config->project_id;
    if ($decoded['iss'] !== $expectedIssuer) {
        $this->throwError('invalid_issuer', 'Invalid token issuer');
    }
}
```

## 7. Migration Checklist

- [ ] Backup all existing files
- [ ] Deploy optimized files
- [ ] Update view includes
- [ ] Test all authentication flows
- [ ] Check error handling
- [ ] Verify security measures
- [ ] Monitor performance
- [ ] Update documentation
- [ ] Train team members
- [ ] Plan rollback strategy

## 8. Rollback Plan

Nếu có vấn đề, rollback ngay:

```bash
# Restore controller
mv app/Controllers/Firebase2s_backup.php app/Controllers/Firebase2s.php

# Restore JavaScript
mv public/wp-includes/javascript/firebasejs/functions_backup.js public/wp-includes/javascript/firebasejs/functions.js

# Clear cache
php spark cache:clear
```

## 9. Support và Maintenance

- Regularly update Firebase SDK
- Monitor authentication metrics
- Review error logs weekly
- Update security measures
- Performance optimization review monthly

Sau khi implement, system sẽ có:

- ✅ Better code organization
- ✅ Improved security
- ✅ Better error handling
- ✅ Enhanced performance
- ✅ Easier maintenance
- ✅ Better debugging capabilities
