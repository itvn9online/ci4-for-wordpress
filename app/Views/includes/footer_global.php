<div id="oi_scroll_top" class="default-bg"><i class="fa fa-chevron-up"></i></div>
<?php

//
$base_model->add_js( 'javascript/footer.js' );
$base_model->add_js( 'themes/' . THEMENAME . '/js/d.js' );

// chức năng riêng dành cho admin
if ( !empty( $session_data ) &&
    //
    isset( $session_data[ 'userID' ] ) && $session_data[ 'userID' ] > 0 &&
    //
    isset( $session_data[ 'userLevel' ] ) && $session_data[ 'userLevel' ] > 0 ) {
    $base_model->add_js( 'javascript/show-edit-btn.js' );
}


// nạp header riêng của từng theme nếu có
$theme_private_view = THEMEPATH . 'Views/get_footer.php';
//echo $theme_private_view . '<br>' . "\n";
if ( file_exists( $theme_private_view ) ) {
    include $theme_private_view;
}

?>
<div id="admin_custom_alert" onClick="$('#admin_custom_alert').fadeOut();"></div>
