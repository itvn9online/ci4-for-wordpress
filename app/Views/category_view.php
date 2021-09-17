<?php

//
//$base_model = new\ App\ Models\ Base();
//$post_model = new\ App\ Models\ Post();
//$term_model = new\ App\ Models\ Term();

//
//print_r( $data );
//print_r( $getconfig );

// tự động tạo slider nếu có
echo $taxonomy_slider;

// nạp view riêng của từng theme nếu có
$theme_private_view = THEMEPATH . 'views/' . basename( __FILE__, '.php' ) . '.php';
//echo $theme_private_view . '<br>' . "\n";


/*
 * Chuẩn bị dữ liệu để phân trang
 */
$post_per_page = $base_model->get_config( $getconfig, 'eb_posts_per_page', 20 );
//echo $post_per_page . '<br>' . "\n";

//
$totalThread = $post_model->count_posts_by( $data );
//echo $totalThread . '<br>' . "\n";
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


//
if ( file_exists( $theme_private_view ) ) {
    include $theme_private_view;
}
// không có thì nạp view mặc định
else {
    require __DIR__ . '/' . basename( __FILE__, '.php' ) . '-default.php';
}

$base_model->add_js( 'themes/' . THEMENAME . '/js/taxonomy.js' );