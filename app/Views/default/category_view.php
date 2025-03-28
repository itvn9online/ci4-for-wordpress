<div class="global-main-module global-<?php echo $data['taxonomy']; ?>-module w90">
    <div class="padding-global-content padding-<?php echo $data['taxonomy']; ?>-content <?php echo $getconfig->eb_posts_sidebar; ?> cf">
        <div class="col-main-content custom-width-global-main custom-width-<?php echo $data['taxonomy']; ?>-main fullsize-if-mobile">
            <div class="col-main-padding col-<?php echo $data['taxonomy']; ?>-padding">
                <h1 data-type="<?php echo $data['taxonomy']; ?>" data-id="<?php echo $data['term_id']; ?>" class="<?php echo $data['taxonomy']; ?>-taxonomy-title global-taxonomy-title global-module-title text-center"><?php echo $data['name']; ?></h1>
                <br>
                <?php

                if (!empty($child_data)) {
                    $child_data = $post_model->list_meta_post($child_data);

                    //
                ?>
                    <div id="term_main" class="posts-list main-posts-list <?php $option_model->posts_in_line($getconfig); ?>">
                        <?php

                        //
                        foreach ($child_data as $child_key => $child_val) {
                            //echo '<!-- ';
                            //print_r( $child_val );
                            //echo ' -->';

                            //
                            $post_model->the_node($child_val, [
                                'taxonomy_post_size' => $taxonomy_post_size,
                                'custom_html' => $term_col_templates,
                            ]);
                        }

                        ?>
                    </div>
                    <br>
                    <div class="public-part-page"><?php echo $public_part_page; ?></div>
                <?php
                }
                ?>
            </div>
        </div>
        <?php
        // hiển thị sidebar nếu có yêu cầu
        if ($getconfig->eb_posts_sidebar != '') {
        ?>
            <div class="col-sidebar-content custom-width-global-sidebar custom-width-<?php echo $data['taxonomy']; ?>-sidebar fullsize-if-mobile">
                <div class="global-right-space <?php echo $data['taxonomy']; ?>-right-space">
                    <?php
                    // sidebar
                    ?>
                </div>
            </div>
        <?php
        }
        ?>
    </div>
</div>