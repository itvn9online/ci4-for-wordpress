<?php

// tự động tạo slider nếu có
//$post_model->the_slider($data, $taxonomy_slider, $lang_model->get_the_text('main_slider_slug', ''));

// nạp view riêng của từng theme nếu có
$theme_default_view = VIEWS_PATH . 'default/' . basename(__FILE__);
// nạp file kiểm tra private view
include VIEWS_PATH . 'private_view.php';

//
$base_model->add_js('themes/' . THEMENAME . '/js/blog_details.js', [
    'cdn' => CDN_BASE_URL,
], [
    'defer'
]);
