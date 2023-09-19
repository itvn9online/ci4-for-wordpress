<div class="w90">
    <div class="text-center">
        <h1 class="global-taxonomy-title global-module-title">Tìm kiếm: <?php echo $by_keyword; ?></h1>
    </div>
    <br>
    <?php
    if (empty($data)) {
        // nạp view riêng của từng theme nếu có
        $theme_default_view = VIEWS_PATH . 'default/empty-' . basename(__FILE__);
        // nạp file kiểm tra private view
        include VIEWS_PATH . 'private_view.php';
    } else {
    ?>
        <div id="search_main" class="main-products-list search-list main-search-list <?php echo $option_model->products_in_line($getconfig); ?>">
            <?php

            foreach ($data as $v) {
                //echo '<!-- ';
                //print_r( $child_val );
                //echo ' -->';

                //
                $post_model->the_product_node($v);
            }

            ?>
        </div>
        <br>
        <div class="public-part-page">
            <?php echo $public_part_page; ?>
        </div>
    <?php
    }
    ?>
</div>