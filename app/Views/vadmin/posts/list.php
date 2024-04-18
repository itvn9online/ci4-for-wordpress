<?php

// Libraries
use App\Libraries\PostType;

// css riêng cho từng post type (nếu có)
$base_model->add_css('wp-admin/css/posts_list.css');
$base_model->add_css('wp-admin/css/' . $post_type . '.css');

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
            <form name="frm_admin_search_controller" action="./sadmin/<?php echo $controller_slug; ?>" method="get">
                <div class="cf">
                    <div class="lf f20">
                        <input name="s" value="<?php echo $by_keyword; ?>" placeholder="Tìm kiếm <?php echo $name_type; ?>" autofocus>
                    </div>
                    <div class="lf f20">
                        <input name="user_id" value="<?php echo $by_user_id; ?>" placeholder="ID người đăng">
                    </div>
                    <div class="lf f20 hide-if-no-taxonomy">
                        <select name="term_id" data-select="<?php echo $by_term_id; ?>" :data-taxonomy="taxonomy" onChange="document.frm_admin_search_controller.submit();" class="each-to-group-taxonomy">
                            <option value="0">- Danh mục
                                <?php echo $name_type; ?> -
                            </option>
                        </select>
                    </div>
                    <div class="lf f20">
                        <select name="post_status" :data-select="post_status" onChange="document.frm_admin_search_controller.submit();">
                            <option value="">- Trạng thái
                                <?php echo $name_type; ?> -
                            </option>
                            <option :value="k" v-for="(v, k) in PostType_arrStatus">{{v}}</option>
                        </select>
                    </div>
                    <div class="lf f20">
                        <button type="submit" class="btn-success"><i class="fa fa-search"></i> Tìm</button>
                    </div>
                </div>
            </form>
        </div>
        <div class="lf f38 text-right">
            <?php

            //
            include __DIR__ . '/list_right_button.php';

            ?>
        </div>
    </div>
    <br>
    <?php

    //
    include __DIR__ . '/list_select_all.php';

    // list table của từng post type nếu được thiết lập trong controller
    if ($list_table_path != '') {
        echo '<div class="wgr-view-path">' . ADMIN_ROOT_VIEWS . $list_table_path . '/list_table.php</div>';

        // sử dụng list table riêng của post type nếu có khai báo
        include ADMIN_ROOT_VIEWS . $list_table_path . '/list_table.php';
    } else {
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
    }

    ?>
</div>
<div class="public-part-page"><?php echo $pagination; ?> Trên tổng số <?php echo number_format($totalThread); ?> bản ghi.</div>
<?php

//
$base_model->JSON_parse(
    [
        'json_data' => $data,
        'PostType_arrStatus' => $post_arr_status,
    ]
);

?>
<script type="text/javascript">
    WGR_vuejs('#app', {
        allow_mysql_delete: allow_mysql_delete,
        post_type: '<?php echo $post_type; ?>',
        post_status: '<?php echo $post_status; ?>',
        taxonomy: '<?php echo $taxonomy; ?>',
        tags: '<?php echo $tags; ?>',
        controller_slug: '<?php echo $controller_slug; ?>',
        for_action: '<?php echo $for_action; ?>',
        PostType_DELETED: '<?php echo PostType::DELETED; ?>',
        PostType_arrStatus: PostType_arrStatus,
        data: json_data,
    });
</script>
<?php

//
include __DIR__ . '/sync_modal.php';

// css riêng cho từng post type (nếu có)
$base_model->add_js('wp-admin/js/post_list.js');
$base_model->add_js('wp-admin/js/' . $post_type . '.js');
