/*
 * luôn xóa phần pass email -> không cho hiển thị
 */
jQuery("#data_smtp_host_show_pass")
	.change(function () {
		jQuery("#data_smtp_host_pass").val(jQuery(this).val()).trigger("blur");
	})
	.focus(function () {
		jQuery(this).val(jQuery("#data_smtp_host_pass").val() || "");
	})
	.blur(function () {
		jQuery("#data_smtp_host_pass").val(jQuery(this).val()).trigger("blur");
		jQuery(this).val("");
	});

//
jQuery("#data_smtp2_host_show_pass")
	.change(function () {
		jQuery("#data_smtp2_host_pass").val(jQuery(this).val()).trigger("blur");
	})
	.focus(function () {
		jQuery(this).val(jQuery("#data_smtp2_host_pass").val() || "");
	})
	.blur(function () {
		jQuery("#data_smtp2_host_pass").val(jQuery(this).val()).trigger("blur");
		jQuery(this).val("");
	});

/*
 * khi bấm test email -> kiểm tra trường bắt buộc
 */
/*
jQuery('.click-check-email-test').click(function () {
    if (jQuery.trim(jQuery('#data_smtp_test_email').val()) == '') {
        WGR_alert('Vui lòng nhập email người nhận sau đó lưu lại rồi mới test', 'error');
        jQuery('#data_smtp_test_email').focus();
        return false;
    }
    return true;
});
*/

//
jQuery("#data_smtp_secure").change(function () {
	var a = jQuery(this).val() || "";
	if (a == "ssl") {
		jQuery("#data_smtp_host_port").val(465).trigger("change");
	} else if (a == "tls") {
		jQuery("#data_smtp_host_port").val(587).trigger("change");
	}
});

//
jQuery("#data_smtp2_secure").change(function () {
	var a = jQuery(this).val() || "";
	if (a == "ssl") {
		jQuery("#data_smtp2_host_port").val(465).trigger("change");
	} else if (a == "tls") {
		jQuery("#data_smtp2_host_port").val(587).trigger("change");
	}
});

//
jQuery("#data_smtp_host_user").change(function () {
	var a = jQuery(this).val() || "";
	if (a.includes("@gmail.") == true) {
		jQuery("#data_smtp_host_name").val("smtp.gmail.com").trigger("change");
	}
});

//
jQuery("#data_smtp2_host_user").change(function () {
	var a = jQuery(this).val() || "";
	if (a.includes("@gmail.") == true) {
		jQuery("#data_smtp2_host_name").val("smtp.gmail.com").trigger("change");
	}
});
