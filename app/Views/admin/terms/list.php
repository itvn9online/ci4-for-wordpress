<?php

// Libraries
use App\Libraries\TaxonomyType;

//
//print_r($data);

//
$ads_size = '';
if ($taxonomy == TaxonomyType::ADS) {
    $ads_size = '%term_meta.custom_size%';
}

// css riêng cho từng post type (nếu có)
$base_model->adds_css([
    'admin/css/terms.css',
    'admin/css/' . $taxonomy . '.css'
]);

?>
<ul class="admin-breadcrumb">
    <li>
        <?php echo $name_type; ?> (
        <?php echo $totalThread; ?>)
    </li>
</ul>
<div id="app" class="ng-main-content2">
    <div class="cf admin-search-form">
        <div class="lf f50">
            <form name="frm_admin_search_controller" action="./admin/<?php echo $controller_slug; ?>" method="get">
                <div class="cf">
                    <div class="lf f30">
                        <?php
                        if ($by_is_deleted > 0) {
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
            <div class="d-inline"> <a href="<?php $term_model->admin_permalink($taxonomy, 0, $controller_slug); ?>" class="btn btn-success btn-mini"> <i class="fa fa-plus"></i> Thêm mới
                    <?php echo $name_type; ?>
                </a> </div>
            <!-- -->
            <div v-if="by_is_deleted == DeletedStatus_DELETED" class="d-inline"><a :href="'admin/' + controller_slug" class="btn btn-primary btn-mini"> <i class="fa fa-list"></i> Quay lại</a></div>
            <div v-if="by_is_deleted != DeletedStatus_DELETED" class="d-inline"><a :href="'admin/' + controller_slug + '?is_deleted=' + DeletedStatus_DELETED" class="btn btn-mini"> <i class="fa fa-trash"></i> Lưu trữ</a></div>
        </div>
    </div>
    <br>
    <?php

    //
    include __DIR__ . '/list_select_all.php';

    //
    if ($list_table_path != '') {
        echo '<div class="wgr-view-path">' . ADMIN_ROOT_VIEWS . $list_table_path . '/list_table.php</div>';

        // sử dụng list table riêng của taxonomy nếu có khai báo
        include ADMIN_ROOT_VIEWS . $list_table_path . '/list_table.php';
    } else {
        // list table mặc định
        include __DIR__ . '/list_table.php';
    }

    ?>
</div>
<div class="public-part-page">
    <?php echo $pagination; ?>
</div>
<p class="d-none">* Copy đoạn code bên dưới rồi cho vào nơi cần hiển thị block này ở trong view. Nhớ thay %slug% thành slug thật trong danh sách ở trên.</p>
<!-- Modal add multi term -->
<div class="modal fade" id="termMultiAddModal" tabindex="-1" aria-labelledby="termMultiAddModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form name="frm_admin_search_controller" action="./admin/<?php echo $controller_slug; ?>/multi_add" method="post" target="target_eb_iframe">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="termMultiAddModalLabel">Thêm nhanh
                        <?php echo $name_type; ?>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="d-none">
                        <input type="number" name="data[term_id]" id="data_term_id" value="" />
                    </div>
                    <p>
                        <?php echo $name_type; ?> cha: <strong id="multi_add_parent_name"></strong>
                    </p>
                    <div class="form-group">
                        <label><strong>Nhập danh sách
                                <?php echo $name_type; ?> cần thêm:
                            </strong>
                            <textarea name="data[term_name]" id="data_term_name" rows="10" class="d-block form-control" aria-required="true" required></textarea>
                        </label>
                        <p class="description">Có thể nhập nhiều
                            <?php echo $name_type; ?>, mỗi
                            <?php echo $name_type; ?> cách nhau bởi dấu xuống dòng [Enter].
                        </p>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary"><i class="fa fa-plus"></i> Bắt đầu thêm</button>
                </div>
            </div>
        </form>
    </div>
</div>
<!-- END Modal add multi term -->
<?php

//
$base_model->JSON_parse([
    'term_data' => $data,
]);

//
$base_model->JSON_echo([], [
    'for_action' => $for_action,
    'controller_slug' => $controller_slug,
    'DeletedStatus_DELETED' => $DeletedStatus_DELETED,
]);

//
if ($taxonomy == TaxonomyType::ADS) {
?>
    <pre><code>&lt;?php $post_model->the_ads( '%slug%' ); ?&gt;</code></pre>
<?php
}

// js riêng cho từng post type (nếu có)
$base_model->adds_js([
    'admin/js/terms.js',
    'admin/js/' . $taxonomy . '.js'
]);

?>
<script>
    WGR_vuejs('#app', {
        allow_mysql_delete: allow_mysql_delete,
        data: term_data,
        by_is_deleted: '<?php echo $by_is_deleted; ?>',
        controller_slug: controller_slug,
        for_action: for_action,
        DeletedStatus_DELETED: DeletedStatus_DELETED,
    });
</script>