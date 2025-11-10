<?php

/**
 * Google Identity Services Authentication View
 * Song song với Firebase Authentication
 */

// Kiểm tra cấu hình Google Client ID
$google_client_id = $firebase_config->google_client_id ?? '';

if (!empty($google_client_id)) {
?>
    <!-- Google Identity Services CDN -->
    <script src="https://accounts.google.com/gsi/client" async defer></script>

    <!-- Google Auth Styles -->
    <style>
        .google-auth-container {
            margin: 20px 0;
            padding: 20px;
            border: 1px solid #e0e0e0;
            /* border-radius: 8px; */
            background: #f9f9f9;
        }

        .google-auth-title {
            font-size: 16px;
            font-weight: 600;
            margin-bottom: 15px;
            color: #333;
        }

        .google-auth-loading {
            display: none;
            text-align: center;
            padding: 20px;
        }

        .google-auth-error {
            display: none;
            color: #d32f2f;
            background: #ffebee;
            padding: 10px;
            border-radius: 4px;
            margin-top: 10px;
        }

        .google-auth-success {
            display: none;
            margin-top: 10px;
        }

        .google-signin-divider {
            text-align: center;
            margin: 20px 0;
            position: relative;
        }

        .google-signin-divider::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 0;
            right: 0;
            height: 1px;
            background: #e0e0e0;
        }

        .google-signin-divider span {
            background: #f9f9f9;
            padding: 0 15px;
            color: #666;
            font-size: 14px;
        }

        .google-signin-wrapper {
            /* display: flex; */
            /* justify-content: center; */
            /* margin: 30px 0; */
            /* min-height: 50px; */
            max-width: 260px;
            margin: 0 auto;
            /* display: inline-block; */
        }
    </style>

    <!-- Google Authentication Container -->
    <div class="google-auth-container">
        <div class="google-auth-title">
            <?php $lang_model->the_text('google_signin_title', 'Hoặc đăng nhập bằng Google'); ?>
        </div>

        <div class="google-signin-wrapper">
            <!-- Loading State -->
            <div id="google-auth-loading" class="google-auth-loading">
                <div class="spinner-border spinner-border-sm" role="status">
                    <span class="sr-only">Loading...</span>
                </div>
                <span>Đang xử lý...</span>
            </div>

            <!-- Google Sign-In Button -->
            <div id="google-signin-button"></div>
        </div>

        <!-- Error Message -->
        <div id="google-auth-error" class="google-auth-error"></div>

        <!-- Success Message -->
        <div id="google-auth-success" class="google-auth-success"></div>
    </div>

    <script>
        // Google Auth Configuration
        window.google_auth_config = {
            client_id: '<?php echo $google_client_id; ?>',
            callback_url: '<?php echo DYNAMIC_BASE_URL . 'googleauth/signin'; ?>',
            redirect_url: '<?php echo ($firebase_config->google_redirect_url ?? ''); ?>',
            show_one_tap: <?php echo ($firebase_config->google_show_one_tap ?? 'off') === 'on' ? 'true' : 'false'; ?>,
            auto_select: <?php echo ($firebase_config->google_auto_select ?? 'off') === 'on' ? 'true' : 'false'; ?>,
            cancel_on_tap_outside: true,
            context: 'signin'
        };

        // CSRF Token if available
        <?php if (isset($csrf_token)) { ?>
            window.csrf_token = <?php echo json_encode($csrf_token); ?>;
        <?php } ?>

        // Dispatch config ready event
        window.dispatchEvent(new CustomEvent('googleConfigReady'));
    </script>

<?php
    // Load Google Auth JavaScript
    $base_model->adds_js([
        'wp-includes/javascript/google-auth.js'
    ], [
        'cdn' => CDN_BASE_URL,
    ], [
        'defer'
    ]);
}
