function action_login_rememberme(key, uri) {
	//console.log(key);
	//console.log(uri);
	if (
		typeof key == "undefined" ||
		key == "" ||
		typeof uri == "undefined" ||
		uri == ""
	) {
		//console.log(Math.random());
		return false;
	}

	// nếu người dùng bấm logout -> sẽ có key này -> bỏ chức năng đăng nhập tự động
	if (localStorage.getItem("remove_rememberme_auto_login") !== null) {
		localStorage.removeItem("remove_rememberme_auto_login");
		localStorage.removeItem(key);
		//console.log(Math.random());
		return false;
	}

	// xác định phiên lưu trữ đăng nhập
	var token = localStorage.getItem(key);
	if (token === null) {
		//console.log(Math.random());
		return false;
	}

	// nếu người dùng bấm vào checkbox tự động đăng nhập thì sẽ có thông số này, ko có nghĩa là chưa từng bấm thì bỏ qua thôi
	if (localStorage.getItem("firebase_auto_login") === null) {
		//console.log(Math.random());
		console.log("auto login has been disable by firebase");
		return false;
	}
	//console.log(key);

	//
	//console.log("token length:", token.length);
	token = decodeURIComponent(token);
	//console.log(token);
	token = JSON.parse(token);
	// console.log(token);

	// thiếu bất kỳ điều kiện nào thì cũng bỏ qua
	if (
		typeof token.header == "undefined" ||
		typeof token.payload == "undefined" ||
		typeof token.signature == "undefined"
	) {
		//console.log(Math.random());
		return false;
	}

	//
	jQuery.ajax({
		type: "POST",
		url: uri + "?_wpnonce=" + Math.random().toString(32),
		dataType: "json",
		//crossDomain: true,
		data: token,
		timeout: 33 * 1000,
		error: function (jqXHR, textStatus, errorThrown) {
			jQueryAjaxError(jqXHR, textStatus, errorThrown, new Error().stack);
		},
		success: function (data) {
			// console.log(data);
			// return false;
			//console.log(data.length);

			// có lỗi thì báo lỗi
			if (typeof data.error != "undefined") {
				console.log("%c " + data.error, "color: red;");

				// đặt tham số này để hủy bỏ chức năng đăng nhập tự động
				localStorage.setItem("remove_rememberme_auto_login", Math.random());
			} else if (typeof data.warning != "undefined") {
				// cảnh báo thì cảnh báo
				console.log("%c " + data.warning, "color: orange;");

				// đặt tham số này để hủy bỏ chức năng đăng nhập tự động
				localStorage.setItem("remove_rememberme_auto_login", Math.random());
			} else if (typeof data.ok != "undefined" && data.ok * 1 > 0) {
				console.log("%c login_rememberme OK!", "color: green;");

				// đến đây nghĩa là đăng nhập thành công -> redirect
				window.location = (function () {
					return (
						$('input[name="login_redirect"]').val() || window.location.href
					);
				})();
			}
		},
	});

	//
	return true;
}
