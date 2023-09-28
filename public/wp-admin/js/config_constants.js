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
		var for_id = $(this).attr("data-id") || "";
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
		var for_id = $(this).attr("data-id") || "";
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
