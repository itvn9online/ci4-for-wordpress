<footer id="wgr__footer">
    <?php

    //$option_model = new\ App\ Models\ Option();
    //$post_model = new\ App\ Models\ Post();

    // nạp view riêng của từng theme nếu có
    $theme_private_view = APPPATH . 'Views/custom/' . basename( __FILE__ );
    //echo $theme_private_view . '<br>' . "\n";

    //
    if ( file_exists( $theme_private_view ) ) {
        include $theme_private_view;
    }
    // không có thì nạp view mặc định
    else {
        include __DIR__ . '/default/' . basename( __FILE__ );
    }

    ?>
</footer>
