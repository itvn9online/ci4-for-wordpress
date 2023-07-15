<!doctype html>
<html lang="<?php
            //$html_lang = LanguageCost::lang_key();
            echo (($html_lang == 'vn' || $html_lang == '') ? 'vi' : $html_lang);
            ?>" data-lang="<?php echo $html_lang; ?>" data-default-lang="<?php echo SITE_LANGUAGE_DEFAULT; ?>" class="no-js no-svg" prefix="og: http://ogp.me/ns# fb: http://ogp.me/ns/fb#">

<head>
    <?php


    /*
     * đoạn head dùng chung cho toàn website
     */
    require __DIR__ . '/includes/head_global.php';


    ?>
    <script>
        redirect_to_canonical('<?php echo $seo['body_class']; ?>');
    </script>
    <?php

    // thêm schema cho trang chủ -> schema này tự tùy biến trong phần bản dịch
    if ($seo['body_class'] == 'home') {
        $lang_model->the_text('home_schema', '<!-- -->');
    }
    // và các trang khác nếu có
    if (isset($seo['dynamic_schema'])) {
        echo $seo['dynamic_schema'];
    }

    ?>
</head>

<body data-session="<?php echo session_id(); ?>" class="<?php echo $seo['body_class']; ?> is-<?php echo $current_user_type . ' ' . $current_user_logged; ?>">
    <?php

    //
    echo $header;

    //
    echo $breadcrumb;


    // thông điệp lỗi trả về nếu có
    include __DIR__ . '/includes/msg_view.php';

    ?>
    <main id="main" role="main">
        <?php

        // nạp view riêng của từng theme nếu có
        $theme_default_view = VIEWS_PATH . 'default/' . basename(__FILE__);
        // nạp file kiểm tra private view
        include VIEWS_PATH . 'private_view.php';

        ?>
    </main>
    <?php

    //
    echo $footer;


    // đoạn footer dùng chung cho toàn website
    require __DIR__ . '/includes/footer_global.php';


    ?>
</body>

</html>