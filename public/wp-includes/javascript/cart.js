//
jQuery(document).ready(function () {
	// mã giảm giá trong cache nếu có
	cache_coupon_code();

	// thêm iframe để submit form cho tiện
	// let has_quick_cart = false;
	if (product_cart_id != "") {
		product_cart_id *= 1;
		if (!isNaN(product_cart_id) && product_cart_id > 0) {
			// lưu ID sản phẩm này vào bộ nhớ tạm -> quyền ưu tiên thấp hơn cache giỏ hàng chính
			let cart_cache = cart_get_cache_data("cache-cart-ids");
			if (cart_cache === null) {
				cart_cache = [];
			} else {
				cart_cache = cart_cache.split(",");
			}
			// console.log("cart cache:", cart_cache, typeof cart_cache);

			//
			if (
				cart_cache.includes(product_cart_id) == false &&
				cart_cache.includes(product_cart_id + "") == false
			) {
				cart_cache.push(product_cart_id + "");
				// cart_set_cache_data("cache-quickcart-id", product_cart_id);
				cart_set_cache_data("cache-cart-ids", cart_cache.join(","));
			}

			//
			// has_quick_cart = true;
			// change_calculate_cart_value();
			// action_calculate_cart_value();
		}
	}

	// nạp cart qua ajax
	// if (has_quick_cart === false) {
	action_ajax_cart();
	/*
	} else {
		jQuery(".cart-is-product").removeClass("d-none");
		cart_sidebar_table();
		cart_table_buttons_added();
	}
	*/

	// hiển thị html cho phần đặt cọc nếu có
	if (cart_config.deposits_money != "") {
		jQuery(".cart-group-deposit-money").removeClass("d-none");
		jQuery(".cart-sub-regular_price").removeClass("bold");
	}

	//
	_global_js_eb.add_primari_iframe();
	_global_js_eb.wgr_nonce("frm_actions_cart");
	cart_customer_cache_data();
});
