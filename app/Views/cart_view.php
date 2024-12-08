<?php

//
$base_model->JSON_parse([
    'cart_config' => [
        'coupon_code' => $coupon_code,
        'coupon_amount' => $coupon_amount,
        'shippings_fee' => $getconfig->shippings_fee,
        'deposits_money' => $getconfig->deposits_money,
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
    'shop_cart_id' => $shop_id,
]);


// các file functions sẽ được nạp trước
$base_model->adds_js([
    'wp-includes/javascript/cart_functions.js',
    THEMEPATH . 'js/cart_functions.js',
], [
    'cdn' => CDN_BASE_URL,
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
