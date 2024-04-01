function done_unzip_system() {
	WGR_alert("DONE! giải nén system zip thành công");

	$("#unzipSystemModal").modal("hide");

	$(".hide-after-unzip-system").fadeOut();
}

function done_unzip_base_code() {
	WGR_alert("DONE! giải nén ci4-for-wordpress zip thành công");

	$("#unzipBaseCodeModal").modal("hide");

	$(".hide-after-unzip-base_code").fadeOut();
}

function done_unzip_themename() {
	WGR_alert("DONE! giải nén theme zip thành công");

	$("#unzipThemNameModal").modal("hide");

	$(".hide-after-unzip-themename").fadeOut();
}

//
var current_full_domain = localStorage.getItem("WGR-current-full-domain");
var current_protocol = web_link;
var current_www = web_link;
if (current_full_domain !== null) {
	current_full_domain = JSON.parse(current_full_domain);
	current_full_domain.http_response =
		current_full_domain.http_response.toString();
	current_full_domain.www_response =
		current_full_domain.www_response.toString();
	console.log("current full domain:", current_full_domain);
	if (current_full_domain.http_response.includes("//") == true) {
		current_protocol = current_full_domain.http_response;
		current_www = current_full_domain.http_response;
	}
}

//
vue_data.encode_url = encodeURIComponent(vue_data.base_url);
vue_data.Date_now = Date.now();
vue_data.phpversion = vue_data.phpversion.replace(".", "").split(".")[0];
vue_data.current_protocol = current_protocol.split("//")[0];
vue_data.current_www = current_www.split(".")[0].split("//")[1];
vue_data.calculate_ci4_update = function (last_time) {
	let current_time = Math.ceil(Date.now() / 1000);
	let one_day = 24 * 3600;
	let cal_day = current_time - last_time;
	cal_day = cal_day / one_day;
	return cal_day.toFixed(1) * 1;
};
vue_data.client_os = (function () {
	let os = window.navigator.platform;
	try {
		let userAgent = window.navigator.userAgent,
			//platform = window.navigator?.userAgentData?.platform || window.navigator.platform,
			platform = window.navigator.userAgentData || null,
			macosPlatforms = ["Macintosh", "MacIntel", "MacPPC", "Mac68K", "macOS"],
			windowsPlatforms = ["Win32", "Win64", "Windows", "WinCE"],
			iosPlatforms = ["iPhone", "iPad", "iPod"];
		if (platform === null) {
			platform = window.navigator.platform;
		} else {
			platform = platform.platform;
		}

		// .includes
		if (WGR_in_array(platform, macosPlatforms)) {
			os = "MacOS";
		} else if (WGR_in_array(platform, iosPlatforms)) {
			os = "iOS";
		} else if (WGR_in_array(platform, windowsPlatforms)) {
			os = "Windows";
		} else if (/Android/.test(userAgent)) {
			os = "Android";
		} else if (/Linux/.test(platform)) {
			os = "Linux";
		}
	} catch (e) {
		WGR_show_try_catch_err(e);
	}

	//
	return os;
})();

//
vue_data.warning_ci_version = function (a, b) {
	//
	a = a.toString().replace(/\./gi, "") * 1;
	b = b.toString() * 1;

	//
	if (a < b) {
		return "orgcolor";
	}
	return "greencolor";
};

//
vue_data.warning_session_drive = function (a, b) {
	if (a.includes("RedisHandler") == true) {
		if (b == "redis") {
			return "orgcolor";
		}
		return "bluecolor";
	} else if (a.includes("MemcachedHandler") == true) {
		if (b == "memcached") {
			return "orgcolor";
		}
		return "greencolor";
	} else if (a.includes("DatabaseHandler") == true) {
		return "bluecolor";
	}

	//
	return "";
};

//
vue_data.client_timezone = function () {
	let tz = "";
	try {
		tz = Intl.DateTimeFormat().resolvedOptions().timeZone;
	} catch (e) {
		tz = new Date().toString().split(" GMT");
		if (tz.length > 1) {
			tz = tz[1];
		} else {
			tz = new Date().getTimezoneOffset();
		}

		//
		WGR_show_try_catch_err(e);
	}
	// console.log(tz);

	//
	return tz;
};

//
WGR_vuejs("#app", vue_data);

//
function dashboard_current_timestamp() {
	return Math.floor(Date.now() / 1000);
}

// cập nhật full URL nếu chưa có
if (
	current_full_domain === null ||
	current_full_domain.expires_in == "undefined" ||
	current_full_domain.expires_in < dashboard_current_timestamp()
) {
	jQuery.ajax({
		type: "GET",
		// lấy base URL từ link http thường (không phải https) -> để xem nó có redirect về https không
		url: "sadmin/asjaxs/check_ssl",
		dataType: "json",
		//crossDomain: true,
		//data: data,
		timeout: 33 * 1000,
		error: function (jqXHR, textStatus, errorThrown) {
			jQueryAjaxError(jqXHR, textStatus, errorThrown, new Error().stack);
		},
		success: function (data) {
			data.expires_in = dashboard_current_timestamp() + 24 * 3600;
			console.log(data);

			//
			localStorage.setItem("WGR-current-full-domain", JSON.stringify(data));
		},
	});
}

//
// $(document).ready(function () {
// 	$("#admin_menu_search").focus();
// });

/**
 * Hiển thị thông tin server và user theo IP
 * Mục đích chính là cập nhật GeoLite2-Db định kỳ
 */
function showServerInfoIp(data, to) {
	// console.log(data);

	//
	let a = [];
	if (typeof data.city != "undefined") {
		a.push(data.city.names.en);
	}
	if (typeof data.subdivisions != "undefined") {
		// a.push(data.subdivisions[0].names.en);
		a.push(data.subdivisions[0].iso_code);
	}
	if (typeof data.country != "undefined") {
		// a.push(data.country.names.en);
		a.push(data.country.iso_code);
	} else if (typeof data.registered_country != "undefined") {
		// a.push(data.registered_country.names.en);
		a.push(data.registered_country.iso_code);
	}

	//
	if (typeof to == "undefined" || to == "") {
		to = ".server-info_ip";
	}
	$(to).append(" " + a.join(", "));
}

function showUserInfoIp(data) {
	return showServerInfoIp(data, ".user-info_ip");
}

//
(function (server_info_ip, user_info_ip) {
	let uri = web_link + "plains/city_db_ip";
	console.log("uri:", uri);
	if (server_info_ip !== null) {
		showServerInfoIp(JSON.parse(server_info_ip));

		//
		if (user_info_ip !== null) {
			showUserInfoIp(JSON.parse(user_info_ip));
		}
		return false;
	}

	// hiển thị thông tin IP hiện tại của server
	jQuery.ajax({
		type: "POST",
		url: uri,
		dataType: "json",
		//crossDomain: true,
		data: {
			ip: server_ip,
		},
		timeout: 66 * 1000,
		error: function (jqXHR, textStatus, errorThrown) {
			console.log(jqXHR);
			console.log(textStatus);
			console.log(errorThrown);
		},
		success: function (data) {
			// console.log(data);
			//console.log(data.length);

			//
			if (typeof data.last_updated != "undefined") {
				console.log(
					"last_updated:",
					new Date(data.last_updated * 1000).toISOString()
				);
			}

			//
			if (typeof data.data != "undefined") {
				showServerInfoIp(data.data);

				//
				g_func.setc(
					"admin_server_location_by_ip",
					JSON.stringify(data.data),
					3600
				);
			}

			// hiển thị thông tin IP hiện tại của người dùng
			jQuery.ajax({
				type: "POST",
				url: web_link + "plains/city_ip",
				dataType: "json",
				//crossDomain: true,
				data: {},
				timeout: 33 * 1000,
				error: function (jqXHR, textStatus, errorThrown) {
					console.log(jqXHR);
					console.log(textStatus);
					console.log(errorThrown);
				},
				success: function (data) {
					// console.log(data);
					//console.log(data.length);

					//
					if (typeof data.last_updated != "undefined") {
						console.log(
							"last_updated:",
							new Date(data.last_updated * 1000).toISOString()
						);
					}

					//
					if (typeof data.data != "undefined") {
						showUserInfoIp(data.data);

						//
						g_func.setc(
							"admin_user_location_by_ip",
							JSON.stringify(data.data),
							2 * 3600
						);
					}
				},
			});
		},
	});
})(
	g_func.getc("admin_server_location_by_ip"),
	g_func.getc("admin_user_location_by_ip")
);
