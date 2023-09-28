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

// kiểm tra nếu có giá lập review thì cần thiết lập đủ 3 giá trị
function check_home_rating_value() {
	var rating_value = $("#data_home_rating_value").val() || "";
	if (rating_value != "") {
		rating_value *= 1;
		if (isNaN(rating_value)) {
			WGR_alert(
				"Rating value chỉ được nhập số nguyên hoặc số thập phân",
				"error"
			);
			$("#data_home_rating_value").focus();
			return false;
		}

		//
		var rating_count = $("#data_home_rating_count").val() || "";
		if (rating_count == "") {
			WGR_alert("Rating count chưa được thiết lập", "error");
			$("#data_home_rating_count").focus();
			return false;
		}

		//
		var review_count = $("#data_home_review_count").val() || "";
		if (review_count == "") {
			WGR_alert("Review count chưa được thiết lập", "error");
			$("#data_home_review_count").focus();
			return false;
		}
	}

	//
	return true;
}

//
$(
	"#data_home_rating_value, #data_home_rating_count, #data_home_review_count"
).change(function () {
	check_home_rating_value();
});
