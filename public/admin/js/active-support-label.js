/*
 * Chức năng tự động focus và scroll tới label chứa thông tin cần hõ trợ
 */

//
var add_class_bg_for_tr_support = false;

// tạo for cho label nếu chưa có
(function () {
    var arr = [
        '#content .control-group input',
        '#content .control-group select',
        '#content .control-group textarea',
    ];

    $(arr.join(',')).each(function () {
        var get_for = $(this).parent('.controls').parent('.control-group').find('label');
        var check_for = get_for.attr('for') || '';
        //console.log(check_for);

        // chưa có thì mới tạo
        if (check_for == '') {
            var label_for = $(this).attr('id') || '';
            if (label_for == '') {
                label_for = $(this).attr('name') || '';
                if (label_for != '') {
                    label_for = label_for.replace(/\[|\]/g, '_');

                    // gán luôn ID cho filed nếu ID này chưa được sử dụng
                    if ($('#' + label_for).length == 0) {
                        $(this).attr({
                            'id': label_for
                        });
                    }
                }
            }
            //console.log(label_for);

            if (label_for != '') {
                label_for += '___auto';
                console.log('label for:', label_for);

                //
                get_for.attr({
                    'for': label_for
                    /*
                }).css({
                    'border': '1px #f00 solid'
                    */
                });
            }
        }
    });
})();

// hiệu ứng mỗi khi bấm vào label -> tạo link support
$('#content .control-group label').click(function () {
    add_class_bg_for_tr_support = true;

    //
    $('.control-group').removeClass('current-selected-support');

    //
    var a = $(this).attr('for') || '';
    if (a != '') {
        //console.log(a);

        // thay đổi URL để khi xuất hiện params tương ứng thì tự động scroll xuống ID này
        _global_js_eb.change_url_tab('support_tab', a);
    }
});

//
(function () {
    // tự động trỏ đến TR đang cần support
    setTimeout(function () {
        if (add_class_bg_for_tr_support == false) {
            var get_support_tab = window.location.href.split('&support_tab=');
            if (get_support_tab.length > 1 && $('.control-group').length > 0) {
                get_support_tab = get_support_tab[1].split('&')[0].split('#')[0];
                console.log(get_support_tab);

                //
                var lb = $('#content .control-group label[for="' + get_support_tab + '"]');

                // chạy và tìm thẻ TR có chứa cái thẻ label này
                if (get_support_tab != '' && lb.length > 0) {

                    // cuộn chuột đến khu vực cần xem -> xem cho dễ
                    window.scroll(0, lb.offset().top - ($(window).height() / 3));

                    //
                    lb.parents('.control-group').addClass('current-selected-support');
                }
            }
        }
    }, 1200);
})();
