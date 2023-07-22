<?php

//
//print_r( $data );
//print_r( $getconfig );


// tự động tạo slider nếu có
echo $taxonomy_slider;


/*
 * chế độ hiển thị nhóm con trong nhóm cha
 */
if (
    $getconfig->show_child_category == 'on' &&
    isset($data['child_term']) &&
    !empty($data['child_term'])
) {
    /*
     * nạp view riêng của từng theme nếu có
     */
    //$theme_default_view = VIEWS_PATH . 'default/' . basename(__FILE__);
    $theme_default_view = VIEWS_PATH . 'default/category_child_view.php';
    // nạp file kiểm tra private view
    include VIEWS_PATH . 'private_view.php';

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
}
// chế độ hiển thì trực tiếp bài viết của nhóm hiện tại
else {
    // khai báo tham số để phân biệt nơi nạp term view
    //define('IN_CATEGORY_VIEW', true);
    if ($debug_enable === true) {
        echo '<div class="wgr-view-path">' . str_replace(PUBLIC_HTML_PATH, '', __DIR__ . '/term_view.php') . '</div>';
    }

    // -> dùng của term view luôn
    include __DIR__ . '/term_view.php';
}
