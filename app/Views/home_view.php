<?php

// nạp view riêng của từng theme nếu có
$theme_private_view = THEMEPATH . 'Views/' . basename( __FILE__ );
//echo $theme_private_view . '<br>' . "\n";

//
if ( file_exists( $theme_private_view ) ) {
    include $theme_private_view;
}
// không có thì nạp view mặc định
else {
    include __DIR__ . '/default/' . basename( __FILE__ );
}

/*
$this->base_model->add_js( 'themes/' . THEMENAME . '/js/home.js', [
    'cdn' => CDN_BASE_URL,
] );
*/