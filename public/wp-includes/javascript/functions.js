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
	arr_ti_le_global = [],
	// tiền tệ mặc định
	currency_fraction_digits = 2,
	currency_locales_format = "",
	currency_sd_format = "USD",
	//
	global_window_width = jQuery(window).width(),
	web_link = window.location.origin + "/";

// config mặc định
if (typeof WGR_config != "undefined") {
	if (
		typeof WGR_config.currency_fraction_digits != "undefined" &&
		WGR_config.currency_fraction_digits != ""
	) {
		currency_fraction_digits = WGR_config.currency_fraction_digits * 1;
		// console.log("currency fraction digits", currency_fraction_digits);
	}

	//
	if (typeof WGR_config.currency_locales_format != "undefined") {
		currency_locales_format = WGR_config.currency_locales_format;
	}

	//
	if (
		typeof WGR_config.currency_sd_format != "undefined" &&
		WGR_config.currency_sd_format != ""
	) {
		currency_sd_format = WGR_config.currency_sd_format.toUpperCase();
	}
}
// console.log("currency fraction digits", currency_fraction_digits);

// định dạng số -> tương tự number_format trong php
var numFormatter = new Intl.NumberFormat("en-US");

// định dạng tiền tệ
var moneyFormatter = numFormatter;
// nếu có định dạng tiền tệ bằng javascript
if (currency_locales_format != "") {
	moneyFormatter = new Intl.NumberFormat(
		currency_locales_format.replaceAll("_", "-").toLowerCase(),
		{
			style: "currency",
			currency: currency_sd_format,
			// minimumFractionDigits: currency_fraction_digits,
		}
	);
}

function WGR_html_alert(m, lnk, auto_hide) {
	return WGR_alert(m, lnk, auto_hide);
}

function WGR_alert(m, lnk, auto_hide) {
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
			console.log(m);
			// class thể hiện màu sắc của alert
			let cl = "";
			if (lnk == "error" || lnk == "danger") {
				cl = "redbg";
			} else if (lnk == "warning") {
				cl = "orgbg";
			}

			// id dùng để hẹn giờ tự ẩn
			let jd = "_" + Math.random().toString(32).replace(/\./gi, "_");

			//
			let htm = [
				'<div id="' +
					jd +
					'" class="' +
					cl +
					'" onClick="jQuery(this).fadeOut();">',
				m,
				"</div>",
			].join(" ");
			//console.log(htm);

			//
			if (jQuery("#my_custom_alert").length < 1) {
				jQuery("body").append('<div id="my_custom_alert"></div>');
			}
			jQuery("#my_custom_alert").append(htm).show();

			//
			if (typeof auto_hide != "number") {
				auto_hide = 6000;
			} else if (auto_hide > 0 && auto_hide < 120) {
				auto_hide *= 1000;
			}

			//
			if (auto_hide > 0) {
				setTimeout(() => {
					jQuery("#" + jd).remove();

					// nếu không còn div nào -> ẩn luôn
					if (jQuery("#my_custom_alert div").length < 1) {
						jQuery("#my_custom_alert").fadeOut();
					}
				}, auto_hide);
			}
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
	let new_scroll_top = window.scrollY || jQuery(window).scrollTop();

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
	jQuery(for_id).each(function () {
		let a = jQuery(this).attr("data-select") || "";

		// nếu có tham số này
		if (a != "" && !jQuery(this).hasClass("set-selected")) {
			// select luôn dữ liệu tương ứng
			/*
			let has_multi = jQuery(this).attr("multiple");
			if (typeof has_multi !== "undefined" && has_multi !== false) {
				console.log("has_multi:", has_multi);
			} else {
				*/
			// nếu ko phải select multi -> cắt theo dấu , -> vì có 1 số dữ liệu sẽ là multi select
			a = a.split(",");
			// }

			// select cho option đầu tiên
			jQuery(this).val(a[0]).addClass("set-selected");

			// các option sau select kiểu prop
			for (let i = 0; i < a.length; i++) {
				jQuery('option[value="' + a[i] + '"]', this)
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
		for (let i = 0; i < 5; i++) {
			str = str.replace(/--/g, "-");
		}
		str = (function (s) {
			// console.log("s", s);
			let str = "",
				re = /^\w+$/,
				t = "";
			for (let i = 0; i < s.length; i++) {
				t = s.slice(i, i + 1);
				// console.log("t", t, "i", i);
				if (t == "-" || t == "+" || re.test(t) == true) {
					str += t;
				}
			}
			// console.log("str", str);
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
		let tags = /<\/?([a-z][a-z0-9]*)\b[^>]*>/gi,
			cm = /<!--[\s\S]*?-->|<\?(?:php)?[\s\S]*?\?>/gi;
		return input.replace(cm, "").replace(tags, function ($0, $1) {
			return WGR_in_array("<" + $1.toLowerCase() + ">", allowed) ? $0 : "";
		});
	},
	trim: function (str) {
		return jQuery.trim(str);
		//		return str.replace(/^\s+|\s+$/g, "");
	},

	setc: function (name, value, seconds, days, set_domain) {
		if (typeof seconds != "number") {
			seconds = 3600;
		}
		if (typeof days != "number") {
			days = 0;
		} else {
			days = days * 86400;
		}

		//
		let ex = Math.floor(Date.now() / 1000) + seconds + days;

		//
		return localStorage.setItem(name, ex.toString() + "|" + value);
	},
	getc: function (name) {
		let a = localStorage.getItem(name);
		if (a !== null) {
			a = a.split("|");
			let ex = a[0] * 1;
			//console.log(a, ex);
			if (isNaN(ex) || ex < Math.floor(Date.now() / 1000)) {
				return null;
			}
			// xong xóa phần tử đầu tiên
			a.shift();
			a = a.join("|");
		}
		return a;
	},
	delck: function (name) {
		return localStorage.removeItem(name);
	},

	text_only: function (str) {
		if (typeof str == "undefined" || str == "") {
			return "";
		}
		str = str.toString().replace(/[^a-zA-Z\s]/g, "");
		return str;
	},
	number_only: function (str, format) {
		if (typeof str == "undefined" || str == "") {
			return 0;
		}
		// mặc định chỉ lấy số
		if (typeof format == "string" && format != "") {
			// console.log(format);
			str = str.toString().replace(eval(format), "");
			if (str == "") {
				return 0;
			}
			// return str;
			return str * 1;
		} else {
			str = str.toString().replace(/[^0-9\-\+]/g, "");
			if (str == "") {
				return 0;
			}
			// return parseInt( str, 10 );
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
		if (str == null) {
			return 0;
		}
		// loại bỏ số 0 ở đầu chuỗi số
		str = str.toString().replace(/\,/g, "") * 1;
		// console.log(str);
		if (isNaN(str)) {
			return "NaN";
		}
		str = str.toFixed(currency_fraction_digits);
		// console.log(str);
		if (str < 1000 && str > -999) {
			return str;
		}

		// chuyển sang định dạng tiền tệ
		return moneyFormatter.format(str);
	},
	number_format: function (str) {
		// loại bỏ số 0 ở đầu chuỗi số
		str = str.toString().replace(/\,/g, "") * 1;
		// console.log(str);
		if (isNaN(str)) {
			return "NaN";
		} else if (str < 1000 && str > -999) {
			return str;
		}
		return numFormatter.format(str);
	},
	formatCurrency: function (num) {
		return g_func.money_format(num);
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
		if (min < max) {
			return Math.floor(Math.random() * (max - min + 1)) + min;
		} else if (min > max) {
			return g_func.getRandomInt(max, min);
		} else if (min > 0) {
			return min;
		}
		return 0;
	},
	rand: function (min, max) {
		return g_func.getRandomInt(min, max);
	},
	short_string: function (str, len, more) {
		str = jQuery.trim(str);

		if (len > 0 && str.length > len) {
			let a = str.split(" ");
			//			console.log(a);
			str = "";

			for (let i = 0; i < a.length; i++) {
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
		WGR_config.current_user_id < 1
	) {
		return false;
	}
	if (typeof max_i != "number") {
		max_i = 15;
	} else if (max_i < 0) {
		window.location.reload();
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
			setTimeout(() => {
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
		for (let i = 0; i < arr.length; i++) {
			if (arr[i].term_id * 1 == jd) {
				return arr[i];
			}
		}

		// thử tìm trong các nhóm con
		for (let i = 0; i < arr.length; i++) {
			if (
				typeof arr[i].child_term == "undefined" ||
				arr[i].child_term.length < 1
			) {
				continue;
			}

			let taxonomy_data = get_taxonomy_data_by_ids(arr[i].child_term, jd);
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
		console.log("action each to taxonomy:", jQuery(".each-to-taxonomy").length);
	//return false;

	//
	jQuery('.each-to-taxonomy[data-id="0"], .each-to-taxonomy[data-id=""]')
		.removeClass("each-to-taxonomy")
		.addClass("zero-to-taxonomy");

	// lấy các ID có
	jQuery(".each-to-taxonomy").each(function () {
		let a = jQuery(this).attr("data-id") || "";
		//console.log('a:', a);
		let as = jQuery(this).data("ids") || "";
		//console.log('as:', as);
		//let taxonomy = jQuery(this).attr('data-taxonomy') || '';
		//console.log('taxonomy:', taxonomy);

		if (a == "") {
			a = as;
		}
		// console.log("a:", a);

		//if (a != '' && taxonomy != '') {
		if (a != "") {
			a = a.split(",");
			//let str = [];
			for (let i = 0; i < a.length; i++) {
				if (a[i] != "") {
					a[i] = jQuery.trim(a[i]);
					a[i] *= 1;
					if (a[i] > 0 && !WGR_in_array(a[i], taxonomy_ids_unique)) {
						taxonomy_ids_unique.push(a[i]);
					}
				}
			}

			//
			jQuery(this)
				.addClass("loading-to-taxonomy")
				.removeClass("each-to-taxonomy");
		}
	});
	//console.log(taxonomy_ids_unique);
	// nếu không có ID nào cẩn xử lý thì bỏ qua đoạn sau luôn
	if (taxonomy_ids_unique.length < 1) {
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
				setTimeout(() => {
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
	jQuery(".loading-to-taxonomy").each(function () {
		let a = jQuery(this).attr("data-id") || "";
		//console.log(a);
		let as = jQuery(this).data("ids") || "";
		//console.log(as);
		//let taxonomy = jQuery(this).attr('data-taxonomy') || '';
		let uri = jQuery(this).data("uri") || "";
		if (uri != "") {
			// thêm term_id nếu không có trong yêu cầu
			if (uri.includes("%term_id%") == false) {
				if (uri.includes("?") == true) {
					uri += "&";
				} else {
					uri += "?";
				}
				uri += "term_id=%term_id%";
			}
		}
		// class riêng cho thẻ A nếu có
		let a_class = jQuery(this).data("class") || "";
		// giãn cách giữa các thẻ A
		let a_space = jQuery(this).data("space") || ", ";

		if (a == "") {
			a = as;
		}

		//
		//if (a != '' && taxonomy != '') {
		if (a != "") {
			a = a.split(",");
			let str = [];
			for (let i = 0; i < a.length; i++) {
				if (a[i] != "") {
					let taxonomy_data = get_taxonomy_data_by_ids(data, a[i] * 1);
					//console.log(taxonomy_data);
					if (taxonomy_data === null) {
						str.push("#" + a[i]);
						continue;
					}
					arr_ajax_taxonomy.push(taxonomy_data);

					//
					let taxonomy_name =
						taxonomy_data.term_shortname != ""
							? taxonomy_data.term_shortname
							: taxonomy_data.name;
					if (uri != "") {
						// thay thế dữ liệu cho uri
						let url = uri;
						for (let x in taxonomy_data) {
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
			jQuery(this).html(str.join(a_space));
		}

		//
		jQuery(this)
			.addClass("loaded-to-taxonomy")
			.removeClass("loading-to-taxonomy");
	});
}

// kiểm tra xem trình duyệt có hỗ trợ định dạng webp không
function support_format_webp() {
	let elem = document.createElement("canvas");

	if (!!(elem.getContext && elem.getContext("2d"))) {
		// was able or not to get WebP representation
		return elem.toDataURL("image/webp").indexOf("data:image/webp") == 0;
	} else {
		// very old browser like IE 8, canvas not supported
		return false;
	}
}

// kiểm tra 1 phần từ có trong 1 mảng hay không
function WGR_in_array(ele, arr) {
	return arr.includes(ele);
	// return arr.indexOf(ele) < 0 ? false : true;
}

function WGR_is_mobile(a) {
	// xem thiết bị có hỗ trợ cảm ứng ko, dựa trên số lượng điểm chạm tối đa
	if ("maxTouchPoints" in navigator) {
		if (navigator.maxTouchPoints > 0) {
			return true;
		}
	}
	// xem thiết bị có hỗ trợ đổi hướng màn hình ko
	else if ("orientation" in window) {
		return true;
	}
	//
	if (screen.width < 775 || jQuery(window).width() < 775) {
		return true;
	}
	//
	if (typeof a == "undefined" || a == "") {
		a = navigator.userAgent;
	}
	//
	if (
		a.includes("Mobile") == true || // Many mobile devices (all iPhone, iPad, etc.)
		a.includes("Android") == true ||
		a.includes("Silk/") == true ||
		a.includes("Kindle") == true ||
		a.includes("BlackBerry") == true ||
		a.includes("Opera Mini") == true ||
		a.includes("Opera Mobi") == true
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
function create_menu_by_taxonomy(arr, parent_class, show_favicon, ops) {
	if (arr.length < 1) {
		console.log("create menu by taxonomy:", arr.length);
		return "";
	}
	if (WGR_config.cf_tester_mode > 0) {
		console.log("create menu by taxonomy:", arr.length);
	}
	// console.log(arr);

	//
	if (typeof show_favicon == "undefined") {
		show_favicon = false;
	}

	//
	if (typeof parent_class == "undefined" || parent_class == "") {
		parent_class = "parent-menu";
	}

	//
	//console.log(typeof ops);
	//console.log(ops);
	if (typeof ops != "object") {
		ops = {};
	}

	//
	// console.log(typeof ops.check_count);
	if (typeof ops.check_count == "undefined") {
		ops.check_count = true;
	}

	//
	let str = "";
	for (let i = 0; i < arr.length; i++) {
		// không hiển thị các danh mục không có bài viết hoặc bị đánh dấu ẩn
		if (arr[i].count * 1 < 1 && ops.check_count === true) {
			continue;
		} else if (arr[i].term_status * 1 > 0) {
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
		let img_favicon = "";
		if (
			show_favicon === true &&
			typeof arr[i].term_favicon != "undefined" &&
			arr[i].term_favicon != ""
		) {
			let ops_width = "",
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
		let sub_menu = "",
			li_class = parent_class,
			a_class = "eb-menu-text";
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
				li_class += " has-sub-menu";
				a_class += " a-sub-menu";
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
			'" class="' +
			a_class +
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
	if (a == "on") {
		return true;
	} else {
		a *= 1;
		if (!isNaN(a) && a > 0) {
			return true;
		}
	}
	return false;
}

// khi muốn nạp nhiều lệnh vue js 1 lúc (ngăn cách bởi dấu ,) -> sử dụng hàm này
function WGR_multi_vuejs(app_id, obj, _callBack, max_i) {
	app_id = app_id.split(",");
	for (let i = 0; i < app_id.length; i++) {
		app_id[i] = jQuery.trim(app_id[i]);

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
		console.log("%c" + "Max loaded Vuejs", "color: red");
		return false;
	}

	//
	if (typeof Vue != "function") {
		setTimeout(() => {
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
	let tzoffset = new Date().getTimezoneOffset() * 60000; // offset in milliseconds
	//console.log('tzoffset:', tzoffset);
	obj.datetime = function (t, len) {
		if (typeof len != "number") {
			len = 19;
		}
		return new Date(t - tzoffset)
			.toISOString()
			.split(".")[0]
			.replace("T", " ")
			.slice(0, len);
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
			.slice(0, len);
	};
	obj.number_format = function (n) {
		return g_func.number_format(n);
	};
	obj.money_format = function (n) {
		return g_func.money_format(n);
	};

	//
	//console.log(obj);
	//console.log(obj.data);
	new Vue({
		el: app_id,
		data: obj,
		mounted: function () {
			jQuery(
				app_id + ".ng-main-content, " + app_id + " .ng-main-content"
			).addClass("loaded");

			//
			if (typeof _callBack == "function") {
				_callBack();
			}

			//console.log(taxonomy_ids_unique);
			//if (obj.action_taxonomy === 1 && taxonomy_ids_unique.length < 1) {
			action_each_to_taxonomy();
			//}
		},
	});
}

function move_custom_code_to() {
	jQuery(".move-custom-code-to")
		.each(function () {
			let data_to = jQuery(this).data("to") || "";
			if (data_to != "") {
				let str = jQuery(this).html() || "";
				jQuery(this).text("");

				//
				let type_move = jQuery(this).data("type") || "";
				if (type_move == "before") {
					jQuery(data_to).before(str);
				} else if (type_move == "after") {
					jQuery(data_to).after(str);
				} else {
					jQuery(data_to).append(str);
				}
				console.log(
					"Move custom code to: " + data_to + " with type:",
					type_move
				);
			} else {
				console.log(
					"%c" + "move-custom-code-to[data-to] not found!",
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
	if (body_class.includes("page404") == true) {
		console.log("%c" + "is 404 page!", "color: red;");
		return false;
	}

	//
	let a = jQuery('link[rel="canonical"]').attr("href") || "";
	//console.log(a);
	if (a != "" && window.location.href.includes(a) == false) {
		if (a.includes("?") == true) {
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
	jQuery(".hide-if-esc").hide();
	jQuery("body").removeClass("no-scroll");

	//
	if (typeof after_hide_if_esc == "function") {
		after_hide_if_esc();
	}

	//
	return false;
}

function WGR_open_poup(str, tit, __callBack) {
	jQuery("#popupModalLabel").html(tit);
	jQuery("#popupModal .modal-body").html(str);
	if (typeof __callBack == "function") {
		__callBack();
	}
	jQuery("#popupModal").modal("show");
}

function WGR_get_params(param, queryString, default_value) {
	if (typeof default_value == "undefined") {
		default_value = "";
	}

	//
	if (typeof queryString == "undefined" || queryString == "") {
		queryString = window.location.search;
	} else {
		queryString = queryString.split("?");
		if (queryString.length > 1) {
			queryString = queryString[1];
		} else {
			return default_value;
		}
	}
	// console.log("queryString:", queryString);
	let urlParams = new URLSearchParams(queryString);
	// console.log("urlParams:", urlParams);
	let a = urlParams.get(param);
	return a === null ? default_value : a;
}

function jQueryAjaxError(jqXHR, textStatus, errorThrown, errorStack) {
	try {
		if (arguments.callee.caller !== null) {
			console.log("Call in: " + arguments.callee.caller.name.toString());
		}
	} catch (e) {
		//
	}
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

// trả về input để vượt qua được captcha -> không có mã này là khỏi submit
// var the_hide_captcha = false;
function get_hide_captcha(a, div_id, the_debug, max_i) {
	if (typeof a != "object") {
		return {};
	}

	//
	if (typeof div_id == "undefined" || div_id == "") {
		div_id = "#hide-captcha";
	}
	if (jQuery(div_id).length < 1) {
		return a;
	}

	//
	// console.log(the_hide_captcha);
	// if (the_hide_captcha === false || jQuery(div_id + " input").length < 1) {
	if (jQuery(div_id + " input").length < 1) {
		if (typeof max_i != "number") {
			max_i = 99;
		} else if (max_i < 0) {
			console.log("max i:", max_i);
			return false;
		}
		setTimeout(() => {
			get_hide_captcha(a, div_id, the_debug, max_i - 1);
		}, 200);
		return false;
	}

	//
	jQuery(div_id + " input").each(function () {
		a[jQuery(this).attr("name")] = jQuery(this).attr("value");
	});

	// thêm tham số này để trả về json thay vì alert
	a["doing_ajax"] = 1;

	//
	if (typeof the_debug != "undefined" && the_debug === true) {
		console.log(a);
	}
	// console.log(div_id, a);

	//
	return a;
}

function WGR_nofollow() {
	let links = document.links;
	for (let i = 0; i < links.length; i++) {
		// console.log(links[i].hostname);
		// console.log(links[i].href);
		if (links[i].hostname == window.location.hostname) {
			continue;
		}
		// console.log(links[i].href);
		links[i].rel = "nofollow";
		links[i].target = "_blank";
	}
}
