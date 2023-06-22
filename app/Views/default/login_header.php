<?php

// phần css, js thì thường sẽ giữ lại
$base_model->add_css('css/maruti-login.css');
$base_model->add_js('javascript/maruti-login.js');

//
$theme_private_view = str_replace(VIEWS_PATH, VIEWS_CUSTOM_PATH, __FILE__);
//echo $theme_private_view . '<br>' . PHP_EOL;

//
if (file_exists($theme_private_view)) {
    include $theme_private_view;
}
// không có thì nạp view mặc định
else {
?>
    <br>
    <div class="partner login_header-partner">
        <h1><a href="<?php echo PARTNER_WEBSITE; ?>?utm_source=ci4forwordpress&utm_medium=logo&utm_campaign=wp-login&utm_term=copyright&utm_content=<?php echo $_SERVER['HTTP_HOST']; ?>" target="_blank">Xây dựng bằng Codeingiter4</a></h1>
    </div>
<?php
}
