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
$theme_private_view = THEMEPATH . 'Views/' . $post_type . '-' . basename( __FILE__ );
//echo $theme_private_view . '<br>' . "\n";

//
if ( file_exists( $theme_private_view ) ) {
    include $theme_private_view;

    // nạp file js cho từng search post type (nếu có)
    $base_model->add_js( 'themes/' . THEMENAME . '/js/search-' . $post_type . '.js' );
}
//
else {
    // thử tìm file search riêng (dạng dùng chung)
    $search_type_view = __DIR__ . '/' . $post_type . '-' . basename( __FILE__ );
    //echo $search_type_view . '<br>' . "\n";

    // có thì ưu tiên dùng
    if ( file_exists( $search_type_view ) ) {
        include $search_type_view;

        // nạp file js cho từng search post type (nếu có)
        $base_model->add_js( 'themes/' . THEMENAME . '/js/search-' . $post_type . '.js' );
    }
    // không có thì mới chuyển sảng view mặc định (loại dùng chung cho toàn bộ post type)
    else {
        // nạp view riêng của từng theme nếu có
        $theme_private_view = THEMEPATH . 'Views/' . basename( __FILE__ );
        //echo $theme_private_view . '<br>' . "\n";

        //
        if ( file_exists( $theme_private_view ) ) {
            include $theme_private_view;
        }
        // không có thì nạp view mặc định
        else {
            include __DIR__ . '/default/' . basename( __FILE__ );
        }
    }
}

// nạp file js dùng chung cho search (nếu có)
$base_model->add_js( 'themes/' . THEMENAME . '/js/search.js' );