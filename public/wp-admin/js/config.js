//
function action_highlighted_code(for_id, for_language) {
	let for_name = for_id.slice(1);
	let highlighting = "highlighting" + for_name;
	let hl_content = "highlighting-content" + for_name;
	// gộp các thuộc tính lại để trong các hàm tiện cắt và xử lý
	let fors_id = [for_id, highlighting, hl_content].join(",");

	//
	if (typeof for_language == "undefined" || for_language == "") {
		for_language = "language-html";
	}

	//
	jQuery(for_id)
		.attr({
			oninput:
				"highlight.update(this.value, '" +
				fors_id +
				"'); highlight.sync_scroll(this, '" +
				fors_id +
				"');",
			onchange: "highlight.no_scroll('" + fors_id + "');",
			onscroll: "highlight.sync_scroll(this, '" + fors_id + "');",
			onkeydown: "highlight.check_tab(this, event, '" + fors_id + "');",
			// onkeyup: "highlight.no_scroll('" + fors_id + "');",
			// onblur: "highlight.show('" + fors_id + "')",
			spellcheck: "false",
		})
		.addClass("highlight-editing")
		// .off("change")
		// .removeClass("fix-textarea-height")
		.after(
			'<pre id="' +
				highlighting +
				'" class="highlighting" aria-hidden="true">' +
				'<code class="' +
				for_language +
				'" id="' +
				hl_content +
				'"></code>' +
				"</pre>"
		);

	//
	jQuery(for_id).parent("div").addClass("highlight-border");

	//
	let v = jQuery(for_id).val() || "";
	// if (v != "") {
	// jQuery(for_id).trigger("change");
	highlight.update(v, fors_id);
	// }
}

//
function set_configs_value(for_id, val) {
	jQuery(for_id).val(val);
}

// bắt ở nhiều sự kiện khác nhau -> vì có thể có sự kiện bị hủy bỏ ở giai đoạn khác
jQuery(".config-main input, .config-main select, .config-main textarea")
	.change(function () {
		get_field_has_change(jQuery(this).attr("name") || "");
	})
	.blur(function () {
		get_field_has_change(jQuery(this).attr("name") || "");
	})
	.focus(function () {
		get_field_has_change(jQuery(this).attr("name") || "");
	})
	.click(function () {
		get_field_has_change(jQuery(this).attr("name") || "");
	});

// Hiệu ứng khi click vào thay đổi màu sắc
jQuery(".click-to-set-site-color").click(function () {
	var a = jQuery(this).data("set") || "";

	if (a == "") {
		WGR_alert("Color picker not found", "error");
		return false;
	}

	var b =
		jQuery("input#" + a).val() ||
		jQuery("input#" + a).attr("placeholder") ||
		"";
	var n = prompt("Color code #:", b);
	//	console.log(n);

	// cho về mã hiện tại nếu người dùng hủy hoặc không nhập màu
	if (n == null || n == "") {
		n = b;
	}
	n = g_func.trim(n.replace(/\s/g, ""));
	if (n == "") {
		n = b;
	}

	// bỏ dấu # ở đầu đi để định dạng lại
	if (n.slice(0, 1) == "#") {
		n = n.slice(1);
	}

	// tự chuyển thành mã 6 màu nếu mã màu nhập vào là 3
	if (n.length == 3) {
		n =
			n.slice(0, 1) +
			n.slice(0, 1) +
			n.slice(1, 2) +
			n.slice(1, 2) +
			n.slice(2) +
			n.slice(2);
	}

	// đến đây, mã màu bắt buộc phải là 6 ký tự
	if (n.length != 6) {
		WGR_alert("Color code with 6 character", "error");
		return false;
	}

	// done
	jQuery("input#" + a)
		.val("#" + n)
		.trigger("change");

	//
	return false;
});

// reset màu về mặc định
jQuery(".click-to-reset-site-color").click(function () {
	var a = jQuery(this).data("set") || "";

	if (a == "") {
		WGR_alert("Color picker not found", "error");
		return false;
	}

	var b = jQuery("input#" + a).attr("placeholder") || "";
	if (b != "") {
		jQuery("input#" + a)
			.val(b)
			.trigger("change");
	}

	//
	return false;
});

//
jQuery(".auto-reset-site-color").each(function () {
	//console.log(jQuery(this).val());
	//console.log(jQuery(this).attr('value'));
	if (jQuery(this).val() == "" || jQuery(this).attr("value") == "") {
		//jQuery(this).trigger('click');
		jQuery(this).val(jQuery(this).attr("placeholder")).trigger("change");
	}
});

//
url_for_text_note();

//
jQuery(document).ready(function () {
	if (typeof action_trans_label == "function") {
		if (typeof arr_meta_default != "undefined") {
			action_trans_label(arr_meta_default);
		}
		if (typeof arr_trans_label != "undefined") {
			action_trans_label(arr_trans_label);
		}
	}
});

//
Submit_form_by_Ctrl_S();
