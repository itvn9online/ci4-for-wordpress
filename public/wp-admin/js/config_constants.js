function click_set_empty_constants(for_id, set_val) {
	var a = $("#" + for_id).attr("type") || "";
	// chức năng này chỉ áp dụng cho input text
	if (a != "text") {
		return false;
	}

	//
	$("#" + for_id)
		.val(set_val)
		.trigger("change");

	//
	return true;
}

//
$(".each-to-is-empty")
	.change(function (e) {
		var for_id = $(this).data("id") || "";
		var result = false;
		if (for_id != "") {
			var set_val = "";
			if ($(this).is(":checked")) {
				set_val = "IS_EMPTY";
			}
			result = click_set_empty_constants(for_id, set_val);
		}
		return result;
	})
	.each(function () {
		var for_id = $(this).data("id") || "";
		if (for_id != "") {
			var a = $("#" + for_id).attr("type") || "";
			// chức năng này chỉ áp dụng cho input text
			if (a != "text") {
				$(this).off("change").prop("disabled", true);
			} else if ($("#" + for_id).val() == "IS_EMPTY") {
				$(this).prop("checked", true);
			}
		}
	});

// thêm lựa chọn múi giờ cho website
(function () {
	// console.log(timezone_identifiers_list);
	let str = "";
	for (let i = 0; i < timezone_identifiers_list.length; i++) {
		str +=
			'<option value="' +
			timezone_identifiers_list[i] +
			'">' +
			timezone_identifiers_list[i] +
			"</option>";
	}
	$("#data_MY_APP_TIMEZONE").append(str);
	WGR_set_prop_for_select("#data_MY_APP_TIMEZONE");
	MY_select2("#data_MY_APP_TIMEZONE");
})();
