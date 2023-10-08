<?php

// lưu session id của người dùng vào file
$user_model->setLogged($current_user_id);

// Nạp url cho request ajax
$base_model->JSON_parse([
    '_rqrm' => [
        // request_multi_logout -> Ajaxs\multi_logout
        'logout' => RAND_MULTI_LOGOUT,
        // request_multi_logged -> Ajaxs\multi_logged
        'logged' => RAND_MULTI_LOGGED,
        // request_confirm_logged -> Ajaxs\confirm_logged
        'cflogged' => RAND_CONFIRM_LOGGED,
        // timeout_device_protection
        'timeout_dp' => 30,
        // logout_device_protection
        'logout_dp' => '',
    ]
]);

// nạp js cảnh báo đăng nhập
$base_model->add_js('wp-includes/javascript/device_protection.js', [
    'cdn' => CDN_BASE_URL,
], [
    'defer'
]);
