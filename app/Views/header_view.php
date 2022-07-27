<header id="wgr__top">
    <?php

    //
    //$menu_model = new\ App\ Models\ Menu();
    //$option_model = new\ App\ Models\ Option();
    //$post_model = new App\ Models\ Post();

    // nạp view riêng của từng theme nếu có
    $theme_default_view = VIEWS_PATH . 'default/' . basename( __FILE__ );
    $theme_private_view = str_replace( VIEWS_PATH, VIEWS_CUSTOM_PATH, $theme_default_view );

    //
    if ( file_exists( $theme_private_view ) ) {
        //echo $theme_private_view . '<br>' . "\n";
        include $theme_private_view;
    }
    // không có thì nạp view mặc định
    else {
        //echo $theme_default_view . '<br>' . "\n";
        include $theme_default_view;
    }

    ?>
</header>