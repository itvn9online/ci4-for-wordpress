function show_edit_btn() {
	// thêm css tạo hình
	/*
	(function (b, e, v, t, s) {
		t = b.createElement(e);
		t.rel = "stylesheet";
		t.type = "text/css";
		t.media = "all";
		t.href = v;
		s = b.getElementsByTagName(e)[0];
		s.parentNode.insertBefore(t, s);
	})(document, "link", "wp-admin/css/show-debug-bar.css");
	*/

	// post
	jQuery(".global-details-title").attr({
		"insert-before": 1,
	});

	// chỉnh controller trước khi tạo link
	jQuery('.eb-sub-menu[data-type="nav_menu"]').attr({
		"data-control": "menus",
	});
	jQuery('.eb-sub-menu[data-type="html_menu"]').attr({
		"data-control": "htmlmenus",
	});

	//
	var preview_url = "&preview_url=" + encodeURIComponent(window.location.href);
	var offset_prev_top = 0;

	//
	jQuery(
		".eb-blog li, .eb-blog div.col, .products-list li, .eb-sub-menu, .global-details-title, .custom-bootstrap-post_type"
	).each(function () {
		var jd = jQuery(this).data("id") || "";
		var type = jQuery(this).data("type") || "";
		var controller = jQuery(this).data("control") || "";
		var insert_before = jQuery(this).attr("insert-before") || "";
		// console.log(insert_before);
		// console.log(jd, type);

		//
		if (jd != "" && type != "") {
			// tìm controller theo post type
			if (controller == "") {
				// mặc định
				controller = "posts";
				var arr_type = arr_post_controller;
				// các post type mặc định
				if (typeof arr_type[type] != "undefined") {
					controller = arr_type[type];
				}
				// custom post type
				else if (
					typeof arr_edit_custom_type != "undefined" &&
					typeof arr_edit_custom_type[type] != "undefined"
				) {
					controller = arr_edit_custom_type[type];
				}
			}
			var url = "sadmin/" + controller + "/add?id=" + jd;
			if (type != "") {
				url += "&post_type=" + type;
			}
			//console.log(url);

			//
			var offset_top = jQuery(this).offset().top || 0;
			if (offset_top < 1) {
				offset_top = offset_prev_top;
			} else {
				offset_prev_top = offset_top;
			}

			//
			url =
				'<a href="' +
				url +
				"&preview_offset_top=" +
				offset_top +
				preview_url +
				'" target="_blank" rel="nofollow" class="click-goto-edit goto-post-edit goto-' +
				type +
				'-edit"><span><i class="fa fa-edit"></i></span></a>';

			if (insert_before != "") {
				jQuery(this).before(url);
			} else {
				jQuery(this).prepend(url);
			}
			jQuery(this).addClass("actived-goto-edit");
		}
	});

	// term
	jQuery(
		".global-taxonomy-title, .eb-widget-title, .eb-widget-hide-title, .custom-bootstrap-taxonomy"
	).each(function () {
		var jd = jQuery(this).data("id") || "";
		var type = jQuery(this).data("type") || "";
		var controller = jQuery(this).data("control") || "";
		// console.log(jd, type);

		//
		if (jd != "" && type != "") {
			// tìm controller theo taxonomy
			if (controller == "") {
				// mặc định
				controller = "terms";
				var arr_type = arr_taxonomy_controller;
				// các post type mặc định
				if (typeof arr_type[type] != "undefined") {
					controller = arr_type[type];
				}
				// custom post type
				else if (
					typeof arr_edit_custom_taxonomy != "undefined" &&
					typeof arr_edit_custom_taxonomy[type] != "undefined"
				) {
					controller = arr_edit_custom_taxonomy[type];
				}
			}
			var url = "sadmin/" + controller + "/add?id=" + jd;
			if (type != "") {
				url += "&taxonomy=" + type;
			}
			//console.log(url);

			//
			var offset_top = jQuery(this).offset().top || 0;
			if (offset_top < 1) {
				offset_top = offset_prev_top;
			} else {
				offset_prev_top = offset_top;
			}

			//
			jQuery(this).prepend(
				'<a href="' +
					url +
					"&preview_offset_top=" +
					offset_top +
					preview_url +
					'" target="_blank" rel="nofollow" class="click-goto-edit goto-taxonomy-edit goto-' +
					type +
					'-edit"><span><i class="fa fa-edit"></i></span></a>'
			);
		}
	});

	//
	jQuery(".web-logo").before(
		'<a href="sadmin/configs?support_tab=data_logo" target="_blank" rel="nofollow" class="click-goto-edit goto-option-edit"><span><i class="fa fa-edit"></i></span></a>'
	);

	// nếu đang mở trong iframe
	if (top != self) {
		// không cho bấm vào các link
		jQuery("a").click(function () {
			return false;
		});

		// link edit mở trong top
		jQuery(".click-goto-edit")
			.attr({
				target: "_top",
			})
			.off("click")
			.click(function () {
				return true;
			});
	}
}

//
jQuery(document).ready(function () {
	setTimeout(show_edit_btn, 600);

	// hiển thị các link để SEO dễ dàng debug
	(function (e) {
		e = encodeURIComponent(e);
		let arr = [
			"https://pagespeed.web.dev/report?url=" + e,
			"https://validator.schema.org/#url=" + e,
			"https://developers.facebook.com/tools/debug/?q=" + e,
			"https://developers.zalo.me/tools/debug-sharing?q=" + e,
			"https://securityheaders.com/?q=" + e,
		];
		for (let i = 0; i < arr.length; i++) {
			console.log(arr[i]);
		}
	})(window.location.href);
});
