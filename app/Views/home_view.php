<?php

//
$base_model->add_css('themes/' . THEMENAME . '/css/home.css', [
    //'get_content' => 1,
    //'preload' => 1,
    'cdn' => CDN_BASE_URL,
]);

// nạp view riêng của từng theme nếu có
$theme_default_view = VIEWS_PATH . 'default/' . basename(__FILE__);
// nạp file kiểm tra private view
include VIEWS_PATH . 'private_view.php';

//
$base_model->add_js('themes/' . THEMENAME . '/js/home.js', [
    'cdn' => CDN_BASE_URL,
], [
    'defer'
]);
