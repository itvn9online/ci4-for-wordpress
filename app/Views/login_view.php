<?php

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