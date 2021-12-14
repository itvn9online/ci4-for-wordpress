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
    $child_data = $base_model->select( '*', 'wp_posts', [
        'wp_posts.post_type' => $post_type,
        'wp_posts.post_status' => PostType::PUBLIC,
        'wp_term_taxonomy.term_id' => $data[ 'term_id' ],
        'wp_posts.lang_key' => LanguageCost::lang_key()
    ], [
        'join' => [
            'wp_term_relationships' => 'wp_term_relationships.object_id = wp_posts.ID',
            'wp_term_taxonomy' => 'wp_term_relationships.term_taxonomy_id = wp_term_taxonomy.term_taxonomy_id',
        ],
        'order_by' => [
            'wp_posts.ID' => 'DESC',
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
