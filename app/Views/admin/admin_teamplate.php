<?php

// Libraries
use App\ Libraries\ TaxonomyType;
use App\ Libraries\ LanguageCost;
use App\ Libraries\ UsersType;

//print_r( $session_data );
//print_r( $_SESSION );

/*
 * thêm custom taxonomy
 */
global $arr_custom_taxonomy;
//print_r( $arr_custom_taxonomy );

//$base_model = new\ App\ Models\ Base();
//$term_model = new\ App\ Models\ Term();


/*
 * nạp thêm file custom dành cho admin (nếu có)
 */
//echo THEMEPATH . '<br>' . "\n";
if ( file_exists( THEMEPATH . 'custom/admin/autoload.php' ) ) {
    include_once THEMEPATH . 'custom/admin/autoload.php';
}


?>
<!DOCTYPE html>
<html lang="en">
<head>
<title>Quản trị</title>
<meta charset="UTF-8"/>
<!-- <meta http-equiv="Cache-control" content="public"> -->
<meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate" />
<meta http-equiv="Pragma" content="no-cache" />
<meta http-equiv="Expires" content="0" />
<meta name="robots" content="noindex, nofollow" />
<!-- <meta name="viewport" content="width=device-width, initial-scale=1.0"/> -->
<base href="<?php echo DYNAMIC_BASE_URL; ?>" />
<link href="<?php echo DYNAMIC_BASE_URL; ?>favicon.png" rel="shortcut icon" type="image/png" />
<!-- bootstrap -->
<link rel="stylesheet" type="text/css" media="all" href="./thirdparty/bootstrap-5.1.3/css/bootstrap.min.css" />
<!-- chưa có thời gian cập nhật bootstrap bản mới -> vẫn ưu tiên dùng bản cũ vậy -->
<link rel="stylesheet" type="text/css" media="all" href="admin/css/bootstrap.min-old.css"/>
<link rel="stylesheet" type="text/css" media="all" href="admin/css/bootstrap-responsive.min.css"/>
<!-- END bootstrap -->
<link rel="stylesheet" type="text/css" media="all" href="./thirdparty/select2/select2.min.css"/>
<link rel="stylesheet" type="text/css" media="all" href="css/my-bootstrap.css"/>
<link rel="stylesheet" type="text/css" media="all" href="admin/css/fullcalendar.css"/>
<link rel="stylesheet" type="text/css" media="all" href="admin/css/maruti-style.css"/>
<link rel="stylesheet" type="text/css" media="all" href="admin/css/tagify.css"/>
<link rel="stylesheet" type="text/css" media="all" href="admin/css/base.css"/>
<!-- <link rel="stylesheet" type="text/css" media="all" href="admin/css/uniform.css"/> -->
<link rel="stylesheet" type="text/css" media="all" href="admin/css/maruti-media.css" class="skin-color"/>
<!-- <link rel="stylesheet" type="text/css" media="all" href="admin/css/select2.css"/> -->
<link rel="stylesheet" type="text/css" media="all" href="./thirdparty/awesome47/css/font-awesome.before.css?v=4.7"/>
<link rel="stylesheet" type="text/css" media="all" href="./thirdparty/awesome47/css/font-awesome.min.css?v=4.7"/>
<!--
<link rel="stylesheet" type="text/css" media="all" href="fonts/fontawesome-free-5.15.1-web/css/fontawesome.min.css" />
<link rel="stylesheet" type="text/css" media="all" href="fonts/fontawesome-free-5.15.1-web/css/brands.min.css" />
<link rel="stylesheet" type="text/css" media="all" href="fonts/fontawesome-free-5.15.1-web/css/solid.min.css" />
<link rel="stylesheet" type="text/css" media="all" href="fonts/fontawesome-free-5.15.1-web/css/v4-shims.min.css" />
-->
<link rel="stylesheet" type="text/css" media="all" href="./thirdparty/jquery-ui/jquery-ui-1.11.2.css?v=4.7"/>
<!--
<script type="text/javascript" src="./thirdparty/validate/jquery.min.js"></script> 
--> 
<script type="text/javascript" src="./thirdparty/jquery/jquery-3.6.0.min.js"></script> 
<!-- <script type="text/javascript" src="./thirdparty/jquery/jquery-migrate-3.3.2.min.js"></script> -->
<!-- <script type="text/javascript" src="./thirdparty/jquery/jquery-migrate-1.4.1.min.js"></script> --> 
<script type="text/javascript" src="./thirdparty/jquery-ui/datepicker.min.js?v=1.12.1"></script> 
<script type="text/javascript" src="./thirdparty/validate/library.js"></script> 
<script type="text/javascript" src="./thirdparty/validate/jquery.validate.min.js"></script> 
<script type="text/javascript" src="./thirdparty/validate/localization/messages_vi.js"></script> 
<!-- <script type="text/javascript" src="ckeditor/ckeditor.js"></script> --> 
<script type="text/javascript" src="./thirdparty/tinymce/tinymce.min.js"></script> 
<!-- <script type="text/javascript" src="https://cdn.ckeditor.com/ckeditor5/28.0.0/classic/ckeditor.js"></script> --> 
<!-- <script type="text/javascript" src="ckfinder/ckfinder.js"></script> --> 
<script type="text/javascript" src="./thirdparty/jquery/jquery-ui.min.js"></script>
<link rel="stylesheet" type="text/css" media="all" href="./thirdparty/jquery/jquery-ui.css"/>
<!-- <script type="text/javascript" src="admin/js/bootstrap.min.js"></script> --> 
<script type="text/javascript" src="./thirdparty/bootstrap-5.1.3/js/bootstrap.bundle.min.js"></script> 
<script type="text/javascript" src="./thirdparty/angular-1.8.2/angular.min.js"></script> 
<!-- <script type="text/javascript" src="./thirdparty/bootstrap-5.1.3/js/bootstrap.min.js"></script> --> 
<script type="text/javascript" src="./thirdparty/select2/select2.full.js"></script> 
<!-- <script type="text/javascript" src="admin/js/select2.min.js"></script> -->
<?php

//$base_model->add_css( 'css/flatsome.css' );
$base_model->add_css( 'css/flatsome-for-bootstrap.css' );
$base_model->add_css( 'css/d.css' );
//$base_model->add_css( 'css/d2.css' );
$base_model->add_css( 'admin/css/admin_teamplate.css' );

$base_model->add_js( 'admin/js/admin_functions.js' );
$base_model->add_js( 'admin/js/admin_teamplate.js' );
$base_model->add_js( 'javascript/functions.js' );
$base_model->add_js( 'javascript/eb.js' );

// đổi nền cho CSS nếu đang ở chế độ debug -> để dễ nhận diện
if ( $debug_enable === true ) {
    ?>
<style>
#admin-header {
    border-top-color: black;
}
#admin-header, #sidebar {
    background-color: darkslateblue;
}
</style>
<?php
}

?>
<script>
var arr_all_taxonomy = {};
try {
    arr_all_taxonomy['<?php echo TaxonomyType::POSTS; ?>'] = JSON.parse('<?php $term_model->json_taxonomy( TaxonomyType::POSTS ); ?>');
    arr_all_taxonomy['<?php echo TaxonomyType::TAGS; ?>'] = JSON.parse('<?php $term_model->json_taxonomy( TaxonomyType::TAGS ); ?>');
    arr_all_taxonomy['<?php echo TaxonomyType::ADS; ?>'] = JSON.parse('<?php $term_model->json_taxonomy( TaxonomyType::ADS ); ?>');
    arr_all_taxonomy['<?php echo TaxonomyType::BLOGS; ?>'] = JSON.parse('<?php $term_model->json_taxonomy( TaxonomyType::BLOGS ); ?>');
    arr_all_taxonomy['<?php echo TaxonomyType::BLOG_TAGS; ?>'] = JSON.parse('<?php $term_model->json_taxonomy( TaxonomyType::BLOG_TAGS ); ?>');
    arr_all_taxonomy['<?php echo TaxonomyType::OPTIONS; ?>'] = JSON.parse('<?php $term_model->json_taxonomy( TaxonomyType::OPTIONS ); ?>');
    <?php
    //
    foreach ($arr_custom_taxonomy as $k => $v) {
        ?>
    arr_all_taxonomy['<?php echo $k; ?>'] = JSON.parse( '<?php $term_model->json_taxonomy( $k ); ?>' );
    <?php
    }
    ?>
} catch ( e ) {
    WGR_show_try_catch_err( e );
    //arr_all_taxonomy = {};
}
//
var web_link = window.location.protocol + '//' + document.domain + '/';
var admin_link = web_link + '<?php echo CUSTOM_ADMIN_URI; ?>';
</script>
</head>
<body class="<?php echo $body_class; ?>">
<div id="admin_custom_alert" onClick="$('#admin_custom_alert').fadeOut();"></div>
<!--Header-part-->
<div id="admin-header" class="cf whitecolor awhitecolor">
    <div class="lf f50">
        <div class="d-inline"><a href="./<?php echo CUSTOM_ADMIN_URI; ?>"><i class="fa fa-cog"></i> Quản trị hệ thống</a></div>
        &nbsp; | &nbsp;
        <div class="d-inline"><a href="./"><i class="fa fa-home"></i> Về trang chủ</a></div>
        &nbsp; | &nbsp;
        <div class="d-inline">Ngôn ngữ:
            <select data-select="<?php echo LanguageCost::lang_key(); ?>" class="admin-change-language">
                <?php

                // cho phép đổi ngôn ngữ ngay trong admin
                foreach ( LanguageCost::list() as $k => $v ) {
                    ?>
                <option value="<?php echo $k; ?>"><?php echo $v; ?></option>
                <?php
                }

                ?>
            </select>
        </div>
    </div>
    <div class="lf f50 text-right">Xin Chào: <a title="Thông tin cá nhân" href="./users/profile"><?php echo $session_data['userName'] != '' ? $session_data['userName'] : $session_data['user_login']; ?></a> &nbsp; | &nbsp; <a title="Đăng xuất" data-bs-toggle="modal" data-bs-target="#logoutModal" href="javascript:;"><i class="fa fa-sign-out"></i> Logout</a></div>
</div>
<!--close-Header-part--> 
<!--top-Header-menu-->
<div id="sidebar">
    <ul class="cf order-admin-menu">
        <?php

        // TEST
        //$session_data[ 'member_type' ] = UsersType::MOD;
        //$session_data[ 'member_type' ] = UsersType::AUTHOR;
        //$session_data[ 'member_type' ] = UsersType::MEMBER;
        //$session_data[ 'member_type' ] = UsersType::GUEST;

        // chạy vòng lặp kiểm tra phân quyền nếu không phải admin
        if ( $session_data[ 'member_type' ] != UsersType::ADMIN ) {
            foreach ( $arr_admin_menu as $k => $v ) {
                //print_r( $v );

                // không tồn tại role -> bỏ qua
                if ( !isset( $v[ 'role' ] ) ) {
                    echo '<!-- Admin role not found! -->';
                    $arr_admin_menu[ $k ] = null;
                    continue;
                }

                // nếu có role -> kiểm tra quyền truy cập
                if ( !empty( $v[ 'role' ] ) && !in_array( $session_data[ 'member_type' ], $v[ 'role' ] ) ) {
                    echo '<!-- Permission deny! -->';
                    $arr_admin_menu[ $k ] = null;
                    continue;
                }

                //
                foreach ( $v[ 'arr' ] as $k_sub => $v_sub ) {
                    //print_r( $v_sub );

                    //
                    if ( isset( $v_sub[ 'role' ] ) &&
                        // phân quyền không trống
                        !empty( $v_sub[ 'role' ] ) &&
                        // kiểm tra quyền truy cập
                        !in_array( $session_data[ 'member_type' ], $v_sub[ 'role' ] ) ) {
                        echo '<!-- Permission sub deny! -->';
                        $v[ 'arr' ] = null;
                        $arr_admin_menu[ $k ] = $v;
                        continue;
                    }
                }

                //
                //echo $v[ 'name' ] . '<br>' . "\n";
            }
        }
        //print_r( $arr_admin_menu );

        ?>
    </ul>
</div>
<div id="content-header">
    <div id="breadcrumb">
        <ul class="cf">
            <li><a href="./" title="Go to Home" class="tip-bottom"> <i class="fa fa-home"></i> Trang chủ</a></li>
            <li><a href="./<?php echo CUSTOM_ADMIN_URI; ?>" title="Go to Home" class="tip-bottom"> <i class="fa fa-cog"></i> Quản trị</a></li>
        </ul>
    </div>
</div>
<div id="content">
    <div class="container-fluid">
        <div class="row-fluid"> <?php echo $content; ?> </div>
    </div>
</div>
<div class="text-center admin-copyright">&copy; <?php echo date('Y'); ?> <a href="https://echbay.com/" target="_blank" rel="nofollow">EchBay.com</a> - All rights reserved. Code using framework <a href="https://codeigniter.com/" target="_blank" rel="nofollow">Codeigniter <?php echo \CodeIgniter\CodeIgniter::CI_VERSION; ?></a> - <span class="cur" onClick="$('#target_eb_iframe').attr({'height':250});">Show process</span></div>
<script>
var arr_admin_menu = <?php echo json_encode($arr_admin_menu); ?>;
</script>
<?php
$base_model->add_js( 'admin/js/admin_footer.js' );
$base_model->add_js( 'admin/js/active-support-label.js' );
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
                <a href="./users/logout" class="d-inline">
                <button type="button" class="btn btn-primary"><i class="fa fa-sign-out"></i> Logout</button>
                </a> </div>
        </div>
    </div>
</div>
<!-- END Modal -->
<iframe id="target_eb_iframe" name="target_eb_iframe" src="about:blank" width="99%" height="250" frameborder="0">AJAX form</iframe>
</body>
</html>