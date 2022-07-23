<?php

// nạp view riêng của từng theme nếu có
$theme_private_view = VIEWS_CUSTOM_PATH . 'default/' . basename( __FILE__ );
//echo $theme_private_view . '<br>' . "\n";

//
if ( file_exists( $theme_private_view ) ) {
    include $theme_private_view;
}
// không có thì nạp view mặc định
else {
    include VIEWS_PATH . 'default/' . basename( __FILE__ );
}

//
$base_model->add_js( 'themes/' . THEMENAME . '/js/home.js', [
    'cdn' => CDN_BASE_URL,
], [
    'defer'
] );