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
    <script src="https://cdn.ampproject.org/v0.js" async {csp-script-nonce}></script>
    <?php

    // nạp mã của analytics (nếu có)
    if ($getconfig->google_analytics != '') {
    ?>
        <script async custom-element="amp-analytics" src="https://cdn.ampproject.org/v0/amp-analytics-0.1.js" {csp-script-nonce}></script>
    <?php
    }

    // nạp mã của adsense (nếu có)
    /*
    if ($getconfig->google_amp_adsense != '') {
    ?>
        <script async custom-element="amp-auto-ads" src="https://cdn.ampproject.org/v0/amp-auto-ads-0.1.js" {csp-script-nonce}></script>
    <?php
    }
    */

    //
    if ($amp_youtube === true) {
    ?>
        <script async custom-element="amp-youtube" src="https://cdn.ampproject.org/v0/amp-youtube-0.1.js" {csp-script-nonce}></script>
    <?php
    }

    //
    if ($amp_iframe === true) {
    ?>
        <script async custom-element="amp-iframe" src="https://cdn.ampproject.org/v0/amp-iframe-0.1.js" {csp-script-nonce}></script>
    <?php
    }

    //
    if ($amp_video === true) {
    ?>
        <script async custom-element="amp-video" src="https://cdn.ampproject.org/v0/amp-video-0.1.js" {csp-script-nonce}></script>
    <?php
    }

    //
    if ($amp_audio === true) {
    ?>
        <script async custom-element="amp-audio" src="https://cdn.ampproject.org/v0/amp-audio-0.1.js" {csp-script-nonce}></script>
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
    <?php

    // nạp mã của adsense (nếu có)
    /*
    if ($getconfig->google_amp_adsense != '') {
    ?>
        <amp-auto-ads type="adsense" data-ad-client="<?php echo $getconfig->google_amp_adsense; ?>"></amp-auto-ads>
    <?php
    }
    */

    ?>
    <header id="#top" class="amp-wp-header">
        <div><a href="<?php echo $amp_base_url; ?>"><?php echo $getconfig->name; ?></a></div>
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
            <div class="amp-wp-meta amp-wp-tax-category"><a href="<?php echo $amp_base_url; ?>"><?php echo $amp_home_label; ?></a>
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
            <p>&copy; <?php $lang_model->the_text('amp_copy_right', 'Bản quyền'); ?> <?php echo date('Y'); ?> <?php echo $getconfig->name; ?> - <?php $lang_model->the_text('amp_all_rights_reserved', 'Test version'); ?> - <a href="<?php echo PARTNER_WEBSITE; ?>" target="_blank" rel="nofollow">AMP by <?php echo PARTNER_BRAND_NAME; ?></a></p>
            <p class="back-to-top"> <a href="#development=1"><?php $lang_model->the_text('amp_development', 'Development'); ?></a> | <a href="#top"><?php $lang_model->the_text('amp_to_top', 'Back to top'); ?></a></p>
        </div>
    </footer>
    <div class="amp-wp-comments-link"><a href="<?php echo $full_link; ?>"><?php $lang_model->the_text('amp_full_version', 'View full version'); ?></a></div>
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