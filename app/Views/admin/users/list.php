<?php

// Libraries
use App\ Libraries\ UsersType;
use App\ Libraries\ DeletedStatus;

?>
<ul class="admin-breadcrumb">
    <li><a href="admin/users">Danh sách thành viên</a> (<?php echo $totalThread; ?>)</li>
    <?php
    if ( $member_type != '' ) {
        ?>
    <li><?php echo UsersType::list($member_type); ?></li>
    <?php
    }
    ?>
</ul>
<div class="cf admin-search-form">
    <div class="lf f50">
        <form name="frm_admin_search_controller" action="./admin/users" method="get">
            <input type="hidden" name="member_type" value="<?php echo $member_type; ?>">
            <div class="cf">
                <div class="lf f30">
                    <input name="s" value="<?php echo $by_keyword; ?>" placeholder="Tìm kiếm <?php echo $member_type != '' ? UsersType::list($member_type) : ''; ?>" autofocus>
                </div>
                <div class="lf f20">
                    <button type="submit" class="btn-success"><i class="fa fa-search"></i> Tìm kiếm</button>
                </div>
            </div>
        </form>
    </div>
    <div class="lf f50 text-right">
        <div class="d-inline"> <a href="admin/users/add" class="btn btn-success btn-mini"> <i class="fa fa-plus"></i> Thêm mới thành viên</a> </div>
        <div class="d-inline"><a href="admin/users?member_type=<?php echo $member_type; ?>&is_deleted=<?php echo DeletedStatus::DELETED; ?>" class="btn btn-mini"> <i class="fa fa-trash"></i> Lưu trữ</a></div>
    </div>
</div>
<br>
<table class="table table-bordered table-striped with-check table-list eb-table">
    <thead>
        <tr>
            <th><input type="checkbox" id="selectall" name="selectall"/></th>
            <th>ID</th>
            <th>Tài khoản</th>
            <th>Email</th>
            <th>Tên hiển thị</th>
            <th>Nhóm</th>
            <th>Trạng thái đăng nhập</th>
            <th><a href="admin/users?member_type=<?php echo $member_type; ?>&order_by=last_login">Đăng nhập cuối <i class="fa fa-sort"></i></a></th>
            <th>Ngày đăng ký</th>
            <th>&nbsp;</th>
        </tr>
    </thead>
    <tbody>
        <?php

        foreach ( $data as $v ) {
            ?>
        <tr>
            <td>&nbsp;</td>
            <td><?php echo $v['ID']; ?></td>
            <td><a href="admin/users/add?id=<?php echo $v['ID']; ?>"><?php echo $v['user_login']; ?></a></td>
            <td><?php echo $v['user_email']; ?></td>
            <td><?php echo $v['display_name']; ?></td>
            <td><a href="admin/users?member_type=<?php echo $v['member_type']; ?>"><?php echo $v['member_type'] != '' ? UsersType::list($v['member_type']) : ''; ?></a></td>
            <td><?php echo UsersType::listStatus( $v['user_status'] ); ?></td>
            <td><?php echo $v['last_login']; ?></td>
            <td><?php echo $v['user_registered']; ?></td>
            <td class="text-center"><?php
            if ( $v[ 'is_deleted' ] != DeletedStatus::DELETED ) {
                ?>
                <a href="admin/users/delete?id=<?php echo $v[ 'ID' ]; ?>&page_num=<?php echo $page_num; ?>&is_deleted=<?php echo $by_is_deleted; ?>" onClick="return click_a_delete_record();" class="redcolor" target="target_eb_iframe"><i class="fa fa-trash"></i></a>
                <?php
                } else {
                    ?>
                <a href="admin/users/restore?id=<?php echo $v[ 'ID' ]; ?>&page_num=<?php echo $page_num; ?>&is_deleted=<?php echo $by_is_deleted; ?>" onClick="return click_a_restore_record();" class="bluecolor" target="target_eb_iframe"><i class="fa fa-undo"></i></a>
                <?php
                }
                ?></td>
        </tr>
        <?php
        }

        ?>
    </tbody>
</table>
<div class="public-part-page"> <?php echo $pagination; ?> Trên tổng số <?php echo $totalThread; ?> bản ghi.</div>
<p class="d-none">* Copy đoạn code bên dưới rồi cho vào nơi cần hiển thị block này ở trong view. Nhớ thay %slug% thành slug thật trong danh sách ở trên.</p>
