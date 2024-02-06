<table class="table table-bordered table-striped with-check table-list eb-table admin-order-table">
    <thead>
        <tr>
            <th><input type="checkbox" class="input-checkbox-all" /></th>
            <th>ID/ Mã hóa đơn/ Ngày cập nhật</th>
            <th>Trạng thái</th>
            <th>Tiêu đề <?php echo $name_type; ?></th>
            <th>Giá trị</th>
            <th>Giảm giá</th>
            <th>Tặng thêm</th>
            <th>Thành viên/ Điện thoại/ Địa chỉ</th>
            <th>&nbsp;</th>
        </tr>
    </thead>
    <tbody id="admin_main_list">
        <tr :data-id="v.ID" v-for="v in data" :class="v.post_status">
            <td width="50" class="text-center"><input type="checkbox" :value="v.ID" class="input-checkbox-control" /></td>
            <td class="text-center">
                <div>#{{v.ID}}</div>
                <div><a :href="v.admin_permalink" class="upper">{{v.post_name}} <i class="fa fa-edit"></i></a></div>
                <div>({{v.post_modified.substr(0, 16)}})</div>
            </td>
            <td>
                <button type="button" class="btn orders-post_status">{{PostType_arrStatus[v.post_status]}}</button>
            </td>
            <td><a :href="v.admin_permalink">{{v.post_title}} <i class="fa fa-edit"></i></a></td>
            <td><span class="ebe-currency-format">{{v.order_money}}</span></td>
            <td><span class="ebe-currency-format">{{v.order_discount}}</span></td>
            <td><span class="ebe-currency-format">{{v.order_bonus}}</span></td>
            <td>
                <div><i class="fa fa-envelope"></i> <a :href="'sadmin/users/add?id=' + v.post_author" :data-id="v.post_author" class="each-to-email" target="_blank">{{v.post_author}}</a></div>
                <div><i class="fa fa-phone"></i> {{v.phone}}</div>
                <div><i class="fa fa-home"></i> {{v.address}}</div>
            </td>
            <td class="text-center big">
                <?php
                include ADMIN_ROOT_VIEWS . 'posts/list_action.php';
                ?>
            </td>
        </tr>
    </tbody>
</table>