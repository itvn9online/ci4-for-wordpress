<?php

//
$base_model->adds_css([
    'wp-includes/css/cart.css',
    THEMEPATH . 'css/cart.css',
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
    'product_cart_id' => $product_id,
]);


//
$base_model->adds_js([
    'wp-includes/javascript/cart.js',
    THEMEPATH . 'js/cart.js',
], [
    'cdn' => CDN_BASE_URL,
], [
    'defer'
]);
