// bấm mở popup add comment
function add_comments_show(comment_ID) {
	jQuery("#data_comment_ID").val(0);
	jQuery("#data_comment_parent").val(0);
	jQuery("#AddCommentsModal").modal("show");

	// tự động điền dữ liệu vào form nếu có dữ liệu trong sessionStorage
	setTimeout(() => {
		get_ai_review_to_form(false);
	}, 500);
}

// bấm mở popup edit comment
function edit_comments_show(comment_ID) {
	if (typeof comment_ID == "undefined") {
		console.log("comment_ID", comment_ID);
		return false;
	}
	comment_ID *= 1;
	if (isNaN(comment_ID) || comment_ID < 1) {
		console.log("comment_ID", comment_ID);
		return false;
	}

	//
	jQuery("#data_comment_ID").val(comment_ID);
	jQuery("#data_comment_parent").val(0);
	jQuery("#AddCommentsModal").modal("show");
}

// bấm mở popup reply comment
function reply_comments_show(comment_parent) {
	if (typeof comment_parent == "undefined") {
		WGR_html_alert("Please select a comment to reply!", "error");
		// console.log("comment_parent", comment_parent);
		return false;
	}
	comment_parent *= 1;
	if (isNaN(comment_parent) || comment_parent < 1) {
		WGR_html_alert("Please select a comment to reply!", "error");
		// console.log("comment_parent", comment_parent);
		return false;
	}

	//
	jQuery("#data_comment_ID").val(0);
	jQuery("#data_comment_parent").val(comment_parent);
	jQuery("#AddCommentsModal").modal("show");
}

// xác nhận trước khi xóa 1 commment
function before_trash_comments() {
	return confirm("Confirm remove this comment!");
}

// sau khi update 1 comment thì load lại trang
function after_update_comments(comment_ID) {
	let support_tab = "commentsdiv";
	if (typeof comment_ID != "undefined") {
		comment_ID *= 1;
		if (!isNaN(comment_ID) && comment_ID > 0) {
			support_tab = "comment_ID" + comment_ID;
		}
	}

	// nạp lại trang này và di chuyển tới phần comment
	window.location =
		web_link +
		"sadmin/" +
		controller_slug +
		"/add?id=" +
		post_id +
		"&support_tab=" +
		support_tab;

	//
	return true;
}

// function lấy dữ liệu từ sessionStorage và điền vào form
function get_ai_review_to_form(show_alert = true) {
	let values = sessionStorage.getItem("ai_review_to_form");
	if (values === null || values.length < 1) {
		if (show_alert) {
			WGR_html_alert("No data found in sessionStorage!", "error");
		}
		return false;
	}
	values = JSON.parse(values);
	if (values.length < 1) {
		if (show_alert) {
			WGR_html_alert("No data found in sessionStorage!", "error");
		}
		return false;
	}
	console.log("values", values);

	// chạy vòng lặp rồi xem email nào chưa được sử dụng thì điền vào form
	let has_filled = false;
	for (let i = 0; i < values.length; i++) {
		let lineValues = values[i];
		if (lineValues.length < 4) {
			WGR_html_alert("Invalid data format in sessionStorage!", "error");
			continue;
		}

		// kiểm tra định dạng email phải có @ và dấu .
		if (lineValues[1].indexOf("@") < 0 || lineValues[1].indexOf(".") < 0) {
			WGR_html_alert("Email format is incorrect! " + lineValues[1], "error");
			continue;
		}

		// xem có .bypostauthor[data-email] nào trùng với email này không
		let email = lineValues[1].trim();
		let existingComment = jQuery(".bypostauthor[data-email='" + email + "']");
		if (existingComment.length > 0) {
			continue;
		}

		// điền vào form
		jQuery("#data_comment_author_name").val(lineValues[0].trim());
		jQuery("#data_comment_author_email").val(lineValues[1].trim());
		jQuery("#data_comment_title").val(lineValues[2].trim());
		jQuery("#data_comment_content").val(lineValues[3].trim());

		// đánh dấu đã điền dữ liệu vào form
		has_filled = true;

		// nếu đã điền dữ liệu vào form thì hiển thị thông báo
		WGR_html_alert("Data has been filled into the form!");

		// chỉ cần điền 1 lần là đủ
		break;
	}

	// nếu không có dữ liệu nào được điền vào form thì dọn dẹp sessionStorage
	if (!has_filled) {
		sessionStorage.removeItem("ai_review_to_form");
	}

	//
	return false;
}

// khi người dùng paste dữ liệu vào ô #ai_review_to_form thì tự động tách các giá trị và điền vào form
jQuery("#ai_review_to_form").on("change", function (e) {
	let data = $(this).val();
	if (data.length < 1) {
		return false;
	}

	// tách theo các ký tự xuống dòng
	data = data.replace(/\r\n/g, "\n").replace(/\r/g, "\n").replace(/\n/g, "\n");
	data = data.split("\n");

	// chạy vòng lăp để xử lý từng dòng, đúng định dạng thì cho vào 1 mảng xong lưu mảng này vào sessionStorage
	let values = [];
	for (let i = 0; i < data.length; i++) {
		let line = data[i].trim();
		if (line.length < 1) {
			continue;
		}

		// loại bỏ các ký tự xuống dòng và khoảng trắng thừa
		line = line.trim();
		if (line.length < 1) {
			continue;
		}

		// loại bỏ dấu | ở đầu và cuối dòng
		if (line.startsWith("|")) {
			line = line.substring(1);
			line = line.trim();
		}
		if (line.endsWith("|")) {
			line = line.substring(0, line.length - 1);
			line = line.trim();
		}

		// tách các giá trị
		let lineValues = line.split("\t");
		if (lineValues.length < 4) {
			lineValues = line.split("|");
			if (lineValues.length < 4) {
				WGR_html_alert(
					"Please paste the correct format: Full Name | Email | Review Title | Review Content",
					"error"
				);
				continue;
				// return false;
			}
		}

		// kiểm tra định dạng email phải có @ và dấu .
		if (lineValues[1].indexOf("@") < 0 || lineValues[1].indexOf(".") < 0) {
			WGR_html_alert("Email format is incorrect! " + lineValues[1], "error");
			continue;
		}

		// kiểm tra các giá trị không được rỗng
		let hasEmptyField = false;
		for (let j = 0; j < lineValues.length; j++) {
			if (lineValues[j].trim().length < 1) {
				WGR_html_alert("Please fill in all fields!", "error");
				hasEmptyField = true;
				break;
			}
		}
		if (hasEmptyField) {
			continue;
		}

		// cho vào mảng values
		values.push(lineValues);
	}
	console.log("values", values);

	// lưu vào sessionStorage
	if (values.length > 0) {
		sessionStorage.setItem("ai_review_to_form", JSON.stringify(values));
		WGR_html_alert("Data has been saved to sessionStorage!", "Success");

		//
		get_ai_review_to_form();
	}

	//
	return false;
});

jQuery(document).ready(function () {
	// nếu có #copy_ai_prompt
	if (jQuery("#copy_ai_prompt").length > 0) {
		let lang = jQuery("#admin-change-language").val() || "vn";
		if (lang == "vn") {
			lang = "tiếng Việt Nam";
		} else {
			lang = "English";
		}

		// thay thế {admin-language} trong #copy_ai_prompt
		let a = jQuery("#copy_ai_prompt").val();
		jQuery("#copy_ai_prompt").val(a.replace("{admin-language}", lang));
	}
});
