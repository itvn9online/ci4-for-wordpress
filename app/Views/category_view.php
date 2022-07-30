<?php

//
//print_r( $data );
//print_r( $getconfig );

// tự động tạo slider nếu có
echo $taxonomy_slider;


/*
 * chế độ hiển thị nhóm con trong nhóm cha
 */
if ( $getconfig->show_child_category == 'on' &&
    isset( $data[ 'child_term' ] ) &&
    !empty( $data[ 'child_term' ] )
) {
    /*
     * nạp view riêng của từng theme nếu có
     */
    $theme_default_view = VIEWS_PATH . 'default/' . basename( __FILE__ );
    $theme_private_view = str_replace( VIEWS_PATH, VIEWS_CUSTOM_PATH, $theme_default_view );

    //
    if ( file_exists( $theme_private_view ) ) {
        //echo $theme_private_view . '<br>' . "\n";
        include $theme_private_view;
    }
    // không có thì nạp view mặc định
    else {
        //echo $theme_default_view . '<br>' . "\n";
        include $theme_default_view;
    }
}
// chế độ hiển thì trực tiếp bài viết của nhóm hiện tại
else {
    // khai báo tham số để phân biệt nơi nạp term view
    define( 'IN_CATEGORY_VIEW', true );

    // -> dùng của term view luôn
    include __DIR__ . '/term_view.php';
}

//
$base_model->add_js( 'themes/' . THEMENAME . '/js/taxonomy.js', [
    'cdn' => CDN_BASE_URL,
], [
    'defer'
] );