/*
 * luôn xóa phần pass email -> không cho hiển thị
 */
$("#data_smtp_host_show_pass")
	.change(function () {
		$("#data_smtp_host_pass").val($(this).val()).trigger("blur");
	})
	.focus(function () {
		$(this).val($("#data_smtp_host_pass").val() || "");
	})
	.blur(function () {
		$("#data_smtp_host_pass").val($(this).val()).trigger("blur");
		$(this).val("");
	});

//
$("#data_smtp2_host_show_pass")
	.change(function () {
		$("#data_smtp2_host_pass").val($(this).val()).trigger("blur");
	})
	.focus(function () {
		$(this).val($("#data_smtp2_host_pass").val() || "");
	})
	.blur(function () {
		$("#data_smtp2_host_pass").val($(this).val()).trigger("blur");
		$(this).val("");
	});

/*
 * khi bấm test email -> kiểm tra trường bắt buộc
 */
/*
$('.click-check-email-test').click(function () {
    if ($.trim($('#data_smtp_test_email').val()) == '') {
        WGR_alert('Vui lòng nhập email người nhận sau đó lưu lại rồi mới test', 'error');
        $('#data_smtp_test_email').focus();
        return false;
    }
    return true;
});
*/

//
$("#data_smtp_secure").change(function () {
	var a = $(this).val() || "";
	if (a == "ssl") {
		$("#data_smtp_host_port").val(465).trigger("change");
	} else if (a == "tls") {
		$("#data_smtp_host_port").val(587).trigger("change");
	}
});

//
$("#data_smtp2_secure").change(function () {
	var a = $(this).val() || "";
	if (a == "ssl") {
		$("#data_smtp2_host_port").val(465).trigger("change");
	} else if (a == "tls") {
		$("#data_smtp2_host_port").val(587).trigger("change");
	}
});

//
$("#data_smtp_host_user").change(function () {
	var a = $(this).val() || "";
	if (a.split("@gmail.").length > 1) {
		$("#data_smtp_host_name").val("smtp.gmail.com").trigger("change");
	}
});

//
$("#data_smtp2_host_user").change(function () {
	var a = $(this).val() || "";
	if (a.split("@gmail.").length > 1) {
		$("#data_smtp2_host_name").val("smtp.gmail.com").trigger("change");
	}
});
