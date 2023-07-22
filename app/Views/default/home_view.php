<?php

// nạp banner q.cáo theo slug
$post_model->the_ads('top-main-slider');

?>
<div class="posts-list home-posts-list <?php $option_model->posts_in_line($getconfig); ?>">
    <?php

    // lấy post mới nhất
    $in_cache = $term_model->key_cache('home-top10');
    $new_posts = $base_model->scache($in_cache);
    if ($new_posts === null) {
        $new_posts = $post_model->get_posts_by([], [
            'limit' => 10,
            //'offset' => 0,
            'order_by' => [
                'menu_order' => 'DESC',
                'time_order' => 'DESC',
                //'post_modified' => 'DESC',
                'ID' => 'DESC'
            ],
        ]);

        //
        $base_model->scache($in_cache, $new_posts, 300);
    }
    //print_r($new_posts);

    //
    foreach ($new_posts as $child_key => $child_val) {
        //print_r($child_val);

        //
        $post_model->the_node($child_val, [
            //'taxonomy_post_size' => $taxonomy_post_size,
        ]);
    }

    ?>
</div>