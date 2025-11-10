<?php

//
use App\Helpers\HtmlTemplate;

//
//print_r( $seo );

?>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<link rel="profile" href="http://gmpg.org/xfn/11" />
<link rel="pingback" href="<?php echo DYNAMIC_BASE_URL; ?>xmlrpc.php" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<title><?php echo $seo['title']; ?></title>
<base href="<?php echo DYNAMIC_BASE_URL; ?>" />
<!-- <meta http-equiv="Cache-control" content="max-age=120" /> -->
<!-- <meta http-equiv="X-UA-Compatible" content="IE=Edge,chrome=1" /> -->
<meta name="theme-color" content="<?php echo $getconfig->default_bg; ?>" />
<meta name="msapplication-navbutton-color" content="<?php echo $getconfig->default_bg; ?>" />
<!-- <meta name="apple-mobile-web-app-capable" content="yes" /> -->
<meta name="mobile-web-app-capable" content="yes" />
<meta name="apple-mobile-web-app-status-bar-style" content="<?php echo $getconfig->default_bg; ?>" />
<!-- <meta http-equiv="x-dns-prefetch-control" content="on" /> -->
<link rel="dns-prefetch" href="https://www.google-analytics.com/" />
<link rel="dns-prefetch" href="https://connect.facebook.net/" />
<link rel="dns-prefetch" href="https://cdn.jsdelivr.net" />
<link rel="dns-prefetch" href="https://s.w.org" />
<meta name="format-detection" content="telephone=no" />
<?php

//
if ($seo['amp_url'] != '') {
?>
    <link rel="amphtml" href="<?php echo $seo['amp_url']; ?>" />
<?php
}

?>
<!-- This site is optimized with the Yoast SEO plugin -->
<link rel="shortcut icon" type="image/png" href="<?php echo $option_model->get_the_favicon($getconfig); ?>" />
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
if ($seo['canonical'] != '') {
?>
    <link rel="canonical" href="<?php echo $seo['canonical']; ?>" />
    <meta property="og:url" content="<?php echo $seo['canonical']; ?>" />
<?php
}

//
if ($seo['shortlink'] != '') {
?>
    <link href="<?php echo $seo['shortlink']; ?>" rel="shortlink" />
<?php
}

//
if (CDN_BASE_URL != '') {
?>
    <link rel="dns-prefetch" href="<?php echo CDN_BASE_URL; ?>" />
<?php
}

//
//print_r( $getconfig );
if ($getconfig->blog_private == 'on' || $seo['index'] == 'off' || isset($_GET['canonical'])) {
?>
    <meta name="robots" content="noindex, nofollow" />
<?php
}

//
if ($getconfig->fb_app_id != '') {
?>
    <meta property="fb:app_id" content="<?php echo $getconfig->fb_app_id; ?>" />
<?php
}
?>
<meta property="og:title" content="<?php echo $seo['title']; ?>" />
<meta property="og:description" content="<?php echo $seo['description']; ?>" />
<meta property="og:type" content="website" />
<meta property="og:site_name" content="<?php $option_model->the_config($getconfig, 'name'); ?>" />
<meta property="og:image" content="<?php $option_model->the_og_image($seo, $getconfig); ?>" />
<meta property="og:image:alt" content="<?php $option_model->the_og_image_alt($seo, $getconfig); ?>" />
<meta property="og:updated_time" content="<?php echo $seo['updated_time']; ?>" />
<meta name="twitter:card" content="summary" />
<meta name="twitter:description" content="<?php echo $seo['description']; ?>" />
<meta name="twitter:title" content="<?php echo $seo['title']; ?>" />
<!-- Yoast SEO plugin -->
<link href="https://fonts.googleapis.com" rel="preconnect" />
<link href="https://fonts.gstatic.com" rel="preconnect" crossorigin />
<link rel="alternate" type="application/rss+xml" title="Dòng thông tin &raquo;" href="<?php echo DYNAMIC_BASE_URL; ?>feed" />
<link rel="alternate" type="application/rss+xml" title="Dòng phản hồi &raquo;" href="<?php echo DYNAMIC_BASE_URL; ?>comments/feed" />
<link rel="https://api.w.org/" href="<?php echo DYNAMIC_BASE_URL; ?>wp-json/" />
<link rel="EditURI" type="application/rsd+xml" title="RSD" href="<?php echo DYNAMIC_BASE_URL; ?>xmlrpc.php?rsd" />
<meta name="generator" content="WordPress <?php echo FAKE_WORDPRESS_VERSION; ?>" />
<!-- -->
<style>
    <?php
    // nạp phần css inline để phục vụ cho bản mobile
    echo file_get_contents(PUBLIC_PUBLIC_PATH . 'wp-includes/css/mobile-usability.css', 1);
    ?>
</style>
<?php


// nạp một số css ở dạng preload
$arr_preload_bootstrap = [
    // bản full
    CDN_BASE_URL . 'wp-includes/thirdparty/bootstrap/css/bootstrap.min.css',
    //'wp-includes/thirdparty/bootstrap/css/bootstrap.rtl.min.css',

    // các module đơn lẻ
    //'wp-includes/thirdparty/bootstrap/css/bootstrap-grid.min.css',
    //'wp-includes/thirdparty/bootstrap/css/bootstrap-grid.rtl.min.css',
    //'wp-includes/thirdparty/bootstrap/css/bootstrap-reboot.min.css',
    //'wp-includes/thirdparty/bootstrap/css/bootstrap-reboot.rtl.min.css',
    //'wp-includes/thirdparty/bootstrap/css/bootstrap-utilities.min.css',
    //'wp-includes/thirdparty/bootstrap/css/bootstrap-utilities.rtl.min.css',
];

// nạp file font theo kiểu inline
//echo $getconfig->disable_fontawesome4;
if ($getconfig->disable_fontawesome4 != 'on') {
    $font_awesome_before = $base_model->get_add_css('wp-includes/thirdparty/awesome47/css/font-awesome.before.css', [
        'get_content' => 1
    ]);
    $font_awesome_before = str_replace('../fonts/', 'wp-includes/thirdparty/awesome47/fonts/', $font_awesome_before);
    echo $font_awesome_before;

    //
    $arr_preload_bootstrap[] = CDN_BASE_URL . 'wp-includes/thirdparty/awesome47/css/font-awesome.min.css?v=4.7';
}

foreach ($arr_preload_bootstrap as $v) {
?>
    <link rel="preload" as="style" onload="this.rel='stylesheet';this.onload=null;" href="<?php echo $v; ?>" />
<?php
}

?>
<!-- <link rel="stylesheet" type="text/css" media="all" href="wp-includes/thirdparty/flatsome/flatsome.css" /> -->
<!-- <link rel="stylesheet" type="text/css" media="all" href="frontend/css/swiper.min.css" /> -->
<script src="<?php echo CDN_BASE_URL; ?>wp-includes/thirdparty/jquery/jquery-3.6.1.min.js"></script>
<!-- <script src="<?php echo CDN_BASE_URL; ?>wp-includes/thirdparty/jquery/jquery-3.7.1.min.js"></script> -->
<!-- <script src="wp-includes/thirdparty/jquery/jquery-migrate-3.3.2.min.js"></script> -->
<!-- <script src="frontend/js/swiper.min.js"></script> -->
<?php

// in ra mã màu dạng global để tiện thay đổi
/*
echo HtmlTemplate::html('root_color.txt', [
    'default_bg' => $getconfig->default_bg,
    'sub_bg' => $getconfig->sub_bg,
    'default_color' => $getconfig->default_color,
    'a_color' => $getconfig->a_color,
]);
*/

//
$base_model->preloads_css([
    'wp-includes/css/d.css',
    'wp-includes/css/d1.css',
    'wp-includes/css/d2.css',
], [
    'cdn' => CDN_BASE_URL,
]);
$base_model->adds_css([
    'wp-includes/css/flatsome.css',
    // thread_list
    'wp-includes/css/products_list.css',
    'wp-includes/css/posts_list.css',
    THEMEPATH . 'style.css',
    THEMEPATH . 'css/posts_node.css',
    THEMEPATH . 'css/products_node.css',
], [
    'cdn' => CDN_BASE_URL,
]);


// mobile
if ($isMobile == true) {
    $base_model->adds_css([
        'wp-includes/css/m.css',
        THEMEPATH . 'css/m.css',
    ], [
        'cdn' => CDN_BASE_URL,
    ]);
}


//
include __DIR__ . '/head_currency.php';

// thêm phầm css tùy chỉnh
$custom_css = '';
if ($getconfig->custom_css != '') {
    $custom_css .= $getconfig->custom_css;
}
if ($getconfig->custom_desktop_css != '') {
    $custom_css .= '@media only screen and (min-width:748px) {' . $getconfig->custom_desktop_css . '}';
}
if ($getconfig->custom_table_css != '') {
    $custom_css .= '@media only screen and (max-width:788px) {' . $getconfig->custom_table_css . '}';
}
if ($getconfig->custom_mobile_css != '') {
    $custom_css .= '@media only screen and (max-width:395px) {' . $getconfig->custom_mobile_css . '}';
}

// xác định kích thước khung web dựa theo config
echo HtmlTemplate::html('custom_css.txt', [
    'default_bg' => $getconfig->default_bg,
    'sub_bg' => $getconfig->sub_bg,
    'default_color' => $getconfig->default_color,
    'a_color' => $getconfig->a_color,
    //
    'site_max_width60' => $getconfig->site_max_width + 60,
    'site_max_width30' => $getconfig->site_max_width + 30,
    'site_max_width19' => $getconfig->site_max_width + 19,
    'site_max_width' => $getconfig->site_max_width,
    'site_full_width' => $getconfig->site_full_width,
    //
    'body_font_size' => $getconfig->body_font_size,
    'bodym_font_size' => $getconfig->bodym_font_size,
    //
    'ebe_currency' => $ebe_currency . $custom_css,
]);


//
$base_model->JSON_parse([
    'WGR_config' => [
        'cf_tester_mode' => ($debug_enable === true) ? 1 : 0,
        'current_user_id' => $current_user_id * 1,
        'site_lang_sub_dir' => (SITE_LANGUAGE_SUB_FOLDER === true) ? 1 : 0,
        'pid' => $current_pid,
        'cid' => $current_cid,
        'sid' => $current_sid,
        'date_format' => EBE_DATE_FORMAT,
        'currency_big_format' => $getconfig->currency_big_format,
        'currency_fraction_digits' => $getconfig->currency_fraction_digits,
        'currency_locales_format' => $getconfig->currency_locales_format,
        'currency_sd_format' => $getconfig->currency_sd_format,
        'pagination_display_1' => $lang_model->get_the_text('pagination_display_1', 'Showing page'),
        'media_url' => CDN_UPLOADS_URL,
    ],
]);


//
$base_model->adds_js([
    'wp-includes/javascript/functions.js',
    'wp-includes/javascript/eb.js',
    //'wp-includes/javascript/slider.js',
    THEMEPATH . 'js/functions.js',
], [
    'cdn' => CDN_BASE_URL,
]);

// nạp thư viện vuejs nếu có yêu cầu
if ($getconfig->enable_vue_js == 'on') {
    $base_model->add_js('wp-includes/thirdparty/vuejs/vue.min.js', [
        'cdn' => CDN_BASE_URL,
    ], [
        'defer'
    ]);
}

//
$base_model->get_the_custom_html($getconfig, 'html_header');

// nạp header riêng của từng theme (tương tự function get_header bên wordpress)
$theme_private_view = VIEWS_CUSTOM_PATH . 'get_header.php';
//echo $theme_private_view;
include VIEWS_PATH . 'private_require_view.php';


// nạp mã của analytics (nếu có)
if ($getconfig->google_analytics != '') {
    echo HtmlTemplate::html('google_analytics.html', [
        'google_analytics' => $getconfig->google_analytics,
    ]);
}

// nạp mã của adsense (nếu có)
if ($getconfig->google_adsense != '') {
    echo HtmlTemplate::html('google_adsense.html', [
        'google_adsense' => $getconfig->google_adsense,
    ]);
}
