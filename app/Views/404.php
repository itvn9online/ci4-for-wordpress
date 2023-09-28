<?php

//
$base_model->adds_css([
    'wp-includes/css/404.css',
    THEMEPATH . 'css/404.css',
], [
    'cdn' => CDN_BASE_URL,
]);

// nạp view riêng của từng theme nếu có
$theme_default_view = VIEWS_PATH . 'default/' . basename(__FILE__);
//echo $theme_default_view;
// nạp file kiểm tra private view
include VIEWS_PATH . 'private_view.php';

//
$base_model->add_js(THEMEPATH . 'js/404.js', [
    'cdn' => CDN_BASE_URL,
], [
    'defer'
]);
