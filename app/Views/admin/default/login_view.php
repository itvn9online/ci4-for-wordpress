<?php

include APPPATH . 'Views/admin/default/login_header.php';

?>
<div id="loginbox">
    <form action="./guest/login" method="POST" id="loginform" class="form-vertical" accept-charset="utf-8">
        <?php $base_model->csrf_field(); ?>
        <input type="hidden" name="login_redirect" value="<?php echo isset($_REQUEST['login_redirect']) ? urldecode($_REQUEST['login_redirect']) : ''; ?>" />
        <div class="control-group normal_text">
            <h3><?php echo $seo['title']; ?></h3>
        </div>
        <div class="control-group">
            <div class="controls">
                <div class="main_input_box"> <span class="add-on greencolor"><i class="fa fa-user"></i></span>
                    <input type="text" placeholder="Tài khoản" name="username" value="" autofocus aria-required="true" required />
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
