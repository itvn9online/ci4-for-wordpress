<?php

//
//print_r($data);
//print_r( $getconfig );


//
$base_model->get_the_custom_html($getconfig, 'html_products_header', $data['term_id']);


// tự động tạo slider nếu có
//echo $taxonomy_slider;


/**
 * Chuẩn bị dữ liệu để phân trang
 */
$post_per_page = $base_model->get_config($getconfig, 'eb_products_per_page', 20);
//$post_per_page = 2;
//echo $post_per_page . '<br>' . "\n";

//
include __DIR__ . '/term_data_view.php';


/**
 * nạp view riêng của từng theme nếu có
 **/
$theme_default_view = VIEWS_PATH . 'default/' . basename(__FILE__);
// nạp file kiểm tra private view
include VIEWS_PATH . 'private_view.php';

//
$base_model->adds_js([
    'wp-includes/javascript/taxonomy.js',
    THEMEPATH . 'js/taxonomy.js',
    THEMEPATH . 'js/' . $taxonomy . '_taxonomy.js',
], [
    'cdn' => CDN_BASE_URL,
], [
    'defer'
]);


//
$base_model->get_the_custom_html($getconfig, 'html_products_body', $data['term_id']);
