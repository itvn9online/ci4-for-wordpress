<div id="loginbox" class="s14 global-profile_view">
    <div class="user-info_form">
        <form name="profile_form" class="form-vertical" accept-charset="utf-8" action="" method="post" target="target_eb_iframe" enctype="multipart/form-data">
            <?php $base_model->csrf_field(); ?>
            <div class="control-group normal_text">
                <h3><?php echo $seo['title']; ?></h3>
            </div>
            <br>
            <div class="s14 main-profile">
                <div class="row">
                    <div class="col medium-4 small-12 large-4">Tài khoản</div>
                    <div class="col medium-8 small-12 large-8">
                        <?php
                        echo $data[ 'user_login' ];

                        // chức năng riêng dành cho admin
                        if ( isset( $session_data[ 'userLevel' ] ) && $session_data[ 'userLevel' ] > 0 ) {
                            ?>
                        <a href="./<?php echo CUSTOM_ADMIN_URI; ?>">@</a>
                        <?php
                        }
                        ?>
                    </div>
                </div>
                <div id="data-user_email">
                    <div class="row change-user_email">
                        <div class="col medium-4 small-12 large-4">Email</div>
                        <div class="col medium-8 small-12 large-8"><?php echo $data['user_email']; ?> - <em class="cur bluecolor click-change-email">Thay đổi email <i class="fa fa-edit"></i></em></div>
                    </div>
                    <div class="row changed-user_email d-none">
                        <div class="col medium-4 small-12 large-4 l40">Email (bắt buộc)</div>
                        <div class="col medium-8 small-12 large-8">
                            <div class="form-control">
                                <input type="email" placeholder="Email" name="data[user_email]" id="data_user_email" value="<?php echo $data['user_email']; ?>" disabled readonly aria-required="true" required>
                            </div>
                            <div class="top-menu-space10">Nếu bạn thay đổi email, chúng tôi sẽ gửi một email xác nhận đến địa chỉ email cũ. <strong>Email mới sẽ không được kích hoạt cho đến khi bạn xác nhận thay đổi</strong> - <em class="cur bluecolor cancel-change-email">Hủy bỏ <i class="fa fa-remove"></i></em></div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col medium-4 small-12 large-4 l40">Ảnh đại diện</div>
                    <div class="col medium-8 small-12 large-8">
                        <label for="file-input-cd" id="click-chose-CD"> <img src="images/_blank.png" height="150"
                                                                             <?php
                                                                             if ($data[ 'avatar' ] != '') {
                                                                                 ?>style="background-image: url(<?php echo $data[ 'avatar' ]; ?>);"
                                                                             <?php
                                                                             }
                                                                             ?> />
                            <input id="file-input-cd" accept="image/*" type="file" name="avatar" />
                        </label>
                    </div>
                </div>
                <div class="row">
                    <div class="col medium-4 small-12 large-4 l40">Họ và tên đệm</div>
                    <div class="col medium-8 small-12 large-8">
                        <div class="form-control">
                            <input type="text" placeholder="Họ và tên đệm" name="data[display_name]" id="data_display_name" value="<?php echo $data['display_name']; ?>" aria-required="true" required>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col medium-4 small-12 large-4 l40">Tên gọi</div>
                    <div class="col medium-8 small-12 large-8">
                        <div class="form-control">
                            <input type="text" placeholder="Tên gọi" name="data[user_nicename]" value="<?php echo $data['user_nicename']; ?>" aria-required="true" required>
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
                <button type="submit" class="btn btn-success">Cập nhật thông tin cá nhân</button>
            </div>
        </form>
    </div>
    <hr />
    <div class="user-pasword_form">
        <form name="pasword_form" class="form-vertical" accept-charset="utf-8" action="" method="post" target="target_eb_iframe">
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