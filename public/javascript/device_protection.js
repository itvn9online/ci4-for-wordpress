/*
 * kiểm tra xem người dùng có đăng nhập trên nhiều thiết bị không
 */
var timeout_device_protection = 30;
var logout_device_protection = "";

//
(function () {
	var _run = function () {
		var min_time = 5;
		var max_time = 30;

		// nếu không có modal ẩn cảnh báo -> ẩn luôn chức năng làm bài đi
		if ($("#warningLoggedModal").length == 0) {
			WGR_alert("Không xác định được modal: Logged", "error");
			return false;
		}

		// nếu người dùng chưa close modal thì thôi không cần kiểm tra -> vì có close mới tiếp tục được
		if ($("#warningLoggedModal").hasClass("show")) {
			if (WGR_check_option_on(WGR_config.cf_tester_mode))
				console.log(Math.random());
			setTimeout(function () {
				_run();
			}, min_time * 1000);
			return false;
		}

		//
		jQuery.ajax({
			type: "POST",
			// link TEST
			url: "ajaxs/multi_logged",
			dataType: "json",
			//crossDomain: true,
			data: { nse: Math.random() },
			timeout: 33 * 1000,
			error: function (jqXHR, textStatus, errorThrown) {
				jQueryAjaxError(jqXHR, textStatus, errorThrown, new Error().stack);
			},
			success: function (data) {
				if (WGR_check_option_on(WGR_config.cf_tester_mode)) console.log(data);

				// bình thường thì để 30s kiểm tra 1 lần
				timeout_device_protection = max_time;

				//
				if (typeof data.error != "undefined") {
					WGR_alert(data.error, "error");
				}
				// không có hash
				else if (typeof data.hash == "undefined") {
					WGR_alert("Không xác định được phiên đăng nhập", "error");
				}
				// nếu hash null -> đã hết phiên
				else if (!data.hash) {
					console.log(data);
				}
				// có hash mà hash khác nhau -> báo cho người dùng biết
				else {
					//data.hash = JSON.parse(data.hash);
					//console.log(data);

					//
					if (
						typeof data.hash.key != "undefined" &&
						data.hash.key != "" &&
						data.hash.key != $("body").attr("data-session")
					) {
						//
						$(".show-logged-ip")
							//.text(data.hash.ip)
							.text(data.hash.key)
							.attr({
								href:
									"https://www.iplocation.net/ip-lookup?query=" + data.hash.ip,
							});
						$(".show-logged-agent").text(data.hash.agent);
						$(".show-logged-device").html(
							WGR_is_mobile(data.hash.agent) === false
								? '<i class="fa fa-desktop"></i>'
								: '<i class="fa fa-mobile"></i>'
						);

						//
						//WGR_alert('Vui lòng không đăng nhập trên nhiều thiết bị!', 'error');
						$("#warningLoggedModal").modal("show");

						// khi có nghi ngờ -> rút ngắn thời gian kiểm tra lại
						//console.log(data.logout);
						if (
							typeof data.chash != "undefined" &&
							data.chash == data.hash.key
						) {
							// hash trong cache mà giống với hash trong db thì cũng bỏ qua luôn
						} else if (
							typeof data.logout != "undefined" &&
							data.logout == "on"
						) {
							logout_device_protection = data.logout;

							//
							jQuery.ajax({
								type: "POST",
								// link TEST
								url: "ajaxs/multi_logout",
								dataType: "json",
								//crossDomain: true,
								data: { nse: Math.random() },
								timeout: 33 * 1000,
								error: function (jqXHR, textStatus, errorThrown) {
									jQueryAjaxError(
										jqXHR,
										textStatus,
										errorThrown,
										new Error().stack
									);
								},
								success: function (data) {
									if (
										typeof data.redirect_to != "undefined" &&
										data.redirect_to != ""
									) {
										window.location = data.redirect_to;
									} else if (
										typeof data.error != "undefined" &&
										data.error != ""
									) {
										WGR_alert(data.error, "error");
									} else {
										WGR_alert("Device protection actived", "error");
									}
								},
							});
						} else {
							timeout_device_protection = min_time;
						}
					}
				}

				//
				setTimeout(function () {
					_run();
				}, timeout_device_protection * 1000);
			},
		});
	};

	//
	if (WGR_config.current_user_id > 0) {
		setTimeout(function () {
			_run();
		}, 5 * 1000);
	}
})();

//
function confirm_kip_logged() {
	jQuery.ajax({
		type: "POST",
		// link TEST
		url: "ajaxs/confirm_logged",
		dataType: "json",
		//crossDomain: true,
		data: {
			nse: Math.random(),
			user_id: WGR_config.current_user_id,
		},
		timeout: 33 * 1000,
		error: function (jqXHR, textStatus, errorThrown) {
			jQueryAjaxError(jqXHR, textStatus, errorThrown, new Error().stack);
		},
		success: function (data) {
			console.log(data);

			// nạp lại trang
			if (logout_device_protection == "on") {
				window.location = window.location.href;
			}
		},
	});

	//
	return true;
}
