<?php

/**
 * include 1 private view
 **/

//
if ($debug_enable === true) {
    echo '<div class="wgr-view-path bold">' . str_replace(PUBLIC_HTML_PATH, '', $theme_private_view) . '</div>';
}

//
//echo $theme_private_view . '<br>' . PHP_EOL;
include $theme_private_view;
