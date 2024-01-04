(function () {
	if (typeof set_login == "undefined" || set_login == "") {
		return false;
	}
	$('#loginform input[name="username"]').val(set_login);
	$('#loginform input[name="password"]').focus();
})();

// tự động đăng nhập khi lưu session
$("#firebase_auto_login").change(function () {
	if ($(this).is(":checked")) {
		localStorage.setItem("firebase_auto_login", window.location.href);
	} else {
		localStorage.removeItem("firebase_auto_login");
	}
});

//
if (localStorage.getItem("firebase_auto_login") !== null) {
	$("#firebase_auto_login").prop("checked", true);
}
