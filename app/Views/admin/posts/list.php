<?php

// Libraries
use App\ Libraries\ PostType;

// css riêng cho từng post type (nếu có)
$base_model->add_css( 'admin/css/posts_list.css' );
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
                    <div class="lf f25 hide-if-no-taxonomy">
                        <select name="term_id" data-select="<?php echo $by_term_id; ?>" :data-taxonomy="taxonomy" onChange="document.frm_admin_search_controller.submit();" class="each-to-group-taxonomy">
                            <option value="0">- Danh mục <?php echo $name_type; ?> -</option>
                        </select>
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
            include $admin_root_views . 'posts/list_right_button.php';

            ?>
        </div>
    </div>
    <br>
    <?php

    //
    include $admin_root_views . 'posts/list_select_all.php';

    // sử dụng list_table riêng của post type nếu có khai báo
    if ( $list_table_path != '' ) {
        include $admin_root_views . $list_table_path . '/list_table.php';
    }
    // list_table mặc định
    else {
        include __DIR__ . '/list_table.php';
    }

    ?>
</div>
<div class="public-part-page"> <?php echo $pagination; ?> Trên tổng số <?php echo $totalThread; ?> bản ghi.</div>
<script>
WGR_vuejs('#app', {
    ALLOW_USING_MYSQL_DELETE: ALLOW_USING_MYSQL_DELETE,
    post_type: '<?php echo $post_type; ?>',
    post_status: '<?php echo $post_status; ?>',
    taxonomy: '<?php echo $taxonomy; ?>',
    controller_slug: '<?php echo $controller_slug; ?>',
    for_action: '<?php echo $for_action; ?>',
    PostType_DELETED: '<?php echo PostType::DELETED; ?>',
    PostType_arrStatus: <?php echo json_encode($post_arr_status); ?>,
    data: <?php echo json_encode($data); ?>,
});
</script>
<?php

//
include $admin_root_views . 'posts/sync_modal.php';

// css riêng cho từng post type (nếu có)
$base_model->add_js( 'admin/js/post_list.js' );
$base_model->add_js( 'admin/js/' . $post_type . '.js' );
