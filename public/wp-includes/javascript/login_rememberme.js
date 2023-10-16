// nếu captcha đã được nạp thì chuyển thành true
var loading_ebe_recaptcha = false;

function action_login_rememberme(
	key,
	uri,
	uri_captcha,
	jsf,
	id_captcha,
	max_i
) {
	//console.log(key);
	//console.log(uri);
	//console.log(uri_captcha);
	//console.log(jsf);
	//console.log(id_captcha);
	//console.log(max_i);
	if (
		typeof key == "undefined" ||
		key == "" ||
		typeof uri == "undefined" ||
		uri == ""
	) {
		//console.log(Math.random());
		return false;
	}

	//
	var _rand = function (length) {
		let result = "";
		const characters =
			"ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789";
		const charactersLength = characters.length;
		let counter = 0;
		while (counter < length) {
			result += characters.charAt(Math.floor(Math.random() * charactersLength));
			counter += 1;
		}
		return result;
	};

	// tải hide-captcha nếu có yêu cầu -> chức năng nạp captcha chạy ké qua file login này luôn do cùng thuộc tính kiểm tra user is_logged
	if (
		loading_ebe_recaptcha === false &&
		typeof uri_captcha != "undefined" &&
		uri_captcha != ""
	) {
		loading_ebe_recaptcha = true;
		var load_ebe_recaptcha = function (hide_captcha) {
			var result_to = "ebe-recaptcha";
			if (typeof hide_captcha != "number") {
				hide_captcha = 0;
			} else {
				result_to = "ebe-rehidecaptcha";
			}
			//console.log(result_to);

			//
			jQuery.ajax({
				type: "POST",
				url:
					uri_captcha +
					"?hide_captcha=" +
					hide_captcha +
					"&_wpnonce=" +
					_rand(64),
				dataType: "html",
				//crossDomain: true,
				data: {
					hide_captcha: hide_captcha,
					result_to: result_to,
					jsf: jsf,
				},
				timeout: 33 * 1000,
				error: function (jqXHR, textStatus, errorThrown) {
					jQueryAjaxError(jqXHR, textStatus, errorThrown, new Error().stack);
				},
				success: function (data) {
					//console.log(data);
					// nạp xong thì trả về khối html -> dùng after để khối captcha này hạn chế bị get bởi lệnh khác khác thông qua class cố định ebe-re***
					$("." + result_to).after(data);
					jQuery('input[name="' + jsf + '_jsf"]').val(
						Math.random().toString(32)
					);
				},
			});
		};

		// nếu có class yêu cầu nạp captcha thì sẽ tiến hành nạp
		if ($(".ebe-recaptcha").length > 0) {
			//console.log("ebe-recaptcha");
			load_ebe_recaptcha();
		}
		if ($(".ebe-rehidecaptcha").length > 0) {
			//console.log("ebe-rehidecaptcha");
			load_ebe_recaptcha(1);
		}
	}

	// nếu người dùng bấm logout -> sẽ có key này -> bỏ chức năng đăng nhập tự động
	if (localStorage.getItem("remove_rememberme_auto_login") !== null) {
		localStorage.removeItem("remove_rememberme_auto_login");
		localStorage.removeItem(key);
		//console.log(Math.random());
		return false;
	}

	// nếu người dùng bấm vào checkbox tự động đăng nhập thì sẽ có thông số này, ko có nghĩa là chưa từng bấm thì bỏ qua thôi
	if (localStorage.getItem("firebase_auto_login") === null) {
		//console.log(Math.random());
		return false;
	}

	// xác định phiên lưu trữ đăng nhập
	var token = localStorage.getItem(key);
	if (token === null) {
		//console.log(Math.random());
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
		//console.log(Math.random());
		return false;
	}

	// lấy thêm hide captchap để cho vào trình đăng nhập tự động nếu có yêu cầu
	if (typeof id_captcha != "undefined" && id_captcha != "") {
		// nếu chưa có input nào -> captcha chưa được tải xong -> chờ tải
		if ($("#" + id_captcha + " input").length < 1) {
			if (typeof max_i != "number") {
				max_i = 99;
			} else if (max_i < 0) {
				console.log("max i:", max_i);
				return false;
			}
			setTimeout(function () {
				action_login_rememberme(
					key,
					uri,
					uri_captcha,
					jsf,
					id_captcha,
					max_i - 1
				);
			}, 200);
			return false;
		}
		token = get_hide_captcha(token, "#" + id_captcha);
	}
	//return console.log(token);

	//
	jQuery.ajax({
		type: "POST",
		url: uri + "?_wpnonce=" + _rand(64),
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
				localStorage.setItem("remove_rememberme_auto_login", Math.random());
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
