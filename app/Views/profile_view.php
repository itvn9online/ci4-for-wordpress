<?php


/*
 * nạp view riêng của từng theme nếu có
 */
$theme_private_view = THEMEPATH . 'Views/' . basename( __FILE__ );
//echo $theme_private_view . '<br>' . "\n";

//
if ( file_exists( $theme_private_view ) ) {
    include_once $theme_private_view;
}
// không có thì nạp view mặc định
else {
    include_once __DIR__ . '/default/' . basename( __FILE__ );
}

//
$base_model->adds_js( [
    'javascript/user-profile.js',
    'javascript/datetimepicker.js',
], [
    'cdn' => CDN_BASE_URL,
], [
    'defer'
] );