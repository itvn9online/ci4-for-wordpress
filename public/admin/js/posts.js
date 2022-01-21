// thêm nút add ảnh đại diện
add_and_show_post_avt('#post_meta_image');


if (typeof page_post_type != 'undefined' && current_post_type == page_post_type) {
    WGR_load_textediter('#data_post_excerpt');
}

//
function action_before_submit_post() {
    fixed_CLS_for_editer('iframe#Resolution_ifr');

    //
    return true;
}

// xử lý đối với hình ảnh trong editer
function fixed_CLS_for_editer(for_iframe) {
    if ($(for_iframe).length > 0) {
        var arr = [];
        jQuery(for_iframe).contents().find('img').each(function () {
            var s = $(this).attr('src') || '';
            //console.log(s);

            //
            var w = $(this).attr('width') || '';
            //console.log(w);
            if (w == '') {
                w = $(this).width() || 0;
                //console.log(w);
                if (w * 1 > 0) {
                    $(this).attr({
                        'width': Math.ceil(w)
                    });

                    //
                    arr.push(s + ' width: ' + w);
                }
            }

            //
            var h = $(this).attr('height') || '';
            //console.log(h);
            if (h == '') {
                h = $(this).height() || 0;
                //console.log(h);
                if (h * 1 > 0) {
                    $(this).attr({
                        'height': Math.ceil(h)
                    });

                    //
                    arr.push(s + ' height: ' + h);
                }
            }
        });

        //
        if (arr.length > 0) {
            console.log('%c ' + for_iframe + ' CLS', 'color: green;');
            for (var i = 0; i < arr.length; i++) {
                console.log(arr[i]);
            }

            //
            return true;
        }
    }

    //
    return false;
}

// xử lý lần 1 lúc nạp xong document
$(document).ready(function () {
    fixed_CLS_for_editer('iframe#Resolution_ifr');
});

// lần 2 lúc nạp xong hình ảnh
$(window).load(function () {
    fixed_CLS_for_editer('iframe#Resolution_ifr');

    // tự động submit để cập nhật module mới cho bài viết
    if (typeof auto_update_module == 'number' && auto_update_module === 1) {
        setTimeout(function () {
            console.log('Auto submit...');
            document.admin_global_form.submit();

            // tự động chuyển sang bài tiếp theo
            if (url_next_post != '') {
                console.log('Auto next: ' + url_next_post);
                setTimeout(function () {
                    window.location = url_next_post;
                }, 3000);
            } else {
                console.log('%c Không xác định được url tiếp theo...', 'color: red;');
                WGR_alert('Không xác định được url tiếp theo...', 'error');
            }
        }, 3000);
    }
});
