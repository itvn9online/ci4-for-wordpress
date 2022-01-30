/*
 * sau khi XÓA sản phẩm thành công thì xử lý ẩn bản ghi bằng javascript
 */
function done_delete_restore(id) {
    $('.admin-media-attachment li[data-id="' + id + '"]').fadeOut();
}

//
$('#upload_image').change(function () {
    $('body').css({
        'opacity': 0.33
    });
    document.frm_global_upload.submit();
});
