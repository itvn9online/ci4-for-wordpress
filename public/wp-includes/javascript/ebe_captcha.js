function action_ebe_captcha(uri) {
	// nếu có class yêu cầu nạp captcha thì sẽ tiến hành nạp
	if ($(".ebe-recaptcha").length < 1) {
		return false;
	}

	//
	if (
		typeof get_logged_signature != "function" ||
		get_logged_signature() === null
	) {
		if (typeof WGR_builder_signature == "function") {
			WGR_builder_signature();
		}
		if (typeof max_i != "number") {
			max_i = 99;
		} else if (max_i < 0) {
			console.log("max_i:", max_i);
			return false;
		}
		setTimeout(() => {
			action_ebe_captcha(uri, max_i - 1);
		}, 200);
		return false;
	}

	//console.log("ebe-recaptcha");
	jQuery.ajax({
		type: "POST",
		url: uri + "?_wpnonce=" + get_logged_signature(),
		dataType: "html",
		//crossDomain: true,
		data: { nse: Math.random() },
		timeout: 33 * 1000,
		error: function (jqXHR, textStatus, errorThrown) {
			jQueryAjaxError(jqXHR, textStatus, errorThrown, new Error().stack);
		},
		success: function (data) {
			// console.log(data);
			// nạp xong thì trả về khối html -> dùng after để khối captcha này hạn chế bị get bởi lệnh khác khác thông qua class cố định ebe-re***
			$(".ebe-recaptcha").after(data);
			console.log(window.location.href);
		},
	});
}
