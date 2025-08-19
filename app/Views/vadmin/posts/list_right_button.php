<!-- menu sẽ được tự động khởi tạo khi dùng hàm để gọi -> không cho add thủ công -->
<div>
    <div :class="'post_type-' + post_type" class="d-inline add-new-posts"><a href="<?php $post_model->admin_permalink($post_type, 0, $controller_slug); ?>" class="btn btn-success btn-mini"> <i class="fa fa-plus"></i> Thêm mới <?php echo $name_type; ?></a></div>
    <!-- -->
    <div v-if="post_status == PostType_DELETED" class="d-inline"><a :href="'sadmin/' + controller_slug" class="btn btn-primary btn-mini"> <i class="fa fa-list"></i> Quay lại</a></div>
    <div v-if="post_status != PostType_DELETED" class="d-inline"><a :href="'sadmin/' + controller_slug + '?post_status=' + PostType_DELETED + '&is_deleted=' + PostType_DELETED" class="btn btn-mini"> <i class="fa fa-trash"></i> Lưu trữ</a></div>
</div>
<div>
    <div v-if="post_type != 'product'" :class="'post_type-' + post_type" class="d-inline add-new-posts"><a href="sadmin/<?php echo $controller_slug; ?>/download/<?php echo $_SERVER['HTTP_HOST'] . '-' . $post_type . '-' . date('Ymd-His'); ?>.xml" class="btn btn-info btn-mini" target="_blank"> <i class="fa fa-sitemap"></i> Xem XML</a></div>
    <div v-if="post_type == 'product'" :class="'post_type-' + post_type" class="d-inline add-new-posts"><a href="sadmin/<?php echo $controller_slug; ?>/download/?type=csv" class="btn btn-info btn-mini" target="_blank"> <i class="fa fa-file-excel-o"></i> Download CSV</a></div>
</div>