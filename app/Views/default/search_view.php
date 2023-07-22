<div class="w90">
    <div class="text-center">
        <h1 class="global-taxonomy-title global-module-title">Tìm kiếm:
            <?php
            echo $by_keyword;
            ?>
        </h1>
    </div>
    <br>
    <?php
    if (empty($data)) {
    ?>
        <br>
        <h3 class="text-center top-menu-space bottom-menu-space">Không có dữ liệu nào phù hợp với từ khóa của bạn.</h3>
        <br>
    <?php
    } else {
    ?>
        <div id="search_main" class="posts-list main-posts-list search-list main-search-list <?php $option_model->posts_in_line($getconfig); ?>">
            <?php

            foreach ($data as $v) {
                //echo '<!-- ';
                //print_r( $child_val );
                //echo ' -->';

                //
                if ($post_type == $product_type) {
                    $post_model->the_product_node($v);
                } else {
                    $post_model->the_node($v);
                }
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