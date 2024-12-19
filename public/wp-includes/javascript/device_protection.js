/**
 * kiểm tra xem người dùng có đăng nhập trên nhiều thiết bị không
 **/
// cho hàm _run vào function để không bị thay đổi code
// tạo biến rm chứa thông tin reqquest thông qua hàm JSON để tham số này không bị thay đổi từ bên ngoài
(function (rm) {
	var _run = function () {
		if (
			typeof get_logged_signature != "function" ||
			get_logged_signature() === null
		) {
			if (typeof WGR_builder_signature == "function") {
				WGR_builder_signature();
			}
			setTimeout(() => {
				_run();
			}, 1000);
			return false;
		}

		//
		var min_time = 5;
		var max_time = 30;

		// nếu không có modal ẩn cảnh báo -> nạp html
		if (jQuery("#warningLoggedModal").length < 1) {
			jQuery.ajax({
				type: "POST",
				// link TEST
				url: rm.logged + "?nse=modal" + Math.random(),
				dataType: "html",
				//crossDomain: true,
				data: {
					_wpnonce: get_logged_signature(),
					the_modal: 1,
				},
				timeout: 33 * 1000,
				error: function (jqXHR, textStatus, errorThrown) {
					jQueryAjaxError(jqXHR, textStatus, errorThrown, new Error().stack);
				},
				success: function (data) {
					//console.log(data);
					jQuery("body").append(data);
				},
				/*
				complete: function (xhr, status) {
					console.log(xhr);
					console.log(status);
				},
				*/
			});

			//
			setTimeout(() => {
				if (jQuery("#warningLoggedModal").length < 1) {
					console.log("Cannot be determined modal: Logged");
				}
				_run();
			}, max_time * 1000);

			//
			return false;
		}

		// nếu người dùng chưa close modal thì thôi không cần kiểm tra -> vì có close mới tiếp tục được
		if (jQuery("#warningLoggedModal").hasClass("show")) {
			if (WGR_check_option_on(WGR_config.cf_tester_mode)) {
				console.log(Math.random());
			}

			//
			setTimeout(() => {
				_run();
			}, min_time * 1000);

			//
			return false;
		}

		//
		jQuery.ajax({
			type: "POST",
			// link TEST
			url: rm.logged + "?nse=checker" + Math.random(),
			dataType: "json",
			//crossDomain: true,
			data: {
				_wpnonce: get_logged_signature(),
			},
			timeout: 33 * 1000,
			error: function (jqXHR, textStatus, errorThrown) {
				jQueryAjaxError(jqXHR, textStatus, errorThrown, new Error().stack);
			},
			success: function (data) {
				if (WGR_check_option_on(WGR_config.cf_tester_mode)) console.log(data);

				// bình thường thì để 30s kiểm tra 1 lần
				rm.timeout_dp = max_time;

				//
				if (typeof data.error != "undefined") {
					WGR_alert(data.error, "error");
				}
				// không có hash
				else if (typeof data.hash == "undefined") {
					WGR_alert("Cannot be determined login session", "error");
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
						data.hash.key != jQuery("body").data("session")
					) {
						//
						jQuery(".show-logged-ip")
							//.text(data.hash.ip)
							.text(data.hash.key)
							.attr({
								href:
									"https://www.iplocation.net/ip-lookup?query=" + data.hash.ip,
							});
						jQuery(".show-logged-agent").text(data.hash.agent);
						jQuery(".show-logged-device").html(
							WGR_is_mobile(data.hash.agent) === false
								? '<i class="fa fa-desktop"></i>'
								: '<i class="fa fa-mobile"></i>'
						);

						//
						//WGR_alert('Vui lòng không đăng nhập trên nhiều thiết bị!', 'error');
						jQuery("#warningLoggedModal").modal("show");

						//console.log(data.logout);
						// khi có nghi ngờ -> rút ngắn thời gian kiểm tra lại
						if (
							typeof data.chash != "undefined" &&
							data.chash == data.hash.key
						) {
							// hash trong cache mà giống với hash trong db thì cũng bỏ qua luôn
						} else if (
							typeof data.logout != "undefined" &&
							data.logout == "on"
						) {
							rm.logout_dp = data.logout;

							//
							jQuery.ajax({
								type: "POST",
								// link TEST
								url: rm.logout + "?nse=logout" + Math.random(),
								dataType: "json",
								//crossDomain: true,
								data: {
									_wpnonce: get_logged_signature(),
								},
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
							rm.timeout_dp = min_time;
						}
					}
				}

				//
				setTimeout(() => {
					_run();
				}, rm.timeout_dp * 1000);
			},
		});
	};

	//
	if (WGR_config.current_user_id > 0) {
		setTimeout(() => {
			_run();
		}, 5 * 1000);
	}
})(JSON.parse(JSON.stringify(_rqrm)));

//
function confirm_kip_logged() {
	jQuery.ajax({
		type: "POST",
		// link TEST
		url: _rqrm.cflogged + "?nse=confirm" + Math.random(),
		dataType: "json",
		//crossDomain: true,
		data: {
			_wpnonce: get_logged_signature(),
			user_id: WGR_config.current_user_id,
		},
		timeout: 33 * 1000,
		error: function (jqXHR, textStatus, errorThrown) {
			jQueryAjaxError(jqXHR, textStatus, errorThrown, new Error().stack);
		},
		success: function (data) {
			console.log(data);

			// nạp lại trang
			if (_rqrm.logout_dp == "on") {
				window.location.reload();
			}
		},
	});

	//
	return true;
}
