/*
 * file js thiết kế riêng cho theme wp
 */


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


//
/*
if (top == self) {
	var so_lan_reset_current_page = 0;
	setInterval(function() {
		so_lan_reset_current_page++;
		if (so_lan_reset_current_page < 3) {
			var jd = '_____eb_js_session_reset_timeout';
			if (dog(jd) == null) {
				jQuery('<div id="' + jd + '" class="d-none"></div>').appendTo('body');
			}
			ajaxl('guest.php?act=reset_timeout', jd, 9, function() {
				console.log('Reset timeout');
			});
		} else {
			window.location = window.location.href;
		}
	}, 1200 * 1000);
}
*/


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
    formatCurrency: function (num, dot, num_thap_phan) {
        if (typeof num == 'undefined' || num == '') {
            return 0;
        }

        //
        if (typeof dot == 'undefined' || dot == '') {
            dot = ',';
        }
        //		console.log( dot );

        num = num.toString().replace(/\s/g, '');
        var str = num,
            //			re = /^\d+$/,
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
            //			console.log( str );
            str = g_func.float_only(str);
            //			if ( str != 0 ) {
            //				console.log( str );
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
            //			}
            //			console.log( str );
        }
        // Tách theo dấu chấm thì bỏ qua
        else {
            //			console.log( str );
            str = g_func.number_only(str);
        }

        var len = str.toString().length;
        //		var len = str.length;
        //		console.log( len );
        if (len > 3) {
            var new_str = str.toString();
            str = '';
            for (var i = 0; i < new_str.length; i++) {
                len -= 3;
                //				console.log( len );
                if (len > 0) {
                    str = dot + new_str.substr(len, 3) + str;
                } else {
                    str = new_str.substr(0, len + 3) + str;
                    break;
                }
            }
        }
        return so_am + str + so_thap_phan;

        //
        //		return num;
    },

    wh: function () {},
    opopup: function (o) {
        if (typeof o == 'undefined') {
            jQuery('#oi_popup').hide();
            return false;
        }

        //
        if (o == 'login') {
            _global_js_eb.ga_event_track('', '', '', {
                //				'category' : '',
                //				'label' : '',
                'action': 'login'
            });
        } else if (o == 'register') {
            _global_js_eb.ga_event_track('', '', '', {
                //				'category' : '',
                //				'label' : '',
                'action': 'sign_up'
            });
        }

        //
        dog('oi_popup', '<div id="oi_popup_inner"><div align="center" style="padding:168px 0">Loading...</div></div>');

        //
        //		ajaxl(web_link + 'eb-' + o, 'oi_popup_inner', 9, function () {
        ajaxl('eb-' + o, 'oi_popup_inner', 9, function () {
            jQuery('#oi_popup_inner .popup-border').show();
        });

        //
        var a = window.scrollY || jQuery(window).scrollTop() || 0;

        //
        jQuery('#oi_popup').show().css({
            'padding-top': a + 'px'
        }).height(jQuery(document).height() - a);

        //
        return false;
    },


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


var _global_js_eb = {
    check_email: function (email, alert_true) {
        var re = /^\w+([\-\.]?\w+)*@\w+(\.\w+){1,3}$/;
        if (re.test(email) == true) {
            return true;
        }
        if (alert_true && alert_true == 1) {
            a_lert('Email kh\u00f4ng \u0111\u00fang \u0111\u1ecbnh d\u1ea1ng');
        }
        return false;
    },
    tim_theo_gia: function (id, arr_gia, str_lnk) {
        if (typeof arr_gia == 'undefined') {
            arr_gia = [{
                v: '-10,000,000',
                t: 'Dưới 10 triệu'
            }, {
                v: '10,000,000 - 20,000,000',
                t: 'Từ 10 triệu đến 20 triệu'
            }, {
                v: '20,000,000 - 50,000,000',
                t: 'Từ 20 triệu đến 50 triệu'
            }, {
                v: '50,000,000 - 100,000,000',
                t: 'Từ 50 triệu đến 100 triệu'
            }, {
                v: '100,000,000',
                t: 'Trên 100 triệu'
            }];
        }

        //
        if (typeof str_lnk == 'undefined') {
            str_lnk = 'price';
        }

        var str = '',
            sl = '';

        for (var i = 0; i < arr_gia.length; i++) {
            arr_gia[i].v = arr_gia[i].v.replace(/\s|\,/g, '');
            arr_gia[i].v = encodeURIComponent(arr_gia[i].v);

            //
            str += '<div><a data-data="' + arr_gia[i].v + '" href="actions/thread&' + str_lnk + '=' + arr_gia[i].v + '">' + arr_gia[i].t + '</a></div>';
        }

        //
        if (typeof id != 'undefined' && id != '') {
            dog(id, str);
        }

        //
        return str;
    },


    //
    sb_token: function (jd) {
        var str = '';
        str += '<input type="text" name="_ebnonce" value="' + window.location.href + '" />';

        $('#' + jd).html(str);
    },

    check_contact_frm: function () {
        _global_js_eb.add_primari_iframe();

        // thêm dữ liệu xác minh
        _global_js_eb.sb_token('frm_contact_token');

        //
        //var f = document.frm_contact;

        //
        return true;
    },


    //
    check_profile_frm: function () {
        _global_js_eb.add_primari_iframe();

        // thêm dữ liệu xác minh
        _global_js_eb.sb_token('frm_profile_token');

        //
        //var f = document.frm_canhan;

        //
        return true;
    },

    check_forgot_pasword_frm: function () {
        _global_js_eb.add_primari_iframe();

        // thêm dữ liệu xác minh
        _global_js_eb.sb_token('frm_fpasswd_token');

        return true;
    },

    check_login_frm: function () {
        _global_js_eb.add_primari_iframe();

        // thêm dữ liệu xác minh
        _global_js_eb.sb_token('frm_login_token');

        return true;
    },

    check_pasword_frm: function () {
        _global_js_eb.add_primari_iframe();

        // thêm dữ liệu xác minh
        _global_js_eb.sb_token('frm_passwd_token');

        //
        var f = document.frm_canhan;

        if (jQuery.trim(f.t_matkhau.value).length < 6) {
            jQuery('.show-if-pass-short').fadeIn();

            f.t_matkhau.focus();

            return false;
        }

        //
        return true;
    },

    contact_func: function () {
        _global_js_eb.cart_customer_cache(document.frm_contact);
    },


    // chuyển kích thước cho bản mobile
    set_mobile_size: function () {
        if (global_window_width > 768) {
            return false;
        }

        if (pid > 0 && cf_product_details_mobile_size != '') {
            jQuery('.thread-details-mobileCenter .ti-le-global').attr({
                'data-size': cf_product_details_mobile_size
            });
        }

        if (cf_product_mobile2_size != '') {
            jQuery('.thread-list .ti-le-global').attr({
                'data-size': cf_product_mobile2_size
            });
        }
    },

    auto_margin: function () {
        // tạo attr mặc định để lưu thuộc tính cũ
        jQuery('.img-max-width').each(function () {
            var max_width = jQuery(this).attr('data-max-width') || '';
            //console.log('aaaaaaaaaaa: ' + max_width);
            if (max_width == '' || max_width < 90) {
                max_width = jQuery(this).attr('data-width') || jQuery(this).width() || 0;
                max_width = Math.ceil(max_width) - 1;
                jQuery(this).attr({
                    'data-max-width': max_width
                });
            }
            //console.log('bbbbbbbbbbb: ' + max_width);

            // chỉnh lại chiều rộng của thẻ DIV trong khung nội dung (trừ đi padding với border của div)
            jQuery('.wp-caption', this).css({
                'max-width': max_width + 'px'
            });


            jQuery('iframe', this).each(function () {
                var a = jQuery(this).attr('src') || '',
                    wit = jQuery(this).attr('data-width') || jQuery(this).attr('width') || 560,
                    no_resize = jQuery(this).attr('data-no-resize') || 0;
                if (no_resize * 1 != 1) {
                    if (WGR_check_option_on(cf_tester_mode)) console.log(a);

                    if (wit > max_width) {
                        wit = max_width - 1;
                    }
                    //				console.log(wit);

                    // chỉ xử lý với video youtube
                    if (a.split('youtube.com/').length > 1) {
                        jQuery(this).attr({
                            //'data-height' : jQuery(this).attr('data-height') || jQuery(this).attr('height') || 315,
                            'data-width': Math.ceil(wit)
                        });
                    }
                }
            });


            // thẻ TABLE
            var i = 0;
            jQuery('table', this)
                // fixed chiều rộng tối đa cho table
                .css({
                    'max-width': max_width + 'px'
                })
                .each(function () {
                    var a = jQuery(this).attr('data-no-reponsive') || '';

                    //
                    if (a == '') {
                        jQuery(this).before('<div class="reponsive-for-table reponsive-for-table' + i + '"></div>');

                        jQuery(this).clone().appendTo('.reponsive-for-table' + i);

                        jQuery(this).remove();

                        i++;
                    }
                });

            //
            jQuery('.reponsive-for-table table').attr({
                'data-no-reponsive': 1
            });

        });


        var avt_max_height = 250,
            //css_m_id = 'css-for-mobile',
            screen_width = jQuery(window).width(),
            current_device = '';

        // nếu có thuộc tính cố định, định dạng cho phiên bản -> lấy theo thuộc tính này
        if (window.location.href.split('&set_device=').length > 1) {
            current_device = window.location.href.split('&set_device=')[1].split('&')[0].split('#')[0];
        } else {
            current_device = g_func.getc('click_set_device_style');
        }

        // for mobile
        if (screen_width < 950 && current_device != 'desktop') {
            // Điều chỉnh bằng cách dùng chung một chức năng
            jQuery('.fix-li-wit').each(function () {
                var a = jQuery(this).width() || 0,
                    w = jQuery(this).attr('data-width') || '',
                    w_big = jQuery(this).attr('data-big-width') || '',
                    // điều chỉnh chiều rộng cho loại thẻ hoặc class nào -> mặc định là li
                    fix_for = jQuery(this).attr('data-tags') || 'li';

                //
                if (a > 0 && w != '') {

                    // Với màn hình ipad dọc Sử dụng kích thước lớn hơn chút
                    if (screen_width > 700 && w_big != '') {
                        w = w_big;
                    }

                    //
                    w = Math.ceil(a / w) - 1;
                    if (w < 1) {
                        w = 1;
                    }

                    //
                    jQuery(fix_for, this).width((100 / w) + '%');
                }
            });


            //
            jQuery('.img-max-width .wp-caption').width('auto');


            // trên mobile -> giới hạn kích thước media
            jQuery('.img-max-width').each(function () {
                // lấy theo kích thước tối đa của khung này luôn
                var max_width = jQuery(this).attr('data-width') || jQuery(this).width() || 250;
                max_width = Math.ceil(max_width) - 1;
                console.log('max_width: ' + max_width);


                // xử lý với video của youtube
                jQuery('iframe', this).each(function () {
                    var a = jQuery(this).attr('src') || '';

                    // chỉ xử lý với video youtube
                    if (a.split('youtube.com/').length > 1) {
                        jQuery(this).attr({
                            'width': max_width,
                            'height': Math.ceil(max_width * youtube_video_default_size)
                        });
                    }
                });
            });
        }
        // for PC
        else {
            //jQuery('body').removeClass('style-for-mobile').removeClass('style-for-table').removeClass('style-for-ngang-mobile');

            //
            jQuery('.fix-li-wit').each(function () {
                var fix_for = jQuery(this).attr('data-tags') || 'li';

                //
                jQuery(fix_for, this).width('');
            });


            // hình ảnh và clip trên bản pc -> giờ mới xử lý
            jQuery('.img-max-width').each(function () {
                var max_width = jQuery(this).attr('data-max-width') || 250;
                max_width = Math.ceil(max_width) - 1;

                //
                jQuery('iframe', this).each(function () {
                    var a = jQuery(this).attr('src') || '';
                    var no_resize = jQuery(this).attr('data-no-resize') || 0;

                    if (no_resize * 1 != 1) {
                        if (WGR_check_option_on(cf_tester_mode)) console.log(a);

                        // chỉ xử lý với video youtube
                        if (a.split('youtube.com/').length > 1) {
                            //console.log('a: ' + a);
                            var wit = jQuery(this).attr('data-width') || jQuery(this).attr('width') || 560;
                            //console.log('wit: ' + jQuery(this).attr('width'));
                            if (wit.toString().split('%').length > 1) {
                                wit = wit.replace(/\%/, '') * 1;
                                wit = jQuery(this).width() || 560;
                                //console.log('wit%: ' + wit);
                            } else if (isNaN(wit)) {
                                wit = jQuery(this).width() || 560;
                            }
                            //console.log('wit1: ' + wit);
                            if (wit > max_width) {
                                wit = max_width;
                            }
                            wit -= 1;
                            //console.log('wit2: ' + wit);

                            //
                            jQuery(this).attr({
                                'width': wit,
                                'height': Math.ceil(wit * youtube_video_default_size)
                            });
                        }
                    }
                });
            });
        }

        //
        if (typeof pid != 'undefined' && pid > 0) {
            var wit_mb = jQuery('.thread-details-mobileAvt').width(),
                hai_mb = wit_mb,
                li_len = jQuery('.thread-details-mobileAvt li').length,
                li_wit = 100 / li_len;

            jQuery('.thread-details-mobileAvt ul').width(wit_mb * li_len);
            jQuery('.thread-details-mobileAvt li').width(li_wit + '%');
        }


        //
        jQuery('.no-set-width-this-li').width('100%');


        // chỉnh kích cỡ ảnh theo tỉ lệ
        var new_arr_ti_le_global = {};
        jQuery('.ti-le-global').each(function () {
            var a = jQuery(this).width(),
                // hiển thị size ảnh gợi ý cho admin
                show_height = 0,
                // tỉ lệ kích thước giữa chiều cao và rộng (nếu có), mặc định là 1x1
                // -> nhập vào là: chiều cao/ chiều rộng
                new_size = jQuery(this).attr('data-size') || '';

            // với size auto -> set thẳng ảnh vào thay vì background
            if (new_size == 'auto') {
                var img = jQuery(this).attr('data-img') || '';
                if (img != '') {
                    jQuery(this).after('<div class="echbay-blog-avt auto-size"><img src="' + img + '" width="' + a + '" /></div>').remove();
                }
            } else if (new_size == 'full') {
                a = jQuery(window).height();
                //				console.log(a);

                //
                jQuery(this).css({
                    'line-height': a + 'px',
                    height: a + 'px'
                });
            } else {
                var pading_size = 'ty-le-h100';
                show_height = a;
                // Tính toán chiều cao mới dựa trên chiều rộng
                if (new_size != '') {
                    if (new_size.split('x').length > 1 || new_size.split('*').length > 1) {
                        new_size.split('x').split('*');
                        new_size = new_size[1] + '/' + new_size[0];
                    }
                    pading_size = 'ty-le-h' + new_size.replace(/\/|\./gi, '_');

                    // v2 -> tính padding theo chiều rộng
                    a = eval(new_size);
                    show_height *= a;
                }
                // mặc định thì cho = 1 -> 100%
                else {
                    a = 1;
                }

                if (typeof arr_ti_le_global[pading_size] == 'undefined') {
                    arr_ti_le_global[pading_size] = a;
                    new_arr_ti_le_global[pading_size] = a;
                }

                // 1 số trường hợp vẫn dùng class cũ
                if ($(this).hasClass('thread-details-mobileAvt')) {
                    jQuery(this).css({
                        'line-height': show_height + 'px',
                        height: show_height + 'px'
                    });
                }
                // còn lại sẽ cho class mới
                else {
                    jQuery(this).addClass(pading_size).addClass('ty-le-global').removeClass('ti-le-global').attr({
                        'data-show-height': show_height
                    });
                }
            }
        });
        //console.log(arr_ti_le_global);
        //console.log(new_arr_ti_le_global);
        var str_css = '';
        for (var x in new_arr_ti_le_global) {
            new_arr_ti_le_global[x] *= 100;

            // quy đổi padding teo % chiều rộng của width
            str_css += '.' + x + '{padding-top:' + (new_arr_ti_le_global[x].toFixed(3) * 1) + '%}';
        }
        if (str_css != '') {
            console.log('ty-le-global padding CSS: ' + str_css);
            $('head').append('<style>' + str_css + '</style>');
        }

    },

    big_banner: function () {
        var a = jQuery('.oi_big_banner li:first').height();

        jQuery('.oi_big_banner, .oi_big_banner li').height(a).css({
            'line-height': a + 'px'
        });
        //		jQuery('.oi_big_banner').height( a );
    },

    money_format_keyup: function () {
        jQuery('.change-tranto-money-format').off('keyup').off('change').keyup(function (e) {
            var k = e.keyCode,
                a = jQuery(this).val() || '';
            if ((k >= 48 && k <= 57) || (k >= 96 && k <= 105) || k == 8 || k == 46) {
                a = g_func.formatCurrency(a);
                if (a == 0 || a == '0') {
                    jQuery(this).val(a).select();
                } else {
                    jQuery(this).val(a).focus();
                }
            }
        }).change(function () {
            jQuery(this).val(g_func.formatCurrency(jQuery(this).val()));
        });
    },

    select_date: function (id, op) {
        if (typeof op == 'undefined') {
            op = {};
        }
        if (typeof op.dateFormat == 'undefined') {
            op.dateFormat = 'yy/mm/dd';
        }
        jQuery.datepicker.regional.vi = {
            monthNames: ['Th\u00e1ng 1', 'Th\u00e1ng 2', 'Th\u00e1ng 3', 'Th\u00e1ng 4', 'Th\u00e1ng 5', 'Th\u00e1ng 6', 'Th\u00e1ng 7', 'Th\u00e1ng 8', 'Th\u00e1ng 9', 'Th\u00e1ng 10', 'Th\u00e1ng 11', 'Th\u00e1ng 12'],
            monthNamesShort: ['Th1', 'Th2', 'Th3', 'Th4', 'Th5', 'Th6', 'Th7', 'Th8', 'Th9', 'Th10', 'Th11', 'Th12'],
            dayNames: ['Chủ Nhật', 'Thứ 2', 'Thứ 3', 'Thứ 4', 'Thứ 5', 'Thứ 6', 'Thứ 7'],
            dayNamesShort: ['CN', 'T2', 'T3', 'T4', 'T5', 'T6', 'T7'],
            dayNamesMin: ['CN', 'T2', 'T3', 'T4', 'T5', 'T6', 'T7']
        };
        jQuery.datepicker.setDefaults(jQuery.datepicker.regional.vi);

        //
        jQuery(id).attr({
            'date-format': op['dateFormat']
        }).change(function () {
            var a = jQuery(this).val() || '',
                df = jQuery(this).attr('date-format') || '',
                b = '';
            a = jQuery.trim(a);
            //			console.log(a);
            //			console.log(df);

            // kiểm tra và chuyển về 1 định dạng
            if (a != '' && a.length == 10 && df != '') {
                a = a.replace(/\-|\s/g, '/').split('/');
                if (df.split('/').length > 1) {
                    jQuery(this).val(a.join('/'));
                } else if (df.split('-').length > 1) {
                    jQuery(this).val(a.join('-'));
                } else if (df.split(' ').length > 1) {
                    jQuery(this).val(a.join(' '));
                }

                //
                df = df.toLowerCase().replace(/\-|\s/g, '/').split('/');
                //				console.log(a);
                //				console.log(df);

                //
                if (df.length == 3) {
                    // định dạng năm/ tháng/ ngày
                    if (df[0] == 'yy' || df[0] == 'yyyy') {
                        // nếu mảng đầu tiên không phải là năm -> bỏ luốn
                        if (a[0].toString().length != 4 && a[2].toString().length == 4) {
                            b = a[0];
                            a[0] = a[2];
                            a[2] = b;

                            jQuery(this).val(a.join('/'));
                        }
                    }
                    // ngày/ tháng/ năm
                    else if (df[2] == 'yy' || df[2] == 'yyyy') {
                        // nếu mảng cuối không phải là năm -> bỏ luốn
                        if (a[2].toString().length != 4 && a[0].toString().length == 4) {
                            b = a[2];
                            a[2] = a[0];
                            a[0] = b;

                            jQuery(this).val(a.join('/'));
                        }
                    }
                }
            }
        }).datepicker(op);
    },

    _log_click_ref: function () {
        setTimeout(function () {
            var a = document.referrer || '',
                click_url = window.location.href,
                s = '',
                s2 = '',
                uri = '',
                staff_id = '',
                check_staff_id = '',
                jd = 'process_referrer_data_click';

            //
            if (a == '') {
                return false;
            }

            // Nếu không phải chế độ TEST -> bỏ qua khi cùng là domain
            //			if ( WGR_check_option_on ( cf_tester_mode ) ) {
            s = a.split('//')[1].split('/')[0];
            s2 = click_url.split('//')[1].split('/')[0];
            if (s.split(s2).length > 1 || s2.split(s).length > 1) {
                return false;
            }
            //			}

            // lưu cookie của referrer để còn cho vào đơn hàng tiện theo dõi
            g_func.setc('eb_document_referrer', escape(a), 0, 7);


            /*
            if (dog(jd) == null) {
            	jQuery('<div id="' + jd + '" style="display:none;"></div>').appendTo('body');
            }
            */

            //
            var pad = function (number, length) {
                    var str = "" + number;
                    while (str.length < length) {
                        str = '0' + str;
                    }
                    return str;
                },
                offset = new Date().getTimezoneOffset();
            offset = ((offset < 0 ? '+' : '-') + pad(parseInt(Math.abs(offset / 60)), 2) + pad(Math.abs(offset % 60), 2));

            //
            var arr = {
                ref: encodeURIComponent(a),
                url: encodeURIComponent(click_url),
                iframe: (function () {
                    return (top != self) ? 1 : 0;
                })(),
                title: (function () {
                    var str = document.title || '';
                    if (str != '') {
                        str = encodeURIComponent(str);
                    }
                    return str;
                })(),
                timezone: encodeURIComponent(offset),
                lang: (function () {
                    var str = navigator.userLanguage || navigator.language || '';
                    return str;
                })(),
                usertime: (function () {
                    var t = new Date().getTime();
                    //					t = parseInt(t / 1000, 10);
                    t = t / 1000 * 1;
                    return t;
                })(),
                window: jQuery(window).width() + 'x' + jQuery(window).height(),
                document: jQuery(document).width() + 'x' + jQuery(document).height(),
                screen: screen.width + 'x' + screen.height,
                quaylai: (g_func.getc('eb_wgr_quaylai_log_click') != null) ? 1 : 0,
                /*
                agent: (function() {
                	var str = navigator.userAgent || navigator.vendor || window.opera || '';
                	str = str.replace(/\s/g, '+');
                	return str;
                })(),
                */
                staff_id: staff_id
            };
            /*
            uri = '';
            for (var x in arr) {
            	uri += '&' + x + '=' + arr[x];
            }
            */
            uri = JSON.stringify(arr);
            if (WGR_check_option_on(cf_tester_mode)) {
                console.log(arr);
                console.log(uri);
            }

            // lưu dưới dạng cookie
            g_func.setc('eb_wgr_log_click', escape(uri), 60);

            // xem là khách cũ hay mới
            g_func.setc('eb_wgr_quaylai_log_click', 1, 3600 * 6);

            //
            setTimeout(function () {
                ajaxl('log_click', jd, 1);
                console.log('Log referrer');
            }, 1200);

            //
            /*
			return false;
			
			
			//
			check_staff_id = click_url.split('utm_source=');
			if (check_staff_id.length > 1) {
				staff_id = check_staff_id[1].split('&')[0].split('/')[0].split('?')[0].split('#')[0];
				if (staff_id != '') {
					staff_id = staff_id.toLowerCase().split('ctv');
					if (staff_id.length > 1) {
						staff_id = staff_id[1].split('eb')[0];
//						staff_id = parseInt(staff_id, 10);
						staff_id = staff_id * 1;
						if (isNaN(staff_id) || staff_id <= 0) {
							staff_id = '';
						}
					} else {
						staff_id = '';
					}
				}
			}
			if (staff_id == '') {
				staff_id = 0;
			}
			if (staff_id > 0) {
				g_func.setc('ss_staff_id', staff_id, 0, 30);
			}
			if (g_func.getc('ss_ads_referre') != null) {
				console.log('user return');
				return false;
			}
			g_func.setc('ss_ads_referre', encodeURIComponent(a), 3600 * 6);
			*/
        }, 600);
    },

    ebBgLazzyLoadOffset: function (i) {
        //		console.log( 'each-to-bgimg offset' );

        if (typeof i != 'number') {
            i = 5;
        }

        jQuery('.each-to-bgimg').each(function () {
            a = jQuery(this).attr({
                'data-offset': jQuery(this).offset().top
            });
        });

        if (i > 0) {
            setTimeout(function () {
                _global_js_eb.ebBgLazzyLoadOffset(i - 1);
            }, 2000);
        }
    },

    ebBgLazzyLoad: function (lazzy_show) {
        var eb_lazzy_class = 'eb-lazzy-effect',
            eb_lazzy_iframe = 'eb-add-iframe',
            a = 0,
            wh = jQuery(window).width(),
            c = '';

        //
        if (typeof lazzy_show == 'number' && lazzy_show > 0) {
            //console.log(lazzy_show);
            //console.log(disable_eblazzy_load);
            //console.log('eb_lazzy_class length: ' + jQuery('.' + eb_lazzy_class).length);

            // Nếu ko đủ class để làm việc -> thoát luôn
            if (disable_eblazzy_load == true) {
                //disable_eblazzy_load = true;
                return false;
            } else if (jQuery('.' + eb_lazzy_class).length == 0 && jQuery('.' + eb_lazzy_iframe).length == 0) {
                disable_eblazzy_load = true;
                return false;
            }

            // load trước các ảnh ngoài màn hình, để lát khách kéo xuống có thể xem được luôn
            //			lazzy_show += 600;
            //			lazzy_show += 1500;
            //			lazzy_show += jQuery(window).height()/ 2;
            lazzy_show += height_for_lazzy_load;
            //			console.log( height_for_lazzy_load );

            //
            jQuery('.' + eb_lazzy_class).each(function () {
                a = jQuery(this).offset().top || 0;
                //				a = jQuery(this).attr('data-offset') || jQuery(this).offset().top || 0;

                if (a < lazzy_show) {
                    var wit = jQuery(this).width() || 300;

                    // v1
                    /*
					jQuery(this).css({
//						opacity: 1,
//					}).css({
						'background-image': 'url(\'' + (jQuery(this).attr('data-img') || '') + '\')'
					});
					*/


                    // v2
                    var img = jQuery(this).attr('data-img') || '',
                        img_table = jQuery(this).attr('data-table-img') || img || '',
                        img_mobile = jQuery(this).attr('data-mobile-img') || img_table || '';

                    //
                    if (img == 'speed') {
                        img = img_mobile;
                        // sử dụng ảnh cho bản mobile
                        //						if ( wh < 768 && img_mobile != '' ) {
                        if (img_mobile != '') {
                            // mobile
                            if (wit < 250) {
                                img += 'm';
                            }
                            // table
                            /*
                            else if ( wit < 400 ) {
                            	img += 't';
                            }
                            */
                        }
                        jQuery(this).addClass(img);
                    } else if (img != '') {
                        // sử dụng ảnh cho bản mobile
                        //						if ( wh < 768 && img_mobile != '' && img.split('.').pop().toLowerCase() != 'png' ) {
                        //						if ( img_mobile != '' && img.split('.').pop().toLowerCase() != 'png' ) {
                        //							if ( wit < 250 ) {
                        if (wh < 550) {
                            if (wit < 150) {
                                img = img_mobile;
                            } else {
                                img = img_table;
                            }
                        } else if (wh < 750) {
                            img = img_table;
                        }
                        /*
                        else if ( wit < 768 ) {
                        	img = img_table;
                        }
                        */
                        //						}

                        // sử dụng cdn nếu ảnh trong thư mục upload
                        //						if ( primary_domain_usage_eb != '' && img.split('/')[0] == 'upload' ) {
                        //							img = img.replace( 'upload/', '//upload.' +primary_domain_usage_eb+ '.com.vn/' );
                        //						}

                        //
                        jQuery(this).css({
                            //							opacity: 1,
                            //						}).css({
                            //							'background-image': 'url(\'' + _global_js_eb.resize_img( img, jQuery(this).width() ) + '\')'
                            'background-image': 'url(\'' + img + '\')'
                        });
                    }

                    //
                    jQuery(this).removeClass(eb_lazzy_class).addClass('lazyload-img-done');
                }
                /*
                else {
                	return false;
                }
                */
            });


            //
            jQuery('.' + eb_lazzy_iframe).each(function () {
                a = jQuery(this).offset().top || 0;
                //				a = jQuery(this).attr('data-offset') || jQuery(this).offset().top || 0;
                //				console.log( 'a: ' + a );
                //				console.log( 'lazzy_show: ' + lazzy_show );

                if (a < lazzy_show) {
                    c = jQuery(this).attr('data-iframe') || '';
                    //					console.log(c);
                    if (c != '') {
                        //						console.log(c);
                        c = unescape(c);
                        //						console.log(c);
                        jQuery(this).html(c);

                        //
                        jQuery(this).removeClass(eb_lazzy_iframe).addClass('lazyload-iframe-done');
                    }
                }
            });

        } else {
            jQuery('.each-to-bgimg').addClass(eb_lazzy_class);
            jQuery('.url-to-google-map').addClass(eb_lazzy_iframe);
            /*
            jQuery('.each-to-bgimg').addClass(eb_lazzy_class).css({
            	opacity: .2
            });
            */

            _global_js_eb.ebBgLazzyLoad(jQuery(window).height() * 1.5);
        }
    },

    fix_url_id: function () {
        if (cid <= 0) {
            return false;
        }

        var wh = window.location.href,
            new_url = '';
        if (wh.split('&').length > 1 || wh.split('-page').length > 1) {
            console.log('Not rewrite URL or Part page');
            return false;
        }


        //
        /*
		if (pid > 0) {
			if (typeof fix_url_pid != 'undefined' && fix_url_pid != '') {
				fix_url_pid = web_link + fix_url_pid;
				
//					console.log( window.location.href.split('//')[1] );
//					console.log( fix_url_pid.split('//')[1] );
				
				try {
					if ( window.location.href.split('//')[1] != fix_url_pid.split('//')[1] ) {
						new_url = fix_url_pid;
					}
				} catch (e) {
					console.log( WGR_show_try_catch_err( e ) );
				}
			}
		}
		else if (fid > 0) {
			*/
        if (fid > 0) {
            for (var i = 0; i < site_group.length; i++) {
                if (new_url == '') {
                    (function (arr) {
                        for (var i = 0; i < arr.length; i++) {
                            (function (arr) {
                                for (var i = 0; i < arr.length; i++) {
                                    if (arr[i].id == fid) {
                                        new_url = _global_js_eb._c_link(fid, arr[i].seo, 'f');
                                        break;
                                    }
                                }
                            }(arr[i].arr));
                        }
                    }(site_group[i].arr));
                }
            }
        } else if (sid > 0) {
            for (var i = 0; i < site_group.length; i++) {
                if (new_url == '') {
                    (function (arr) {
                        for (var i = 0; i < arr.length; i++) {
                            if (arr[i].id == sid) {
                                new_url = _global_js_eb._c_link(sid, arr[i].seo, 's');
                                break;
                            }
                        }
                    }(site_group[i].arr));
                }
            }
        } else {
            for (var i = 0; i < site_group.length; i++) {
                if (site_group[i].id == cid) {
                    new_url = _global_js_eb._c_link(cid, site_group[i].seo);
                    break;
                }
            }
        }

        //
        if (web_link.split('//')[1] + new_url != window.location.href.split('//')[1]) {
            console.log(web_link.split('//')[1] + new_url);
            console.log(window.location.href.split('//')[1]);

            window.history.pushState("", '', new_url);
        }
    },

    cart_agent: function () {

        if (dog('cart_user_agent') == null) {
            console.log('cart_user_agent not found');
            return false;
        }


        //
        var pad = function (number, length) {
                var str = "" + number;
                while (str.length < length) {
                    str = '0' + str;
                }
                return str;
            },
            offset = new Date().getTimezoneOffset(),
            str = '';

        //
        offset = ((offset < 0 ? '+' : '-') + pad(parseInt(Math.abs(offset / 60)), 2) + pad(Math.abs(offset % 60), 2));

        //
        var eb_referrer = g_func.getc('eb_document_referrer');
        if (eb_referrer == null) {
            eb_referrer = '';
        } else {
            eb_referrer = unescape(eb_referrer);
        }

        //
        var ck_utm = g_func.getc('WGR_ref_for_campaign');
        if (ck_utm == null) {
            ck_utm = {};
        } else {
            ck_utm = jQuery.parseJSON(unescape(ck_utm));
        }

        //
        var arr = {
            // user info
            hd_ten: '',
            hd_dienthoai: '',
            hd_email: '',
            hd_diachi: '',
            hd_ghichu: '',
            hd_discount_code: '',
            hd_thanhtoan: 'tructiep',

            // user agent
            hd_url: window.location.href,
            hd_title: (function (str) {
                return str;
            })(document.title || ''),
            hd_timezone: offset,
            hd_lang: (function (str) {
                return str;
            })(navigator.userLanguage || navigator.language || ''),
            hd_usertime: (function () {
                var t = new Date().getTime();
                //				t = parseInt(t / 1000, 10);
                t = t / 1000 * 1;
                return t;
            })(),
            hd_window: jQuery(window).width() + 'x' + jQuery(window).height(),
            hd_document: jQuery(document).width() + 'x' + jQuery(document).height(),
            hd_screen: screen.width + 'x' + screen.height,
            hd_agent: navigator.userAgent,
            hd_referrer: eb_referrer,
            hd_utm_source: typeof ck_utm['src'] != 'undefined' ? ck_utm['src'] : '',
            hd_utm_medium: typeof ck_utm['med'] != 'undefined' ? ck_utm['med'] : '',
            hd_utm_campaign: typeof ck_utm['cam'] != 'undefined' ? ck_utm['cam'] : ''
        };

        // user info
        if (pid > 0) {
            var f = document.frm_cart;

            arr.hd_ten = f.t_ten.value;
            arr.hd_dienthoai = f.t_dienthoai.value;
            arr.hd_email = f.t_email.value;
            arr.hd_diachi = f.t_diachi.value;
            arr.hd_ghichu = f.t_ghichu.value;
            arr.hd_discount_code = f.t_discount_code.value;
            arr.hd_chietkhau = f.t_discount_value.value;
        }
        // cart
        else {
            var f = document.frm_cart;

            //
            try {
                arr.hd_ten = f.t_ten.value;
                arr.hd_dienthoai = f.t_dienthoai.value;
                arr.hd_email = f.t_email.value;
                arr.hd_diachi = f.t_diachi.value;
                arr.hd_ghichu = f.t_ghichu.value;
                if (typeof f.t_discount_code != 'undefined') {
                    arr.hd_discount_code = f.t_discount_code.value;

                    if (typeof f.t_discount_value != 'undefined') {
                        arr.hd_chietkhau = f.t_discount_value.value;
                    }
                }
                arr.hd_thanhtoan = f.t_thanhtoan.value;
            } catch (e) {
                console.log(WGR_show_try_catch_err(e));

                //
                try {
                    arr.hd_ten = jQuery('#t_ten').val() || '';
                    arr.hd_dienthoai = jQuery('#t_dienthoai').val() || '';
                    arr.hd_email = jQuery('#t_email').val() || '';
                    arr.hd_diachi = jQuery('#t_diachi').val() || '';
                    arr.hd_ghichu = jQuery('#t_ghichu').val() || '';
                    if (typeof f.t_discount_code != 'undefined') {
                        arr.hd_discount_code = f.t_discount_code.value;

                        if (typeof f.t_discount_value != 'undefined') {
                            arr.hd_chietkhau = f.t_discount_value.value;
                        }
                    }
                    arr.hd_thanhtoan = jQuery('input[name="t_thanhtoan"]:checked').val() || 'tructiep';
                } catch (e) {
                    console.log(WGR_show_try_catch_err(e));
                }
            }
        }

        // key tìm kiếm đơn hàng
        arr.hd_key = g_func.non_mark_seo(arr.hd_ten + arr.hd_dienthoai + arr.hd_email);
        arr.hd_key = arr.hd_key.replace(/\-/g, '');

        //		console.log(arr);

        /*
		for (var x in arr) {
			// v2
			arr[x] = encodeURIComponent(arr[x].toString().replace(/"/g, '&quot;'));
			str += ',' + x + ':"' + arr[x] + '"'
			
			// v1
//					str += '<input type="text" name="' + x + '" value="' + arr[x] + '" />';
		}
		if (str != '') {
			str = str.substr(1)
		}
		*/
        //		str = JSON.stringify( arr );
        //		alert( str );

        //
        //		if ( dog('hd_customer_info') == null ) {
        if (jQuery('#hd_customer_info').length == 0) {
            jQuery('#cart_user_agent').append('<textarea name="hd_customer_info" id="hd_customer_info"></textarea>');
        }
        jQuery('#hd_customer_info').val(escape(JSON.stringify(arr)));

        //
        //		if ( dog('hd_re_link') == null ) {
        if (jQuery('#hd_re_link').length == 0) {
            jQuery('#cart_user_agent').append('<input type="text" name="t_re_link" id="hd_re_link" value="" />');
        }
        jQuery('#hd_re_link').val(window.location.href);

    },

    _c_link: function (id, seo, name) {
        if (typeof name == 'undefined' || name == '') {
            name = 'c';
        }
        if (typeof seo == 'undefined') {
            return name + id + '/';
        }

        /*
         * định hình URL
         */
        // -> /thiet-ke-web-gia-re-c1/
        if (cf_categories_url == 1) {
            return seo + '-' + name + id + '/';
        }
        // -> /thiet-ke-web-gia-re/
        else if (cf_categories_url == 2) {
            return seo + '/';
        }
        // /c1/thiet-ke-web-gia-re.html
        else if (cf_categories_url == 3) {
            return name + id + '/' + seo + '.html';
        }
        // -> /c1/thiet-ke-web-gia-re/
        else if (cf_categories_url == 4) {
            return name + id + '/' + seo + '/';
        }
        // -> /thiet-ke-web-gia-re.html
        else if (cf_categories_url == 5) {
            return seo + '.html';
        }

        // default -> /c1-thiet-ke-web-gia-re.html
        return seo + '-' + name + id + '.html';
    },

    youtube_id: function (a, start_end) {
        if (a.split('youtube.com').length > 1 || a.split('youtu.be').length > 1) {
            // lấy thời gian bắt đầu, kết thúc nếu có
            var s = '',
                e = '';
            if (typeof start_end != 'undefined') {
                start_end = a.replace(/\?/g, '&');
                //				console.log( start_end );

                //
                s = start_end.split('&start=');
                if (s.length > 1) {
                    s = '&start=' + s[1].split('&')[0];
                } else {
                    s = '';
                }

                //
                e = start_end.split('&end=');
                if (e.length > 1) {
                    e = '&end=' + e[1].split('&')[0];
                } else {
                    e = '';
                }
            }

            //
            var youtube_parser = function (url) {
                var regExp = /^.*((youtu.be\/)|(v\/)|(\/u\/\w\/)|(embed\/)|(watch\?))\??v?=?([^#\&\?]*).*/;

                var match = url.match(regExp);

                return (match && match[7].length == 11) ? match[7] : false;
            };

            return youtube_parser(a) + s + e;
        }

        //
        return '';
    },

    user_img_loc: function (wit, hai) {
        // lấy tọa độ người dùng
        var a = _global_js_eb.user_loc();
        //		console.log(a);
        //		console.log(typeof a);

        // Kiểu dữ liệu về bắt buộc phải là một mảng
        if (typeof a == 'object' && typeof a.loc != 'undefined') {
            //			console.log(a);

            //
            if (typeof wit != 'number') wit = 400;

            if (typeof hai != 'number') hai = 400;

            return '//maps.googleapis.com/maps/api/staticmap?center=' + a.loc + '&zoom=14&size=' + wit + 'x' + hai + '&sensor=false';
        }

        // Mặc định trả về 1 chuỗi
        return '';
    },

    user_string_loc: function () {
        // lấy tọa độ người dùng
        var a = _global_js_eb.user_loc();
        //		console.log(a);
        //		console.log(typeof a);

        // Kiểu dữ liệu về bắt buộc phải là một mảng
        if (typeof a == 'object') {
            // Chuyển thành chuỗi
            a = JSON.stringify(a);
            //			console.log(a);
            //			console.log(typeof a);

            return a;
        }

        // Mặc định trả về 1 chuỗi
        return '';
    },

    user_loc: function (real_time, after_load) {
        // TEST
        //		g_func.delck('ipinfo_to_language'); return;

        // kiểm tra trong cookie xem có ko
        if (typeof real_time != 'undefined' && real_time == 1) {
            var a = null;
            console.log('clear ipinfo_to_language cookie');
        } else {
            var a = g_func.getc('ipinfo_to_language');
        }
        //		console.log( a );

        // nếu có -> trả về luôn
        if (a != null) {
            //			console.log(a);
            //			console.log(typeof a);

            //
            //			g_func.setc('ipinfo_to_language', a, 3600 * 2 );

            //
            var json_array = function (a) {
                if (typeof a != 'object') {
                    try {
                        return JSON.parse(a);
                    } catch (e) {
                        console.log(WGR_show_try_catch_err(e));
                    }
                }

                return a;
            };

            // Chỉnh lại chuỗi về dạng mảng
            a = json_array(a);

            // làm lại lần nữa cho chắc ăn
            if (typeof a != 'object') {
                a = json_array(a);
            }

            // quá tam 3 bận
            if (typeof a != 'object') {
                // xóa cookie này đi để tạo lại sau
                g_func.delck('ipinfo_to_language');

                //trả về một trường trống
                return {};
            }

            //
            if (typeof after_load == 'function') {
                after_load(a);
            }

            //
            console.log(a);

            return a;
        }

        // chức năng hỏi tọa độ chỉ hoạt động trên HTTPS -> kiểm tra luôn
        if (window.location.protocol != 'https:') {
            console.log('navigator.geolocation only runing in HTTPS');

            // chuyển sang chế độ lấy tự động luôn và ngay
            return _global_js_eb.user_auto_loc(after_load);
        }


        // Hỏi tọa độ của người dùng
        navigator.geolocation.getCurrentPosition(function (position) {
            // Nếu người dùng tiết lộ -> xin luôn
            var lat = position.coords.latitude,
                lon = position.coords.longitude;
            if (WGR_check_option_on(cf_tester_mode)) console.log(position);
            //			console.log( lat );
            //			console.log( lon );

            //
            //			var data = '{"loc":"' +lat+ ',' +lon+ '"}';
            var data = {
                loc: lat + ',' + lon,
                lat: lat,
                lon: lon
            };
            if (WGR_check_option_on(cf_tester_mode)) console.log(data);

            // lưu lại trong cookies
            g_func.setc('ipinfo_to_language', JSON.stringify(data), 3600 * 2);

            if (typeof after_load == 'function') {
                after_load(data);
            }

            return data;
        }, function () {
            //			return _global_js_eb.user_auto_loc( after_load );
            return _global_js_eb.user_auto_loc();
        }, {
            timeout: 10000
        });

        // mặc định là tra về 1 mảng trống
        return {};
    },

    // tự động lấy vị trí tương đối của người dùng mà không cần xin phép
    user_auto_loc: function (after_load) {
        console.log('AUTO get user Position');

        //
        var url_get_ip_info = window.location.protocol + '//ipinfo.io';
        if (typeof client_ip != 'undefined' && client_ip != '') {
            url_get_ip_info += '/' + client_ip;
        }
        if (WGR_check_option_on(cf_tester_mode)) console.log(url_get_ip_info);

        // Không cho thì lấy gần đúng
        jQuery.getJSON(url_get_ip_info, function (data) {
            if (typeof data.lat == 'undefined') {
                data.lat = data.loc.split(',')[0];
            }
            if (typeof data.lon == 'undefined') {
                data.lon = data.loc.split(',')[1];
            }

            if (WGR_check_option_on(cf_tester_mode)) console.log(data);

            g_func.setc('ipinfo_to_language', JSON.stringify(data), 3600 * 2);

            if (typeof after_load == 'function') {
                after_load(data);
            }

            return data;
        });

        return {};
    },


    demo_html: function (clat, len) {
        console.log('Demo html');

        //
        jQuery('.' + clat).each(function () {
            var str = jQuery(this).html() || '',
                demo = '';

            if (str != '') {
                for (var i = 0; i < len; i++) {
                    demo += str;
                }

                jQuery(this).html(demo);
            }
        });
    },


    page404_func: function () {
        jQuery('.click-go-to-search').click(function () {
            jQuery('input[type="search"]:first').focus();
        });
    },


    cart_create_arr_poruduct: function (cart_total_price) {

        // reset lại mảng
        ebe_arr_cart_product_list = [];

        //		var cart_total_price = 0;

        // nếu đang là xem trang chi tiết
        if (pid > 0 && typeof eb_wp_post_type != 'undefined' && eb_wp_post_type == 'post') {

            //
            var color_name = jQuery('.oi_product_color li.selected').attr('title') || jQuery('.oi_product_color li:first').attr('title') || '',
                color_img = jQuery('.oi_product_color li.selected').attr('data-img') || jQuery('.oi_product_color li:first').attr('data-img') || jQuery('meta[property="og:image"]').attr('content') || '',
                sku = jQuery('.oi_product_color li.selected').attr('data-sku') || jQuery('.oi_product_color li:first').attr('data-sku') || product_js.sku || '';
            /*
            if ( color_img != '' ) {
            	color_img = ' <span data-src="' + color_img + '" class="order-img-color-product"></span>';
            }
            */

            // tạo khóa tìm kiếm để sau tìm sản phẩm được chuẩn hơn
            var cart_product_slug = g_func.non_mark_seo(product_js.tieude + sku),
                cart_quan = jQuery('#oi_change_soluong input').val() || jQuery('#oi_change_soluong select').val() || 1;

            //
            ebe_arr_cart_product_list.push({
                "id": pid,
                "name": product_js.tieude,
                "slug": cart_product_slug.replace(/\-/g, ''),
                "size": jQuery('.oi_product_size li.selected').attr('data-name') || jQuery('.oi_product_size li:first').attr('data-name') || '',
                //				"color" : color_name + color_img,
                "color": color_name,
                "color_img": color_img,
                "old_price": product_js.gia,
                "price": product_js.gm,
                // thêm phần giá riêng theo màu hoặc size
                "child_price": price_for_quick_cart,
                "quan": cart_quan,
                "sku": sku
            });

            //
            jQuery('.eb-global-frm-cart input[name^=t_new_price]').val(price_for_quick_cart);
            //			cart_total_price = price_for_quick_cart * cart_quan;
        }
        // nếu đang là xem trong giỏ hàng
        else {
            // xóa phần giá riêng trong giỏ hàng
            jQuery('.gia-rieng-size-color').remove();

            //
            jQuery('.each-for-set-cart-value').each(function () {
                var cart_pid = jQuery(this).attr('data-id') || 0,
                    cart_product_name = jQuery('.get-product-name-for-cart', this).html() || '',
                    cart_product_slug = '',
                    sku = jQuery('.show-list-color[data-id="' + cart_pid + '"] select').find(':selected').attr('data-sku') || jQuery(this).attr('data-sku') || '',
                    gia_moi = jQuery(this).attr('data-price') || 0,
                    gia_rieng = jQuery('.cart-price-inline .global-details-giamoi', this).html() || '',
                    cart_quan = jQuery('.change-select-quantity', this).val() || 1;

                // xử lý giá riêng
                if (gia_rieng != '') {
                    gia_rieng = g_func.number_only(gia_rieng);
                }

                // nếu giá riêng bằng giá mới thì thôi
                if (gia_rieng * 1 == gia_moi * 1) {
                    gia_rieng = 0;
                }

                // tạo 1 input để lưu giá riêng vào submit -> gửi mail
                if (gia_rieng * 1 > 0) {
                    jQuery('.show-list-size', this).append('<input type="hidden" name="t_new_price[' + cart_pid + ']" value="' + gia_rieng + '" class="gia-rieng-size-color" />');
                }

                // tạo khóa tìm kiếm để sau tìm sản phẩm được chuẩn hơn
                cart_product_slug = g_func.non_mark_seo(cart_product_name + sku);

                //
                ebe_arr_cart_product_list.push({
                    "id": cart_pid,
                    "name": cart_product_name,
                    "slug": cart_product_slug.replace(/\-/g, ''),
                    "size": jQuery('.show-list-size[data-id="' + cart_pid + '"] select').val() || '',
                    "color": jQuery('.show-list-color[data-id="' + cart_pid + '"] select').val() || '',
                    "color_img": jQuery('.cart-list-avt', this).attr('data-img') || '',
                    "old_price": jQuery(this).attr('data-old-price') || 0,
                    "price": gia_moi,
                    // thêm phần giá riêng theo màu hoặc size
                    "child_price": gia_rieng,
                    "quan": cart_quan,
                    "sku": sku
                });

                //
                //				cart_total_price += ( gia_moi * 1 ) * ( cart_quan * 1 );
                //				cart_total_price += ( gia_rieng * 1 ) * ( cart_quan * 1 );
            });
        }
        if (WGR_check_option_on(cf_tester_mode)) console.log(ebe_arr_cart_product_list);

        //
        //		if ( dog('hd_products_info') == null ) {
        if (jQuery('#hd_products_info').length == 0) {
            jQuery('#cart_user_agent').append('<textarea name="hd_products_info" id="hd_products_info"></textarea>');
        }
        jQuery('#hd_products_info').val(escape(JSON.stringify(ebe_arr_cart_product_list)));

        //
        if (jQuery('#hd_total_price').length == 0) {
            jQuery('#cart_user_agent').append('<input type="text" name="t_total_price" id="hd_total_price" value="0" />');
        }
        if (typeof cart_total_price != 'undefined') {
            jQuery('#hd_total_price').val(cart_total_price);
        }

    },

    cart_func: function () {

        //
        _global_js_eb.cart_create_arr_poruduct();
        _global_js_eb.cart_agent();

        //
        if (typeof new_cart_auto_add_id == 'number' && new_cart_auto_add_id > 0) {
            _global_js_eb.cart_add_item(new_cart_auto_add_id);
        }

        //
        _global_js_eb.check_null_cart();
        _global_js_eb.cart_customer_cache();

        // khi thay đổi số lượng sản phẩm
        jQuery('.change-select-quantity').change(function () {
            _global_js_eb.cart_calculator();
        });

    },

    cart_discount_code: function (co_ma_giam_gia, cl) {

        if (co_ma_giam_gia != 1) {
            return false;
        }

        jQuery(cl).show();

        jQuery('.auto-check-discount-code').off('change').change(function () {
            var a = jQuery(this).val() || '',
                jd = 'oi_check_discount_code';

            //
            arr_discount_code_return = {};

            //
            $('input[name="t_discount_value"]').val('');

            //
            if (a == '') {
                $('#' + jd).hide();
                return false;
            } else if (a.length < 3) {
                console.log('DISCOUNT CODE too short!');
                return false;
            }

            //
            if (dog(jd) == null) {
                jQuery(cl).after('<div id="' + jd + '" class="small text-center l15"></div>');
            }
            jQuery('#' + jd).show();

            //
            jQuery.ajax({
                //				type: 'POST',
                type: 'GET',
                url: ajaxl_url('check_discount_code&code=' + a + '&no_echo=1'),
                data: ''
            }).done(function (msg) {
                if (typeof msg == 'object') {
                    console.log(msg);
                    arr_discount_code_return = msg;
                    WGR_check_discount_code_return(jd);
                } else {
                    jQuery('#' + jd).html('<span class="orgcolor">Lỗi cú pháp! vui lòng báo cho kỹ thuật viên. Xin cảm ơn</span>');
                }
            });
            //			ajaxl('check_discount_code&code=' + a, jd, 1 );
            //			ajaxl('check_discount_code&code=' + a, jd, 1, function() {
            //			});
            /*
		}).off('keyup').keyup(function () {
			return false;
			*/
        });

    },

    // load size và color trong giỏ hàng
    cart_user_select_size_color: function () {

        // select màu với size theo lựa chọn trước đó
        var a = g_func.getc('eb_cookie_cart_lists');
        if (a != null) {
            //			console.log(a);
            a = jQuery.parseJSON(unescape(a));
            //			console.log(a);
            return a;
        }
        return {};

    },
    cart_size_in_color: function (arr_user_select_size_color) {
        if (typeof arr_user_select_size_color != 'object') {
            arr_user_select_size_color = _global_js_eb.cart_user_select_size_color();
        }

        // Size cho phần giỏ hàng
        jQuery('.show-list-size').each(function () {
            // ưu tiên lấy zie của color trước
            //			var a = jQuery(this).attr('data-color-size') || jQuery(this).attr('data-size') || jQuery(this).html() || '',
            //			var a = jQuery(this).attr('data-color-size') || jQuery(this).attr('data-size') || '',
            var a = jQuery(this).attr('data-color-size') || '',
                t_post_id = jQuery(this).attr('data-id') || '';

            // nếu không có -> mới lấy zie của sản phẩm
            if (a == '') {
                a = jQuery(this).attr('data-size') || '';
            }

            //
            if (a != '') {
                a = jQuery.parseJSON(unescape(a));
                //				console.log(a);
                //				console.log(a.length);

                //
                if (typeof a[0] != 'undefined' && typeof a[0].name == 'undefined') {
                    //				if ( a.length == 1 && typeof a[0][0].ten != 'undefined' ) {
                    a = a[0];
                }

                var str = '',
                    data_price = '';
                for (var i = 0; i < a.length; i++) {
                    // conver từ bản code cũ sang
                    if (typeof a[i].name == 'undefined') {
                        if (typeof a[i].ten != 'undefined') {
                            a[i].name = a[i].ten;
                        } else {
                            a[i].name = '';
                        }
                    }

                    if (typeof a[i].val == 'undefined') {
                        if (typeof a[i].soluong != 'undefined') {
                            a[i].val = a[i].soluong;
                        } else {
                            a[i].val = 0;
                        }
                    } else if (a[i].val == '') {
                        a[i].val = 0;
                    }

                    //
                    if (a[i].name != '' && a[i].val >= 0) {
                        data_price = '';
                        if (a[i].price != '' && a[i].price > 0) {
                            data_price = ' data-price="' + a[i].price + '"';
                        }

                        sl = '';
                        if (typeof arr_user_select_size_color['_' + t_post_id] != 'undefined' && typeof arr_user_select_size_color['_' + t_post_id].size != 'undefined' && arr_user_select_size_color['_' + t_post_id].size == a[i].name) {
                            sl = ' selected';
                        }

                        //
                        str += '<option' + data_price + ' value="' + a[i].name + '"' + sl + '>' + a[i].name + '</option>';
                    }
                }

                //
                if (str != '') {
                    jQuery(this).show().html('<div class="lf f20">' + (jQuery(this).attr('data-name') || '') + '</div>'
                        + '<div class="lf f80"><select data-id="' + t_post_id + '" name="t_size[' + t_post_id + ']">' + str + '</select></div>');
                }
            }
        });

        //
        jQuery('.show-list-size select').off('change').change(function () {
            _global_js_eb.cart_create_arr_poruduct();
            _global_js_eb.cart_calculator();
        });

    },
    cart_size_color: function () {

        // select màu với size theo lựa chọn trước đó
        var arr_user_select_size_color = _global_js_eb.cart_user_select_size_color();
        sl = '';

        // Size cho phần giỏ hàng -> tạo dữ liệu mặc định để tí html không bị mất
        jQuery('.show-list-size').each(function () {
            var a = jQuery(this).html() || '';
            if (a != '') {
                //				console.log(a);
                if (a.substr(0, 1) == ',') {
                    a = a.substr(1);
                }
                if (a.substr(0, 1) != '[') {
                    a = "[" + a + "]";
                }
                //				console.log(a);
                try {
                    a = eval(a);
                } catch (e) {
                    a = [];
                    console.log(WGR_show_try_catch_err(e));
                }

                jQuery(this).attr({
                    'data-size': escape(JSON.stringify(a))
                });
            }
        });


        //
        jQuery('.show-list-color').each(function () {
            var t_post_id = jQuery(this).attr('data-id') || '';
            //			console.log(t_post_id);
            //			console.log(jQuery('img', this).length);

            //
            var str = '';

            jQuery('img', this).each(function () {
                var s = jQuery(this).attr('data-src') || jQuery(this).attr('src') || '';

                if (s != '') {
                    // trạng thái
                    var status = jQuery(this).attr('data-status') || 1;

                    if (status > 0) {
                        var color_name = jQuery(this).attr('alt') || jQuery(this).attr('title') || jQuery(this).attr('data-color') || '',
                            color_quan = jQuery(this).attr('data-quan') || '',
                            color_price = jQuery(this).attr('data-price') || '',
                            color_sku = jQuery(this).attr('data-sku') || '',
                            color_size = jQuery(this).attr('data-size') || '',
                            data_price = '';

                        // Để trống -> coi như còn hàng
                        if (color_quan == '') {
                            color_quan = 1;
                        }
                        if (WGR_check_option_on(cf_tester_mode)) console.log('color_quan: ' + color_quan);

                        // còn hàng thì mới hiển thị
                        if (color_quan > 0) {
                            if (color_price != '' && color_price > 0) {
                                data_price = ' data-price="' + color_price + '"';
                            }

                            sl = '';
                            if (typeof arr_user_select_size_color['_' + t_post_id] != 'undefined' && typeof arr_user_select_size_color['_' + t_post_id].color != 'undefined' && arr_user_select_size_color['_' + t_post_id].color == color_name) {
                                sl = ' selected';

                                jQuery('.show-list-size[data-id="' + t_post_id + '"]').attr({
                                    'data-color-size': color_size
                                });
                            }

                            //
                            str += '<option' + data_price + ' data-sku="' + color_sku + '" data-size="' + color_size + '" value="' + color_name + '"' + sl + '>' + color_name + '</option>';
                        }
                    }
                }
            });

            //
            if (str != '') {
                jQuery(this).show().html('<div class="lf f20">' + (jQuery(this).attr('data-name') || '') + '</div>'
                    + '<div class="lf f80"><select data-id="' + t_post_id + '" name="t_color[' + t_post_id + ']">' + str + '</select></div>');
            }

            // chọn luôn số lượng cho người dùng
            if (typeof arr_user_select_size_color['_' + t_post_id] != 'undefined' && typeof arr_user_select_size_color['_' + t_post_id].quan != 'undefined' && arr_user_select_size_color['_' + t_post_id].quan > 1) {
                jQuery('.change-select-quantity[data-name="' + t_post_id + '"]').val(arr_user_select_size_color['_' + t_post_id].quan);
            }

        });

        //
        jQuery('.show-list-color select').change(function () {
            var a = jQuery(this).attr('data-id') || '';
            if (a != '') {
                var color_size = jQuery('.show-list-color[data-id="' + a + '"] select').find(':selected').attr('data-size') || '';
                //				console.log(color_size);

                jQuery('.show-list-size[data-id="' + a + '"]').attr({
                    'data-color-size': color_size
                });

                // nạp lại size mới
                _global_js_eb.cart_size_in_color();
            }

            //
            _global_js_eb.cart_create_arr_poruduct();
            _global_js_eb.cart_calculator();
        });

        // nạp size sau color để còn lấy giá với size riêng của color
        _global_js_eb.cart_size_in_color();

        //
        _global_js_eb.cart_create_arr_poruduct();
        _global_js_eb.cart_calculator();

    },

    // tính toán lại giá trị giỏ hàng mỗi lần đổi số lượng, size, color
    cart_calculator: function () {

        // tính lại tổng tiền theo thời gian thực
        var total = 0,
            line = 0;
        jQuery('.each-for-set-cart-value').each(function () {
            // lấy giá sản phẩm, ưu tiên lấy giá theo size trước
            var gia = jQuery('.show-list-size select option:selected', this).attr('data-price') || 0,
                soluong = jQuery('.change-select-quantity', this).val() || 1;

            // nếu không có giá theo size
            if (gia <= 0) {
                // lấy giá theo màu
                gia = jQuery('.show-list-color select option:selected', this).attr('data-price') || 0;

                // lấy giá sản phẩm hiện tại nếu không có giá theo size hoặc màu
                if (gia <= 0) {
                    gia = jQuery(this).attr('data-price') || 0;
                }
            }

            // tổng tiền trên mỗi dòng
            line = gia * soluong;
            jQuery('.cart-price-inline .global-details-giamoi', this).html(g_func.money_format(gia));
            jQuery('.cart-total-inline .global-details-giamoi', this).html(g_func.money_format(line));

            // tính tổng tiền
            total -= 0 - line;
        });
        jQuery('.cart-table-total .global-details-giamoi').html(g_func.money_format(total));

        // cập nhật lại thông số cho cả giỏ hàng
        _global_js_eb.cart_create_arr_poruduct();

    },

    check_null_cart: function () {
        if (typeof pid == 'number' && pid > 0) {
            return true;
        }

        var a = jQuery('#oi_cart .each-for-set-cart-value').length || jQuery('.cart-count-tr tr').length || 0;
        //		console.log(a);

        // Nếu có sản phẩm trong giỏ hàng (bỏ đi tr đầu tiên)
        //		if ( a > 1 ) {
        if (a > 0) {
            jQuery('#cart_null').hide();
            jQuery('#oi_cart, #oi_send_invoice').fadeIn();

            return true;
        } else {
            jQuery('#oi_cart, #oi_send_invoice').hide();
            jQuery('#cart_null').fadeIn();
        }

        //
        return false;
    },

    check_size_color_cart: function () {

        // size tron trang chi tiết
        if (pid == 0) {
            return true;
        }


        // chọn size
        if (arr_product_size.length > 0 && jQuery('#cart_user_agent input[name^=t_size]').val() == '') {
            a_lert('Vui lòng chọn Kích cỡ sản phẩm bạn muốn mua');

            jQuery('body,html').animate({
                scrollTop: jQuery('.oi_product_size').offset().top - 110
            }, 800);

            return false;
        }

        // chọn màu
        if (arr_product_color.length > 0 && jQuery('#cart_user_agent input[name^=t_color]').val() == '') {
            a_lert('Vui lòng chọn Màu sắc sản phẩm bạn muốn mua');

            jQuery('body,html').animate({
                scrollTop: jQuery('.oi_product_color').offset().top - 110
            }, 800);

            return false;
        }

        //
        return true;
    },

    check_cart: function () {
        // nếu trong chi tiết sản phẩm -> sẽ kiểm tra sản phẩm có đang bị đánh dấu hết hàng không
        if (typeof pid != 'undefined' && pid > 0) {
            if (product_post_status != 'publish') {
                a_lert('Xin lỗi quý khách! Sản phẩm hiện đang ngừng bán');
                return false;
            } else if (product_trv_trangthai * 1 === 7) {
                a_lert('Xin lỗi quý khách! Sản phẩm hiện đang hết hàng');
                return false;
            }
        }

        //
        if (sb_submit_cart_disabled == 1) {
            console.log('wating cart submit');
            return false;
        }
        console.log('wating cart submit');

        // nếu giỏ hàng trống -> bỏ qua
        if (_global_js_eb.check_null_cart() == false) {
            a_lert('Vui lòng chọn sản phẩm trước khi tiếp tục');
            return false;
        }

        //
        _global_js_eb.wgr_nonce('frm_cart');
        _global_js_eb.add_primari_iframe();

        //
        var f = document.frm_cart;

        // nếu chưa chọn màu hoặc size -> yêu cầu chọn
        if (_global_js_eb.check_size_color_cart() == false) {
            return false;
        }

        // loại bỏ mã HTML có trong nội dung -> tránh mọi người nhập liệu vớ vẩn
        if (f.t_ten.value != '') f.t_ten.value = g_func.strip_tags(f.t_ten.value);
        if (f.t_dienthoai.value != '') f.t_dienthoai.value = g_func.strip_tags(f.t_dienthoai.value);
        if (f.t_email.value != '') f.t_email.value = g_func.strip_tags(f.t_email.value);
        if (f.t_diachi.value != '') f.t_diachi.value = g_func.strip_tags(f.t_diachi.value);
        if (f.t_ghichu.value != '') f.t_ghichu.value = g_func.strip_tags(f.t_ghichu.value);

        // -> sử dụng form động để loại bỏ mã HTML
        /*
        jQuery('form[name="frm_cart"] input[type="text"], form[name="frm_cart"] textarea').each(function() {
        	if ( jQuery(this).val() != '' ) {
        		jQuery(this).val() = g_func.strip_tags( jQuery(this).val() );
        	}
        });
        */

        //
        var check_phone_number = g_func.number_only(f.t_dienthoai.value);

        if (check_phone_number.toString().length < 9) {
            a_lert('Vui lòng nhập ít nhất một số điện thoại bạn đang sử dụng');
            f.t_dienthoai.focus();
            return false;
        }

        if (_global_js_eb.check_email(f.t_email.value) == false) {
            var new_email = g_func.non_mark_seo(f.t_dienthoai.value);
            try {
                new_email += '@' + web_link.split('//')[1].split('/')[0].replace('www.', '').replace(/\:/g, '.');
            } catch (e) {
                console.log(WGR_show_try_catch_err(e));
            }
            f.t_email.value = new_email;

            if (_global_js_eb.check_email(f.t_email.value) == false) {
                a_lert('Email không đúng định dạng');
                f.t_email.focus();
                return false;
            }
        }

        if (f.t_diachi.value.replace(/\s/g, '') == '') {
            f.t_diachi.value = f.t_dienthoai.value;
        }

        //		_global_js_eb.cart_create_arr_poruduct();
        _global_js_eb.cart_agent();
        //		return false;

        // lưu thông tin khách hàng
        g_func.setc('eb_cookie_cart_name', f.t_ten.value, 0, 7);
        g_func.setc('eb_cookie_cart_phone', f.t_dienthoai.value, 0, 7);
        g_func.setc('eb_cookie_cart_email', f.t_email.value, 0, 7);
        g_func.setc('eb_cookie_cart_address', f.t_diachi.value, 0, 7);

        //
        jQuery('body').css({
            opacity: 0.2
        });

        // không cho submit đơn liên tục
        //		f.sb_submit_cart.disabled = true;
        sb_submit_cart_disabled = 1;

        // khi load xong sẽ cho submit trở lại
        jQuery('#target_eb_iframe').on('load', function () {
            jQuery('rME').css({
                opacity: 1
            });


            //
            //			f.sb_submit_cart.disabled = false;
            sb_submit_cart_disabled = 0;
        });

        // hoặc load lâu quá -> cũng cho load trở lại
        setTimeout(function () {
            sb_submit_cart_disabled = 0;
        }, 5000);

        //
        _global_js_eb.ga_event_track('', '', '', {
            //			'category' : '',
            //			'label' : '',
            'action': 'checkout_progress'
        });

        //
        return true;
    },

    cart_add_item_v2: function (new_cart_id, action_obj) {

        //
        if (typeof g_func.number_only(new_cart_id) != 'number') {
            a_lert(lang_cart_post_null);
            return false;
        }

        //
        if (typeof action_obj != 'object') {
            action_obj = {};
        }

        var remove_item = 0;
        if (typeof action_obj.remove_item != 'undefined') {
            remove_item = action_obj.remove_item;
        }
        //		console.log(remove_item);

        var tr_id = '';
        if (typeof action_obj.tr_id != 'undefined') {
            tr_id = action_obj.tr_id;
        }

        var c = 'eb_cookie_cart_list_id',
            cart_id_in_cookie = g_func.getc(c),
            c_arr = 'eb_cookie_cart_list_arr',
            cart_arr_in_cookie = g_func.getc(c_arr),
            list_cart_id = '';
        console.log(cart_id_in_cookie);
        console.log(cart_arr_in_cookie);

        //
        if (cart_id_in_cookie == null) {
            cart_id_in_cookie = '';
        }
        console.log(cart_id_in_cookie);

        //
        if (cart_arr_in_cookie == null) {
            cart_arr_in_cookie = [];
        } else {
            try {
                cart_arr_in_cookie = jQuery.parseJSON(cart_arr_in_cookie);
            } catch (e) {
                console.log('ERROR conver cart in cookie');
                console.log(WGR_show_try_catch_err(e));
                cart_arr_in_cookie = [];
            }
        }
        console.log(cart_arr_in_cookie);


        // xóa khỏi giỏ hàng
        if (remove_item == 1) {
            if (confirm(lang_cart_confirm_remove) == false) {
                return false;
            }

            //
            _global_js_eb.ga_event_track('', '', '', {
                //				'category' : '',
                //				'label' : '',
                'action': 'remove_from_cart'
            });


            //
            for (var i = 0; i < cart_arr_in_cookie.length; i++) {
                if (cart_arr_in_cookie[i] == null || cart_arr_in_cookie[i].id == new_cart_id) {
                    cart_arr_in_cookie[i] = null;
                } else {
                    list_cart_id += ',' + cart_arr_in_cookie[i].id;
                }
            }
            //			console.log(list_cart_id);
            g_func.setc(c, list_cart_id, 0, 7);

            //			console.log(list_cart_id);
            g_func.setc(c_arr, JSON.stringify(cart_arr_in_cookie), 0, 7);

            //
            if (tr_id != '' && dog(tr_id) != null) {
                jQuery('#' + tr_id).fadeOut().remove();
            }

            //
            _global_js_eb.check_null_cart();

            console.log('Remove cart (' + new_cart_id + ')');
            return false;
        }


        // add to cart
        var check_cart_exist = 0;
        for (var i = 0; i < cart_arr_in_cookie.length; i++) {
            if (cart_arr_in_cookie[i].id == new_cart_id) {
                console.log('Cart exist (' + new_cart_id + ')');
                check_cart_exist = 1;
                break;
            }
        }

        //
        if (check_cart_exist == 0) {
            console.log('Add to cart (' + new_cart_id + ')');

            cart_arr_in_cookie.push({
                id: new_cart_id,
                quan: 1
            });

            list_cart_id += ',' + new_cart_id;

            //
            console.log('Save cart (' + new_cart_id + ')');

            g_func.setc(c, list_cart_id, 0, 7);
            g_func.setc(c_arr, JSON.stringify(cart_arr_in_cookie), 0, 7);
        }
        console.log(list_cart_id);
        console.log(cart_arr_in_cookie);
        console.log(JSON.stringify(cart_arr_in_cookie));


        // thời gian chuyển sang trang cart -> để mấy cái pixel tải xong đã
        var time_to_cart = 600;


        // google tracking add to cart -> Sử dụng chức năng Mục tiêu trong google analytics
        /*
        if ( _global_js_eb.gg_track( web_link + 'cart/?id=' + new_cart_id ) == true ) {
        	time_to_cart = 1200;
        }
        */
        setTimeout(function () {
            //
            //			_global_js_eb.gg_track( web_link + 'cart/?id=' + new_cart_id );

            //
            _global_js_eb.ga_event_track('Add to cart', 'Product ' + new_cart_id, '', {
                //				'category' : '',
                //				'label' : '',
                //				'action' : 'add_to_cart'
                'action': 'begin_checkout'
            });
        }, 200);


        // facebook tracking add to cart
        var track_arr = {
            //			'content_name' : '',
            //			'content_category' : '',
            'content_ids': [new_cart_id]
            //			'content_type' : 'product'
        };

        if (typeof action_obj.price != 'undefined' && action_obj.price > 0) {
            track_arr.value = action_obj.price;
            track_arr.currency = cf_current_sd_price;
        }

        //
        if (_global_js_eb.fb_track('AddToCart', track_arr) == true) {
            time_to_cart = 1200;
        }


        // nếu có ID tự động add
        if (typeof new_cart_auto_add_id != 'undefined') {
            console.log('Cart auto add');
        }
        // hoặc chuyển tới trang giỏ hàng
        else {
            console.log('Waiting redirect to cart');

            //
            setTimeout(function () {
                window.location = web_link + 'cart/';
                //					window.location = web_link + 'cart/?id=' + new_cart_id;
            }, time_to_cart);
        }

    },

    cart_add_item: function (new_cart_id, action_obj) {
        console.log('Add ' + new_cart_id + ' to cart with obj: ');
        console.log(action_obj);

        //
        //		if ( typeof g_func.number_only( new_cart_id ) != 'number' ) {
        if (g_func.number_only(new_cart_id) * 1 <= 0) {
            a_lert(lang_cart_post_null);
            return false;
        }

        //
        if (typeof action_obj != 'object') {
            action_obj = {};
        }

        //
        var remove_item = 0;
        if (typeof action_obj.remove_item != 'undefined') {
            remove_item = action_obj.remove_item;
        }
        //		console.log(remove_item);

        var tr_id = '';
        if (typeof action_obj.tr_id != 'undefined') {
            tr_id = action_obj.tr_id;
        }

        var c = 'eb_cookie_cart_list_id',
            cart_id_in_cookie = g_func.getc(c),
            add_cart_id = ',' + new_cart_id,
            list_cart_id = '';

        list_cart_id = (cart_id_in_cookie == null) ? '' : cart_id_in_cookie;
        console.log(list_cart_id);
        //		console.log(add_cart_id);

        // xóa khỏi giỏ hàng
        if (remove_item == 1) {
            if (confirm(lang_cart_confirm_remove) == false) {
                return false;
            }

            //
            _global_js_eb.ga_event_track('', '', '', {
                //				'category' : '',
                //				'label' : '',
                'action': 'remove_from_cart'
            });


            // v1
            /*
            if ( list_cart_id != '' ) {
            	list_cart_id = list_cart_id.replace( add_cart_id, '' );
            	console.log(list_cart_id);
            	
            	if ( list_cart_id == '' ) {
            		g_func.delck( c );
            	} else {
            		g_func.setc( c, list_cart_id, 0, 7 );
            	}
            }
            */

            //
            if (tr_id != '' && dog(tr_id) != null) {

                // xóa TR tương ứng đi
                jQuery('#' + tr_id).fadeOut().remove();

                // tính lại tổng tiền
                var total = 0,
                    list_cart_id = '';
                jQuery('.each-for-set-cart-value').each(function () {
                    var gia = jQuery(this).attr('data-price') || 0,
                        soluong = jQuery('.change-select-quantity', this).val() || 1,
                        post_id = jQuery(this).attr('data-id') || 0;

                    total -= 0 - (gia * soluong);

                    if (post_id > 0) {
                        list_cart_id += ',' + post_id;
                    }
                });
                jQuery('.cart-table-total .global-details-giamoi').html(g_func.money_format(total));

                // lưu giỏ hàng mới
                //				console.log(list_cart_id);
                if (list_cart_id == '') {
                    g_func.delck(c);
                } else {
                    g_func.setc(c, list_cart_id, 0, 7);
                }
                //				console.log( g_func.getc( c ) );

            }

            //
            _global_js_eb.check_null_cart();

            console.log('Remove cart (' + new_cart_id + ')');

            return false;
        }
        //		console.log(list_cart_id);


        // thêm vào giỏ hàng
        if (list_cart_id == '' || list_cart_id.split(add_cart_id).length == 1) {
            list_cart_id += add_cart_id.toString();
            g_func.setc(c, list_cart_id, 0, 7);

            console.log('Save cart (' + new_cart_id + ')');
        } else {
            console.log('Cart exist (' + new_cart_id + ')');
        }
        //		return false;


        // thời gian chuyển sang trang cart -> để mấy cái pixel tải xong đã
        var time_to_cart = 600;


        // google tracking add to cart -> Sử dụng chức năng Mục tiêu trong google analytics
        /*
        if ( _global_js_eb.gg_track( web_link + 'cart/?id=' + new_cart_id ) == true ) {
        	time_to_cart = 1200;
        }
        */
        setTimeout(function () {
            //			_global_js_eb.gg_track( web_link + 'cart/?id=' + new_cart_id );
            _global_js_eb.ga_event_track('Add to cart', 'Product ' + new_cart_id, '', {
                //				'category' : '',
                //				'label' : '',
                //				'action' : 'add_to_cart'
                'action': 'begin_checkout'
            });
        }, 200);


        // facebook tracking add to cart
        var track_arr = {
            //			'content_name' : '',
            //			'content_category' : '',
            'content_ids': [new_cart_id]
            //			'content_type' : 'product'
        };

        if (typeof action_obj.price != 'undefined' && action_obj.price > 0) {
            track_arr.value = action_obj.price;
            track_arr.currency = cf_current_sd_price;
        }

        //
        if (_global_js_eb.fb_track('AddToCart', track_arr) == true) {
            time_to_cart = 1200;
        }


        // nếu có ID tự động add
        if (typeof new_cart_auto_add_id != 'undefined') {
            console.log('Cart auto add');
            return false;
        }


        // chuyển tới trang giỏ hàng
        console.log('Waiting redirect to cart');

        //
        setTimeout(function () {

            //
            if (top != self) {
                parent.window.location = web_link + 'cart/';
            }
            // Hiển thị bình thường
            else {
                // mở trong iframe
                if (typeof action_obj.open_iframe != 'undefined' && action_obj.open_iframe == 1) {
                    // nạp iframe
                    _global_js_eb.add_primari_iframe();

                    //
                    window.open(web_link + 'cart/', 'target_eb_iframe');
                }
                // mở trong cửa sổ hiện tại
                else {
                    window.location = web_link + 'cart/';
                    //					window.location = web_link + 'cart/?id=' + new_cart_id;
                }
            }

            //
            add_to_cart_running = false;

        }, time_to_cart);

        /*
        _global_js_eb.cart_func.pro_add(pid, {
        	size: (jQuery('#oi_product_size li.selected').attr('data-id') || '')
        });
        */
    },

    cart_remove_item: function (remove_id, tr_id) {
        //		alert(remove_id);
        _global_js_eb.cart_add_item(remove_id, {
            'remove_item': 1,
            'tr_id': tr_id
        });
    },

    cpl_cart: function (my_hd_id, my_hd_mahoadon, my_message) {
        //		alert(my_hd_id); alert(my_hd_mahoadon); return false;

        // xóa cookie giỏ hàng
        g_func.delck('eb_cookie_cart_list_id');
        g_func.delck('eb_cookie_cart_lists');

        //
        if (typeof my_hd_id == 'undefined' || typeof my_hd_mahoadon == 'undefined') {
            console.log('Lỗi chốt dữ liệu');
            return false;
        }

        // lưu thông tin đơn hàng để gửi đi
        g_func.setc('eb_cookie_order_id', my_hd_id, 0, 7);
        //		g_func.setc( 'eb_cookie_order_sku', my_hd_mahoadon, 0, 7 );

        //
        //		if ( typeof my_message != 'undefined' && my_message != '' ) alert( my_message );

        //
        window.location = web_link + 'hoan-tat';
    },

    // nạp thông tin khách hàng cho giỏ hàng -> từ cookies
    cart_customer_cache: function (f) {

        //		return false;

        // nếu không có form truyền vào -> tìm form mặc định
        if (typeof f == 'undefined') {
            // nếu frm mặc định cũng không có -> lỗi
            if (typeof document.frm_cart == 'undefined') {
                return false;
            }

            f = document.frm_cart;
        }

        //
        var a = function (c) {
            c = g_func.getc(c);
            return c == null ? '' : c;
        };
        //		console.log(a);
        //		return false;

        if (typeof f.t_ten != 'undefined') f.t_ten.value = a('eb_cookie_cart_name');
        if (typeof f.t_dienthoai != 'undefined') f.t_dienthoai.value = a('eb_cookie_cart_phone');
        if (typeof f.t_email != 'undefined') f.t_email.value = a('eb_cookie_cart_email');
        if (typeof f.t_diachi != 'undefined') f.t_diachi.value = a('eb_cookie_cart_address');
    },

    // google tracking
    // https://support.google.com/adwords/answer/6331304?&hl=vi
    gg_track: function (url) {
        if (typeof url == 'undefined'
            //		|| typeof goog_report_conversion != 'function'
            || url == '') {
            return false;
        }
        console.log('Google tracking (' + url + ') by ' + private_info_setting_site_upper);

        //
        if (typeof goog_report_conversion == 'function') {
            goog_report_conversion(url);
        }

        //
        return true;
    },

    // google analytics tracking
    // https://developers.google.com/analytics/devguides/collection/analyticsjs/events
    // https://developers.google.com/analytics/devguides/collection/gtagjs/events
    // https://developers.google.com/analytics/devguides/collection/gtagjs/enhanced-ecommerce
    ga_event_track: function (eventCategory, eventAction, eventLabel, ops, max_for) {

        // không track đối với người dùng đã đăng nhập
        if (isLogin > 0 && WGR_check_option_on(cf_disable_tracking)) {
            console.log('ga_event_track disable by user login');
            return false;
        }

        //
        /*
        if ( typeof goog_report_conversion == 'undefined' ) {
        	return false;
        }
        */

        // Mảng dành cho các option nâng cao khác
        if (typeof ops != 'object') {
            ops = {};
        }

        if (typeof eventCategory == 'undefined' || eventCategory == '') {
            if (typeof ops['action'] != 'undefined' && ops['action'] != '') {
                eventCategory = ops['action'];
            } else {
                eventCategory = 'Null Category';
            }
        }

        if (typeof eventAction == 'undefined' || eventAction == '') {
            if (typeof ops['action'] != 'undefined' && ops['action'] != '') {
                eventAction = ops['action'];
            } else {
                eventAction = 'Null Action';
            }
        }

        if (typeof eventLabel == 'undefined' || eventLabel == '') {
            eventLabel = document.title;
        }

        // ưu tiên gtag
        if (WGR_check_option_on(cf_gtag_id) && typeof gtag == 'function') {
            if (typeof ops['action'] == 'undefined') {
                ops['action'] = eventAction;
            }
            if (typeof ops['category'] == 'undefined') {
                ops['category'] = eventCategory + ' (EBv2)';
            }
            if (typeof ops['label'] == 'undefined') {
                ops['label'] = eventLabel;
            }
            console.log(ops);

            //
            var para = {
                'event_category': ops['category'],
                'event_label': ops['label']
            };

            //
            if (typeof ops['items'] == 'undefined') {
                para['items'] = ops['items'];
            }
            console.log(para);

            //
            gtag('event', ops['action'], para);
            console.log('Google analytics (gtag) event tracking (' + eventAction + ') by ' + private_info_setting_site_upper);
        }
        // rồi đến ga
        else if (typeof ga == 'function') {
            ga('send', 'event', eventCategory + ' (EB)', eventAction, eventLabel);
            console.log('Google analytics event tracking (' + eventAction + ') by ' + private_info_setting_site_upper);
        } else {
            //		if ( typeof ga != 'function' ) {
            if (typeof max_for == "undefined") {
                max_for = 20;
            }

            // nạp lại track này lần nữa (do fbq thường load chậm hơn website)
            if (max_for > 0) {
                // từ lần lặp cuối, cho phép tracking qua cả gtag
                if (max_for < 5) {
                    cf_gtag_id = 1;
                }

                //
                setTimeout(function () {
                    _global_js_eb.ga_event_track(eventCategory, eventAction, eventLabel, ops, max_for - 1);
                }, 500);
                //				console.log( 'Re-load GG tracking (' + max_for + ')...' );

                return false;
            }

            // 
            console.log('Max for GG track: ' + max_for);
            return false;
        }

        //
        return true;
    },

    // facebook dynamic remarketing
    // https://developers.facebook.com/docs/marketing-api/facebook-pixel/v2.8
    // https://developers.facebook.com/docs/facebook-pixel/implementation/marketing-api
    fb_track: function (track_name, track_arr, max_for) {
        //
        //		console.log('aaaaaaaaa');

        if (WGR_check_option_on(cf_facebook_tracking) == false) {
            console.log('fb_track has been disable!');
            return false;
        }

        // Không chạy trong iframe
        if (top != self) {
            console.log('fb_track not run in iframe');
            return false;
        }

        // không track đối với người dùng đã đăng nhập
        if (isLogin > 0 && WGR_check_option_on(cf_disable_tracking)) {
            console.log('fb_track disable by user login');
            return false;
        }

        // không có tên sự kiện cũng thoát
        if (typeof track_name == 'undefined' || track_name == '') {
            console.log('track_name not found');
            return false;
        }

        //
        if (typeof track_arr != 'object') {
            track_arr = {};
        } else {
            // mặc định type = product
            if (typeof track_arr.content_type == 'undefined' || track_arr.content_type == '') {
                track_arr.content_type = 'product';
            }
        }

        // nếu fb chưa được nạp -> thử kiểm tra và chờ load lại
        if (typeof fbq == 'undefined') {
            if (typeof max_for == "undefined") {
                max_for = 60;
            }

            // nạp lại track này lần nữa (do fbq thường load chậm hơn website)
            if (max_for > 0) {
                setTimeout(function () {
                    _global_js_eb.fb_track(track_name, track_arr, max_for - 1);
                }, 500);
                console.log( 'Re-load FB tracking (' + max_for + ')...' );

                return false;
            }

            // 
            console.log('Max for FB track: ' + max_for);
            return false;
        }

        // kiểm tra độ chuẩn của track
        if ((function (a) {
                a = a.toLowerCase();

                if (a == 'purchase') {
                    //if (track_arr['content_type'] == 'undefined' || track_arr['content_ids'] == 'undefined' || track_arr['content_ids'].length == 0) {
                    if (track_arr['content_type'] == 'undefined' || track_arr['contents'] == 'undefined' || track_arr['contents'].length == 0) {
                        return false;
                    }
                } else if (a == 'addtocart') {
                    if (track_arr['content_type'] == 'undefined' || track_arr['content_ids'] == 'undefined' || track_arr['content_ids'].length == 0) {
                        return false;
                    }
                } else if (a == 'viewcontent') {
                    if (track_arr['content_type'] == 'undefined') {
                        return false;
                    }
                }

                //
                return true;
            })(track_name) == false) {
            console.log('Facebook pixel tracking (' + track_name + ') disable by parameter is NULL');
            console.log(track_arr);
            return false;
        }

        //
        fbq('track', track_name, track_arr);

        //
        console.log('%c Facebook pixel tracking (' + track_name + ') by ' + private_info_setting_site_upper, 'color: green;');
        console.log(track_arr);

        //
        return true;
    },

    // lưu log phiên làm việc vào ga luôn
    ga_event_log: function (eventCategory, eventAction, eventLabel) {
        if (typeof eventAction == 'undefined' || eventAction == '') {
            eventAction = 'staff';
            if (typeof mtv_id != 'undefined') {
                eventAction += mtv_id;
            } else if (typeof isLogin != 'undefined') {
                eventAction += isLogin;
            }
        }

        //
        try {
            _global_js_eb.ga_event_track(eventCategory, eventAction, eventLabel);
        } catch (e) {
            console.log(WGR_show_try_catch_err(e));
        }
    },


    /*
     * Nạp iframe để submit
     */
    add_primari_iframe: function () {
        if (dog('target_eb_iframe') == null) {
            jQuery('body').append('<iframe id="target_eb_iframe" name="target_eb_iframe" src="about:blank" width="750" height="600">AJAX form</iframe>');
        }

        return true;
    },


    // thay thế một giá trị nhất định trên URL
    change_url_tab: function (parameter, new_value) {
        if (typeof parameter == 'undefined' || parameter == '') {
            console.log('parameter not found');
            return false;
        }
        parameter = '&' + parameter + '=';

        // nếu không có giá trị gì thì hủy luôn
        if (typeof new_value == 'undefined' || new_value == '') {
            window.history.pushState("", document.title, window.location.href.split(parameter)[0]);
            return true;
        }

        //
        var str = '',
            current_url = window.location.href.split(parameter);
        //		console.log( current_url.length );
        //		console.log( current_url );

        // nếu có giá trị sẵn -> thay thế
        if (current_url.length > 1) {
            var new_url = current_url[1].split('&');
            //			console.log( new_url );
            new_url[0] = new_value;
            //			console.log( new_url );

            // ghép giá trị mới
            current_url[1] = new_url.join('&');

            // nối lại url sau khi cắt ghép
            str = current_url.join(parameter);
        }
        // nếu chưa có -> thêm mới
        else {
            str = current_url[0] + parameter + new_value;
        }
        //		console.log( str );
        window.history.pushState("", '', str);

        return true;
    },

    // thêm mã xác nhận mỗi khi submit form
    wgr_nonce: function (form_name) {
        if (jQuery('form[name="' + form_name + '"] input[name="__wgr_request_from"]').length == 0) {
            jQuery('form[name="' + form_name + '"]').append('<input type="hidden" name="__wgr_request_from" value="' + window.location.href + '" />');
        }

        if (jQuery('form[name="' + form_name + '"] input[name="__wgr_nonce"]').length == 0) {
            if (typeof date_time == 'number') {
                jQuery('form[name="' + form_name + '"]').append('<input type="hidden" name="__wgr_nonce" value="' + date_time + '" />');
            }
        }

        return true;
    },
    check_register: function (f) {
        if (typeof f == 'undefined') {
            f = 'frm_dangky';
        }

        _global_js_eb.wgr_nonce(f);

        return _global_js_eb.add_primari_iframe();
    },
    check_quick_register: function (form_name) {
        // form mặc địch, thủ công nhưng cũng có tí chuẩn
        if (form_name == 'frm_dk_nhantin') {
            var f = document.frm_dk_nhantin;

            if (f.t_hoten.value != '') {
                f.t_hoten.value = g_func.strip_tags(f.t_hoten.value);
            }
            if (f.t_dienthoai.value != '') {
                f.t_dienthoai.value = g_func.strip_tags(f.t_dienthoai.value);
            }
            if (f.t_email.value != '') {
                f.t_email.value = g_func.strip_tags(f.t_email.value);
            }
        }
        // với các form tên khác -> sử dụng form động luôn
        else {
            jQuery('form[name="' + form_name + '"] input[type="text"], form[name="' + form_name + '"] textarea').each(function () {
                if (jQuery(this).val() != '') {
                    jQuery(this).val(g_func.strip_tags(jQuery(this).val()));
                }
            });
        }

        _global_js_eb.wgr_nonce(form_name);

        return _global_js_eb.add_primari_iframe();
        /*
	},
	
	
	_eb_code64 : function ( str, type ) {
		*/
    },

    // giả lập GET của PHP
    _get: function (p) {
        var wl = window.location.href.replace(/\&amp\;/g, '&').replace(/\?/g, '&'),
            a = wl.split('&' + p + '='),
            s = '';
        if (a.length > 1) {
            s = a[1].split('&')[0].split('#')[0];
        }
        return s;
    },


    ebe_currency_format: function () {
        // hỗ trợ chuyển đổi đơn vị tiền tệ nếu to quá
        var mot_ty = 1000000000;
        var mot_trieu = 1000000;
        var conver_to_trieu = false;
        if (typeof cf_big_price_before != 'undefined' && WGR_check_option_on(cf_big_price_before)) {
            conver_to_trieu = true;
        }
        jQuery('.ebe-currency-format').each(function () {
            var a = jQuery(this).attr('data-num') || jQuery(this).html() || '';

            //if (a != '' && a != '0') {
            if (a != '') {
                a *= 1;
                if (a > 0) {
                    var b = 0;
                    
                    //
                    if (conver_to_trieu == true) {
                        // nếu lớn hơn 1 tỷ -> tính theo đơn vị tỷ
                        if (a > mot_ty) {
                            // làm tròn theo đơn vị tỷ
                            if (a % mot_ty == 0) {
                                a = a/ mot_ty;
                                jQuery(this).addClass('convert-to-ty');
                            }
                            // làm tròn theo đơn vị triệu
                            else if (a % mot_trieu == 0) {
                                a = a/ mot_trieu;
                                jQuery(this).addClass('convert-to-trieu');
                                
                                // gán b để chuyển đổi sang tỷ
                                b = a;
                            }
                        }
                        else if (a > mot_trieu) {
                            // làm tròn theo đơn vị triệu
                            if (a % mot_trieu == 0) {
                                a = a/ mot_trieu;
                                jQuery(this).addClass('convert-to-trieu');
                            }
                        }
                    }
                    
                    // trường hợp số tiền > mot_ty và không tròn số
                    if (b > 0) {
                        // tính phần tỷ
                        a = b - (b % 1000);
                        a = a/ 1000;
                        
                        // tính phần triệu
                        b = b % 1000;
                        
                        // in ra
                        jQuery(this).html('<span class="ebe-currency convert-to-ty">' + g_func.money_format(a) + '</span> ' + b);
                    }
                    // còn lại sẽ in bình thường
                    else {
                        jQuery(this).html(g_func.money_format(a));
                    }
                }
            }
        }).removeClass('ebe-currency-format');
    }

};

//
//var ___eb_for_wp = _global_js_eb.add_primari_iframe;

