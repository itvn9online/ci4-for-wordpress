<?php

//
include VIEWS_PATH . 'default/login_header.php';

//
//echo $firebase_config->disable_local_login;
?>
<div id="loginbox">
    <div class="control-group normal_text login-title">
        <h3><?php echo $seo['title']; ?></h3>
    </div>
    <div class="loginflex">
        <?php
        if ($firebase_config->disable_local_login != 'on') {
        ?>
            <div>
                <form id="loginform" name="loginform" class="form-vertical" accept-charset="utf-8" action="<?php echo ACTION_LOGIN_FORM; ?>" method="post" target="target_eb_iframe">
                    <?php $base_model->anti_spam_field(); ?>
                    <input type="hidden" name="login_redirect" value="<?php echo $login_redirect; ?>" />
                    <div class="control-group">
                        <div class="controls">
                            <div class="main_input_box"> <span class="add-on greencolor"><i class="fa fa-user"></i></span>
                                <input type="text" placeholder="<?php $lang_model->the_text('login_username', 'Username or Email'); ?>" name="username" value="" autofocus aria-required="true" required />
                            </div>
                        </div>
                        <br>
                        <div class="controls">
                            <div class="main_input_box"> <span class="add-on redcolor"><i class="fa fa-lock"></i></span>
                                <input type="password" placeholder="<?php $lang_model->the_text('login_password', 'Password'); ?>" name="password" aria-required="true" required />
                            </div>
                        </div>
                        <?php

                        // đăng nhập sai quá nhiều lần thì hiển thị thêm captcha để bắt xác thực
                        if ($base_model->check_faild_login() > 0) {
                            include VIEWS_PATH . 'default/login_captcha.php';
                        }

                        ?>
                    </div>
                    <div class="form-actions cf l35">
                        <div class="cf">
                            <div class="lf f62">
                                <label>
                                    <input type="checkbox" name="rememberme" id="firebase_auto_login" value="30" />
                                    <?php $lang_model->the_text('login_rememberme_label', 'Remember password'); ?>
                                </label>
                            </div>
                            <div class="lf f38 text-right">
                                <button type="submit" class="btn btn-success"><?php echo $seo['title']; ?></button>
                            </div>
                        </div>
                    </div>
                </form>
                <div class="text-center top-menu-space">
                    <?php
                    // chỉ hiển thị link đăng ký khi được phép
                    if ($getconfig->disable_register_member != 'on') {
                    ?>
                        <a href="./guest/register"><?php $lang_model->the_text('register_account_label', 'Register new account'); ?></a> |
                    <?php
                    }
                    ?>
                    <a href="./guest/resetpass"><?php $lang_model->the_text('forgot_password_label', 'Forgot password?'); ?></a>
                </div>
            </div>
        <?php
        }
        ?>
        <div class="text-center">
            <?php
            include VIEWS_PATH . 'firebase_auth_view.php';
            ?>
        </div>
    </div>
    <p id="backtoblog" class="text-center"> <a href="<?php echo base_url(); ?>">&larr; <?php $lang_model->the_text('login_to_home', 'Back to homepage'); ?></a> </p>
</div>
<br>
<br>