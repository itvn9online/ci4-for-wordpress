<?php

//
$base_model->adds_css([
    'public/css/login.css',
    'themes/' . THEMENAME . '/css/login.css',
], [
        'cdn' => CDN_BASE_URL,
    ]);

// nạp view riêng của từng theme nếu có
$theme_default_view = VIEWS_PATH . 'default/' . basename(__FILE__);
// nạp file kiểm tra private view
include VIEWS_PATH . 'private_view.php';

//
$base_model->JSON_echo([
    // mảng này sẽ in ra dưới dạng JSON hoặc number
], [
        // mảng này sẽ in ra dưới dạng string
        'set_login' => $set_login,
    ]);

//
$base_model->adds_js([
    'javascript/login.js',
    'themes/' . THEMENAME . '/js/login.js',
], [
        'cdn' => CDN_BASE_URL,
    ], [
        'defer'
    ]);