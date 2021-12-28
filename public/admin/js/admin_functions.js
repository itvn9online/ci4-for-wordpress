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
        if (typeof add_img_tag == 'undefined') {
            add_img_tag = 0;
        }
        if (typeof img_size == 'undefined') {
            img_size = 'full';
        }
        if (typeof input_type == 'undefined') {
            input_type = 'text';
        }

        //
        $('body').append('<div class="hide-if-esc wgr-wp-upload"><iframe id="oi_wgr_wp_upload_iframe" name="oi_wgr_wp_upload_iframe" src="admin/uploads?post_type=file_upload&quick_upload=1&insert_to=' + insert_to + '&add_img_tag=' + add_img_tag + '&img_size=' + img_size + '&input_type=' + input_type + '" width="95%" height="' + ($(window).height() / 100 * 90) + '" frameborder="0">AJAX form</iframe></div>');
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
    if (typeof add_img_tag == 'undefined') {
        add_img_tag = 0;
    }
    if (typeof img_size == 'undefined') {
        img_size = 'full';
    }
    if (typeof input_type == 'undefined') {
        input_type = 'text';
    }

    //
    var str = [];
    //str.push(' <input type="button" class="btn btn-info" value="Chọn ảnh" onclick="BrowseServer( \'Images:/\', \'' + for_id.substr(1) + '\' );"/>');
    str.push(' <input type="button" class="btn btn-info add-image-' + for_id.replace(/\#|\./gi, '-') + '" value="Thêm ảnh" onclick="WgrWp_popup_upload( \'' + for_id.substr(1) + '\', ' + add_img_tag + ', \'' + img_size + '\', \'' + input_type + '\' );"/>');

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

        var data_size = img.attr('data-size') || 'full';
        if (data_size == '') {
            data_size = 'full';
        }
        var data_src = img.attr('data-' + data_size) || '';

        if (data_src == '') {
            data_src = img.attr('data-thumbnail') || '';
            if (data_src == '') {
                alert('Không xác định được URL của ảnh!');
                return false;
            }
        }
        var input_type = img.attr('data-input_type') || '';
        //console.log(input_type);
        if (input_type == 'textediter') {
            if (data_src.split('//').length == 1) {
                data_src = $('base').attr('href') + data_src;
            }
            top.tinymce.get(insert_to).insertContent('<img src="' + data_src + '" class="echbay-push-img" />');
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
            top.$('#' + insert_to).val(data_src);
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
