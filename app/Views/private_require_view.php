<?php

/**
 * include 1 private view nhưng sẽ kiểm tra có mới include
 **/

//
//echo $theme_private_view . '<br>' . PHP_EOL;
if (is_file($theme_private_view)) {
    include __DIR__ . '/private_include_view.php';
}
