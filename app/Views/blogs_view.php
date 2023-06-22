<?php

// tự động tạo slider nếu có
echo $taxonomy_slider;


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
//echo 'taxonomy_post_size: ' . $taxonomy_post_size . '<br>' . PHP_EOL;
//echo 'taxonomy_custom_post_size: ' . $taxonomy_custom_post_size . '<br>' . PHP_EOL;

//
$post_per_page = $base_model->get_config($getconfig, 'eb_blogs_per_page', 10);
//echo $post_per_page . '<br>' . PHP_EOL;

//
$totalThread = $post_model->count_blogs_by($data);
//echo $totalThread . '<br>' . PHP_EOL;

if ($totalThread > 0) {
    $totalPage = ceil($totalThread / $post_per_page);
    if ($totalPage < 1) {
        $totalPage = 1;
    }
    //echo $totalPage . '<br>' . PHP_EOL;
    if ($ops['page_num'] > $totalPage) {
        $ops['page_num'] = $totalPage;
    } else if ($ops['page_num'] < 1) {
        $ops['page_num'] = 1;
    }
    //echo $totalThread . '<br>' . PHP_EOL;
    //echo $totalPage . '<br>' . PHP_EOL;
    $offset = ($ops['page_num'] - 1) * $post_per_page;

    $public_part_page = $base_model->EBE_pagination($ops['page_num'], $totalPage, $term_model->get_term_permalink($data));
} else {
    $public_part_page = '';
}


//
$theme_default_view = VIEWS_PATH . 'default/' . basename(__FILE__);
// nạp file kiểm tra private view
include VIEWS_PATH . 'private_view.php';


//
$base_model->add_js('themes/' . THEMENAME . '/js/blogs_list.js', [
    'cdn' => CDN_BASE_URL,
], [
    'defer'
]);
