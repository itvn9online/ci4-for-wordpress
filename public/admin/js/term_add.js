//console.log(arr_list_category);

//
function set_new_term_url(url, slug) {
	// thiết lập lại url sau khi update
	$(".set-new-url")
		.attr({
			href: url,
		})
		.html(url);

	// cập nhật lại luôn mục url cũ
	$("#old_slug").val(slug);
}

function after_update_term() {
	if (typeof reload_preview_if_isset == "function") {
		reload_preview_if_isset();
	}
}

/*
 * tạo các option con cho phần select Danh mục cha
 */
if ($("#data_parent").length > 0) {
	// chạy ajax nạp dữ liệu của taxonomy
	load_term_select_option(set_parent, "data_parent", function (data, jd) {
		//console.log(data);
		$("#data_parent")
			.removeClass("set-selected")
			.append(create_term_select_option(data));

		// disabled option của term hiện tại đi -> không để nó chọn chính nó làm cha
		if (data_term_id > 0) {
			$('#data_parent option[value="' + data_term_id + '"]').prop(
				"disabled",
				true
			);
		}

		// tạo lại selected
		WGR_set_prop_for_select("#data_parent");
		MY_select2("#data_parent");
	});
}

// thêm nút add ảnh đại diện
add_and_show_post_avt("#data_term_avatar", "", "medium");
add_and_show_post_avt("#data_term_favicon", "", "medium");

// không cho submit liên tục
var submit_if_ctrl_s = false;
$(document).bind("keyup keydown", function (e) {
	// khi người dùng ctrl + s -> save
	if (e.ctrlKey && e.which == 83) {
		if (submit_if_ctrl_s === false) {
			console.log("Submit form by Ctrl + S");
			submit_if_ctrl_s = true;
			setTimeout(function () {
				submit_if_ctrl_s = false;
			}, 600);

			//
			document.admin_global_form.submit();
		}
		return false;
	}
	return true;
});
