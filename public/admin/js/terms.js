/*
 * sau khi XÓA sản phẩm thành công thì xử lý ẩn bản ghi bằng javascript
 */
function done_delete_restore(id) {
    $('#admin_main_list tr[data-id="' + id + '"]').fadeOut();
}

// do sử dụng aguilarjs đang không tạo được dnah mục theo dạng đệ quy -> tự viết function riêng vậy
function term_tree_view(data, tmp, gach_ngang) {
    if (data.length <= 0) {
        return false;
    }
    if (typeof gach_ngang == 'undefined') {
        gach_ngang = '';
    }
    //console.log('gach ngang:', gach_ngang);

    //
    var str = '';
    var arr = null;
    for (var i = 0; i < data.length; i++) {
        //console.log(data[i]);

        //
        if (data[i].parent > 0) {
            str = tmp;

            //
            for (var j = 0; j < 10; j++) {
                str = str.replace('{{v.gach_ngang}}', gach_ngang);
                str = str.replace('{{controller_slug}}', controller_slug);
                str = str.replace('{{for_action}}', for_action);
            }

            //
            arr = data[i];
            for (var x in arr) {
                //console.log(typeof arr[x], arr[x]);
                if (typeof arr[x] != 'object') {
                    for (var j = 0; j < 10; j++) {
                        str = str.replace('{{v.' + x + '}}', arr[x]);
                    }
                } else {
                    //
                }
            }

            //
            //console.log(str);
            $('.each-to-child-term[data-id="' + arr.parent + '"]').after(str);
        }

        //
        //console.log(data[i].child_term.length);
        if (data[i].child_term.length > 0) {
            term_tree_view(data[i].child_term, tmp, gach_ngang + '&#8212; ');
        }
    }
}

function before_tree_view(tmp, max_i) {
    if (typeof max_i != 'number') {
        max_i = 50;
    } else if (max_i < 0) {
        return false;
    }

    // chờ khi aguilar nạp xong html thì mới nạp tree view
    if ($('#admin_main_list tr.ng-scope').length == 0) {
        setTimeout(function () {
            before_tree_view(tmp, max_i - 1);
        }, 100);

        //
        return false;
    }

    //
    term_tree_view(term_data, tmp);
    $('.this-child-term div[ng-if]').remove();
}

(function () {
    if (term_data.length <= 0) {
        return false;
    }

    //
    var tmp = $('#admin_main_list tr:first').html() || '';
    if (tmp == '') {
        return false;
    }
    tmp = '<tr data-id="{{v.term_id}}" class="each-to-child-term this-child-term">' + tmp + '</tr>';
    //console.log(tmp);

    //
    before_tree_view(tmp);
})();
