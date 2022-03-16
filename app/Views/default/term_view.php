<?php

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
    <br>
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
