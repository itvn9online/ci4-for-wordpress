/*
 * lấy các input có sự kiện change -> để tránh việc update tùm lum
 */
var list_field_has_change = {};

function get_field_has_change(a) {
	//console.log(a);

	// chỉ thực thi với phần data
	if (a != "" && a.includes("data[") == true) {
		a = a.replace("data[", "").split("]")[0];
		//console.log(a);

		// đủ điều kiện thì xác thực cho phép update
		if (typeof list_field_has_change[a] == "undefined") {
			//console.log(a);
			list_field_has_change[a] = 1;
			jQuery("#list_field_has_change").val(
				JSON.stringify(list_field_has_change)
			);
		}
	}
}

function done_field_has_change() {
	jQuery("#list_field_has_change").val("");
	list_field_has_change = {};
}

// function này sẽ tìm phần ghi chú của config và thay thế bằng dữ liệu người dùng đã nhập vào -> tạo ra cái nhìn trực quan
function action_replace_config_app(a, key, val) {
	a = a.replace(key, val);
	//console.log(a);
	// nếu vẫn còn key
	if (a.includes(key) == true) {
		// lặp lại function -> đệ quy cho đến hết
		return action_replace_config_app(a, key, val);
	}
	return a;
}

function replace_url_config_app(key, val) {
	if (val == "") {
		console.log(key + ": val is EMPTY");
		return false;
	}
	jQuery(".controls-text-note").each(function () {
		var a = jQuery(this).html();
		//console.log(a);
		if (a.includes(key) == true) {
			jQuery(this).html(action_replace_config_app(a, key, val));
		}
	});
	return false;
}
