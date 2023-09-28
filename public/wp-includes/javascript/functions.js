//console.log( typeof jQuery );
//console.log( typeof $ );
if (typeof $ == "undefined") {
	$ = jQuery;
}

var bg_load = "Loading...",
	//	ctimeout = null,
	// tỉ lệ tiêu chuẩn của video youtube -> lấy trên youtube
	//youtube_video_default_size = 315 / 560,
	youtube_video_default_size = 9 / 16,
	//	youtube_video_default_size = 480/ 854,
	// tên miền chính sử dụng code này
	primary_domain_usage_eb = "",
	disable_eblazzy_load = false,
	height_for_lazzy_load = 0,
	sb_submit_cart_disabled = 0,
	ebe_arr_cart_product_list = [],
	ebe_arr_cart_customer_info = [],
	arr_ti_le_global = {};

var global_window_width = jQuery(window).width(),
	web_link = window.location.protocol + "//" + document.domain + "/";

function WGR_html_alert(m, lnk) {
	return WGR_alert(m, lnk);
}

function WGR_alert(m, lnk) {
	if (typeof m == "undefined") {
		m = "";
	}
	if (typeof lnk == "undefined") {
		lnk = "";
	}
	//console.log(m);
	//console.log(lnk);

	//
	if (top != self) {
		top.WGR_alert(m, lnk);
	} else {
		if (m != "") {
			// class thể hiện màu sắc của alert
			var cl = "";
			if (lnk == "error") {
				cl = "redbg";
			} else if (lnk == "warning") {
				cl = "orgbg";
			}

			// id dùng để hẹn giờ tự ẩn
			var jd = "_" + Math.random().toString(32).replace(/\./gi, "_");

			//
			var htm = [
				'<div id="' + jd + '" class="' + cl + '" onClick="$(this).fadeOut();">',
				m,
				"</div>",
			].join(" ");
			//console.log(htm);

			//
			if ($("#my_custom_alert").length <= 0) {
				$("body").append('<div id="my_custom_alert"></div>');
			}
			$("#my_custom_alert").append(htm).show();

			//
			setTimeout(function () {
				$("#" + jd).remove();

				// nếu không còn div nào -> ẩn luôn
				if ($("#my_custom_alert div").length <= 0) {
					$("#my_custom_alert").fadeOut();
				}
			}, 6000);
		} else if (lnk != "") {
			return WGR_redirect(lnk);
		}
	}

	//
	return false;
}

function WGR_redirect(l) {
	if (top != self) {
		top.WGR_redirect(l);
	} else if (typeof l != "undefined" && l != "") {
		window.location = l;
	}
}

function WGR_show_try_catch_err(e) {
	return (
		"name: " +
		e.name +
		"; line: " +
		(e.lineNumber || e.line) +
		"; script: " +
		(e.fileName || e.sourceURL || e.script) +
		"; stack: " +
		(e.stackTrace || e.stack) +
		"; message: " +
		e.message
	);
}

//
var current_croll_up_or_down = 0;

function WGR_show_or_hide_to_top() {
	var new_scroll_top = window.scrollY || jQuery(window).scrollTop();

	//
	if (new_scroll_top > 120) {
		jQuery("body").addClass("ebfixed-top-menu");

		// xác định hướng cuộn chuột lên hay xuống
		if (current_croll_up_or_down > new_scroll_top) {
			jQuery("body")
				.addClass("ebfixed-up-menu")
				.removeClass("ebfixed-down-menu");
		} else if (current_croll_up_or_down < new_scroll_top) {
			jQuery("body")
				.addClass("ebfixed-down-menu")
				.removeClass("ebfixed-up-menu");
		}
		current_croll_up_or_down = new_scroll_top;

		//
		if (new_scroll_top > 500) {
			jQuery("body").addClass("ebshow-top-scroll");

			//
			_global_js_eb.ebBgLazzyLoad(new_scroll_top);
		} else {
			jQuery("body").removeClass("ebshow-top-scroll");
		}
	} else {
		jQuery("body")
			.removeClass("ebfixed-top-menu")
			.removeClass("ebfixed-up-menu")
			.removeClass("ebfixed-down-menu")
			.removeClass("ebshow-top-scroll");
	}
}

// set prop cho select
function WGR_set_prop_for_select(for_id) {
	$(for_id).each(function () {
		var a = $(this).attr("data-select") || "";

		// nếu có tham số này
		if (a != "" && !$(this).hasClass("set-selected")) {
			// select luôn dữ liệu tương ứng -> cắt theo dấu , -> vì có 1 số dữ liệu sẽ là multi select
			a = a.split(",");

			// select cho option đầu tiên
			$(this).val(a[0]).addClass("set-selected");

			// các option sau select kiểu prop
			for (var i = 0; i < a.length; i++) {
				$('option[value="' + a[i] + '"]', this)
					.prop("selected", true)
					.addClass("bold")
					.addClass("gray2bg");
			}
		}
	});
}

var g_func = {
	non_mark: function (str) {
		str = str.toLowerCase();
		str = str.replace(
			/\u00e0|\u00e1|\u1ea1|\u1ea3|\u00e3|\u00e2|\u1ea7|\u1ea5|\u1ead|\u1ea9|\u1eab|\u0103|\u1eb1|\u1eaf|\u1eb7|\u1eb3|\u1eb5/g,
			"a"
		);
		str = str.replace(
			/\u00e8|\u00e9|\u1eb9|\u1ebb|\u1ebd|\u00ea|\u1ec1|\u1ebf|\u1ec7|\u1ec3|\u1ec5/g,
			"e"
		);
		str = str.replace(/\u00ec|\u00ed|\u1ecb|\u1ec9|\u0129/g, "i");
		str = str.replace(
			/\u00f2|\u00f3|\u1ecd|\u1ecf|\u00f5|\u00f4|\u1ed3|\u1ed1|\u1ed9|\u1ed5|\u1ed7|\u01a1|\u1edd|\u1edb|\u1ee3|\u1edf|\u1ee1/g,
			"o"
		);
		str = str.replace(
			/\u00f9|\u00fa|\u1ee5|\u1ee7|\u0169|\u01b0|\u1eeb|\u1ee9|\u1ef1|\u1eed|\u1eef/g,
			"u"
		);
		str = str.replace(/\u1ef3|\u00fd|\u1ef5|\u1ef7|\u1ef9/g, "y");
		str = str.replace(/\u0111/g, "d");
		return str;
	},
	non_mark_seo: function (str) {
		str = this.non_mark(str);
		str = str.replace(/\s/g, "-");
		str = str.replace(
			/!|@|%|\^|\*|\(|\)|\+|\=|\<|\>|\?|\/|,|\.|\:|\;|\'|\"|\&|\#|\[|\]|~|$|_/g,
			""
		);
		str = str.replace(/-+-/g, "-");
		str = str.replace(/^\-+|\-+$/g, "");
		for (var i = 0; i < 5; i++) {
			str = str.replace(/--/g, "-");
		}
		str = (function (s) {
			var str = "",
				re = /^\w+$/,
				t = "";
			for (var i = 0; i < s.length; i++) {
				t = s.substr(i, 1);
				if (t == "-" || t == "+" || re.test(t) == true) {
					str += t;
				}
			}
			return str;
		})(str);
		return str;
	},
	strip_tags: function (input, allowed) {
		if (typeof input == "undefined" || input == "") {
			return "";
		}

		//
		if (typeof allowed == "undefined") {
			allowed = "";
		}

		//
		allowed = (
			((allowed || "") + "").toLowerCase().match(/<[a-z][a-z0-9]*>/g) || []
		).join("");
		var tags = /<\/?([a-z][a-z0-9]*)\b[^>]*>/gi,
			cm = /<!--[\s\S]*?-->|<\?(?:php)?[\s\S]*?\?>/gi;
		return input.replace(cm, "").replace(tags, function ($0, $1) {
			return allowed.indexOf("<" + $1.toLowerCase() + ">") > -1 ? $0 : "";
		});
	},
	trim: function (str) {
		return jQuery.trim(str);
		//		return str.replace(/^\s+|\s+$/g, "");
	},

	setc: function (name, value, seconds, days, set_domain) {
		var expires = "";

		// tính theo ngày -> số giây trong ngày luôn
		if (typeof days == "number" && days > 0) {
			seconds = days * 24 * 3600;
		} else {
			days = 0;
		}

		//
		//		if ( typeof seconds == 'number' && seconds > 0 ) {
		//		if ( typeof seconds == 'number' && seconds != 0 ) {
		if (typeof seconds == "number") {
			// chuyển sang dạng timestamp
			seconds = seconds * 1000;

			var date = new Date();
			date.setTime(date.getTime() + seconds);
			expires = "; expires=" + date.toGMTString();
		}

		// set cookie theo domain
		var cdomain = "";
		if (typeof set_domain != "undefined") {
			if (set_domain.toString().split(".").length == 1) {
				cdomain = window.location.host || document.domain || "";
			} else {
				cdomain = set_domain;
			}

			//
			cdomain = cdomain.split(".");
			//			console.log(cdomain);

			// bỏ www đi -> áp dụng cho tất cả các domain
			if (cdomain[0] == "www") {
				cdomain[0] = "";
				cdomain = cdomain.join(".");
			}
			// thêm dấu . vào đầu domain
			else if (cdomain[0] != "") {
				cdomain = "." + cdomain.join(".");
			}
			// có dấu . ở đầu rồi thì thôi
			else {
				cdomain = cdomain.join(".");
			}
			//			console.log(cdomain);

			//
			document.cookie =
				encodeURIComponent(name) +
				"=" +
				encodeURIComponent(value) +
				expires +
				";domain=" +
				cdomain +
				";path=/";
		} else {
			document.cookie =
				encodeURIComponent(name) +
				"=" +
				encodeURIComponent(value) +
				expires +
				";path=/";
		}

		//
		if (WGR_check_option_on(WGR_config.cf_tester_mode))
			console.log(
				"Set cookie: " +
					name +
					" with value: " +
					value +
					" for domain: " +
					cdomain +
					" time: " +
					seconds +
					" (" +
					days +
					" day)"
			);
	},
	getc: function (name) {
		var nameEQ = encodeURIComponent(name) + "=",
			ca = document.cookie.split(";"),
			re = "";
		for (var i = 0; i < ca.length; i++) {
			var c = ca[i];
			while (c.charAt(0) === " ") {
				c = c.substring(1, c.length);
			}
			if (c.indexOf(nameEQ) === 0) {
				re = decodeURIComponent(c.substring(nameEQ.length, c.length));
			}
		}

		//
		if (re == "") {
			return null;
		}

		return re;
	},

	delck: function (name) {
		g_func.setc(name, "", 0 - 24 * 3600 * 7);

		//		document.cookie = name + '=;expires=Thu, 01 Jan 1970 00:00:01 GMT;';

		// v1 -> lỗi
		//		g_func.setc(name, "", -1);
		//		g_func.setc(name, "", -1, 0, true);
	},

	text_only: function (str) {
		if (typeof str == "undefined" || str == "") {
			return "";
		}
		str = str.toString().replace(/[^a-zA-Z\s]/g, "");

		if (str == "") {
			return "";
		}

		return str;
	},
	number_only: function (str, format) {
		if (typeof str == "undefined" || str == "") {
			return 0;
		}
		// mặc định chỉ lấy số
		if (typeof format == "string" && format != "") {
			//			console.log(format);
			str = str.toString().replace(eval(format), "");

			if (str == "") {
				return 0;
			}

			//			return str;
			return str * 1;
		} else {
			str = str.toString().replace(/[^0-9\-\+]/g, "");

			if (str == "") {
				return 0;
			}

			//			return parseInt( str, 10 );
			return str * 1;
		}
	},
	only_number: function (str) {
		return g_func.number_only(str);
	},
	float_only: function (str) {
		return g_func.number_only(str, "/[^0-9-+.]/g");
	},
	money_format: function (str) {
		// loại bỏ số 0 ở đầu chuỗi số
		str = str.toString().replace(/\,/g, "").split(".");
		//		str[0] = parseInt( str[0], 10 );
		str[0] = str[0] * 1;

		// chuyển sang định dạng tiền tệ
		return g_func.formatCurrency(str.join("."), ",", 2);
	},
	number_format: function (str) {
		return g_func.formatCurrency(str);
	},
	formatV2Currency: function (number, decimals, dec_point, thousands_sep) {
		// Strip all characters but numerical ones.
		number = (number + "").replace(/[^0-9+\-Ee.]/g, "");
		var n = !isFinite(+number) ? 0 : +number,
			prec = !isFinite(+decimals) ? 0 : Math.abs(decimals),
			sep = typeof thousands_sep === "undefined" ? "," : thousands_sep,
			dec = typeof dec_point === "undefined" ? "." : dec_point,
			s = "",
			toFixedFix = function (n, prec) {
				var k = Math.pow(10, prec);
				return "" + Math.round(n * k) / k;
			};
		// Fix for IE parseFloat(0.55).toFixed(0) = 0;
		s = (prec ? toFixedFix(n, prec) : "" + Math.round(n)).split(".");
		if (s[0].length > 3) {
			s[0] = s[0].replace(/\B(?=(?:\d{3})+(?!\d))/g, sep);
		}
		if ((s[1] || "").length < prec) {
			s[1] = s[1] || "";
			s[1] += new Array(prec - s[1].length + 1).join("0");
		}
		//console.log(s);
		// bỏ đoạn sau dấu dec nếu nó là 2 số 0 trở lên
		return s.join(dec).split(dec + "00")[0];
	},
	formatCurrency: function (num, dot, num_thap_phan) {
		if (typeof num == "undefined" || num == "") {
			return 0;
		}

		//
		if (typeof dot == "undefined" || dot == "") {
			dot = ",";
		}
		//console.log( 'dot: ' + dot );
		var dec_point = ".";
		if (dot != ",") {
			dec_point = ",";
		}
		//console.log( 'dec_point: ' + dec_point );
		if (typeof num_thap_phan == "undefined" || num_thap_phan == "") {
			num_thap_phan = 0;
		}
		//console.log( 'num_thap_phan: ' + num_thap_phan );

		/*
		 * v3
		 */
		return g_func.formatV2Currency(num, num_thap_phan, dec_point, dot);
	},

	wh: function () {},
	opopup: function (o) {},

	mb_v2: function () {
		return WGR_is_mobile();
	},
	mb: function (a) {
		return g_func.mb_v2();
	},

	/**
	 * Returns a random number between min (inclusive) and max (exclusive)
	 */
	getRandomArbitrary: function (min, max) {
		return Math.random() * (max - min) + min;
	},

	/**
	 * Returns a random integer between min (inclusive) and max (inclusive)
	 * Using Math.round() will give you a non-uniform distribution!
	 */
	getRandomInt: function (min, max) {
		if (min != max && min < max) {
			return Math.floor(Math.random() * (max - min + 1)) + min;
		}
		return 0;
	},
	rand: function (min, max) {
		return g_func.getRandomInt(min, max);
	},
	short_string: function (str, len, more) {
		str = jQuery.trim(str);

		if (len > 0 && str.length > len) {
			var a = str.split(" ");
			//			console.log(a);
			str = "";

			for (var i = 0; i < a.length; i++) {
				if (a[i] != "") {
					str += a[i] + " ";

					if (str.length > len) {
						break;
					}
				}
			}
			//			console.log(str.length);
			str = jQuery.trim(str);

			if (typeof more == "undefined" || more == true || more == 1) {
				str += "...";
			}
		}

		return str;
	},
};

// duy trì trạng thái đăng nhập
function WGR_duy_tri_dang_nhap(max_i) {
	if (
		typeof WGR_config.current_user_id != "undefined" &&
		WGR_config.current_user_id <= 0
	) {
		return false;
	}
	if (typeof max_i != "number") {
		max_i = 15;
	} else if (max_i < 0) {
		window.location = window.location.href;
		return false;
	}
	if (typeof WGR_config.current_user_id != "undefined") {
		console.log(
			"Current user ID: " +
				WGR_config.current_user_id +
				" (max i: " +
				max_i +
				")"
		);
	}

	//
	jQuery.ajax({
		type: "GET",
		url: "logged/confirm_login",
		dataType: "json",
		//crossDomain: true,
		//data: data,
		timeout: 33 * 1000,
		error: function (jqXHR, textStatus, errorThrown) {
			jQueryAjaxError(jqXHR, textStatus, errorThrown, new Error().stack);
		},
		success: function (data) {
			console.log(data);

			//
			setTimeout(function () {
				WGR_duy_tri_dang_nhap(max_i - 1);
			}, 5 * 60 * 1000);
		},
	});

	//
	return true;
}

// tạo vòng lặp để hiển thị danh sách nhóm từ ID -> làm vậy cho nhẹ web
function get_taxonomy_data_by_ids(arr, jd) {
	//console.log(arr);

	if (jd > 0) {
		for (var i = 0; i < arr.length; i++) {
			if (arr[i].term_id * 1 == jd) {
				return arr[i];
			}
		}

		// thử tìm trong các nhóm con
		for (var i = 0; i < arr.length; i++) {
			if (
				typeof arr[i].child_term == "undefined" ||
				arr[i].child_term.length <= 0
			) {
				continue;
			}

			var taxonomy_data = get_taxonomy_data_by_ids(arr[i].child_term, jd);
			if (taxonomy_data !== null) {
				return taxonomy_data;
			}
		}
	}

	//
	return null;
}

// hiển thị tên của danh mục bằng javascript -> giảm tải cho server
var taxonomy_ids_unique = [];
// mảng chứa thông tin của term để hiển thị
var arr_ajax_taxonomy = [];
// khi tiến trình nạp dữ liệu qua ajax hoàn tất thì đổi nó thành true -> để các tiến trình khác dễ nắm bắt
var ready_load_ajax_taxonomy = false;
// không cho việc load ajax diễn ra liên tục
var loading_ajax_taxonomy = false;
// nạp lại taxonomy nếu có yêu cầu
var reload_ajax_taxonomy = false;

// lấy thông tin các taxonomy đang hiện hoạt trên trang
function action_each_to_taxonomy() {
	try {
		if (WGR_config.cf_tester_mode > 0 && arguments.callee.caller !== null) {
			console.log("Call in: " + arguments.callee.caller.name.toString());
		}
	} catch (e) {
		WGR_show_try_catch_err(e);
	}

	// nếu đang có tiến trình được kích hoạt thỉ hủy bỏ việc nạp -> chờ đợi
	if (loading_ajax_taxonomy === true) {
		if (WGR_config.cf_tester_mode > 0) console.log("loading ajax taxonomy");
		// bật chế độ nạp lại taxonomy
		reload_ajax_taxonomy = true;
		return false;
	}
	loading_ajax_taxonomy = true;

	// daidq (2022-03-06): thử cách nạp các nhóm được hiển thị trên trang hiện tại -> cách này nạp ít dữ liệu mà độ chuẩn xác lại cao
	taxonomy_ids_unique = [];
	if (WGR_config.cf_tester_mode > 0)
		console.log("action each to taxonomy:", $(".each-to-taxonomy").length);
	//return false;

	//
	$('.each-to-taxonomy[data-id="0"], .each-to-taxonomy[data-id=""]')
		.removeClass("each-to-taxonomy")
		.addClass("zero-to-taxonomy");

	// lấy các ID có
	$(".each-to-taxonomy").each(function () {
		var a = $(this).attr("data-id") || "";
		//console.log('a:', a);
		var as = $(this).attr("data-ids") || "";
		//console.log('as:', as);
		//var taxonomy = $(this).attr('data-taxonomy') || '';
		//console.log('taxonomy:', taxonomy);

		if (a == "") {
			a = as;
		}
		//console.log('a:', a);

		//if (a != '' && taxonomy != '') {
		if (a != "") {
			a = a.split(",");
			//var str = [];
			for (var i = 0; i < a.length; i++) {
				if (a[i] != "") {
					a[i] = $.trim(a[i]);
					a[i] *= 1;
					if (a[i] > 0) {
						var has_add = false;
						for (var j = 0; j < taxonomy_ids_unique.length; j++) {
							if (a[i] == taxonomy_ids_unique[j]) {
								has_add = true;
								break;
							}
						}
						//console.log('has add:', has_add);
						if (has_add === false) {
							taxonomy_ids_unique.push(a[i]);
						}
					}
				}
			}

			//
			$(this).addClass("loading-to-taxonomy").removeClass("each-to-taxonomy");
		}
	});
	//console.log(taxonomy_ids_unique);
	// nếu không có ID nào cẩn xử lý thì bỏ qua đoạn sau luôn
	if (taxonomy_ids_unique.length == 0) {
		if (WGR_config.cf_tester_mode > 0)
			console.log("taxonomy ids unique length");

		//
		reset_each_to_taxonomy();
		//after_each_to_taxonomy();
		return false;
	}
	//console.log(taxonomy_ids_unique);
	//return false;

	// chạy ajax nạp dữ liệu của taxonomy
	jQuery.ajax({
		type: "POST",
		url: "ajaxs/get_taxonomy_by_ids",
		dataType: "json",
		//crossDomain: true,
		data: {
			ids: taxonomy_ids_unique.join(","),
		},
		timeout: 33 * 1000,
		error: function (jqXHR, textStatus, errorThrown) {
			jQueryAjaxError(jqXHR, textStatus, errorThrown, new Error().stack);
		},
		success: function (data) {
			if (WGR_config.cf_tester_mode > 0) console.log(data);

			//
			if (reload_ajax_taxonomy === true) {
				setTimeout(function () {
					if (WGR_config.cf_tester_mode > 0)
						console.log("reload ajax taxonomy");
					action_each_to_taxonomy();
				}, 100);
			}

			// xong việc thì gán lại các tham số này về mặc định
			reset_each_to_taxonomy();

			//
			return after_each_to_taxonomy(data);
		},
	});

	//
	//taxonomy_ids_unique = [];
}

function reset_each_to_taxonomy() {
	// xác nhận taxonomy đã được nạp xong
	ready_load_ajax_taxonomy = true;

	// gán lại các tham số này về mặc định
	loading_ajax_taxonomy = false;
	reload_ajax_taxonomy = false;
}

// hiển thị tên danh mục sau khi nạp xong
function after_each_to_taxonomy(data) {
	//console.log(data);
	//return false;

	//
	$(".loading-to-taxonomy").each(function () {
		var a = $(this).attr("data-id") || "";
		//console.log(a);
		var as = $(this).attr("data-ids") || "";
		//console.log(as);
		//var taxonomy = $(this).attr('data-taxonomy') || '';
		var uri = $(this).attr("data-uri") || "";
		if (uri != "") {
			// thêm term_id nếu không có trong yêu cầu
			if (uri.split("%term_id%").length == 1) {
				if (uri.split("?").length > 1) {
					uri += "&";
				} else {
					uri += "?";
				}
				uri += "term_id=%term_id%";
			}
		}
		// class riêng cho thẻ A nếu có
		var a_class = $(this).attr("data-class") || "";
		// giãn cách giữa các thẻ A
		var a_space = $(this).attr("data-space") || ", ";

		if (a == "") {
			a = as;
		}

		//
		//if (a != '' && taxonomy != '') {
		if (a != "") {
			a = a.split(",");
			var str = [];
			for (var i = 0; i < a.length; i++) {
				if (a[i] != "") {
					var taxonomy_data = get_taxonomy_data_by_ids(data, a[i] * 1);
					//console.log(taxonomy_data);
					if (taxonomy_data === null) {
						str.push("#" + a[i]);
						continue;
					}
					arr_ajax_taxonomy.push(taxonomy_data);

					//
					var taxonomy_name =
						taxonomy_data.term_shortname != ""
							? taxonomy_data.term_shortname
							: taxonomy_data.name;
					if (uri != "") {
						// thay thế dữ liệu cho uri
						var url = uri;
						for (var x in taxonomy_data) {
							url = url.replace("%" + x + "%", taxonomy_data[x]);
						}

						//
						taxonomy_name =
							'<a href="' +
							url +
							'" class="' +
							a_class +
							'">' +
							taxonomy_name +
							"</a>";
						//console.log(taxonomy_name);
					}

					if (taxonomy_name != "") {
						str.push(taxonomy_name);
					}
				}
			}

			// in ra
			$(this).html(str.join(a_space));
		}

		//
		$(this).addClass("loaded-to-taxonomy").removeClass("loading-to-taxonomy");
	});
}

// kiểm tra xem trình duyệt có hỗ trợ định dạng webp không
function support_format_webp() {
	var elem = document.createElement("canvas");

	if (!!(elem.getContext && elem.getContext("2d"))) {
		// was able or not to get WebP representation
		return elem.toDataURL("image/webp").indexOf("data:image/webp") == 0;
	} else {
		// very old browser like IE 8, canvas not supported
		return false;
	}
}

function WGR_is_mobile(a) {
	if (screen.width < 775 || jQuery(window).width() < 775) {
		return true;
	}

	//
	if (typeof a == "undefined" || a == "") {
		a = navigator.userAgent;
	}

	//
	if (
		a.split("Mobile").length > 1 || // Many mobile devices (all iPhone, iPad, etc.)
		a.split("Android").length > 1 ||
		a.split("Silk/").length > 1 ||
		a.split("Kindle").length > 1 ||
		a.split("BlackBerry").length > 1 ||
		a.split("Opera Mini").length > 1 ||
		a.split("Opera Mobi").length > 1
	) {
		return true;
	}
	return false;
}

function get_term_permalink(data) {
	//console.log(data);
	return web_link + data.term_permalink;
}

// tạo menu tự động dựa theo danh mục đang có
function create_menu_by_taxonomy(arr, li_class, show_favicon, ops) {
	if (arr.length <= 0) {
		console.log("create menu by taxonomy:", arr.length);
		return "";
	}
	console.log("create menu by taxonomy:", arr.length);
	//console.log(arr);

	//
	if (typeof show_favicon == "undefined") {
		show_favicon = false;
	}

	//
	if (typeof li_class == "undefined" || li_class == "") {
		li_class = "parent-menu";
	}

	//
	//console.log(typeof ops);
	//console.log(ops);
	if (typeof ops != "object") {
		ops = {};
	}

	//
	var str = "";
	for (var i = 0; i < arr.length; i++) {
		// không hiển thị các danh mục không có bài viết hoặc bị đánh dấu ẩn
		if (arr[i].count * 1 <= 0 || arr[i].term_status * 1 > 0) {
			continue;
		}

		//
		if (
			typeof arr[i].term_shortname == "undefined" ||
			arr[i].term_shortname == ""
		) {
			arr[i].term_shortname = arr[i].name;
		}

		// hiển thị icon cho danh mục nếu có
		var img_favicon = "";
		if (
			show_favicon === true &&
			typeof arr[i].term_favicon != "undefined" &&
			arr[i].term_favicon != ""
		) {
			var ops_width = "",
				ops_height = "";
			if (typeof ops.width != "undefined") {
				ops_width = ' width="' + ops.width + '"';
			}

			//
			if (typeof ops.height != "undefined") {
				ops_height = ' height="' + ops.height + '"';
			}
			//console.log(ops);

			//
			img_favicon =
				'<img src="' +
				arr[i].term_favicon +
				'"' +
				ops_width +
				ops_height +
				' alt="' +
				arr[i].term_shortname +
				'"> ';
		}

		//
		var sub_menu = "";
		//console.log(typeof arr[i].child_term);
		if (
			typeof arr[i].child_term != "undefined" &&
			arr[i].child_term.length > 0
		) {
			sub_menu = create_menu_by_taxonomy(
				arr[i].child_term,
				"childs-menu",
				show_favicon,
				ops
			);
			if (sub_menu != "") {
				sub_menu = '<ul class="sub-menu">' + sub_menu + "</ul>";
			}
		}
		//console.log(get_term_permalink(arr[i]));

		//
		str +=
			'<li data-id="' +
			arr[i].term_id +
			'" class="' +
			li_class +
			'"><a href="' +
			get_term_permalink(arr[i]) +
			'" data-id="' +
			arr[i].term_id +
			'">' +
			img_favicon +
			arr[i].term_shortname +
			' <span class="taxonomy-count">' +
			arr[i].count +
			"</span></a>" +
			sub_menu +
			"</li>";
	}
	//console.log(str);

	//
	return str;
}

function WGR_check_option_on(a) {
	if (a * 1 > 0) {
		return true;
	}
	return false;
}

// khi muốn nạp nhiều lệnh vue js 1 lúc (ngăn cách bởi dấu ,) -> sử dụng hàm này
function WGR_multi_vuejs(app_id, obj, _callBack, max_i) {
	app_id = app_id.split(",");
	for (var i = 0; i < app_id.length; i++) {
		app_id[i] = $.trim(app_id[i]);

		//
		WGR_vuejs(app_id[i], obj, _callBack, max_i);
	}
}

// các pha nạp vuejs xong sẽ nạp lại taxonomy
function WGR_taxonomy_vuejs(app_id, obj, _callBack, max_i) {
	obj.action_taxonomy = 1;
	return WGR_vuejs(app_id, obj, _callBack, max_i);
}

// chờ vuejs nạp xong để khởi tạo nội dung
function WGR_vuejs(app_id, obj, _callBack, max_i) {
	if (typeof max_i != "number") {
		max_i = 100;
	} else if (max_i < 0) {
		console.log("%c Max loaded Vuejs", "color: red");
		return false;
	}

	//
	if (typeof Vue != "function") {
		setTimeout(function () {
			WGR_vuejs(app_id, obj, _callBack, max_i - 1);
		}, 100);
		return false;
	}

	// gọi tới chức năng nạp taxonomy -> mặc định là có
	if (typeof obj.action_taxonomy == "undefined") {
		obj.action_taxonomy = 0;
	}

	// chưa tìm ra hàm định dạng ngày tháng tương tự angular -> tự viết hàm riêng vậy
	// -> xác định giờ theo múi giờ hiện tại của user
	var tzoffset = new Date().getTimezoneOffset() * 60000; // offset in milliseconds
	//console.log('tzoffset:', tzoffset);
	obj.datetime = function (t, len) {
		if (typeof len != "number") {
			len = 19;
		}
		return new Date(t - tzoffset)
			.toISOString()
			.split(".")[0]
			.replace("T", " ")
			.substr(0, len);
	};
	obj.date = function (t) {
		return new Date(t - tzoffset).toISOString().split("T")[0];
	};
	obj.time = function (t, len) {
		if (typeof len != "number") {
			len = 8;
		}
		return new Date(t - tzoffset)
			.toISOString()
			.split(".")[0]
			.split("T")[1]
			.substr(0, len);
	};
	obj.number_format = function (n) {
		return new Intl.NumberFormat().format(n);
	};

	//
	//console.log(obj);
	//console.log(obj.data);
	new Vue({
		el: app_id,
		data: obj,
		mounted: function () {
			$(app_id + ".ng-main-content, " + app_id + " .ng-main-content").addClass(
				"loaded"
			);

			//
			if (typeof _callBack == "function") {
				_callBack();
			}

			//console.log(taxonomy_ids_unique);
			//if (obj.action_taxonomy === 1 && taxonomy_ids_unique.length == 0) {
			action_each_to_taxonomy();
			//}
		},
	});
}

function move_custom_code_to() {
	$(".move-custom-code-to")
		.each(function () {
			var data_to = $(this).attr("data-to") || "";
			if (data_to != "") {
				var str = $(this).html() || "";
				$(this).text("");

				//
				var type_move = $(this).attr("data-type") || "";
				if (type_move == "before") {
					$(data_to).before(str);
				} else if (type_move == "after") {
					$(data_to).after(str);
				} else {
					$(data_to).append(str);
				}
				console.log(
					"Move custom code to: " + data_to + " with type:",
					type_move
				);
			} else {
				console.log(
					"%c move-custom-code-to[data-to] not found!",
					"color: darkviolet;"
				);
			}
		})
		.addClass("move-custom-code-done")
		.removeClass("move-custom-code-to");
}

// kiểm tra url hiện tại có trùng với canonical không, nếu không thì redirect tới canonical
function redirect_to_canonical(body_class) {
	// không thực hiện redirect ở trang 404
	if (body_class.split("page404").length > 1) {
		console.log("%c is 404 page!", "color: red;");
		return false;
	}

	//
	var a = $('link[rel="canonical"]').attr("href") || "";
	//console.log(a);
	if (a != "" && window.location.href.split(a).length === 1) {
		if (a.split("?").length > 1) {
			a += "&";
		} else {
			a += "?";
		}
		a += "canonical=client&uri=" + encodeURIComponent(window.location.href);
		//console.log(a);
		window.location = a;
	}
}

function hide_if_esc() {
	if (top != self) {
		return top.hide_if_esc();
	}

	//
	$(".hide-if-esc").hide();
	$("body").removeClass("no-scroll");

	//
	return false;
}

function WGR_open_poup(str, tit, __callBack) {
	$("#popupModalLabel").html(tit);
	$("#popupModal .modal-body").html(str);
	if (typeof __callBack == "function") {
		__callBack();
	}
	$("#popupModal").modal("show");
}

function WGR_get_params(param) {
	var queryString = window.location.search;
	//console.log(queryString);
	var urlParams = new URLSearchParams(queryString);
	var a = urlParams.get(param);
	//console.log(a);
	return a === null ? "" : a;
}

function jQueryAjaxError(jqXHR, textStatus, errorThrown, errorStack) {
	if (typeof errorStack != "undefined") {
		console.log(errorStack);
	}
	console.log(jqXHR);
	if (typeof jqXHR.responseText != "undefined") {
		console.log(jqXHR.responseText);
	}
	console.log(errorThrown);
	console.log(textStatus);
	if (textStatus === "timeout") {
	}
}
