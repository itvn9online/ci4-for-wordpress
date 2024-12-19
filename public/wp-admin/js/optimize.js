//
(function () {
	var current_page =
		jQuery(".public-part-page span.current").data("page") || "";
	if (current_page != "") {
		current_page *= 1;

		//
		console.log("current page:", current_page);
		current_page++;
		if (current_page > totalPage) {
			return false;
		}
		setTimeout(() => {
			window.location =
				web_link + "sadmin/uploads/optimize?page_num=" + current_page;
		}, 5000);
	}
})();
