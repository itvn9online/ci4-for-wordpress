//console.log(arr_list_category);

/*
 * tạo các option con cho phần select Danh mục cha
 */
if ($('#data_parent').length > 0) {
    // chạy ajax nạp dữ liệu của taxonomy
    load_term_select_option(set_parent, 'data_parent', function (data, jd) {
        $('#data_parent').append(create_term_select_option(data)).removeClass('set-selected');

        // tạo lại selected
        WGR_set_prop_for_select('#data_parent');
    });
}
