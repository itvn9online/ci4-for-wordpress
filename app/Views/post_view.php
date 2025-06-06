<?php

//
$base_model->add_css(THEMEPATH . 'css/post.css', [
    //'get_content' => 1,
    //'preload' => 1,
    'cdn' => CDN_BASE_URL,
]);

//
$base_model->get_the_custom_html($getconfig, 'html_post_header', $data['ID']);

// tự động tạo slider nếu có
//$post_model->the_slider($data, $taxonomy_slider, $lang_model->get_the_text('main_slider_slug', ''));

// nạp view riêng của từng theme nếu có
$theme_default_view = VIEWS_PATH . 'default/' . basename(__FILE__);
// nạp file kiểm tra private view
include VIEWS_PATH . 'private_view.php';

//
$base_model->JSON_echo([
    'post_id' => $data['ID'],
    'post_author' => $data['post_author'],
    // custom fake view
    'custom_fview' => CUSTOM_FAKE_POST_VIEW,
]);

//
$base_model->adds_js([
    'wp-includes/javascript/posts_functions.js',
    THEMEPATH . 'js/post.js'
], [
    'cdn' => CDN_BASE_URL,
], [
    'defer'
]);

//
if ($getconfig->post_toc == 'on') {
    $base_model->add_css('wp-includes/css/post_toc.css', [
        'cdn' => CDN_BASE_URL,
    ], [
        'defer'
    ]);

    // 
    $base_model->add_js('wp-includes/javascript/post_toc.js', [
        'cdn' => CDN_BASE_URL,
    ], [
        'defer'
    ]);
}

//
$base_model->get_the_custom_html($getconfig, 'html_post_body', $data['ID']);
