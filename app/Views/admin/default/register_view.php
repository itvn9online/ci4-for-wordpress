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
    <form id="loginform" class="form-vertical" accept-charset="utf-8" action="./guest/register" method="POST">
        <input type="hidden" name="<?php echo csrf_token(); ?>" value="<?php echo csrf_hash(); ?>" />
        <div class="control-group normal_text">
            <h3><?php echo $seo['title']; ?></h3>
        </div>
        <div class="control-group">
            <div class="controls">
                <div class="main_input_box"> <span class="add-on greencolor"><i class="fa fa-envelope-o"></i></span>
                    <input type="email" placeholder="Email" name="data[email]" value="<?php echo set_value('email'); ?>" autofocus aria-required="true" required />
                </div>
            </div>
            <br>
            <div class="controls">
                <div class="main_input_box"> <span class="add-on redcolor"><i class="fa fa-lock"></i></span>
                    <input type="password" placeholder="Mật khẩu" name="data[password]" maxlength="255" aria-required="true" required />
                </div>
            </div>
            <br>
            <div class="controls">
                <div class="main_input_box"> <span class="add-on"><i class="fa fa-lock"></i></span>
                    <input type="password" placeholder="Nhắc lại mật khẩu" name="data[password2]" maxlength="255" aria-required="true" required />
                </div>
            </div>
        </div>
        <div class="form-actions cf l35"><a href="./guest/login">Đăng nhập</a> | <a href="./guest/resetpass">Quên mật khẩu?</a> <span class="pull-right">
            <input type="submit" class="btn btn-success" value="<?php echo $seo['title']; ?>" />
            </span> </div>
    </form>
    <p id="backtoblog" class="text-center"> <a href="<?php echo base_url(); ?>">&larr; Quay lại Trang chủ</a> </p>
</div>
<br>
<br>
