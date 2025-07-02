<?php

// css riêng cho từng post type (nếu có)
$base_model->adds_css([
    'wp-admin/css/' . $comment_type . '.css',
    'wp-admin/css/order_list.css',
]);

?>
<ul class="admin-breadcrumb">
    <li>Danh sách {{vue_data.comment_name}} ({{vue_data.totalThread}})</li>
</ul>
<div class="cf admin-search-form">
    <div class="lf f50">
        <form name="frm_admin_search_controller" :action="'./sadmin/' + controller_slug" method="get">
            <div class="cf">
                <div class="lf f30">
                    <input v-if="vue_data.by_is_deleted > 0" type="hidden" name="is_deleted" :value="vue_data.by_is_deleted">
                    <input name="s" :value="vue_data.by_keyword" :placeholder="'Tìm kiếm ' + vue_data.comment_name" autofocus aria-required="true" required>
                </div>
                <div class="lf f20">
                    <button type="submit" class="btn-success"><i class="fa fa-search"></i> Tìm</button>
                </div>
            </div>
        </form>
    </div>
    <div class="lf f50 text-right">
        <div class="d-inline"><a :href="'sadmin/' + controller_slug + '?is_deleted=' + DeletedStatus_DELETED" class="btn btn-mini"> <i class="fa fa-trash"></i> Lưu trữ</a></div>
    </div>
</div>
<br>
<?php

//
$has_private_view = false;

// list table của từng comment type nếu tìm thấy file
if ($comment_type != '') {
    $theme_default_view = ADMIN_ROOT_VIEWS . $comment_type . '/list_table.php';
    // nạp file kiểm tra private view
    include VIEWS_PATH . 'private_view.php';
} else {
    $theme_default_view = '';
}

// list table mặc định
if ($has_private_view === false) {
    if ($theme_default_view == '' || !is_file($theme_default_view)) {
        // nạp view riêng của từng theme nếu có
        $theme_default_view = __DIR__ . '/list_table.php';
        // nạp file kiểm tra private view
        include VIEWS_PATH . 'private_view.php';
    }
}

?>
<div class="public-part-page">
    <?php echo $pagination; ?> Trên tổng số {{vue_data.totalThread}} bản ghi.
</div>
<iframe id="order_details_iframe" name="order-details-iframe" title="Orderdetails iframe" src="about:blank" width="333" class="hide-if-esc"></iframe>
<?php

//
$base_model->JSON_parse(
    [
        'json_data' => $data,
        'vue_data' => $vue_data,
        'json_params' => [
            'for_action' => $for_action,
            'controller_slug' => $controller_slug,
            'DeletedStatus_DELETED' => $DeletedStatus_DELETED,
        ],
    ]
);

// js riêng cho từng comments type (nếu có)
$base_model->adds_js([
    'wp-admin/js/popup_functions.js',
    'wp-admin/js/comments.js',
    'wp-admin/js/comment_list.js',
    'wp-admin/js/' . $comment_type . '.js',
]);
