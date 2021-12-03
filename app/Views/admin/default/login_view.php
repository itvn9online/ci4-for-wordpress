<!--
<link rel="stylesheet" href="./admin/css/bootstrap.min.css" />
<link rel="stylesheet" href="./admin/css/bootstrap-responsive.min.css" />
-->
<link rel="stylesheet" href="./admin/css/maruti-login.css" />
<br>
<div class="partner">
    <h1><a href="<?php echo PARTNER_WEBSITE; ?>?utm_source=ci4forwordpress&utm_medium=logo&utm_campaign=wp_login&utm_term=copyright&utm_content=<?php echo $_SERVER['HTTP_HOST']; ?>" target="_blank">Xây dựng bằng WordPress</a></h1>
</div>
<div id="loginbox">
    <form action="./guest/login" method="POST" id="loginform" class="form-vertical" accept-charset="utf-8">
        <input type="hidden" name="<?php echo csrf_token(); ?>" value="<?php echo csrf_hash(); ?>" />
        <input type="hidden" name="login_redirect" value="<?php echo isset($_REQUEST['login_redirect']) ? urldecode($_REQUEST['login_redirect']) : ''; ?>" />
        <div class="control-group normal_text">
            <h3><?php echo $seo['title']; ?></h3>
        </div>
        <div class="control-group">
            <div class="controls">
                <div class="main_input_box"> <span class="add-on greencolor"><i class="fa fa-user"></i></span>
                    <input type="text" placeholder="Tài khoản" name="username" value="<?php echo set_value('username'); ?>" autofocus aria-required="true" required />
                </div>
            </div>
            <br>
            <div class="controls">
                <div class="main_input_box"> <span class="add-on redcolor"><i class="fa fa-lock"></i></span>
                    <input type="password" placeholder="Mật khẩu" name="password" aria-required="true" required />
                </div>
            </div>
        </div>
        <div class="form-actions cf l35"><a href="./guest/register">Đăng ký</a> | <a href="./guest/resetpass"> Quên mật khẩu?</a> <span class="pull-right">
            <input type="submit" class="btn btn-success" value="<?php echo $seo['title']; ?>" />
            </span> </div>
    </form>
    <p id="backtoblog" class="text-center"> <a href="<?php echo base_url(); ?>">&larr; Quay lại Trang chủ</a> </p>
</div>
<br>
<br>
