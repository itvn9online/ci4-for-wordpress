<?php
// Libraries
//use App\Libraries\UsersType;
//use App\Language\admin\AdminTranslate;

//print_r( $session_data );
//print_r( $_SESSION );

/**
 * thêm custom taxonomy
 */

//global ARR_CUSTOM_TAXONOMY;
//print_r(ARR_CUSTOM_TAXONOMY);


// TEST
//$session_data[ 'member_type' ] = UsersType::MOD;
//$session_data[ 'member_type' ] = UsersType::AUTHOR;
//$session_data[ 'member_type' ] = UsersType::MEMBER;
//$session_data[ 'member_type' ] = UsersType::GUEST;

// chạy vòng lặp kiểm tra phân quyền nếu không phải admin
if ($session_data['member_type'] != $is_admin) {
    foreach ($arr_admin_menu as $k => $v) {
        //print_r( $v );

        // không tồn tại role -> quyền admin -> bỏ qua
        if (!isset($v['role'])) {
            echo '<!-- Admin role not found! -->';
            $arr_admin_menu[$k] = null;
            continue;
        }

        // nếu có role -> kiểm tra quyền truy cập
        if (!empty($v['role']) && !in_array($session_data['member_type'], $v['role'])) {
            echo '<!-- Permission deny! -->';
            $arr_admin_menu[$k] = null;
            continue;
        }

        //
        foreach ($v['arr'] as $k_sub => $v_sub) {
            //print_r( $v_sub );

            //
            if (
                isset($v_sub['role']) &&
                // phân quyền không trống
                !empty($v_sub['role']) &&
                // kiểm tra quyền truy cập
                !in_array($session_data['member_type'], $v_sub['role'])
            ) {
                echo '<!-- Permission sub deny! -->';
                $v['arr'] = null;
                $arr_admin_menu[$k] = $v;
                continue;
            }
        }

        //
        //echo $v[ 'name' ] . '<br>' . PHP_EOL;
    }
}
//print_r( $arr_admin_menu );

// đổi nền cho CSS nếu đang ở chế độ debug -> để dễ nhận diện
if ($debug_enable === true) {
    $body_class .= ' body-debug_enable';
}

?>
<!DOCTYPE html>
<html lang="<?php
            //$html_lang = LanguageCost::lang_key();
            echo (($html_lang == 'vn' || $html_lang == '') ? 'vi' : $html_lang);
            ?>" data-lang="<?php echo $html_lang; ?>" data-default-lang="<?php echo SITE_LANGUAGE_DEFAULT; ?>">

<head>
    <title>Quản trị</title>
    <meta charset="UTF-8" />
    <!-- <meta http-equiv="Cache-control" content="public"> -->
    <meta http-equiv="Cache-Control" content="max-age=0, no-cache" />
    <meta http-equiv="Pragma" content="no-cache" />
    <meta http-equiv="Expires" content="0" />
    <meta name="robots" content="noindex, nofollow" />
    <!-- <meta name="viewport" content="width=device-width, initial-scale=1.0"/> -->
    <base href="<?php echo DYNAMIC_BASE_URL; ?>" />
    <link rel="shortcut icon" type="image/png" href="<?php echo DYNAMIC_BASE_URL; ?>favicon.png" />
    <!-- bootstrap -->
    <link rel="stylesheet" type="text/css" media="all" href="wp-includes/thirdparty/bootstrap/css/bootstrap.min.css" />
    <!-- chưa có thời gian cập nhật bootstrap bản mới -> vẫn ưu tiên dùng bản cũ vậy -->
    <link rel="stylesheet" type="text/css" media="all" href="wp-includes/thirdparty/bootstrap-old/bootstrap.min-old.css" />
    <!-- <link rel="stylesheet" type="text/css" media="all" href="wp-includes/thirdparty/bootstrap-old/bootstrap.min-old-bak.css" /> -->
    <link rel="stylesheet" type="text/css" media="all" href="wp-includes/thirdparty/bootstrap-old/bootstrap-responsive.min.css" />
    <!-- END bootstrap -->
    <!-- <link rel="stylesheet" type="text/css" media="all" href="wp-includes/thirdparty/select2/select2.min.css" /> -->
    <link rel="stylesheet" type="text/css" media="all" href="wp-includes/thirdparty/select2-4.0.13/css/select2.min.css" />
    <link rel="stylesheet" type="text/css" media="all" href="wp-includes/css/my-bootstrap.css" />
    <link rel="stylesheet" type="text/css" media="all" href="wp-admin/css/fullcalendar.css" />
    <link rel="stylesheet" type="text/css" media="all" href="wp-admin/css/maruti-style.css" />
    <link rel="stylesheet" type="text/css" media="all" href="wp-admin/css/tagify.css" />
    <link rel="stylesheet" type="text/css" media="all" href="wp-admin/css/base.css" />
    <!-- <link rel="stylesheet" type="text/css" media="all" href="wp-admin/css/uniform.css"/> -->
    <link rel="stylesheet" type="text/css" media="all" href="wp-admin/css/maruti-media.css" class="skin-color" />
    <link rel="stylesheet" type="text/css" media="all" href="wp-includes/thirdparty/awesome47/css/font-awesome.before.css?v=4.7" />
    <link rel="stylesheet" type="text/css" media="all" href="wp-includes/thirdparty/awesome47/css/font-awesome.min.css?v=4.7" />
    <!--
<link rel="stylesheet" type="text/css" media="all" href="fonts/fontawesome-free-5.15.1-web/css/fontawesome.min.css" />
<link rel="stylesheet" type="text/css" media="all" href="fonts/fontawesome-free-5.15.1-web/css/brands.min.css" />
<link rel="stylesheet" type="text/css" media="all" href="fonts/fontawesome-free-5.15.1-web/css/solid.min.css" />
<link rel="stylesheet" type="text/css" media="all" href="fonts/fontawesome-free-5.15.1-web/css/v4-shims.min.css" />
-->
    <!-- <script src="wp-includes/thirdparty/validate/jquery.min.js"></script> -->
    <!-- <script src="wp-includes/thirdparty/jquery/jquery-3.6.1.min.js"></script> -->
    <script src="wp-includes/thirdparty/jquery/jquery-3.7.1.min.js"></script>
    <!-- <script src="wp-includes/thirdparty/jquery/jquery-migrate-3.3.2.min.js"></script> -->
    <!-- <script src="wp-includes/thirdparty/jquery/jquery-migrate-1.4.1.min.js"></script> -->
    <script src="wp-includes/thirdparty/validate/library.js"></script>
    <script src="wp-includes/thirdparty/validate/jquery.validate.min.js"></script>
    <script src="wp-includes/thirdparty/validate/localization/messages_vi.js"></script>
    <!-- <script src="ckeditor/ckeditor.js"></script> -->
    <script src="wp-includes/thirdparty/tinymce/tinymce.min.js?v=4.9.11"></script>
    <!-- <script src="wp-includes/thirdparty/tinymce/js/tinymce/tinymce.min.js?v=5.10.9"></script> -->
    <!-- <script src="wp-includes/thirdparty/tinymce/js/tinymce/tinymce.min.js?v=7.2.1"></script> -->
    <!-- <script src="https://cdn.ckeditor.com/ckeditor5/28.0.0/classic/ckeditor.js"></script> -->
    <!-- <script src="ckfinder/ckfinder.js"></script> -->
    <script src="wp-includes/thirdparty/jquery-ui/jquery-ui.min.js"></script>
    <link rel="stylesheet" type="text/css" media="all" href="wp-includes/thirdparty/jquery-ui/jquery-ui.min.css" />
    <!-- <script src="wp-admin/js/bootstrap.min.js"></script> -->
    <script src="wp-includes/thirdparty/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="wp-includes/thirdparty/angular/angular.min.js"></script>
    <script src="wp-includes/thirdparty/vuejs/vue<?php echo ($debug_enable !== true ? '.min' : ''); ?>.js"></script>
    <!-- <script src="wp-includes/thirdparty/bootstrap/js/bootstrap.min.js"></script> -->
    <!-- <script src="wp-includes/thirdparty/select2/select2.min.js"></script> -->
    <script src="wp-includes/thirdparty/select2-4.0.13/js/select2.min.js"></script>
    <script src="wp-includes/thirdparty/jQuery-menu-aim-master/jquery.menu-aim.js"></script>
    <?php

    $base_model->adds_css([
        //'wp-includes/css/flatsome.css',
        'wp-includes/css/flatsome-for-bootstrap.css',
        'wp-includes/css/d.css',
        //'wp-includes/css/d2.css',
        'wp-admin/css/admin_teamplate.css',
        // admin thì luôn show debug bar rồi
        'wp-admin/css/show-debug-bar.css',
        'wp-includes/thirdparty/highlighted-code/highlight.css',
    ]);

    //
    $base_model->JSON_echo([
        // mảng này sẽ in ra dưới dạng JSON hoặc number
        'allow_mysql_delete' => ALLOW_USING_MYSQL_DELETE ? 'true' : 'false',
    ], [
        // mảng này sẽ in ra dưới dạng string
    ]);

    //
    $base_model->JSON_parse([
        'is_admin' => 1,
        'arr_admin_menu' => $arr_admin_menu,
        'arr_lang_list' => $arr_lang_list,
        'WGR_config' => [
            'cf_tester_mode' => ($debug_enable === true) ? 1 : 0,
            'current_user_id' => $current_user_id * 1,
            'date_format' => EBE_DATE_FORMAT,
            'currency_big_format' => $getconfig->currency_big_format,
            'currency_fraction_digits' => $getconfig->currency_fraction_digits,
            'currency_locales_format' => $getconfig->currency_locales_format,
            'currency_sd_format' => $getconfig->currency_sd_format,
            'pagination_display_1' => $lang_model->get_the_text('pagination_display_1', 'Showing page'),
        ],
    ]);

    $base_model->adds_js([
        'wp-includes/javascript/functions_users.js',
        'wp-admin/js/admin_functions.js',
        'wp-admin/js/admin_functions2.js',
        'wp-admin/js/admin_teamplate.js',
        'wp-includes/javascript/functions.js',
        'wp-includes/javascript/functions_footer.js',
        THEMEPATH . 'js/functions.js',
        'wp-includes/javascript/eb.js',
        'wp-includes/thirdparty/highlighted-code/highlight.js',
    ]);


    //
    include VIEWS_PATH . 'includes/head_currency.php';
    ?>
    <script src="wp-includes/thirdparty/highlighted-code/prism.min.js"></script>
    <style>
        <?php echo $ebe_currency; ?>
    </style>
    <script type="text/javascript">
        var web_link = window.location.protocol + '//' + document.domain + '/';
        var admin_link = web_link + '<?php echo CUSTOM_ADMIN_URI; ?>';
        var cdn_media_link = '<?php echo CDN_UPLOADS_URL; ?>';
    </script>
</head>

<body class="is-admin <?php echo $body_class; ?>">
    <!--Header-part-->
    <div id="admin-header" class="cf whitecolor awhitecolor">
        <div class="lf f45">
            <div class="d-inline"><i class="fa fa-cog"></i> <a href="<?php echo CUSTOM_ADMIN_URI; ?>" class="lang-admin_quan_tri_he_thong">System admin</a></div>
            &nbsp; | &nbsp;
            <div class="d-inline"><i class="fa fa-home"></i> <a href="./" class="lang-admin_ve_trang_chu">Back to home</a></div>
            &nbsp; | &nbsp;
            <div class="d-inline"><span class="lang-admin_ngon_ngu">Language</span>:
                <select data-select="<?php echo $html_lang; ?>" id="admin-change-language">
                </select>
            </div>
        </div>
        <div class="lf f30">
            <div class="admin-menu-search text-center">
                <input type="search" id="admin_menu_search" placeholder="Search in admin menu. Ex: post, product, setting, config, user..." />
            </div>
            <div class="admin-menu-result">
                <div class="admin-menu-padding">
                    <div class="admin-menu-header">View search result menu for "<strong class="admin-menu-key"></strong>"</div>
                    <div id="admin_menu_result"></div>
                    <div class="admin-menu-none">No result for "<strong class="admin-menu-key"></strong>"</div>
                </div>
            </div>
        </div>
        <div class="lf f25 text-right"><span class="lang-admin_xin_chao">Howdy</span>, <a title="<?php $lang_model->the_text('admin_thong_tin_ca_nhan', 'Thông tin cá nhân'); ?>" href="users/profile">
                <?php
                echo ($session_data['display_name'] != '' ? $session_data['display_name'] : $session_data['user_login']);
                ?>
            </a> &nbsp; | &nbsp; <i class="fa fa-sign-out"></i> <a title="<?php $lang_model->the_text('admin_dang_xuat', 'Đăng xuất'); ?>" data-bs-toggle="modal" data-bs-target="#logoutModal" href="javascript:;" class="lang-admin_dang_xuat">Logout</a></div>
    </div>
    <!--close-Header-part-->
    <!--top-Header-menu-->
    <div id="adminmenumain">
        <div id="sidebar">
            <ul class="cf order-admin-menu">
            </ul>
        </div>
    </div>
    <!-- nút quay lại dành cho preview post, term -->
    <div class="preview-btn">
        <a href="#" title="Quay lại trang chính" class="btn btn-primary back-preview-mode"><i class="fa fa-arrow-left"></i></a>
        <button type="button" title="Mở rộng sang 2 bên" onclick="return expand_preview_mode();" class="btn btn-success"><i class="fa fa-arrows-h"></i></button>
        <button type="button" title="Tắt chế độ Preview" onclick="return close_preview_mode();" class="btn btn-danger"><i class="fa fa-arrows"></i></button>
    </div>
    <div id="content">
        <div id="content-header">
            <div id="breadcrumb">
                <ul class="cf">
                    <li><a href="./" title="Go to Home" class="tip-bottom"> <i class="fa fa-home"></i> Home</a>
                    </li>
                    <li><a href="<?php echo CUSTOM_ADMIN_URI; ?>" title="Go to Home" class="tip-bottom"> <i class="fa fa-cog"></i> Dashboard</a></li>
                </ul>
            </div>
        </div>
        <div class="container-fluid">
            <div class="row-fluid">
                <?php

                /*
                 * thông điệp lỗi trả về nếu có
                 */
                include dirname(__DIR__) . '/includes/msg_view.php';

                //
                ?>
                <div id="for_vue">
                    <?php
                    echo $content;
                    ?>
                </div>
            </div>
        </div>
    </div>
    <div class="text-center admin-copyright">&copy;
        <?php echo date('Y'); ?> <a href="<?php echo PARTNER_WEBSITE; ?>" target="_blank" rel="nofollow">
            <?php echo PARTNER_BRAND_NAME; ?>
        </a> - All rights reserved. Code using framework <a href="https://codeigniter.com/" target="_blank" rel="nofollow">Codeigniter
            <?php echo \CodeIgniter\CodeIgniter::CI_VERSION; ?>
        </a> - <span class="cur" onClick="$('#target_eb_iframe').addClass('show-target-eb');">Show process</span>
        - <span class="cur" onClick="return admin_print_debug_data();">Print debug data</span>
    </div>
    <?php

    // 
    $base_model->adds_js([
        'wp-admin/js/admin_seach_menu.js',
        'wp-admin/js/admin_footer.js',
        'wp-includes/javascript/datetimepicker.js',
        'wp-includes/javascript/pagination.js',
    ]);

    // 
    $base_model->adds_js([
        'wp-admin/js/active-support-label.js',
    ], [], [
        'defer'
    ]);

    ?>
    <!-- Modal logout -->
    <div class="modal fade" id="logoutModal" tabindex="-1" aria-labelledby="logoutModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="logoutModalLabel">Đăng xuất</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">Xác nhận đăng xuất khỏi hệ thống...</div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <a href="users/logout" class="d-inline btn btn-primary"><i class="fa fa-sign-out"></i> Logout</a>
                </div>
            </div>
        </div>
    </div>
    <!-- END Modal -->
    <iframe id="target_eb_iframe" name="target_eb_iframe" title="EB iframe" src="about:blank" width="99%" height="550" frameborder="0">AJAX form</iframe>
    <?php

    // cuối cùng là nạp module ngôn ngữ
    $base_model->adds_js([
        'wp-admin/langs/' . $html_lang . '.js',
        'wp-admin/js/admin_lang.js',
    ], [], [
        'defer'
    ]);

    ?>
</body>

</html>