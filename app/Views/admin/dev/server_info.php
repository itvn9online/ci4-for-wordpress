<ul class="admin-breadcrumb">
    <li>Thông tin server</li>
</ul>
<!-- -->
<div class="flatsome">
    <div class="row row-small">
        <div class="col col-12">
            <div class="bold redcolor upper">PHP SESSION</div>
        </div>
    </div>
    <?php

    foreach ( $all_session as $k => $v ) {
        ?>
    <div class="row row-small">
        <div class="col col-3"><?php echo $k; ?></div>
        <div class="col col-8">
            <?php

            //
            if ( is_array( $v ) ) {
                echo json_encode( $v );
            } else {
                echo $v;
            }

            ?>
        </div>
    </div>
    <?php
    }

    ?>
</div>
<!-- -->
<div class="flatsome">
    <div class="row row-small">
        <div class="col col-12">
            <div class="bold redcolor upper">PHP COOKIES</div>
        </div>
    </div>
    <?php

    foreach ( $all_cookie as $k => $v ) {
        ?>
    <div class="row row-small">
        <div class="col col-3"><?php echo $k; ?></div>
        <div class="col col-8">
            <?php

            //
            if ( is_array( $v ) ) {
                echo json_encode( $v );
            } else {
                echo $v;
            }

            ?>
        </div>
    </div>
    <?php
    }

    ?>
</div>
<!-- -->
<div class="flatsome">
    <div class="row row-small">
        <div class="col col-12">
            <div class="bold redcolor upper">SERVER (PHP Variables)</div>
        </div>
    </div>
    <?php

    foreach ( $data as $k => $v ) {
        ?>
    <div class="row row-small">
        <div class="col col-3"><?php echo $k; ?></div>
        <div class="col col-8">
            <?php

            //
            if ( is_array( $v ) ) {
                echo json_encode( $v );
            } else {
                echo $v;
            }

            ?>
        </div>
    </div>
    <?php
    }

    ?>
</div>
