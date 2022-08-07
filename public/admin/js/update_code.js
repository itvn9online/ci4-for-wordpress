//
function done_submit_update_code(file_name) {
    WGR_body_opacity();
    document.frm_global_upload.reset();
    if (typeof file_name != 'undefined' && file_name != '') {
        file_name = ' (' + file_name + ')';
    }
    WGR_alert('Update và giải nén code thành công' + file_name);

    //
    $('#confirm_is_coder').prop('checked', false);
    $('#confirm_is_super_coder').prop('checked', false);
}

function auto_submit_update_code() {
    var fullPath = $('#upload_code').val() || '';
    //console.log(fullPath);

    //
    if (fullPath == '') {
        WGR_alert('Không xác định được file upload', 'error');
        return false;
    }

    //
    //fullPath = fullPath.split('.')[0];

    // kiểm tra file upload phải đúng với tên theme hiện tại
    if (fullPath.split(themeName).length === 1
        // hoặc file code chính là: ci4-for-wordpress
        && fullPath.split('ci4-for-wordpress.').length === 1
        // hoặc system của codeigniter
        && fullPath.split('system.').length === 1) {
        WGR_alert('Theme này chỉ hỗ trợ upload file trùng tên với theme là: ' + themeName, 'warning');
        return false;
    }

    //
    WGR_body_opacity(0.1);
    document.frm_global_upload.submit();
    return true;
}

// chức năng download code thì không cần thiết
function before_start_download_in_github() {
    if ($('#confirm_is_coder').is(':checked') == false) {
        $('#confirm_is_coder').parent('p').addClass('redcolor').addClass('bold').addClass('medium18');
        return false;
    }

    //
    WGR_body_opacity(0.1);
    return true;
}

// chức năng reset code -> yêu cầu xác nhận 2 lần
function before_start_reset_in_github() {
    if (before_start_download_in_github() !== true) {
        return false;
    }
    WGR_body_opacity(1);

    //
    if ($('#confirm_is_super_coder').is(':checked') == false) {
        $('#confirm_is_super_coder').parent('p').addClass('redcolor').addClass('bold').addClass('medium18');
        return false;
    }

    //
    WGR_body_opacity(0.1);
    return true;
}

function done_submit_restore_code() {
    $('#restoreModal, #cleanupModal').modal('hide');
    window.location = window.location.href;
}

function before_unzip_thirdparty() {
    return WGR_body_opacity(0.1);
}

function after_unzip_thirdparty() {
    return WGR_body_opacity();
}

function done_unzip_system() {
    return done_submit_restore_code();
}
