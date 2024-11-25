function action_mail_queue_sending() {
	// chỉ gửi mail khi trạng thái đơn hàng giống với thiết lập
	if (
		current_order_data.mail_queue_sending_type != "" &&
		current_order_data.mail_queue_sending_type !=
			current_order_data.order_status
	) {
		console.log(
			"mail_queue_sending_type",
			current_order_data.mail_queue_sending_type
		);
		return false;
	}

	//
	console.log("mail_my_queue", current_order_data.mail_queue_sending_type);
	jQuery.ajax({
		type: "POST",
		url: "actions/mail_my_queue",
		dataType: "json",
		//crossDomain: true,
		data: {
			nse: Math.random(),
			mail_queue_sending_type: current_order_data.mail_queue_sending_type,
		},
		timeout: 33 * 1000,
		error: function (jqXHR, textStatus, errorThrown) {
			jQueryAjaxError(jqXHR, textStatus, errorThrown, new Error().stack);
		},
		success: function (res) {
			console.log(res);
		},
	});
}

//
$(document).ready(function () {
	action_mail_queue_sending();
});
