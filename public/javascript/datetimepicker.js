/*
 * nạp datetimepicker theo cách Ếch Bay
 */
if (typeof datetimepicker_loaded == 'undefined') {
    (function () {
        var s1 = document.createElement("script"),
            s0 = document.getElementsByTagName("script")[0];
        s1.async = true;
        s1.src = web_link + 'thirdparty/datetimepicker/jquery.datetimepicker.js';
        //s1.src = web_link + 'thirdparty/datetimepicker-2.3.7/build/jquery.datetimepicker.min.js';
        //s1.charset = 'UTF-8';
        //s1.setAttribute('crossorigin', '*');
        s0.parentNode.insertBefore(s1, s0);
    })();

    //
    (function () {
        // Get HTML head element
        var head = document.getElementsByTagName('HEAD')[0];

        // Create new link Element
        var link = document.createElement('link');

        // set the attributes for link element 
        link.rel = 'stylesheet';
        link.type = 'text/css';
        link.href = web_link + 'thirdparty/datetimepicker/jquery.datetimepicker.css';
        //link.href = web_link + 'thirdparty/datetimepicker-2.3.7/build/jquery.datetimepicker.min.css';

        // Append link element to HTML head
        head.appendChild(link);
    })();
}
var datetimepicker_loaded = true;

// hàm chuyển đổi date string sang timestamp sau khi close
function datetimepicker_onClose(input_name, input_id, type) {
    var pick_name = 'picker_' + input_name;
    //console.log('pick name:', pick_name);

    var pick_id = 'picker_' + input_id;
    //console.log('pick id:', input_id);

    //
    var input_ = $('#' + input_id);

    // ẩn input đi
    input_.attr({
        'readonly': true,
        'type': 'hidden',
    });

    //
    var val = input_.val() || '';
    var new_date = '';
    if (val != '') {
        //console.log('val:', val);
        val *= 1;
        //console.log('val:', val);
        if (val > 0) {
            var tzoffset = (new Date()).getTimezoneOffset() * 60000; // offset in milliseconds
            //tzoffset = 0;
            new_date = new Date(val * 1000 - tzoffset).toISOString();
            //console.log('new date:', new_date);

            // lấy ngày tháng năm và giờ
            if (type == 'datetime') {
                new_date = new_date.split('.')[0].replace('T', ' ');
            }
            // date -> chỉ lấy ngày tháng năm
            else {
                new_date = new_date.split('T')[0];
            }
            //console.log('new date:', new_date);
        }
    }

    //
    input_.before('<input type="text" class="' + (input_.attr('class') || '') + ' ebe-jquery-ui-' + type + '" placeholder="' + (input_.attr('placeholder') || '') + '" name="' + pick_name + '" id="' + pick_id + '" value="' + new_date + '" autocomplete="off">');

    //
    $('#' + pick_id).change(function () {
        var a = $(this).val() || '';
        //console.log('value:', a);

        //
        if (a != '') {
            // định dạng ngày giờ theo kiểu Việt Nam
            //var s = a.split(' ');
            //var s1 = s[0].split('-');
            //var s2 = s[1].split(':');
            //var d = new Date(s1[2], s1[1] - 1, s1[0], s2[0], s2[1], s2[2]);
            //$('#' + input_id).val(Math.ceil(d.getTime() / 1000));

            // -> xác định giờ theo múi giờ hiện tại của user
            var tzoffset = 0;
            //tzoffset = (new Date()).getTimezoneOffset() * 60000; // offset in milliseconds
            console.log('tzoffset:', tzoffset);
            var time_stamp = 0;

            // định dạng ngày giờ theo chuẩn quốc tế
            if (a.length == 10) {
                // chuyển ngày sang timestamp
                time_stamp = Date.parse(a + ' 00:00:00');
            } else {
                // chuyển ngày giờ sang timestamp
                time_stamp = Date.parse(a);
            }
            time_stamp *= 1;
            console.log('time_stamp:', time_stamp);
            if (tzoffset !== 0) {
                time_stamp += tzoffset;
                console.log('time_stamp:', time_stamp);
            }
            time_stamp = Math.ceil(time_stamp / 1000);
            // nếu là pick date -> đưa về cuối ngày -> để nếu có hết hạn thì cũng cuối ngày mới bị khóa
            if ($('#' + input_id).hasClass('datepicker')) {
                time_stamp += 24 * 3600 - 1;
                console.log('time_stamp:', time_stamp);
            }
            $('#' + input_id).val(time_stamp);

            //
            console.log('Test date:', new Date($('#' + input_id).val() * 1000).toISOString());
        } else {
            $('#' + input_id).val('0');
        }
    });
}

// khởi tạo đối tượng cho các kiểu date picker
function create_dynamic_datepicker(type) {
    $('input[type="' + type + '"], input.' + type + 'picker').each(function () {
        var a = $(this).attr('type') || '';
        //console.log('type:', a);

        // nếu đây là dạng số -> conver sang timestamp khi close
        //if (type != 'time' && a == 'number') {
        if (a == 'number') {
            var input_name = $(this).attr('name') || '';
            if (input_name != '') {
                //console.log('input name:', input_name);
                input_name = input_name.replace(/\[|\]/gi, '_');
                //console.log('input name:', input_name);

                //
                var input_id = $(this).attr('id') || '';
                //console.log('input id:', input_id);
                if (input_id == '') {
                    input_id = input_name;
                }

                // ẩn input đi
                $(this).attr({
                    'id': input_id
                });

                //
                datetimepicker_onClose(input_name, input_id, type);
            }
        } else {
            $(this).addClass('ebe-jquery-ui-' + type).attr({
                'type': 'text',
                'autocomplete': 'off',
            });
        }
    });

    //
    return $('.ebe-jquery-ui-' + type).length;
}

//
function EBE_load_datetimepicker(max_i) {
    if (typeof max_i != 'number') {
        max_i = 100;
    } else if (max_i < 0) {
        return false;
    }

    //
    if (typeof $().datetimepicker != 'function') {
        setTimeout(function () {
            EBE_load_datetimepicker(max_i - 1);
        }, 100);
        return false;
    }

    // chỉ lấy ngày tháng
    var MY_datepicker = function (id, op) {
        MY_datetimepicker(id, {
            timepicker: false,
            format: 'Y-m-d'
        });
    };

    // chỉ lấy giờ
    var MY_timepicker = function (id, op) {
        MY_datetimepicker(id, {
            datepicker: false,
            format: 'H:i'
        });
    };

    // lấy ngày tháng và giờ
    var MY_datetimepicker = function (id, op) {
        if (typeof op != 'object') {
            op = {};
        }

        //
        var default_op = {
            lang: $('html').attr('lang') || 'vi',
            timepicker: true,
            formatTime: 'H:i',
            //format: 'd-m-Y H:i:s'
            format: 'Y-m-d H:i:s',
            //showTimezone: true,
        };
        for (var x in default_op) {
            if (typeof op[x] == 'undefined') {
                op[x] = default_op[x];
            }
        }
        //console.log('op:', op);

        //
        $(id).datetimepicker(op);
    };

    // pick date
    if (create_dynamic_datepicker('date') > 0) {
        MY_datepicker('.ebe-jquery-ui-date');
    }
    // pick date time
    if (create_dynamic_datepicker('datetime') > 0) {
        MY_datetimepicker('.ebe-jquery-ui-datetime');
    }
    // pick time
    if (create_dynamic_datepicker('time') > 0) {
        MY_timepicker('.ebe-jquery-ui-time');
    }
}
EBE_load_datetimepicker();
