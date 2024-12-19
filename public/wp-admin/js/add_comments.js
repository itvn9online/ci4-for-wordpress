// bấm mở popup add comment
function add_comments_show(comment_ID) {
	jQuery("#data_comment_ID").val(0);
	jQuery("#data_comment_parent").val(0);
	jQuery("#AddCommentsModal").modal("show");
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
		console.log("comment_parent", comment_parent);
		return false;
	}
	comment_parent *= 1;
	if (isNaN(comment_parent) || comment_parent < 1) {
		console.log("comment_parent", comment_parent);
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
