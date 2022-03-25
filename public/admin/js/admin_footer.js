/*
var aaaaaaaa = '';
$('#sidebar a').each(function () {
    var hr = $(this).attr('href') || '';
    aaaaaaaa += '\'' + hr + '\' => [ ' + "\n" + ' \'name\' => \'' + $.trim($(this).html()) + '\', ' + "\n" + ' \'arr\' => [] ' + "\n" + ' ],' + "\n";
});
console.log(aaaaaaaa);
*/

//
(function (arr) {
    var str = '';
    for (var x in arr) {
        if (arr[x] === null) {
            continue;
        }

        // tạo icon
        if (typeof arr[x].icon == 'undefined' || arr[x].icon == '') {
            arr[x].icon = 'fa fa-caret-right';
        }
        arr[x].icon = '<i class="' + arr[x].icon + '"></i>';

        //
        str += '<li style="order: ' + arr[x].order + '"><a href="' + x + '">' + arr[x].icon + arr[x].name + '</a>';

        //
        //console.log(arr[x]);
        if (arr[x].arr !== null) {
            str += '<ul class="sub-menu">';

            //
            var v_sub = arr[x].arr;
            for (var k_sub in v_sub) {
                // tạo icon
                if (typeof v_sub[k_sub].icon == 'undefined' || v_sub[k_sub].icon == '') {
                    v_sub[k_sub].icon = 'fa fa-caret-right';
                }
                v_sub[k_sub].icon = '<i class="' + v_sub[k_sub].icon + '"></i>';

                //
                str += '<li><a href="' + k_sub + '">' + v_sub[k_sub].icon + v_sub[k_sub].name + '</a></li>';
            }

            //
            str += '</ul>';
        }

        //
        str += '</li>';
    }

    //
    $('#sidebar ul').html(str);
})(arr_admin_menu);

// chỉnh lại chiều cao cho textediter nếu có
$('.auto-ckeditor').each(function () {
    var h = $(this).attr('data-height') || '';
    var jd = $(this).attr('id') || '';

    if (h != '' && jd != '') {
        WGR_load_textediter('#' + jd, {
            height: h * 1
        });
        /*
        CKEDITOR.replace(jd, {
            height: h * 1
        });
        */
    } else {
        console.log('%c auto-ckeditor not has attr data-height or id', 'color: red;');
    }
});

// tạo breadcrumb theo từng module riêng biệt
if ($('.admin-breadcrumb').length > 0) {
    $('#breadcrumb ul').append($('.admin-breadcrumb').html());

    // sửa lại title cho admin
    (function (str) {
        document.title = str + ' | ' + document.title;
    })($('#breadcrumb li:last-child a').html() || $('#breadcrumb li:last-child ').html());
}

// tự động checkbox khi có dữ liệu
$('#content input[type="checkbox"]').each(function () {
    var a = $(this).attr('data-value') || '';
    //console.log(a);

    // nếu có tham số này
    if (a != '') {
        var v = $(this).val();

        if (a == v) {
            $(this).prop('checked', true);
        }
    }
});

//
convert_size_to_one_format();
fix_textarea_height();

// bắt đâu tạo actived cho admin menu
(function (w) {
    console.log(w);
    w = w.replace(web_link, '');
    w = w.split('&support_tab=')[0].split('?support_tab=')[0];
    console.log(w);

    // tạo segment cho admin menu
    $('#sidebar a').each(function () {
        var a = $(this).attr('href') || '';
        if (a != '') {
            //console.log(a);
            a = a.replace(web_link, '');
            //console.log(a);
            $(this).attr({
                'data-segment': get_last_url_segment(a),
            });
        }
    });

    // so khớp với menu xem có không
    if (set_last_url_segment(get_last_url_segment(w)) === true) {
        return false;
    }
    // thử bỏ dấu ? nếu có
    w = w.split('?')[0];
    if (set_last_url_segment(get_last_url_segment(w)) === true) {
        return false;
    }

    // cắt bớt đi để so khớp tiếp
    for (var i = 0; i < 10; i++) {
        w = remove_last_url_segment(w);
        console.log(w);
        if (w == '' || w == 'admin') {
            break;
        }
        if (set_last_url_segment(get_last_url_segment(w)) === true) {
            break;
        }
    }
})(window.location.href);


/*
 * duy trì đăng nhập đối với tài khoản admin (tầm 4 tiếng -> tương ứng với 1 ca làm việc)
 */
//WGR_duy_tri_dang_nhap(4 * 60);
setInterval(function () {
    document.getElementById('target_eb_iframe').src = web_link + 'admin/admin/admin_logged';
}, 10 * 60 * 1000);


/*
 * thay đổi ngôn ngữ trong admin
 */
(function () {
    var str = '';
    for (var x in arr_lang_list) {
        str += '<option value="' + x + '">' + arr_lang_list[x] + '</option>';
    }
    $('.admin-change-language').html(str);
})();
$('.admin-change-language').change(function () {
    var a = $(this).val() || '';

    //
    if (a != '') {
        //console.log(a);

        window.location = web_link + '/?set_lang=' + a + '&redirect_to=' + encodeURIComponent(window.location.href);
    }
});


//
$(document).ready(function () {
    // tự động select khi có dữ liệu
    WGR_set_prop_for_select('#content select');
    WGR_set_prop_for_select('select.admin-change-language');

    //
    //$('input[type=checkbox],input[type=radio],input[type=file]').uniform();

    // kích hoạt select2 khi lượng option đủ lớn
    $('select').each(function () {
        if ($('option', this).length > 10) {
            $(this).select2();
        }
    });
    //$('.colorpicker').colorpicker();
    //$('.datepicker').datepicker();

    //
    action_each_to_taxonomy();
    each_to_page_part();
    //action_data_img_src();
    action_for_check_checked_all();
}).keydown(function (e) {
    //console.log(e.keyCode);

    //
    if (e.keyCode == 27) {
        hide_if_esc();
    }
});
