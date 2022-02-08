<?php

//
$theme_private_view = THEMEPATH . 'Views/' . basename( __FILE__ );

//
$base_model->add_css( 'css/maruti-login.css' );
$base_model->add_js( 'javascript/maruti-login.js', 0, [
    'defer'
] );

// nhúng file header riêng của theme -> thường dụng khi muốn xóa LOGO WebGiaRe
if ( file_exists( $theme_private_view ) ) {
    include $theme_private_view;
}
// không có thì nạp view mặc định
else {
    ?>
<br>
<div class="partner">
    <h1><a href="<?php echo PARTNER_WEBSITE; ?>?utm_source=ci4forwordpress&utm_medium=logo&utm_campaign=wp-login&utm_term=copyright&utm_content=<?php echo $_SERVER['HTTP_HOST']; ?>" target="_blank">Xây dựng bằng WordPress</a></h1>
</div>
<?php
}
