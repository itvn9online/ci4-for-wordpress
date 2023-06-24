<?php
/*
 * file view dành cho các term khác, ngoài các term mặc định được hỗ trợ
 */

//
//print_r($data);
//print_r( $ops );
//print_r( $getconfig );

// tự động tạo slider nếu có (và chưa được gọi ra)
if (!defined('IN_CATEGORY_VIEW')) {
    echo $taxonomy_slider;
}


/*
 * Chuẩn bị dữ liệu để phân trang
 */
$post_per_page = $base_model->get_config($getconfig, 'eb_posts_per_page', 20);
//$post_per_page = 2;
//echo $post_per_page . '<br>' . PHP_EOL;

//
$totalThread = $data['count'];
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


    /*
     * chuẩn bị dữ liệu để hiển thị ra
     */
    //echo $taxonomy_post_size . '<br>' . PHP_EOL;
    //print_r( $data );

    //
    $in_cache = 'view-' . $offset . '-' . $post_per_page;
    $child_data = $term_model->the_cache($data['term_id'], $in_cache);
    $child_data = NULL;
    if ($child_data === NULL) {
        $child_data = $post_model->post_category($post_type, $data, [
            'offset' => $offset,
            'limit' => $post_per_page
        ]);

        // nếu đến đây mà query lại tìm không thấy bài viết -> tính lại tổng bài
        if (empty($child_data)) {
            //echo basename(__FILE__) . ':' . __LINE__ . '<br>' . PHP_EOL;

            //
            //print_r($data);
            $totalThread = $post_model->fix_term_count($data, $post_type);
            echo $totalThread . '<br>' . PHP_EOL;
        }
        // nếu có thì hiển thị bình thường
        else {
            //print_r( $child_data );
            // -> chạy 1 vòng để nạp lại permalink trước khi cache -> tránh trường hợp update liên tọi
            foreach ($child_data as $k => $v) {
                $child_data[$k]['post_permalink'] = $post_model->get_post_permalink($v);
            }

            //
            $term_model->the_cache($data['term_id'], $in_cache, $child_data);
        }
    }
    //echo basename(__FILE__) . ':' . __LINE__ . '<br>' . PHP_EOL;
    //print_r( $child_data );
} else {
    //echo basename(__FILE__) . ':' . __LINE__ . '<br>' . PHP_EOL;
    $public_part_page = '';
    $child_data = [];
}
//echo basename(__FILE__) . ':' . __LINE__ . '<br>' . PHP_EOL;
//echo $taxonomy;


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
    $theme_default_view = VIEWS_PATH . 'default/' . basename(__FILE__);
    // nạp file kiểm tra private view
    include VIEWS_PATH . 'private_view.php';
}


//
if (!defined('IN_CATEGORY_VIEW')) {
    $base_model->add_js('themes/' . THEMENAME . '/js/taxonomy.js', [
        'cdn' => CDN_BASE_URL,
    ], [
        'defer'
    ]);
}
