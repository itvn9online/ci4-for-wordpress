$(document).ready(function () {
	// post
	$(".global-details-title").attr({
		"insert-before": 1,
	});

	// chỉnh controller trước khi tạo link
	$('.eb-sub-menu[data-type="nav_menu"]').attr({
		"data-control": "menus",
	});
	$('.eb-sub-menu[data-type="html_menu"]').attr({
		"data-control": "htmlmenus",
	});

	//
	$(
		".eb-blog li , .eb-blog div.col, .thread-list li, .eb-sub-menu, .global-details-title, .custom-bootstrap-post_type"
	).each(function () {
		var jd = $(this).attr("data-id") || "";
		//console.log(jd);
		var type = $(this).attr("data-type") || "";
		var controller = $(this).attr("data-control") || "";
		//console.log(type);
		var insert_before = $(this).attr("insert-before") || "";
		//console.log(insert_before);

		//
		if (jd != "" && type != "") {
			// tìm controller theo post type
			if (controller == "") {
				// mặc định
				controller = "posts";
				var arr_type = {
					blog: "blogs",
					ads: "adss",
					page: "pages",
				};
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
			var url = "admin/" + controller + "/add?id=" + jd;
			if (type != "") {
				url += "&post_type=" + type;
			}
			//console.log(url);
			url =
				'<a href="' +
				url +
				'" target="_blank" rel="nofollow" class="click-goto-edit"><span><i class="fa fa-edit"></i></span></a>';

			if (insert_before != "") {
				$(this).before(url);
			} else {
				$(this).prepend(url);
			}
		}
	});

	// term
	$(
		".global-taxonomy-title, .eb-widget-title, .eb-widget-hide-title, .custom-bootstrap-taxonomy"
	).each(function () {
		var jd = $(this).attr("data-id") || "";
		//console.log(jd);
		var type = $(this).attr("data-type") || "";
		//console.log(type);
		var controller = $(this).attr("data-control") || "";

		//
		if (jd != "" && type != "") {
			// tìm controller theo taxonomy
			if (controller == "") {
				// mặc định
				controller = "terms";
				var arr_type = {
					post_options: "postoptions",
					tags: "tags",
					blogs: "blogcategory",
					blog_tags: "blogtags",
					ads_options: "adsoptions",
				};
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
			var url = "admin/" + controller + "/add?id=" + jd;
			if (type != "") {
				url += "&taxonomy=" + type;
			}
			//console.log(url);

			$(this).prepend(
				'<a href="' +
					url +
					'" target="_blank" rel="nofollow" class="click-goto-edit"><span><i class="fa fa-edit"></i></span></a>'
			);
		}
	});

	$(".web-logo").before(
		'<a href="admin/configs?support_tab=data_logo" target="_blank" rel="nofollow" class="click-goto-edit"><span><i class="fa fa-edit"></i></span></a>'
	);
});
