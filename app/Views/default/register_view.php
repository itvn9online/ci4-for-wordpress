<?php

//
include VIEWS_PATH . 'default/login_header.php';

?>
<div id="loginbox">
    <form id="loginform" name="loginform" class="form-vertical" accept-charset="utf-8" action="<?php echo $form_action; ?>" method="post" target="target_eb_iframe">
        <?php $base_model->anti_spam_field(); ?>
        <div class="control-group normal_text">
            <h3>
                <?php echo $seo['title']; ?>
            </h3>
        </div>
        <div class="control-group">
            <div class="controls">
                <div class="main_input_box"> <span class="add-on greencolor"><i class="fa fa-envelope-o"></i></span>
                    <input type="email" placeholder="Email" name="data[email]" value="" autofocus aria-required="true" required />
                </div>
            </div>
            <br>
            <div class="controls">
                <div class="main_input_box"> <span class="add-on redcolor"><i class="fa fa-lock"></i></span>
                    <input type="password" placeholder="<?php $lang_model->the_text('login_password', 'Password'); ?>" name="data[password]" maxlength="255" aria-required="true" required />
                </div>
            </div>
            <br>
            <div class="controls">
                <div class="main_input_box"> <span class="add-on"><i class="fa fa-lock"></i></span>
                    <input type="password" placeholder="<?php $lang_model->the_text('login_re_password', 'Re-enter Password'); ?>" name="data[password2]" maxlength="255" aria-required="true" required />
                </div>
            </div>
            <?php

            // thêm mã xác thực cho quá trình đăng ký tài khoản
            include VIEWS_PATH . 'default/login_captcha.php';

            ?>
        </div>
        <div class="form-actions cf l35">
            <a href="./guest/login"><?php $lang_model->the_text('login_label', 'Log In'); ?></a> |
            <a href="./guest/resetpass"><?php $lang_model->the_text('forgot_password_label', 'Forgot password?'); ?></a> <span class="pull-right">
                <button type="submit" class="btn btn-success"><?php echo $seo['title']; ?></button>
            </span>
        </div>
    </form>
    <p id="backtoblog" class="text-center"> <a href="<?php echo base_url(); ?>">&larr; <?php $lang_model->the_text('login_to_home', 'Back to homepage'); ?></a> </p>
</div>
<br>
<br>