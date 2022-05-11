function open_input_change_user_password() {
    $('.hide-if-change-password').hide();
    $('.show-if-change-password').removeClass('d-none').show();
    $('#data_ci_pass').focus();
}

function close_input_change_user_password() {
    $('.hide-if-change-password').show();
    $('.show-if-change-password').hide();
    $('#data_ci_pass').val('');
}

function submit_input_change_user_password() {
    if (confirm('Xác nhận thay đổi mật khẩu đăng nhập cho tài khoản này!') === true) {
        var a = $('#data_ci_pass').val() || '';
        if (a.length >= 6) {
            document.admin_global_form.submit();
        } else {
            WGR_alert('Mật khẩu tối thiểu phải từ 6 ký tự trở lên', 'error');
            $('#data_ci_pass').focus();
        }
        return true;
    }
    return false;
}

function random_input_change_user_password() {
    var a = Math.random().toString(32).split('.')[1].substr(0, 8);
    var b = Math.random().toString(32).split('.')[1].substr(0, 9);
    $('#data_ci_pass').val(a + '@' + b);
}

//
$('#data_ci_pass').focus(function () {
    $('.redcolor-if-pass-focus').addClass('redcolor');
}).blur(function () {
    $('.redcolor-if-pass-focus').removeClass('redcolor');
});
