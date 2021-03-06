<?php

//
use App\ Helpers\ HtmlTemplate;

?>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title><?php echo $seo['title']; ?></title>
<base href="<?php echo DYNAMIC_BASE_URL; ?>" />
<!-- <meta http-equiv="Cache-control" content="public"> --> 
<!-- <meta http-equiv="X-UA-Compatible" content="IE=Edge,chrome=1" /> -->
<meta name="theme-color" content="<?php echo $getconfig->default_bg; ?>">
<meta name="msapplication-navbutton-color" content="<?php echo $getconfig->default_bg; ?>">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="<?php echo $getconfig->default_bg; ?>">
<!-- <meta http-equiv="x-dns-prefetch-control" content="on"> -->
<link rel="dns-prefetch" href="https://www.google-analytics.com/" />
<link rel="dns-prefetch" href="https://connect.facebook.net/" />
<link rel="dns-prefetch" href="//cdn.jsdelivr.net" />
<link rel="dns-prefetch" href="//s.w.org" />
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
//
if ( CDN_BASE_URL != '' ) {
    ?>
<link rel="dns-prefetch" href="<?php echo CDN_BASE_URL; ?>" />
<?php
}

//
//print_r( $getconfig );
if ( $getconfig->blog_private == 'on' ) {
    ?>
<meta name="robots" content="noindex, nofollow" />
<?php
}

//
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


// n???p file font theo ki???u inline
$font_awesome_before = $base_model->get_add_css( 'thirdparty/awesome47/css/font-awesome.before.css', [
    'get_content' => 1
] );
$font_awesome_before = str_replace( '../fonts/', 'thirdparty/awesome47/fonts/', $font_awesome_before );
echo $font_awesome_before;


// n???p m???t s??? css ??? d???ng preload
$arr_preload_bootstrap = [
    CDN_BASE_URL . 'thirdparty/awesome47/css/font-awesome.min.css?v=4.7',

    // b???n full
    CDN_BASE_URL . 'thirdparty/bootstrap-5.1.3/css/bootstrap.min.css',
    //'thirdparty/bootstrap-5.1.3/css/bootstrap.rtl.min.css',

    // c??c module ????n l???
    //'thirdparty/bootstrap-5.1.3/css/bootstrap-grid.min.css',
    //'thirdparty/bootstrap-5.1.3/css/bootstrap-grid.rtl.min.css',
    //'thirdparty/bootstrap-5.1.3/css/bootstrap-reboot.min.css',
    //'thirdparty/bootstrap-5.1.3/css/bootstrap-reboot.rtl.min.css',
    //'thirdparty/bootstrap-5.1.3/css/bootstrap-utilities.min.css',
    //'thirdparty/bootstrap-5.1.3/css/bootstrap-utilities.rtl.min.css',
];

foreach ( $arr_preload_bootstrap as $v ) {
    ?>
<link rel="preload" as="style" onload="this.onload=null;this.rel='stylesheet'" href="<?php echo $v; ?>" />
<?php
}

?>
<!-- <link rel="stylesheet" type="text/css" media="all" href="thirdparty/flatsome/flatsome.css" /> --> 
<!-- <link rel="stylesheet" type="text/css" media="all" href="frontend/css/swiper.min.css" /> --> 
<script src="<?php echo CDN_BASE_URL; ?>thirdparty/jquery/jquery-3.6.0.min.js"></script> 
<!-- <script src="thirdparty/jquery/jquery-migrate-3.3.2.min.js"></script> --> 
<!-- <script src="frontend/js/swiper.min.js"></script> -->
<?php

// in ra m?? m??u d???ng global ????? ti???n thay ?????i
echo HtmlTemplate::html( 'root_color.txt', [
    'default_bg' => $getconfig->default_bg,
    'sub_bg' => $getconfig->sub_bg,
    'default_color' => $getconfig->default_color,
    'a_color' => $getconfig->a_color,
] );

//
$base_model->preloads_css( [
    'css/d.css',
    'css/d2.css',
], [
    'cdn' => CDN_BASE_URL,
] );
$base_model->adds_css( [
    'css/flatsome.css',
    'css/thread_list.css',
    'themes/' . THEMENAME . '/style.css',
    'themes/' . THEMENAME . '/css/thread_node.css',
], [
    'cdn' => CDN_BASE_URL,
] );


// mobile
if ( $isMobile == true ) {
    $base_model->adds_css( [
        'css/m.css',
        'themes/' . THEMENAME . '/css/m.css',
    ], [
        'cdn' => CDN_BASE_URL,
    ] );
}


// x??c ?????nh k??ch th?????c khung web d???a theo config
echo HtmlTemplate::html( 'custom_css.txt', [
    'site_max_width30' => $getconfig->site_max_width + 30,
    'site_max_width19' => $getconfig->site_max_width + 19,
    'site_max_width' => $getconfig->site_max_width,
    'site_full_width' => $getconfig->site_full_width,
] );


//
$base_model->adds_js( [
    'javascript/functions.js',
    'javascript/eb.js',
    //'javascript/slider.js',
    'themes/' . THEMENAME . '/js/functions.js',
], [
    'cdn' => CDN_BASE_URL,
] );

//
$WGR_config = [
    'cf_tester_mode' => ( $debug_enable === true ) ? 1 : 0,
    'current_user_id' => $current_user_id * 1,
    'pid' => $current_pid,
    'cid' => $current_tid,
];

?>
<script>
var WGR_config=<?php echo json_encode($WGR_config); ?>;
</script>
<?php

//
echo $getconfig->html_header;


// n???p header ri??ng c???a t???ng theme n???u c??
$theme_private_view = VIEWS_CUSTOM_PATH . 'default/get_header.php';
//echo $theme_private_view . '<br>' . "\n";
if ( file_exists( $theme_private_view ) ) {
    include $theme_private_view;
}
// kh??ng c?? th?? n???p view m???c ?????nh
else {
    include VIEWS_PATH . 'default/get_header.php';
}


// n???u c?? ID google analytics th?? n???p n??
if ( $getconfig->google_analytics != '' ) {
    echo HtmlTemplate::html( 'google_analytics.txt', [
        'google_analytics' => $getconfig->google_analytics,
    ] );
}
