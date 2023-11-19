<ul class="admin-breadcrumb">
    <li>Rewrite Rule</li>
</ul>
<div class="medium">
    <p>* Chức năng tạo các bản ghi dùng để redirect link 404 tương tự như trong file .htaccess dùng thay thế cho rewrite của htaccess hoặc các host không hỗ trợ htaccess như nginx.</p>
    <p>* Nơi lưu trữ file: <strong><?php echo $rules_path; ?></strong></p>
    <p>Nội dung mẫu:</p>
    <div>
        <input type="text" value="RewriteRule ^url-cu-can-redirect$ <?php echo DYNAMIC_BASE_URL; ?>url-se-redirect-den [R=301,L]" onDblClick="click2Copy(this);" readonly class="form-control" />
    </div>
    <p>Hoặc:</p>
    <div>
        <input type="text" value="RewriteRule ^url-cu-can-redirect$ /url-se-redirect-den [R=301,L]" onDblClick="click2Copy(this);" readonly class="form-control" />
    </div>
    <p>Hoặc:</p>
    <div>
        <input type="text" value="RewriteRule ^url-cu-can-redirect$ url-se-redirect-den [R=301,L]" onDblClick="click2Copy(this);" readonly class="form-control" />
    </div>
    <p>Mặc định sẽ là redirect 301 (redirect vĩnh viễn), khi cần redirect tạm thời, hãy đặt thành 302 như sau:</p>
    <div>
        <input type="text" value="RewriteRule ^url-cu-can-redirect$ url-se-redirect-den [R=302,L]" onDblClick="click2Copy(this);" readonly class="form-control" />
    </div>
    <p>Khi cần vô hiệu hóa tạm thời tính năng redirect 1 url nào đó, hãy thêm dấu # vào trước. Ví dụ:</p>
    <div>
        <input type="text" value="#RewriteRule ^url-bi-vo-hieu-hoa$ <?php echo DYNAMIC_BASE_URL; ?>url-se-redirect-den [R=301,L]" onDblClick="click2Copy(this);" readonly class="form-control" />
    </div>
</div>
<br>
<div class="form-horizontal">
    <form action="" method="post" role="form" enctype="multipart/form-data" target="target_eb_iframe">
        <input type="hidden" value="" name="data[has_change]" id="data_has_change" />
        <div>
            <textarea rows="<?php echo count(explode("\n", $rules_content)) + 10; ?>" placeholder="Danh sách các RewriteRule, mỗi rule cách nhau bởi dấu xuống dòng" name="data[rules]" onchange="$('#data_has_change').val('yes');" class="form-control medium"><?php echo htmlentities($rules_content, ENT_QUOTES, 'UTF-8'); ?></textarea>
        </div>
        <div class="form-actions frm-fixed-btn cf">
            <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> Cập nhật</button>
        </div>
    </form>
</div>