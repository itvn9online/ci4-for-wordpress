<?php
/*
* File view chức năng đăng nhập qua firebase
*/

//
if ($getconfig->g_firebase_config != '') {
    //print_r($session_data);

    //
    $firebase_language_code = $getconfig->g_firebase_language_code;
    if ($firebase_language_code == '') {
        $firebase_language_code = 'vi';
    }

    //
?>
    <script src="https://www.gstatic.com/firebasejs/8.10.1/firebase-app.js"></script>
    <script src="https://www.gstatic.com/firebasejs/8.10.1/firebase-auth.js"></script>
    <script>
        <?php
        echo $getconfig->g_firebase_config;
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
    <div id="loading">Loading...</div>
    <div id="loaded" class="hidden">
        <div id="main">
            <div id="user-signed-in" class="hidden">
                <div id="firebase_user-info">
                    <p>Name: <span id="firebase_name"></span></p>
                    <p>Email: <span id="firebase_email"></span></p>
                    <p>Phone: <span id="firebase_phone"></span></p>
                </div>
                <div>
                    <button type="button" id="sign-out" class="btn btn-secondary"><?php $lang_model->the_text('firebase_sign_out', 'Đăng xuất'); ?></button>
                    <button type="button" id="delete-account" class="btn btn-danger d-none"><?php $lang_model->the_text('firebase_delete_account', 'Xóa tài khoản'); ?></button>
                </div>
            </div>
            <div id="user-signed-out" class="hidden">
                <div id="firebaseui-spa">
                    <div id="firebaseui-container"></div>
                </div>
            </div>
        </div>
    </div>
<?php

    //
    $base_model->JSON_echo([
        // mảng này sẽ in ra dưới dạng JSON hoặc number
    ], [
        // mảng này sẽ in ra dưới dạng string
        'firebase_privacy_policy_url' => $getconfig->g_firebase_privacy_policy_url,
        'firebase_terms_service_url' => $getconfig->g_firebase_terms_service_url,
        'firebase_default_country' => $getconfig->g_firebase_default_country,
        'firebase_login_hint' => $getconfig->g_firebase_login_hint,
        //
        'firebase_auth_google' => $getconfig->firebase_auth_google,
        'firebase_auth_facebook' => $getconfig->firebase_auth_facebook,
        'firebase_auth_twitter' => $getconfig->firebase_auth_twitter,
        'firebase_auth_github' => $getconfig->firebase_auth_github,
        'firebase_auth_email' => $getconfig->firebase_auth_email,
        'firebase_auth_anonymous' => $getconfig->firebase_auth_anonymous,
        'firebase_auth_phone' => $getconfig->firebase_auth_phone,
        //
        'default_national_number' => $current_user_id > 0 ? $session_data['user_phone'] : '',
        'sign_in_success_url' => $sign_in_success_url,
    ]);

    // các function sẽ sử dụng cho quá trình đăng nhập qua firebase
    $base_model->adds_js([
        'javascript/firebasejs/functions.js',
        'javascript/firebasejs/' . $file_auth . '.js',
        'javascript/firebasejs/app.js',
    ], [
        'cdn' => CDN_BASE_URL,
    ], [
        'defer'
    ]);
} else {
?>
    <p class="medium18 text-center">SDK setup and configuration is EMPTY!
        <br>Please find <b>g_firebase_config</b> in base code and setup...
    </p>
<?php
}
