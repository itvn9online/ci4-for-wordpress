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
