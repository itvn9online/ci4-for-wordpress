// trả về dữ liệu trong cache
function cart_get_cache_data(key) {
	return g_func.getc(key);
}

// lưu dữ liệu vào cache
function cart_set_cache_data(key, c, t) {
	// mặc định là lưu 1 tháng
	if (typeof t != "number") {
		t = 24 * 3600 * 30;
	}
	return g_func.setc(key, c, t);
}

//
function action_calculate_cart_value() {
	let item_total = 0,
		price_sub_total = 0,
		price_total = 0;
	$(".change-cart-quantity").each(function () {
		let this_total = 0;
		let jd = $(this).data("id") || "";
		// console.log("jd", jd);
		let price = $(this).data("price") || "";
		// console.log("price", price);
		let quan = $(this).val() || "";
		// console.log("quan", quan);

		//
		if (jd != "" && price != "" && quan != "") {
			// jd *= 1;
			price *= 1;
			quan *= 1;

			//
			if (!isNaN(jd * 1) && !isNaN(price) && !isNaN(quan)) {
				this_total = price * quan;
				// console.log("this total", this_total);

				//
				price_sub_total += this_total;
				item_total += quan;

				//
				$('.change-product-quantity[data-id="' + jd + '"]').html(quan);
			}
		}

		//
		$('.change-cart-regular_price[data-id="' + jd + '"]').html(
			g_func.money_format(this_total)
		);
	});

	// tổng phụ
	$(".cart-subtotal-regular_price").html(g_func.money_format(price_sub_total));

	// tổng chính -> sau khi tính thuế má, phí vận chuyển, mã giảm giá...
	price_total = price_sub_total;
	if (cart_config.coupon_amount.toString().split("%").length > 1) {
		let coupon_amount = g_func.number_only(cart_config.coupon_amount);
		console.log("coupon amount", coupon_amount);
		if (coupon_amount < 0) {
			coupon_amount = 0 - coupon_amount;
		}

		//
		if (coupon_amount > 0) {
			let discount_amount = (price_total / 100) * coupon_amount;
			console.log("discount amount", discount_amount);
			price_total = price_total - discount_amount;
		}
	} else {
		cart_config.coupon_amount *= 1;
		price_total -= cart_config.coupon_amount;
	}
	$(".cart-total-regular_price").html(
		g_func.money_format(price_total + cart_config.shipping_fee)
	);

	//
	$(".total-cart-quantity").html(item_total);
}

//
function change_calculate_cart_value() {
	$(".change-cart-quantity")
		.off("change")
		.change(function () {
			action_calculate_cart_value();
		});
}

// nạp giỏ hàng theo cache
function action_ajax_cart() {
	let a = cart_get_cache_data("cache-cart-ids");
	if (a === null) {
		a = cart_get_cache_data("cache-quickcart-id");
		if (a === null) {
			// hiển thị thông báo giỏ hàng trống
			$(".cart-is-empty").show();
			return false;
		}
	}

	//
	jQuery.ajax({
		type: "POST",
		url: "actions/ajax_cart",
		dataType: "json",
		//crossDomain: true,
		data: {
			ids: a,
		},
		timeout: 33 * 1000,
		error: function (jqXHR, textStatus, errorThrown) {
			jQueryAjaxError(jqXHR, textStatus, errorThrown, new Error().stack);
		},
		success: function (data) {
			// console.log(data);

			//
			if (typeof data.error != "undefined") {
				console.log("%c " + data.error, "color: red");
			} else if (typeof data.table != "undefined") {
				// gọi đến hàm trước khi nạp xong giỏ hàng (nếu có)
				if (typeof action_before_ajax_cart == "function") {
					action_before_ajax_cart(data);
				}

				//
				$("#append_ajax_cart").html(data.table);
				change_calculate_cart_value();
				action_calculate_cart_value();

				// hiển thị nội dung giỏ hàng
				$(".cart-is-product").removeClass("d-none");

				// định dạng chiều cao cho khung ảnh
				_global_js_eb.auto_margin();

				// hiển thị ảnh đại diện
				$(".cart-image .each-to-bgimg")
					.each(function () {
						let a = $(this).data("img") || "";
						if (a != "") {
							jQuery(this).css({
								"background-image": "url('" + a + "')",
							});
						}
					})
					.removeClass("each-to-bgimg");

				// định dạng tiền tệ
				_global_js_eb.ebe_currency_format();

				// gọi đến hàm sau khi nạp xong giỏ hàng (nếu có)
				if (typeof action_after_ajax_cart == "function") {
					action_after_ajax_cart(data);
				}
			}
		},
	});
}

// chuyển sang nội dung của tab cart
function proceed_to_cart() {
	$(".checkout-content").hide();
	$(".cart-content").fadeIn();
	window.scroll(0, $(".row-cart").offset().top - 90);
	return false;
}

// chuyển sang nội dung của tab nhập coupon
function proceed_to_coupon() {
	proceed_to_cart();
	$("#coupon_custom_code").focus();
	return false;
}

// chuyển sang nội dung của tab checkout
function proceed_to_checkout() {
	$(".cart-content").hide();
	$(".checkout-content").fadeIn();
	return false;
}

// hiển thị mã giảm giá nếu có
function show_coupon_code() {
	if (cart_config.coupon_code != "") {
		$(".cart-discount-code").html(cart_config.coupon_code);
		// tính theo % -> hiển thị %
		if (cart_config.coupon_amount.toString().split("%").length > 1) {
			$(".cart-discount-value")
				.html(cart_config.coupon_amount)
				.removeClass("ebe-currency");
		} else {
			// hiển thị số tiền
			$(".cart-discount-value")
				.html(g_func.money_format(cart_config.coupon_amount))
				.addClass("ebe-currency");
		}
		$(".cart-sidebar-coupon").show();
	} else {
		$(".cart-sidebar-coupon").hide();
	}
}

// xóa mã giảm giá
function remove_coupon_code() {
	if (confirm("You want remove this coupon code?") !== true) {
		return false;
	}

	//
	localStorage.removeItem("cache-coupon-code");

	//
	return set_coupon_code(0, "");
}

// thiết lập lại giá trị cho phần coupon
function set_coupon_code(val, code) {
	// nếu tính theo % thì không cần xử lý gì cả
	// giảm theo số nguyên thì mới gán số nguyên
	if (val.toString().split("%").length < 2) {
		val *= 1;
		// mã giảm giá luôn là 1 số dương
		if (val < 0) {
			val = 0 - val;
		}
	}

	// gán lại giá trị cho phần coupon
	cart_config.coupon_code = code;
	$("#coupon_code").val(code);
	cart_config.coupon_amount = val;
	// tính toán lại giỏ hàng
	action_calculate_cart_value();
	// hiển thị phần coupon
	show_coupon_code();
	//
	return true;
}

// thêm mã giảm giá
function add_coupon_code(val, code) {
	if (typeof code == "undefined" || code == "") {
		code = $.trim($("#coupon_custom_code").val() || "");
	}
	if (code != "" && typeof val != "undefined") {
		// lưu coupon này vào cache
		cart_set_cache_data("cache-coupon-code", code + ";" + val);

		//
		return set_coupon_code(val, code);
	}
	return false;
}

//
function cache_coupon_code() {
	let a = cart_get_cache_data("cache-coupon-code");
	// console.log("cache-coupon-code", a);

	//
	if (a !== null) {
		// console.log("cache-coupon-code", a);
		a = a.split(";");
		if (a.length === 2 && $.trim(a[0]) != "" && $.trim(a[1]) != "") {
			return set_coupon_code(a[1], a[0]);
		}
	}

	//
	show_coupon_code();
}

// xóa sản phẩm khỏi cache giỏ hàng
function remove_from_cart(jd) {
	if (confirm("Confirm remove this product from your cart!") === false) {
		return false;
	}

	//
	if (typeof jd == "undefined" || jd == "") {
		return false;
	}

	//
	jd *= 1;
	if (isNaN(jd) || jd < 1) {
		return false;
	}

	// xóa khỏi giỏ hàng chính
	remove_from_cache_cart(jd, "cache-cart-ids");
	// xóa khỏi giỏ hàng phụ
	remove_from_cache_cart(jd, "cache-quickcart-id");

	// xong thì nạp lại trang
	window.location = window.location.href;

	//
	return true;
}

function remove_from_cache_cart(jd, key) {
	let a = cart_get_cache_data(key);
	if (a !== null) {
		a = a.split(",");
		// console.log(a);

		//
		let arr = [];
		for (let i = 0; i < a.length; i++) {
			a[i] = $.trim(a[i]);
			if (a[i] != "") {
				a[i] *= 1;
				if (!isNaN(a[i]) && a[i] != jd) {
					arr.push(a[i]);
				}
			}
		}
		// console.log(arr);

		// nếu còn dữ liệu thì lưu giỏ hàng mới
		if (arr.length > 0) {
			cart_set_cache_data(key, arr.join(","));
		} else {
			// không có thì xóa sạch
			localStorage.removeItem(key);
		}
	}
}

// tự động nhập liệu thông tin khách hàng trong cache
function cart_customer_cache_data() {
	$(
		[
			".cart-is-product .checkout-form input[type='text']",
			".cart-is-product .checkout-form input[type='email']",
			".cart-is-product .checkout-form input[type='tel']",
			".cart-is-product .checkout-form input[type='number']",
		].join(",")
	).each(function () {
		// tạo id nếu chưa có
		let a = $(this).attr("id") || "";
		if (a == "") {
			let b = $(this).attr("name") || "";
			if (b != "") {
				b = b.replace(/\[|\]/gi, "_");
				$(this)
					.attr({
						id: b,
					})
					.addClass("customer-cache-data");
			}
		}
	});

	// lấy dữ liệu người dùng từ cache
	let c = get_customer_cache_data();
	for (let x in c) {
		// gán vào form
		$("#" + x).val(c[x]);
	}

	// mỗi lần người dùng thay đổi input trong form thì lưu thông tin này lại để sau nhập cho khách
	$(".customer-cache-data").change(function () {
		// lấy id của input
		let a = $(this).attr("id") || "";
		if (a != "") {
			// lấy dữ liệu trong cache
			let c = get_customer_cache_data();
			// gán dữ liệu người dùng đã nhập
			c[a] = $(this).val();
			// lưu vào cache
			cart_set_cache_data("customer-cache-data", JSON.stringify(c));
		}
	});
}

// trả về thông tin khách hàng trong cache
function get_customer_cache_data() {
	let c = cart_get_cache_data("customer-cache-data");
	// console.log(c);
	if (c !== null) {
		c = JSON.parse(c);
	} else {
		c = {};
	}
	// console.log(c);
	return c;
}

//
jQuery(document).ready(function () {
	// hiển thị phí vận chuyển
	// console.log(cart_config.shipping_fee);
	// console.log(cart_config);
	if (cart_config.shipping_fee == "") {
		// quy đổi thành dạng số để giỏ hàng còn cộng tiền
		cart_config.shipping_fee *= 1;
		$(".cart-sidebar-shipping").html(cart_config.calculated_later);
	} else {
		// quy đổi thành dạng số để giỏ hàng còn cộng tiền
		cart_config.shipping_fee *= 1;
		if (cart_config.shipping_fee < 1) {
			$(".cart-sidebar-shipping").html(cart_config.free_shipping);
		} else {
			$(".cart-sidebar-shipping")
				.html(g_func.money_format(cart_config.shipping_fee))
				.addClass("ebe-currency");
		}
	}

	// mã giảm giá trong cache nếu có
	cache_coupon_code();

	// thêm iframe để submit form cho tiện
	let has_quick_cart = false;
	if (product_cart_id != "") {
		product_cart_id *= 1;
		if (!isNaN(product_cart_id) && product_cart_id > 0) {
			// lưu ID sản phẩm này vào bộ nhớ tạm -> quyền ưu tiên thấp hơn cache giỏ hàng chính
			cart_set_cache_data("cache-quickcart-id", product_cart_id);

			//
			has_quick_cart = true;
			change_calculate_cart_value();
			action_calculate_cart_value();
		}
	}

	// nạp cart qua ajax
	if (has_quick_cart === false) {
		action_ajax_cart();
	} else {
		$(".cart-is-product").removeClass("d-none");
	}

	//
	_global_js_eb.add_primari_iframe();
	_global_js_eb.wgr_nonce("frm_actions_cart");
	cart_customer_cache_data();
});
