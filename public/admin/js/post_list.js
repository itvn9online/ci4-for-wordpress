/*
 * sau khi XÓA sản phẩm thành công thì xử lý ẩn bản ghi bằng javascript
 */
function done_delete_restore(id) {
    $('#admin_main_list tr[data-id="' + id + '"]').fadeOut();
}

//
function action_delete_restore_checked_post(method_control, method_name) {
    if (confirm('Xác nhận ' + method_name + ' các bản ghi đã chọn!') !== true) {
        return false;
    }
    //console.log(arr_check_checked_all);

    //
    jQuery.ajax({
        type: 'POST',
        url: 'admin/posts/' + method_control,
        dataType: 'json',
        data: {
            ids: arr_check_checked_all.join(','),
        },
        success: function (data) {
            if (typeof data.error != 'undefined') {
                WGR_alert(data.error + ' - Code: ' + data.code, 'error');
            } else if (typeof data.result != 'undefined') {
                if (data.result === true) {
                    WGR_alert(method_name + ' các bản ghi đã chọn thành công');

                    //
                    for (var i = 0; i < arr_check_checked_all.length; i++) {
                        // bỏ check cho các checkbox
                        $('.input-checkbox-control[value="' + arr_check_checked_all[i] + '"]').hide().remove();
                        // xóa luôn TR đi
                        $('#admin_main_list tr[data-id="' + arr_check_checked_all[i] + '"]').hide().remove();
                    }
                    //arr_check_checked_all = [];
                    $('.input-checkbox-all').prop('checked', false);
                    get_check_checked_all_value();
                } else {
                    WGR_alert('Có lỗi trong quá trình ' + method_name + ' bản ghi', 'warning');
                    console.log(data);
                }
            } else {
                console.log(data);
            }
        }
    });
}

function click_delete_checked_post() {
    action_delete_restore_checked_post('delete_all', 'XÓA');
}

//
function click_restore_checked_post() {
    action_delete_restore_checked_post('restore_all', 'Khôi phục');
}

/*
 * tạo select box các nhóm dữ liệu cho khung tìm kiếm
 */
$(document).ready(function () {
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
});
