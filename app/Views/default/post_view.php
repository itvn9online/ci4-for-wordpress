<div class="global-main-module global-<?php echo $data['post_type']; ?>-module w90">
    <div class="padding-global-content padding-<?php echo $data['post_type']; ?>-content <?php echo $getconfig->eb_post_sidebar; ?> cf">
        <div class="col-main-content custom-width-global-main custom-width-<?php echo $data['post_type']; ?>-main fullsize-if-mobile">
            <div class="col-main-padding col-<?php echo $data['post_type']; ?>-padding">
                <h1 data-type="<?php echo $data['post_type']; ?>" data-id="<?php echo $data['ID']; ?>" class="post-details-title global-details-title global-module-title"><?php echo $data['post_title']; ?></h1>
                <br />
                <!-- <div><?php $post_model->show_post_thumbnail($data['post_meta']); ?></div> -->
                <!-- <br /> -->
                <div class="img-max-width medium l20 global-details-content <?php echo $data['post_type']; ?>-details-content ul-default-style"><?php echo $data['post_content']; ?></div>
                <br />
                <?php

                // nạp view riêng của từng theme nếu có
                $theme_default_view = __DIR__ . '/post_main_sidebar_view.php';
                // nạp file kiểm tra private view
                include VIEWS_PATH . 'private_view.php';

                ?>
            </div>
        </div>
        <?php

        // hiển thị sidebar nếu có yêu cầu
        if ($getconfig->eb_post_sidebar != '') {
            // nạp view riêng của từng theme nếu có
            $theme_default_view = __DIR__ . '/post_secondary_sidebar_view.php';
            // nạp file kiểm tra private view
            include VIEWS_PATH . 'private_view.php';
        }

        ?>
    </div>
</div>
<?php
// hiển thị bài cùng nhóm nếu có
if (!empty($same_cat_data)) {
?>
    <div class="text-center other-<?php echo $data['post_type']; ?>-title global-module-title"><?php $lang_model->the_text('same_post_title', 'Other post'); ?></div>
    <div id="<?php echo $data['post_type']; ?>_same_cat" class="posts-list other-posts-list <?php $option_model->post_in_line($getconfig); ?>">
        <?php

        foreach ($same_cat_data as $child_key => $child_val) {
            //echo '<!-- ';
            //print_r( $child_val );
            //echo ' -->';

            //
            $post_model->the_node($child_val, [
                'taxonomy_post_size' => $taxonomy_post_size,
            ]);
        }

        ?>
    </div>
<?php
}
