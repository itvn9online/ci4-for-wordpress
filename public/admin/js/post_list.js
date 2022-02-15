/*
 * sau khi XÓA sản phẩm thành công thì xử lý ẩn bản ghi bằng javascript
 */
function done_delete_restore(id) {
    $('#admin_main_list tr[data-id="' + id + '"]').fadeOut();
}

/*
 * tạo select box các nhóm dữ liệu cho khung tìm kiếm
 */
(function () {
    //console.log(a);

    //
    if ($('.each-to-taxonomy-group').length == 0) {
        return false;
    }

    //
    $('.each-to-taxonomy-group').each(function () {
        var a = $(this).attr('data-taxonomy') || '';
        //console.log(a);
        if (a != '' && typeof arr_all_taxonomy[a] != 'undefined') {
            $(this).append(create_term_select_option(arr_all_taxonomy[a]));
        } else {
            $(this).parent('.hide-if-no-taxonomy').hide();
        }
    });
})();
