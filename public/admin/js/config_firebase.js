// function này sẽ tìm phần ghi chú của config và thay thế bằng dữ liệu người dùng đã nhập vào -> tạo ra cái nhìn trực quan
function replace_url_config_app(findk, key, val) {
	$(".controls-text-note").each(function () {
		var a = $(this).html();
		//console.log(a);
		if (a.split(findk).length > 1) {
			//console.log(a);
			if (val != "") {
				a = a.replace(key, val);
				a = a.replace(key, val);
				$(this).html(a);
			}
			return false;
		}
	});
	return false;
}

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
		console.log(firebaseConfig);
		if (typeof firebaseConfig != "undefined") {
			$("#data_firebase_json_config").val(JSON.stringify(firebaseConfig));

			//
			replace_url_config_app(
				"/YOUR_FIREBASE_PROJECT_ID.",
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
	replace_url_config_app(
		"/YOUR_FACEBOOK_APP_ID/",
		"YOUR_FACEBOOK_APP_ID",
		fb_app_id
	);

	//
	if (($("#data_g_firebase_config").val() || "") != "") {
		$("#data_g_firebase_config").trigger("change");
	}
});
