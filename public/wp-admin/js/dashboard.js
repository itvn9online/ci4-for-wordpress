function done_unzip_system() {
	WGR_alert("DONE! giải nén system zip thành công");

	$("#unzipSystemModal").modal("hide");

	$(".hide-after-unzip-system").fadeOut();
}

//
var current_full_domain = localStorage.getItem("WGR-current-full-domain");
var current_protocol = web_link;
var current_www = web_link;
if (current_full_domain !== null) {
	current_full_domain = JSON.parse(current_full_domain);
	console.log("current full domain:", current_full_domain);
	current_protocol = current_full_domain.http_response;
	current_www = current_full_domain.http_response;
}

//
vue_data.encode_url = encodeURIComponent(vue_data.base_url);
vue_data.Date_now = Date.now();
vue_data.phpversion = vue_data.phpversion.replace(".", "").split(".")[0];
vue_data.current_protocol = current_protocol.split("//")[0];
vue_data.current_www = current_www.split(".")[0].split("//")[1];
vue_data.calculate_ci4_update = function (last_time) {
	var current_time = Math.ceil(Date.now() / 1000);
	var one_day = 24 * 3600;
	var cal_day = current_time - last_time;
	cal_day = cal_day / one_day;
	return cal_day.toFixed(1) * 1;
};
vue_data.client_os = (function () {
	var os = window.navigator.platform;
	try {
		var userAgent = window.navigator.userAgent,
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

		//
		if (macosPlatforms.indexOf(platform) !== -1) {
			os = "MacOS";
		} else if (iosPlatforms.indexOf(platform) !== -1) {
			os = "iOS";
		} else if (windowsPlatforms.indexOf(platform) !== -1) {
			os = "Windows";
		} else if (/Android/.test(userAgent)) {
			os = "Android";
		} else if (/Linux/.test(platform)) {
			os = "Linux";
		}
	} catch (e) {
		WGR_show_try_catch_err(e);
	}

	return os;
})();
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
WGR_vuejs("#app", vue_data);

//
function dashboard_current_timestamp() {
	var currentDate = new Date();
	var timestamp = currentDate.getTime();
	return Math.ceil(timestamp / 1000);
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
		url: "admin/asjaxs/check_ssl",
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
$(document).ready(function () {
	$("#admin_menu_search").focus();
});
