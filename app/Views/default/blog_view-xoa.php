<div class="global-main-module global-blog-module w90">
    <div class="padding-global-content padding-blog-content <?php echo $getconfig->eb_blog_sidebar; ?> cf">
        <div class="col-main-content custom-width-global-main custom-width-blog-main fullsize-if-mobile">
            <div class="col-main-padding col-blog-padding">
                <h1 data-type="<?php echo $data['post_type']; ?>" data-id="<?php echo $data['ID']; ?>" class="blog-details-title global-details-title global-module-title">
                    <?php
                    echo $data['post_title'];
                    ?>
                </h1>
                <div class="img-max-width medium l20 global-details-excerpt <?php echo $data['post_type']; ?>-details-excerpt ul-default-style remove-first-img">
                    <?php
                    echo $data['post_excerpt'];
                    ?>
                </div>
                <div class="img-max-width medium l20 global-details-content <?php echo $data['post_type']; ?>-details-content ul-default-style remove-first-img">
                    <?php
                    echo $data['post_content'];
                    ?>
                </div>
                <br />
                <?php
                // html_for_fb_comment

                if (!empty($same_cat_data)) {
                ?>
                    <div class="text-center">
                        <div class="other-post-title global-module-title">Bài viết tương tự</div>
                    </div>
                    <br>
                    <div id="blog_same_cat" class="fix-li-wit eb-blog other-eb-blog row <?php $option_model->blogs_in_line($getconfig); ?>">
                        <?php

                        foreach ($same_cat_data as $child_key => $child_val) {
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
                <?php
                }

                ?>
                <br>
                <div class="global-main-widget global-blog-widget">
                    <?php
                    // str_for_details_sidebar
                    ?>
                </div>
            </div>
        </div>
        <?php
        if ($getconfig->eb_blog_sidebar != '') {
        ?>
            <div class="col-sidebar-content custom-width-global-sidebar custom-width-blog-sidebar fullsize-if-mobile">
                <div class="global-right-space blog-right-space">
                    <?php
                    // str_sidebar
                    ?>
                </div>
            </div>
        <?php
        }
        ?>
    </div>
</div>