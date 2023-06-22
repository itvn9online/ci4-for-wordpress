/*
 * kiểm tra xem người dùng có đăng nhập trên nhiều thiết bị không
 */
var timeout_device_protection = 30;

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
			type: "GET",
			// link TEST
			url: "ajaxs/multi_loged",
			dataType: "json",
			//crossDomain: true,
			//data: data,
			timeout: 33 * 1000,
			error: function (jqXHR, textStatus, errorThrown) {
				jQueryAjaxError(jqXHR, textStatus, errorThrown, new Error().stack);
			},
			success: function (data) {
				if (WGR_check_option_on(WGR_config.cf_tester_mode)) console.log(data);

				// bình thường thì để 30s kiểm tra 1 lần
				timeout_device_protection = max_time;

				// không có hash
				if (typeof data.hash == "undefined") {
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
							.text(data.hash.ip)
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
						timeout_device_protection = min_time;
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
