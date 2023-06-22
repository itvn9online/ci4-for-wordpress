<?php
/*
 * file này được include vào từng view sau đó nó sẽ kiểm tra có view riêng của từng theme không
 */
//var_dump( $debug_enable );

// default view -> được truyền vào từ parent include

// -> private view
$theme_private_view = str_replace(VIEWS_PATH, VIEWS_CUSTOM_PATH, $theme_default_view);

//
//if ( $debug_enable === true )echo str_replace( PUBLIC_HTML_PATH, '', $theme_default_view ) . '<br>' . PHP_EOL . str_replace( PUBLIC_HTML_PATH, '', $theme_private_view ) . '<br>' . PHP_EOL;

//
if (file_exists($theme_private_view)) {
    if ($debug_enable === true) echo '<div class="wgr-view-path bold">' . str_replace(PUBLIC_HTML_PATH, '', $theme_private_view) . '</div>';

    //
    include $theme_private_view;
}
// không có thì nạp view mặc định
else {
    if ($debug_enable === true) echo '<div class="wgr-view-path">' . str_replace(PUBLIC_HTML_PATH, '', $theme_default_view) . '</div>';

    //
    include $theme_default_view;
}
