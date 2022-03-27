<div id="loginbox" class="s14 global-profile_view">
    <form name="profile_form" class="form-vertical" accept-charset="utf-8" action="./users/profile" method="post" target="target_eb_iframe">
        <?php $base_model->csrf_field(); ?>
        <div class="control-group normal_text">
            <h3><?php echo $seo['title']; ?></h3>
        </div>
        <br>
        <div class="s14">
            <div class="row">
                <div class="col medium-4 small-12 large-4">Tài khoản</div>
                <div class="col medium-8 small-12 large-8"><?php echo $data['user_login']; ?></div>
            </div>
            <div class="row">
                <div class="col medium-4 small-12 large-4">Email</div>
                <div class="col medium-8 small-12 large-8">
                    <?php

                    //
                    echo $data[ 'user_email' ];

                    // chức năng riêng dành cho admin
                    if ( $current_user_id > 0 && isset( $session_data[ 'userLevel' ] ) && $session_data[ 'userLevel' ] > 0 ) {
                        ?>
                    <a href="./<?php echo CUSTOM_ADMIN_URI; ?>">@</a>
                    <?php
                    }

                    ?>
                </div>
            </div>
            <div class="row">
                <div class="col medium-4 small-12 large-4 l40">Họ và tên</div>
                <div class="col medium-8 small-12 large-8">
                    <div class="form-control">
                        <input type="text" placeholder="Họ và tên đệm" name="data[display_name]" value="<?php echo $data['display_name']; ?>" aria-required="true" required>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col medium-4 small-12 large-4 l40">Tên rút gọn</div>
                <div class="col medium-8 small-12 large-8">
                    <div class="form-control">
                        <input type="text" placeholder="Tên rút gọn" name="data[user_nicename]" value="<?php echo $data['user_nicename']; ?>" aria-required="true" required>
                    </div>
                </div>
            </div>
            <div class="row data-user_birthday">
                <div class="col medium-4 small-12 large-4 l40">Ngày sinh</div>
                <div class="col medium-8 small-12 large-8">
                    <div class="form-control">
                        <input type="date" placeholder="Ngày sinh" name="data[user_birthday]" value="<?php echo $data['user_birthday']; ?>">
                    </div>
                </div>
            </div>
            <div class="row data-user_phone">
                <div class="col medium-4 small-12 large-4 l40">Điện thoại liên hệ</div>
                <div class="col medium-8 small-12 large-8">
                    <div class="form-control">
                        <input type="text" placeholder="Điện thoại liên hệ" name="data[user_phone]" value="<?php echo $data['user_phone']; ?>">
                    </div>
                </div>
            </div>
        </div>
        <div class="form-actions text-center">
            <input type="submit" class="btn btn-success" value="Cập nhật thông tin cá nhân" />
        </div>
    </form>
    <hr />
    <div class="user-pasword_form">
        <form name="pasword_form" class="form-vertical" accept-charset="utf-8" action="./users/profile" method="post" target="target_eb_iframe">
            <?php $base_model->csrf_field(); ?>
            <div class="control-group normal_text">
                <h3>Đổi mật khẩu đăng nhập</h3>
            </div>
            <br>
            <div class="s14">
                <div class="row">
                    <div class="col medium-4 small-12 large-4 l40">Thay đổi mật khẩu</div>
                    <div class="col medium-8 small-12 large-8">
                        <div class="form-control">
                            <input type="text" placeholder="Thay đổi mật khẩu" name="data[ci_pass]" id="data_ci_pass" value="" onfocus="$('.redcolor-if-pass-focus').addClass('redcolor');" onblur="$('.redcolor-if-pass-focus').removeClass('redcolor');" aria-required="true" required autocomplete="off">
                        </div>
                    </div>
                </div>
                <p class="text-center redcolor-if-pass-focus">* <em>Chỉ nhập mật khẩu khi bạn cần đổi mật khẩu đăng nhập</em>.</p>
            </div>
            <div class="form-actions text-center">
                <input type="submit" class="btn btn-success" value="Thay đổi mật khẩu" />
            </div>
        </form>
        <hr />
    </div>
    <div>
        <div class="row">
            <div class="col medium-4 small-12 large-4">Ngày đăng ký</div>
            <div class="col medium-8 small-12 large-8"><?php echo $data['user_registered']; ?></div>
        </div>
        <div class="row">
            <div class="col medium-4 small-12 large-4">Đăng nhập cuối</div>
            <div class="col medium-8 small-12 large-8"><?php echo $data['last_login']; ?></div>
        </div>
        <div class="row">
            <div class="col medium-4 small-12 large-4">Cập nhật cuối</div>
            <div class="col medium-8 small-12 large-8"><?php echo $data['last_updated']; ?></div>
        </div>
    </div>
</div>
<br>
<?php

$base_model->adds_js( [
    'javascript/user-profile.js',
    'javascript/datetimepicker.js',
], [
    'cdn' => CDN_BASE_URL,
], [
    'defer'
] );
