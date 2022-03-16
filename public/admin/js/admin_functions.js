function WGR_widget_add_custom_style_to_field() {
    jQuery('.click_add_widget_class').off('click').click(function () {
        var a = jQuery(this).attr('data-value') || '',
            cl = 0;

        if (a != '') {
            var b = jQuery('#term_meta_custom_style').val() || '';

            var c = '';
            if (b == '') {
                c = a;
                cl = 1;
            } else {
                // tạo khoảng trắng 2 đầu để còn kiểm tra dữ liệu đã add rồi hay chưa
                b = ' ' + jQuery.trim(b) + ' ';

                // xóa
                if (b.split(' ' + a + ' ').length > 1) {
                    c = b.replace(' ' + a + ' ', '');
                }
                // thêm
                else {
                    c = a + b;
                    cl = 1;
                }
            }
            jQuery('#term_meta_custom_style').val(jQuery.trim(c)).change();

            // tạo hiệu ứng thay đổi để người dùng dễ nhìn
            if (cl === 1) {
                jQuery('i.fa', this).removeClass('fa-minus-square').addClass('fa-check-square');
            } else {
                jQuery('i.fa', this).addClass('fa-minus-square').removeClass('fa-check-square');
            }
        }

        return false;
    });

    //
    var a = $('#term_meta_custom_style').val() || '';

    if (a != '') {
        a = a.split(' ');

        for (var i = 0; i < a.length; i++) {
            jQuery('.click_add_widget_class[data-value="' + a[i] + '"] i.fa').removeClass('fa-minus-square').addClass('fa-check-square');
        }
    }
}

function fix_textarea_height() {
    jQuery('.fix-textarea-height textarea, textarea.fix-textarea-height').off('change').change(function () {
        var a = jQuery(this).attr('data-resize') || '',
            min_height = jQuery(this).attr('data-min-height') || 60,
            add_height = jQuery(this).attr('data-add-height') || 20;
        //		console.log(min_height);

        if (a == '') {
            jQuery(this).height(20);

            //
            var new_height = jQuery(this).get(0).scrollHeight || 0;
            new_height -= 0 - add_height;
            if (new_height < min_height) {
                new_height = min_height;
            }

            //
            jQuery(this).height(new_height);

            //
            console.log('Fix textarea height #' + (jQuery(this).attr('name') || jQuery(this).attr('id') || 'NULL'));
        }
    }).off('click').click(function () {
        jQuery(this).change();
    }).each(function () {
        jQuery(this).change();
    });
}


var current_textediter_insert_to = '';

function WgrWp_popup_upload(insert_to, add_img_tag, img_size, input_type) {
    if (current_textediter_insert_to != insert_to && $('oi_wgr_wp_upload_iframe').length == 0) {
        current_textediter_insert_to = insert_to;

        //
        if (typeof add_img_tag == 'undefined' || add_img_tag == '') {
            add_img_tag = 0;
        }
        if (typeof img_size == 'undefined') {
            //img_size = 'full';
            img_size = 'large';
        }
        if (typeof input_type == 'undefined') {
            input_type = 'text';
        }

        //
        $('body').append('<div class="hide-if-esc wgr-wp-upload"><iframe id="oi_wgr_wp_upload_iframe" name="oi_wgr_wp_upload_iframe" src="admin/uploads?quick_upload=1&insert_to=' + insert_to + '&add_img_tag=' + add_img_tag + '&img_size=' + img_size + '&input_type=' + input_type + '" width="95%" height="' + ($(window).height() / 100 * 90) + '" frameborder="0">AJAX form</iframe></div>');
    }


    //
    $('body').addClass('no-scroll');
    $('.wgr-wp-upload').show();
}

// nạp ảnh đại diện cho các input
function add_and_show_post_avt(for_id, add_img_tag, img_size, input_type) {
    if ($(for_id).length != 1) {
        console.log(for_id + ' not found! (length != 1)');
        return false;
    }
    if (typeof add_img_tag == 'undefined' || add_img_tag == '') {
        add_img_tag = 0;
    }
    if (typeof img_size == 'undefined') {
        //img_size = 'full';
        img_size = 'large';
    }
    if (typeof input_type == 'undefined') {
        input_type = 'text';
    }

    //
    var str = [];
    //str.push(' <input type="button" class="btn btn-info" value="Chọn ảnh" onclick="BrowseServer( \'Images:/\', \'' + for_id.substr(1) + '\' );"/>');
    str.push(' <button type="button" class="btn btn-info add-image-' + for_id.replace(/\#|\./gi, '-') + '" onclick="WgrWp_popup_upload( \'' + for_id.substr(1) + '\', ' + add_img_tag + ', \'' + img_size + '\', \'' + input_type + '\' );">Thêm ảnh</button>');

    //
    $('.for-' + for_id).remove();

    //
    if (input_type != 'textediter') {
        var img = $(for_id).val() || '';
        if (img != '') {
            str.push('<p class="show-img-if-change for-' + for_id.substr(1) + '"><img src="' + img + '" class="control-group-avt" /></p>');
        }
    }

    //
    $(for_id).after(str.join(' '));

}

function click_set_img_for_input(img_id) {
    var img = $('.media-attachment-img[data-id="' + img_id + '"]');
    insert_to = img.attr('data-insert') || '';

    //
    if (insert_to == '') {
        return false;
    }

    //console.log(insert_to);
    /*
    if (top.$('#' + insert_to).length === 1) {
        insert_to = '#' + insert_to;
    } else if (top.$('.' + insert_to).length === 1) {
        insert_to = '.' + insert_to;
    } else {
        insert_to = '';
    }
    */
    //console.log(insert_to);

    //
    if (insert_to != '') {
        var add_img_tag = img.attr('data-add_img_tag') || '';
        add_img_tag *= 1;

        //var data_size = img.attr('data-size') || 'full';
        var data_size = img.attr('data-size') || 'large';
        if (data_size == '') {
            //data_size = 'full';
            data_size = 'large';
        }
        var data_src = img.attr('data-' + data_size) || '';

        // lấy các thuộc tính của ảnh -> tối ưu SEO
        var img_attr = [];
        var data_srcset = img.attr('data-srcset') || '';
        if (data_srcset != '') {
            img_attr.push('data-to-srcset="' + data_srcset + '"');
        }
        var data_sizes = img.attr('data-sizes') || '';
        if (data_sizes != '') {
            img_attr.push('sizes="' + data_sizes + '"');
        }
        var data_width = img.attr('data-width') || '';
        if (data_width != '') {
            img_attr.push('width="' + data_width + '"');
        }
        var data_height = img.attr('data-height') || '';
        if (data_height != '') {
            img_attr.push('height="' + data_height + '"');
        }

        if (data_src == '') {
            data_src = img.attr('data-thumbnail') || '';
            if (data_src == '') {
                alert('Không xác định được URL của ảnh!');
                return false;
            }
        }
        var input_type = img.attr('data-input_type') || '';
        //console.log(input_type);
        // insert ảnh vào text area
        if (input_type == 'textediter') {
            if (data_src.split('//').length == 1) {
                data_src = $('base').attr('href') + data_src;
            }
            top.tinymce.get(insert_to).insertContent('<img src="' + data_src + '"' + img_attr.join(' ') + ' class="echbay-push-img" />');
        } else {
            // thay ảnh hiển thị
            //console.log('.show-img-if-change.for-' + insert_to);
            //console.log('.show-img-if-change.for-' + data_src);
            //console.log(top.$('.show-img-if-change.for-' + insert_to + ' img').length);
            top.$('.show-img-if-change.for-' + insert_to + ' img').attr({
                'src': data_src
            });

            //
            if (add_img_tag === 1) {
                data_src = '<img src="' + data_src + '" />';
            }
            top.$('#' + insert_to).val(data_src).trigger('focus');
        }
    }
    hide_if_esc();
}

function WGR_load_textediter(for_id, ops) {
    if (typeof ops == 'undefined') {
        ops = {};
    }
    if (typeof ops['height'] == 'undefined') {
        ops['height'] = 250;
    }
    if (typeof ops['plugins'] == 'undefined') {
        ops['plugins'] = [
            'advlist autolink lists link image imagetools charmap print preview anchor',
            'searchreplace visualblocks code fullscreen',
            'insertdatetime media table paste code help wordcount'
        ];
    }
    if (typeof ops['toolbar'] == 'undefined') {
        ops['toolbar'] = 'undo redo | formatselect | '
            + 'bold italic backcolor | alignleft aligncenter '
            + 'alignright alignjustify | bullist numlist outdent indent | image | '
            + 'link table | '
            + 'removeformat code | help';
    }

    //
    tinymce.init({
        /*
        editor_encoding: "raw",
        apply_source_formatting: true,
        encoding: 'html',
        allow_html_in_named_anchor: true,
        element_format: 'xhtml',
        */
        selector: 'textarea' + for_id,
        height: ops['height'],
        //menubar: false,
        plugins: ops['plugins'],
        //a11y_advanced_options: true,
        //
        image_title: true,
        image_caption: true,
        image_advtab: true,
        //imagetools_toolbar: "rotateleft rotateright | flipv fliph | editimage imageoptions",
        // rel cho thẻ A
        rel_list: [{
            title: 'None',
            value: ''
        }, {
            title: 'No Referrer',
            value: 'noreferrer'
        }, {
            title: 'No Follow',
            value: 'nofollow'
            /*
		}, {
			title: 'No Opener',
			value: 'noopener'
			*/
        }, {
            title: 'External Link',
            value: 'external'
        }],
        //
        toolbar: ops['toolbar'],
        setup: function (ed) {
            // sự kiện khi khi nhấp đúp chuột
            ed.on('DblClick', function (e) {
                //console.log(e.target.nodeName);
                // nếu là hình ảnh -> mở hộp thoại sửa ảnh
                if (e.target.nodeName == 'IMG') {
                    tinymce.activeEditor.execCommand('mceImage');
                }
                // nếu là URL -> mở hộp chỉnh sửa URL
                else if (e.target.nodeName == 'A') {
                    tinymce.activeEditor.execCommand('mceLink');
                }
            });
        },
        //content_style: 'body { font-family:Helvetica,Arial,sans-serif; font-size:14px }'
    });

    //
    add_and_show_post_avt(for_id, 1, '', 'textediter');
}

// gán src cho thẻ img từ data-img -> dùng cho angularjs
function action_data_img_src() {
    $('.each-to-img-src').each(function () {
        var a = $(this).attr('data-src') || '';
        if (a != '') {
            $(this).attr({
                'src': a
            });
        }
    });
}

function click_a_delete_record() {
    return confirm('Xác nhận xóa bản ghi này?');
}

function click_a_restore_record() {
    return true;
    //return confirm('Xác nhận phục hồi bản ghi này?');
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


//
var loading_term_select_option = {};
var arr_all_taxonomy = {};

function load_term_select_option(a, jd, _callBack, max_i) {
    //console.log(a);
    //console.log(arr_all_taxonomy);

    // nếu term này được nạp rồi thì chờ đợi
    if (typeof loading_term_select_option[a] != 'undefined') {
        if (typeof max_i != 'number') {
            max_i = 100;
        } else if (max_i < 0) {
            console.log('%c max_i in load_term_select_option', 'color: red;');
            return false;
        }

        //
        if (typeof arr_all_taxonomy[a] != 'undefined') {
            console.log('%c using arr_all_taxonomy', 'color: blue;');
            _callBack(arr_all_taxonomy[a], jd);
            return false;
        }

        //
        setTimeout(function () {
            return load_term_select_option(a, jd, _callBack, max_i - 1);
        }, 500);
        return false;
    }
    loading_term_select_option[a] = true;

    //
    jQuery.ajax({
        type: 'POST',
        url: 'admin/ajax/get_taxonomy_by_taxonomy',
        dataType: 'json',
        //crossDomain: true,
        data: {
            taxonomy: a,
            //jd: jd,
        },
        timeout: 33 * 1000,
        error: function (jqXHR, textStatus, errorThrown) {
            console.log(jqXHR);
            if (typeof jqXHR.responseText != 'undefined') {
                console.log(jqXHR.responseText);
            }
            console.log(errorThrown);
            console.log(textStatus);
            if (textStatus === 'timeout') {
                //
            }
        },
        success: function (data) {
            //console.log(data);
            //console.log(data.length);

            //
            if (typeof data.error != 'undefined') {
                console.log('%c ' + data.error, 'color: red;');
            } else {
                arr_all_taxonomy[a] = data;

                //
                if (typeof _callBack == 'function') {
                    _callBack(data, jd);
                } else {
                    console.log(data);
                }
                //console.log('arr_all_taxonomy:', arr_all_taxonomy);
            }
        }
    });
}

// tạo danh sách option cho các select term
function create_term_select_option(arr, space) {
    //console.log('Call in: ' + arguments.callee.caller.name.toString());
    //console.log(arr);

    //
    if (typeof space == 'undefined') {
        space = '';
    }

    //
    var str = '';
    for (var i = 0; i < arr.length; i++) {
        str += '<option value="' + arr[i].term_id + '">' + space + arr[i].name + '</option>';

        //
        if (arr[i].child_term.length > 0) {
            str += create_term_select_option(arr[i].child_term, '&#160 &#160 ' + space);
        }
    }
    //console.log(str);

    //
    return str;
}


/*
 * chức năng select all user và chỉnh sửa nhanh
 */
var arr_check_checked_all = [];

function get_check_checked_all_value() {
    arr_check_checked_all = [];
    $('.input-checkbox-control').each(function () {
        if ($(this).is(':checked')) {
            arr_check_checked_all.push($(this).val());
        }
    });
    console.log(arr_check_checked_all);

    //
    if (arr_check_checked_all.length > 0) {
        $('.quick-edit-form').fadeIn();
    } else {
        $('.quick-edit-form').fadeOut();
    }
}

//
function action_for_check_checked_all() {
    $('.input-checkbox-all').change(function () {
        // checked cho tất cả select liên quan
        $('.input-checkbox-control').prop('checked', $(this).is(':checked'));
        get_check_checked_all_value();
    });
    //$('.input-checkbox-all').prop('checked', true).trigger('change');

    // select từng input
    $('.input-checkbox-control').change(function () {
        get_check_checked_all_value();
    });
}
