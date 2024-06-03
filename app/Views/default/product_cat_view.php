<!-- <div class="global-taxonomy-block"> -->
<div data-type="<?php echo $data['taxonomy']; ?>" data-id="<?php echo $data['term_id']; ?>" class="global-taxonomy-title w90">
    <h1 class="<?php echo $data['taxonomy']; ?>-taxonomy-title global-module-title home-h1-title">
        <?php
        echo $data['name'];
        ?>
    </h1>
</div>
<?php

//
if (!empty($child_data)) {
    $child_data = $post_model->list_meta_post($child_data);

    //
?>
    <div class="col-main-padding col-<?php echo $data['taxonomy']; ?>-padding">
        <?php
        if (strpos($data['description'], 'Auto create taxonomy') === false) {
        ?>
            <div class="w90 global-cats-description">
                <div class="global-<?php echo $data['taxonomy']; ?>-description ul-default-style img-max-width">
                    <?php echo $data['description']; ?>
                </div>
            </div>
        <?php
        }
        ?>
        <div>
            <div id="category_main" class="main-products-list <?php $option_model->products_in_line($getconfig); ?>">
                <?php

                foreach ($child_data as $child_key => $child_val) {
                    //echo '<!-- ';
                    //print_r( $child_val );
                    //echo ' -->';

                    //
                    $post_model->the_product_node($child_val, [
                        'taxonomy_post_size' => $taxonomy_post_size,
                    ], PRODUCT_DEFAULT_META);
                }

                ?>
            </div>
            <br>
            <div class="public-part-page"><?php echo $public_part_page; ?></div>
        </div>
    </div>
<?php
}
// không có sản phẩm nào -> báo không có
else {
    //
}

?>
<!-- </div> -->