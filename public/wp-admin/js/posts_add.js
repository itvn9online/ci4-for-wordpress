function set_new_post_url(url, post_name) {
	// thiết lập lại url sau khi update
	jQuery(".set-new-url")
		.attr({
			href: url,
		})
		.html(url);

	// cập nhật lại luôn mục url cũ
	jQuery("#old_postname").val(post_name);
	jQuery("#old_permalink").val(url);
}

function after_update_post() {
	if (typeof reload_preview_if_isset == "function") {
		reload_preview_if_isset();
	}
}

// chỉnh lại size ảnh nếu có lựa chọn
jQuery("#post_meta_image_size").change(function () {
	//console.log(1);
	if (jQuery("#post_meta_image").val() == "") {
		jQuery(
			"#post_meta_image_medium, #post_meta_image_thumbnail, #post_meta_image_webp, #post_meta_image_medium_large, #post_meta_image_large"
		).val("");
		jQuery(".for-post_meta_image img").hide();
		return false;
	}

	//
	let avt_size = jQuery(this).val() || "";
	if (avt_size != "") {
		console.log("avt size:", avt_size);

		// nếu là sử dụng kích cỡ thật
		if (avt_size == "image_origin") {
			// lấy cỡ large
			let img = jQuery("#post_meta_image_large").val() || "";
			console.log(img);
			// xóa đi chữ large ở cuối rồi thêm vào thôi
			if (img != "") {
				jQuery("#post_meta_image").val(img.replace("-large.", "."));
			}
		} else {
			// còn lại sẽ dò tìm kích cỡ ưng ý và thêm
			let img = jQuery("#post_meta_" + avt_size).val() || "";
			console.log(img);
			if (img != "") {
				jQuery("#post_meta_image").val(img);
			}
		}

		//
		localStorage.setItem("post-meta-image-size", avt_size);
	}
});

// khi các post meta được bỏ check -> sẽ có 1 post meta khác được checked -> để lệnh update còn biết cái nào bỏ check mà remove
jQuery(".post_uncheck_meta").change(function () {
	let a = jQuery(this).attr("id") || "";
	if (a != "") {
		a = a.replace("post_meta_", "post_uncheck_meta_");
		if (jQuery("." + a).length > 0) {
			console.log("post_uncheck_meta_:", a);
			if (jQuery(this).is(":checked")) {
				jQuery("." + a).prop("checked", false);
			} else {
				jQuery("." + a).prop("checked", true);
			}
		}
	}
});

// select sẵn size ảnh nếu có
(function () {
	if (jQuery("#post_meta_image_size").length < 1) {
		return false;
	}

	//
	let a = localStorage.getItem("post-meta-image-size");
	//console.log(a);
	if (a !== null && a != "") {
		let b = jQuery("#post_meta_image_size").val("data-select") || "";
		if (b != "") {
			jQuery("#post_meta_image_size").val(a).trigger("change");
		}
	}
})();

//
jQuery(document).ready(function () {
	for_admin_global_checkbox();

	//
	show_input_length_char("data_post_title");
	jQuery("#data_post_title").trigger("change");

	//
	show_input_length_char("post_meta_meta_title");
	jQuery("#post_meta_meta_title").trigger("change");

	//
	show_input_length_char("post_meta_meta_description");
	jQuery("#post_meta_meta_description").trigger("change");

	// thêm span vào .control-post_meta_post_rating_value
	jQuery("#post_meta_post_rating_value")
		.parent()
		.find(".controls-text-note")
		.append(
			' <span class="cur bluecolor click-create-random-rating">Bấm vào đây để tạo dữ liệu ngẫu nhiên!</span>'
		);
	jQuery(".click-create-random-rating").click(function () {
		let has_changed = false;

		// nếu #post_meta_post_rating_value không có giá trị thì tạo mới
		if ((jQuery("#post_meta_post_rating_value").val() || "") == "") {
			jQuery("#post_meta_post_rating_value").val(
				g_func.getRandomInt(45, 50) / 10
			);
			has_changed = true;
		}

		// nếu #post_meta_post_rating_count không có giá trị thì tạo mới
		if ((jQuery("#post_meta_post_rating_count").val() || "") == "") {
			jQuery("#post_meta_post_rating_count").val(g_func.getRandomInt(1, 70));
			has_changed = true;
		}

		//
		if (has_changed === false) {
			WGR_html_alert(
				"Không có dữ liệu nào được khởi tạo do đã có dữ liệu tồn tại trước đó!",
				"warning"
			);
		}
	});
});

// với menu ko hỗ trợ bấm Ctrl + S -> vì còn phải chạy qua lệnh builder menu nữa
if (
	typeof current_post_type == "undefined" ||
	current_post_type != "nav_menu"
) {
	Submit_form_by_Ctrl_S();
}
