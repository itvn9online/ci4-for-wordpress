<?php

//
$uri_quick_upload = [];
foreach ($_GET as $k => $v) {
    //echo $k . '<br>' . PHP_EOL;
    $uri_quick_upload[] = $k . '=' . $v;
}

//
if ($mode == 'list') {
    $inc_style = 'list_list';
} else {
    $inc_style = 'list_grid';
}

//
if (isset($_GET['quick_upload'])) {
    //$uri_quick_upload = '&quick_upload=1';
?>
    <style>
        body {
            background: white;
            padding-top: 0;
            padding-left: 0;
        }

        #admin-header,
        #adminmenumain,
        #sidebar,
        #content-header,
        .admin-copyright,
        .hide-if-quick-edit,
        #target_eb_iframe {
            display: none !important;
        }

        /*
.show-if-quick-upload {
    display: block !important;
}
    */
    </style>
<?php
}

//
$str_insert_to = '';
if (isset($_GET['insert_to'])) {
    $str_insert_to = $_GET['insert_to'];
?>
    <div class="rf"><strong onClick="return hide_if_esc();" class="cur medium18"><i class="fa fa-close"></i></strong></div>
    <p class="text-center redcolor">* Bấm vào dấu <strong class="greencolor"><i class="fa fa-plus"></i></strong> hoặc bấm
        <strong>đúp chuột</strong> vào hình ảnh để nhúng ảnh vào nội dung
    </p>
<?php
}

//
$add_img_tag = '';
if (isset($_GET['add_img_tag'])) {
    $add_img_tag = $_GET['add_img_tag'];
}

//
$img_size = '';
if (isset($_GET['img_size'])) {
    $img_size = $_GET['img_size'];
}

//
$input_type = '';
if (isset($_GET['input_type'])) {
    $input_type = $_GET['input_type'];
}

//
if (!empty($uri_quick_upload)) {
    $uri_quick_upload = '&' . implode('&', $uri_quick_upload);
} else {
    $uri_quick_upload = '';
}
