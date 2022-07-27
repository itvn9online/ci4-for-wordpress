<?php

/*
 * file search mặc định -> ưu tiên post type = post
 */

//
//print_r( $data );

/*
 * thử xem có file search riêng theo từng post type không
 */
// -> ưu tiên trong theme riêng trước
$theme_private_view = VIEWS_CUSTOM_PATH . 'default/' . $post_type . '-' . basename( __FILE__ );

//
if ( file_exists( $theme_private_view ) ) {
    //echo $theme_private_view . '<br>' . "\n";
    include $theme_private_view;

    // nạp file js cho từng search post type (nếu có)
    $base_model->add_js( 'themes/' . THEMENAME . '/js/search-' . $post_type . '.js', [
        'cdn' => CDN_BASE_URL,
    ], [
        'defer'
    ] );
}
//
else {
    // thử tìm file search riêng (dạng dùng chung)
    $search_type_view = __DIR__ . '/' . $post_type . '-' . basename( __FILE__ );

    // có thì ưu tiên dùng
    if ( file_exists( $search_type_view ) ) {
        //echo $search_type_view . '<br>' . "\n";
        include $search_type_view;

        // nạp file js cho từng search post type (nếu có)
        $base_model->add_js( 'themes/' . THEMENAME . '/js/search-' . $post_type . '.js', [
            'cdn' => CDN_BASE_URL,
        ], [
            'defer'
        ] );
    }
    // không có thì mới chuyển sảng view mặc định (loại dùng chung cho toàn bộ post type)
    else {
        // nạp view riêng của từng theme nếu có
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
}

// nạp file js dùng chung cho search (nếu có)
$base_model->add_js( 'themes/' . THEMENAME . '/js/search.js', [
    'cdn' => CDN_BASE_URL,
], [
    'defer'
] );