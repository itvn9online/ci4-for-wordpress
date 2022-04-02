<div class="d-inline"> <a :href="'admin/' + controller_slug + '/add'" class="btn btn-success btn-mini"> <i class="fa fa-plus"></i> Thêm mới {{member_name}}</a> </div>
<!-- -->
<div v-if="by_is_deleted == DeletedStatus_DELETED" class="d-inline"><a :href="'admin/' + controller_slug + '?member_type=' + member_type" class="btn btn-primary btn-mini"> <i class="fa fa-list"></i> Quay lại</a></div>
<div v-if="by_is_deleted != DeletedStatus_DELETED" class="d-inline"><a :href="'admin/' + controller_slug + '?member_type=' + member_type + '&is_deleted=' + DeletedStatus_DELETED" class="btn btn-mini"> <i class="fa fa-trash"></i> Lưu trữ</a></div>
