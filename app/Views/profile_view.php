<?php

//
$base_model->add_css(
    'css/user-profile.css',
    [
        'cdn' => CDN_BASE_URL,
    ]
);


/*
 * nạp view riêng của từng theme nếu có
 */
$theme_default_view = VIEWS_PATH . 'default/' . basename(__FILE__);
// nạp file kiểm tra private view
include VIEWS_PATH . 'private_view.php';

//
$base_model->adds_js([
    'javascript/uploads.js',
    'javascript/user-profile.js',
    'javascript/datetimepicker.js',
], [
    'cdn' => CDN_BASE_URL,
], [
    'defer'
]);
