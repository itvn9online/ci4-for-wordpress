<meta name="viewport" content="initial-scale=1, width=device-width, maximum-scale=1, minimum-scale=1, user-scalable=no">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title><?php echo $seo['title']; ?></title>
<base href="<?php echo DYNAMIC_BASE_URL; ?>" />
<meta http-equiv="Cache-control" content="public">
<meta http-equiv="X-UA-Compatible" content="IE=Edge,chrome=1" />
<meta name="theme-color" content="#ff2442">
<meta name="msapplication-navbutton-color" content="#ff2442">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="#ff2442">
<meta http-equiv="x-dns-prefetch-control" content="on">
<link rel="dns-prefetch" href="//www.google-analytics.com" />
<meta name="format-detection" content="telephone=no">
<link href="<?php echo $option_model->get_the_favicon($getconfig); ?>" rel="shortcut icon" type="image/png" />
<link href="<?php echo $seo['canonical']; ?>" rel="canonical" />
<link href="https://fonts.googleapis.com" rel="preconnect" />
<link href="https://fonts.gstatic.com" rel="preconnect" crossorigin />
<link href="thirdparty/awesome4/css/font-awesome.min.css?v=4.7" rel="stylesheet" />
<link href="thirdparty/bootstrap-5.1.3/css/bootstrap.min.css" rel="stylesheet" type="text/css" />
<!-- <link href="thirdparty/flatsome/flatsome.css" rel="stylesheet" type="text/css" /> -->
<!-- <link href="frontend/css/swiper.min.css" rel="stylesheet" type="text/css" /> -->
<script src="thirdparty/jquery/jquery-3.6.0.min.js" type="application/javascript"></script> 
<script src="thirdparty/jquery/jquery-migrate-3.3.2.min.js" type="text/javascript"></script> 
<!-- <script src="frontend/js/swiper.min.js" type="application/javascript"></script> -->
<?php

$base_model->add_css( 'css/d.css' );
$base_model->add_css( 'css/d2.css' );
$base_model->add_css( 'css/flatsome.css' );
$base_model->add_css( 'css/thread_list.css' );
$base_model->add_css( 'themes/' . THEMENAME . '/style.css' );
$base_model->add_css( 'themes/' . THEMENAME . '/css/thread_node.css' );

$base_model->add_js( 'javascript/functions.js' );
$base_model->add_js( 'javascript/eb.js' );
$base_model->add_js( 'javascript/slider.js' );
$base_model->add_js( 'themes/' . THEMENAME . '/js/functions.js' );

//print_r( $getconfig );
if ( !isset( $getconfig->site_max_width ) || empty( $getconfig->site_max_width ) ) {
    $getconfig->site_max_width = 999;
}
if ( !isset( $getconfig->site_full_width ) || empty( $getconfig->site_full_width ) ) {
    $getconfig->site_full_width = 1666;
}

// dùng để css chiều rộng cho before, after của menu nav
?>
<style>
.row {
max-width: <?php echo ( $getconfig->site_max_width + 30 ) . 'px';
?>;
}
.w90, .w99 {
max-width: <?php echo $getconfig->site_max_width . 'px';
?>;
}
.w96 {
max-width: <?php echo $getconfig->site_full_width . 'px';
?>;
}
</style>
<script>
var cf_tester_mode = 1,
    pid = 0,
    global_window_width = jQuery(window).width(),
    web_link = window.location.protocol + '//' + document.domain + '/';
</script>
<?php

if ( isset( $getconfig->html_header ) ) {
    echo $getconfig->html_header;
}


// nạp header riêng của từng theme nếu có
$theme_private_view = THEMEPATH . 'Views/get_header.php';
//echo $theme_private_view . '<br>' . "\n";
if ( file_exists( $theme_private_view ) ) {
    include $theme_private_view;
}
