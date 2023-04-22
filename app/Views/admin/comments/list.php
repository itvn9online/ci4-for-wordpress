<?php

// css riêng cho từng post type (nếu có)
$base_model->add_css('admin/css/' . $comment_type . '.css');

?>
<ul class="admin-breadcrumb">
    <li>Danh sách {{vue_data.comment_name}} ({{vue_data.totalThread}})</li>
</ul>
<div class="cf admin-search-form">
    <div class="lf f50">
        <form name="frm_admin_search_controller" :action="'./admin/' + controller_slug" method="get">
            <div class="cf">
                <div class="lf f30">
                    <input v-if="vue_data.by_is_deleted > 0" type="hidden" name="is_deleted" :value="vue_data.by_is_deleted">
                    <input name="s" :value="vue_data.by_keyword" :placeholder="'Tìm kiếm ' + vue_data.comment_name" autofocus aria-required="true" required>
                </div>
                <div class="lf f20">
                    <button type="submit" class="btn-success"><i class="fa fa-search"></i> Tìm kiếm</button>
                </div>
            </div>
        </form>
    </div>
    <div class="lf f50 text-right">
        <div class="d-inline"><a :href="'admin/' + controller_slug + '?is_deleted=' + DeletedStatus_DELETED" class="btn btn-mini"> <i class="fa fa-trash"></i> Lưu trữ</a></div>
    </div>
</div>
<br>
<table class="table table-bordered table-striped with-check table-list eb-table">
    <thead>
        <tr>
            <th><input type="checkbox" id="selectall" name="selectall" /></th>
            <th>Tiêu đề</th>
            <th>Email</th>
            <th>Trạng thái</th>
            <th>IP</th>
            <th>Ngày tạo</th>
            <th>Lang</th>
            <th>&nbsp;</th>
        </tr>
    </thead>
    <tbody id="admin_main_list" class="ng-main-content">
        <tr v-for="v in data">
            <td>&nbsp;</td>
            <td><a :href="'admin/' + controller_slug + '?comment_id=' + v.comment_ID">{{v.comment_title}} <i class="fa fa-edit"></i></a> {{v.comment_slug}}</td>
            <td>{{v.comment_author_email}}</td>
            <td>{{v.comment_approved}}</td>
            <td>{{v.comment_author_IP}}</td>
            <td>{{v.comment_date.substr(0, 16)}}</td>
            <td>{{v.lang_key}}</td>
            <td class="text-center">
                <div>
                    <div v-if="v.is_deleted != DeletedStatus_DELETED">
                        <div><a :href="'admin/' + controller_slug + '/delete?id=' + v.comment_ID + for_action" onClick="return click_a_delete_record();" class="redcolor" target="target_eb_iframe"><i class="fa fa-trash"></i></a> </div>
                    </div>
                    <div v-if="v.is_deleted == DeletedStatus_DELETED">
                        <div><a :href="'admin/' + controller_slug + '/restore?id=' + v.comment_ID + for_action" onClick="return click_a_restore_record();" class="bluecolor" target="target_eb_iframe"><i class="fa fa-undo"></i></a></div>
                    </div>
                </div>
            </td>
        </tr>
    </tbody>
</table>
<div class="public-part-page">
    <?php echo $pagination; ?> Trên tổng số {{vue_data.totalThread}} bản ghi.
</div>
<?php

//
$base_model->JSON_parse(
    [
        'json_data' => $data,
        'vue_data' => $vue_data,
    ]
);

?>
<script>
    WGR_vuejs('#for_vue', {
        for_action: '<?php echo $for_action; ?>',
        controller_slug: '<?php echo $controller_slug; ?>',
        DeletedStatus_DELETED: '<?php echo $DeletedStatus_DELETED; ?>',
        data: json_data,
        vue_data: vue_data,
    });
</script>
<?php

// js riêng cho từng comments type (nếu có)
$base_model->add_js('admin/js/comments.js');
$base_model->add_js('admin/js/' . $comment_type . '.js');
