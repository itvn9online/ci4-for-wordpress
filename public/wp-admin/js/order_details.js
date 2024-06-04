if (top != self) {
	$("body").addClass("hide-for-popup_order");
	top.show_order_details_iframe();
	$(".show-if-order-popup").show();

	//
	$("a").each(function () {
		let a = $(this).attr("target") || "";
		if (a == "") {
			$(this).attr({
				target: "_top",
			});
		}
	});
}
