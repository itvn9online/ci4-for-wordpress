function open_input_change_user_password() {
	jQuery(".hide-if-change-password").hide();
	jQuery(".show-if-change-password").removeClass("d-none").show();
	jQuery("#data_ci_pass").focus();
}

function close_input_change_user_password() {
	jQuery(".hide-if-change-password").show();
	jQuery(".show-if-change-password").hide();
	jQuery("#data_ci_pass").val("");
}

function submit_input_change_user_password() {
	if (
		confirm("Xác nhận thay đổi mật khẩu đăng nhập cho tài khoản này!") === true
	) {
		var a = jQuery("#data_ci_pass").val() || "";
		if (a.length >= 6) {
			document.admin_global_form.submit();
		} else {
			WGR_alert("Mật khẩu tối thiểu phải từ 6 ký tự trở lên", "error");
			jQuery("#data_ci_pass").focus();
		}
		return true;
	}
	return false;
}

function random_input_change_user_password() {
	var a = Math.random().toString(32).split(".")[1].substr(0, 8);
	var b = Math.random().toString(32).split(".")[1].substr(0, 9);
	jQuery("#data_ci_pass").val(a + "@" + b);
}

function check_user_email_before_add() {
	// tạo email theo họ tên -> dành cho trường hợp không có email
	var a = jQuery("#data_user_email").val() || "";
	if (a == "") {
		var b = jQuery("#data_display_name").val() || "";
		if (b != "") {
			b = g_func.non_mark_seo(b);
			b = b.replace(/\-/g, "");
			if (b != "") {
				a = b;
				jQuery("#data_user_email").val(a).change();
			}
		}
	}
	if (a != "" && a.includes("@") == false) {
		jQuery("#data_user_email").val(a + "@" + document.domain);
	}
	return true;
}

function before_submit_user_add() {
	check_user_email_before_add();
	return true;
}

//
jQuery("#data_user_email")
	.change(function () {
		var a = jQuery(this).val();
		if (a != "") {
			a = jQuery.trim(a);
			a = a.toLowerCase();
			jQuery(this).val(a);

			if (jQuery("#data_user_login").val() == "") {
				jQuery("#data_user_login").val(
					g_func.non_mark_seo(jQuery.trim(a.split("@")[0]))
				);
			}
		}
	})
	.keydown(function (e) {
		//console.log(e.keyCode);
		if (e.keyCode == 13) {
			var a = jQuery(this).val() || "";
			if (a != "" && a.includes("@") == false) {
				WGR_alert("Email format is not supported", "warning");
				setTimeout(() => {
					jQuery("#data_user_email").val(a + "@" + document.domain);
				}, 200);
			}
		}
	});

//
jQuery("#data_ci_pass")
	.focus(function () {
		jQuery(".redcolor-if-pass-focus").addClass("redcolor");
	})
	.blur(function () {
		jQuery(".redcolor-if-pass-focus").removeClass("redcolor");
	});

//
Submit_form_by_Ctrl_S();
