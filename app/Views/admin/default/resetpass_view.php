<?php

include __DIR__ . '/login_header.php';

?>
<div id="loginbox">
    <form id="loginform" class="form-vertical" accept-charset="utf-8" action="./guest/resetpass" method="POST">
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
        </div>
        <div class="form-actions cf l35"><a href="./guest/login">Đăng nhập</a> | <a href="./guest/register">Đăng ký</a> <span class="pull-right">
            <input type="submit" class="btn btn-success" value="<?php echo $seo['title']; ?>" />
            </span> </div>
    </form>
    <p id="backtoblog" class="text-center"> <a href="<?php echo base_url(); ?>">&larr; Quay lại Trang chủ</a> </p>
</div>
<br>
<br>
