//
$(document).ready(function () {
	//console.log(zalo_app_id);
	replace_url_config_app("YOUR_ZALO_APP_ID", zalo_app_id);

	// các dữ liệu không khuyến khích sửa
	$("#data_zalooa_access_token, #data_zalooa_refresh_token")
		.attr({
			readonly: "readonly",
			title: "Bấm đúp chuột để cập nhật dữ liệu này",
		})
		.dblclick(function () {
			$(this).removeAttr("readonly");
		});

	// các dữ liệu không cho sửa
	$("#data_zalooa_expires_token").attr({
		disabled: "disabled",
		readonly: "readonly",
	});
});
