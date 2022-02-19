<ul class="admin-breadcrumb">
    <li>Update code</li>
</ul>
<h3>Chức năng dùng để update code dưới dạng file .ZIP</h3>
<p class="medium bluecolor">Chọn file code có đuôi .zip (được cung cấp bởi bên code) sau đó upload lên host, hệ thống sẽ thực hiện giải nén và cập nhật code cho website</p>
<div>
    <form action="" method="post" name="frm_global_upload" role="form" enctype="multipart/form-data" target="target_eb_iframe">
        <input type="hidden" name="data" value="1" />
        <div>
            <input type="file" name="upload_code" id="upload_code" onChange="return auto_submit_update_code();" accept=".zip" />
        </div>
        <br>
        <div>
            <button type="submit" class="btn btn-success"><i class="fa fa-upload"></i> Upload và giải nén code</button>
        </div>
    </form>
</div>
<br>
<br>
<br>
<h3>Chức năng update code trực tiếp từ github</h3>
<p class="medium bluecolor">Code sẽ được tải từ link <em><?php echo $link_download_github; ?></em> và tiến hành giải nén như thông thường.</p>
<p class="medium redcolor"><i class="fa fa-warning"></i> Lưu ý! việc update code yêu cầu kỹ năng xử lý code để đề phòng trường hợp update lỗi thì vẫn có thể khôi phục hoạt động của website.</p>
<div class="cf">
    <div class="lf f45">
        <p class="medium blackcolor">
            <input type="checkbox" id="confirm_is_coder" />
            Xác nhận bạn có khả năng xử lý code trong trường hợp lỗi.</p>
        <a href="admin/dashboard/download_code" target="target_eb_iframe" onClick="return before_start_download_in_github();">
        <button type="button" class="btn btn-warning blackcolor"><i class="fa fa-download"></i> Tiến hành download và giải nén code</button>
        </a></div>
    <div class="lf f55">
        <p class="medium blackcolor">
            <input type="checkbox" id="confirm_is_super_coder" />
            XÓA toàn bộ code cũ và thay thế bằng phiên bản code mới nhất từ <strong>github</strong>!</p>
        <a href="admin/dashboard/reset_code" target="target_eb_iframe" onClick="return before_start_reset_in_github();">
        <button type="button" class="btn btn-danger"><i class="fa fa-refresh"></i> Tiến hành download và reset code</button>
        </a></div>
</div>
<?php

// phục hồi lại thư code từ thư mục app deleted nếu quá trình update code có lỗi
if ( $app_deleted_exist === true ) {
    ?>
<br>
<br>
<div> 
    <!-- Button trigger modal -->
    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#restoreModal"> <i class="fa fa-undo"></i> Phục hồi lại code trước khi update </button>
</div>
<!-- Modal -->
<div class="modal fade" id="restoreModal" tabindex="-1" aria-labelledby="restoreModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="restoreModalLabel">Xác nhận restore code!</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">Dùng khi muốn sử dụng lại code trước khi update!</div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <a href="admin/dashboard/restore_code" class="d-inline" target="target_eb_iframe">
                <button type="button" class="btn btn-primary"><i class="fa fa-undo"></i> Confirm</button>
                </a> </div>
        </div>
    </div>
</div>
<?php
}

//
//var_dump( $app_deleted_exist );

//
$base_model->add_js( 'admin/js/update_code.js' );
