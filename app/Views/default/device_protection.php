<?php

// lưu session id của người dùng vào file
$user_model->setLogged($current_user_id);

// Nạp url cho request ajax
$base_model->JSON_echo([
    // mảng này sẽ in ra dưới dạng JSON hoặc number
], [
    // mảng này sẽ in ra dưới dạng string
    // request_multi_logout -> Ajaxs\multi_logout
    'rmlogout' => RAND_MULTI_LOGOUT,
    // request_multi_logged -> Ajaxs\multi_logged
    'rmlogged' => RAND_MULTI_LOGGED,
]);

// nạp js cảnh báo đăng nhập
$base_model->add_js('wp-includes/javascript/device_protection.js', [
    'cdn' => CDN_BASE_URL,
], [
    'defer'
]);
