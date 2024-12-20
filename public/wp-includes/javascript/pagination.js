// phân trang sử dụng javascript
function EBE_part_page(Page, TotalPage, strLinkPager, sub_part, add_query) {
	if (TotalPage < 2) {
		return (
			WGR_config.pagination_display_1 +
			' <span data-page="' +
			Page +
			'" class="current">' +
			Page +
			"</span> "
		);
	}
	if (typeof sub_part == "undefined") {
		sub_part = "/page/";
	}
	// console.log(Page, TotalPage);
	// console.log(strLinkPager, sub_part);

	// thêm dấu ? hoặc dấu & cho url
	if (typeof add_query != "undefined" && add_query != "") {
		add_query *= 1;
		if (!isNaN(add_query) && add_query > 0) {
			if (strLinkPager.includes("?") == true) {
				strLinkPager += "&";
			} else {
				strLinkPager += "?";
			}
		}
	} else if (sub_part.includes("=") == true) {
		if (strLinkPager.includes("?") == true) {
			strLinkPager += "&";
		} else {
			strLinkPager += "?";
		}
	}
	strLinkPager += sub_part;
	//console.log(strLinkPager);
	let show_page = 8;
	let chia_doi = Math.ceil(show_page / 2);
	//let str_page = '';
	let current_page =
		' <span data-page="' + Page + '" class="current">' + Page + "</span> ";

	//
	Page *= 1;
	//let prev_1_page = Page - 1;

	// lấy các trang trước
	let prev_page = "",
		first_page = "",
		begin_i = Page - chia_doi;
	//console.log('begin i:', begin_i);
	if (begin_i < 1) {
		begin_i = 1;
	} else if (begin_i > 1) {
		first_page =
			' <a data-page="' +
			(Page - 1) +
			'" href="' +
			strLinkPager +
			(Page - 1) +
			'" rel="nofollow">&lt;&lt;</a> ';
		first_page +=
			' <a data-page="1" rel="nofollow" href="' + strLinkPager + '1">1</a> ';
		if (begin_i > 2) {
			first_page += " ... ";
		}
	}
	for (let i = begin_i; i < Page; i++) {
		prev_page +=
			' <a data-page="' +
			i +
			'" rel="nofollow" href="' +
			strLinkPager +
			i +
			'">' +
			i +
			"</a> ";
		show_page--;
	}
	//console.log('show page:', show_page);

	// -> các trang sau
	let next_page = "",
		last_page = "";
	let end_i = Page + show_page;
	//console.log('end i:', end_i);
	if (end_i > TotalPage) {
		end_i = TotalPage;
	} else if (end_i < TotalPage) {
		if (end_i < TotalPage - 1) {
			last_page += " ... ";
		}
		last_page +=
			' <a data-page="' +
			TotalPage +
			'" rel="nofollow" href="' +
			strLinkPager +
			TotalPage +
			'">' +
			TotalPage +
			'</a> <a data-page="' +
			(Page + 1) +
			'" href="' +
			strLinkPager +
			(Page + 1) +
			'" rel="nofollow">&gt;&gt;</a> ';
	}
	for (let i = Page + 1; i <= end_i; i++) {
		next_page +=
			' <a data-page="' +
			i +
			'" rel="nofollow" href="' +
			strLinkPager +
			i +
			'">' +
			i +
			"</a> ";
	}

	//
	return first_page + prev_page + current_page + next_page + last_page;
}

//
jQuery(".each-to-page-part").each(function () {
	let page = jQuery(this).data("page") || "",
		total = jQuery(this).data("total") || "",
		url = jQuery(this).data("url") || "",
		params = jQuery(this).data("params") || "",
		query = jQuery(this).data("query") || "";

	//
	jQuery(this).before(EBE_part_page(page, total, url, params, query));
});
jQuery(".each-to-page-part").before("<!-- div.each-to-page-part -->").remove();
