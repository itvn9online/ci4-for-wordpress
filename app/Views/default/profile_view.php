<div id="loginbox" class="s14 global-profile_view">
    <div class="user-info_form">
        <form name="profile_form" class="form-vertical" accept-charset="utf-8" action="" method="post" target="target_eb_iframe" enctype="multipart/form-data">
            <div class="control-group normal_text">
                <h3><?php echo $seo['title']; ?></h3>
            </div>
            <br>
            <div class="s14 main-profile">
                <div id="data-user_email">
                    <div class="row change-user_email">
                        <div class="col small-12 medium-4 large-4">Email</div>
                        <div class="col small-12 medium-8 large-8">
                            <?php echo $data['user_email']; ?> -
                            <em class="cur bluecolor click-change-email"><?php $lang_model->the_text('account_change_email', 'Change your email'); ?> <i class="fa fa-edit"></i></em>
                        </div>
                    </div>
                    <div class="row changed-user_email d-none">
                        <div class="col small-12 medium-4 large-4 l40">Email (bắt buộc)</div>
                        <div class="col small-12 medium-8 large-8">
                            <div class="form-control">
                                <input type="email" placeholder="Email" name="data[user_email]" id="data_user_email" value="<?php echo $data['user_email']; ?>" disabled readonly aria-required="true" required>
                            </div>
                            <div class="top-menu-space10">
                                <?php
                                if (strpos($data['user_email'], '@' . $_SERVER['HTTP_HOST']) === false) {
                                ?>
                                    Khi bạn thay đổi email, chúng tôi sẽ gửi một mail xác nhận
                                    đến địa chỉ email cũ. <strong>Email mới sẽ không được kích hoạt cho đến khi bạn xác nhận
                                        thay đổi</strong>
                                <?php
                                } else {
                                ?>
                                    Email hiện tại là email tự động được tạo ra bởi hệ thống và không thể sử dụng nó. Vui lòng đổi sang email riêng của bạn (nếu có)
                                <?php
                                }
                                ?>
                                - <em class="cur bluecolor cancel-change-email">Hủy bỏ <i class="fa fa-remove"></i></em></div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col small-12 medium-4 large-4"><?php $lang_model->the_text('profile_nickname', 'Nickname'); ?></div>
                    <div class="col small-12 medium-8 large-8">
                        <?php
                        echo $data['user_login'];


                        // chức năng riêng dành cho admin
                        if (isset($session_data['userLevel']) && $session_data['userLevel'] > 0) {
                        ?>
                            <a href="./<?php echo CUSTOM_ADMIN_URI; ?>">@</a>
                        <?php
                        }
                        ?>
                    </div>
                </div>
                <div class="row">
                    <div class="col small-12 medium-4 large-4 l40"><?php $lang_model->the_text('profile_avatar', 'Avatar'); ?></div>
                    <div class="col small-12 medium-8 large-8">
                        <label data-updating="1" for="file-input-media" id="click-chose-media"> <img src="wp-includes/images/_blank.png" height="150" <?php if ($data['avatar'] != '') { ?>style="background-image: url(
                            <?php echo $data['avatar']; ?>);" <?php } ?> />
                            <input id="file-input-media" accept="image/*" type="file" class="cur" />
                            <input type="hidden" name="data[avatar]" id="file-input-avatar" value="<?php echo $data['avatar']; ?>" />
                        </label>
                    </div>
                </div>
                <div class="row">
                    <div class="col small-12 medium-4 large-4 l40"><?php $lang_model->the_text('profile_full_name', 'Full name'); ?></div>
                    <div class="col small-12 medium-8 large-8">
                        <div class="form-control">
                            <input type="text" placeholder="<?php $lang_model->the_text('profile_full_name', 'Full name'); ?>" name="data[display_name]" id="data_display_name" value="<?php echo $data['display_name']; ?>" aria-required="true" required>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col small-12 medium-4 large-4 l40"><?php $lang_model->the_text('profile_shortname', 'Shortname'); ?></div>
                    <div class="col small-12 medium-8 large-8">
                        <div class="form-control">
                            <input type="text" placeholder="<?php $lang_model->the_text('profile_shortname', 'Shortname'); ?>" name="data[user_nicename]" id="data_user_nicename" value="<?php echo $data['user_nicename']; ?>" aria-required="true" required>
                        </div>
                    </div>
                </div>
                <div class="row data-user_birthday">
                    <div class="col small-12 medium-4 large-4 l40"><?php $lang_model->the_text('profile_birthday', 'Birthday'); ?></div>
                    <div class="col small-12 medium-8 large-8">
                        <div class="form-control">
                            <input type="date" placeholder="<?php $lang_model->the_text('profile_birthday', 'Birthday'); ?>" name="data[user_birthday]" value="<?php echo $data['user_birthday']; ?>">
                        </div>
                    </div>
                </div>
                <div class="row data-user_phone">
                    <div class="col small-12 medium-4 large-4 l40"><?php $lang_model->the_text('profile_cell_phone', 'Cell phone'); ?></div>
                    <div class="col small-12 medium-8 large-8">
                        <div class="form-control">
                            <input type="text" placeholder="<?php $lang_model->the_text('profile_cell_phone', 'Cell phone'); ?>" name="data[user_phone]" value="<?php echo $data['user_phone']; ?>">
                        </div>
                    </div>
                </div>
            </div>
            <div class="form-actions text-center">
                <button type="submit" class="btn btn-success"><i class="fa fa-save"></i> <?php $lang_model->the_text('profile_update', 'Update'); ?></button>
            </div>
        </form>
    </div>
    <hr />
    <div class="user-pasword_form">
        <form name="pasword_form" class="form-vertical" accept-charset="utf-8" action="" method="post" target="target_eb_iframe">
            <div class="control-group normal_text">
                <h3><?php $lang_model->the_text('profile_change_password', 'Change login password'); ?></h3>
            </div>
            <br>
            <div class="s14">
                <div class="row">
                    <div class="col small-12 medium-4 large-4 l40"><?php $lang_model->the_text('profile_new_password', 'New password'); ?></div>
                    <div class="col small-12 medium-8 large-8">
                        <div class="form-control">
                            <input type="text" placeholder="<?php $lang_model->the_text('profile_new_password', 'New password'); ?>" name="data[ci_pass]" id="data_ci_pass" value="" onfocus="jQuery('.redcolor-if-pass-focus').addClass('redcolor');" onblur="jQuery('.redcolor-if-pass-focus').removeClass('redcolor');" aria-required="true" required autocomplete="off">
                        </div>
                    </div>
                </div>
                <p class="text-center redcolor-if-pass-focus">* <em><?php $lang_model->the_text('profile_note_password', 'Only enter your password when you need to change your login password'); ?></em>.</p>
            </div>
            <div class="form-actions text-center">
                <button type="submit" class="btn btn-success"><i class="fa fa-key"></i> <?php $lang_model->the_text('profile_update_password', 'Update password'); ?></button>
            </div>
        </form>
        <hr />
    </div>
    <div>
        <div class="row">
            <div class="col small-12 medium-4 large-4"><?php $lang_model->the_text('profile_registration_date', 'Registration date'); ?></div>
            <div class="col small-12 medium-8 large-8">
                <?php echo $data['user_registered']; ?>
            </div>
        </div>
        <div class="row">
            <div class="col small-12 medium-4 large-4"><?php $lang_model->the_text('profile_last_login', 'Last login'); ?></div>
            <div class="col small-12 medium-8 large-8">
                <?php echo $data['last_login']; ?> (<?php echo $data['login_type']; ?>)
            </div>
        </div>
        <div class="row">
            <div class="col small-12 medium-4 large-4"><?php $lang_model->the_text('profile_last_updated', 'Last updated'); ?></div>
            <div class="col small-12 medium-8 large-8">
                <?php echo $data['last_updated']; ?>
            </div>
        </div>
    </div>
</div>
<br>