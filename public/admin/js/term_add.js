//console.log(arr_list_category);

/*
 * tạo các option con cho phần select Danh mục cha
 */
if ($('#data_parent').length > 0) {
    // chạy ajax nạp dữ liệu của taxonomy
    load_term_select_option(set_parent, 'data_parent', function (data, jd) {
        //console.log(data);
        $('#data_parent').append(create_term_select_option(data)).removeClass('set-selected');
        // disabled option của term hiện tại đi -> không để nó chọn chính nó làm cha
        if (data_term_id > 0) {
            $('#data_parent option[value="' + data_term_id + '"]').prop('disabled', true);
        }

        // tạo lại selected
        WGR_set_prop_for_select('#data_parent');
    });
}

// thêm nút add ảnh đại diện
add_and_show_post_avt('#data_term_avatar', '', 'medium');
add_and_show_post_avt('#data_term_favicon', '', 'medium');