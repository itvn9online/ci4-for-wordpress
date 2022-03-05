/*
 * kiểm tra xem người dùng có đăng nhập trên nhiều thiết bị không
 */
var timeout_device_protection = 30;

function get_user_logged_key() {
    jQuery.ajax({
        type: 'GET',
        // link TEST
        url: 'ajax/multi_login',
        dataType: 'json',
        //crossDomain: true,
        //data: data,
        success: function (data) {
            //console.log(data);

            // bình thường thì để 30s kiểm tra 1 lần
            timeout_device_protection = 30;

            // không có hash
            if (typeof data.hash == 'undefined') {
                // -> lỗi
            }
            // có hash mà hash khác nhau -> báo cho người dùng biết
            else if (data.hash != '') {
                data.hash = JSON.parse(data.hash);
                //console.log(data);

                //
                if (typeof data.hash.key != 'undefined' && data.hash.key != '' && data.hash.key != session_id) {
                    //
                    $('.show-logged-ip').text(data.hash.ip);
                    $('.show-logged-agent').text(data.hash.agent);

                    //
                    //WGR_alert('Vui lòng không đăng nhập trên nhiều thiết bị!', 'error');
                    $('#warningLoggedModal').modal('show');

                    // khi có nghi ngờ -> rút ngắn thời gian kiểm tra lại
                    timeout_device_protection = 5;
                }
            }

            //
            setTimeout(function () {
                get_user_logged_key();
            }, timeout_device_protection * 1000);
        }
    });
}
if (current_user_id > 0) {
    setTimeout(function () {
        get_user_logged_key();
    }, 5 * 1000);
}
