<div class="w90">
    <div class="text-center">
        <h1 class="global-taxonomy-title global-module-title">Tìm kiếm:
            <?php
            echo $by_keyword;
            ?>
        </h1>
    </div>
    <br>
    <ul id="category_main" class="fix-li-wit thread-list main-thread-list cf <?php $option_model->posts_in_line( $getconfig ); ?>">
        <?php

        foreach ( $data as $v ) {
            //echo '<!-- ';
            //print_r( $child_val );
            //echo ' -->';

            //
            $post_model->the_node( $v );
        }

        ?>
    </ul>
    <div class="public-part-page">
        <?php

        echo $public_part_page;

        ?>
    </div>
</div>
