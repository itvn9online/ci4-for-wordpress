<?php

// Libraries
use App\Libraries\UsersType;

//
$base_model->adds_css([
    'admin/css/user_add.css',
    'admin/css/' . $member_type . '_add.css',
]);

?>
<ul class="admin-breadcrumb">
    <li><a href="admin/<?php echo $controller_slug; ?>">Danh sách
            <?php echo $member_name; ?>
        </a></li>
    <li>
        <?php
        if ($data['ID'] > 0) {
        ?>
            Chỉnh sửa
        <?php
        } else {
        ?>
            Thêm mới
        <?php
        }

        //
        echo $member_name;
        ?>
    </li>
</ul>
<div id="app" class="widget-box">
    <div class="widget-content nopadding">
        <form action="" method="post" name="admin_global_form" id="admin_global_form" accept-charset="utf-8" onSubmit="return before_submit_user_add();" class="form-horizontal" target="target_eb_iframe">
            <div class="row left-menu-space main-user-add">
                <div class="col col-8 left-user-add">
                    <div class="begin-user-add"></div>
                    <?php
                    if ($data['ID'] > 0) {
                    ?>
                        <div class="control-group">
                            <label class="control-label">ID</label>
                            <div class="controls bold redcolor">
                                <?php echo $data['ID']; ?>
                            </div>
                        </div>
                        <?php

                        // hiển thị nút login as
                        //print_r( $session_data );
                        if (
                            isset($session_data['ID']) &&
                            // ID đang đăng nhập và ID đang xem không được giống nhau
                            $session_data['ID'] != $data['ID'] &&
                            // tài khoản phải là admin
                            $session_data['member_type'] == UsersType::ADMIN
                        ) {
                        ?>
                            <div class="control-group">
                                <label class="control-label">&nbsp;</label>
                                <div class="controls bold">
                                    <a href="admin/<?php echo $controller_slug; ?>/login_as?id=<?php echo $data['ID']; ?>" class="btn btn-info admin-login-as" target="target_eb_iframe">Đăng nhập với tư cách
                                        <?php echo $data['user_email']; ?> <i class="fa fa-sign-in"></i>
                                    </a> &nbsp;
                                    <a href="admin/orders?user_id=<?php echo $data['ID']; ?>" class="btn btn-inverse">Danh sách đơn hàng <i class="fa fa-search"></i></a>
                                </div>
                            </div>
                    <?php
                        } // END login as

                    } // END ID > 0
                    ?>
                    <div class="control-group">
                        <label class="control-label">Email</label>
                        <div class="controls">
                            <input type="email" class="span6" placeholder="Email" name="data[user_email]" id="data_user_email" value="<?php echo $data['user_email']; ?>" aria-required="true" required />
                        </div>
                    </div>
                    <div class="control-group">
                        <label class="control-label">Tài khoản</label>
                        <div class="controls bold bluecolor">
                            <input type="text" class="span6" placeholder="Tài khoản" name="data[user_login]" id="data_user_login" value="<?php echo $data['user_login']; ?>" onDblClick="$('#data_user_login').removeAttr('readonly');" aria-required="true" required <?php echo ($data['user_login'] != '' ? ' readonly' : ''); ?> />
                        </div>
                    </div>
                    <div class="control-group">
                        <label class="control-label">Nhóm tài khoản</label>
                        <div class="controls">
                            <select data-select="<?php echo $data['member_type']; ?>" name="data[member_type]" class="span5">
                                <option value="">[ Chọn nhóm
                                    <?php echo $member_name; ?> ]
                                </option>
                                <?php

                                //
                                foreach ($arr_members_type as $type_k => $type_v) {
                                    echo '<option value="' . $type_k . '">' . $type_v . '</option>';
                                }

                                ?>
                            </select>
                        </div>
                    </div>
                    <div class="control-group">
                        <label class="control-label">Trạng thái đăng nhập</label>
                        <div class="controls">
                            <select data-select="<?php echo $data['user_status']; ?>" name="data[user_status]" id="data_user_status" aria-required="true" required class="span5">
                                <?php

                                //
                                foreach (UsersType::statusList() as $type_k => $type_v) {
                                    echo '<option value="' . $type_k . '">' . $type_v . '</option>';
                                }

                                ?>
                            </select>
                        </div>
                    </div>
                    <div class="control-group">
                        <label class="control-label">Họ và tên</label>
                        <div class="controls">
                            <input type="text" class="span6" placeholder="Họ và tên" name="data[display_name]" id="data_display_name" value="<?php $base_model->the_esc_html($data['display_name']); ?>" />
                        </div>
                    </div>
                    <div class="control-group">
                        <label class="control-label">Tên rút gọn</label>
                        <div class="controls">
                            <input type="text" class="span4" placeholder="Tên rút gọn" name="data[user_nicename]" value="<?php $base_model->the_esc_html($data['user_nicename']); ?>" />
                        </div>
                    </div>
                    <div class="control-group">
                        <label class="control-label">Ngày sinh</label>
                        <div class="controls">
                            <input type="date" class="span4" placeholder="Ngày sinh" name="data[user_birthday]" value="<?php echo $data['user_birthday']; ?>" />
                        </div>
                    </div>
                    <div class="control-group">
                        <label class="control-label">Website/ Facebook</label>
                        <div class="controls">
                            <input type="text" class="span6" placeholder="Website/ Trang cá nhân" name="data[user_url]" id="data_user_url" value="<?php echo $data['user_url']; ?>" />
                        </div>
                    </div>
                    <div class="control-group">
                        <label class="control-label">Điện thoại</label>
                        <div class="controls">
                            <input type="text" class="span4" placeholder="Điện thoại" name="data[user_phone]" id="data_user_phone" value="<?php echo $data['user_phone']; ?>" />
                        </div>
                    </div>
                    <div class="control-group">
                        <label class="control-label">Firebase uid</label>
                        <div class="controls">
                            <input type="text" class="span4 graycolor" placeholder="Firebase uid" name="data[firebase_uid]" id="data_firebase_uid" value="<?php echo $data['firebase_uid']; ?>" />
                            <p class="controls-text-note">Khi người dùng đăng nhập qua Firebase sẽ có thông số này (đã được mã hóa). Nếu nó không khớp với phiên đăng nhập của người dùng thì phiên sẽ bị từ chối. Để reset phiên cho người dùng thì cần xóa trắng dữ liệu ở đây đi và cập nhật lại.</p>
                        </div>
                    </div>
                    <div class="control-group">
                        <label class="control-label">Zalo OA uid</label>
                        <div class="controls">
                            <input type="text" class="span4 graycolor" placeholder="Zalo OA uid" name="data[zalo_oa_id]" id="data_zalo_oa_id" value="<?php echo $data['zalo_oa_id']; ?>" />
                            <p class="controls-text-note">Khi người dùng đăng nhập qua Zalo OA sẽ có thông số này. Thông số này có thể được thiết lập cho nhiều tài khoản khác nhau vì mục đích chính của nó là để gửi tin nhắn thông báo nên không cần thiết lập kiểu dữ liệu duy nhất.</p>
                        </div>
                    </div>
                    <div class="end-user-add"></div>
                    <?php
                    if ($data['ci_pass'] != '') {
                    ?>
                        <div class="control-group">
                            <label class="control-label">Mật khẩu đăng nhập</label>
                            <div class="controls">
                                <input type="text" class="span4" placeholder="Mật khẩu" name="data[ci_pass]" id="data_ci_pass" value="<?php echo $data['ci_pass']; ?>" />
                            </div>
                        </div>
                    <?php
                    } else {
                    ?>
                        <br>
                        <br>
                        <div class="control-group">
                            <label class="control-label"><span class="hide-if-change-password">&nbsp;</span> <span class="show-if-change-password d-none">Thay đổi mật khẩu</span></label>
                            <div class="controls">
                                <div class="hide-if-change-password">
                                    <div onClick="return open_input_change_user_password();" class="cur s14 bluecolor">Thay
                                        đổi mật khẩu đăng nhập cho <strong>
                                            <?php echo $member_name; ?>
                                        </strong> này <i class="fa fa-edit"></i></div>
                                </div>
                                <div class="show-if-change-password d-none">
                                    <input type="text" class="span4" placeholder="Mật khẩu" name="data[ci_pass]" id="data_ci_pass" value="<?php echo $data['ci_pass']; ?>" />
                                    <button type="button" onClick="return random_input_change_user_password();" class="btn btn-info"><i class="fa fa-refresh"></i> Tạo mật khẩu ngẫu nhiên</button>
                                    <button type="button" onClick="return submit_input_change_user_password();" class="btn btn-primary"><i class="fa fa-save"></i> Cập nhật</button>
                                    <button type="button" onClick="return close_input_change_user_password();" class="btn btn-danger"><i class="fa fa-backward"></i> Hủy bỏ</button>
                                    <p class="redcolor-if-pass-focus">* Chỉ nhập mật khẩu khi bạn cần đổi mật khẩu cho
                                        <?php echo $member_name; ?>.
                                    </p>
                                </div>
                            </div>
                        </div>
                    <?php
                    }
                    ?>
                    <br>
                </div>
                <div class="col col-4 right-user-add">
                    <div class="control-group">
                        <label class="control-label">Ảnh đại diện</label>
                        <div class="controls">
                            <div class="user-bg-avatar each-to-bg-src" :data-src="data.avatar">&nbsp;</div>
                        </div>
                    </div>
                    <div class="control-group">
                        <label class="control-label">Ngày đăng ký</label>
                        <div class="controls">
                            <?php echo $data['user_registered']; ?>
                        </div>
                    </div>
                    <div class="control-group">
                        <label class="control-label">Đăng nhập cuối</label>
                        <div class="controls">
                            <?php echo $data['last_login']; ?> (<?php echo $data['login_type']; ?>)
                        </div>
                    </div>
                    <div class="control-group">
                        <label class="control-label">Cập nhật cuối</label>
                        <div class="controls">
                            <?php echo $data['last_updated']; ?>
                        </div>
                    </div>
                    <br>
                    <div class="form-actions frm-fixed-btn cf">
                        <?php
                        if ($data['ID'] > 0) {
                        ?>
                            <a href="admin/<?php echo $controller_slug; ?>/delete?id=<?php echo $data['ID']; ?>" onClick="return click_a_delete_record();" class="btn btn-danger btn-small" target="target_eb_iframe"><i class="fa fa-trash"></i> XÓA {{member_name}}</a>
                            <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> Lưu
                                lại</button>
                        <?php
                        } else {
                        ?>
                            <button type="submit" onClick="return check_user_email_before_add();" class="btn btn-primary"><i class="fa fa-plus"></i> Thêm mới {{member_name}}</button>
                        <?php
                        }
                        ?>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
<?php

//
$base_model->JSON_parse([
    'user_data' => $data,
]);

//
$base_model->JSON_echo([
    // mảng này sẽ in ra dưới dạng JSON hoặc number
], [
    // mảng này sẽ in ra dưới dạng string
    'member_name' => $member_name,
]);

?>
<script>
    WGR_vuejs('#app', {
        data: user_data,
        member_name: member_name,
    });
</script>
<?php

//
$base_model->adds_js([
    'admin/js/user_add.js',
    'admin/js/' . $member_type . '_add.js',
]);


/*
 * nạp thêm custom view nếu có
 */
$theme_private_view = str_replace(VIEWS_PATH, VIEWS_CUSTOM_PATH, __FILE__);
include VIEWS_PATH . 'private_require_view.php';
