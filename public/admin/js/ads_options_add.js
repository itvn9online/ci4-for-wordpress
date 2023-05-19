function WGR_widget_add_custom_style_to_field() {
	var arr_my_class = {
		bgtrans: "eb-blog-avt background-color: transparent.",
		bgcover: "eb-blog-avt background-size: 100% (cố định 2 chiều).",
		bgcontain: "eb-blog-avt background-size: contain (tự fixed 1 chiều).",
		bgwidth: "eb-blog-avt background-size: 100% auto (width 100% hight auto).",
		bghight: "eb-blog-avt background-size: auto 100% (width auto hight 100%).",
		bgradius: "eb-blog-avt border-radius: 50%.",
		noborder: "eb-blog-avt border: 0 none.",
		"noborder-widget-title": "eb-widget-title border: 0 none.",
		hideavt: "eb-blog-avt opacity: .001.",
		nomargin: "không tạo giãn cách giữa các LI.",
		"oneline-in-mobile": "Ép buộc về một dòng trên phiên bản mobile.",
		"title-center":
			"Căn chữ ra giữa đồng thời tạo style mới cho tiêu đề chính.",
		"title-bold": "in đậm tiêu đề chính.",
		"title-upper": "viết HOA tiêu đề chính.",
		"title-line": "thêm gạch ngang trên tiêu đề chính.",
		"title-line50": ".title-line + with: 50%",
		"title-line-bg": ".title-line + default-bg",
		"title-top-line": ".title-line + top",
		"title-bottom-line": ".title-line + bottom",
		"title-line20": ".title-line + width 20%, max-width 90px",
		"title-line38": ".title-line + width 38%, max-width 250px",
		"height-auto-title": "đặt style eb-blog-title height: auto",
		"height-auto-gioithieu": "đặt style eb-blog-gioithieu height: auto",
		"show-view-more": "hiển thị nút xem thêm (nếu có)",
		mcb: "gán màu cơ bản cho tiêu đề của danh mục widget",
		"global-title":
			"Class CSS cho tiêu đề widget (class này sẽ dùng chung cho toàn bộ website)",
		"global-home-title":
			"Class CSS cho tiêu đề widget (class này sẽ dùng cho trang chủ)",
		"global-post-title": "Class CSS cho tiêu đề bài viết",
	};
	var str = "";
	for (var x in arr_my_class) {
		str +=
			'<div><strong data-value="' +
			x +
			'" class="cur click_add_widget_class"><i class="fa fa-minus-square"></i> ' +
			x +
			"</strong>: " +
			arr_my_class[x] +
			"</div>";
	}
	$("#term_meta_custom_style").after(str);

	//
	jQuery(".click_add_widget_class")
		.off("click")
		.click(function () {
			var a = jQuery(this).attr("data-value") || "",
				cl = 0;

			if (a != "") {
				var b = jQuery("#term_meta_custom_style").val() || "";

				var c = "";
				if (b == "") {
					c = a;
					cl = 1;
				} else {
					// tạo khoảng trắng 2 đầu để còn kiểm tra dữ liệu đã add rồi hay chưa
					b = " " + jQuery.trim(b) + " ";

					// xóa
					if (b.split(" " + a + " ").length > 1) {
						c = b.replace(" " + a + " ", "");
					}
					// thêm
					else {
						c = a + b;
						cl = 1;
					}
				}
				jQuery("#term_meta_custom_style").val(jQuery.trim(c)).change();

				// tạo hiệu ứng thay đổi để người dùng dễ nhìn
				if (cl === 1) {
					jQuery("i.fa", this)
						.removeClass("fa-minus-square")
						.addClass("fa-check-square");
				} else {
					jQuery("i.fa", this)
						.addClass("fa-minus-square")
						.removeClass("fa-check-square");
				}
			}

			return false;
		});

	//
	var a = $("#term_meta_custom_style").val() || "";

	if (a != "") {
		a = a.split(" ");

		for (var i = 0; i < a.length; i++) {
			jQuery('.click_add_widget_class[data-value="' + a[i] + '"] i.fa')
				.removeClass("fa-minus-square")
				.addClass("fa-check-square");
		}
	}
}

//
WGR_widget_add_custom_style_to_field();

// khi người dùng thay đổi số lượng bản ghi cần hiển thị
function set_col_for_ads_row(a, in_id) {
	var b = $(in_id).val() || "";
	if (b == "") {
		var c = "";
		$(in_id + " option").each(function () {
			if ($.trim($(this).html()) == a) {
				c = $(this).attr("value") || "";
			}
		});
		console.log(c);
		if (c != "") {
			$(in_id).val(c).trigger("change");
		}
	}
}

$("#term_meta_post_number").change(function () {
	var a = $(this).val() || "";
	// tự select các giá trị khác tương ứng -> đỡ phải select nhiều
	if (a != "" && a * 1 > 0) {
		// tùy vào số lượng bản ghi cần hiển thị mà đưa ra số cột tương ứng
		set_col_for_ads_row(a * 1 > 3 ? "3" : a, "#post_meta_num_line");
		set_col_for_ads_row(
			a * 1 > 3 ? "3" : a * 1 > 2 ? "2" : a,
			"#post_meta_num_medium_line"
		);
		set_col_for_ads_row(a * 1 > 2 ? "2" : a, "#post_meta_num_small_line");
	}
});

//
(function () {
	// nếu không có tệp html nào thì ẩn nó đi thôi
	console.log(arr_custom_cloumn);
	var str = "";
	for (var i = 0; i < arr_custom_cloumn.length; i++) {
		str +=
			'<option value="' +
			arr_custom_cloumn[i].split(".")[0] +
			'">' +
			arr_custom_cloumn[i] +
			"</option>";
	}
	$("#post_meta_post_custom_cloumn").append(str);
})();
