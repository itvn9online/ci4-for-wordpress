<?php

/**
 * file view dành cho các term khác, ngoài các term mặc định được hỗ trợ -> sẽ tự động kiểm tra view trong custom view nếu có thì in ra
 **/

//
//print_r($data);
//print_r( $ops );
//print_r( $getconfig );


/*
 * Chuẩn bị dữ liệu để phân trang
 */
$post_per_page = $base_model->get_config($getconfig, 'eb_posts_per_page', 20);
//$post_per_page = 2;
//echo $post_per_page . '<br>' . PHP_EOL;

//
include __DIR__ . '/term_data_view.php';

// xem có sử dụng view term template không
$term_template = '';
// nếu có dùng template riêng -> dùng luôn
if (isset($data['term_meta']['term_template']) && $data['term_meta']['term_template'] != '') {
    $term_template = $data['term_meta']['term_template'];
}

// xem có sử dụng col HTML riêng không
$term_col_templates = '';
// nếu có dùng col HTML riêng -> dùng luôn
if (isset($data['term_meta']['term_col_templates']) && $data['term_meta']['term_col_templates'] != '') {
    $term_col_templates = file_get_contents(THEMEPATH . 'term-col-templates/' . $data['term_meta']['term_col_templates'], 1);
}

// nạp view template riêng của từng page nếu có
if ($term_template != '') {
    // nạp css riêng nếu có
    $base_model->add_css(THEMEPATH . 'term-templates/' . $term_template . '.css', [
        'cdn' => CDN_BASE_URL,
    ]);

    // nạp view
    $theme_private_view = THEMEPATH . 'term-templates/' . $term_template . '.php';
    include VIEWS_PATH . 'private_include_view.php';

    // nạp js riêng nếu có
    $base_model->add_js(THEMEPATH . 'term-templates/' . $term_template . '.js', [
        'cdn' => CDN_BASE_URL,
    ], [
        'defer'
    ]);
} else {
    // nạp view riêng của từng term nếu có
    //$theme_default_view = VIEWS_PATH . 'default/' . basename(__FILE__);
    $theme_default_view = VIEWS_PATH . 'default/' . $taxonomy . '_view.php';
    // nạp file kiểm tra private view
    include VIEWS_PATH . 'private_view.php';
}


//
$base_model->adds_js([
    'javascript/taxonomy.js',
    'themes/' . THEMENAME . '/js/taxonomy.js',
    'themes/' . THEMENAME . '/js/' . $taxonomy . '_taxonomy.js',
], [
    'cdn' => CDN_BASE_URL,
], [
    'defer'
]);
