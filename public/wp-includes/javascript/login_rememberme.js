function action_login_rememberme(key, id_captcha) {
	//console.log(key);

	// nếu người dùng bấm logout -> sẽ có key này -> bỏ chức năng đăng nhập tự động
	if (localStorage.getItem("remove_rememberme_auto_login") !== null) {
		localStorage.removeItem("remove_rememberme_auto_login");
		localStorage.removeItem(key);
		return false;
	}

	// xác định phiên lưu trữ đăng nhập
	var token = localStorage.getItem(key);
	if (token === null) {
		return false;
	}

	//
	//console.log("token length:", token.length);
	token = decodeURIComponent(token);
	//console.log(token);
	token = JSON.parse(token);
	//console.log(token);

	// thiếu bất kỳ điều kiện nào thì cũng bỏ qua
	if (
		typeof token.header == "undefined" ||
		typeof token.payload == "undefined" ||
		typeof token.signature == "undefined"
	) {
		return false;
	}

	// lấy thêm hide captchap để cho vào
	token = get_hide_captcha(token, "#" + id_captcha);
	//console.log(token);

	//
	jQuery.ajax({
		type: "POST",
		url: "guest/rememberme_login",
		dataType: "json",
		//crossDomain: true,
		data: token,
		timeout: 33 * 1000,
		error: function (jqXHR, textStatus, errorThrown) {
			jQueryAjaxError(jqXHR, textStatus, errorThrown, new Error().stack);
		},
		success: function (data) {
			//console.log(data);
			//console.log(data.length);

			// có lỗi thì báo lỗi
			if (typeof data.error != "undefined") {
				console.log("%c " + data.error, "color: red;");

				// đặt tham số này để hủy bỏ chức năng đăng nhập tự động
				//localStorage.setItem("remove_rememberme_auto_login", Math.random());
			} else if (typeof data.warning != "undefined") {
				// cảnh báo thì cảnh báo
				console.log("%c " + data.warning, "color: orange;");

				// đặt tham số này để hủy bỏ chức năng đăng nhập tự động
				localStorage.setItem("remove_rememberme_auto_login", Math.random());
			} else if (typeof data.ok != "undefined" && data.ok * 1 > 0) {
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
