<div class="w90">
    <div class="text-center">
        <h1 data-type="<?php echo $data['taxonomy']; ?>" data-id="<?php echo $data['term_id']; ?>" class="<?php echo $data['taxonomy']; ?>-taxonomy-title global-taxonomy-title global-module-title">
            <?php
            echo $data['name'];
            ?>
        </h1>
    </div>
    <br>
    <?php

    //
    foreach ($data['child_term'] as $key => $val) {
        //echo '<!-- ';
        //print_r( $val );
        //echo ' -->';

        /*
        $child_data = $post_model->get_posts_by( $val, [
        'limit' => 4,
        ] );
        */
        $child_data = $post_model->post_category($post_type, $val, [
            //'offset' => $offset,
            'limit' => $base_model->get_config($getconfig, 'max_child_category', 4),
        ]);
        if (empty($child_data)) {
            continue;
        }
        $taxonomy_child_custom_post_size = '';
        if (isset($val['term_meta']['taxonomy_custom_post_size']) && $val['term_meta']['taxonomy_custom_post_size'] != '') {
            $taxonomy_child_custom_post_size = $val['term_meta']['taxonomy_custom_post_size'];
        }
        //echo '<!-- ';
        //print_r( $child_data );
        //echo ' -->';

    ?>
        <div class="category-child-block">
            <div>
                <h2 class="global-module-title"><a href="<?php $term_model->the_term_permalink($val); ?>">
                        <?php echo $val['name']; ?>
                    </a></h2>
            </div>
            <br>
            <div class="category_main posts-list main-posts-list <?php $option_model->posts_in_line($getconfig); ?>">
                <?php

                foreach ($child_data as $child_key => $child_val) {
                    //echo '<!-- ';
                    //print_r( $child_val );
                    //echo ' -->';

                    //
                    $post_model->the_node($child_val, [
                        'taxonomy_post_size' => $taxonomy_child_custom_post_size != '' ? $taxonomy_child_custom_post_size : $taxonomy_post_size,
                    ]);
                }

                ?>
            </div>
            <br>
        </div>
    <?php

    }

    ?>
</div>