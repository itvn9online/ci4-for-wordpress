<!doctype html>
<html amp lang="vi">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $seo['title']; ?></title>
    <meta name="theme-color" content="#ff4400">
    <meta name="msapplication-navbutton-color" content="#ff4400">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="#ff4400">
    <link rel="shortcut icon" type="image/png" href="<?php echo $option_model->get_the_favicon($getconfig); ?>" />
    <link rel="canonical" href="<?php echo $full_link; ?>" />
    <!-- <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Merriweather:400,400italic,700,700italic"> -->
    <script src="https://cdn.ampproject.org/v0.js" async></script>
    <?php
    if ($getconfig->google_analytics != '') {
    ?>
        <script async custom-element="amp-analytics" src="https://cdn.ampproject.org/v0/amp-analytics-0.1.js"></script>
    <?php
    }

    //
    if ($amp_youtube === true) {
    ?>
        <script async custom-element="amp-youtube" src="https://cdn.ampproject.org/v0/amp-youtube-0.1.js"></script>
    <?php
    }

    //
    if ($amp_iframe === true) {
    ?>
        <script async custom-element="amp-iframe" src="https://cdn.ampproject.org/v0/amp-iframe-0.1.js"></script>
    <?php
    }
    ?>
    <style amp-boilerplate>
        <?php
        echo file_get_contents(PUBLIC_PUBLIC_PATH . 'wp-includes/css/amp-boilerplate.css');
        ?>
    </style>
    <!-- <link rel="stylesheet" href="<?php echo DYNAMIC_BASE_URL; ?>wp-includes/css/amp-boilerplate.css"> -->
    <noscript>
        <style amp-boilerplate>
            body {
                -webkit-animation: none;
                -moz-animation: none;
                -ms-animation: none;
                animation: none
            }
        </style>
    </noscript>
    <style amp-custom>
        <?php
        echo file_get_contents(PUBLIC_PUBLIC_PATH . 'wp-includes/css/amp-custom.css');
        ?>
    </style>
    <!-- <link rel="stylesheet" href="<?php echo DYNAMIC_BASE_URL; ?>wp-includes/css/amp-custom.css"> -->
    <script type="application/ld+json">
        <?php echo json_encode($breadcrumb_list); ?>
    </script>
    <?php
    if (!empty($blog_posting)) {
    ?>
        <script type="application/ld+json">
            <?php echo json_encode($blog_posting); ?>
        </script>
    <?php
    }
    ?>
</head>

<body>
    <header id="#top" class="amp-wp-header">
        <div><a href="<?php echo DYNAMIC_BASE_URL; ?>"><?php echo $getconfig->name; ?></a></div>
    </header>
    <article class="amp-wp-article">
        <?php

        // nạp view riêng của từng theme nếu có
        $theme_default_view = VIEWS_PATH . 'default/' . $file_view . '.php';
        // nạp file kiểm tra private view
        include VIEWS_PATH . 'private_view.php';

        ?>
        <br>
    </article>
    <article class="amp-wp-article">
        <footer class="amp-wp-article-footer">
            <div class="amp-wp-meta amp-wp-tax-category"><a href="<?php echo DYNAMIC_BASE_URL; ?>">Trang chủ</a>
                <?php
                if ($terms_link != '') {
                ?>
                    &raquo; <a href="<?php echo $terms_link; ?>"><?php echo $terms_title; ?></a>
                <?php
                }
                ?>
                &raquo; <a href="<?php echo $amp_link; ?>"><?php echo $amp_title; ?></a> </div>
        </footer>
    </article>
    <footer class="amp-wp-footer">
        <div>
            <p>&copy; Bản quyền <?php echo date('Y'); ?> <?php echo $getconfig->name; ?> - Toàn bộ phiên bản - <a href="<?php echo PARTNER_WEBSITE; ?>" target="_blank" rel="nofollow">AMP by <?php echo PARTNER_BRAND_NAME; ?></a></p>
            <p class="back-to-top"> <a href="#development=1">Nhà phát triển</a> | <a href="#top">Về đầu trang</a></p>
        </div>
    </footer>
    <div class="amp-wp-comments-link"><a href="<?php echo $full_link; ?>">Xem phiên bản đầy đủ</a></div>
    <br>
    <?php
    if ($getconfig->google_analytics != '') {
    ?>
        <amp-analytics type="gtag" data-credentials="include">
            <script type="application/json">
                {
                    "vars": {
                        "gtag_id": "<?php echo $getconfig->google_analytics; ?>",
                        "config": {
                            "<?php echo $getconfig->google_analytics; ?>": {
                                "groups": "default"
                            }
                        }
                    }
                }
            </script>
        </amp-analytics>
    <?php
    }
    ?>
</body>

</html>