/**
 * file chứa các functon không dùng ngay, thường là các function chỉ dùng sau khi người dùng có thao tác bấm, ví dụ submit
 * chuyển file này xuống dưới footer và để defer
 */

/**
 * bấm chuột vào 1 input thì thực hiện copy text trong đó luôn
 */
function click2Copy(element, textShow) {
	element.focus();
	element.select();
	document.execCommand("copy");

	if (typeof textShow != "undefined" && textShow === true) {
		try {
			textShow = element.value;
			textShow = " " + $.trim(textShow);
		} catch (e) {
			textShow = "";
		}
	} else {
		textShow = "";
	}
	WGR_html_alert("Copied" + textShow);
}

/**
 * reload lại trang sau khi submit xong
 */
function done_action_submit(go_to, token, ck_key) {
	if (typeof token != "undefined" && token.length > 99) {
		localStorage.setItem(ck_key, token);
	}

	//
	if (typeof go_to != "undefined" && go_to != "") {
		window.location = go_to;
	} else {
		window.location = window.location.href;
	}
}

// chức năng đồng bộ dữ liệu liên quan đến post, term
function sync_ajax_post_term() {
	let last_run = g_func.getc("sync-ajax-post-term");
	// console.log("last_run:", last_run);
	if (last_run !== null) {
		last_run *= 1;
		last_run = Date.now() - last_run;
		console.log("last sync-ajax-post-term:", Math.ceil(last_run / 1000));
		return false;
	}

	//
	jQuery.ajax({
		type: "GET",
		url: "ajaxs/sync_ajax_post_term",
		dataType: "json",
		//crossDomain: true,
		//data: data,
		timeout: 33 * 1000,
		error: function (jqXHR, textStatus, errorThrown) {
			jQueryAjaxError(jqXHR, textStatus, errorThrown, new Error().stack);
		},
		success: function (data) {
			if (WGR_config.cf_tester_mode > 0) {
				console.log(data);
			}

			//
			g_func.setc("sync-ajax-post-term", Date.now(), 600);
		},
	});
}

// canvas fingerprint -> xác định thiết bị của người dùng
var run_builder_signature = false;
function WGR_builder_signature() {
	if (
		run_builder_signature !== false ||
		g_func.getc("WGR_logged_signature") !== null
	) {
		return false;
	}
	run_builder_signature = true;
	console.log("run_builder_signature:", run_builder_signature);

	//
	let canvas = document.body.appendChild(document.createElement("canvas"));
	let ctx = canvas.getContext("2d");
	canvas.height = 200;
	canvas.width = 375;
	canvas.style.position = "absolute";
	canvas.style.left = "-9999px";

	// Text with lowercase/uppercase/punctuation symbols
	let txt = document.domain || navigator.userAgent;
	// console.log("txt:", txt);
	ctx.textBaseline = "top";
	// The most common type
	ctx.font = "14px 'Arial'";
	ctx.textBaseline = "alphabetic";
	ctx.fillStyle = "#f60";
	ctx.fillRect(125, 1, 62, 20);
	// Some tricks for color mixing to increase the difference in rendering
	ctx.fillStyle = "#069";
	ctx.fillText(txt, 2, 15);
	ctx.fillStyle = "rgba(102, 204, 0, 0.7)";
	ctx.fillText(txt, 4, 17);

	// canvas blending
	// http://blogs.adobe.com/webplatform/2013/01/28/blending-features-in-canvas/
	// http://jsfiddle.net/NDYV8/16/
	ctx.globalCompositeOperation = "multiply";
	ctx.fillStyle = "rgb(255,0,255)";
	ctx.beginPath();
	ctx.arc(50, 50, 50, 0, Math.PI * 2, true);
	ctx.closePath();
	ctx.fill();
	ctx.fillStyle = "rgb(0,255,255)";
	ctx.beginPath();
	ctx.arc(100, 50, 50, 0, Math.PI * 2, true);
	ctx.closePath();
	ctx.fill();
	ctx.fillStyle = "rgb(255,255,0)";
	ctx.beginPath();
	ctx.arc(75, 100, 50, 0, Math.PI * 2, true);
	ctx.closePath();
	ctx.fill();
	ctx.fillStyle = "rgb(255,0,255)";
	// canvas winding
	// http://blogs.adobe.com/webplatform/2013/01/30/winding-rules-in-canvas/
	// http://jsfiddle.net/NDYV8/19/
	ctx.arc(75, 75, 75, 0, Math.PI * 2, true);
	ctx.arc(75, 75, 25, 0, Math.PI * 2, true);
	ctx.fill("evenodd");

	//
	let sha256 = (function () {
		// Eratosthenes seive to find primes up to 311 for magic constants. This is why SHA256 is better than SHA1
		let i = 1,
			j,
			K = [],
			H = [];

		while (++i < 18) {
			for (j = i * i; j < 312; j += i) {
				K[j] = 1;
			}
		}
		function x(num, root) {
			return ((Math.pow(num, 1 / root) % 1) * 4294967296) | 0;
		}
		for (i = 1, j = 0; i < 313; ) {
			if (!K[++i]) {
				H[j] = x(i, 2);
				K[j++] = x(i, 3);
			}
		}
		function S(X, n) {
			return (X >>> n) | (X << (32 - n));
		}
		function SHA256(b) {
			let HASH = H.slice((i = 0)),
				s = unescape(encodeURI(b)),
				/*
            encode as utf8 */ W = [],
				l = s.length,
				m = [],
				a,
				y,
				z;
			for (; i < l; )
				m[i >> 2] |= (s.charCodeAt(i) & 0xff) << (8 * (3 - (i++ % 4)));
			l *= 8;
			m[l >> 5] |= 0x80 << (24 - (l % 32));
			m[(z = ((l + 64) >> 5) | 15)] = l;

			for (i = 0; i < z; i += 16) {
				a = HASH.slice((j = 0), 8);
				for (; j < 64; a[4] += y) {
					if (j < 16) {
						W[j] = m[j + i];
					} else {
						W[j] =
							(S((y = W[j - 2]), 17) ^ S(y, 19) ^ (y >>> 10)) +
							(W[j - 7] | 0) +
							(S((y = W[j - 15]), 7) ^ S(y, 18) ^ (y >>> 3)) +
							(W[j - 16] | 0);
					}

					a.unshift(
						(y =
							((a.pop() +
								(S((b = a[4]), 6) ^ S(b, 11) ^ S(b, 25)) +
								(((b & a[5]) ^ (~b & a[6])) + K[j])) |
								0) +
							(W[j++] | 0)) +
							(S((l = a[0]), 2) ^ S(l, 13) ^ S(l, 22)) +
							((l & a[1]) ^ (a[1] & a[2]) ^ (a[2] & l))
					);
				}

				for (j = 8; j--; ) HASH[j] = a[j] + HASH[j];
			}

			for (s = ""; j < 63; )
				s += ((HASH[++j >> 3] >> (4 * (7 - (j % 8)))) & 15).toString(16);

			return s;
		}

		return SHA256;
	})();

	//
	// document.body.appendChild(document.createElement('br'));
	// document.body.appendChild(document.createTextNode(sha256(canvas.toDataURL())));
	// console.log(canvas.toDataURL());
	g_func.setc("WGR_logged_signature", sha256(canvas.toDataURL()), 3600);

	// xong việc thì ẩn nó đi
	canvas.style.opacity = "0";
	// console.log(g_func.getc("WGR_logged_signature"));

	//
	return true;
}

/**
 * Khi người dùng bấm nút thêm sp vào giỏ hàng
 * Web nào có sử dụng chức năng mua hàng thì trong file d.js của child-theme theme gọi tới function để dùng hiệu ứng thêm sp vào giỏ hàng
 */
function action_add_to_cart() {
	$(".click-add-to-cart").click(function () {
		let a = $(this).attr("data-id") || "";
		if (a != "") {
			console.log("Add to cart:", a);

			//
			a *= 1;
			if (isNaN(a)) {
				WGR_alert("Product ID ERROR", "error");
			} else {
				// lấy danh sách giỏ hàng hiện tại
				let current_cart = localStorage.getItem("wgr_local_cart_data");
				if (current_cart === null) {
					current_cart = {};
				} else {
					// console.log("current_cart:", current_cart);
					current_cart = JSON.parse(current_cart);
					// console.log("current_cart:", current_cart);
				}

				// xem sp này đã được thêm vào giỏ chưa
				if (typeof current_cart["_" + a] == "undefined") {
					current_cart["_" + a] = 1;
				} else {
					current_cart["_" + a] *= 1;
					if (isNaN(current_cart["_" + a]) || current_cart["_" + a] < 1) {
						current_cart["_" + a] = 1;
					} else {
						current_cart["_" + a] = current_cart["_" + a] + 1;
					}
				}
				console.log("current_cart:", current_cart);
				localStorage.setItem(
					"wgr_local_cart_data",
					JSON.stringify(current_cart)
				);
			}
		}
	});
}

/**
 * Khi hoàn tất quá trình đặt hàng -> clear cart
 */
function WGR_clear_local_cart() {
	return localStorage.removeItem("wgr_local_cart_data");
}
