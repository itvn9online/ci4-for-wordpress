<?php

// tự động tạo slider nếu có
//$post_model->the_slider($data, [], $lang_model->get_the_text('main_slider_slug', ''));

// nạp css dùng chung
$base_model->add_css('themes/' . THEMENAME . '/css/page.css', [
    'cdn' => CDN_BASE_URL,
]);

// nạp view template riêng của từng page nếu có
if ($page_template != '') {
    // nạp css riêng nếu có
    $base_model->add_css(THEMEPATH . 'page-templates/' . $page_template . '.css', [
        'cdn' => CDN_BASE_URL,
    ]);

    // nạp view
    $theme_private_view = THEMEPATH . 'page-templates/' . $page_template . '.php';
    include VIEWS_PATH . 'private_include_view.php';

    // nạp js riêng nếu có
    $base_model->add_js(THEMEPATH . 'page-templates/' . $page_template . '.js', [
        'cdn' => CDN_BASE_URL,
    ], [
        'defer'
    ]);
} else {
    // nạp view riêng của từng theme nếu có
    $theme_default_view = VIEWS_PATH . 'default/' . basename(__FILE__);
    // nạp file kiểm tra private view
    include VIEWS_PATH . 'private_view.php';
}

// nạp js dùng chung
$base_model->add_js('themes/' . THEMENAME . '/js/page.js', [
    'cdn' => CDN_BASE_URL,
], [
    'defer'
]);
