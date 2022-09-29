//
jQuery(document).ready(function () {
    // thêm iframe để submit form cho tiện
    _global_js_eb.add_primari_iframe();
    _global_js_eb.wgr_nonce('profile_form');
    _global_js_eb.wgr_nonce('pasword_form');

    // hiển thị trước ảnh đại diện nếu có
    document.getElementById("file-input-media").onchange = function () {
        //console.log(this.files);

        //
        var mediaData = this.files[0];
        console.log(mediaData);

        //
        var reader = new FileReader();
        reader.onload = function (e) {
            $('#click-chose-media img').css('background-image', `url(${e.target.result})`);

            // upload luôn ảnh lên server -> kèm resize tại local cho nó nhẹ server
            ajax_push_image_to_server({
                action: 'uploads/avatar_push',
                data: e.target.result,
                file_name: 'avatar',
                last_modified: Math.ceil(mediaData.lastModified / 1000),
                set_bg: '#click-chose-media img',
                set_thumb: '#file-input-avatar',
                input_file: '#file-input-media',
                img_max_width: 250,
            });
        };
        // chỉ lấy ảnh số 0
        reader.readAsDataURL(mediaData);
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
