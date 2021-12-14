<?php

//
//$option_model = new\ App\ Models\ Option();

// update lượt xem
//$this->post_model->update_views( $data[ 'ID' ] );

?>
<div class="global-page-module w90">
    <div class="padding-global-content cf ">
        <div class="col-main-content custom-width-page-main fullsize-if-mobile">
            <div class="col-main-padding col-page-padding">
                <h1 data-type="<?php echo $data[ 'post_type' ]; ?>" data-id="<?php echo $data[ 'ID' ]; ?>" class="post-details-title global-details-title global-module-title">
                    <?php
                    echo $data[ 'post_title' ];
                    ?>
                </h1>
                <br>
                <div class="img-max-width medium l20 global-details-content <?php echo $data[ 'post_type' ]; ?>-details-content ul-default-style">
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
                <ul id="post_same_cat" class="fix-li-wit thread-list other-thread-list cf <?php $option_model->posts_in_line( $getconfig ); ?>">
                    <?php

                    foreach ( $same_cat_data as $child_key => $child_val ) {
                        //echo '<!-- ';
                        //print_r( $child_val );
                        //echo ' -->';

                        //
                        $post_model->the_node( $child_val, [
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
