function post_admin_permalink(post_type, id, controller_slug) {
	var url = web_link + "admin/" + controller_slug + "/add";
	if (id > 0) {
		url += "?id=" + id;
	}
	return url;
}

// chỉ trả về link admin của 1 term
function term_admin_permalink(taxonomy, id, controller_slug) {
	var url = web_link + "admin/" + controller_slug + "/add";
	if (id > 0) {
		url += "?id=" + id;
	}
	return url;
}

// tính toán lại chiều cao của iframe -> tránh mấy cái scroll vướng víu
function re_height_iframe_editer(for_id, if_id, max_i) {
	if (typeof max_i != "number") {
		max_i = 20;

		// đồng bộ thông số iframe id
		if_id = for_id;
		if (if_id.substr(0, 1) == "#") {
			if_id = if_id.substr(1);
		}
		if_id = if_id + "_ifr";
		console.log(if_id);
	} else if (max_i < 0) {
		console.log("max_i in re_height_iframe_editer");
		return false;
	}

	//
	if (document.getElementById(if_id) === null) {
		setTimeout(function () {
			re_height_iframe_editer(for_id, if_id, max_i - 1);
		}, 200);
		return false;
	}
	//console.log(document.getElementById(if_id));

	//
	/*
	jQuery("#" + if_id).resize(function () {
		console.log(for_id);
		re_height_iframe_editer(for_id);
	});
	*/

	//
	var iframe_h = jQuery("#" + if_id).height();

	//
	var iframe_body_h =
		jQuery("#" + if_id)
			.contents()
			.find("body")
			.height() ||
		document.getElementById(if_id).contentWindow.document.body.offsetHeight;
	//console.log("iframe body h:", iframe_body_h);
	iframe_body_h *= 1;
	if (iframe_h > iframe_body_h) {
		return false;
	}
	iframe_body_h += 90;
	//console.log("iframe body h:", iframe_body_h);

	//
	$("#" + if_id).css({
		height: iframe_body_h + "px",
	});
}
