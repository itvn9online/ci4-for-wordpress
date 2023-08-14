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
    $base_model->add_css(
        'css/users.css',
        [
            'cdn' => CDN_BASE_URL,
        ]
    );

    ?>
</head>

<body class="<?php echo $seo['body_class']; ?> is-<?php echo $current_user_type; ?>">
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

    ?>
</body>

</html>