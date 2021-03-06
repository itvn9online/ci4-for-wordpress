<div id="oi_scroll_top" class="default-bg"><i class="fa fa-chevron-up"></i></div>
<?php

//
$base_model->adds_js( [
    'javascript/functions_footer.js',
    // https://getbootstrap.com/docs/4.0/getting-started/contents/
    //'thirdparty/bootstrap-5.1.3/js/bootstrap.bundle.min.js', // bao gồm cả Popper -> ít dùng -> khi nào dùng thì include vào sau
    'thirdparty/bootstrap-5.1.3/js/bootstrap.min.js',
    'javascript/footer.js',
    'javascript/pagination.js',
    'themes/' . THEMENAME . '/js/d.js'
], [
    'cdn' => CDN_BASE_URL,
], [
    'defer'
] );

// chức năng riêng dành cho admin
if ( $current_user_id > 0 && isset( $session_data[ 'userLevel' ] ) && $session_data[ 'userLevel' ] > 0 ) {
    // hiển thị debug bar nếu có
    $base_model->add_css( 'admin/css/show-debug-bar.css', [
        'cdn' => CDN_BASE_URL,
    ] );

    // nút edit
    $base_model->add_js( 'admin/js/show-edit-btn.js', [
        'cdn' => CDN_BASE_URL,
    ], [
        'defer'
    ] );
}


// nạp header riêng của từng theme nếu có
$theme_private_view = VIEWS_CUSTOM_PATH . 'default/get_footer.php';
//echo $theme_private_view . '<br>' . "\n";
if ( file_exists( $theme_private_view ) ) {
    include $theme_private_view;
}
// không có thì nạp view mặc định
else {
    include VIEWS_PATH . 'default/get_footer.php';
}

?>
<div id="admin_custom_alert" onClick="$('#admin_custom_alert').fadeOut();"></div>
<?php

//
/*
$base_model->add_js( 'javascript/analytics.js', [], [
    'defer'
] );
*/


//
//print_r( $getconfig );
if ( $getconfig->enable_device_protection == 'on' ) {
    include_once __DIR__ . '/device_protection.php';
}
