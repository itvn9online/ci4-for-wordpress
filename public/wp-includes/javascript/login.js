(function () {
	if (typeof set_login == "undefined" || set_login == "") {
		return false;
	}
	jQuery('#loginform input[name="username"]').val(set_login);
	jQuery('#loginform input[name="password"]').focus();
})();

// tự động đăng nhập khi lưu session
jQuery("#firebase_auto_login").change(function () {
	if (jQuery(this).is(":checked")) {
		localStorage.setItem("firebase_auto_login", window.location.href);
	} else {
		localStorage.removeItem("firebase_auto_login");
	}
});

//
if (localStorage.getItem("firebase_auto_login") !== null) {
	jQuery("#firebase_auto_login").prop("checked", true);
}
