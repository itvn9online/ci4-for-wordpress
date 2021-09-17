//
$('.click-mobile-nav').click(function () {
    $('body').toggleClass('mobile-nav-active');
});


// xóa href cho các thẻ a không sử dụng
$('a[href="#"], a[href="javascript:;"]').removeAttr('href').attr({
    'rel': 'noreferrer'
});

// khi bấm nút đăng xuất
$('a[href="users/logout"], a[href="./users/logout"]').click(function () {
    return confirm('Xác nhận Đăng xuất khỏi tài khoản!');
});

/*
 * tạo hiệu ứng selected cho các thẻ a
 */
(function (a) {
    $('a[href="' + a + '"]').addClass('active-menu-item');
    console.log(a);

    //
    var base_url = $('base').attr('href') || '';
    if (base_url != '') {
        a = a.replace(base_url, '');
        console.log(a);
        $('a[href="' + a + '"], a[href="./' + a + '"]').addClass('active-menu-item');
    }
})(window.location.href);

// tạo active cho li con
$('.sub-menu a.active-menu-item').addClass('active').parent('li').addClass('current-menu-item');

// tạo active cho li cha
$('ul li.current-menu-item').addClass('active').parent('ul').parent('li').addClass('current-menu-parent');

// tạo active cho li ông
$('ul li.current-menu-parent').addClass('active').parent('ul').parent('li').addClass('current-menu-grand').addClass('active');


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


//
jQuery('#oi_scroll_top, .oi_scroll_top').click(function () {
    jQuery('body,html').animate({
        scrollTop: 0
    }, 500);
});
