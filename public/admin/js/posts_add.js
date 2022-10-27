// chỉnh lại size ảnh nếu có lựa chọn
$('#post_meta_image_size').change(function () {
    var avt_size = $(this).val() || '';
    if (avt_size != '') {
        console.log('avt size:', avt_size);

        // nếu là sử dụng kích cỡ thật
        if (avt_size == 'image_origin') {
            // lấy cỡ large
            var img = $('#post_meta_image_large').val() || '';
            // xóa đi chữ large ở cuối rồi thêm vào thôi
            if (img != '') {
                $('#post_meta_image').val(img.replace('-large.', '.'));
            }
        } else {
            // còn lại sẽ dò tìm kích cỡ ưng ý và thêm
            var img = $('#post_meta_' + avt_size).val() || '';
            if (img != '') {
                $('#post_meta_image').val(img);
            }
        }
    }
});

//
$(document).ready(function () {
    for_admin_global_checkbox();
});
