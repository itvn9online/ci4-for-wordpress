// biên dịch mã javascript trong firebase config sang mã để PHP đọc được
$("#data_g_firebase_config").change(function (e) {
	var a = $(this).val() || "";
	if (a != "") {
		$("#const-firebaseConfig").remove();
		$("body").append(
			'<script id="const-firebaseConfig">' +
				a.replace("const firebaseConfig", "var firebaseConfig") +
				"</script>"
		);
		//console.log(firebaseConfig);
		if (typeof firebaseConfig != "undefined") {
			$("#data_firebase_json_config").val(JSON.stringify(firebaseConfig));

			//
			replace_url_config_app(
				"YOUR_FIREBASE_PROJECT_ID",
				firebaseConfig.projectId
			);
		} else {
			$("#data_firebase_json_config").val("");
		}
		$("#data_firebase_json_config").trigger("change");
	}
});

//
$(document).ready(function () {
	//console.log(fb_app_id);
	replace_url_config_app("YOUR_FACEBOOK_APP_ID", fb_app_id);

	//
	if (($("#data_g_firebase_config").val() || "") != "") {
		$("#data_g_firebase_config").trigger("change");
	}

	// các dữ liệu không cho sửa
	$("#data_firebase_json_config").attr({
		//disabled: "disabled",
		readonly: "readonly",
	});
});
