// xác định trình duyệt hỗ trợ webp hay không
var attr_data_webp = "data-webp";

var _global_js_eb = {
	check_email: function (email, alert_true) {
		var re = /^\w+([\-\.]?\w+)*@\w+(\.\w+){1,3}$/;
		if (re.test(email) == true) {
			return true;
		}
		if (alert_true && alert_true == 1) {
			a_lert("Email kh\u00f4ng \u0111\u00fang \u0111\u1ecbnh d\u1ea1ng");
		}
		return false;
	},
	tim_theo_gia: function (id, arr_gia, str_lnk) {},

	//
	sb_token: function (jd) {},

	check_contact_frm: function () {},

	//
	check_profile_frm: function () {},

	check_forgot_pasword_frm: function () {},

	check_login_frm: function () {},

	check_pasword_frm: function () {},

	contact_func: function () {},

	// chuyển kích thước cho bản mobile
	set_mobile_size: function () {},

	auto_margin: function () {
		// tạo attr mặc định để lưu thuộc tính cũ
		jQuery(".img-max-width").each(function () {
			var max_width = jQuery(this).attr("data-max-width") || "";
			//console.log('aaaaaaaaaaa: ' + max_width);
			if (max_width == "" || max_width < 90) {
				max_width =
					jQuery(this).attr("data-width") || jQuery(this).width() || 0;
				max_width = Math.ceil(max_width) - 1;
				jQuery(this).attr({
					"data-max-width": max_width,
				});
			}
			//console.log('bbbbbbbbbbb: ' + max_width);

			// chỉnh lại chiều rộng của thẻ DIV trong khung nội dung (trừ đi padding với border của div)
			jQuery(".wp-caption", this).css({
				"max-width": max_width + "px",
			});

			jQuery("iframe", this).each(function () {
				var a = jQuery(this).attr("src") || "",
					wit =
						jQuery(this).attr("data-width") ||
						jQuery(this).attr("width") ||
						560,
					no_resize = jQuery(this).attr("data-no-resize") || 0;
				if (no_resize * 1 != 1) {
					if (WGR_check_option_on(WGR_config.cf_tester_mode)) console.log(a);

					if (wit > max_width) {
						wit = max_width - 1;
					}
					//				console.log(wit);

					// chỉ xử lý với video youtube
					if (a.split("youtube.com/").length > 1) {
						jQuery(this).attr({
							//'data-height' : jQuery(this).attr('data-height') || jQuery(this).attr('height') || 315,
							"data-width": Math.ceil(wit),
						});
					}
				}
			});

			// thẻ TABLE
			var i = 0;
			jQuery("table", this)
				// fixed chiều rộng tối đa cho table
				.css({
					"max-width": max_width + "px",
				})
				.each(function () {
					var a = jQuery(this).attr("data-no-reponsive") || "";

					//
					if (a == "") {
						jQuery(this).before(
							'<div class="reponsive-for-table reponsive-for-table' +
								i +
								'"></div>'
						);

						jQuery(this)
							.clone()
							.appendTo(".reponsive-for-table" + i);

						jQuery(this).remove();

						i++;
					}
				});

			//
			jQuery(".reponsive-for-table table").attr({
				"data-no-reponsive": 1,
			});
		});

		var avt_max_height = 250,
			//css_m_id = 'css-for-mobile',
			screen_width = jQuery(window).width(),
			current_device = "";

		// nếu có thuộc tính cố định, định dạng cho phiên bản -> lấy theo thuộc tính này
		if (window.location.href.split("&set_device=").length > 1) {
			current_device = window.location.href
				.split("&set_device=")[1]
				.split("&")[0]
				.split("#")[0];
		} else {
			current_device = g_func.getc("click_set_device_style");
		}

		// for mobile
		if (screen_width < 950 && current_device != "desktop") {
			// Điều chỉnh bằng cách dùng chung một chức năng
			jQuery(".fix-li-wit").each(function () {
				var a = jQuery(this).width() || 0,
					w = jQuery(this).attr("data-width") || "",
					w_big = jQuery(this).attr("data-big-width") || "",
					// điều chỉnh chiều rộng cho loại thẻ hoặc class nào -> mặc định là li
					fix_for = jQuery(this).attr("data-tags") || "li";

				//
				if (a > 0 && w != "") {
					// Với màn hình ipad dọc Sử dụng kích thước lớn hơn chút
					if (screen_width > 700 && w_big != "") {
						w = w_big;
					}

					//
					w = Math.ceil(a / w) - 1;
					if (w < 1) {
						w = 1;
					}

					//
					//jQuery(fix_for, this).width((100 / w) + '%');
					jQuery(fix_for, this).css({
						width: ((100 / w).toFixed(6) * 1).toString() + "%",
					});
				}
			});

			//
			jQuery(".img-max-width .wp-caption").width("auto");

			// trên mobile -> giới hạn kích thước media
			jQuery(".img-max-width").each(function () {
				// lấy theo kích thước tối đa của khung này luôn
				var max_width =
					jQuery(this).attr("data-width") || jQuery(this).width() || 250;
				max_width = Math.ceil(max_width) - 1;
				//console.log("max_width: " + max_width);

				// xử lý với video của youtube
				jQuery("iframe", this).each(function () {
					var a = jQuery(this).attr("src") || "";

					// chỉ xử lý với video youtube
					if (a.split("youtube.com/").length > 1) {
						jQuery(this).attr({
							width: max_width,
							height: Math.ceil(max_width * youtube_video_default_size),
						});
					}
				});
			});
		}
		// for PC
		else {
			//jQuery('body').removeClass('style-for-mobile').removeClass('style-for-table').removeClass('style-for-ngang-mobile');

			//
			jQuery(".fix-li-wit").each(function () {
				var fix_for = jQuery(this).attr("data-tags") || "li";

				//
				jQuery(fix_for, this).width("");
			});

			// hình ảnh và clip trên bản pc -> giờ mới xử lý
			jQuery(".img-max-width").each(function () {
				var max_width = jQuery(this).attr("data-max-width") || 250;
				max_width = Math.ceil(max_width) - 1;

				//
				jQuery("iframe", this).each(function () {
					var a = jQuery(this).attr("src") || "";
					var no_resize = jQuery(this).attr("data-no-resize") || 0;

					if (no_resize * 1 != 1) {
						if (WGR_check_option_on(WGR_config.cf_tester_mode)) console.log(a);

						// chỉ xử lý với video youtube
						if (a.split("youtube.com/").length > 1) {
							//console.log('a: ' + a);
							var wit =
								jQuery(this).attr("data-width") ||
								jQuery(this).attr("width") ||
								560;
							//console.log('wit: ' + jQuery(this).attr('width'));
							if (wit.toString().split("%").length > 1) {
								wit = wit.replace(/\%/, "") * 1;
								wit = jQuery(this).width() || 560;
								//console.log('wit%: ' + wit);
							} else if (isNaN(wit)) {
								wit = jQuery(this).width() || 560;
							}
							//console.log('wit1: ' + wit);
							if (wit > max_width) {
								wit = max_width;
							}
							wit -= 1;
							//console.log('wit2: ' + wit);

							//
							jQuery(this).attr({
								width: wit,
								height: Math.ceil(wit * youtube_video_default_size),
							});
						}
					}
				});
			});
		}

		//
		if (typeof pid != "undefined" && pid > 0) {
			var wit_mb = jQuery(".thread-details-mobileAvt").width(),
				hai_mb = wit_mb,
				li_len = jQuery(".thread-details-mobileAvt li").length,
				li_wit = 100 / li_len;

			jQuery(".thread-details-mobileAvt ul").width(wit_mb * li_len);
			jQuery(".thread-details-mobileAvt li").width(li_wit + "%");
		}

		//
		jQuery(".no-set-width-this-li").width("100%");

		// chỉnh kích cỡ ảnh theo tỉ lệ
		var new_arr_ti_le_global = {};
		jQuery(".ti-le-global").each(function () {
			var a = jQuery(this).width(),
				// hiển thị size ảnh gợi ý cho admin
				show_height = 0,
				// tỉ lệ kích thước giữa chiều cao và rộng (nếu có), mặc định là 1x1
				// -> nhập vào là: chiều cao/ chiều rộng
				new_size = jQuery(this).attr("data-size") || "";

			// với size auto -> set thẳng ảnh vào thay vì background
			if (new_size == "auto") {
				var img = jQuery(this).attr("data-img") || "";
				if (img != "") {
					jQuery(this)
						.after(
							'<div class="eb-blog-avt auto-size"><img src="' +
								img +
								'" width="' +
								a +
								'" /></div>'
						)
						.remove();
				}
			} else if (new_size == "full") {
				a = jQuery(window).height();
				//				console.log(a);

				//
				jQuery(this).css({
					"line-height": a + "px",
					height: a + "px",
				});
			} else {
				var pading_size = "ty-le-h100";
				show_height = a;
				// Tính toán chiều cao mới dựa trên chiều rộng
				if (new_size != "") {
					if (
						new_size.split("x").length > 1 ||
						new_size.split("*").length > 1
					) {
						new_size.split("x").split("*");
						new_size = new_size[1] + "/" + new_size[0];
					}
					pading_size = "ty-le-h" + new_size.replace(/\/|\./gi, "_");

					// v2 -> tính padding theo chiều rộng
					a = eval(new_size);
					show_height *= a;
				}
				// mặc định thì cho = 1 -> 100%
				else {
					a = 1;
				}

				if (typeof arr_ti_le_global[pading_size] == "undefined") {
					arr_ti_le_global[pading_size] = a;
					new_arr_ti_le_global[pading_size] = a;
				}

				// 1 số trường hợp vẫn dùng class cũ
				if ($(this).hasClass("thread-details-mobileAvt")) {
					jQuery(this).css({
						"line-height": show_height + "px",
						height: show_height + "px",
					});
				}
				// còn lại sẽ cho class mới
				else {
					jQuery(this)
						.addClass(pading_size)
						.addClass("ty-le-global")
						.removeClass("ti-le-global")
						.attr({
							"data-show-height": show_height,
						});
				}
			}
		});
		//console.log(arr_ti_le_global);
		//console.log(new_arr_ti_le_global);
		var str_css = "";
		for (var x in new_arr_ti_le_global) {
			new_arr_ti_le_global[x] *= 100;

			// quy đổi padding teo % chiều rộng của width
			str_css +=
				"." +
				x +
				"{padding-top:" +
				new_arr_ti_le_global[x].toFixed(3) * 1 +
				"%}";
		}
		if (str_css != "") {
			if (WGR_config.cf_tester_mode > 0) {
				console.log("ty-le-global padding CSS: " + str_css);
			}
			$("head").append("<style>" + str_css + "</style>");
		}
	},

	big_banner: function () {},

	money_format_keyup: function () {
		jQuery(".change-tranto-money-format")
			.off("keyup")
			.off("change")
			.keyup(function (e) {
				var k = e.keyCode,
					a = jQuery(this).val() || "";
				if (
					(k >= 48 && k <= 57) ||
					(k >= 96 && k <= 105) ||
					k == 8 ||
					k == 46
				) {
					a = g_func.formatCurrency(a);
					if (a == 0 || a == "0") {
						jQuery(this).val(a).select();
					} else {
						jQuery(this).val(a).focus();
					}
				}
			})
			.change(function () {
				jQuery(this).val(g_func.formatCurrency(jQuery(this).val()));
			});
	},

	_log_click_ref: function () {},

	ebBgLazzyLoadOffset: function (i) {
		//console.log( 'each-to-bgimg offset' );

		if (typeof i != "number") {
			i = 5;
		}

		jQuery(".each-to-bgimg").each(function () {
			a = jQuery(this).attr({
				"data-offset": jQuery(this).offset().top,
			});
		});

		if (i > 0) {
			setTimeout(function () {
				_global_js_eb.ebBgLazzyLoadOffset(i - 1);
			}, 2000);
		}
	},

	ebBgLazzyLoad: function (lazzy_show) {
		var eb_lazzy_class = "eb-lazzy-effect",
			eb_lazzy_iframe = "eb-add-iframe",
			a = 0,
			wh = jQuery(window).width(),
			c = "";

		//
		if (typeof lazzy_show == "number" && lazzy_show > 0) {
			//console.log(lazzy_show);
			//console.log(disable_eblazzy_load);
			//console.log('eb_lazzy_class length: ' + jQuery('.' + eb_lazzy_class).length);

			// Nếu ko đủ class để làm việc -> thoát luôn
			if (disable_eblazzy_load == true) {
				//disable_eblazzy_load = true;
				return false;
			} else if (
				jQuery("." + eb_lazzy_class).length == 0 &&
				jQuery("." + eb_lazzy_iframe).length == 0
			) {
				disable_eblazzy_load = true;
				return false;
			}

			// load trước các ảnh ngoài màn hình, để lát khách kéo xuống có thể xem được luôn
			lazzy_show += height_for_lazzy_load;
			//console.log( height_for_lazzy_load );

			//
			jQuery("." + eb_lazzy_class).each(function () {
				a = jQuery(this).offset().top || 0;

				if (a < lazzy_show) {
					var wit = jQuery(this).width() || 300;
					//console.log('width:', wit);
					if (wit > 1024) {
						var img = jQuery(this).attr("data-img") || "";
					} else if (wit > 360) {
						var img =
							jQuery(this).attr("data-large-img") ||
							jQuery(this).attr(attr_data_webp) ||
							jQuery(this).attr("data-img") ||
							"";
					} else {
						var img =
							jQuery(this).attr(attr_data_webp) ||
							jQuery(this).attr("data-img") ||
							"";
					}

					//
					if (img != "") {
						jQuery(this).css({
							"background-image": "url('" + img + "')",
						});
					}

					//
					jQuery(this)
						.removeClass(eb_lazzy_class)
						.addClass("lazyload-img-done");
				}
			});

			//
			jQuery("." + eb_lazzy_iframe).each(function () {
				a = jQuery(this).offset().top || 0;
				if (a < lazzy_show) {
					c = jQuery(this).attr("data-iframe") || "";
					//console.log(c);
					if (c != "") {
						//console.log(c);
						c = unescape(c);
						//console.log(c);
						jQuery(this).html(c);

						//
						jQuery(this)
							.removeClass(eb_lazzy_iframe)
							.addClass("lazyload-iframe-done");
					}
				}
			});
		} else {
			jQuery(".each-to-bgimg").addClass(eb_lazzy_class);
			jQuery(".url-to-google-map").addClass(eb_lazzy_iframe);

			_global_js_eb.ebBgLazzyLoad(jQuery(window).height() * 1.5);
		}
	},

	// nạp slider của flatsome
	loadFlatsomeSlider: function (flickity_options, add_class) {
		$(".ebwidget-run-slider .eb-blog").each(function () {
			var has_attr = $(this).attr("data-flickity-options") || "";

			//
			if (has_attr == "" && !$(this).hasClass("actived-slider")) {
				//
				if (typeof flickity_options != "object") {
					flickity_options = {
						cellAlign: "left",
						//cellAlign: "center",
						imagesLoaded: true,
						lazyLoad: 1,
						freeScroll: false,
						// wrapAround true -> cho phép slider chạy vòng tròn
						wrapAround: true,
						autoPlay: 6000,
						pauseAutoPlayOnHover: true,
						prevNextButtons: true,
						contain: true,
						adaptiveHeight: true,
						dragThreshold: 10,
						percentPosition: true,
						pageDots: true,
						rightToLeft: false,
						draggable: true,
						selectedAttraction: 0.1,
						parallax: 0,
						friction: 0.6,
					};
				}

				//
				if (typeof add_class != "object" || add_class.length == 0) {
					add_class = [
						//"row",
						//"row-collapse",
						//"row-small",
						//"row-full-width",
						//"align-equal",
						"slider",
						"actived-slider",
						"row-slider",
						"slider-nav-circle",
						//'slider-nav-large',
						"slider-nav-light",
						//'slider-style-normal',
						//'is-draggable',
						//'sliflickity-enabledder',
					];
					//console.log(add_class);
				}

				//
				$(this)
					.attr({
						"data-flickity-options": JSON.stringify(flickity_options),
					})
					.addClass(add_class.join(" "));
			}
		});
	},

	fix_url_id: function () {},

	cart_agent: function () {},

	_c_link: function (id, seo, name) {},

	youtube_id: function (a, start_end) {
		if (a.split("youtube.com").length > 1 || a.split("youtu.be").length > 1) {
			// lấy thời gian bắt đầu, kết thúc nếu có
			var s = "",
				e = "";
			if (typeof start_end != "undefined") {
				start_end = a.replace(/\?/g, "&");
				//				console.log( start_end );

				//
				s = start_end.split("&start=");
				if (s.length > 1) {
					s = "&start=" + s[1].split("&")[0];
				} else {
					s = "";
				}

				//
				e = start_end.split("&end=");
				if (e.length > 1) {
					e = "&end=" + e[1].split("&")[0];
				} else {
					e = "";
				}
			}

			//
			var youtube_parser = function (url) {
				var regExp =
					/^.*((youtu.be\/)|(v\/)|(\/u\/\w\/)|(embed\/)|(watch\?))\??v?=?([^#\&\?]*).*/;

				var match = url.match(regExp);

				return match && match[7].length == 11 ? match[7] : false;
			};

			return youtube_parser(a) + s + e;
		}

		//
		return "";
	},

	user_img_loc: function (wit, hai) {},

	user_string_loc: function () {},

	user_loc: function (real_time, after_load) {},

	// tự động lấy vị trí tương đối của người dùng mà không cần xin phép
	user_auto_loc: function (after_load) {},

	demo_html: function (clat, len) {},

	page404_func: function () {},

	cart_create_arr_poruduct: function (cart_total_price) {},

	cart_func: function () {},

	cart_discount_code: function (co_ma_giam_gia, cl) {},

	// load size và color trong giỏ hàng
	cart_user_select_size_color: function () {},
	cart_size_in_color: function (arr_user_select_size_color) {},
	cart_size_color: function () {},

	// tính toán lại giá trị giỏ hàng mỗi lần đổi số lượng, size, color
	cart_calculator: function () {},

	check_null_cart: function () {},

	check_size_color_cart: function () {},

	check_cart: function () {},

	cart_add_item_v2: function (new_cart_id, action_obj) {},

	cart_add_item: function (new_cart_id, action_obj) {},

	cart_remove_item: function (remove_id, tr_id) {},

	cpl_cart: function (my_hd_id, my_hd_mahoadon, my_message) {},

	// nạp thông tin khách hàng cho giỏ hàng -> từ cookies
	cart_customer_cache: function (f) {},

	// google tracking
	// https://support.google.com/adwords/answer/6331304?&hl=vi
	gg_track: function (url) {
		if (
			typeof url == "undefined" ||
			//		|| typeof goog_report_conversion != 'function'
			url == ""
		) {
			return false;
		}
		console.log(
			"Google tracking (" + url + ") by " + private_info_setting_site_upper
		);

		//
		if (typeof goog_report_conversion == "function") {
			goog_report_conversion(url);
		}

		//
		return true;
	},

	// google analytics tracking
	// https://developers.google.com/analytics/devguides/collection/analyticsjs/events
	// https://developers.google.com/analytics/devguides/collection/gtagjs/events
	// https://developers.google.com/analytics/devguides/collection/gtagjs/enhanced-ecommerce
	ga_event_track: function (
		eventCategory,
		eventAction,
		eventLabel,
		ops,
		max_for
	) {
		// không track đối với người dùng đã đăng nhập
		if (
			WGR_config.current_user_id > 0 &&
			WGR_check_option_on(cf_disable_tracking)
		) {
			console.log("ga_event_track disable by user login");
			return false;
		}

		//
		/*
        if ( typeof goog_report_conversion == 'undefined' ) {
            return false;
        }
        */

		// Mảng dành cho các option nâng cao khác
		if (typeof ops != "object") {
			ops = {};
		}

		if (typeof eventCategory == "undefined" || eventCategory == "") {
			if (typeof ops["action"] != "undefined" && ops["action"] != "") {
				eventCategory = ops["action"];
			} else {
				eventCategory = "Null Category";
			}
		}

		if (typeof eventAction == "undefined" || eventAction == "") {
			if (typeof ops["action"] != "undefined" && ops["action"] != "") {
				eventAction = ops["action"];
			} else {
				eventAction = "Null Action";
			}
		}

		if (typeof eventLabel == "undefined" || eventLabel == "") {
			eventLabel = document.title;
		}

		// ưu tiên gtag
		if (WGR_check_option_on(cf_gtag_id) && typeof gtag == "function") {
			if (typeof ops["action"] == "undefined") {
				ops["action"] = eventAction;
			}
			if (typeof ops["category"] == "undefined") {
				ops["category"] = eventCategory + " (EBv2)";
			}
			if (typeof ops["label"] == "undefined") {
				ops["label"] = eventLabel;
			}
			console.log(ops);

			//
			var para = {
				event_category: ops["category"],
				event_label: ops["label"],
			};

			//
			if (typeof ops["items"] == "undefined") {
				para["items"] = ops["items"];
			}
			console.log(para);

			//
			gtag("event", ops["action"], para);
			console.log(
				"Google analytics (gtag) event tracking (" +
					eventAction +
					") by " +
					private_info_setting_site_upper
			);
		}
		// rồi đến ga
		else if (typeof ga == "function") {
			ga("send", "event", eventCategory + " (EB)", eventAction, eventLabel);
			console.log(
				"Google analytics event tracking (" +
					eventAction +
					") by " +
					private_info_setting_site_upper
			);
		} else {
			//		if ( typeof ga != 'function' ) {
			if (typeof max_for == "undefined") {
				max_for = 20;
			}

			// nạp lại track này lần nữa (do fbq thường load chậm hơn website)
			if (max_for > 0) {
				// từ lần lặp cuối, cho phép tracking qua cả gtag
				if (max_for < 5) {
					cf_gtag_id = 1;
				}

				//
				setTimeout(function () {
					_global_js_eb.ga_event_track(
						eventCategory,
						eventAction,
						eventLabel,
						ops,
						max_for - 1
					);
				}, 500);
				//				console.log( 'Re-load GG tracking (' + max_for + ')...' );

				return false;
			}

			//
			console.log("Max for GG track: " + max_for);
			return false;
		}

		//
		return true;
	},

	// facebook dynamic remarketing
	// https://developers.facebook.com/docs/marketing-api/facebook-pixel/v2.8
	// https://developers.facebook.com/docs/facebook-pixel/implementation/marketing-api
	fb_track: function (track_name, track_arr, max_for) {
		//
		//		console.log('aaaaaaaaa');

		if (WGR_check_option_on(cf_facebook_tracking) == false) {
			console.log("fb_track has been disable!");
			return false;
		}

		// Không chạy trong iframe
		if (top != self) {
			console.log("fb_track not run in iframe");
			return false;
		}

		// không track đối với người dùng đã đăng nhập
		if (
			WGR_config.current_user_id > 0 &&
			WGR_check_option_on(cf_disable_tracking)
		) {
			console.log("fb_track disable by user login");
			return false;
		}

		// không có tên sự kiện cũng thoát
		if (typeof track_name == "undefined" || track_name == "") {
			console.log("track_name not found");
			return false;
		}

		//
		if (typeof track_arr != "object") {
			track_arr = {};
		} else {
			// mặc định type = product
			if (
				typeof track_arr.content_type == "undefined" ||
				track_arr.content_type == ""
			) {
				track_arr.content_type = "product";
			}
		}

		// nếu fb chưa được nạp -> thử kiểm tra và chờ load lại
		if (typeof fbq == "undefined") {
			if (typeof max_for == "undefined") {
				max_for = 60;
			}

			// nạp lại track này lần nữa (do fbq thường load chậm hơn website)
			if (max_for > 0) {
				setTimeout(function () {
					_global_js_eb.fb_track(track_name, track_arr, max_for - 1);
				}, 500);
				console.log("Re-load FB tracking (" + max_for + ")...");

				return false;
			}

			//
			console.log("Max for FB track: " + max_for);
			return false;
		}

		// kiểm tra độ chuẩn của track
		if (
			(function (a) {
				a = a.toLowerCase();

				if (a == "purchase") {
					//if (track_arr['content_type'] == 'undefined' || track_arr['content_ids'] == 'undefined' || track_arr['content_ids'].length == 0) {
					if (
						track_arr["content_type"] == "undefined" ||
						track_arr["contents"] == "undefined" ||
						track_arr["contents"].length == 0
					) {
						return false;
					}
				} else if (a == "addtocart") {
					if (
						track_arr["content_type"] == "undefined" ||
						track_arr["content_ids"] == "undefined" ||
						track_arr["content_ids"].length == 0
					) {
						return false;
					}
				} else if (a == "viewcontent") {
					if (track_arr["content_type"] == "undefined") {
						return false;
					}
				}

				//
				return true;
			})(track_name) == false
		) {
			console.log(
				"Facebook pixel tracking (" +
					track_name +
					") disable by parameter is NULL"
			);
			console.log(track_arr);
			return false;
		}

		//
		fbq("track", track_name, track_arr);

		//
		console.log(
			"%c Facebook pixel tracking (" +
				track_name +
				") by " +
				private_info_setting_site_upper,
			"color: green;"
		);
		console.log(track_arr);

		//
		return true;
	},

	// lưu log phiên làm việc vào ga luôn
	ga_event_log: function (eventCategory, eventAction, eventLabel) {
		if (typeof eventAction == "undefined" || eventAction == "") {
			eventAction = "staff";
			if (typeof mtv_id != "undefined") {
				eventAction += mtv_id;
			} else if (typeof WGR_config.current_user_id != "undefined") {
				eventAction += WGR_config.current_user_id;
			}
		}

		//
		try {
			_global_js_eb.ga_event_track(eventCategory, eventAction, eventLabel);
		} catch (e) {
			console.log(WGR_show_try_catch_err(e));
		}
	},

	/*
	 * Nạp iframe để submit
	 */
	add_primari_iframe: function () {
		if (document.getElementById("target_eb_iframe") == null) {
			jQuery("body").append(
				'<iframe id="target_eb_iframe" name="target_eb_iframe" title="EB iframe" src="about:blank" width="99%" height="555">AJAX form</iframe>'
			);
		}

		return true;
	},

	// thêm mã xác nhận mỗi khi submit form
	wgr_nonce: function (form_name) {
		var a = jQuery('form[name="' + form_name + '"]');
		if (a.length == 0) {
			return false;
		}

		// xác định nới request tới
		if (
			jQuery('form[name="' + form_name + '"] input[name="__wgr_request_from"]')
				.length == 0
		) {
			a.append(
				'<input type="hidden" name="__wgr_request_from" value="' +
					window.location.href +
					'" />'
			);
		}
		// thời gian truy cập form
		if (
			jQuery('form[name="' + form_name + '"] input[name="__wgr_nonce"]')
				.length == 0
		) {
			a.append(
				'<input type="hidden" name="__wgr_nonce" value="' +
					Math.ceil(Date.now() / 1000) +
					'" />'
			);
		}
		// thêm tham số xác định target của form để đưa ra phương thức xử lý code phù hợp
		if (
			jQuery('form[name="' + form_name + '"] input[name="__wgr_target"]')
				.length == 0
		) {
			a.append(
				'<input type="hidden" name="__wgr_target" value="' +
					(a.attr("target") || "") +
					'" />'
			);
		}

		return true;
	},
	check_register: function (f) {},
	check_quick_register: function (form_name) {},

	// giả lập GET của PHP
	_get: function (p) {},

	ebe_currency_format: function () {
		// hỗ trợ chuyển đổi đơn vị tiền tệ nếu to quá
		var mot_ty = 1000000000;
		var mot_trieu = 1000000;
		var conver_to_trieu = false;
		if (
			typeof cf_big_price_before != "undefined" &&
			WGR_check_option_on(cf_big_price_before)
		) {
			conver_to_trieu = true;
		}
		jQuery(".ebe-currency-format")
			.each(function () {
				var a = jQuery.trim(
					jQuery(this).attr("data-num") || jQuery(this).html() || ""
				);

				//if (a != '' && a != '0') {
				if (a != "") {
					a *= 1;
					if (a > 0) {
						var b = 0;

						//
						if (conver_to_trieu == true) {
							// nếu lớn hơn 1 tỷ -> tính theo đơn vị tỷ
							if (a > mot_ty) {
								// làm tròn theo đơn vị tỷ
								if (a % mot_ty == 0) {
									a = a / mot_ty;
									jQuery(this).addClass("convert-to-ty");
								}
								// làm tròn theo đơn vị triệu
								else if (a % mot_trieu == 0) {
									a = a / mot_trieu;
									jQuery(this).addClass("convert-to-trieu");

									// gán b để chuyển đổi sang tỷ
									b = a;
								}
							} else if (a > mot_trieu) {
								// làm tròn theo đơn vị triệu
								if (a % mot_trieu == 0) {
									a = a / mot_trieu;
									jQuery(this).addClass("convert-to-trieu");
								}
							}
						}

						// trường hợp số tiền > mot_ty và không tròn số
						if (b > 0) {
							// tính phần tỷ
							a = b - (b % 1000);
							a = a / 1000;

							// tính phần triệu
							b = b % 1000;

							// in ra
							jQuery(this).html(
								'<span class="ebe-currency convert-to-ty">' +
									g_func.money_format(a) +
									"</span> " +
									b
							);
						}
						// còn lại sẽ in bình thường
						else {
							jQuery(this).html(g_func.money_format(a));
						}
					}
				}
			})
			.removeClass("ebe-currency-format")
			.addClass("ebe-currency");
	},
};

//
//var ___eb_for_wp = _global_js_eb.add_primari_iframe;
