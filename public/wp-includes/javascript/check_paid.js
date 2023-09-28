/*
* Chức năng kiểm tra trạng thái đơn hàng đã được thanh toán chưa
*/
// Số lần kiểm tra tối đa -> hết lượt này nếu chưa thanh toán thì thôi
var max_check_paid = 60;
// giãn cách mỗi lần kiểm tra
var time_check_paid = 6000;

//
function payments_check_paid(__callBack) {
    jQuery.ajax({
        type: 'POST',
        url: 'payments/check_paid',
        dataType: 'json',
        //crossDomain: true,
        data: {
            order_id: order_id,
        },
        success: function (data) {
            if (WGR_config.cf_tester_mode > 0) {
                console.log(data);
            }

            //
            if (typeof data.status != 'undefined' && data.status * 1 > 0) {
                console.log(data.status);

                //
                if (typeof __callBack == 'function') {
                    __callBack();
                }
            }
            else if (typeof data.error != 'undefined') {
                console.log(data.error, max_check_paid);

                //
                if (max_check_paid > 0) {
                    max_check_paid--;

                    //
                    setTimeout(function () {
                        payments_check_paid(__callBack);
                    }, time_check_paid);
                }
            }
        }
    });
}