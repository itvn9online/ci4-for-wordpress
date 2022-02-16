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
<p class="medium blackcolor">
    <input type="checkbox" id="confirm_is_coder" />
    Xác nhận bạn có khả năng xử lý code trong trường hợp lỗi.</p>
<div><a href="admin/dashboard/download_code" target="target_eb_iframe" onClick="return before_start_download_in_github();">
    <button type="button" class="btn btn-warning blackcolor"><i class="fa fa-download"></i> Tiến hành download và giải nén code</button>
    </a></div>
<?php

//
$base_model->add_js( 'admin/js/update_code.js' );
