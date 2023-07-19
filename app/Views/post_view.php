<?php

//
$base_model->add_css('themes/' . THEMENAME . '/css/post.css', [
    //'get_content' => 1,
    //'preload' => 1,
    'cdn' => CDN_BASE_URL,
]);

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
]);

//
$base_model->adds_js([
    'javascript/posts_functions.js',
    'themes/' . THEMENAME . '/js/post.js'
], [
    'cdn' => CDN_BASE_URL,
], [
    'defer'
]);
