//
function ajax_push_image_to_server(action, str, file_name, set_bg_for, reset_input_file, img_max_width) {
    file_name = file_name.split('.');
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
    img.src = str;

    //
    if (typeof img_max_width != 'number') {
        img_max_width = 999;
    } else if (img_max_width < 90) {
        img_max_width = 90;
    } else if (img_max_width > 1366) {
        img_max_width = 1366;
    }

    //
    setTimeout(function () {
        var width = img.width;
        var height = img.height;
        if (width > 0 && height > 0) {
            var MAX_WIDTH = img_max_width;
            var MAX_HEIGHT = img_max_width;
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
                console.log('has resize:', img_max_width);
            }
            width = Math.ceil(width);
            height = Math.ceil(height);
            if (has_resize === true) {
                var canvas = document.createElement('canvas');
                canvas.width = width;
                canvas.height = height;
                canvas.getContext('2d').drawImage(img, 0, 0, canvas.width, canvas.height);
                var dataurl = canvas.toDataURL('image/jpeg', 1.9);
                str = dataurl;
            }
        }

        //
        $.ajax({
            type: "POST",
            url: action,
            data: {
                img: str,
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
                    $(set_bg_for).css({
                        'background-image': 'url(' + data.img_large + ')'
                    });

                    //
                    $('#file-input-avatar').val(data.img_thumb);
                }

                //
                $(reset_input_file).val('');
            },
        });
    }, 200);
}
