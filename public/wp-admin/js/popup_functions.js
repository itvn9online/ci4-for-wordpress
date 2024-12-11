var current_open_popup_id = 0,
	current_url = window.location.href,
	order_details_height_iframe = false;

function action_popup_details_iframe() {
	$("body").addClass("hide-for-popup_order");

	//
	top.loaded_popup_details_iframe();

	//
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

function loaded_popup_details_iframe() {
	$("#order_details_iframe").addClass("actived");
}

function show_popup_details_iframe() {
	if (top != self) {
		action_popup_details_iframe();
	}
}

function after_hide_if_esc() {
	// console.log(Math.random());
	window.history.pushState("", document.title, current_url);
}

function order_details_set_iframe(a, uri) {
	// console.log(Math.random(), a);
	// return false;
	if (a != "") {
		a *= 1;

		//
		if (!isNaN(a) && a > 0) {
			if (current_open_popup_id === a) {
				if ($("body").hasClass("no-scroll")) {
					return false;
				}
			}
			current_open_popup_id = a;

			//
			$("body").addClass("no-scroll");

			//
			$("#order_details_iframe")
				.show()
				.css({
					height: $(window).height() + "px",
				})
				.removeClass("actived");

			//
			let url = web_link + uri + a;

			//
			window.open(url, "order-details-iframe");

			//
			window.history.pushState("", document.title, url);

			// thay đổi chiều cao cho popup
			if (order_details_height_iframe === false) {
				order_details_height_iframe = true;

				$(window).resize(function () {
					$("#order_details_iframe").css({
						height: $(window).height() + "px",
					});
				});
			}

			//
			return false;
		}
	}

	//
	return true;
}
