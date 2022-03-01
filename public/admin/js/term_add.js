//console.log(arr_list_category);

/*
 * tạo các option con cho phần select Danh mục cha
 */
if ($('#data_parent').length > 0) {
    $('#data_parent').append(create_term_select_option(arr_list_category));

    //
    $('#data_parent option[value="' + data_term_id + '"]').prop('disabled', true);
}
