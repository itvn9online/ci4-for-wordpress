<?php

//
//$base_model = new\ App\ Models\ Base();
//$post_model = new\ App\ Models\ Post();
//$lang_model = new\ App\ Models\ Lang();

// tự động tạo slider nếu có
$post_model->the_slider( $data, $taxonomy_slider, $lang_model->get_the_text( 'main_slider_slug' ) );

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

$base_model->add_js( 'themes/' . THEMENAME . '/js/blog_details.js', [
    'cdn' => CDN_BASE_URL,
], [
    'defer'
] );