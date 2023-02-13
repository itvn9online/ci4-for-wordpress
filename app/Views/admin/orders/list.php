<?php

// Libraries
use App\Libraries\OrderType;

// css riêng cho từng post type (nếu có)
$base_model->add_css('admin/css/posts_list.css');
$base_model->add_css('admin/css/' . $post_type . '.css');

?>
<ul class="admin-breadcrumb">
    <li>Danh sách
        <?php echo $name_type; ?> (
        <?php echo $totalThread; ?>)
    </li>
</ul>
<div id="app" class="ng-main-content">
    <div class="cf admin-search-form">
        <div class="lf f62">
            <form name="frm_admin_search_controller" action="./admin/<?php echo $controller_slug; ?>" method="get">
                <div class="cf">
                    <div class="lf f25">
                        <input name="s" value="<?php echo $by_keyword; ?>"
                            placeholder="Tìm kiếm <?php echo $name_type; ?>" autofocus aria-required="true" required>
                    </div>
                    <div class="lf f25">
                        <select name="post_status" :data-select="post_status"
                            onChange="document.frm_admin_search_controller.submit();">
                            <option value="">- Trạng thái
                                <?php echo $name_type; ?> -
                            </option>
                            <option :value="k" v-for="(v, k) in PostType_arrStatus">{{v}}</option>
                        </select>
                    </div>
                    <div class="lf f25">
                        <button type="submit" class="btn-success"><i class="fa fa-search"></i> Tìm kiếm</button>
                    </div>
                </div>
            </form>
        </div>
        <div class="lf f38 text-right">
            <?php

            //
            include ADMIN_ROOT_VIEWS . 'posts/list_right_button.php';

            ?>
        </div>
    </div>
    <br>
    <?php

    //
    include ADMIN_ROOT_VIEWS . 'posts/list_select_all.php';

    ?>
    <table class="table table-bordered table-striped with-check table-list eb-table admin-order-table">
        <thead>
            <tr>
                <th><input type="checkbox" class="input-checkbox-all" /></th>
                <th>ID/ Mã hóa đơn/ Ngày cập nhật</th>
                <th>Trạng thái</th>
                <th>Tiêu đề
                    <?php echo $name_type; ?>
                </th>
                <th>Giá trị</th>
                <th>Giảm giá</th>
                <th>Tặng thêm</th>
                <th>Thành viên/ Điện thoại/ Địa chỉ</th>
                <th>&nbsp;</th>
            </tr>
        </thead>
        <tbody id="admin_main_list">
            <tr :data-id="v.ID" v-for="v in data" :class="v.post_status">
                <td width="50" class="text-center"><input type="checkbox" :value="v.ID"
                        class="input-checkbox-control" /></td>
                <td class="text-center">
                    <div>#{{v.ID}}</div>
                    <div><a :href="v.admin_permalink" class="upper">{{v.post_name}} <i class="fa fa-edit"></i></a></div>
                    <div>({{v.post_modified.substr(0, 16)}})</div>
                </td>
                <td>{{PostType_arrStatus[v.post_status]}}</td>
                <td><a :href="v.admin_permalink">{{v.post_title}} <i class="fa fa-edit"></i></a></td>
                <td><span class="ebe-currency">{{ number_format(v.order_money) }}</span></td>
                <td><span class="ebe-currency">{{ number_format(v.order_discount) }}</span></td>
                <td><span class="ebe-currency">{{ number_format(v.order_bonus) }}</span></td>
                <td>
                    <div><i class="fa fa-envelope"></i> <a :href="'admin/users/add?id=' + v.post_author"
                            :data-id="v.post_author" class="each-to-email" target="_blank">{{v.post_author}}</a></div>
                    <div><i class="fa fa-phone"></i></div>
                    <div><i class="fa fa-home"></i></div>
                </td>
                <td width="90" class="text-center">
                    <?php
                    include ADMIN_ROOT_VIEWS . 'posts/list_action.php';
                    ?>
                </td>
            </tr>
        </tbody>
    </table>
</div>
<div class="public-part-page">
    <?php echo $pagination; ?> Trên tổng số
    <?php echo $totalThread; ?> bản ghi.
</div>
<?php

//
$base_model->JSON_parse(
    [
        'json_data' => $data,
        'PostType_arrStatus' => $post_arr_status,
    ]
);

?>
<script>
WGR_vuejs('#app', {
    controller_slug: '<?php echo $controller_slug; ?>',
    post_type: '<?php echo $post_type; ?>',
    post_status: '<?php echo $post_status; ?>',
    for_action: '<?php echo $for_action; ?>',
    PostType_DELETED: '<?php echo OrderType::DELETED; ?>',
    PostType_arrStatus: PostType_arrStatus,
    data: json_data,
});
</script>
<?php

//
include ADMIN_ROOT_VIEWS . 'posts/sync_modal.php';

// css riêng cho từng post type (nếu có)
$base_model->add_js('admin/js/post_list.js');
$base_model->add_js('admin/js/' . $post_type . '.js');