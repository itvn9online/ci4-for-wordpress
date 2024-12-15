// không thay đổi giá trị giỏ hàng liên tục
var delay_change_cart_data = null,
	cart_discount_type = "",
	global_item_total = 0;

// trả về dữ liệu trong cache
function cart_get_cache_data(key) {
	return g_func.getc(key);
}

// lưu dữ liệu vào cache
function cart_set_cache_data(key, c, t) {
	// mặc định là lưu 1 tháng
	if (typeof t != "number") {
		t = 86400 * 30;
	}
	console.log("set cache:", key);
	return g_func.setc(key, c, t);
}

// tạo giãn cách khi update thông tin giỏ hàng
function action_calculate_cart_value() {
	// chỉ tính toán giá trị giỏ hàng ở giỏ hàng
	if (typeof cart_config == "undefined") {
		return false;
	}

	//
	jQuery("body").addClass("body-onload");
	clearTimeout(delay_change_cart_data);
	delay_change_cart_data = setTimeout(() => {
		run_calculate_cart_value();
		jQuery("body").removeClass("body-onload");
	}, 500);
}

function run_calculate_cart_value() {
	// console.log("b", Math.random());
	let item_total = 0,
		price_sub_total = 0,
		price_total = 0;
	jQuery(".change-cart-quantity").each(function () {
		let this_total = 0;
		let jd = jQuery(this).data("id") || "";
		// console.log("jd", jd);
		let price = jQuery(this).data("price") || "";
		// console.log("price", price);
		let quan = jQuery(this).val() || "";
		// console.log("quan", quan);

		//
		if (quan == "") {
			quan = 1;
			jQuery(this).val(quan);
		} else {
			quan *= 1;
			if (isNaN(quan)) {
				quan = 1;
				jQuery(this).val(quan);
			}
		}

		//
		if (jd != "" && price != "") {
			// jd *= 1;
			price *= 1;

			//
			if (!isNaN(jd * 1) && !isNaN(price)) {
				this_total = price * quan;
				// console.log("this total", this_total);

				//
				price_sub_total += this_total;
				item_total += quan;

				//
				jQuery('.change-product-quantity[data-id="' + jd + '"]').html(quan);
			}
		}

		//
		jQuery('.change-cart-regular_price[data-id="' + jd + '"]').html(
			g_func.money_format(this_total)
		);
	});
	global_item_total = item_total;

	// tổng phụ
	jQuery(".cart-subtotal-regular_price").html(
		g_func.money_format(price_sub_total)
	);

	// tổng chính -> sau khi tính thuế má, phí vận chuyển, mã giảm giá...
	price_total = price_sub_total;
	let couponAmount = 0;
	// tính theo % giỏ hàng
	if (cart_config.coupon_amount.toString().includes("%") == true) {
		// couponAmount = g_func.number_only(cart_config.coupon_amount);
		couponAmount =
			jQuery.trim(cart_config.coupon_amount.toString().split("%")[0]) * 1;
		if (couponAmount < 0) {
			couponAmount = 0 - couponAmount;
		}
		console.log("coupon amount", couponAmount);

		//
		if (couponAmount > 0) {
			let discount_amount = (price_total / 100) * couponAmount;
			discount_amount = discount_amount.toFixed(2) * 1;
			console.log("discount amount", discount_amount);
			price_total = price_total - discount_amount;
		}
	} else {
		couponAmount = cart_config.coupon_amount * 1;

		// tính theo số lượng sản phẩm trong giỏ hàng
		if (item_total > 1 && cart_discount_type == "fixed_product") {
			// console.log(cart_discount_type, item_total);
			let fixed_product = couponAmount * item_total;
			// hiển thị số tiền giảm giá
			jQuery(".cart-discount-value")
				.html(g_func.money_format(fixed_product))
				.addClass("ebe-currency");

			//
			price_total -= fixed_product;
		} else {
			// tính theo tổng tiền của giỏ hàng
			price_total -= couponAmount;
		}
	}

	// hiển thị phí vận chuyển
	// console.log(cart_config.shippings_fee, cart_config);
	let shippingFee = 0;
	if (cart_config.shippings_fee == "") {
		jQuery(".cart-sidebar-shipping").html(cart_config.calculated_later);
	} else {
		// quy đổi thành dạng số để giỏ hàng còn cộng tiền
		shippingFee = cart_config.shippings_fee.toString();
		// nếu có tham số [qty]
		if (shippingFee.includes("[qty]")) {
			// phí vận chuyển sẽ tính theo từng đầu sản phẩm
			shippingFee = jQuery.trim(shippingFee.split("*")[0]) * item_total;
		} else if (shippingFee.includes("%")) {
			shippingFee = jQuery.trim(shippingFee.split("%")[0]) * 1;
			shippingFee = (price_total / 100) * shippingFee;
			shippingFee = shippingFee.toFixed(2) * 1;
		} else {
			// mặc định sẽ tính cho toàn bộ giỏ hàng
			shippingFee = shippingFee * 1;
		}
		// nếu lỗi quy đổi
		if (isNaN(shippingFee)) {
			// cho về dạng tính toán sau
			jQuery(".cart-sidebar-shipping").html(cart_config.calculated_later);
		} else {
			// ít quá thì free-ship
			if (shippingFee < 0.1) {
				jQuery(".cart-sidebar-shipping").html(cart_config.free_shipping);
			} else {
				// nhiều thì hiển thị ra
				jQuery(".cart-sidebar-shipping")
					.html(g_func.money_format(shippingFee))
					.addClass("ebe-currency");
			}
		}
	}
	// console.log(shippingFee);

	//
	cart_total_regular_price(price_total + shippingFee);

	//
	jQuery(".total-cart-quantity").html(item_total);
}

function cart_total_regular_price(a) {
	jQuery(".cart-total-regular_price").html(g_func.money_format(a));

	//
	if (cart_config.deposits_money != "") {
		let b = 0;
		if (cart_config.deposits_money.includes("%") !== false) {
			b = g_func.number_only(cart_config.deposits_money);
			b = (a / 100) * b;
			// làm tròn phần thập phân
			// b = b.toFixed(2);
			// làm tròn phía sàn
			b = Math.round(b);
			jQuery(".cart-total-deposit-money").addClass("ebe-currency");
		} else {
			b = cart_config.deposits_money * 1;
		}
		// ->số tiền đặt cọc trước
		jQuery(".cart-total-deposit-money").html(b);
		// số tiền còn lại
		jQuery(".cart-total-deposit_balance").html(a - b);
	}
}

//
function change_calculate_cart_value() {
	jQuery(".change-cart-quantity")
		.off("change")
		.change(function () {
			action_calculate_cart_value();
		});
}

// nạp giỏ hàng theo cache
function action_ajax_cart(order_received) {
	let a = cart_get_cache_data("cache-cart-ids");
	if (a === null) {
		a = cart_get_cache_data("cache-quickcart-id");
		if (a === null) {
			// hiển thị thông báo giỏ hàng trống
			jQuery(".cart-is-empty").show();
			return false;
		}
	}
	// console.log(a);

	//
	if (typeof order_received == "undefined" || order_received !== true) {
		order_received = 0;
	} else {
		order_received = 1;
	}

	//
	jQuery.ajax({
		type: "POST",
		url: "actions/ajax_cart",
		dataType: "json",
		//crossDomain: true,
		data: {
			ids: a,
			product_cart_id:
				typeof product_cart_id == "undefined" ? "0" : product_cart_id,
			shop_cart_id: typeof shop_cart_id == "undefined" ? "0" : shop_cart_id,
			order_received: order_received,
		},
		timeout: 33 * 1000,
		error: function (jqXHR, textStatus, errorThrown) {
			jQueryAjaxError(jqXHR, textStatus, errorThrown, new Error().stack);
		},
		success: function (data) {
			// console.log(data);

			//
			if (typeof data.error != "undefined") {
				console.log("%c" + data.error, "color: red");
			} else if (typeof data.table != "undefined") {
				// gọi đến hàm trước khi nạp xong giỏ hàng (nếu có)
				console.log("action_before_ajax_cart");
				if (typeof action_before_ajax_cart == "function") {
					action_before_ajax_cart(data);
				}

				//
				jQuery("#append_ajax_cart").html(data.table);
				cart_sidebar_table();
				change_calculate_cart_value();
				action_calculate_cart_value();

				// hiển thị nội dung giỏ hàng
				jQuery(".cart-is-product").removeClass("d-none");

				// định dạng chiều cao cho khung ảnh
				_global_js_eb.auto_margin();

				// hiển thị ảnh đại diện
				jQuery(".cart-image .each-to-bgimg")
					.each(function () {
						let a = jQuery(this).data("img") || "";
						if (a != "") {
							jQuery(this).css({
								"background-image": "url('" + a + "')",
							});
						}
					})
					.removeClass("each-to-bgimg");

				// định dạng tiền tệ
				_global_js_eb.ebe_currency_format();

				//
				cart_table_buttons_added();

				// gọi đến hàm sau khi nạp xong giỏ hàng (nếu có)
				console.log("action_after_ajax_cart");
				if (typeof action_after_ajax_cart == "function") {
					action_after_ajax_cart(data);
				}
			}
		},
	});

	//
	return true;
}

// chuyển sang nội dung của tab cart
function proceed_to_cart() {
	jQuery(".checkout-content").hide();
	jQuery(".cart-content").fadeIn();
	window.scroll(0, jQuery(".row-cart").offset().top - 90);
	return false;
}

// chuyển sang nội dung của tab nhập coupon
function proceed_to_coupon() {
	proceed_to_cart();
	jQuery("#coupon_custom_code").focus();
	return false;
}

// chuyển sang nội dung của tab checkout
function proceed_to_checkout() {
	// nếu có function này -> nó sẽ viết ở trong file cart_functions.js của theme
	if (typeof before_proceed_to_checkout == "function") {
		// kiểm tra nó nếu khác true thì trả về lỗi luôn
		if (before_proceed_to_checkout() !== true) {
			return false;
		}
	}

	// nếu số lượng sp trong giỏ hàng là 0 -> bỏ luôn
	if (global_item_total < 1) {
		WGR_html_alert("Please select the product you want to buy", "warning");
		return false;
	}

	//
	jQuery(".cart-content").hide();
	jQuery(".checkout-content").fadeIn();

	//
	return false;
}

//
function action_submit_cart() {
	// nếu có function này -> nó sẽ viết ở trong file cart_functions.js của theme
	if (typeof action_before_submit_cart == "function") {
		// gọi tới function con để kiểm tra thay vì dùng function cha
		return action_before_submit_cart();
	}
	return true;
}

// hiển thị mã giảm giá nếu có
function show_coupon_code() {
	if (cart_config.coupon_code != "") {
		// console.log(cart_discount_type, cart_config.coupon_code);
		jQuery(".cart-discount-code").html(cart_config.coupon_code);
		// tính theo % -> hiển thị % giảm giá
		if (cart_config.coupon_amount.toString().includes("%") == true) {
			jQuery(".cart-discount-value")
				.html(cart_config.coupon_amount)
				.removeClass("ebe-currency");
		} else {
			// hiển thị số tiền giảm giá
			jQuery(".cart-discount-value")
				.html(g_func.money_format(cart_config.coupon_amount))
				.addClass("ebe-currency");
		}
		jQuery(".cart-sidebar-coupon").show();
	} else {
		jQuery(".cart-sidebar-coupon").hide();
	}
}

// xóa mã giảm giá
function remove_coupon_code() {
	if (confirm("You want remove this Coupon code?") !== true) {
		return false;
	}

	//
	localStorage.removeItem("cache-coupon-code");

	//
	return set_coupon_code(0, "");
}

// thiết lập lại giá trị cho phần coupon
function set_coupon_code(val, code, discount_type) {
	// nếu tính theo % thì không cần xử lý gì cả
	// giảm theo số nguyên thì mới gán số nguyên
	if (val.toString().split("%").length < 2) {
		val *= 1;
		// mã giảm giá luôn là 1 số dương
		if (val < 0) {
			val = 0 - val;
		}
	}

	//
	if (typeof discount_type == "undefined") {
		discount_type = "";
	}
	cart_discount_type = discount_type;

	// gán lại giá trị cho phần coupon
	cart_config.coupon_code = code;
	jQuery("#coupon_code").val(code);
	cart_config.coupon_amount = val;
	// tính toán lại giỏ hàng
	action_calculate_cart_value();
	// hiển thị phần coupon
	show_coupon_code();
	//
	return true;
}

// thêm mã giảm giá
function add_coupon_code(val, code, discount_type) {
	if (typeof code == "undefined" || code == "") {
		code = jQuery.trim(jQuery("#coupon_custom_code").val() || "");
	}

	//
	if (typeof discount_type == "undefined") {
		discount_type = "";
	}

	//
	if (code != "" && typeof val != "undefined") {
		// lưu coupon này vào cache
		cart_set_cache_data(
			"cache-coupon-code",
			code + ";" + val + ";" + discount_type
		);

		//
		return set_coupon_code(val, code, discount_type);
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
		if (a.length === 3 && jQuery.trim(a[0]) != "" && jQuery.trim(a[1]) != "") {
			let discount_type = a[2];
			return set_coupon_code(a[1], a[0], discount_type);
		}
	}

	//
	show_coupon_code();
}

// xóa sản phẩm khỏi cache giỏ hàng
function remove_from_cart(jd, reload_now) {
	// mặc định sẽ nạp lại trang
	if (typeof reload_now == "undefined") {
		reload_now = true;
	}
	// xác nhận xóa sản phẩm khỏi giỏ hàng
	if (
		reload_now == true &&
		confirm("Confirm remove this product from your cart!") === false
	) {
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
	if (reload_now == true) {
		// window.location.reload();
		window.location = window.location.href.split("?id=")[0].split("&id=")[0];
	}

	//
	return false;
}

function remove_from_cache_cart(jd, key) {
	let a = cart_get_cache_data(key);
	if (a !== null) {
		a = a.split(",");
		// console.log(a);

		//
		let arr = [];
		for (let i = 0; i < a.length; i++) {
			a[i] = jQuery.trim(a[i]);
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
	jQuery(
		[
			".cart-is-product .checkout-form input[type='text']",
			".cart-is-product .checkout-form input[type='email']",
			".cart-is-product .checkout-form input[type='tel']",
			".cart-is-product .checkout-form input[type='number']",
			".cart-is-product .checkout-form select",
		].join(",")
	).each(function () {
		// tạo id nếu chưa có
		let a = jQuery(this).attr("id") || "";
		let b = jQuery(this).attr("name") || "";
		if (b != "") {
			if (a == "") {
				b = b.replace(/\[|\]/gi, "_");
				jQuery(this).attr({
					id: b,
				});
			}
			jQuery(this).addClass("customer-cache-data");
		}
	});

	// lấy dữ liệu người dùng từ cache
	let c = get_customer_cache_data();
	for (let x in c) {
		// gán vào form
		jQuery("#" + x).val(c[x]);
	}

	// mỗi lần người dùng thay đổi input trong form thì lưu thông tin này lại để sau nhập cho khách
	jQuery(".customer-cache-data").change(function () {
		// lấy id của input
		let a = jQuery(this).attr("id") || "";
		if (a != "") {
			// lấy dữ liệu trong cache
			let c = get_customer_cache_data();
			// gán dữ liệu người dùng đã nhập
			c[a] = jQuery(this).val();
			// console.log(c);
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
		// console.log(c);
	} else {
		c = {};
	}
	// console.log(c);
	return c;
}

// khi click vào nút thêm bớt số lượng sp trong giỏ hàng
function cart_table_buttons_added() {
	jQuery(".cart-table .buttons_added input[type='button']")
		.off("click")
		.click(function () {
			let a = jQuery(this).data("value") || "";
			if (a != "") {
				a *= 1;

				//
				if (!isNaN(a)) {
					let b = jQuery(this).attr("data-id");
					let q = jQuery(
						".cart-table .buttons_added .change-cart-quantity[data-id='" +
							b +
							"']"
					);
					let v = q.val() || "";
					if (v != "") {
						if (!isNaN(v)) {
							v = v * 1 + a;
							// tối thiểu là 0 sp trong giỏ hàng
							if (v < 0) {
								v = 0;
							}
							q.val(v);
							action_calculate_cart_value();
						}
					}
				}
			}
		});
}

// hiển thị danh sách sản phẩm ở phần sidebar
function cart_sidebar_table() {
	let a = jQuery(".cart-hidden-table").html() || "";
	if (a != "") {
		jQuery(".cart-hidden-table").html("");
		jQuery(".cart-sidebar-table").html(a);
	}
}

// khi submit cart thành công thì sẽ thực hiện xóa bỏ session giỏ hàng ở máy trạm
function remove_session_cart(order_received, ids) {
	// nếu có ids truyền vào
	if (typeof ids != "undefined" && ids != "") {
		ids = ids.split(",");
		// chạy vòng lặp xóa sản phẩm cần xóa
		for (let i = 0; i < ids.length; i++) {
			remove_from_cart(ids[i], false);
		}
	} else {
		// xóa khỏi giỏ hàng chính
		localStorage.removeItem("cache-cart-ids");
		// xóa khỏi giỏ hàng phụ
		localStorage.removeItem("cache-quickcart-id");
	}

	// xong thì nạp lại trang
	if (typeof order_received == "undefined" || order_received == "") {
		order_received = window.location.href;
	}
	window.location = order_received;

	//
	return false;
}

function action_continue_shopping() {
	let a = document.referrer;
	if (a == "") {
		a = web_link;
		if (product_cart_id * 1 > 0) {
			a += "?p=" + product_cart_id;
		}
	}
	window.location = a;
	return true;
}
