<?php

//
if ($debug_enable === true) {
    echo '<div class="wgr-view-path">' . str_replace(PUBLIC_HTML_PATH, '', __FILE__) . '</div>';
}

// TEST
//echo __FILE__ . ':' . __LINE__ . '<br>' . "\n";
//$term_model->sync_term_child_count();
//$term_model->update_count_post_in_term($data);

//
$totalThread = $post_model->fix_term_count($data, $post_type);
// echo $totalThread . '<br>' . "\n";
//$totalThread = $data['count'];
//echo $totalThread . '<br>' . "\n";

//if ($totalThread > 0) {
$totalPage = ceil($totalThread / $post_per_page);
if ($totalPage < 1) {
    $totalPage = 1;
}
//echo $totalPage . '<br>' . "\n";
if ($ops['page_num'] > $totalPage) {
    $ops['page_num'] = $totalPage;
} else if ($ops['page_num'] < 1) {
    $ops['page_num'] = 1;
}
//echo $totalThread . '<br>' . "\n";
//echo $totalPage . '<br>' . "\n";
$offset = ($ops['page_num'] - 1) * $post_per_page;

//
$public_part_page = $base_model->EBE_pagination($ops['page_num'], $totalPage, $term_model->get_term_permalink($data));


/*
     * chuẩn bị dữ liệu để hiển thị ra
     */
//echo $taxonomy_post_size . '<br>' . "\n";
//print_r( $data );


//
$in_cache = 'view-' . $post_type . '-' . $offset . '-' . $post_per_page;
$child_data = $term_model->the_cache($data['term_id'], $in_cache);
$child_data = null;
if ($child_data === null) {
    $child_data = $post_model->post_category($post_type, $data, [
        'offset' => $offset,
        'limit' => $post_per_page
    ]);

    //
    if (!empty($child_data)) {
        //echo basename(__FILE__) . ':' . __LINE__ . '<br>' . "\n";
        //print_r( $child_data );
        //print_r($data);

        // -> chạy 1 vòng để nạp lại permalink trước khi cache -> tránh trường hợp update liên tọi
        /*
            foreach ($child_data as $k => $v) {
                $child_data[$k]['post_permalink'] = $post_model->get_post_permalink($v);
            }
            */

        //
        $term_model->the_cache($data['term_id'], $in_cache, $child_data);
    }
}
    //echo basename(__FILE__) . ':' . __LINE__ . '<br>' . "\n";
    //print_r( $child_data );
    /*
} else {
    //echo basename(__FILE__) . ':' . __LINE__ . '<br>' . "\n";
    $public_part_page = '';
    $child_data = [];
}
*/
//echo basename(__FILE__) . ':' . __LINE__ . '<br>' . "\n";
//echo $taxonomy;
