function after_render_order_list() {
	jQuery(".post_excerpt-to-products")
		.each(function () {
			let a = jQuery.trim(jQuery(this).html());
			// console.log(a);
			// return false;
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
					jQuery(this).html(str.join("<br />"));
				} catch (e) {
					WGR_show_try_catch_err(e);
				}
			}
		})
		.show();

	//
	jQuery(".orders-open-popup").click(function () {
		return order_details_set_iframe(
			jQuery(this).attr("data-id") || "",
			jQuery(this).attr("href")
		);
	});
}

//
WGR_vuejs(
	"#app",
	{
		controller_slug: json_params.controller_slug,
		post_type: json_params.post_type,
		post_status: json_params.post_status,
		for_action: json_params.for_action,
		PostType_DELETED: json_params.PostType_DELETED,
		PostType_arrStatus: PostType_arrStatus,
		data: json_data,
		calc_total_order: function (
			order_money,
			order_discount,
			shipping_fee,
			order_bonus
		) {
			order_money *= 1;
			order_discount *= 1;
			shipping_fee *= 1;
			order_bonus *= 1;

			//
			return order_money - order_discount + shipping_fee - order_bonus;
		},
	},
	function () {
		done_build_order_list();
		after_render_order_list();
	}
);
