<?php

// nạp banner q.cáo  thao slug
$post_model->the_ads('top-main-slider');

?>
<ul class="fix-li-wit thread-list home-thread-list cf <?php $option_model->posts_in_line($getconfig); ?>">
    <?php

// lấy post mới nhất
$new_posts = $post_model->get_posts_by([], [
    'limit' => 10,
    //'offset' => 0,
    'order_by' => [
        'menu_order' => 'DESC',
        'post_modified' => 'DESC',
        'ID' => 'DESC'
    ],
]);
//print_r($new_posts);
foreach ($new_posts as $child_key => $child_val) {
    //print_r($child_val);

    //
    $post_model->the_node($child_val, [
        //'taxonomy_post_size' => $taxonomy_post_size,
    ]);
}

?>
</ul>