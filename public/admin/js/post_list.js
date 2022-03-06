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
    if ($('.each-to-group-taxonomy').length == 0) {
        return false;
    }

    //
    $('.each-to-group-taxonomy').each(function () {
        var a = $(this).attr('data-taxonomy') || '';
        var jd = $(this).attr('id') || '';
        if (jd == '') {
            jd = '_' + Math.random().toString(32).replace('.', '_');
            //console.log(jd);

            //
            $(this).attr({
                id: jd
            });
        }
        //console.log(a);

        // chạy ajax nạp dữ liệu của taxonomy
        load_term_select_option(a, jd, function (data, jd) {
            if (data.length > 0) {
                // tạo select
                $('#' + jd).append(create_term_select_option(data)).removeClass('set-selected');

                // tạo lại selected
                WGR_set_prop_for_select('#' + jd);
            } else {
                $('#' + jd).parent('.hide-if-no-taxonomy').hide();
            }
        });
    });
})();
