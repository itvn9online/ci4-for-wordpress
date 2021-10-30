<?php

// Libraries
use App\ Libraries\ UsersType;

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
    <div class="lf f80">
        <form name="frm_admin_search_controller" action="./admin/users" method="get">
            <input type="hidden" name="member_type" value="<?php echo $member_type; ?>">
            <div class="cf">
                <div class="lf f20">
                    <input name="s" value="<?php echo $by_keyword; ?>" placeholder="Tìm kiếm <?php echo $member_type != '' ? UsersType::list($member_type) : ''; ?>">
                </div>
                <div class="lf f10">
                    <button type="submit" class="btn-success"><i class="fa fa-search"></i> Tìm kiếm</button>
                </div>
            </div>
        </form>
    </div>
    <div class="lf f20">
        <div class="buttons text-right"> <a href="admin/users/add" class="btn btn-success btn-mini"> <i class="fa fa-plus"></i> Thêm mới thành viên</a> </div>
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
            <th>Đăng nhập cuối</th>
            <th>Ngày đăng ký</th>
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
            <td><?php echo $v['last_login']; ?></td>
            <td><?php echo $v['user_registered']; ?></td>
        </tr>
        <?php
        }

        ?>
    </tbody>
</table>
<div class="public-part-page"> <?php echo $pagination; ?> Trên tổng số <?php echo $totalThread; ?> bản ghi.</div>
<p class="d-none">* Copy đoạn code bên dưới rồi cho vào nơi cần hiển thị block này ở trong view. Nhớ thay %slug% thành slug thật trong danh sách ở trên.</p>
