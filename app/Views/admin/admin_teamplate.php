<?php

//print_r( $session_data );

//$base_model = new\ App\ Models\ Base();
$term_model = new\ App\ Models\ Term();

// Libraries
use App\ Libraries\ TaxonomyType;
use App\ Libraries\ LanguageCost;
use App\ Libraries\ UsersType;


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
<!-- <meta name="viewport" content="width=device-width, initial-scale=1.0"/> -->
<base href="<?php echo DYNAMIC_BASE_URL; ?>" />
<link href="<?php echo DYNAMIC_BASE_URL; ?>favicon.png" rel="shortcut icon" type="image/png" />
<link rel="stylesheet" href="admin/css/bootstrap.min.css"/>
<link rel="stylesheet" href="admin/css/bootstrap-responsive.min.css"/>
<!-- <link rel="stylesheet" href="outsource/bootstrap-5.0.2/css/bootstrap.min.css" type="text/css" /> -->
<link rel="stylesheet" href="outsource/select2/select2.min.css"/>
<link rel="stylesheet" href="admin/css/fullcalendar.css"/>
<link rel="stylesheet" href="admin/css/maruti-style.css"/>
<link rel="stylesheet" href="admin/css/tagify.css"/>
<link rel="stylesheet" href="admin/css/base.css"/>
<!-- <link rel="stylesheet" href="admin/css/uniform.css"/> -->
<link rel="stylesheet" href="admin/css/maruti-media.css" class="skin-color"/>
<!-- <link rel="stylesheet" href="admin/css/select2.css"/> -->
<link rel="stylesheet" href="outsource/awesome4/css/font-awesome.min.css?v=4.7"/>
<!--
<link rel="stylesheet" href="fonts/fontawesome-free-5.15.1-web/css/fontawesome.min.css" type="text/css" media="all" />
<link rel="stylesheet" href="fonts/fontawesome-free-5.15.1-web/css/brands.min.css" type="text/css" media="all" />
<link rel="stylesheet" href="fonts/fontawesome-free-5.15.1-web/css/solid.min.css" type="text/css" media="all" />
<link rel="stylesheet" href="fonts/fontawesome-free-5.15.1-web/css/v4-shims.min.css" type="text/css" media="all" />
-->

<link rel="stylesheet" href="outsource/jquery-ui/jquery-ui-1.11.2.css?v=4.7"/>

<!--
<script type="text/javascript" src="outsource/validate/jquery.min.js"></script> 
--> 
<script type="text/javascript" src="outsource/jquery/jquery-3.6.0.min.js"></script> 
<script type="text/javascript" src="outsource/jquery/jquery-migrate-3.3.2.min.js"></script> 
<!-- <script type="text/javascript" src="outsource/jquery/jquery-migrate-1.4.1.min.js"></script> --> 
<script type="text/javascript" src="outsource/jquery-ui/datepicker.min.js?v=1.12.1"></script> 
<script type="text/javascript" src="outsource/validate/library.js"></script> 
<script type="text/javascript" src="outsource/validate/jquery.validate.min.js"></script> 
<script type="text/javascript" src="outsource/validate/localization/messages_vi.js"></script> 
<!-- <script type="text/javascript" src="ckeditor/ckeditor.js"></script> --> 
<script type="text/javascript" src="outsource/tinymce/tinymce.min.js"></script> 
<!-- <script src="https://cdn.ckeditor.com/ckeditor5/28.0.0/classic/ckeditor.js"></script> --> 
<!-- <script type="text/javascript" src="ckfinder/ckfinder.js"></script> --> 
<script type="text/javascript" src="outsource/jquery/jquery-ui.min.js"></script>
<link rel="stylesheet" href="outsource/jquery/jquery-ui.css"/>
<script src="admin/js/bootstrap.min.js"></script> 
<script src="outsource/select2/select2.full.js"></script> 
<!-- <script src="admin/js/select2.min.js"></script> -->
<?php

//$base_model->add_css( 'css/flatsome.css' );
$base_model->add_css( 'css/d.css' );
//$base_model->add_css( 'css/d2.css' );
$base_model->add_css( 'css/admin_teamplate.css' );

$base_model->add_js( 'javascript/admin_functions.js' );
$base_model->add_js( 'javascript/admin_teamplate.js' );
$base_model->add_js( 'javascript/functions.js' );
$base_model->add_js( 'javascript/eb.js' );

// đổi nền cho CSS nếu đang ở chế độ debug -> để dễ nhận diện
if ( $debug_enable === true ) {
    ?>
<style>
#admin-header {
    background-color: darkviolet;
    border-top-color: black;
}
#sidebar {
    background-color: darkviolet;
}
</style>
<?php
}

?>
<script>
try {
    var arr_all_taxonomy = {
        '<?php echo TaxonomyType::POSTS; ?>' : JSON.parse( '<?php $term_model->json_taxonomy( TaxonomyType::POSTS ); ?>' ),
        '<?php echo TaxonomyType::TAGS; ?>' : JSON.parse( '<?php $term_model->json_taxonomy( TaxonomyType::TAGS ); ?>' ),
        '<?php echo TaxonomyType::ADS; ?>' : JSON.parse( '<?php $term_model->json_taxonomy( TaxonomyType::ADS ); ?>' ),
        '<?php echo TaxonomyType::BLOGS; ?>' : JSON.parse( '<?php $term_model->json_taxonomy( TaxonomyType::BLOGS ); ?>' ),
        '<?php echo TaxonomyType::BLOG_TAGS; ?>' : JSON.parse( '<?php $term_model->json_taxonomy( TaxonomyType::BLOG_TAGS ); ?>' ),
        '<?php echo TaxonomyType::OPTIONS; ?>' : JSON.parse( '<?php $term_model->json_taxonomy( TaxonomyType::OPTIONS ); ?>' ),
    };
} catch ( e ) {
    WGR_show_try_catch_err( e );
    var arr_all_taxonomy = {};
}
</script>
</head>
<body>
<div id="admin_custom_alert" onClick="$('#admin_custom_alert').fadeOut();"></div>

<!--Header-part-->
<div id="admin-header" class="cf whitecolor awhitecolor">
    <div class="lf f50"><a href="./<?php echo CUSTOM_ADMIN_URI; ?>"><i class="fa fa-cog"></i> Quản trị hệ thống</a> &nbsp; | &nbsp; <a href="./"><i class="fa fa-home"></i> Về trang chủ</a> &nbsp; | &nbsp; Ngôn ngữ: <?php echo LanguageCost::list( LanguageCost::lang_key() ); ?></div>
    <div class="lf f50 text-right">Xin Chào: <a title="Thông tin cá nhân" href="./users/profile"><?php echo $session_data['userName'] != '' ? $session_data['userName'] : $session_data['user_login']; ?></a> &nbsp; | &nbsp; <a title="Đăng xuất" onClick="return confirm('Xác nhận đăng xuất khỏi hệ thống');" href="./users/logout"><i class="fa fa-sign-out"></i> <span class="text">Logout</span></a></div>
</div>
<!--close-Header-part--> 

<!--top-Header-menu-->
<div id="sidebar">
    <ul class="cf order-admin-menu">
        <?php

        // TEST
        //$session_data[ 'member_type' ] = UsersType::MOD;
        foreach ( $arr_admin_menu as $k => $v ) {
            //print_r( $v );

            // chỉ kiểm tra đối với tài khoản không pahir là admin
            if ( $session_data[ 'member_type' ] != UsersType::ADMIN ) {
                // không tồn tại role -> bỏ qua
                if ( !isset( $v[ 'role' ] ) ) {
                    echo '<!-- Admin role not found! -->';
                    continue;
                }

                // nếu có role -> kiểm tra quyền truy cập
                if ( !empty( $v[ 'role' ] ) && !in_array( $session_data[ 'member_type' ], $v[ 'role' ] ) ) {
                    echo '<!-- Permission deny! -->';
                    continue;
                }
            }

            // tạo icon
            if ( !isset( $v[ 'icon' ] ) || $v[ 'icon' ] == '' ) {
                $v[ 'icon' ] = 'fa fa-caret-right';
            }
            $v[ 'icon' ] = '<i class="' . $v[ 'icon' ] . '"></i>';

            ?>
        <li style="order: <?php echo $v['order']; ?>"><a href="<?php echo $k; ?>"><?php echo $v['icon'] . $v['name']; ?></a>
            <?php

            if ( !empty( $v[ 'arr' ] ) ) {
                echo '<ul class="sub-menu">';
                foreach ( $v[ 'arr' ] as $k_sub => $v_sub ) {
                    // tạo icon
                    if ( !isset( $v_sub[ 'icon' ] ) || $v_sub[ 'icon' ] == '' ) {
                        $v_sub[ 'icon' ] = 'fa fa-caret-right';
                    }
                    $v_sub[ 'icon' ] = '<i class="' . $v_sub[ 'icon' ] . '"></i>';

                    ?>
        <li><a href="<?php echo $k_sub; ?>"><?php echo $v_sub['icon'] . $v_sub['name']; ?></a></li>
        <?php
        }
        echo '</ul>';
        }

        ?>
        </li>
        <?php
        }

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
<div class="text-center admin-copyright">&copy; <?php echo date('Y'); ?> <a href="https://echbay.com/" target="_blank" rel="nofollow">EchBay.com</a> - All rights reserved. Code using framework <a href="https://codeigniter.com/" target="_blank" rel="nofollow">Codeigniter <?php echo CodeIgniter\CodeIgniter::CI_VERSION; ?></a> - <span class="cur" onClick="$('#target_eb_iframe').attr({'height':250});">Show process</span></div>
<?php
$base_model->add_js( 'javascript/admin_footer.js' );
?>
<iframe id="target_eb_iframe" name="target_eb_iframe" src="about:blank" width="99%" height="250" frameborder="0">AJAX form</iframe>
</body>
</html>