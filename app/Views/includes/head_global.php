<?php

//
use App\Helpers\HtmlTemplate;

//
//print_r( $seo );

?>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<?php echo '<title>' . $seo['title'] . '</title>' . PHP_EOL; ?>
<base href="<?php echo DYNAMIC_BASE_URL; ?>" />
<meta http-equiv="Cache-control" content="max-age=60, private, must-revalidate">
<!-- <meta http-equiv="X-UA-Compatible" content="IE=Edge,chrome=1" /> -->
<meta name="theme-color" content="<?php echo $getconfig->default_bg; ?>">
<meta name="msapplication-navbutton-color" content="<?php echo $getconfig->default_bg; ?>">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="<?php echo $getconfig->default_bg; ?>">
<!-- <meta http-equiv="x-dns-prefetch-control" content="on"> -->
<link rel="dns-prefetch" href="https://www.google-analytics.com/" />
<link rel="dns-prefetch" href="https://connect.facebook.net/" />
<link rel="dns-prefetch" href="https://cdn.jsdelivr.net" />
<link rel="dns-prefetch" href="https://s.w.org" />
<meta name="format-detection" content="telephone=no">
<!-- SEO -->
<link href="<?php echo $option_model->get_the_favicon($getconfig); ?>" rel="shortcut icon" type="image/png" />
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
    <link href="<?php echo $seo['canonical']; ?>" rel="canonical" />
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
if ($getconfig->blog_private == 'on' || $seo['index'] == 'off') {
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
<!-- END SEO -->
<!-- -->
<link href="https://fonts.googleapis.com" rel="preconnect" />
<link href="https://fonts.gstatic.com" rel="preconnect" crossorigin />
<!-- -->
<?php

// nạp phần css inline để phục vụ cho bản mobile
?>
<style>
    <?php
    echo file_get_contents(PUBLIC_PUBLIC_PATH . 'css/mobile-usability.css', 1);
    ?>
</style>
<?php


// nạp một số css ở dạng preload
$arr_preload_bootstrap = [
    // bản full
    CDN_BASE_URL . 'thirdparty/bootstrap/css/bootstrap.min.css',
    //'thirdparty/bootstrap/css/bootstrap.rtl.min.css',

    // các module đơn lẻ
    //'thirdparty/bootstrap/css/bootstrap-grid.min.css',
    //'thirdparty/bootstrap/css/bootstrap-grid.rtl.min.css',
    //'thirdparty/bootstrap/css/bootstrap-reboot.min.css',
    //'thirdparty/bootstrap/css/bootstrap-reboot.rtl.min.css',
    //'thirdparty/bootstrap/css/bootstrap-utilities.min.css',
    //'thirdparty/bootstrap/css/bootstrap-utilities.rtl.min.css',
];

// nạp file font theo kiểu inline
//echo $getconfig->disable_fontawesome4;
if ($getconfig->disable_fontawesome4 != 'on') {
    $font_awesome_before = $base_model->get_add_css('thirdparty/awesome47/css/font-awesome.before.css', [
        'get_content' => 1
    ]);
    $font_awesome_before = str_replace('../fonts/', 'thirdparty/awesome47/fonts/', $font_awesome_before);
    echo $font_awesome_before;

    //
    $arr_preload_bootstrap[] = CDN_BASE_URL . 'thirdparty/awesome47/css/font-awesome.min.css?v=4.7';
}

foreach ($arr_preload_bootstrap as $v) {
?>
    <link rel="preload" as="style" onload="this.onload=null;this.rel='stylesheet'" href="<?php echo $v; ?>" />
<?php
}

?>
<!-- <link rel="stylesheet" type="text/css" media="all" href="thirdparty/flatsome/flatsome.css" /> -->
<!-- <link rel="stylesheet" type="text/css" media="all" href="frontend/css/swiper.min.css" /> -->
<script src="<?php echo CDN_BASE_URL; ?>thirdparty/jquery/jquery-3.6.1.min.js"></script>
<!-- <script src="thirdparty/jquery/jquery-migrate-3.3.2.min.js"></script> -->
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
    'css/d.css',
    'css/d1.css',
    'css/d2.css',
], [
    'cdn' => CDN_BASE_URL,
]);
$base_model->adds_css([
    'css/flatsome.css',
    // thread_list
    'css/products_list.css',
    'css/posts_list.css',
    'themes/' . THEMENAME . '/style.css',
    'themes/' . THEMENAME . '/css/posts_node.css',
    'themes/' . THEMENAME . '/css/products_node.css',
], [
    'cdn' => CDN_BASE_URL,
]);


// mobile
if ($isMobile == true) {
    $base_model->adds_css([
        'css/m.css',
        'themes/' . THEMENAME . '/css/m.css',
    ], [
        'cdn' => CDN_BASE_URL,
    ]);
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
]);


//
$base_model->adds_js([
    'javascript/functions.js',
    'javascript/eb.js',
    //'javascript/slider.js',
    'themes/' . THEMENAME . '/js/functions.js',
], [
    'cdn' => CDN_BASE_URL,
]);

// nạp thư viện vuejs nếu có yêu cầu
if ($getconfig->enable_vue_js == 'on') {
    $base_model->add_js('thirdparty/vuejs/vue.min.js', [
        'cdn' => CDN_BASE_URL,
    ], [
        'defer'
    ]);
}

//
$WGR_config = [
    'cf_tester_mode' => ($debug_enable === true) ? 1 : 0,
    'current_user_id' => $current_user_id * 1,
    'site_lang_sub_dir' => (SITE_LANGUAGE_SUB_FOLDER === true) ? 1 : 0,
    'pid' => $current_pid,
    'cid' => $current_tid,
];

//
$base_model->JSON_parse([
    'WGR_config' => $WGR_config,
]);

//
echo $getconfig->html_header;

// nạp header riêng của từng theme (tương tự function get_header bên wordpress)
$theme_private_view = VIEWS_CUSTOM_PATH . 'get_header.php';
include VIEWS_PATH . 'private_require_view.php';


// nếu có ID google analytics thì nạp nó
if ($getconfig->google_analytics != '') {
    echo HtmlTemplate::html('google_analytics.txt', [
        'google_analytics' => $getconfig->google_analytics,
    ]);
}
