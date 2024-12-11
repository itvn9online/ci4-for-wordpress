/**
 * Chức năng kiểm tra trạng thái đơn hàng đã được thanh toán chưa
 */
// Số lần kiểm tra tối đa -> hết lượt này nếu chưa thanh toán thì thôi
var max_check_paid = 60;
// giãn cách mỗi lần kiểm tra
var time_check_paid = 6000;

//
function payments_check_paid(__callBack) {
	// xác định trạng thái đơn đã thanh toán -> nếu thanh toán rồi thì thôi ko quét nữa
	if (
		typeof current_order_status == "undefined" ||
		current_order_status == null
	) {
		console.log("%c" + "current_order_status not found!", "color: red");
		return false;
	} else if (
		current_order_status == localStorage.getItem("order_when_completed_value")
	) {
		console.log(
			"%c" + "current_order_status is " + current_order_status,
			"color: green"
		);
		return false;
	}

	//
	jQuery.ajax({
		type: "POST",
		url: "payments/check_paid",
		dataType: "json",
		//crossDomain: true,
		data: {
			order_id: order_id,
		},
		success: function (data) {
			if (WGR_config.cf_tester_mode > 0) {
				console.log(data);
			}

			//
			if (typeof data.ok != "undefined" && data.ok * 1 > 0) {
				if (typeof data.status != "undefined") {
					console.log(data.ok, data.status);
					if (data.status != "") {
						localStorage.setItem("order_when_completed_value", data.status);
					}
				} else {
					console.log(data.ok);
				}

				//
				if (typeof __callBack == "function") {
					__callBack();
				}
			} else if (typeof data.error != "undefined") {
				console.log(data.error, max_check_paid);

				//
				if (max_check_paid > 0) {
					max_check_paid--;

					//
					setTimeout(() => {
						payments_check_paid(__callBack);
					}, time_check_paid);
				}
			}
		},
	});
}
