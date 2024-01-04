function set_new_post_url(url, post_name) {
	// thiết lập lại url sau khi update
	$(".set-new-url")
		.attr({
			href: url,
		})
		.html(url);

	// cập nhật lại luôn mục url cũ
	$("#old_postname").val(post_name);
}

function after_update_post() {
	if (typeof reload_preview_if_isset == "function") {
		reload_preview_if_isset();
	}
}

// chỉnh lại size ảnh nếu có lựa chọn
$("#post_meta_image_size").change(function () {
	//console.log(1);
	if ($("#post_meta_image").val() == "") {
		$(
			"#post_meta_image_medium, #post_meta_image_thumbnail, #post_meta_image_webp, #post_meta_image_medium_large, #post_meta_image_large"
		).val("");
		$(".for-post_meta_image img").hide();
		return false;
	}

	//
	let avt_size = $(this).val() || "";
	if (avt_size != "") {
		console.log("avt size:", avt_size);

		// nếu là sử dụng kích cỡ thật
		if (avt_size == "image_origin") {
			// lấy cỡ large
			let img = $("#post_meta_image_large").val() || "";
			console.log(img);
			// xóa đi chữ large ở cuối rồi thêm vào thôi
			if (img != "") {
				$("#post_meta_image").val(img.replace("-large.", "."));
			}
		} else {
			// còn lại sẽ dò tìm kích cỡ ưng ý và thêm
			let img = $("#post_meta_" + avt_size).val() || "";
			console.log(img);
			if (img != "") {
				$("#post_meta_image").val(img);
			}
		}

		//
		localStorage.setItem("post-meta-image-size", avt_size);
	}
});

// khi các post meta được bỏ check -> sẽ có 1 post meta khác được checked -> để lệnh update còn biết cái nào bỏ check mà remove
$(".post_uncheck_meta").change(function () {
	let a = $(this).attr("id") || "";
	if (a != "") {
		a = a.replace("post_meta_", "post_uncheck_meta_");
		if ($("." + a).length > 0) {
			console.log("post_uncheck_meta_:", a);
			if ($(this).is(":checked")) {
				$("." + a).prop("checked", false);
			} else {
				$("." + a).prop("checked", true);
			}
		}
	}
});

// select sẵn size ảnh nếu có
(function () {
	if ($("#post_meta_image_size").length < 1) {
		return false;
	}

	//
	let a = localStorage.getItem("post-meta-image-size");
	//console.log(a);
	if (a !== null && a != "") {
		let b = $("#post_meta_image_size").val("data-select") || "";
		if (b != "") {
			$("#post_meta_image_size").val(a).trigger("change");
		}
	}
})();

//
$(document).ready(function () {
	for_admin_global_checkbox();
});

// với menu ko hỗ trợ bấm Ctrl + S -> vì còn phải chạy qua lệnh builder menu nữa
if (
	typeof current_post_type == "undefined" ||
	current_post_type != "nav_menu"
) {
	Submit_form_by_Ctrl_S();
}
