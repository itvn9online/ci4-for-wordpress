/**
 * File chứa function dùng chung của bảng posts
 */

// update lượt xem cho post -> do web có dùng cache nên update qua đây mới đúng số
function update_post_viewed(fview, for_post_id, for_post_author) {
	if (typeof for_post_id != "number" || for_post_id < 1) {
		console.log("%c for_post_id is undefined", "color: red");
		return false;
	}
	if (typeof for_post_author != "number" || for_post_author < 1) {
		for_post_author = 0;
	}
	if (typeof fview != "number") {
		fview = 1;
	} else if (fview < 1) {
		console.log("%c fview is ZERO", "color: red");
		return false;
	}

	//
	var k = "update_post_viewed" + for_post_id;
	var a = sessionStorage.getItem(k);
	if (a !== null) {
		if (WGR_config.cf_tester_mode > 0) {
			console.log("CANCEL by update-post-viewed:", a);
		}
		return false;
	}

	//
	jQuery.ajax({
		type: "POST",
		url: "ajaxs/update_post_viewed",
		dataType: "json",
		//crossDomain: true,
		data: {
			current_user_id: WGR_config.current_user_id,
			pid: for_post_id,
			post_author: for_post_author,
			fview: fview,
		},
		success: function (data) {
			if (WGR_config.cf_tester_mode > 0) {
				console.log("update-post-viewed:", data);
			}
			sessionStorage.setItem(k, Math.floor(Date.now() / 1000));
		},
	});
}

//
update_post_viewed(
	typeof custom_fview == "number" ? custom_fview : 1,
	typeof post_id == "number" ? post_id : 0,
	typeof post_author == "number" ? post_author : 0
);
