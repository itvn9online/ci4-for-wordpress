<?php

// Libraries
use App\ Libraries\ UsersType;

?>
<ul class="admin-breadcrumb">
    <li><a href="admin/<?php echo $controller_slug; ?>">Danh sách <?php echo $member_name; ?></a></li>
    <li>
        <?php
        if ( $data[ 'ID' ] > 0 ) {
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
<div class="widget-box">
    <div class="widget-content nopadding">
        <form action="" method="post" name="admin_global_form" id="contact-form" accept-charset="utf-8" class="form-horizontal" target="target_eb_iframe">
            <?php
            if ( $data[ 'ID' ] > 0 ) {
                ?>
            <div class="control-group">
                <label class="control-label">ID</label>
                <div class="controls bold redcolor"><?php echo $data['ID']; ?></div>
            </div>
            <div class="control-group">
                <label class="control-label">Ngày đăng ký</label>
                <div class="controls"><?php echo $data['user_registered']; ?></div>
            </div>
            <?php

            // hiển thị nút login as
            //print_r( $session_data );
            if ( isset( $session_data[ 'ID' ] ) &&
                // ID đang đăng nhập và ID đang xem không được giống nhau
                $session_data[ 'ID' ] != $data[ 'ID' ] &&
                // tài khoản phải là admin
                $session_data[ 'member_type' ] == UsersType::ADMIN ) {
                ?>
            <div class="control-group">
                <label class="control-label">&nbsp;</label>
                <div class="controls bold"><a href="admin/<?php echo $controller_slug; ?>/login_as?id=<?php echo $data['ID']; ?>" class="btn btn-info" target="target_eb_iframe">Đăng nhập với tư cách <?php echo $data['user_email']; ?> <i class="fa fa-sign-in"></i></a></div>
            </div>
            <?php
            } // END login as

            } // END ID > 0
            ?>
            <div class="control-group">
                <label class="control-label">Tài khoản</label>
                <div class="controls bold bluecolor">
                    <input type="text" class="span3" placeholder="Tài khoản" name="data[user_login]" id="data_user_login" value="<?php echo $data[ 'user_login' ]; ?>" onDblClick="$('#data_user_login').removeAttr('readonly');" aria-required="true" required <?php
                    if ( $data[ 'user_login' ] != '' ) {
                        echo ' readonly';
                    }
                           ?> />
                </div>
            </div>
            <div class="control-group">
                <label class="control-label">Email</label>
                <div class="controls">
                    <input type="email" class="span6 required" placeholder="Email" name="data[user_email]" value="<?php echo $data['user_email']; ?>" aria-required="true" required />
                </div>
            </div>
            <div class="control-group">
                <label class="control-label">Họ và tên</label>
                <div class="controls">
                    <input type="text" class="span6 required" placeholder="Họ và tên" name="data[display_name]" value="<?php echo $data['display_name']; ?>" />
                </div>
            </div>
            <div class="control-group">
                <label class="control-label">Tên rút gọn</label>
                <div class="controls">
                    <input type="text" class="span6 required" placeholder="Tên rút gọn" name="data[user_nicename]" value="<?php echo $data['user_nicename']; ?>" />
                </div>
            </div>
            <div class="control-group">
                <label class="control-label">Nhóm <?php echo $member_name; ?></label>
                <div class="controls">
                    <select data-select="<?php echo $data['member_type']; ?>" name="data[member_type]" aria-required="true" required>
                        <?php

                        foreach ( $arr_members_type as $type_k => $type_v ) {
                            echo '<option value="' . $type_k . '">' . $type_v . '</option>';
                        }

                        ?>
                    </select>
                </div>
            </div>
            <div class="control-group">
                <label class="control-label">Trạng thái đăng nhập</label>
                <div class="controls">
                    <select data-select="<?php echo $data['user_status']; ?>" name="data[user_status]" aria-required="true" required>
                        <?php

                        foreach ( UsersType::listStatus() as $type_k => $type_v ) {
                            echo '<option value="' . $type_k . '">' . $type_v . '</option>';
                        }

                        ?>
                    </select>
                </div>
            </div>
            <div class="control-group">
                <label class="control-label">Website</label>
                <div class="controls">
                    <input type="text" class="span6 required" placeholder="Website/ Trang cá nhân" name="data[user_url]" value="<?php echo $data['user_url']; ?>" />
                </div>
            </div>
            <div class="control-group">
                <label class="control-label">Thay đổi mật khẩu</label>
                <div class="controls">
                    <input type="text" class="span6" placeholder="Mật khẩu" name="data[ci_pass]" value="<?php echo $data[ 'ci_pass' ]; ?>" onfocus="$('.redcolor-if-pass-focus').addClass('redcolor');" onblur="$('.redcolor-if-pass-focus').removeClass('redcolor');" />
                    <p class="redcolor-if-pass-focus">* Chỉ nhập mật khẩu khi bạn cần đổi mật khẩu cho <?php echo $member_name; ?>.</p>
                </div>
            </div>
            <div class="form-actions frm-fixed-btn">
                <?php
                if ( $data[ 'ID' ] > 0 ) {
                    ?>
                <button type="submit" class="btn btn-success rf"><i class="fa fa-save"></i> Lưu lại</button>
                <a href="admin/<?php echo $controller_slug; ?>/delete?id=<?php echo $data[ 'ID' ]; ?>" onClick="return click_a_delete_record();" class="btn btn-danger" target="target_eb_iframe"><i class="fa fa-trash"></i> XÓA</a>
                <?php
                } else {
                    ?>
                <button type="submit" class="btn btn-success rf"><i class="fa fa-plus"></i> Thêm mới</button>
                <?php
                }
                ?>
            </div>
        </form>
    </div>
</div>
<?php

//
$base_model->add_js( 'admin/js/' . $member_type . '_add.js' );
