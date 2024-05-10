<?php

// nạp banner q.cáo theo slug
$post_model->the_ads('top-main-slider');

?>
<div class="row">
    <div class="col small-12 medium-9 large-9">
        <div class="col-inner">
            <div class="posts-list home-posts-list <?php $option_model->posts_in_line($getconfig); ?>">
                <?php

                // lấy post mới nhất
                $in_cache = $term_model->key_cache('home-post-top10');
                $new_prods = $base_model->scache($in_cache);
                if ($new_prods === null) {
                    $new_prods = $post_model->get_posts_by([
                        // nếu cần lấy theo danh mục nào đó thì truyền term id vào đây
                        // 'term_id' => 1,
                    ], [
                        'select' => DEFAULT_SELECT_POST_COL,
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
                    $base_model->scache($in_cache, $new_prods, 3600);
                }
                //print_r($new_prods);

                //
                foreach ($new_prods as $child_key => $child_val) {
                    //print_r($child_val);

                    //
                    $post_model->the_node($child_val, [
                        //'taxonomy_post_size' => $taxonomy_post_size,
                    ]);
                }

                ?>
            </div>
            <br>
            <div class="posts-list home-posts-list <?php $option_model->products_in_line($getconfig); ?>">
                <?php

                // lấy post mới nhất
                $in_cache = $term_model->key_cache('home-prod-top10');
                $new_prods = $base_model->scache($in_cache);
                if ($new_prods === null) {
                    $new_prods = $post_model->get_products_by([
                        // nếu cần lấy theo danh mục nào đó thì truyền term id vào đây
                        // 'term_id' => 1,
                    ], [
                        'select' => DEFAULT_SELECT_POST_COL,
                        'limit' => 10,
                        'offset' => rand(0, 100),
                        'order_by' => [
                            'menu_order' => 'DESC',
                            'time_order' => 'DESC',
                            //'post_modified' => 'DESC',
                            'ID' => 'DESC'
                        ],
                    ]);

                    //
                    $base_model->scache($in_cache, $new_prods, 3600);
                }
                //print_r($new_prods);

                //
                foreach ($new_prods as $child_key => $child_val) {
                    //print_r($child_val);

                    //
                    $post_model->the_product_node($child_val, [
                        //'taxonomy_post_size' => $taxonomy_post_size,
                    ]);
                }

                ?>
            </div>
        </div>
    </div>
    <div class="col small-12 medium-3 large-3">
        <div class="col-inner">&nbsp;</div>
    </div>
</div>