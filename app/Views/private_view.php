<?php
/*
 * file này được include vào từng view sau đó nó sẽ kiểm tra có view riêng của từng theme không
 */
//var_dump( $debug_enable );

// default view -> được truyền vào từ parent include

// -> private view
$theme_private_view = str_replace( VIEWS_PATH, VIEWS_CUSTOM_PATH, $theme_default_view );

//
//if ( $debug_enable === true )echo str_replace( PUBLIC_HTML_PATH, '', $theme_default_view ) . '<br>' . "\n" . str_replace( PUBLIC_HTML_PATH, '', $theme_private_view ) . '<br>' . "\n";

//
if ( file_exists( $theme_private_view ) ) {
    if ( $debug_enable === true )echo '<strong>' . str_replace( PUBLIC_HTML_PATH, '', $theme_private_view ) . '</strong><br>' . "\n";

    //
    include $theme_private_view;
}
// không có thì nạp view mặc định
else {
    if ( $debug_enable === true )echo str_replace( PUBLIC_HTML_PATH, '', $theme_default_view ) . '<br>' . "\n";

    //
    include $theme_default_view;
}