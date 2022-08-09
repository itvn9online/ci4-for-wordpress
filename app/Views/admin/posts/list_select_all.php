<div class="quick-edit-form">
    <div class="row">
        <div class="col">
            <div v-if="post_status == PostType_DELETED">
                <button type="button" onClick="return click_restore_checked('posts');" class="btn btn-info"><i class="fa fa-bars"></i> Chuyển thành Bản nháp</button>
                <button type="button" onClick="return click_remove_checked('posts');" class="btn btn-danger"><i class="fa fa-remove"></i> XÓA <span v-if="allow_mysql_delete == true">Hoàn toàn</span></button>
            </div>
            <div v-if="post_status != PostType_DELETED">
                <button type="button" onClick="return click_delete_checked('posts');" class="btn btn-danger"><i class="fa fa-trash"></i> Lưu trữ</button>
            </div>
        </div>
    </div>
</div>
