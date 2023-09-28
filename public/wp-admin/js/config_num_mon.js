function convert_option_number(obj) {
	var a = $.trim(obj.value);
	if (a != "") {
		a = g_func.float_only(a);
		if (isNaN(a)) {
			a = "0";
		}
		//console.log(obj.value);
		obj.value = a;
	}
}

//
console.log("%c Chạy vòng lặp thay thế text cho label", "color: green;");
//action_trans_label(arr_meta_default);
//action_trans_label(arr_trans_label);
