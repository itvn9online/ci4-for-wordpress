<div class="d-inline"> <a :href="'sadmin/' + controller_slug + '/add'" class="btn btn-success btn-mini"> <i class="fa fa-plus"></i> Thêm mới {{member_name}}</a> </div>
<!-- -->
<?php
if ($totalThread > 0) {
?>
    <div class="d-inline"> <a :href="'sadmin/' + controller_slug + '/download'" class="btn btn-info btn-mini" target="target_eb_iframe"> <i class="fa fa-download"></i> Download {{member_name}}</a> </div>
<?php
}
?>
<!-- -->
<div v-if="by_is_deleted == DeletedStatus_DELETED" class="d-inline"><a :href="'sadmin/' + controller_slug + '?member_type=' + member_type" class="btn btn-primary btn-mini"> <i class="fa fa-list"></i> Quay lại</a></div>
<div v-if="by_is_deleted != DeletedStatus_DELETED" class="d-inline"><a :href="'sadmin/' + controller_slug + '?member_type=' + member_type + '&is_deleted=' + DeletedStatus_DELETED" class="btn btn-mini"> <i class="fa fa-trash"></i> Lưu trữ</a></div>