<?php

// Libraries
use App\Libraries\PostType;

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
                    <div class="lf f25 hide-if-no-taxonomy">
                        <select name="term_id" data-select="<?php echo $by_term_id; ?>" :data-taxonomy="taxonomy"
                            onChange="document.frm_admin_search_controller.submit();" class="each-to-group-taxonomy">
                            <option value="0">- Danh mục
                                <?php echo $name_type; ?> -
                            </option>
                        </select>
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

    // sử dụng list_table riêng của post type nếu có khai báo
    if ($list_table_path != '') {
        include ADMIN_ROOT_VIEWS . $list_table_path . '/list_table.php';
    }
    // list_table mặc định
    else {
        include __DIR__ . '/list_table.php';
    }

    ?>
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
        allow_mysql_delete: allow_mysql_delete,
        post_type: '<?php echo $post_type; ?>',
        post_status: '<?php echo $post_status; ?>',
        taxonomy: '<?php echo $taxonomy; ?>',
        controller_slug: '<?php echo $controller_slug; ?>',
        for_action: '<?php echo $for_action; ?>',
        PostType_DELETED: '<?php echo PostType::DELETED; ?>',
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