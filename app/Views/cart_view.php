<?php

//
$base_model->JSON_parse([
    'cart_config' => [
        'coupon_code' => $coupon_code,
        'coupon_amount' => $coupon_amount,
        'shipping_fee' => $getconfig->shipping_fee,
        'deposit_money' => $getconfig->deposit_money,
        'free_shipping' => $lang_model->get_the_text('shipping_free', 'Free shipping'),
        'calculated_later' => $lang_model->get_the_text('calculated_later', 'Calculated later'),
    ],
]);

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
    'wp-includes/javascript/datetimepicker.js',
    'wp-includes/javascript/cart.js',
    THEMEPATH . 'js/cart.js',
], [
    'cdn' => CDN_BASE_URL,
], [
    'defer'
]);
