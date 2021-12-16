//console.log( typeof jQuery );
//console.log( typeof $ );
if (typeof $ == 'undefined') {
    $ = jQuery;
}


var bg_load = 'Loading...',
    //	ctimeout = null,
    // tỉ lệ tiêu chuẩn của video youtube -> lấy trên youtube
    //youtube_video_default_size = 315 / 560,
    youtube_video_default_size = 9 / 16,
    //	youtube_video_default_size = 480/ 854,
    // tên miền chính sử dụng code này
    primary_domain_usage_eb = '',
    disable_eblazzy_load = false,
    height_for_lazzy_load = 0,
    sb_submit_cart_disabled = 0,
    ebe_arr_cart_product_list = [],
    ebe_arr_cart_customer_info = [],
    arr_ti_le_global = {};

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


//
var current_croll_up_or_down = 0;

function WGR_show_or_hide_to_top() {
    var new_scroll_top = window.scrollY || jQuery(window).scrollTop();

    //
    if (new_scroll_top > 120) {
        jQuery('body').addClass('ebfixed-top-menu');

        // xác định hướng cuộn chuột lên hay xuống
        if (current_croll_up_or_down > new_scroll_top) {
            jQuery('body').addClass('ebfixed-up-menu').removeClass('ebfixed-down-menu');
        } else if (current_croll_up_or_down < new_scroll_top) {
            jQuery('body').addClass('ebfixed-down-menu').removeClass('ebfixed-up-menu');
        }
        current_croll_up_or_down = new_scroll_top;

        //
        if (new_scroll_top > 500) {
            jQuery('body').addClass('ebshow-top-scroll');

            //
            _global_js_eb.ebBgLazzyLoad(new_scroll_top);
        } else {
            jQuery('body').removeClass('ebshow-top-scroll');
        }
    } else {
        jQuery('body').removeClass('ebfixed-top-menu').removeClass('ebfixed-up-menu').removeClass('ebfixed-down-menu').removeClass('ebshow-top-scroll');
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


var g_func = {
    non_mark: function (str) {
        str = str.toLowerCase();
        str = str.replace(/\u00e0|\u00e1|\u1ea1|\u1ea3|\u00e3|\u00e2|\u1ea7|\u1ea5|\u1ead|\u1ea9|\u1eab|\u0103|\u1eb1|\u1eaf|\u1eb7|\u1eb3|\u1eb5/g, "a");
        str = str.replace(/\u00e8|\u00e9|\u1eb9|\u1ebb|\u1ebd|\u00ea|\u1ec1|\u1ebf|\u1ec7|\u1ec3|\u1ec5/g, "e");
        str = str.replace(/\u00ec|\u00ed|\u1ecb|\u1ec9|\u0129/g, "i");
        str = str.replace(/\u00f2|\u00f3|\u1ecd|\u1ecf|\u00f5|\u00f4|\u1ed3|\u1ed1|\u1ed9|\u1ed5|\u1ed7|\u01a1|\u1edd|\u1edb|\u1ee3|\u1edf|\u1ee1/g, "o");
        str = str.replace(/\u00f9|\u00fa|\u1ee5|\u1ee7|\u0169|\u01b0|\u1eeb|\u1ee9|\u1ef1|\u1eed|\u1eef/g, "u");
        str = str.replace(/\u1ef3|\u00fd|\u1ef5|\u1ef7|\u1ef9/g, "y");
        str = str.replace(/\u0111/g, "d");
        return str;
    },
    non_mark_seo: function (str) {
        str = this.non_mark(str);
        str = str.replace(/\s/g, "-");
        str = str.replace(/!|@|%|\^|\*|\(|\)|\+|\=|\<|\>|\?|\/|,|\.|\:|\;|\'|\"|\&|\#|\[|\]|~|$|_/g, "");
        str = str.replace(/-+-/g, "-");
        str = str.replace(/^\-+|\-+$/g, "");
        for (var i = 0; i < 5; i++) {
            str = str.replace(/--/g, '-');
        }
        str = (function (s) {
            var str = '',
                re = /^\w+$/,
                t = '';
            for (var i = 0; i < s.length; i++) {
                t = s.substr(i, 1);
                if (t == '-' || t == '+' || re.test(t) == true) {
                    str += t;
                }
            }
            return str;
        })(str);
        return str;
    },
    strip_tags: function (input, allowed) {
        if (typeof input == 'undefined' || input == '') {
            return '';
        }

        //
        if (typeof allowed == 'undefined') {
            allowed = '';
        }

        //
        allowed = (((allowed || "") + "").toLowerCase().match(/<[a-z][a-z0-9]*>/g) || []).join('');
        var tags = /<\/?([a-z][a-z0-9]*)\b[^>]*>/gi,
            cm = /<!--[\s\S]*?-->|<\?(?:php)?[\s\S]*?\?>/gi;
        return input.replace(cm, '').replace(tags, function ($0, $1) {
            return allowed.indexOf('<' + $1.toLowerCase() + '>') > -1 ? $0 : '';
        });
    },
    trim: function (str) {
        return jQuery.trim(str);
        //		return str.replace(/^\s+|\s+$/g, "");
    },

    setc: function (name, value, seconds, days, set_domain) {
        var expires = "";

        // tính theo ngày -> số giây trong ngày luôn
        if (typeof days == 'number' && days > 0) {
            seconds = days * 24 * 3600;
        } else {
            days = 0;
        }

        //
        //		if ( typeof seconds == 'number' && seconds > 0 ) {
        //		if ( typeof seconds == 'number' && seconds != 0 ) {
        if (typeof seconds == 'number') {
            // chuyển sang dạng timestamp
            seconds = seconds * 1000;

            var date = new Date();
            date.setTime(date.getTime() + seconds);
            expires = "; expires=" + date.toGMTString();
        }


        // set cookie theo domain
        var cdomain = '';
        if (typeof set_domain != 'undefined') {
            if (set_domain.toString().split('.').length == 1) {
                cdomain = window.location.host || document.domain || '';
            } else {
                cdomain = set_domain;
            }

            //
            cdomain = cdomain.split('.');
            //			console.log(cdomain);

            // bỏ www đi -> áp dụng cho tất cả các domain
            if (cdomain[0] == 'www') {
                cdomain[0] = '';
                cdomain = cdomain.join('.');
            }
            // thêm dấu . vào đầu domain
            else if (cdomain[0] != '') {
                cdomain = '.' + cdomain.join('.');
            }
            // có dấu . ở đầu rồi thì thôi
            else {
                cdomain = cdomain.join('.');
            }
            //			console.log(cdomain);

            //
            document.cookie = encodeURIComponent(name) + "=" + encodeURIComponent(value) + expires + ";domain=" + cdomain + ";path=/";
        } else {
            document.cookie = encodeURIComponent(name) + "=" + encodeURIComponent(value) + expires + ";path=/";
        }


        //
        if (WGR_check_option_on(cf_tester_mode)) console.log('Set cookie: ' + name + ' with value: ' + value + ' for domain: ' + cdomain + ' time: ' + seconds + ' (' + days + ' day)');
    },
    getc: function (name) {
        var nameEQ = encodeURIComponent(name) + "=",
            ca = document.cookie.split(';'),
            re = '';
        for (var i = 0; i < ca.length; i++) {
            var c = ca[i];
            while (c.charAt(0) === ' ') {
                c = c.substring(1, c.length);
            }
            if (c.indexOf(nameEQ) === 0) {
                re = decodeURIComponent(c.substring(nameEQ.length, c.length));
            }
        }

        //
        if (re == '') {
            return null;
        }

        return re;
    },

    delck: function (name) {
        g_func.setc(name, "", 0 - (24 * 3600 * 7));

        //		document.cookie = name + '=;expires=Thu, 01 Jan 1970 00:00:01 GMT;';

        // v1 -> lỗi
        //		g_func.setc(name, "", -1);
        //		g_func.setc(name, "", -1, 0, true);
    },

    text_only: function (str) {
        if (typeof str == 'undefined' || str == '') {
            return '';
        }
        str = str.toString().replace(/[^a-zA-Z]/g, '');

        if (str == '') {
            return '';
        }

        return str;
    },
    number_only: function (str, format) {
        if (typeof str == 'undefined' || str == '') {
            return 0;
        }
        // mặc định chỉ lấy số
        if (typeof format == 'string' && format != '') {
            //			console.log(format);
            str = str.toString().replace(eval(format), '');

            if (str == '') {
                return 0;
            }

            //			return str;
            return str * 1;
        } else {
            str = str.toString().replace(/[^0-9\-\+]/g, '');

            if (str == '') {
                return 0;
            }

            //			return parseInt( str, 10 );
            return str * 1;
        }
    },
    only_number: function (str) {
        return g_func.number_only(str);
    },
    float_only: function (str) {
        return g_func.number_only(str, '/[^0-9\-\+\.]/g');
    },
    money_format: function (str) {
        // loại bỏ số 0 ở đầu chuỗi số
        str = str.toString().replace(/\,/g, '').split('.');
        //		str[0] = parseInt( str[0], 10 );
        str[0] = str[0] * 1;

        // chuyển sang định dạng tiền tệ
        return g_func.formatCurrency(str.join('.'), ',', 2);
    },
    number_format: function (str) {
        return g_func.formatCurrency(str);
    },
    formatV2Currency: function (number, decimals, dec_point, thousands_sep) {
        // Strip all characters but numerical ones.
        number = (number + '').replace(/[^0-9+\-Ee.]/g, '');
        var n = !isFinite(+number) ? 0 : +number,
            prec = !isFinite(+decimals) ? 0 : Math.abs(decimals),
            sep = (typeof thousands_sep === 'undefined') ? ',' : thousands_sep,
            dec = (typeof dec_point === 'undefined') ? '.' : dec_point,
            s = '',
            toFixedFix = function (n, prec) {
                var k = Math.pow(10, prec);
                return '' + Math.round(n * k) / k;
            };
        // Fix for IE parseFloat(0.55).toFixed(0) = 0;
        s = (prec ? toFixedFix(n, prec) : '' + Math.round(n)).split('.');
        if (s[0].length > 3) {
            s[0] = s[0].replace(/\B(?=(?:\d{3})+(?!\d))/g, sep);
        }
        if ((s[1] || '').length < prec) {
            s[1] = s[1] || '';
            s[1] += new Array(prec - s[1].length + 1).join('0');
        }
        return s.join(dec);
    },
    formatCurrency: function (num, dot, num_thap_phan) {
        if (typeof num == 'undefined' || num == '') {
            return 0;
        }

        //
        if (typeof dot == 'undefined' || dot == '') {
            dot = ',';
        }
        //console.log( 'dot: ' + dot );
        var dec_point = '.';
        if (dot != ',') {
            dec_point = ',';
        }
        //console.log( 'dec_point: ' + dec_point );
        if (typeof num_thap_phan == 'undefined' || num_thap_phan == '') {
            num_thap_phan = 0;
        }
        //console.log( 'num_thap_phan: ' + num_thap_phan );

        /*
         * v3
         */
        return g_func.formatV2Currency(num, num_thap_phan, dec_point, dot);


        //
        console.log(num);
        num = jQuery.trim(num);
        num = num.toString().replace(/\s/g, '');

        /*
         * sử dụng v2
         */
        var so_thap_phan = num.split('.');
        if (so_thap_phan.length > 1) {
            num = so_thap_phan[0];
            if (typeof num_thap_phan == 'number') {
                so_thap_phan = '.' + so_thap_phan[1].toString().substr(0, num_thap_phan);
            } else {
                so_thap_phan = '.' + so_thap_phan[1];
            }
        } else {
            so_thap_phan = '';
        }
        return g_func.formatV2Currency(num) + so_thap_phan;

        /*
         * v1
         */
        var str = num,
            //re = /^\d+$/,
            so_am = '',
            so_thap_phan = '';

        if (num.substr(0, 1) == '-') {
            so_am = '-';
        }

        /*
        for (var i = 0, t = ''; i < num.length; i++) {
        	t = num.substr(i, 1);
        	if (re.test(t) == true) {
        		str += t;
        	}
        }
        */
        // Nếu không phải tách số theo dấu chấm -> tìm cả số thập phân
        if (dot != '.') {
            //console.log( str );
            str = g_func.float_only(str);
            //if ( str != 0 ) {
            //console.log( str );
            so_thap_phan = str.toString().split('.');
            if (so_thap_phan.length > 1) {
                str = so_thap_phan[0];
                if (typeof num_thap_phan == 'number') {
                    so_thap_phan = '.' + so_thap_phan[1].toString().substr(0, num_thap_phan);
                } else {
                    so_thap_phan = '.' + so_thap_phan[1];
                }
            } else {
                so_thap_phan = '';
            }
            //}
            //console.log( str );
        }
        // Tách theo dấu chấm thì bỏ qua
        else {
            //console.log( str );
            str = g_func.number_only(str);
        }

        var len = str.toString().length;
        //var len = str.length;
        //console.log( len );
        if (len > 3) {
            var new_str = str.toString();
            str = '';
            for (var i = 0; i < new_str.length; i++) {
                len -= 3;
                //console.log( len );
                if (len > 0) {
                    str = dot + new_str.substr(len, 3) + str;
                } else {
                    str = new_str.substr(0, len + 3) + str;
                    break;
                }
            }
        }
        console.log(str);
        return so_am + str.replace(/\-/gi, '') + so_thap_phan;

        //
        //return num;
    },

    wh: function () {},
    opopup: function (o) {},


    mb_v2: function () {
        if (screen.width < 775 || jQuery(window).width() < 775) {
            return true;
        }
        return false;
    },
    mb: function (a) {
        return g_func.mb_v2();
    },


    /**
     * Returns a random number between min (inclusive) and max (exclusive)
     */
    getRandomArbitrary: function (min, max) {
        return Math.random() * (max - min) + min;
    },

    /**
     * Returns a random integer between min (inclusive) and max (inclusive)
     * Using Math.round() will give you a non-uniform distribution!
     */
    getRandomInt: function (min, max) {
        if (min != max && min < max) {
            return Math.floor(Math.random() * (max - min + 1)) + min;
        }
        return 0;
    },
    rand: function (min, max) {
        return g_func.getRandomInt(min, max);
    },
    short_string: function (str, len, more) {
        str = jQuery.trim(str);

        if (len > 0 && str.length > len) {
            var a = str.split(" ");
            //			console.log(a);
            str = '';

            for (var i = 0; i < a.length; i++) {
                if (a[i] != '') {
                    str += a[i] + ' ';

                    if (str.length > len) {
                        break
                    }
                }
            }
            //			console.log(str.length);
            str = jQuery.trim(str);

            if (typeof more == 'undefined' || more == true || more == 1) {
                str += '...';
            }
        }

        return str;
    }
};
