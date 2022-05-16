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

function check_user_email_before_add() {
    // tạo email theo họ tên -> dành cho trường hợp không có email
    var a = $('#data_user_email').val() || '';
    if (a == '') {
        var b = $('#data_display_name').val() || '';
        if (b != '') {
            b = g_func.non_mark_seo(b);
            b = b.replace(/\-/g, '');
            if (b != '') {
                a = b;
                $('#data_user_email').val(a).change();
            }
        }
    }
    if (a != '' && a.split('@').length == 1) {
        $('#data_user_email').val(a + '@' + document.domain)
    }
    return true;
}

function before_submit_user_add() {
    check_user_email_before_add();
    return true;
}

//
$('#data_user_email').change(function () {
    var a = $(this).val();
    if (a != '') {
        a = $.trim(a);
        a = a.toLowerCase();
        $(this).val(a);

        if ($('#data_user_login').val() == '') {
            $('#data_user_login').val(g_func.non_mark_seo($.trim(a.split('@')[0])));
        }
    }
}).keydown(function (e) {
    //console.log(e.keyCode);
    if (e.keyCode == 13) {
        var a = $(this).val() || '';
        if (a != '' && a.split('@').length == 1) {
            WGR_alert('Email không đúng định dạng được hỗ trợ', 'warning');
            setTimeout(function () {
                $('#data_user_email').val(a + '@' + document.domain)
            }, 200);
        }
    }
});

//
$('#data_ci_pass').focus(function () {
    $('.redcolor-if-pass-focus').addClass('redcolor');
}).blur(function () {
    $('.redcolor-if-pass-focus').removeClass('redcolor');
});
