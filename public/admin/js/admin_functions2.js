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
