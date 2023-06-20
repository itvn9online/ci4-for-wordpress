/*
 * lấy các input có sự kiện change -> để tránh việc update tùm lum
 */
var list_field_has_change = {};

function get_field_has_change(a) {
	//console.log(a);

	// chỉ thực thi với phần data
	if (a != "" && a.split("data[").length > 1) {
		a = a.replace("data[", "").split("]")[0];
		//console.log(a);

		// đủ điều kiện thì xác thực cho phép update
		if (typeof list_field_has_change[a] == "undefined") {
			//console.log(a);
			list_field_has_change[a] = 1;
			$("#list_field_has_change").val(JSON.stringify(list_field_has_change));
		}
	}
}

function done_field_has_change() {
	$("#list_field_has_change").val("");
	list_field_has_change = {};
}

// function này sẽ tìm phần ghi chú của config và thay thế bằng dữ liệu người dùng đã nhập vào -> tạo ra cái nhìn trực quan
function replace_url_config_app(key, val) {
	if (val == "") {
		console.log(key + ": val is EMPTY");
		return false;
	}
	$(".controls-text-note").each(function () {
		var a = $(this).html();
		//console.log(a);
		var findk = ["/" + key + "/", "/" + key + ".", "." + key + ".", "/" + key];
		for (var i = 0; i < findk.length; i++) {
			if (a.split(findk[i]).length > 1) {
				//console.log(a);
				var v = findk[i].replace(key, val);
				//console.log(v);
				for (var j = 0; j < 10; j++) {
					if (a.split(findk[i]).length > 1) {
						a = a.replace(findk[i], v);
					} else {
						break;
					}
				}
				$(this).html(a);
				//return false;
				//break;
			}
		}
	});
	return false;
}
