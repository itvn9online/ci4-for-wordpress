<?php

//
use App\ Libraries\ LanguageCost;
use App\ Libraries\ PostType;

//
//$post_model = new\ App\ Models\ Post();

?>
<div class="w90">
    <div class="text-center">
        <h1 data-type="<?php echo $data[ 'taxonomy' ]; ?>" data-id="<?php echo $data[ 'term_id' ]; ?>" class="<?php echo $data[ 'taxonomy' ]; ?>-taxonomy-title global-taxonomy-title global-module-title">
            <?php
            echo $data[ 'name' ];
            ?>
        </h1>
    </div>
    <br>
    <?php

    //
    //echo $taxonomy_post_size . '<br>' . "\n";

    //print_r( $data );

    //
    $child_data = $base_model->select( '*', WGR_TABLE_PREFIX . 'posts', [
        WGR_TABLE_PREFIX . 'posts.post_type' => $post_type,
        WGR_TABLE_PREFIX . 'posts.post_status' => PostType::PUBLIC,
        WGR_TABLE_PREFIX . 'term_taxonomy.term_id' => $data[ 'term_id' ],
        WGR_TABLE_PREFIX . 'posts.lang_key' => LanguageCost::lang_key()
    ], [
        'join' => [
            WGR_TABLE_PREFIX . 'term_relationships' => WGR_TABLE_PREFIX . 'term_relationships.object_id = ' . WGR_TABLE_PREFIX . 'posts.ID',
            WGR_TABLE_PREFIX . 'term_taxonomy' => WGR_TABLE_PREFIX . 'term_relationships.term_taxonomy_id = ' . WGR_TABLE_PREFIX . 'term_taxonomy.term_taxonomy_id',
        ],
        'order_by' => [
            WGR_TABLE_PREFIX . 'posts.post_modified' => 'DESC',
            WGR_TABLE_PREFIX . 'posts.ID' => 'DESC',
        ],
        // hiển thị mã SQL để check
        //'show_query' => 1,
        // trả về câu query để sử dụng cho mục đích khác
        //'get_query' => 1,
        'offset' => $offset,
        'limit' => $post_per_page
    ] );
    //print_r( $child_data );

    if ( !empty( $child_data ) ) {
        $child_data = $post_model->list_meta_post( $child_data );

        //
        ?>
    <ul id="category_main" class="fix-li-wit thread-list main-thread-list cf <?php $option_model->posts_in_line( $getconfig ); ?>">
        <?php

        foreach ( $child_data as $child_key => $child_val ) {
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
    <div class="public-part-page">
        <?php

        echo $public_part_page;

        ?>
    </div>
    <?php
    }
    // không có sản phẩm nào -> báo không có
    else {
        //
    }

    ?>
</div>
