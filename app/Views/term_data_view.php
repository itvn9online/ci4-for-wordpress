<?php

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
    $in_cache = 'view-' . $post_type . '-' . $offset . '-' . $post_per_page;
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
            //echo $totalThread . '<br>' . PHP_EOL;
        }
        // nếu có thì hiển thị bình thường
        else {
            //print_r( $child_data );
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
    //echo basename(__FILE__) . ':' . __LINE__ . '<br>' . PHP_EOL;
    //print_r( $child_data );
} else {
    //echo basename(__FILE__) . ':' . __LINE__ . '<br>' . PHP_EOL;
    $public_part_page = '';
    $child_data = [];
}
//echo basename(__FILE__) . ':' . __LINE__ . '<br>' . PHP_EOL;
//echo $taxonomy;
