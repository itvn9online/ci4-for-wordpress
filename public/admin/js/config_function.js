/*
 * lấy các input có sự kiện change -> để tránh việc update tùm lum
 */
var list_field_has_change = {};

function get_field_has_change(a) {
    //console.log(a);

    // chỉ thực thi với phần data
    if (a != '' && a.split('data[').length > 1) {
        a = a.replace('data[', '').split(']')[0];
        //console.log(a);

        // đủ điều kiện thì xác thực cho phép update
        if (typeof list_field_has_change[a] == 'undefined') {
            //console.log(a);
            list_field_has_change[a] = 1;
            $('#list_field_has_change').val(JSON.stringify(list_field_has_change));
        }
    }
}

function done_field_has_change() {
    $('#list_field_has_change').val('');
    list_field_has_change = {};
}
