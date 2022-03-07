<!-- Modal logout -->
<div class="modal fade" id="autoSyncPostDetails" tabindex="-1" aria-labelledby="autoSyncPostDetailsLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="autoSyncPostDetailsLabel">Đồng bộ lại dữ liệu theo tiêu chuẩn chung</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body s14">
                <p>Thi thoảng có những cập nhật mới liên quan đến hệ thống <?php echo $name_type; ?>, yêu cầu phải chạy lại chức năng Cập nhật cho toàn bộ <?php echo $name_type; ?> để chúng có thể nhận tính năng mới...</p>
                <p>Khi đó, thay vì vào từng <?php echo $name_type; ?> để bấm Cập nhật thì sẽ sử dụng chức năng này, máy tính sẽ làm việc đó cho bạn.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <a href="admin/<?php echo $controller_slug; ?>?auto_update_module=1" class="d-inline btn btn-primary"> <i class="fa fa-refresh"></i> Bắt đầu đồng bộ </a> </div>
        </div>
    </div>
</div>
<div class="text-right"><a class="btn btn-info" data-bs-toggle="modal" data-bs-target="#autoSyncPostDetails" href="javascript:;"> <i class="fa fa-refresh"></i> Đồng bộ lại dữ liệu theo tiêu chuẩn chung</a></div>
<!-- END Modal --> 