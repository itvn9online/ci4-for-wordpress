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

// mở 1 tab mới để test constants sau khi lưu
function open_home_for_test_config_constants() {
	WGR_alert("Open new tab for test your constants after change");
	setTimeout(function () {
		window.open(web_link, "_blank");
	}, 1000);
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

// thêm lựa chọn ngôn ngữ hiển thị cho website
(function () {
	let str = "";
	for (let i = 0; i < site_language_fixed.length; i++) {
		str +=
			'<label class="lf f33"><input type="checkbox" name="site_language_fixed[]" value="' +
			site_language_fixed[i].value +
			'" class="site-language-fixed" /> ' +
			site_language_fixed[i].text +
			" (" +
			site_language_fixed[i].value +
			")</label>";
	}

	//
	$("#data_SITE_LANGUAGE_SUPPORT")
		.attr({
			type: "hidden",
		})
		.after('<div class="cf">' + str + "</div>");

	//
	(function () {
		let a = $("#data_SITE_LANGUAGE_SUPPORT").val() || "";
		if (a != "") {
			a = a.split(",");
			for (let i = 0; i < a.length; i++) {
				$(".site-language-fixed[value='" + a[i] + "']").prop("checked", true);
			}
		}
	})();

	//
	$(".site-language-fixed").change(function () {
		let str = [];
		$(".site-language-fixed").each(function () {
			if ($(this).is(":checked")) {
				str.push($(this).val());
			}
		});
		// console.log(str);
		$("#data_SITE_LANGUAGE_SUPPORT").val(str.join(",")).trigger("change");
	});

	//
	$(".each-to-is-empty[data-id='data_SITE_LANGUAGE_SUPPORT']")
		.off("change")
		.prop("disabled", true);
})();

// code riêng cho phần chọn ngày tháng hiển thị
(function () {
	$("#data_EBE_DATE_TEXT_FORMAT")
		.addClass("set-selected")
		.addClass("has-select2");
	// return false;

	//
	let a = $("#data_EBE_DATE_TEXT_FORMAT").data("select") || "";
	if (a != "") {
		let b = $("#data_EBE_DATE_TEXT_FORMAT option[value='" + a + "']").length;
		console.log(b);

		//
		if (b > 0) {
			$("#data_EBE_DATE_TEXT_FORMAT option[value='" + a + "']").prop(
				"selected",
				true
			);
		}
	}
})();

//
/*
$(document).ready(function () {
	action_highlighted_code("#current_dynamic_constants", "language-php");
});
*/
