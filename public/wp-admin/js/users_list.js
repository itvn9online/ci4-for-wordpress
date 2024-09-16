function show_link_of_user_exist(user_id) {
	// console.log(user_id);

	//
	if ($(".admin-search-form").length > 0) {
		// hiển thị link dẫn đến tài khoản trùng lặp
		$(".admin-search-form").after(
			'<div><a href="sadmin/' +
				controller_slug +
				"/add?id=" +
				user_id +
				'" target="_blank">View exist account #' +
				user_id +
				"</a></div>"
		);
	} else {
		console.log("%c" + ".admin-search-form not found!", "color: orange");
	}
}

//
$(document).ready(function () {
	$(".click-show-hidden-data").click(function () {
		$(this).html($(this).attr("title"));
	});
});
