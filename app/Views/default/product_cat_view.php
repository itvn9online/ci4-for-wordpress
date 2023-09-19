<div class="text-center">
    <h1 data-type="<?php echo $data['taxonomy']; ?>" data-id="<?php echo $data['term_id']; ?>" class="<?php echo $data['taxonomy']; ?>-taxonomy-title global-taxonomy-title global-module-title home-h1-title">
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
        <div class="global-cats-description global-<?php echo $data['taxonomy']; ?>-description ul-default-style img-max-width">
            <?php echo $data['description']; ?>
        </div>
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
                    ]);
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
