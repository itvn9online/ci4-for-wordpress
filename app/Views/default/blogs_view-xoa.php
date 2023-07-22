<div class="w90">
    <div class="d-none">
        <div class="text-center">
            <h1 data-type="<?php echo $data['taxonomy']; ?>" data-id="<?php echo $data['term_id']; ?>" class="<?php echo $data['taxonomy']; ?>-taxonomy-title global-taxonomy-title global-module-title">
                <?php
                echo $data['name'];
                ?>
            </h1>
        </div>
        <br>
    </div>
    <?php

    //print_r( $data );

    //
    if ($totalThread > 0) {
        $child_data = $post_model->get_blogs_by($data, [
            'limit' => $post_per_page,
            'offset' => $offset
        ]);
        if (!empty($child_data)) {

            //
    ?>
            <div id="blogs_main" class="fix-li-wit eb-blog eb-blogmain row <?php $option_model->blogs_in_line($getconfig); ?>">
                <?php

                foreach ($child_data as $child_key => $child_val) {
                    //echo '<!-- ';
                    //print_r( $child_val );
                    //echo ' -->';

                    //
                    $post_model->the_node($child_val, [
                        //'taxonomy_post_size' => $taxonomy_custom_post_size,
                        'taxonomy_post_size' => $taxonomy_post_size,
                    ]);
                }

                ?>
            </div>
            <br>
            <div class="public-part-page">
                <?php

                echo $public_part_page;

                ?>
            </div>
    <?php
        }
    }
    // không có sản phẩm nào -> báo không có
    else {
        //
    }

    ?>
</div>