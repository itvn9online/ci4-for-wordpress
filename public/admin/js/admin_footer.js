/*
var aaaaaaaa = '';
$('#sidebar a').each(function () {
    var hr = $(this).attr('href') || '';
    aaaaaaaa += '\'' + hr + '\' => [ ' + "\n" + ' \'name\' => \'' + $.trim($(this).html()) + '\', ' + "\n" + ' \'arr\' => [] ' + "\n" + ' ],' + "\n";
});
console.log(aaaaaaaa);
*/

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

// tự động select khi có dữ liệu
WGR_set_prop_for_select('#content select');
WGR_set_prop_for_select('select.admin-change-language');

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

function click_a_delete_record() {
    return confirm('Xác nhận xóa bản ghi này?');
}

function click_a_restore_record() {
    return confirm('Xác nhận phục hồi bản ghi này?');
}

function click_a_remove_record() {
    return confirm('Xác nhận XÓA hoàn toàn bản ghi này?');
}

function click_delete_record() {
    if ($('#is_deleted').length !== 1) {
        console.log('%c ERROR is_deleted.length', 'color: red;');
    }

    if (click_a_delete_record() === false) {
        return false;
    }

    $('#is_deleted').val(1);
    document.admin_global_form.submit();

    // hủy lệnh nếu code có lỗi
    setTimeout(function () {
        $('#is_deleted').val(0);
    }, 600);
}


function click_duplicate_record() {
    if ($('#is_duplicate').length !== 1) {
        console.log('%c ERROR is_duplicate.length', 'color: red;');
    }

    if (confirm('Bạn thực sự muốn nhân bản bản ghi này?') === false) {
        return false;
    }

    $('#is_duplicate').val(1);
    document.admin_global_form.submit();

    // hủy lệnh nếu code có lỗi
    setTimeout(function () {
        $('#is_duplicate').val(0);
    }, 600);
}


// phần thiết lập thông số của size -> chỉnh về 1 định dạng
function convert_size_to_one_format() {
    jQuery('#post_meta_custom_size, #term_meta_custom_size, #data_cf_product_size, #data_cf_blog_size, #term_meta_taxonomy_custom_post_size, #data_main_banner_size, #data_second_banner_size').off('change').change(function () {
        var a = jQuery(this).val() || '';
        a = jQuery.trim(a);
        if (a != '') {
            // kích thước dùng chung
            if (a.split('%').length == 3) {
                //
            } else {
                a = a.replace(/\s/g, '');

                // kích thước tự động thì cũng bỏ qua luôn
                if (a == 'auto' || a == 'full') {
                    //
                } else {
                    // nếu có dấu x -> chuyển về định dạng của Cao/ Rộng
                    if (a.split('x').length > 1) {
                        a = a.split('x');

                        if (a[0] == a[1]) {
                            a = 1;
                        } else {
                            a = a[1] + '/' + a[0];
                        }
                    }
                    a = a.toString().replace(/[^0-9\/]/g, '');
                }
            }

            jQuery(this).val(a);
        }
    }).off('blur').blur(function () {
        jQuery(this).change();
    });


    jQuery('.fixed-width-for-config').off('change').change(function () {
        var a = jQuery(this).val() || '';
        if (a != '') {
            a = a.replace(/\s/g, '');

            if (a != '') {
                a = a * 1;

                // nếu giá trị nhập vào nhỏ hơn 10 -> tính toán tự động số sản phẩm trên hàng theo kích thước tiêu chuẩn
                if (a < 10) {
                    // lấy kích thước tiêu chuẩn
                    var b = jQuery(this).attr('data-width') || '';
                    if (b != '') {
                        // tính toán
                        jQuery(this).val(Math.ceil(b / a) - 5);
                    }
                }
            }
        }
    }).off('blur').blur(function () {
        jQuery(this).change();
    });
}
convert_size_to_one_format();

fix_textarea_height();


function hide_if_esc() {
    if (top != self) {
        return top.hide_if_esc();
    }

    //
    $('.hide-if-esc').hide();
    $('body').removeClass('no-scroll');

    //
    return false;
}


/*
setInterval(function () {
    (function (new_scroll_top) {
        if (new_scroll_top > 120) {
            jQuery('body').addClass('ebfixed-top-menu');

            //
            if (new_scroll_top > 500) {
                jQuery('body').addClass('ebshow-top-scroll');
            } else {
                jQuery('body').removeClass('ebshow-top-scroll');
            }
        } else {
            jQuery('body').removeClass('ebfixed-top-menu').removeClass('ebshow-top-scroll');
        }
    })(window.scrollY || jQuery(window).scrollTop());
}, 300);
*/


/**
 * Unicorn Admin Template
 * Diablo9983 -> diablo9983@gmail.com
 **/
$(document).ready(function () {

    //$('input[type=checkbox],input[type=radio],input[type=file]').uniform();

    $('select').each(function () {
        if ($('option', this).length > 10) {
            $(this).select2();
        }
    });
    //$('.colorpicker').colorpicker();
    //$('.datepicker').datepicker();

    //
    action_each_to_taxonomy();
    //action_data_img_src();
}).keydown(function (e) {
    //console.log(e.keyCode);

    //
    if (e.keyCode == 27) {
        hide_if_esc();
    }
});


/*
 * tạo menu select cho admin
 */
function add_active_class_for_sidebar(w) {
    w = $.trim(w);
    if (w == '') {
        return false;
    }
    console.log(w);
    if (w.substr(w.length - 1) == '&') {
        w = w.substr(0, w.length - 1);
        console.log(w);
    }

    //
    var has_active = false;
    $('#sidebar a').each(function () {
        var a = $(this).attr('href') || '';
        if (a != '') {
            if (w.split(a).length > 1) {
                console.log(a);
                $(this).parents('li').addClass('active');
                has_active = true;
            }
        }
    });

    //
    return has_active;
}

function get_last_url_segment(w) {
    // lấy phần tử cuối cùng trong URL
    var a = w.split('&support_tab=')[0].split('?support_tab=')[0];
    a = a.replace(web_link, '');
    /*
    a = a.split('/');
    if (a[a.length - 1] == '') {
        a = a[a.length - 2];
    } else {
        a = a[a.length - 1];
    }
    */
    a = g_func.non_mark_seo(a);
    //console.log('last w:', a);

    //
    return a;
}

// bắt đâu tạo actived cho admin menu
(function (w) {
    // tạo segment cho admin menu
    $('#sidebar a').each(function () {
        var a = $(this).attr('href') || '';
        if (a != '') {
            $(this).attr({
                'data-segment': get_last_url_segment(a),
            });
        }
    });
    // lấy segment hiện tại
    var last_w = get_last_url_segment(w);

    // so khớp với menu xem có không
    $('#sidebar a[data-segment="' + last_w + '"]').parents('li').addClass('active');

    // nếu có rồi thì không cần đoạn so khớp đằng sau nữa
    if ($('#sidebar li.active').length > 0) {
        console.log('active for admin menu by segment');
        return false;
    }

    //
    if (add_active_class_for_sidebar(w) === false) {
        var w2 = w.split('&');
        if (w2.length > 1) {
            w2[w2.length - 1] = '';
            w2 = w2.join('&');
        } else {
            w2 = w2[0];
        }

        //
        if (add_active_class_for_sidebar(w2) === false) {
            if (w2.split('/add?').length > 1) {
                if (add_active_class_for_sidebar(w2.replace('/add?', '/?')) === false) {
                    add_active_class_for_sidebar(w2.replace('/add?', '?'))
                }
            }
        }
    }
})(window.location.href);


/*
 * Chuyển định dạng select date sang jquery-ui
 */
$('input[type="date"]').addClass('ebe-jquery-ui-date').attr({
    'type': 'text'
});
_global_js_eb.select_date('.ebe-jquery-ui-date');


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
$('.admin-change-language').change(function () {
    var a = $(this).val() || '';

    //
    if (a != '') {
        //console.log(a);

        window.location = web_link + '/?set_lang=' + a + '&redirect_to=' + encodeURIComponent(window.location.href);
    }
});
