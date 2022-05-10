jQuery(document).ready(function () {
    // thêm iframe để submit form cho tiện
    _global_js_eb.add_primari_iframe();
    _global_js_eb.wgr_nonce('profile_form');
    _global_js_eb.wgr_nonce('pasword_form');

    // hiển thị trước ảnh đại diện nếu có
    document.getElementById("file-input-cd").onchange = function () {
        var reader = new FileReader();
        reader.onload = function (e) {
            $('#click-chose-CD img').css('background-image', `url(${e.target.result})`)
        };
        reader.readAsDataURL(this.files[0]);
    };
});


//
$('.click-change-email').click(function () {
    $('.change-user_email').addClass('d-none');
    $('.changed-user_email').removeClass('d-none');
    $('#data_user_email').prop('disabled', false).prop('readonly', false).focus();
});

//
$('.cancel-change-email').click(function () {
    $('.change-user_email').removeClass('d-none');
    $('.changed-user_email').addClass('d-none');
    $('#data_user_email').prop('disabled', true).prop('readonly', true);
});
