<?php

//
//$menu_model = new\ App\ Models\ Menu();
//$option_model = new\ App\ Models\ Option();
//$post_model = new App\ Models\ Post();

// nạp view riêng của từng theme nếu có
$theme_private_view = THEMEPATH . 'views/' . basename( __FILE__, '.php' ) . '.php';
//echo $theme_private_view . '<br>' . "\n";

//
if ( file_exists( $theme_private_view ) ) {
    include $theme_private_view;
}
// không có thì nạp view mặc định
else {
    require __DIR__ . '/' . basename( __FILE__, '.php' ) . '-default.php';
}