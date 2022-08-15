<div class="global-page-module w90">
    <div class="padding-global-content cf ">
        <div class="col-main-content custom-width-page-main fullsize-if-mobile">
            <div class="col-main-padding col-page-padding">
                <h1 data-type="<?php echo $data[ 'post_type' ]; ?>" data-id="<?php echo $data[ 'ID' ]; ?>" class="blog-details-title global-details-title global-module-title">
                    <?php
                    echo $data[ 'post_title' ];
                    ?>
                </h1>
                <div class="img-max-width medium l20 global-details-content <?php echo $data[ 'post_type' ]; ?>-details-content ul-default-style remove-first-img">
                    <?php
                    echo $data[ 'post_content' ];
                    ?>
                </div>
                <br />
                <?php
                // html_for_fb_comment

                if ( !empty( $same_cat_data ) ) {
                    ?>
                <div class="text-center">
                    <div class="other-post-title global-module-title">Bài viết tương tự</div>
                </div>
                <br>
                <ul id="blog_same_cat" class="fix-li-wit eb-blog other-eb-blog cf <?php $option_model->blogs_in_line( $getconfig ); ?>">
                    <?php

                    foreach ( $same_cat_data as $child_key => $child_val ) {
                        //echo '<!-- ';
                        //print_r( $child_val );
                        //echo ' -->';

                        //
                        $post_model->the_blog_node( $child_val, [
                            //'taxonomy_post_size' => $taxonomy_custom_post_size,
                            'taxonomy_post_size' => $taxonomy_post_size,
                        ] );
                    }

                    ?>
                </ul>
                <?php
                }

                ?>
                <br>
                <div class="global-page-widget">
                    <?php
                    // str_for_details_sidebar
                    ?>
                </div>
            </div>
        </div>
        <div class="col-sidebar-content custom-width-global-sidebar custom-width-page-sidebar fullsize-if-mobile">
            <div class="page-right-space global-right-space">
                <?php
                // str_sidebar
                ?>
            </div>
        </div>
    </div>
</div>
