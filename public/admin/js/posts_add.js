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
	var avt_size = $(this).val() || "";
	if (avt_size != "") {
		console.log("avt size:", avt_size);

		// nếu là sử dụng kích cỡ thật
		if (avt_size == "image_origin") {
			// lấy cỡ large
			var img = $("#post_meta_image_large").val() || "";
			console.log(img);
			// xóa đi chữ large ở cuối rồi thêm vào thôi
			if (img != "") {
				$("#post_meta_image").val(img.replace("-large.", "."));
			}
		} else {
			// còn lại sẽ dò tìm kích cỡ ưng ý và thêm
			var img = $("#post_meta_" + avt_size).val() || "";
			console.log(img);
			if (img != "") {
				$("#post_meta_image").val(img);
			}
		}

		//
		localStorage.setItem("post-meta-image-size", avt_size);
	}
});

// select sẵn size ảnh nếu có
(function () {
	if ($("#post_meta_image_size").length === 0) {
		return false;
	}

	//
	var a = localStorage.getItem("post-meta-image-size");
	//console.log(a);
	if (a !== null && a != "") {
		var b = $("#post_meta_image_size").val("data-select") || "";
		if (b != "") {
			$("#post_meta_image_size").val(a).trigger("change");
		}
	}
})();

// không cho submit liên tục
var submit_if_ctrl_s = false;
$(document)
	.ready(function () {
		for_admin_global_checkbox();
	})
	.bind("keyup keydown", function (e) {
		// khi người dùng ctrl + s -> save
		if (e.ctrlKey && e.which == 83) {
			if (submit_if_ctrl_s === false) {
				console.log("Submit form by Ctrl + S");
				submit_if_ctrl_s = true;
				setTimeout(function () {
					submit_if_ctrl_s = false;
				}, 600);

				// kiểm tra form trước khi submit
				if (action_before_submit_post() === true) {
					document.admin_global_form.submit();
				}
			}
			return false;
		}
		return true;
	});
