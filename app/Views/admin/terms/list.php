<?php

// Libraries
use App\ Libraries\ TaxonomyType;

// css riêng cho từng post type (nếu có)
$base_model->add_css( 'admin/css/terms.css' );
$base_model->add_css( 'admin/css/' . $taxonomy . '.css' );

?>
<ul class="admin-breadcrumb">
    <li><?php echo $name_type; ?> (<?php echo $totalThread; ?>)</li>
</ul>
<div id="app" class="ng-main-content2">
    <div class="cf admin-search-form">
        <div class="lf f50">
            <form name="frm_admin_search_controller" action="./admin/<?php echo $controller_slug; ?>" method="get">
                <div class="cf">
                    <div class="lf f30">
                        <?php
                        if ( $by_is_deleted > 0 ) {
                            ?>
                        <input type="hidden" name="is_deleted" value="<?php echo $by_is_deleted; ?>">
                        <?php
                        }
                        ?>
                        <input name="s" value="<?php echo $by_keyword; ?>" placeholder="Tìm kiếm <?php echo $name_type; ?>" autofocus aria-required="true" required>
                    </div>
                    <div class="lf f20">
                        <button type="submit" class="btn-success"><i class="fa fa-search"></i> Tìm kiếm</button>
                    </div>
                </div>
            </form>
        </div>
        <div class="lf f50 text-right">
            <div class="d-inline"> <a href="<?php $term_model->admin_permalink( $taxonomy, 0, $controller_slug ); ?>" class="btn btn-success btn-mini"> <i class="fa fa-plus"></i> Thêm mới <?php echo $name_type; ?></a> </div>
            <!-- -->
            <div v-if="by_is_deleted == DeletedStatus_DELETED" class="d-inline"><a :href="'admin/' + controller_slug" class="btn btn-primary btn-mini"> <i class="fa fa-list"></i> Quay lại</a></div>
            <div v-if="by_is_deleted != DeletedStatus_DELETED" class="d-inline"><a :href="'admin/' + controller_slug + '?is_deleted=' + DeletedStatus_DELETED" class="btn btn-mini"> <i class="fa fa-trash"></i> Lưu trữ</a></div>
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
                <th>ID</th>
                <th>Tiêu đề <?php echo $name_type; ?></th>
                <th>Slug</th>
                <th class="d-none show-if-ads-type">Size</th>
                <th>Nội dung</th>
                <th>Ngôn ngữ</th>
                <th>Tổng</th>
                <th>STT</th>
                <th>&nbsp;</th>
            </tr>
        </thead>
        <tbody id="admin_main_list">
            <tr data-id="{{v.term_id}}" class="each-to-child-term">
                <td width="50" class="text-center"><input type="checkbox" value="{{v.term_id}}" class="input-checkbox-control" /></td>
                <td>{{v.term_id}}</td>
                <td><a href="{{v.get_admin_permalink}}">{{v.gach_ngang}}{{v.name}} <i class="fa fa-edit"></i></a></td>
                <td><a href="{{v.view_url}}" target="_blank">{{v.slug}} <i class="fa fa-external-link"></i></a></td>
                <td class="d-none show-if-ads-type">&nbsp;</td>
                <td>&nbsp;</td>
                <td width="90">{{v.lang_key}}</td>
                <td width="90">{{v.count}}</td>
                <td width="60"><input type="text" data-id="{{v.term_id}}" value="{{v.term_order}}" size="5" class="form-control s change-update-term_order" /></td>
                <td width="90" class="text-center"><?php
                require __DIR__ . '/list_action.php';
                ?></td>
            </tr>
            <?php

            //echo $term_model->list_html_view( $data, '', $by_is_deleted, $controller_slug );
            //$term_model->get_admin_permalink($v['taxonomy'], $v['term_id']);

            ?>
        </tbody>
    </table>
</div>
<div class="public-part-page"> <?php echo $pagination; ?> </div>
<p class="d-none">* Copy đoạn code bên dưới rồi cho vào nơi cần hiển thị block này ở trong view. Nhớ thay %slug% thành slug thật trong danh sách ở trên.</p>
<script>
var term_data = <?php echo json_encode($data); ?>;
var for_action = '<?php echo $for_action; ?>';
var controller_slug = '<?php echo $controller_slug; ?>';
var DeletedStatus_DELETED = '<?php echo $DeletedStatus_DELETED; ?>';
</script>
<?php

if ( $taxonomy == TaxonomyType::ADS ) {
    ?>
<pre><code>&lt;?php $post_model->the_ads( '%slug%' ); ?&gt;</code></pre>
<?php
}

// js riêng cho từng post type (nếu có)
$base_model->add_js( 'admin/js/terms.js' );
$base_model->add_js( 'admin/js/' . $taxonomy . '.js' );

?>
<script>
WGR_vuejs('#app', {
    ALLOW_USING_MYSQL_DELETE: ALLOW_USING_MYSQL_DELETE,
    data: term_data,
    by_is_deleted: '<?php echo $by_is_deleted; ?>',
    controller_slug: controller_slug,
    for_action: for_action,
    DeletedStatus_DELETED: DeletedStatus_DELETED,
});
</script>