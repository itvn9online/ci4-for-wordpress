<?php

// Libraries
use App\ Libraries\ PostType;

//
//$base_model = new\ App\ Models\ Base();
//$post_model = new\ App\ Models\ PostAdmin();

// css riêng cho từng post type (nếu có)
$base_model->add_css( 'admin/css/' . $post_type . '.css' );

?>
<ul class="admin-breadcrumb">
    <li>Danh sách <?php echo $name_type; ?> (<?php echo $totalThread; ?>)</li>
</ul>
<div id="app" class="ng-main-content">
    <div class="cf admin-search-form">
        <div class="lf f62">
            <form name="frm_admin_search_controller" action="./admin/<?php echo $controller_slug; ?>" method="get">
                <div class="cf">
                    <div class="lf f25">
                        <input name="s" value="<?php echo $by_keyword; ?>" placeholder="Tìm kiếm <?php echo $name_type; ?>" autofocus aria-required="true" required>
                    </div>
                    <div class="lf f25">
                        <select name="post_status" :data-select="post_status" onChange="document.frm_admin_search_controller.submit();">
                            <option value="">- Trạng thái <?php echo $name_type; ?> -</option>
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
            include VIEWS_PATH . 'admin/posts/list_right_button.php';

            ?>
        </div>
    </div>
    <br>
    <?php

    //
    include VIEWS_PATH . 'admin/posts/list_select_all.php';

    ?>
    <table class="table table-bordered table-striped with-check table-list eb-table">
        <thead>
            <tr>
                <th><input type="checkbox" class="input-checkbox-all" /></th>
                <th>Mã HĐ/ Ngày gửi</th>
                <th>Trạng thái</th>
                <th>Tiêu đề <?php echo $name_type; ?></th>
                <th>Thành viên/ Điện thoại/ Địa chỉ</th>
                <th>&nbsp;</th>
            </tr>
        </thead>
        <tbody id="admin_main_list">
            <tr :data-id="v.ID" v-for="v in data">
                <td width="50" class="text-center"><input type="checkbox" :value="v.ID" class="input-checkbox-control" /></td>
                <td><div>{{v.post_name}}</div>
                    <div>({{v.post_modified.substr(0, 16)}})</div></td>
                <td :class="'post_status post_status-' + v.post_status">{{PostType_arrStatus[v.post_status]}}</td>
                <td><a :href="v.admin_permalink" class="bold">{{v.post_title}} <i class="fa fa-edit"></i></a></td>
                <td><div>Thành viên</div>
                    <div>Điện thoại</div>
                    <div>Địa chỉ</div></td>
                <td width="90" class="text-center"><?php
                include VIEWS_PATH . 'admin/posts/list_action.php';
                ?></td>
            </tr>
        </tbody>
    </table>
</div>
<div class="public-part-page"> <?php echo $pagination; ?> Trên tổng số <?php echo $totalThread; ?> bản ghi.</div>
<script>
WGR_vuejs('#app', {
    controller_slug: '<?php echo $controller_slug; ?>',
    PostType_MENU: '<?php echo PostType::MENU; ?>',
    post_type: '<?php echo $post_type; ?>',
    post_status: '<?php echo $post_status; ?>',
    for_action: '<?php echo $for_action; ?>',
    PostType_DELETED: '<?php echo PostType::DELETED; ?>',
    PostType_arrStatus: <?php echo json_encode(PostType::arrStatus()); ?>,
    data: <?php echo json_encode($data); ?>,
});
</script>
<?php

//
include VIEWS_PATH . 'admin/posts/sync_modal.php';

// css riêng cho từng post type (nếu có)
$base_model->add_js( 'admin/js/post_list.js' );
$base_model->add_js( 'admin/js/' . $post_type . '.js' );
