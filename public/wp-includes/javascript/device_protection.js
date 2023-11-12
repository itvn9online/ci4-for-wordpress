/**
 * kiểm tra xem người dùng có đăng nhập trên nhiều thiết bị không
 **/
// cho hàm _run vào function để không bị thay đổi code
// tạo biến rm chứa thông tin reqquest thông qua hàm JSON để tham số này không bị thay đổi từ bên ngoài
(function (rm) {
	// canvas fingerprint -> xác định thiết bị của người dùng
	if (g_func.getc("WGR_logged_signature") === null) {
		var canvas = document.body.appendChild(document.createElement("canvas"));
		var ctx = canvas.getContext("2d");
		canvas.height = 200;
		canvas.width = 375;
		canvas.style.position = "absolute";
		canvas.style.left = "-9999px";

		// Text with lowercase/uppercase/punctuation symbols
		var txt = document.domain || navigator.userAgent;
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
		var sha256 = (function () {
			// Eratosthenes seive to find primes up to 311 for magic constants. This is why SHA256 is better than SHA1
			var i = 1,
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
				var HASH = H.slice((i = 0)),
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
	}
	// console.log(g_func.getc("WGR_logged_signature"));

	//
	var _run = function () {
		var min_time = 5;
		var max_time = 30;

		// nếu không có modal ẩn cảnh báo -> nạp html
		if ($("#warningLoggedModal").length < 1) {
			jQuery.ajax({
				type: "POST",
				// link TEST
				url: rm.logged + "?nse=" + Math.random(),
				dataType: "html",
				//crossDomain: true,
				data: {
					_wpnonce: g_func.getc("WGR_logged_signature"),
					the_modal: 1,
				},
				timeout: 33 * 1000,
				error: function (jqXHR, textStatus, errorThrown) {
					jQueryAjaxError(jqXHR, textStatus, errorThrown, new Error().stack);
				},
				success: function (data) {
					//console.log(data);
					$("body").append(data);
				},
				/*
				complete: function (xhr, status) {
					console.log(xhr);
					console.log(status);
				},
				*/
			});

			//
			setTimeout(function () {
				if ($("#warningLoggedModal").length < 1) {
					console.log("Không xác định được modal: Logged");
				}
				_run();
			}, max_time * 1000);

			//
			return false;
		}

		// nếu người dùng chưa close modal thì thôi không cần kiểm tra -> vì có close mới tiếp tục được
		if ($("#warningLoggedModal").hasClass("show")) {
			if (WGR_check_option_on(WGR_config.cf_tester_mode)) {
				console.log(Math.random());
			}

			//
			setTimeout(function () {
				_run();
			}, min_time * 1000);

			//
			return false;
		}

		//
		jQuery.ajax({
			type: "POST",
			// link TEST
			url: rm.logged + "?nse=" + Math.random(),
			dataType: "json",
			//crossDomain: true,
			data: {
				_wpnonce: g_func.getc("WGR_logged_signature"),
			},
			timeout: 33 * 1000,
			error: function (jqXHR, textStatus, errorThrown) {
				jQueryAjaxError(jqXHR, textStatus, errorThrown, new Error().stack);
			},
			success: function (data) {
				if (WGR_check_option_on(WGR_config.cf_tester_mode)) console.log(data);

				// bình thường thì để 30s kiểm tra 1 lần
				rm.timeout_dp = max_time;

				//
				if (typeof data.error != "undefined") {
					WGR_alert(data.error, "error");
				}
				// không có hash
				else if (typeof data.hash == "undefined") {
					WGR_alert("Không xác định được phiên đăng nhập", "error");
				}
				// nếu hash null -> đã hết phiên
				else if (!data.hash) {
					console.log(data);
				}
				// có hash mà hash khác nhau -> báo cho người dùng biết
				else {
					//data.hash = JSON.parse(data.hash);
					//console.log(data);

					//
					if (
						typeof data.hash.key != "undefined" &&
						data.hash.key != "" &&
						data.hash.key != $("body").attr("data-session")
					) {
						//
						$(".show-logged-ip")
							//.text(data.hash.ip)
							.text(data.hash.key)
							.attr({
								href:
									"https://www.iplocation.net/ip-lookup?query=" + data.hash.ip,
							});
						$(".show-logged-agent").text(data.hash.agent);
						$(".show-logged-device").html(
							WGR_is_mobile(data.hash.agent) === false
								? '<i class="fa fa-desktop"></i>'
								: '<i class="fa fa-mobile"></i>'
						);

						//
						//WGR_alert('Vui lòng không đăng nhập trên nhiều thiết bị!', 'error');
						$("#warningLoggedModal").modal("show");

						//console.log(data.logout);
						// khi có nghi ngờ -> rút ngắn thời gian kiểm tra lại
						if (
							typeof data.chash != "undefined" &&
							data.chash == data.hash.key
						) {
							// hash trong cache mà giống với hash trong db thì cũng bỏ qua luôn
						} else if (
							typeof data.logout != "undefined" &&
							data.logout == "on"
						) {
							rm.logout_dp = data.logout;

							//
							jQuery.ajax({
								type: "POST",
								// link TEST
								url: rm.logout + "?nse=" + Math.random(),
								dataType: "json",
								//crossDomain: true,
								data: {
									_wpnonce: g_func.getc("WGR_logged_signature"),
								},
								timeout: 33 * 1000,
								error: function (jqXHR, textStatus, errorThrown) {
									jQueryAjaxError(
										jqXHR,
										textStatus,
										errorThrown,
										new Error().stack
									);
								},
								success: function (data) {
									if (
										typeof data.redirect_to != "undefined" &&
										data.redirect_to != ""
									) {
										window.location = data.redirect_to;
									} else if (
										typeof data.error != "undefined" &&
										data.error != ""
									) {
										WGR_alert(data.error, "error");
									} else {
										WGR_alert("Device protection actived", "error");
									}
								},
							});
						} else {
							rm.timeout_dp = min_time;
						}
					}
				}

				//
				setTimeout(function () {
					_run();
				}, rm.timeout_dp * 1000);
			},
		});
	};

	//
	if (WGR_config.current_user_id > 0) {
		setTimeout(function () {
			_run();
		}, 5 * 1000);
	}
})(JSON.parse(JSON.stringify(_rqrm)));

//
function confirm_kip_logged() {
	jQuery.ajax({
		type: "POST",
		// link TEST
		url: _rqrm.cflogged + "?nse=" + Math.random(),
		dataType: "json",
		//crossDomain: true,
		data: {
			_wpnonce: g_func.getc("WGR_logged_signature"),
			user_id: WGR_config.current_user_id,
		},
		timeout: 33 * 1000,
		error: function (jqXHR, textStatus, errorThrown) {
			jQueryAjaxError(jqXHR, textStatus, errorThrown, new Error().stack);
		},
		success: function (data) {
			console.log(data);

			// nạp lại trang
			if (rm.logout_dp == "on") {
				window.location = window.location.href;
			}
		},
	});

	//
	return true;
}
