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

// chỉnh lại size ảnh nếu có lựa chọn
$("#post_meta_image_size").change(function () {
	var avt_size = $(this).val() || "";
	if (avt_size != "") {
		console.log("avt size:", avt_size);

		// nếu là sử dụng kích cỡ thật
		if (avt_size == "image_origin") {
			// lấy cỡ large
			var img = $("#post_meta_image_large").val() || "";
			// xóa đi chữ large ở cuối rồi thêm vào thôi
			if (img != "") {
				$("#post_meta_image").val(img.replace("-large.", "."));
			}
		} else {
			// còn lại sẽ dò tìm kích cỡ ưng ý và thêm
			var img = $("#post_meta_" + avt_size).val() || "";
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

//
$(document).ready(function () {
	for_admin_global_checkbox();
});
