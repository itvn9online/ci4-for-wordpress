<?php
/*
 * file view dành cho các term khác, ngoài các term mặc định được hỗ trợ
 */

//
//use App\ Libraries\ LanguageCost;
//use App\ Libraries\ PostType;


//
//$base_model = new\ App\ Models\ Base();
//$post_model = new\ App\ Models\ Post();
//$term_model = new\ App\ Models\ Term();

//
//print_r( $data );
//print_r( $ops );
//print_r( $getconfig );

// tự động tạo slider nếu có (và chưa được gọi ra)
if ( !defined( 'IN_CATEGORY_VIEW' ) ) {
    echo $taxonomy_slider;
}


/*
 * Chuẩn bị dữ liệu để phân trang
 */
$post_per_page = $base_model->get_config( $getconfig, 'eb_posts_per_page', 20 );
//$post_per_page = 2;
//echo $post_per_page . '<br>' . "\n";

//
$totalThread = $data[ 'count' ];
//echo $totalThread . '<br>' . "\n";
//$totalThread = $post_model->count_posts_by( $data );
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


    /*
     * chuẩn bị dữ liệu để hiển thị ra
     */
    //echo $taxonomy_post_size . '<br>' . "\n";
    //print_r( $data );

    //
    $in_cache = 'view-' . $offset . '-' . $post_per_page;
    $child_data = $term_model->the_cache( $data[ 'term_id' ], $in_cache );
    if ( $child_data === NULL ) {
        $child_data = $post_model->post_category( $post_type, $data, [
            'offset' => $offset,
            'limit' => $post_per_page
        ] );

        //
        $term_model->the_cache( $data[ 'term_id' ], $in_cache, $child_data );
    }
    //print_r( $child_data );
} else {
    $public_part_page = '';
    $child_data = [];
}


/*
 * nạp view riêng của từng theme nếu có
 */
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

//
if ( !defined( 'IN_CATEGORY_VIEW' ) ) {
    $base_model->add_js( 'themes/' . THEMENAME . '/js/taxonomy.js', [
        'cdn' => CDN_BASE_URL,
    ], [
        'defer'
    ] );
}