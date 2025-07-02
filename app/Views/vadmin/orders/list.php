<?php

// Libraries
use App\Libraries\OrderType;

//
if (isset($_GET['print_data'])) {
    echo '<!-- ';
    print_r($data);
    echo ' -->';
}

// css riêng cho từng post type (nếu có)
$base_model->adds_css([
    'wp-admin/css/posts_list.css',
    'wp-admin/css/order_list.css',
    'wp-admin/css/' . $post_type . '.css',
    THEMEPATH . 'css/order_list.css',
    THEMEPATH . 'css/' . $post_type . '.css',
]);

?>
<ul class="admin-breadcrumb">
    <li>Danh sách <?php echo $name_type; ?> (<?php echo $totalThread; ?>)</li>
</ul>
<div id="app" class="ng-main-content">
    <div class="cf admin-search-form">
        <div class="lf f62">
            <form name="frm_admin_search_controller" action="./sadmin/<?php echo $controller_slug; ?>" method="get">
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
                        <button type="submit" class="btn-success"><i class="fa fa-search"></i> Tìm</button>
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

    //
    $has_private_view = false;

    // list table của từng post type nếu tìm thấy file
    if ($post_type != '') {
        $theme_default_view = ADMIN_ROOT_VIEWS . $post_type . '/list_table.php';
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
</div>
<div class="public-part-page"><?php echo $pagination; ?> Trên tổng số <?php echo number_format($totalThread); ?> bản ghi.</div>
<iframe id="order_details_iframe" name="order-details-iframe" title="Orderdetails iframe" src="about:blank" width="333" class="hide-if-esc"></iframe>
<?php

//
include ADMIN_ROOT_VIEWS . 'posts/sync_modal.php';

//
$base_model->JSON_parse(
    [
        'json_data' => $data,
        'PostType_arrStatus' => $post_arr_status,
        'json_params' => [
            'for_action' => $for_action,
            'controller_slug' => $controller_slug,
            'post_type' => $post_type,
            'post_status' => $post_status,
            'PostType_DELETED' => OrderType::DELETED,
        ],
    ]
);

// css riêng cho từng post type (nếu có)
$base_model->adds_js([
    'wp-admin/js/popup_functions.js',
    'wp-admin/js/shop_order_functions.js',
    'wp-admin/js/post_list.js',
    'wp-admin/js/order_list.js',
    'wp-admin/js/' . $post_type . '.js',
    THEMEPATH . 'js/' . $post_type . '.js',
]);
