var arr_ti_le_global = {};

function ti_le_global() {
    var new_arr_ti_le_global = {};
    jQuery('.ti-le-global').each(function () {
        var a = jQuery(this).width(),
            // hiển thị size ảnh gợi ý cho admin
            show_height = 0,
            // tỉ lệ kích thước giữa chiều cao và rộng (nếu có), mặc định là 1x1
            // -> nhập vào là: chiều cao/ chiều rộng
            new_size = jQuery(this).attr('data-size') || '';

        var pading_size = 'ty-le-h100';
        show_height = a;
        // Tính toán chiều cao mới dựa trên chiều rộng
        if (new_size != '') {
            if (new_size.split('x').length > 1 || new_size.split('*').length > 1) {
                new_size.split('x').split('*');
                new_size = new_size[1] + '/' + new_size[0];
            }
            pading_size = 'ty-le-h' + new_size.replace(/\/|\./gi, '_');

            //
            //				a *= new_size;

            // v2 -> tính padding theo chiều rộng
            a = eval(new_size);
            show_height *= a;

            // v1 -> tính chiều cao theo chiều rộng
            //a *= eval(new_size);
            //a += 1;
        }
        // mặc định thì cho = 1 -> 100%
        else {
            a = 1;
        }
        // Mặc định là 1x1 -> chiều cao = chiều rộng
        //				else {
        //				}
        //console.log(pading_size);
        //console.log(a);

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
}
//ti_le_global();

//
/*
console.log('%c TEST each-to-bgimg', 'color: red;');
$('.each-to-bgimg').each(function () {
    var img = $(this).attr('data-img') || '';

    if (img != '') {
        $(this).css({
            'background-image': 'url(\'' + img + '\')'
        });
    }
});
*/


//
$('.click-mobile-nav').click(function () {
    $('body').toggleClass('mobile-nav-active');
});

// hiển thị trước hình ảnh cho màn hình đầu tiên
_global_js_eb.ebBgLazzyLoad();
_global_js_eb.auto_margin();

// khi document đã load xong
jQuery(document).ready(function () {
    // chiều cao của document đủ lớn
    /*
    if (jQuery(document).height() > jQuery(window).height() * 1.5) {
    }
    */
    setInterval(function () {
        WGR_show_or_hide_to_top();
    }, 250);

    //
    if (height_for_lazzy_load == 0) {
        height_for_lazzy_load = jQuery(window).height();
    }
});

//
jQuery(window).resize(function () {
    _global_js_eb.auto_margin();
});
