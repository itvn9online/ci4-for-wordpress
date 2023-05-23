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
		} else {
			$("#data_firebase_json_config").val("");
		}
		$("#data_firebase_json_config").trigger("change");
	}
});
