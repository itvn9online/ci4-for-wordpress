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
	var wh = $(window).height();
	if (iframe_body_h > wh) {
		iframe_body_h = Math.ceil((wh / 100) * 75);
	}
	// console.log("iframe body h:", iframe_body_h);
	// console.log("body h:", wh);

	//
	$("#" + if_id).css({
		height: iframe_body_h + "px",
	});
}

// tìm url trong text vào tạo link
function WGR_urlify(text) {
	var urlRegex = /(https?:\/\/[^\s]+)/g;
	return text.replace(urlRegex, function (url) {
		url = url.replace(web_link, "");
		if (url == "") {
			url = web_link;
		}
		return '<a href="' + url + '">' + url + "</a>";
	});
	// or alternatively
	// return text.replace(urlRegex, '<a href="$1">$1</a>')
}

function WGR_set_nofollow() {
	var links = document.links;
	for (var i = 0; i < links.length; i++) {
		if (links[i].hostname == window.location.hostname) {
			continue;
		}
		links[i].rel = "nofollow";
		links[i].target = "_blank";
	}
}

function url_for_text_note() {
	$(".controls-text-note").each(function () {
		var a = $(this).html();
		if (a.split("://").length > 1) {
			a = WGR_urlify(a);
			//console.log(a);
			$(this).html(a);
		}
	});
}

// Tạo khung tìm kiếm theo các label đang có trong trang hiện tại
function create_search_by_label() {
	var str = "";
	$("label").each(function () {
		var a = $(this).attr("for") || "";
		if (a != "" && $("#admin_menu_result label[for='" + a + "']").length < 1) {
			var b = $(this).text();
			var k = a + b;
			k = g_func.non_mark_seo(k).replace(/\-/g, "");
			//console.log(a);
			str +=
				"<li data-key='" +
				k +
				"'><label for='" +
				a +
				"'><i class='fa fa-tag'></i> " +
				b.replace(/\:/g, "") +
				"</label></li>";
		}
	});

	//
	if (str != "") {
		$("#admin_menu_result ul").prepend(str);
	}
}

/**
 * Remove a parameter to the URL with JavaScript [duplicate]
 * https://stackoverflow.com/questions/16941104/remove-a-parameter-to-the-url-with-javascript
 */
function removeParam(key, sourceURL) {
	var rtn = sourceURL.split("?")[0],
		param,
		params_arr = [],
		queryString = sourceURL.indexOf("?") !== -1 ? sourceURL.split("?")[1] : "";
	if (queryString !== "") {
		params_arr = queryString.split("&");
		for (var i = params_arr.length - 1; i >= 0; i -= 1) {
			param = params_arr[i].split("=")[0];
			if (param === key) {
				params_arr.splice(i, 1);
			}
		}
		if (params_arr.length) rtn = rtn + "?" + params_arr.join("&");
	}
	return rtn;
}

// thêm tham số in ra 1 số thông tin khi cần debug code
function admin_print_debug_data() {
	var a = removeParam("print_data", window.location.href);
	if (a.split("?").length > 1) {
		a += "&";
	} else {
		a += "?";
	}
	window.location = a + "print_data=1";
	return false;
}
