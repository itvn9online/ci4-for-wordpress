<?php

//
//$option_model = new\ App\ Models\ Option();

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

    if ( isset( $getconfig->show_child_category ) && $getconfig->show_child_category == 'on' &&
        //
        isset( $data[ 'child_term' ] ) && !empty( $data[ 'child_term' ] ) ) {
        foreach ( $data[ 'child_term' ] as $key => $val ) {
            //echo '<!-- ';
            //print_r( $val );
            //echo ' -->';

            $child_data = $post_model->get_posts_by( $val, [
                'limit' => 4,
            ] );
            if ( empty( $child_data ) ) {
                continue;
            }
            $taxonomy_child_custom_post_size = '';
            if ( isset( $val[ 'term_meta' ][ 'taxonomy_custom_post_size' ] ) && $val[ 'term_meta' ][ 'taxonomy_custom_post_size' ] != '' ) {
                $taxonomy_child_custom_post_size = $val[ 'term_meta' ][ 'taxonomy_custom_post_size' ];
            }
            //echo '<!-- ';
            //print_r( $child_data );
            //echo ' -->';

            ?>
    <div class="category-child-block">
        <div>
            <h2 class="global-module-title"><a href="<?php $term_model->the_permalink($val); ?>"><?php echo $val[ 'name' ]; ?></a></h2>
        </div>
        <br>
        <ul class="category_main fix-li-wit thread-list main-thread-list cf <?php $option_model->posts_in_line( $getconfig ); ?>">
            <?php

            foreach ( $child_data as $child_key => $child_val ) {
                //echo '<!-- ';
                //print_r( $child_val );
                //echo ' -->';

                //
                $post_model->the_node( $child_val, [
                    'taxonomy_post_size' => $taxonomy_child_custom_post_size != '' ? $taxonomy_child_custom_post_size : $taxonomy_post_size,
                ] );
            }

            ?>
        </ul>
        <br>
    </div>
    <?php

    }
    }
    // 
    else {
        //print_r( $data );

        //
        $child_data = $post_model->get_posts_by( $data, [
            'limit' => $post_per_page,
            'offset' => $offset,
        ] );
        if ( !empty( $child_data ) ) {

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
    }

    ?>
</div>
