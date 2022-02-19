//
function done_submit_update_code() {
    $('body').css({
        'opacity': 1
    });
    document.frm_global_upload.reset();
    WGR_alert('Update và giải nén code thành công');
}

function auto_submit_update_code() {
    $('body').css({
        'opacity': 0.1
    });
    document.frm_global_upload.submit();
}

// chức năng download code thì không cần thiết
function before_start_download_in_github() {
    if ($('#confirm_is_coder').is(':checked') == false) {
        $('#confirm_is_coder').parent('p').addClass('redcolor').addClass('bold').addClass('medium18');
        return false;
    }

    //
    $('body').css({
        'opacity': 0.1
    });
    return true;
}

// chức năng reset code -> yêu cầu xác nhận 2 lần
function before_start_reset_in_github() {
    if (before_start_download_in_github() !== true) {
        $('body').css({
            'opacity': 1
        });
        return false;
    }

    //
    if ($('#confirm_is_super_coder').is(':checked') == false) {
        $('#confirm_is_super_coder').parent('p').addClass('redcolor').addClass('bold').addClass('medium18');
        return false;
    }

    //
    $('body').css({
        'opacity': 0.1
    });
    return true;
}
