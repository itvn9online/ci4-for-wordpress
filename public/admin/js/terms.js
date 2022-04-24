/*
 * sau khi XÓA sản phẩm thành công thì xử lý ẩn bản ghi bằng javascript
 */
function done_delete_restore(id) {
    $('#admin_main_list tr[data-id="' + id + '"]').fadeOut();
}

//
function action_delete_restore_checked_term(method_control, method_name) {
    if (confirm('Xác nhận ' + method_name + ' các bản ghi đã chọn!') !== true) {
        return false;
    }
    //console.log(arr_check_checked_all);

    //
    jQuery.ajax({
        type: 'POST',
        url: 'admin/terms/' + method_control,
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

function click_delete_checked_term() {
    action_delete_restore_checked_term('delete_all', 'Lưu trữ');
}

function click_restore_checked_term() {
    action_delete_restore_checked_term('restore_all', 'Khôi phục');
}

function click_remove_checked_term() {
    action_delete_restore_checked_term('remove_all', 'XÓA');
}

// do sử dụng aguilarjs đang không tạo được danh mục theo dạng đệ quy -> tự viết function riêng vậy
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

function tmp_to_term_html(data, tmp, gach_ngang) {
    if (typeof gach_ngang == 'undefined') {
        gach_ngang = '';
    }

    //
    var str = tmp;
    for (var j = 0; j < 10; j++) {
        str = str.replace('{{v.gach_ngang}}', gach_ngang);
    }

    //
    var arr = data;
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
    return str;
}

function term_v2_tree_view(tmp, term_id, gach_ngang) {
    // lần đầu thì lấy nhóm cấp 1 trước
    if (typeof term_id == 'undefined') {
        $('#admin_main_list').text('');
        term_id = 0;
        gach_ngang = '';
    } else {
        term_id *= 1;
    }
    //console.log('gach ngang:', gach_ngang);
    //console.log('term id:', term_id);
    //console.log('term_data:', term_data);

    //
    //var has_term = false;
    for (var i = 0; i < term_data.length; i++) {
        // không lấy các phần tử đã được set null
        if (term_data[i] === null) {
            continue;
        }
        // chỉ lấy những phần tử có parent trùng với dữ liệu truyền vào
        else if (term_data[i].parent * 1 !== term_id) {
            continue;
        }
        //console.log(term_data[i]);
        //has_term = true;

        // hiển thị nhóm hiện tại ra
        $('#admin_main_list').append(tmp_to_term_html(term_data[i], tmp, gach_ngang));

        // nạp nhóm con luôn và ngay
        term_v2_tree_view(tmp, term_data[i].term_id, gach_ngang + '&#8212; ');

        //
        term_data[i] = null;
    }
    //if (term_id <= 0) console.log('has term:', has_term);
}

// tìm term cha của 1 term để xem cha của nó có tồn tại trong danh sách này không
function check_term_parent_by_id(parent_id) {
    var has_parent = false;
    // chạy vòng lặp để tìm xem 1 term có cha ở cùng trang không
    for (var j = 0; j < term_data.length; j++) {
        if (term_data[j] !== null && term_data[j].term_id * 1 === parent_id * 1) {
            // nếu có thì thử xem thằng cha này có cha không -> ông
            var has_granfather = check_term_parent_by_id(term_data[j].parent);
            //console.log('has granfather:', has_granfather);

            // nếu có ông, cụ, kị.... thì lấy phần tử đó
            if (has_granfather !== false) {
                has_parent = has_granfather;
            }
            // không thì trả về phần tử hiện tại luôn
            else {
                has_parent = j;
            }

            //
            break;
        }
    }
    return has_parent;
}

// hiển thị các term chưa bị null
function term_not_null_tree_view(tmp, gach_ngang) {
    if (typeof gach_ngang == 'undefined') {
        // các nhóm ở đây không phải nhóm cấp 1 -> nên để ít nhất 1 gạch ngang
        //gach_ngang = '| ';
        gach_ngang = '';
    }
    //console.log('gach ngang:', gach_ngang);
    //console.log('term_data:', term_data);

    //
    for (var i = 0; i < term_data.length; i++) {
        // không lấy các phần tử đã được set null
        if (term_data[i] === null) {
            continue;
        }
        //console.log(term_data[i]);

        // thử xem nhóm này có đang là nhóm con của nhóm nào trong đây không
        var j = check_term_parent_by_id(term_data[i].parent);
        // tìm thấy cha thì in nhóm cha trước rồi mới in nhóm con
        if (j !== false) {
            $('#admin_main_list').append(tmp_to_term_html(term_data[j], tmp, gach_ngang));

            // nạp nhóm con luôn và ngay
            term_v2_tree_view(tmp, term_data[j].term_id, gach_ngang + '&#8212; ');

            term_data[j] = null;
        }
        // không thấy cha thì in trực tiếp nó ra thôi
        else {
            $('#admin_main_list').append(tmp_to_term_html(term_data[i], tmp, gach_ngang));

            // nạp nhóm con luôn và ngay
            term_v2_tree_view(tmp, term_data[i].term_id, gach_ngang + '&#8212; ');
        }

        //
        term_data[i] = null;
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
    //$('.this-child-term div[v-if]').remove();
}

(function () {
    if (term_data.length <= 0) {
        // không có dữ liệu thì xóa template đi
        $('#admin_main_list').text('');
        return false;
    }

    //
    var tmp = $('#admin_main_list tr:first').html() || '';
    $('#admin_main_list').text('');
    if (tmp == '') {
        return false;
    }
    for (var i = 0; i < 10; i++) {
        tmp = tmp.replace('{{DeletedStatus_DELETED}}', DeletedStatus_DELETED);
        tmp = tmp.replace('{{for_action}}', for_action);
        tmp = tmp.replace('{{controller_slug}}', controller_slug);
    }
    tmp = '<tr data-id="{{v.term_id}}" class="each-to-child-term this-child-term">' + tmp + '</tr>';
    //console.log(tmp);

    /*
     * phiên bản sử dụng aguilar js
     */
    //before_tree_view(tmp);

    /*
     * phiên bản sử dụng js thuần
     */
    term_v2_tree_view(tmp);
    //console.log('term data:', term_data);

    // chạy rà soát lại những nhóm chưa được xác định -> not null
    term_not_null_tree_view(tmp);
    //console.log('term data:', term_data);
})();


/*
 * thay đổi số thứ tự của term
 */
$('.change-update-term_order').attr({
    type: 'number'
}).change(function () {
    var a = $(this).attr('data-id') || '';
    if (a != '') {
        var v = $(this).val();
        v *= 1;
        if (v <= 0) {
            v = 0;
        }

        //
        /*
        jQuery.ajax({
            type: 'POST',
            url: web_link + 'ajax/get_taxonomy_by_taxonomy',
            dataType: 'json',
            data: {
                taxonomy: a,
            },
            success: function (data) {
                console.log(data);
            }
        });
        */
    }
});
