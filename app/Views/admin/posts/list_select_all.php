<div class="quick-edit-form">
    <div class="row">
        <div class="col">
            <button type="button" onClick="return click_restore_checked_post();" ng-if="post_status == PostType_DELETED" class="btn btn-info"><i class="fa fa-bars"></i> Chuyển thành Bản nháp</button>
            <button type="button" onClick="return click_delete_checked_post();" ng-if="post_status != PostType_DELETED" class="btn btn-danger"><i class="fa fa-trash"></i> Xóa</button>
        </div>
    </div>
</div>
