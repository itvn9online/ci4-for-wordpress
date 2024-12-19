//
jQuery(document).ready(function () {
	//console.log(zalo_app_id);
	replace_url_config_app("YOUR_ZALO_APP_ID", zalo_app_id);

	// các dữ liệu không khuyến khích sửa
	jQuery("#data_zalooa_access_token, #data_zalooa_refresh_token")
		.addClass("graycolor")
		.attr({
			readonly: "readonly",
			title:
				"Dữ liệu này không khuyến khích thay đổi thủ công! Nếu bạn vẫn muốn tiếp tục, hãy bấm đúp chuột để cập nhật dữ liệu này...",
		})
		.click(function () {
			WGR_alert(jQuery(this).attr("title"), "warning");
		})
		.dblclick(function () {
			jQuery(this).removeAttr("readonly");
		});

	// các dữ liệu không cho sửa
	jQuery("#data_zalooa_expires_token").attr({
		disabled: "disabled",
		readonly: "readonly",
	});

	//
	jQuery("#data_zalooa_webhook")
		.attr({
			readonly: "readonly",
			ondblclick: "click2Copy(this);",
		})
		.val(web_link + "zalos/webhook");

	//
	let a = jQuery("#data_zalooa_expires_token").val() || "";
	if (a != "") {
		a *= 1000;
		if (!isNaN(a)) {
			let tzoffset = new Date().getTimezoneOffset() * 60000; // offset in milliseconds
			a = new Date(a - tzoffset).toISOString().split(".")[0].replace("T", " ");
			jQuery("#data_zalooa_expires_token").after(" " + a);
		}
	}
});
