<?php

//
if (!isset($seo)) {
    $seo = $base_model->default_seo('Users', 'users/profile');
}

//
if (!isset($breadcrumb)) {
    $breadcrumb = '';
}

?>
<!doctype html>
<html lang="<?php
            echo (($html_lang == 'vn' || $html_lang == '') ? 'vi' : $html_lang);
            ?>" class="no-js no-svg" prefix="og: http://ogp.me/ns# fb: http://ogp.me/ns/fb#">

<head>
    <?php


    /*
     * đoạn head dùng chung cho toàn website
     */
    require __DIR__ . '/includes/head_global.php';

    //
    $base_model->adds_css(
        [
            'wp-includes/css/users.css',
            THEMEPATH . 'css/users.css',
        ],
        [
            'cdn' => CDN_BASE_URL,
        ]
    );

    ?>
</head>

<body data-session="<?php echo $base_model->MY_sessid(); ?>" class="<?php echo $seo['body_class']; ?> is-<?php echo $current_user_type; ?>">
    <?php

    //
    echo $header;

    //
    echo $breadcrumb;


    /*
     * thông điệp lỗi trả về nếu có
     */
    include __DIR__ . '/includes/msg_view.php';


    /*
     * nạp view riêng của từng theme nếu có
     */
    $theme_default_view = VIEWS_PATH . 'default/' . basename(__FILE__);
    // nạp file kiểm tra private view
    include VIEWS_PATH . 'private_view.php';


    //
    echo $footer;


    /*
     * đoạn footer dùng chung cho toàn website
     */
    require __DIR__ . '/includes/footer_global.php';

    //
    $base_model->adds_js([
        'wp-includes/javascript/users.js',
        THEMEPATH . 'js/users.js',
    ], [
        'cdn' => CDN_BASE_URL,
    ], [
        'defer'
    ]);

    ?>
    <iframe id="target_eb_iframe" name="target_eb_iframe" title="EB iframe" src="about:blank" width="333" height="550"></iframe>
</body>

</html>