//
//WGR_set_prop_for_select('#content select');

// kiểm tra xem nếu có session sau logout thì chuyển tới đây
/*
(function (a) {
    if (a !== null) {
        sessionStorage.removeItem('logout_redirect');
        window.location = a;
    }
})(sessionStorage.getItem('logout_redirect'));
*/

//
WGR_nofollow();

// xóa href cho các thẻ a không sử dụng
jQuery('a[href="#"], a[href="javascript:;"], a[href=""]')
	.click(function () {
		return false;
	})
	.addClass("noreferrer-noopener")
	//.removeAttr("href")
	.attr({
		href: web_link,
		rel: "noreferrer noopener",
		// "aria-hidden": "true",
	});

// khi bấm nút đăng xuất
jQuery('a[href="users/logout"], a[href="./users/logout"]')
	.addClass("users-logout")
	.click(function () {
		//sessionStorage.setItem('logout_redirect', window.location.href);
		let a =
			jQuery(this).data("title") ||
			jQuery(this).attr("title") ||
			"Xác nhận Đăng xuất khỏi tài khoản!";
		let result = confirm(a);
		//console.log(result);

		if (result === true) {
			// đặt tham số này để hủy bỏ chức năng đăng nhập tự động
			localStorage.setItem(
				"remove_rememberme_auto_login",
				window.location.href
			);
			// Xóa auto login qua firebase
			localStorage.removeItem("firebase_auto_login");
		}
		return result;
	});

//
jQuery("a").each(function () {
	let a = jQuery(this).attr("href") || "";
	if (a.slice(0, 1) == "#") {
		jQuery(this)
			// .addClass("noreferrer-noopener")
			.attr({
				href: window.location.href.split("#")[0] + a,
			})
			.click(function () {
				setTimeout(() => {
					WGR_auto_scroll_by_hash();
				}, 600);
			});
	}
});

//
// jQuery("a.noreferrer-noopener")
// 	.click(function () {
// 		return false;
// 	})
// 	.attr({
// 		rel: "noreferrer noopener",
// 		// "aria-hidden": "true",
// 	});

/**
 * tạo hiệu ứng selected cho các thẻ a
 */
function action_active_menu_item() {
	let a = window.location.href;

	//
	jQuery('a[href="' + a + '"]').addClass("active-menu-item");
	if (WGR_config.cf_tester_mode > 0) console.log(a);

	//
	let base_url = jQuery("base").attr("href") || "";
	if (base_url != "") {
		a = a.replace(base_url, "").split("/page/")[0];
		if (WGR_config.cf_tester_mode > 0) console.log(a);
		jQuery('a[href="' + a + '"], a[href="./' + a + '"]').addClass(
			"active-menu-item"
		);
	}

	// với link trang chủ -> chỉnh url theo ngôn ngữ đang xem
	if (WGR_config.site_lang_sub_dir > 0) {
		let data_lang = jQuery("html").data("lang") || "";
		//console.log(data_lang);
		let data_default_lang = jQuery("html").data("default-lang") || "";
		//console.log(data_default_lang);
		if (
			data_lang != "" &&
			data_default_lang != "" &&
			data_lang != data_default_lang
		) {
			jQuery('a[href="./"], a[href="' + web_link + '"]').attr({
				href: "./" + data_lang + "/",
			});
		}
	}

	// tạo active cho li con
	jQuery(".sub-menu a.active-menu-item")
		.addClass("active")
		.parent("li")
		.addClass("current-menu-item");

	// tạo active cho li cha
	jQuery("ul li.current-menu-item")
		.addClass("active")
		.parent("ul")
		.parent("li")
		.addClass("current-menu-parent");

	// tạo active cho li ông
	jQuery("ul li.current-menu-parent")
		.addClass("active")
		.parent("ul")
		.parent("li")
		.addClass("current-menu-grand")
		.addClass("active");
}

// hỗ trợ scroll để vị trí chỉ định trên hash của url -> do trình duyệt cũng auto scroll nhưng thường bị khuất nếu có fixed menu
function WGR_auto_scroll_by_hash(repeat) {
	let a = window.location.hash;
	if (a == "") {
		return false;
	}

	//
	// if (jQuery(a).length < 1) {
	if (document.getElementById(a.slice(1)) === null) {
		return false;
	}
	console.log("Auto scrol to:", a);

	//
	jQuery("body,html").scrollTop(
		jQuery(a).offset().top - (jQuery("#wgr__top").height() || 90)
	);

	//
	/*
	if (typeof repeat == "undefined" || repeat === true) {
		setTimeout(() => {
			WGR_auto_scroll_by_hash(false);
		}, 600);
	}
	*/

	//
	return true;
}

// nếu trình duyệt không hỗ trợ định dạng webp -> xóa bỏ định dạng webp nếu có
if (support_format_webp() !== true) {
	attr_data_webp = "data-img";
	//jQuery('.each-to-bgimg').removeAttr('data-webp');
}

// hiển thị trước hình ảnh cho màn hình đầu tiên
_global_js_eb.ebBgLazzyLoad();
_global_js_eb.loadFlatsomeSlider();
_global_js_eb.auto_margin();

// khi document đã load xong
jQuery(document)
	.ready(function () {
		// move_custom_code_to();
		action_each_to_taxonomy();
		action_active_menu_item();

		// chiều cao của document đủ lớn
		/*
    if (jQuery(document).height() > jQuery(window).height() * 1.5) {
    }
    */
		setInterval(() => {
			WGR_show_or_hide_to_top();
		}, 250);

		//
		if (height_for_lazzy_load < 1) {
			height_for_lazzy_load = jQuery(window).height();
		}
		// console.log("height_for_lazzy_load", height_for_lazzy_load);
		_global_js_eb.ebBgLazzyLoad(height_for_lazzy_load);

		//
		_global_js_eb.ebe_currency_format();
		_global_js_eb.ebe_number_format();

		//
		if (typeof sync_ajax_post_term == "function") {
			sync_ajax_post_term();
		}

		//
		// WGR_nofollow();

		//
		WGR_auto_scroll_by_hash();

		//
		(function () {
			let mh = 0;
			jQuery(".align-custom-equal").each(function () {
				mh = 0;
				let h = 0;
				jQuery(".col-inner", this).each(function () {
					h = jQuery(this).height();
					// console.log("h:", h);
					if (h > mh) {
						mh = h;
					}
				});
				if (mh > 0) {
					// console.log("mh:", mh, mh.toFixed(2) * 1);
					jQuery(".col-inner", this).css({
						"min-height": mh.toFixed(2) * 1 + "px",
						// "min-height": "400px",
						// border: "1px #f00 solid",
					});
				}
			});
		})();

		//
		jQuery("body").addClass("document-ready");
	})
	.keydown(function (e) {
		//console.log(e.keyCode);

		//
		if (e.keyCode == 27) {
			hide_if_esc();
		}
	});

//
jQuery(window)
	.resize(function () {
		height_for_lazzy_load = jQuery(window).height();
		_global_js_eb.auto_margin();
	})
	.on("load", function () {
		WGR_auto_scroll_by_hash();
	});

//
jQuery("#oi_scroll_top, .oi_scroll_top").click(function () {
	window.scroll(0, 0);
	/*
	jQuery("body,html").animate(
		{
			scrollTop: 0,
		},
		500
	);
	*/
});

// duy trì trạng thái đăng nhập
//WGR_duy_tri_dang_nhap();
