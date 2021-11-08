<?php

//
//$base_model = new\ App\ Models\ Base();
//$post_model = new\ App\ Models\ Post();
//$lang_model = new\ App\ Models\ Lang();

// tự động tạo slider nếu có
$post_model->the_slider( $data, [], $lang_model->get_the_text( 'main_slider_slug' ) );

// nạp css dùng chung
$base_model->add_css( 'themes/' . THEMENAME . '/css/page.css' );

// nạp view riêng của từng page nếu có
if ( $page_template != '' ) {
    // nạp css riêng nếu có
    $base_model->add_css( THEMEPATH . 'page-templates/' . $page_template . '.css' );
    
    // nạp view
    require THEMEPATH . 'page-templates/' . $page_template . '.php';
    
    // nạp js riêng nếu có
    $base_model->add_js( THEMEPATH . 'page-templates/' . $page_template . '.js' );
} else {
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
}

// nạp js dùng chung
$base_model->add_js( 'themes/' . THEMENAME . '/js/page.js' );