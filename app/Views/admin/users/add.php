<?php

// Libraries
use App\ Libraries\ UsersType;

?>
<ul class="admin-breadcrumb">
    <li><a href="admin/users">Danh sách thành viên</a></li>
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
        ?>
        thành viên </li>
</ul>
<div class="widget-box">
    <div class="widget-content nopadding">
        <form action="" method="post" name="admin_global_form" id="contact-form" accept-charset="utf-8" class="form-horizontal" target="target_eb_iframe">
            <div class="control-group">
                <label class="control-label">Tài khoản</label>
                <div class="controls">
                    <input type="text" class="span6" placeholder="Tài khoản" value="<?php echo $data['user_login']; ?>" disabled aria-required="true" required />
                </div>
            </div>
            <div class="control-group">
                <label class="control-label">Email</label>
                <div class="controls">
                    <input type="email" class="span6 required" placeholder="Email" name="data[user_email]" value="<?php echo $data['user_email']; ?>" aria-required="true" required />
                </div>
            </div>
            <div class="cf">
                <label class="control-label">Ngày đăng ký</label>
                <div class="controls" style="margin-top: 5px;"><?php echo $data['user_registered']; ?></div>
            </div>
            <div class="control-group">
                <label class="control-label">Họ và tên</label>
                <div class="controls">
                    <input type="text" class="span6 required" placeholder="Họ và tên" name="data[display_name]" value="<?php echo $data['display_name']; ?>" />
                </div>
            </div>
            <div class="control-group">
                <label class="control-label">Nhóm thành viên</label>
                <div class="controls">
                    <select data-select="<?php echo $data['member_type']; ?>" name="data[member_type]" aria-required="true" required>
                        <?php

                        foreach ( UsersType::list() as $type_k => $type_v ) {
                            echo '<option value="' . $type_k . '">' . $type_v . '</option>';
                        }

                        ?>
                    </select>
                </div>
            </div>
            <div class="control-group">
                <label class="control-label">Thay đổi mật khẩu</label>
                <div class="controls">
                    <input type="text" class="span6" placeholder="Mật khẩu" name="data[ci_pass]" value="" onfocus="$('.redcolor-if-pass-focus').addClass('redcolor');" onblur="$('.redcolor-if-pass-focus').removeClass('redcolor');" />
                    <p class="redcolor-if-pass-focus">* Chỉ nhập mật khẩu khi bạn cần đổi mật khẩu cho thành viên.</p>
                </div>
            </div>
            <div class="form-actions frm-fixed-btn">
                <?php
                if ( $data[ 'ID' ] > 0 ) {
                    ?>
                <button type="submit" class="btn btn-success rf"><i class="fa fa-save"></i> Lưu lại</button>
                <a href="admin/users/delete?id=<?php echo $data[ 'ID' ]; ?>" onClick="click_a_delete_record();" class="btn btn-danger" target="target_eb_iframe"><i class="fa fa-trash"></i> XÓA</a>
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
