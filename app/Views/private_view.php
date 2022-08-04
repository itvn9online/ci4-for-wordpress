<?php
/*
 * file này được include vào từng view sau đó nó sẽ kiểm tra có view riêng của từng theme không
 */
// default view -> được truyền vào từ parent include
echo $theme_default_view . '<br>' . "\n";

// -> private view
$theme_private_view = str_replace( VIEWS_PATH, VIEWS_CUSTOM_PATH, $theme_default_view );
echo $theme_private_view . '<br>' . "\n";

//
if ( file_exists( $theme_private_view ) ) {
    include $theme_private_view;
}
// không có thì nạp view mặc định
else {
    include $theme_default_view;
}