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
<?php

//
$base_model->add_js( 'admin/js/update_code.js' );
