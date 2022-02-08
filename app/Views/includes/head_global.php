<meta name="viewport" content="width=device-width, initial-scale=1.0">
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
<link rel="dns-prefetch" href="https://www.google-analytics.com/" />
<meta name="format-detection" content="telephone=no">
<!-- SEO -->
<link href="<?php echo $option_model->get_the_favicon($getconfig); ?>" rel="shortcut icon" type="image/png" />
<link href="<?php echo $seo['canonical']; ?>" rel="canonical" />
<meta name="title" content="<?php echo $seo['title']; ?>" />
<meta name="keywords" content="<?php echo $seo['keyword']; ?>" />
<meta name="news_keywords" content="<?php echo $seo['keyword']; ?>" />
<meta name="description" content="<?php echo $seo['description']; ?>" />
<meta name="abstract" content="<?php echo $seo['description']; ?>" />
<meta name="RATING" content="GENERAL" />
<meta itemprop="name" content="<?php echo $seo['title']; ?>" />
<meta itemprop="description" content="<?php echo $seo['description']; ?>" />
<?php
if ( $getconfig->fb_app_id != '' ) {
    ?>
<meta property="fb:app_id" content="<?php echo $getconfig->fb_app_id; ?>" />
<?php
}
?>
<meta property="og:title" content="<?php echo $seo['title']; ?>" />
<meta property="og:url" content="<?php echo $seo['canonical']; ?>" />
<meta property="og:description" content="<?php echo $seo['description']; ?>" />
<meta property="og:type" content="website" />
<meta property="og:site_name" content="<?php $option_model->the_config($getconfig, 'name'); ?>" />
<meta property="og:image" content="<?php
                                   if ( isset( $seo['og_image'] ) && $seo['og_image'] != '' ) {
                                       echo $seo['og_image'];
                                   } else {
                                       echo base_url() . ltrim( str_replace( base_url(), '', $option_model->get_config($getconfig, 'image') ), '/' );
                                   }
                                   ?>" />
<meta property="og:image:alt" content="<?php
                                   if ( isset( $seo['og_image_alt'] ) && $seo['og_image_alt'] != '' ) {
                                       echo $seo['og_image_alt'];
                                   } else {
                                       $option_model->the_config($getconfig, 'name');
                                   }
                                   ?>" />
<meta property="og:updated_time" content="<?php echo time(); ?>" />
<meta name="twitter:card" content="summary" />
<meta name="twitter:description" content="<?php echo $seo['description']; ?>" />
<meta name="twitter:title" content="<?php echo $seo['title']; ?>" />
<!-- END SEO --> 
<!-- -->
<link href="https://fonts.googleapis.com" rel="preconnect" />
<link href="https://fonts.gstatic.com" rel="preconnect" crossorigin />
<!-- -->
<?php

// nạp file font theo kiểu inline
$font_awesome_before = $base_model->get_add_css( 'thirdparty/awesome47/css/font-awesome.before.css', true );
$font_awesome_before = str_replace( '../fonts/', './thirdparty/awesome47/fonts/', $font_awesome_before );
echo $font_awesome_before;

?>
<link rel="preload" as="style" onload="this.onload=null;this.rel='stylesheet'" href="thirdparty/awesome47/css/font-awesome.min.css?v=4.7" />
<link rel="preload" as="style" onload="this.onload=null;this.rel='stylesheet'" href="thirdparty/bootstrap-5.1.3/css/bootstrap.min.css" />
<!-- <link rel="stylesheet" type="text/css" media="all" href="thirdparty/flatsome/flatsome.css" /> --> 
<!-- <link rel="stylesheet" type="text/css" media="all" href="frontend/css/swiper.min.css" /> --> 
<script type="text/javascript" src="thirdparty/jquery/jquery-3.6.0.min.js"></script> 
<!--
<script type="text/javascript" src="thirdparty/jquery/jquery-migrate-3.3.2.min.js"></script> 
<link rel="preload" as="script" onload="this.onload=null;this.type='text/javascript'" href="thirdparty/jquery/jquery-3.6.0.min.js">
<link rel="preload" as="script" onload="this.onload=null;this.type='text/javascript'" href="thirdparty/jquery/jquery-migrate-3.3.2.min.js">
--> 
<!-- <script type="text/javascript" src="frontend/js/swiper.min.js"></script> -->
<?php

//
$base_model->add_css( 'css/d.css' );
$base_model->preload_css( 'css/d2.css' );
$base_model->adds_css( [
    'css/flatsome.css',
    'css/thread_list.css',
    'themes/' . THEMENAME . '/style.css',
    'themes/' . THEMENAME . '/css/thread_node.css',
] );

$base_model->adds_js( [
    'javascript/functions.js',
    'javascript/eb.js',
    //'javascript/slider.js',
    'themes/' . THEMENAME . '/js/functions.js',
] );

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
var cf_tester_mode = '<?php echo $debug_enable === true ? 1 : 0; ?>' * 1,
current_user_id='<?php echo $current_user_id; ?>' * 1,
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


// nếu có ID google analytics thì nạp nó
if ( $getconfig->google_analytics != '' ) {
    ?>
<!-- Global site tag (gtag.js) - Google Analytics --> 
<script async src="https://www.googletagmanager.com/gtag/js?id=<?php echo $getconfig->google_analytics; ?>"></script> 
<script>
window.dataLayer = window.dataLayer || [];
function gtag(){dataLayer.push(arguments);}
gtag('js', new Date());
gtag('config', '<?php echo $getconfig->google_analytics; ?>');
</script>
<?php
}
