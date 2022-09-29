//
function ajax_push_image_to_server(params, __callBack) {
    if (typeof params != 'object') {
        WGR_alert('typeof params is not OBJECT!', 'error');
        return false;
    }

    // các tham số bắt buộc
    var require_params = [
        // action xử lý việc upload
        'action',
        // dữ liệu ảnh để upload
        'data',
        // thiết lập file name
        'file_name',
        // input select file đầu vào -> dùng để reset form sau khi upload thành công
        'input_file',
    ];
    for (var i = 0; i < require_params.length; i++) {
        if (typeof params[require_params[i]] == 'undefined' || params[require_params[i]] == '') {
            WGR_alert('Parameter ' + require_params[i].replace(/\_/gi, ' ') + ' is not EMPTY!', 'error');
            return false;
        }
    }

    // các tham số không bắt buộc -> không có thì để trống -> không phải làm gì
    var option_params = [
        // thiết lập ảnh làm bg sau khi upload thành công
        'set_bg',
        // thiết lập thumbnail
        'set_thumb',
        // thiết lập ảnh lớn
        'set_val',
    ];
    for (var i = 0; i < option_params.length; i++) {
        if (typeof params[option_params[i]] == 'undefined') {
            params[option_params[i]] = '';
        }
    }

    // định dạng file name về 1 mối chuẩn chỉ
    var file_name = params['file_name'].split('.');
    if (file_name.length > 1) {
        file_name[file_name.length - 1] = '';
        file_name = file_name.join('.');
    } else {
        file_name = file_name[0];
    }
    file_name = g_func.non_mark_seo(file_name);
    console.log('file name:', file_name);

    //
    var img = document.createElement('img');
    img.src = params['data'];

    //
    if (typeof params['img_max_width'] != 'number') {
        params['img_max_width'] = 999;
    } else if (params['img_max_width'] < 90) {
        params['img_max_width'] = 90;
    } else if (params['img_max_width'] > 1366) {
        params['img_max_width'] = 1366;
    }

    //
    setTimeout(function () {
        var width = img.width;
        var height = img.height;
        if (width > 0 && height > 0) {
            var MAX_WIDTH = params['img_max_width'];
            var MAX_HEIGHT = params['img_max_width'];
            var has_resize = false;
            if (width > height) {
                if (width > MAX_WIDTH) {
                    height *= MAX_WIDTH / width;
                    width = MAX_WIDTH;
                    has_resize = true;
                }
            } else {
                if (height > MAX_HEIGHT) {
                    width *= MAX_HEIGHT / height;
                    height = MAX_HEIGHT;
                    has_resize = true;
                }
            }
            if (has_resize === true) {
                console.log('has resize:', params['img_max_width']);
            }
            width = Math.ceil(width);
            height = Math.ceil(height);
            if (has_resize === true) {
                var canvas = document.createElement('canvas');
                canvas.width = width;
                canvas.height = height;
                canvas.getContext('2d').drawImage(img, 0, 0, canvas.width, canvas.height);
                var dataurl = canvas.toDataURL('image/jpeg', 1.9);
                params['data'] = dataurl;
            }
        }

        //
        $.ajax({
            type: "POST",
            url: params['action'],
            data: {
                img: params['data'],
                file_name: file_name,
            },
            timeout: 33 * 1000,
            error: function (jqXHR, textStatus, errorThrown) {
                console.log(jqXHR);
                if (typeof jqXHR.responseText != 'undefined') {
                    console.log(jqXHR.responseText);
                }
                console.log(errorThrown);
                console.log(textStatus);
                if (textStatus === 'timeout') { }
            },
            success: function (data) {
                console.log(data);
                //console.log(typeof data.img_large);
                if (typeof data.img_large != 'undefined') {
                    //console.log(typeof data.img_large);
                    data.img_large += '?v=' + Math.random();
                    data.img_thumb += '?v=' + Math.random();

                    //
                    if (params['set_bg'] != '') {
                        $(params['set_bg']).css({
                            'background-image': 'url(' + data.img_large + ')'
                        });
                    }

                    //
                    if (params['set_thumb'] != '') {
                        $(params['set_thumb']).val(data.img_thumb);
                    }

                    //
                    if (params['set_val'] != '') {
                        $(params['set_val']).val(data.img_large);
                    }
                }

                //
                $(params['input_file']).val('');

                //
                if (typeof __callBack == 'function') {
                    __callBack();
                } else {
                    console.log('%c __callBack is not FUNCTION', 'color: red;');
                }
            },
        });
    }, 200);
}
