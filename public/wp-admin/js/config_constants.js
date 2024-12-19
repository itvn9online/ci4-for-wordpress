function click_set_empty_constants(for_id, set_val) {
	var a = jQuery("#" + for_id).attr("type") || "";
	// chức năng này chỉ áp dụng cho input text
	if (a != "text") {
		return false;
	}

	//
	jQuery("#" + for_id)
		.val(set_val)
		.trigger("change");

	//
	return true;
}

// mở 1 tab mới để test constants sau khi lưu
function open_home_for_test_config_constants() {
	WGR_alert("Open new tab for test your constants after change");
	setTimeout(() => {
		window.open(web_link, "_blank");
	}, 1000);
}

//
jQuery(".each-to-is-empty")
	.change(function (e) {
		var for_id = jQuery(this).data("id") || "";
		var result = false;
		if (for_id != "") {
			var set_val = "";
			if (jQuery(this).is(":checked")) {
				set_val = "IS_EMPTY";
			}
			result = click_set_empty_constants(for_id, set_val);
		}
		return result;
	})
	.each(function () {
		var for_id = jQuery(this).data("id") || "";
		if (for_id != "") {
			var a = jQuery("#" + for_id).attr("type") || "";
			// chức năng này chỉ áp dụng cho input text
			if (a != "text") {
				jQuery(this).off("change").prop("disabled", true);
			} else if (jQuery("#" + for_id).val() == "IS_EMPTY") {
				jQuery(this).prop("checked", true);
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
	jQuery("#data_MY_APP_TIMEZONE").append(str);
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
	jQuery("#data_SITE_LANGUAGE_SUPPORT")
		.attr({
			type: "hidden",
		})
		.after('<div class="cf">' + str + "</div>");

	//
	(function () {
		let a = jQuery("#data_SITE_LANGUAGE_SUPPORT").val() || "";
		if (a != "") {
			a = a.split(",");
			for (let i = 0; i < a.length; i++) {
				jQuery(".site-language-fixed[value='" + a[i] + "']").prop(
					"checked",
					true
				);
			}
		}
	})();

	//
	jQuery(".site-language-fixed").change(function () {
		let str = [];
		jQuery(".site-language-fixed").each(function () {
			if (jQuery(this).is(":checked")) {
				str.push(jQuery(this).val());
			}
		});
		// console.log(str);
		jQuery("#data_SITE_LANGUAGE_SUPPORT").val(str.join(",")).trigger("change");
	});

	//
	jQuery(".each-to-is-empty[data-id='data_SITE_LANGUAGE_SUPPORT']")
		.off("change")
		.prop("disabled", true);
})();

// code riêng cho phần chọn ngày tháng hiển thị
(function () {
	jQuery("#data_EBE_DATE_TEXT_FORMAT")
		.addClass("set-selected")
		.addClass("has-select2");
	// return false;

	//
	let a = jQuery("#data_EBE_DATE_TEXT_FORMAT").data("select") || "";
	if (a != "") {
		let b = jQuery(
			"#data_EBE_DATE_TEXT_FORMAT option[value='" + a + "']"
		).length;
		console.log(b);

		//
		if (b > 0) {
			jQuery("#data_EBE_DATE_TEXT_FORMAT option[value='" + a + "']").prop(
				"selected",
				true
			);
		}
	}
})();

//
/*
jQuery(document).ready(function () {
	action_highlighted_code("#current_dynamic_constants", "language-php");
});
*/
