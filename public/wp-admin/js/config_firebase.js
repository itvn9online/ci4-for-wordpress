// biên dịch mã javascript trong firebase config sang mã để PHP đọc được
jQuery("#data_g_firebase_config").change(function (e) {
	var a = jQuery(this).val() || "";
	if (a != "") {
		jQuery("#const-firebaseConfig").remove();
		jQuery("body").append(
			'<script id="const-firebaseConfig">' +
				a.replace("const firebaseConfig", "var firebaseConfig") +
				"</script>"
		);
		//console.log(firebaseConfig);
		if (typeof firebaseConfig != "undefined") {
			jQuery("#data_firebase_json_config").val(JSON.stringify(firebaseConfig));

			//
			replace_url_config_app(
				"YOUR_FIREBASE_PROJECT_ID",
				firebaseConfig.projectId
			);
		} else {
			jQuery("#data_firebase_json_config").val("");
		}
		jQuery("#data_firebase_json_config").trigger("change");
	}
});

//
jQuery(document).ready(function () {
	//console.log(fb_app_id);
	replace_url_config_app("YOUR_FACEBOOK_APP_ID", fb_app_id);

	//
	if ((jQuery("#data_g_firebase_config").val() || "") != "") {
		jQuery("#data_g_firebase_config").trigger("change");
	}

	// các dữ liệu không cho sửa
	jQuery("#data_firebase_json_config").attr({
		//disabled: "disabled",
		readonly: "readonly",
	});
});
