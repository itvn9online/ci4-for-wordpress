<?php

// view riêng của từng post type
$theme_private_view = ADMIN_ROOT_VIEWS . $post_type . '/add.php';
// echo $theme_private_view;

// nạp view riêng nếu có
if (is_file($theme_private_view)) {
    include $theme_private_view;
} else {
    // nạp view mặc định
    include __DIR__ . '/add_default.php';
}
