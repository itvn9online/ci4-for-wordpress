<?php

//$base_model = new\ App\ Models\ Base();
//$option_model = new\ App\ Models\ Option();

use App\ Libraries\ LanguageCost;
//echo LanguageCost::lang_key();
//die( __FILE__ . ':' . __LINE__ );

?>
<!doctype html>
<html lang="<?php
            $html_lang = LanguageCost::lang_key();
            if ( $html_lang =='vn'||$html_lang== '' ) {
                $html_lang='vi';
            }
            echo $html_lang;
            ?>" class="no-js no-svg" prefix="og: http://ogp.me/ns# fb: http://ogp.me/ns/fb#">
<head>
<?php


/*
 * đoạn head dùng chung cho toàn website
 */
require __DIR__ . '/includes/head_global.php';


?>
</head>
<body class="<?php echo $seo['body_class']; ?>">
<?php

//
echo $header;

//
echo $breadcrumb;


/*
 * thông điệp lỗi trả về nếu có
 */
include __DIR__ . '/msg_view.php';


/*
 * nạp view riêng của từng theme nếu có
 */
$theme_private_view = THEMEPATH . 'Views/' . basename( __FILE__ );
//echo $theme_private_view . '<br>' . "\n";

//
if ( file_exists( $theme_private_view ) ) {
    include $theme_private_view;
}
// không có thì nạp view mặc định
else {
    include __DIR__ . '/default/' . basename( __FILE__ );
}


//
echo $footer;


/*
 * đoạn head dùng chung cho toàn website
 */
require __DIR__ . '/includes/footer_global.php';


?>
</body>
</html>