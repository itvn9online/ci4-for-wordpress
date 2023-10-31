<?php

/**
 * file này được include vào từng view sau đó nó sẽ kiểm tra có view riêng của từng theme không
 */
//var_dump( $debug_enable );

// default view -> được truyền vào từ parent include

// -> private view
$theme_private_view = str_replace(VIEWS_PATH, VIEWS_CUSTOM_PATH, $theme_default_view);

// Tham số dùng để file include sẽ biết có custom view hay không
$has_private_view = false;

//
if (file_exists($theme_private_view)) {
    include __DIR__ . '/private_include_view.php';

    //
    $has_private_view = true;
}
// không có thì nạp view mặc định
else {
    // nạp thông qua private_require_view -> kiểm tra có file thì sẽ nạp
    $theme_private_view = $theme_default_view;
    include __DIR__ . '/private_require_view.php';
}
