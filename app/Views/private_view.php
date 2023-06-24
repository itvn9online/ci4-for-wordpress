<?php
/*
 * file này được include vào từng view sau đó nó sẽ kiểm tra có view riêng của từng theme không
 */
//var_dump( $debug_enable );

// default view -> được truyền vào từ parent include

// -> private view
$theme_private_view = str_replace(VIEWS_PATH, VIEWS_CUSTOM_PATH, $theme_default_view);

//
if (file_exists($theme_private_view)) {
    include __DIR__ . '/private_include_view.php';
}
// không có thì nạp view mặc định
else {
    if ($debug_enable === true) {
        echo '<div class="wgr-view-path">' . str_replace(PUBLIC_HTML_PATH, '', $theme_default_view) . '</div>';
    }

    //
    include $theme_default_view;
}
