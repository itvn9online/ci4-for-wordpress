var auto_hide_admin_custom_alert = null;

function HTV_alert(m, lnk) {
    return WGR_alert(m, lnk);
}

function WGR_alert(m, lnk) {
    if (typeof m == 'undefined') {
        m = '';
    }
    if (typeof lnk == 'undefined') {
        lnk = '';
    }
    //console.log(m);
    //console.log(lnk);

    //
    if (top != self) {
        top.WGR_alert(m, lnk);
    } else {
        if (m != '') {
            if ($('#admin_custom_alert').length > 0) {
                $('#admin_custom_alert').removeClass('orgbg').removeClass('redbg').html('<div>' + m + '</div>').show();
                if (lnk == 'error') {
                    $('#admin_custom_alert').addClass('redbg');
                } else if (lnk == 'warning') {
                    $('#admin_custom_alert').addClass('orgbg');
                }

                clearTimeout(auto_hide_admin_custom_alert);
                auto_hide_admin_custom_alert = setTimeout(function () {
                    $('#admin_custom_alert').fadeOut();
                }, 6000);
            } else {
                alert(m);
            }
        } else if (lnk != '') {
            HTV_redirect(lnk);
        }
    }

    //
    return false;
}

function HTV_redirect(l) {
    if (top != self) {
        top.HTV_redirect(l);
    } else if (typeof l != 'undefined' && l != '') {
        window.location = l;
    }
}

function WGR_show_try_catch_err(e) {
    return 'name: ' + e.name + '; line: ' + (e.lineNumber || e.line) + '; script: ' + (e.fileName || e.sourceURL || e.script) + '; stack: ' + (e.stackTrace || e.stack) + '; message: ' + e.message;
}

// khởi tạo slider từ mẫu menu
function HTV_menu_slider(for_id, ops, slider_ops) {
    if (jQuery('#' + for_id).length !== 1) {
        console.log('length for slider #' + for_id + ' == ' + jQuery('#' + for_id).length);
        return false;
    }

    //
    var str = '';

    // tạo object mặc định
    if (typeof ops != 'object') {
        ops = {};
    }

    // thông số mặc định của slider
    if (typeof slider_ops != 'object') {
        slider_ops = {
            centeredSlides: true,
            autoplay: {
                delay: 5000,
                disableOnInteraction: false
            },
            pagination: {
                el: '.swiper-test-pagination'
            }
        };
    }

    // class mặc định
    if (typeof ops['slider_class'] == 'undefined') {
        ops['slider_class'] = '';
    }

    // size mặc định
    if (typeof ops['size'] == 'undefined') {
        ops['size'] = jQuery('#' + for_id + ' .htv-text-slider').attr('data-size') || '';
        if (ops['size'] == '') {
            ops['size'] = '2/3';
        }
    }

    // -> kích slider
    var slider_width = $('#' + for_id).width();
    var slider_height = Math.ceil(slider_width * eval(ops['size'])) - 1;

    // lấy class đi kèm
    var htv_text_slider = jQuery('#' + for_id + ' .htv-text-slider').attr('class') || 'htv-text-slider';

    // lấy các ảnh trong slider
    jQuery('#' + for_id + ' img').each(function () {
        var url = $(this).attr('src') || '';
        //console.log(url);

        //
        str += '<div class="swiper-slide"><div class="' + ops['slider_class'] + '"> <img width="' + slider_width + '" height="' + slider_height + '" src="' + url + '"> </div></div>';

        var a_parent = $(this).parent().attr('href') || '';
        console.log(a_parent);
    });

    $('#' + for_id).html('<div class="swiper-wrapper">' + str + '</div> <div class="swiper-pagination"></div>').addClass(htv_text_slider);

    //
    var swiper = new Swiper('#' + for_id, slider_ops);
}


function WGR_show_or_hide_to_top() {
    var new_scroll_top = window.scrollY || jQuery(window).scrollTop();

    //
    if (new_scroll_top > 120) {
        jQuery('body').addClass('ebfixed-top-menu');

        //
        if (new_scroll_top > 500) {
            jQuery('body').addClass('ebshow-top-scroll');

            //
            _global_js_eb.ebBgLazzyLoad(new_scroll_top);
        } else {
            jQuery('body').removeClass('ebshow-top-scroll');
        }
    } else {
        jQuery('body').removeClass('ebfixed-top-menu').removeClass('ebshow-top-scroll');
    }
}


// set prop cho select
function WGR_set_prop_for_select(for_id) {
    $(for_id).each(function () {
        var a = $(this).attr('data-select') || '';

        // nếu có tham số này
        if (a != '' && !$(this).hasClass('set-selected')) {
            // select luôn dữ liệu tương ứng -> cắt theo dấu , -> vì có 1 số dữ liệu sẽ là multi select
            a = a.split(',');

            // select cho option đầu tiên
            $(this).val(a[0]).addClass('set-selected');

            // các option sau select kiểu prop
            if (a.length > 1) {
                for (var i = 0; i < a.length; i++) {
                    $('option[value="' + a[i] + '"]', this).prop('selected', true);
                }
            }
        }
    });
}
