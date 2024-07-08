var current_open_order_id = 0;

function show_order_details_iframe() {
	$("#order_details_iframe").css({
		opacity: 1,
	});
}

//
$(document).ready(function () {
	$(".post_excerpt-to-products")
		.each(function () {
			let a = $.trim($(this).html());
			// console.log(a);
			if (1 != "") {
				try {
					a = JSON.parse(a);
					// console.log(a);

					//
					let str = [];
					for (let i = 0; i < a.length; i++) {
						str.push(
							'- <a href="' +
								web_link +
								"?p=" +
								a[i].ID +
								'" target="_blank">' +
								a[i].post_title +
								" (" +
								a[i]._price +
								" x " +
								a[i]._quantity +
								")</a>"
						);
					}
					$(this).html(str.join("<br />"));
				} catch (e) {
					WGR_show_try_catch_err(e);
				}
			}
		})
		.show();

	//
	$(".orders-open-popup").click(function () {
		let a = $(this).attr("data-id") || "";
		// console.log(Math.random(), a);
		if (a != "") {
			a *= 1;

			//
			if (!isNaN(a) && a > 0) {
				if (current_open_order_id === a) {
					if ($("body").hasClass("no-scroll")) {
						return false;
					}
				}
				current_open_order_id = a;

				//
				$("body").addClass("no-scroll");

				//
				$("#order_details_iframe")
					.show()
					.css({
						height: $(window).height() + "px",
						opacity: 0.2,
					});

				//
				let url = web_link + "sadmin/orders/add?id=" + a;

				//
				window.open(url, "order_details_iframe");

				//
				window.history.pushState("", document.title, url);

				//
				return false;
			}
		}

		//
		return true;
	});
});
