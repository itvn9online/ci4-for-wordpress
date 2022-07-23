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
            include __DIR__ . '/list_right_button.php';

            ?>
        </div>
    </div>
    <br>
    <?php

    //
    include __DIR__ . '/list_select_all.php';

    ?>
    <table class="table table-bordered table-striped with-check table-list eb-table">
        <thead>
            <tr>
                <th><input type="checkbox" class="input-checkbox-all" /></th>
                <th>Tiêu đề <?php echo $name_type; ?></th>
                <th>Ảnh đại diện</th>
                <th>Danh mục</th>
                <th>Trạng thái</th>
                <th colspan="2">Ngày tạo/ Last Update</th>
                <th>Lang</th>
                <th>STT</th>
                <th>&nbsp;</th>
            </tr>
        </thead>
        <tbody id="admin_main_list">
            <tr :data-id="v.ID" v-for="v in data">
                <td width="50" class="text-center"><input type="checkbox" :value="v.ID" class="input-checkbox-control" /></td>
                <td><div><a :href="v.admin_permalink" class="bold">{{v.post_title}} <i class="fa fa-edit"></i></a></div>
                    <div :class="post_type == PostType_MENU ? 'd-none' : ''"><a :href="v.the_permalink" target="_blank" class="small blackcolor">{{v.post_name}} <i class="fa fa-external-link"></i></a></div></td>
                <td><div :class="post_type == PostType_MENU ? 'd-none' : ''" class="img-max-width"> <a :href="v.admin_permalink"><img
                          :src="v.thumbnail"
                          height="90"
                          data-class="each-to-img-src"
                          style="height: 90px; width: auto;" /></a> </div></td>
                <td :data-id="v.main_category_key"
                :data-taxonomy="taxonomy"
                :data-uri="'admin/' + controller_slug"
                class="each-to-taxonomy">&nbsp;</td>
                <td :class="'post_status post_status-' + v.post_status">{{PostType_arrStatus[v.post_status]}}</td>
                <td>{{v.post_date.substr(0, 16)}}</td>
                <td>{{v.post_modified.substr(0, 16)}}</td>
                <td width="90">{{v.lang_key}}</td>
                <td width="60"><input type="text" :data-id="v.ID" :value="v.menu_order" size="5" class="form-control s change-update-menu_order" /></td>
                <td width="90" class="text-center"><?php
                require __DIR__ . '/list_action.php';
                ?></td>
            </tr>
        </tbody>
    </table>
</div>
<div class="public-part-page"> <?php echo $pagination; ?> Trên tổng số <?php echo $totalThread; ?> bản ghi.</div>
<script>
WGR_vuejs('#app', {
    ALLOW_USING_MYSQL_DELETE: ALLOW_USING_MYSQL_DELETE,
    data: <?php echo json_encode($data); ?>,
    post_type: '<?php echo $post_type; ?>',
    post_status: '<?php echo $post_status; ?>',
    PostType_MENU: '<?php echo PostType::MENU; ?>',
    taxonomy: '<?php echo $taxonomy; ?>',
    controller_slug: '<?php echo $controller_slug; ?>',
    for_action: '<?php echo $for_action; ?>',
    PostType_DELETED: '<?php echo PostType::DELETED; ?>',
    PostType_arrStatus: <?php echo json_encode(PostType::arrStatus()); ?>,
});
</script>
<?php

//
include VIEWS_PATH . 'admin/posts/sync_modal.php';

//
if ( $post_type == PostType::MENU ) {
    ?>
<pre><code>&lt;?php $menu_model->the_menu( '%slug%' ); ?&gt;</code></pre>
<?php
}

// css riêng cho từng post type (nếu có)
$base_model->add_js( 'admin/js/post_list.js' );
$base_model->add_js( 'admin/js/' . $post_type . '.js' );
