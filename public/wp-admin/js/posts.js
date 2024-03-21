// thêm nút add ảnh đại diện
add_and_show_post_avt("#post_meta_image", "", "medium");

// nạp text-editer cho phần tóm tắt nếu phát hiện thấy có sử dụng mã html
(function (str) {
	// không áp dụng cho menu
	if (
		typeof current_post_type != "undefined" &&
		current_post_type == "nav_menu"
	) {
		console.log(
			"%c" + "textediter not enable in: " + current_post_type,
			"color: red"
		);
		return false;
	}
	// khi có session này -> cũng không áp dụng
	if (localStorage.getItem("click-disable-editer") !== null) {
		console.log(
			"%c" + "textediter not enable because click-disable-editer",
			"color: orange"
		);
		return false;
	}

	// một số cấu trúc đặc trưng của html
	if (str != "" && str.includes("</") == true && str.includes(">") == true) {
		// cho hết dữ liệu về 1 dòng
		str = str.split("\n");
		let arr = [];
		for (let i = 0; i < str.length; i++) {
			arr.push($.trim(str[i]));
		}
		$("#data_post_excerpt").val(arr.join(" "));

		//
		console.log("Auto enable text-editer for post_excerpt");
		WGR_load_textediter("#data_post_excerpt");
		$('.click-enable-editer[data-for="data_post_excerpt"]').prop(
			"checked",
			true
		);
	}
})($("#data_post_excerpt").val() || "");

// 1 số editer chỉ kích hoạt khi có bấm
$(".click-enable-editer").change(function (e) {
	let jd = $(this).data("for") || "";
	if (jd != "") {
		//console.log(jd);
		// nếu đang checked
		if ($(this).is(":checked")) {
			//console.log("checked");
			localStorage.removeItem("click-disable-editer");

			// tạo textediter
			let h = $(this).data("height") || "";
			if (h == "" || h < 200) {
				h = 200;
			}

			//
			WGR_load_textediter("#" + jd, {
				height: h * 1,
			});
		} else {
			if (
				confirm(
					"Cần phải nạp lại trang để bỏ chế độ editer. Bạn có muốn tiếp tục không?"
				) === true
			) {
				// tạo session này xong nạp lại trang -> khi thấy có session này thì editer sẽ ko được nạp
				localStorage.setItem("click-disable-editer", Math.random());
				let a = window.location.href
					.split("&support_tab=")[0]
					.split("?support_tab=")[0];
				if (a.includes("?") == true) {
					a += "&";
				} else {
					a += "?";
				}
				window.location = a + "support_tab=data_post_excerpt";
			} else {
				$('.click-enable-editer[data-for="data_post_excerpt"]').prop(
					"checked",
					true
				);
			}
		}
	}
	//$(this).fadeOut();
});

//
function action_before_submit_post() {
	fixed_CLS_for_editer("iframe#Resolution_ifr");

	//
	$("#post_meta_image_size").trigger("change");

	//
	return true;
}

// xử lý đối với hình ảnh trong editer
function fixed_CLS_for_editer(for_iframe) {
	if ($(for_iframe).length < 1) {
		return false;
	}

	//
	let arr = [];
	jQuery(for_iframe)
		.contents()
		.find("img")
		.each(function () {
			let s = $(this).attr("src") || "";
			//console.log(s);

			//
			let w = $(this).attr("width") || "";
			//console.log(w);
			if (w == "") {
				w = $(this).width() || 0;
				//console.log(w);
				if (w * 1 > 0) {
					$(this).attr({
						width: Math.ceil(w),
					});

					//
					arr.push(s + " width: " + w);
				}
			}

			//
			let h = $(this).attr("height") || "";
			//console.log(h);
			if (h == "") {
				h = $(this).height() || 0;
				//console.log(h);
				if (h * 1 > 0) {
					$(this).attr({
						height: Math.ceil(h),
					});

					//
					arr.push(s + " height: " + h);
				}
			}
		});

	//
	if (arr.length < 1) {
		return false;
	}

	//
	console.log("%c" + for_iframe + " CLS", "color: green;");
	for (let i = 0; i < arr.length; i++) {
		console.log(arr[i]);
	}

	//
	return true;
}

// xử lý lần 1 lúc nạp xong document
$(document).ready(function () {
	fixed_CLS_for_editer("iframe#Resolution_ifr");
});

// lần 2 lúc nạp xong hình ảnh
$(window).on("load", function () {
	fixed_CLS_for_editer("iframe#Resolution_ifr");

	// tự động submit để cập nhật module mới cho bài viết
	if (typeof auto_update_module != "undefined" && auto_update_module * 1 > 0) {
		setTimeout(function () {
			console.log("Auto submit...");
			document.admin_global_form.submit();

			// tự động chuyển sang bài tiếp theo
			if (url_next_post != "") {
				console.log("Auto next: " + url_next_post);
				setTimeout(function () {
					window.location = url_next_post;
				}, 3000);
			} else {
				$(".show-if-end-function")
					.removeClass("orgcolor")
					.addClass("redcolor")
					.text("Cannot be determined next url...");
			}
		}, 3000);
	}
});

/**
 * tạo các option con cho phần select Danh mục cha
 */
(function (cats) {
	for (let x in cats) {
		// console.log(x);
		// console.log(cats[x]);

		//
		if ($("#" + x).length > 0 && cats[x] != "") {
			// chạy ajax nạp dữ liệu của taxonomy
			load_term_select_option(cats[x], x, function (data, jd) {
				// console.log(data);

				//
				$("#" + jd)
					.removeClass("set-selected")
					.append(create_term_select_option(data));

				// tạo lại selected
				WGR_set_prop_for_select("#" + jd);
				console.log(
					"#" + jd + " option length:",
					$("#" + jd + " option").length
				);

				//
				MY_select2("#" + jd);
			});
		}
	}
})({
	post_meta_post_category: typeof post_cat != "undefined" ? post_cat : "",
	post_meta_post_tags: typeof post_tags != "undefined" ? post_tags : "",
	post_meta_post_options:
		typeof post_options != "undefined" ? post_options : "",
});
