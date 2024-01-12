<?php

//
$base_model->get_the_custom_html($getconfig, 'html_product_header', $data['ID']);

// tự động tạo slider nếu có
//$post_model->the_slider($data, $taxonomy_slider, $lang_model->get_the_text('main_slider_slug', ''));

//
//print_r($data);

// nạp view riêng của từng theme nếu có
$theme_default_view = VIEWS_PATH . 'default/' . basename(__FILE__);
// nạp file kiểm tra private view
include VIEWS_PATH . 'private_view.php';

//
$base_model->JSON_echo([
    'post_id' => $data['ID'],
    'post_author' => $data['post_author'],
    // custom fake view
    'custom_fview' => 60,
]);

//
$base_model->adds_js([
    'wp-includes/javascript/posts_functions.js',
    THEMEPATH . 'js/product.js'
], [
    'cdn' => CDN_BASE_URL,
], [
    'defer'
]);

//
$base_model->get_the_custom_html($getconfig, 'html_product_body', $data['ID']);
