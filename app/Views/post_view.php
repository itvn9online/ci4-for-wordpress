<?php

//
//$base_model = new\ App\ Models\ Base();
//$post_model = new\ App\ Models\ Post();
//$lang_model = new\ App\ Models\ Lang();

// tự động tạo slider nếu có
$post_model->the_slider( $data, $taxonomy_slider, $lang_model->get_the_text( 'main_slider_slug' ) );

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

$base_model->add_js( 'themes/' . THEMENAME . '/js/post.js', [
    'cdn' => CDN_BASE_URL,
], [
    'defer'
] );