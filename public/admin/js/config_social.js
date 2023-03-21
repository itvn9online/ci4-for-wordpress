// thêm chức năng add ảnh cho form
add_and_show_post_avt("#data_image");

//
$("#data_zalo").change(function () {
	var a = $.trim($(this).val() || "");
	if (a != "") {
		a = a.replace(/\s/gi, "").split("/");
		if (a.length > 1) {
			a = a[a.length - 1];
		}
		$("#data_zalo_me")
			.val("https://zalo.me/" + a)
			.trigger("change");
	}
});
