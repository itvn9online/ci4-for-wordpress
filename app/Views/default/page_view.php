<?php

// update lượt xem
//$this->post_model->update_views( $data[ 'ID' ] );

?>
<div class="global-page-module w90">
    <div class="padding-global-content cf ">
        <div class="col-main-content custom-width-global-main custom-width-page-main fullsize-if-mobile">
            <div class="col-main-padding col-page-padding">
                <h1 data-type="<?php echo $data['post_type']; ?>" data-id="<?php echo $data['ID']; ?>" class="page-details-title global-details-title global-module-title">
                    <?php
                    echo $data['post_title'];
                    ?>
                </h1>
                <div class="img-max-width medium l20 global-details-content <?php echo $data['post_type']; ?>-details-content ul-default-style">
                    <?php
                    echo $data['post_content'];
                    ?>
                </div>
                <br />
                <?php
                // html_for_fb_comment
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