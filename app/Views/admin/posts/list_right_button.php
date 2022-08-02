<!-- menu sẽ được tự động khởi tạo khi dùng hàm để gọi -> không cho add thủ công -->
<div :class="'post_type-' + post_type" class="d-inline add-new-posts"><a href="<?php $post_model->admin_permalink( $post_type, 0, $controller_slug ); ?>" class="btn btn-success btn-mini"> <i class="fa fa-plus"></i> Thêm mới <?php echo $name_type; ?></a></div>
<!-- -->
<div v-if="post_status == PostType_DELETED" class="d-inline"><a :href="'admin/' + controller_slug" class="btn btn-primary btn-mini"> <i class="fa fa-list"></i> Quay lại</a></div>
<div v-if="post_status != PostType_DELETED" class="d-inline"><a :href="'admin/' + controller_slug + '?post_status=' + PostType_DELETED + '&is_deleted=' + PostType_DELETED" class="btn btn-mini"> <i class="fa fa-trash"></i> Lưu trữ</a></div>
