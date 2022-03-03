/*
 * kiểm tra xem người dùng có đăng nhập trên nhiều thiết bị không
 */
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
                }
            }

            //
            setTimeout(function () {
                get_user_logged_key();
            }, 10 * 1000);
        }
    });
}
if (current_user_id > 0) {
    get_user_logged_key();
}
