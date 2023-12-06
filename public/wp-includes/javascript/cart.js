function action_calculate_cart_value() {
	let sub_total = 0;
	let sub_price_total = 0;
	$(".change-cart-quantity").each(function () {
		let this_total = 0;
		let jd = $(this).data("id") || "";
		console.log("jd", jd);
		let price = $(this).data("price") || "";
		console.log("price", price);
		let quan = $(this).val() || "";
		console.log("quan", quan);

		//
		if (jd != "" && price != "" && quan != "") {
			// jd *= 1;
			price *= 1;
			quan *= 1;

			//
			if (!isNaN(jd * 1) && !isNaN(price) && !isNaN(quan)) {
				this_total = price * quan;
				console.log("this_total", this_total);

				//
				sub_price_total += this_total;
				sub_total += quan;
			}
		}

		//
		$('.change-cart-regular_price[data-id="' + jd + '"]').html(this_total);
	});
	$(".total-cart-regular_price").html(sub_price_total);
	$(".total-cart-quantity").html(sub_total);
}

//
function change_calculate_cart_value() {
	$(".change-cart-quantity")
		.off("change")
		.change(function () {
			action_calculate_cart_value();
		});
}

function action_ajax_cart() {
	jQuery.ajax({
		type: "POST",
		url: "actions/ajax_cart",
		dataType: "json",
		//crossDomain: true,
		data: {
			ids: "5462",
		},
		timeout: 33 * 1000,
		error: function (jqXHR, textStatus, errorThrown) {
			jQueryAjaxError(jqXHR, textStatus, errorThrown, new Error().stack);
		},
		success: function (data) {
			console.log(data);

			//
			if (typeof data.error != "undefined") {
			} else if (typeof data.table != "undefined") {
				$("#append_ajax_cart").html(data.table);
				change_calculate_cart_value();
				action_calculate_cart_value();
			}
		},
	});
}

//
jQuery(document).ready(function () {
	// thêm iframe để submit form cho tiện
	let has_quick_cart = false;
	if (product_cart_id != "") {
		product_cart_id *= 1;
		if (!isNaN(product_cart_id) && product_cart_id > 0) {
			has_quick_cart = true;
			change_calculate_cart_value();
			action_calculate_cart_value();
		}
	}

	// nạp cart qua ajax
	if (has_quick_cart === false) {
		action_ajax_cart();
	}

	//
	_global_js_eb.add_primari_iframe();
	_global_js_eb.wgr_nonce("frm_actions_cart");
});
