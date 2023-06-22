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
<div class="medium bluecolor">
    <p>Code sẽ được tải trực tiếp từ github và tiến hành giải nén như thông thường</p>
    <p>Bước 1: download và giải nén system <a href="<?php echo $link_download_system_github; ?>" target="_blank" rel="nofollow"><em><?php echo $link_download_system_github; ?></em></a></p>
    <p>Bước 2: download và giải nén các module code còn lại: <a href="<?php echo $link_download_github; ?>" target="_blank" rel="nofollow"><em><?php echo $link_download_github; ?></em></a></p>
</div>
<p class="medium redcolor"><i class="fa fa-warning"></i> Lưu ý! việc update code yêu cầu kỹ năng xử lý code để đề phòng trường hợp update lỗi thì vẫn có thể khôi phục hoạt động của website.</p>
<div class="cf">
    <div class="lf f45">
        <p class="medium blackcolor">
            <input type="checkbox" id="confirm_is_coder" />
            Xác nhận bạn có khả năng xử lý code trong trường hợp lỗi.
        </p>
        <form method="post" action="./admin/dashboard/download_code" onsubmit="return before_start_download_in_github();" target="target_eb_iframe">
            <button type="submit" class="btn btn-warning blackcolor"><i class="fa fa-download"></i> Tiến hành download và giải nén code</button>
        </form>
    </div>
    <div class="lf f55">
        <p class="medium blackcolor">
            <input type="checkbox" id="confirm_is_super_coder" />
            XÓA toàn bộ code cũ và thay thế bằng phiên bản code mới nhất từ <a href="https://github.com/itvn9online/ci4-for-wordpress" target="_blank" rel="nofollow" class="bold">github</a>!
        </p>
        <form method="post" action="./admin/dashboard/reset_code" onsubmit="return before_start_reset_in_github();" target="target_eb_iframe">
            <button type="submit" class="btn btn-danger"><i class="fa fa-refresh"></i> Tiến hành download và reset code</button>
        </form>
    </div>
</div>
<?php

// phục hồi lại thư code từ thư mục app deleted nếu quá trình update code có lỗi
if ($app_deleted_exist === true) {
?>
    <br>
    <br>
    <div>
        <!-- Button trigger modal -->
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#restoreModal"> <i class="fa fa-undo"></i> Phục hồi lại code trước khi update </button>
        <button type="button" class="btn btn-info" data-bs-toggle="modal" data-bs-target="#cleanupModal"> <i class="fa fa-magic"></i> Dọn dẹp code sau khi khi update </button>
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
                    </a>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="cleanupModal" tabindex="-1" aria-labelledby="cleanupModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="cleanupModalLabel">Xác nhận dọn dẹp code!</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">Dùng khi muốn dọn dẹp code sau khi update! (xóa các thư mục -deleted)</div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <a href="admin/dashboard/cleanup_code" class="d-inline" target="target_eb_iframe">
                        <button type="button" class="btn btn-primary"><i class="fa fa-magic"></i> Confirm</button>
                    </a>
                </div>
            </div>
        </div>
    </div>
<?php
}

//
//var_dump( $app_deleted_exist );

?>
<br>
<br>
<br>
<h3>Giải nén lại toàn bộ thirdparty code</h3>
<p class="medium bluecolor">Chức năng này sẽ tiến hành unzip lại toàn bộ file .zip trong thư mục <strong>thirdparty</strong> và <strong>vendor</strong>, do điều kiện mặc định là nếu thư mục tương ứng tồn tại thì không giải nén nên khi cần update thirdparty code là không update được</p>
<div>
    <form action="./admin/dashboard/unzip_thirdparty" method="post" onSubmit="return before_unzip_thirdparty();" target="target_eb_iframe">
        <input type="hidden" name="data" value="1" />
        <div>
            <button type="submit" class="btn btn-success"><i class="fa fa-file-archive-o"></i> Unzip thirdparty code</button>
        </div>
    </form>
</div>
<br>
<br>
<br>
<h3>Phần mềm bên thứ 3</h3>
<p class="medium bluecolor">Nhằm mục đích giúp cho code không được quá lỗi thời, có thể ảnh hưởng tới các vấn đề về bảo mật, thi thoảng hãy kiểm tra và cập nhật phần mềm bên thứ 3.</p>
<h4>Danh sách phần mềm bên thứ 3 đang được sử dụng trong website này:</h4>
<ol>
    <?php
    foreach ($arr_list_thirdparty as $v) {
    ?>
        <li><?php echo $v; ?></li>
    <?php
    }
    ?>
</ol>
<h4>Liên kết để kiểm tra và download phiên bản mới (nếu có):</h4>
<p class="medium orgcolor">* Các phần mềm có độ ưu tiên cập nhật giảm dần, càng xuống dưới thì mức độ cần để ý càng giảm. Một số phần mềm không có trong danh sách thường do phần mềm đó đã dừng phát triển quá lâu và coder chưa có thời gian để tìm ra phần mềm thay thế.</p>
<ol>
    <?php
    foreach ($arr_download_thirdparty as $v) {
    ?>
        <li><a href="<?php echo $v; ?>" target="_blank" rel="nofollow"><?php echo $v; ?></a></li>
    <?php
    }
    ?>
</ol>
<div class="medium">
    <p class="bold redcolor">* Lưu ý! Đối với các thirdparty dùng mã nguồn PHP, ưu tiên sử dụng composer để download và update code. Code lúc nạp sẽ nạp file autoload.php có được sau khi composer</p>
    <p class="bold">Cài đặt composer cho Window:</p>
    <ol>
        <li>Download và cài đặt composer cho window: https://getcomposer.org/download/</li>
        <li>Cài đặt xampp nếu chưa có: https://www.apachefriends.org/download.html</li>
        <li>Chỉnh biến môi trường cho php
            <blockquote>Add <strong>C:\xampp\php</strong> to your PATH environment variable.(My Computer->properties -> Advanced system setting -> Environment Variables ->path (click on edit))</blockquote>
        </li>
        <li>Tham khảo: https://stackoverflow.com/questions/31291317/php-is-not-recognized-as-an-internal-or-external-command-in-command-prompt</li>
        <li>Mở 1 terminal mới với quyền administrator</li>
        <li>Tạo thư mục để code composer sẽ tập trung trong này:
            <blockquote>mkdir composer</blockquote>
            <blockquote>cd composer</blockquote>
        </li>
        <li>Chạy lệnh composer code được cung cấp bởi tác giả.</li>
    </ol>
    <p class="bold">Cài đặt composer cho Linux/ Ubuntu:</p>
    <ol>
        <li>Nếu chưa cài đặt composer thì vào đây để xem hướng dẫn cài đặt</li>
        <li>https://phoenixnap.com/kb/how-to-install-and-use-php-composer-on-centos-7</li>
        <li>Chạy các lệnh sau để tạo thư mục composer riêng:
            <blockquote>cd ~</blockquote>
            <blockquote>mkdir -p /root/composer</blockquote>
            <blockquote>cd /root/composer</blockquote>
        </li>
        <li>Chạy lệnh composer code được cung cấp bởi tác giả.</li>
    </ol>
</div>
<!-- -->
<script>
    var themeName = '<?php echo THEMENAME; ?>'
</script>
<?php
//
$base_model->add_js('admin/js/update_code.js');
