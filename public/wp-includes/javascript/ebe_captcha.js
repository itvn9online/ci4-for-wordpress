function action_ebe_captcha(uri) {
	// nếu có class yêu cầu nạp captcha thì sẽ tiến hành nạp
	if ($(".ebe-recaptcha").length < 1) {
		return false;
	}

	//console.log("ebe-recaptcha");
	jQuery.ajax({
		type: "POST",
		url: uri + "?_wpnonce=" + Math.random().toString(32),
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
