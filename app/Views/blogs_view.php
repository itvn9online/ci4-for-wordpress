<?php

//
//$base_model = new\ App\ Models\ Base();
//$post_model = new\ App\ Models\ Post();
//$term_model = new\ App\ Models\ Term();

// tự động tạo slider nếu có
echo $taxonomy_slider;

// nạp view riêng của từng theme nếu có
$theme_private_view = THEMEPATH . 'Views/' . basename( __FILE__ );
//echo $theme_private_view . '<br>' . "\n";


/*
 * Chuẩn bị dữ liệu để phân trang
 */
//print_r( $ops );
/*
$taxonomy_custom_post_size = '';
if ( isset( $data[ 'term_meta' ][ 'taxonomy_custom_post_size' ] ) && $data[ 'term_meta' ][ 'taxonomy_custom_post_size' ] != '' ) {
    $taxonomy_custom_post_size = $data[ 'term_meta' ][ 'taxonomy_custom_post_size' ];
}
*/
//echo 'taxonomy_post_size: ' . $taxonomy_post_size . '<br>' . "\n";
//echo 'taxonomy_custom_post_size: ' . $taxonomy_custom_post_size . '<br>' . "\n";

//
$post_per_page = $base_model->get_config( $getconfig, 'eb_blogs_per_page', 10 );
//echo $post_per_page . '<br>' . "\n";

//
$totalThread = $post_model->count_blogs_by( $data );
//echo $totalThread . '<br>' . "\n";

if ( $totalThread > 0 ) {
    $totalPage = ceil( $totalThread / $post_per_page );
    if ( $totalPage < 1 ) {
        $totalPage = 1;
    }
    //echo $totalPage . '<br>' . "\n";
    if ( $ops[ 'page_num' ] > $totalPage ) {
        $ops[ 'page_num' ] = $totalPage;
    } else if ( $ops[ 'page_num' ] < 1 ) {
        $ops[ 'page_num' ] = 1;
    }
    //echo $totalThread . '<br>' . "\n";
    //echo $totalPage . '<br>' . "\n";
    $offset = ( $ops[ 'page_num' ] - 1 ) * $post_per_page;

    $public_part_page = $base_model->EBE_pagination( $ops[ 'page_num' ], $totalPage, $term_model->get_the_permalink( $data ) );
} else {
    $public_part_page = '';
}


//
if ( file_exists( $theme_private_view ) ) {
    include $theme_private_view;
}
// không có thì nạp view mặc định
else {
    include __DIR__ . '/default/' . basename( __FILE__ );
}

$base_model->add_js( 'themes/' . THEMENAME . '/js/blogs_list.js', [
    'cdn' => CDN_BASE_URL,
], [
    'defer'
] );