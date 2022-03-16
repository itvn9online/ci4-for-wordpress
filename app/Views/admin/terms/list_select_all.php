<div class="quick-edit-form">
    <div class="row">
        <div class="col">
            <button type="button" onClick="return click_restore_checked_term();" ng-if="by_is_deleted == DeletedStatus_DELETED" class="btn btn-info"><i class="fa fa-refresh"></i> Phục hồi</button>
            <button type="button" onClick="return click_delete_checked_term();" ng-if="by_is_deleted != DeletedStatus_DELETED" class="btn btn-danger"><i class="fa fa-trash"></i> Xóa</button>
        </div>
    </div>
</div>
