<?php
/*
* File view chức năng đăng nhập qua firebase
*/
//print_r($firebase_config);

//
if (!empty($firebase_config->g_firebase_config)) {
    //print_r($session_data);

    //
    $firebase_language_code = $firebase_config->g_firebase_language_code;
    if ($firebase_language_code == '') {
        $firebase_language_code = 'vi';
    }

    //
?>
    <script src="https://www.gstatic.com/firebasejs/8.10.1/firebase-app.js"></script>
    <script src="https://www.gstatic.com/firebasejs/8.10.1/firebase-auth.js"></script>
    <script>
        <?php
        echo $firebase_config->g_firebase_config;
        ?>
    </script>
    <script src="https://www.gstatic.com/firebasejs/ui/6.0.2/firebase-ui-auth__<?php echo $firebase_language_code; ?>.js"></script>
    <link type="text/css" rel="stylesheet" href="https://www.gstatic.com/firebasejs/ui/6.0.2/firebase-ui-auth.css" />
    <?php

    //
    $base_model->add_css('javascript/firebasejs/style.css', [
        'cdn' => CDN_BASE_URL,
    ]);

    ?>
    <br>
    <div id="firebase-loading">Loading...</div>
    <div id="firebase-loaded" class="hidden">
        <div id="user-signed-in" class="hidden">
            <p><?php $lang_model->the_text('firebase_loged_title', 'Hoặc tiếp tục với thông tin đăng nhập này:'); ?></p>
            <div id="firebase_photo"></div>
            <div id="firebase_name"></div>
            <div id="firebase_email"></div>
            <div id="firebase_phone"></div>
            <br>
            <div>
                <button type="button" id="sign-in" onclick="return continueSignIn();" class="btn btn-primary"><?php $lang_model->the_text('firebase_sign_in', 'Kết nối'); ?></button>
                <button type="button" id="sign-out" onclick="return firebaseSignOut('<?php $lang_model->the_text('firebase_confirm_logout', 'Bạn thật sự muốn thoát tài khoản này!'); ?>');" class="btn btn-secondary"><?php $lang_model->the_text('firebase_sign_out', 'Hủy bỏ'); ?></button>
                <button type="button" id="delete-account" onclick="return firebaseDeleteAccountt('<?php $lang_model->the_text('firebase_confirm_delete', 'Xác nhận xóa tài khoản khỏi website này!'); ?>');" class="btn btn-danger d-none"><?php $lang_model->the_text('firebase_delete_account', 'Xóa'); ?></button>
            </div>
        </div>
        <div id="user-signed-out" class="hidden">
            <div id="firebaseui-container"></div>
            <label>
                <input type="checkbox" id="firebase_auto_login" /> Tự động đăng nhập lần sau
            </label>
        </div>
    </div>
    <br>
<?php

    //
    $base_model->JSON_parse([
        'sign_in_success_params' => $sign_in_success_params,
        'token_expires_time' => $expires_time,
        'firebase_dynamic_config' => [
            'privacy_policy_url' => $firebase_config->g_firebase_privacy_policy_url,
            'terms_service_url' => $firebase_config->g_firebase_terms_service_url,
            'sign_in_redirect_to' => $firebase_config->firebase_sign_in_redirect_to,
            'default_country' => $firebase_config->g_firebase_default_country,
            'login_hint' => $firebase_config->g_firebase_login_hint,
            'save_session' => $firebase_config->save_firebase_session,
            //
            'google' => $firebase_config->firebase_auth_google,
            'facebook' => $firebase_config->firebase_auth_facebook,
            'apple' => $firebase_config->firebase_auth_apple,
            'microsoft' => $firebase_config->firebase_auth_microsoft,
            'yahoo' => $firebase_config->firebase_auth_yahoo,
            'twitter' => $firebase_config->firebase_auth_twitter,
            'github' => $firebase_config->firebase_auth_github,
            'email' => $firebase_config->firebase_auth_email,
            'anonymous' => $firebase_config->firebase_auth_anonymous,
            'phone' => $firebase_config->firebase_auth_phone,
            'verify_phone' => $firebase_config->firebase_verify_phone,
            //
            'default_national_number' => $current_user_id > 0 ? $session_data['user_phone'] : (isset($phone_number) ? $phone_number : ''),
        ],
    ]);

    // các function sẽ sử dụng cho quá trình đăng nhập qua firebase
    $base_model->adds_js([
        'javascript/firebasejs/base64.js',
        'javascript/firebasejs/functions.js',
        'javascript/firebasejs/' . $file_auth . '.js',
        'javascript/firebasejs/app.js',
    ], [
        'cdn' => CDN_BASE_URL,
    ], [
        'defer'
    ]);
}
