/*
var aaaaaaaa = '';
$('#sidebar a').each(function () {
    let hr = $(this).attr('href') || '';
    aaaaaaaa += '\'' + hr + '\' => [ ' + "\n" + ' \'name\' => \'' + $.trim($(this).html()) + '\', ' + "\n" + ' \'arr\' => [] ' + "\n" + ' ],' + "\n";
});
console.log(aaaaaaaa);
*/

/**
 * Chức năng aim menu cho admin
 * https://github.com/kamens/jQuery-menu-aim
 **/
function activateSubmenu(row) {
	let $row = $(row);
	//console.log(1);

	// Keep the currently activated row's highlighted look
	$row.addClass("aiming");
}

//
function deactivateSubmenu(row) {
	let $row = $(row);
	//console.log(0);

	// Hide the submenu and remove the row's highlighted look
	$row.removeClass("aiming");
}

//
(function (arr) {
	let str = "",
		cl = "",
		count_menu = 1;
	for (let x in arr) {
		if (arr[x] === null) {
			continue;
		}
		count_menu++;

		// tạo icon
		if (typeof arr[x].icon == "undefined" || arr[x].icon == "") {
			arr[x].icon = "fa fa-caret-right";
		}
		arr[x].icon = '<i class="' + arr[x].icon + '"></i>';

		// tạo target
		if (typeof arr[x].target == "undefined" || arr[x].target == "") {
			arr[x].target = "_self";
		}

		// tạo tag
		if (typeof arr[x].tag == "undefined") {
			arr[x].tag = "";
		}

		//
		cl = "";
		// chỉnh lại css cho các menu dưới chân admin
		if (arr[x].order * 1 < 50) {
			cl = "last-sidebar-li";
		} else if (arr[x].order * 1 < 70) {
			cl = "middle-sidebar-li";
		}

		//
		str +=
			'<li class="' +
			cl +
			'" style="order: ' +
			arr[x].order +
			'"><a href="' +
			x +
			'" target="' +
			arr[x].target +
			'" data-tag="' +
			arr[x].tag +
			'">' +
			arr[x].icon +
			arr[x].name +
			"</a>";

		//
		//console.log(arr[x]);
		if (arr[x].arr !== null) {
			let str_sub = "",
				v_sub = arr[x].arr;
			for (let k_sub in v_sub) {
				if (v_sub[k_sub] === null) {
					continue;
				}

				// tạo icon
				if (
					typeof v_sub[k_sub].icon == "undefined" ||
					v_sub[k_sub].icon == ""
				) {
					v_sub[k_sub].icon = "fa fa-caret-right";
				}
				v_sub[k_sub].icon = '<i class="' + v_sub[k_sub].icon + '"></i>';

				// tạo target
				if (
					typeof v_sub[k_sub].target == "undefined" ||
					v_sub[k_sub].target == ""
				) {
					v_sub[k_sub].target = "_self";
				}

				// tạo tag
				if (typeof v_sub[k_sub].tag == "undefined") {
					v_sub[k_sub].tag = "";
				}

				//
				str_sub +=
					'<li><a href="' +
					k_sub +
					'" target="' +
					v_sub[k_sub].target +
					'" data-tag="' +
					v_sub[k_sub].tag +
					'">' +
					v_sub[k_sub].icon +
					v_sub[k_sub].name +
					"</a></li>";
			}

			//
			if (str_sub != "") {
				str += '<ul class="sub-menu">' + str_sub + "</ul>";
			}
		}

		//
		str += "</li>";
	}

	//
	$("#sidebar ul").html(str);
	// nếu số lượng menu đủ nhiều
	if (count_menu > 9) {
		// kích hoạt chế độ menu tinh chỉnh menu dưới chân trang
		$("#sidebar .order-admin-menu").addClass("smart-admin-menu");
	}
})(arr_admin_menu);

// khi di chuột vào menu admin -> thêm class để xác định người dùng đang di chuột
$("#sidebar").hover(
	function () {
		$("body").addClass("sidebar-hover");
	},
	function () {
		$("body").removeClass("sidebar-hover");
	}
);

// chỉnh lại chiều cao cho textediter nếu có
$(".auto-ckeditor").each(function () {
	let h = $(this).data("height") || "",
		jd = $(this).attr("id") || "";

	if (h != "" && jd != "") {
		WGR_load_textediter("#" + jd, {
			height: h * 1,
		});
		/*
        CKEDITOR.replace(jd, {
            height: h * 1
        });
        */
	} else {
		console.log(
			"%c" + "auto-ckeditor not has attr data-height or id",
			"color: red;"
		);
	}
});

// tạo breadcrumb theo từng module riêng biệt
if ($(".admin-breadcrumb").length > 0) {
	$("#breadcrumb ul").append($(".admin-breadcrumb").html());

	// sửa lại title cho admin
	(function (str) {
		document.title = str + " | " + document.title;
	})(
		$("#breadcrumb li:last-child a").html() ||
			$("#breadcrumb li:last-child ").html()
	);
}

// tự động checkbox khi có dữ liệu
$('#content input[type="checkbox"]').each(function () {
	let a = $(this).data("value") || "";
	//console.log(a);

	// nếu có tham số này
	if (a != "") {
		let v = $(this).val();

		if (a == v) {
			$(this).prop("checked", true);
		}
	}
});

//
convert_size_to_one_format();
fix_textarea_height();

// bắt đâu tạo actived cho admin menu
(function (w) {
	console.log(w);
	w = w.replace(web_link, "");
	w = w
		.split("&support_tab=")[0]
		.split("?support_tab=")[0]
		.split("&preview_offset_top=")[0]
		.split("?preview_offset_top=")[0];
	console.log(w);

	// tạo segment cho admin menu
	$("#sidebar a").each(function () {
		let a = $(this).attr("href") || "";
		if (a != "") {
			//console.log(a);
			a = a.replace(web_link, "");
			//console.log(a);
			$(this).attr({
				"data-segment": get_last_url_segment(a),
			});
		}
	});

	// so khớp với menu xem có không
	if (set_last_url_segment(get_last_url_segment(w)) === true) {
		return false;
	}
	// thử bỏ dấu ? nếu có
	w = w.split("?")[0];
	if (set_last_url_segment(get_last_url_segment(w)) === true) {
		return false;
	}

	// cắt bớt đi để so khớp tiếp
	for (let i = 0; i < 10; i++) {
		w = remove_last_url_segment(w);
		console.log(w);
		if (w == "" || w == "admin") {
			break;
		}
		if (set_last_url_segment(get_last_url_segment(w)) === true) {
			break;
		}
	}
})(window.location.href);

/*
 * duy trì đăng nhập đối với tài khoản admin (tầm 4 tiếng -> tương ứng với 1 ca làm việc)
 */
//WGR_duy_tri_dang_nhap(4 * 60);
setInterval(() => {
	if ($("body.preview-url").length < 1) {
		document.getElementById("target_eb_iframe").src =
			web_link + "sadmin/sadmin/admin_logged";
	}
}, 10 * 60 * 1000);

/*
 * thay đổi ngôn ngữ trong admin
 */
(function () {
	let str = "";
	for (let x in arr_lang_list) {
		str += '<option value="' + x + '">' + arr_lang_list[x] + "</option>";
	}
	$("#admin-change-language").html(str);
})();
$("#admin-change-language").change(function () {
	let a = $(this).val() || "";

	//
	if (a != "") {
		//console.log(a);

		// tạo url cho phép hiển thị view clone_lang
		let redirect_to = window.location.href;
		redirect_to = redirect_to.split("?clone_lang=")[0].split("&clone_lang=")[0];
		if (redirect_to.includes("?") == true) {
			//redirect_to += "&";
		} else {
			//redirect_to += "?";
		}
		//redirect_to += "clone_lang=1";

		//
		window.location =
			web_link +
			"layout/change_lang?set_lang=" +
			a +
			"&redirect_to=" +
			encodeURIComponent(redirect_to);
	}
});

// xác định scroll để xem người dùng đang cuộn chuột lên hay xuống
setInterval(() => {
	(function (new_scroll_top) {
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
	})(window.scrollY || jQuery(window).scrollTop());
}, 200);

// xác định chiều cao của admin menu và window
var current_admin_window_height = $(window).height();
var current_admin_menu_height = $("#sidebar .order-admin-menu").height();

//
$(document)
	.ready(function () {
		/*
		 * chức năng clone HTML từ các khối thuộc dạng custom -> cho vào khối dùng chung
		 * dùng khi cần hiển thị thêm dữ liệu đối với các loại dữ liệu khác nhau mà vẫn muốn tái sử dụng code mẫu
		 */
		move_custom_code_to();

		// tự động select khi có dữ liệu
		WGR_set_prop_for_select("#content select");
		WGR_set_prop_for_select("select#admin-change-language");

		//
		//$('input[type=checkbox],input[type=radio],input[type=file]').uniform();

		// kích hoạt select2 khi lượng option đủ lớn
		$("select").each(function () {
			if ($("option", this).length > 10 && !$(this).hasClass("has-select2")) {
				$(this).select2();
				$(this).addClass("has-select2");
			}
		});
		//$('.colorpicker').colorpicker();
		//$('.datepicker').datepicker();

		//
		action_each_to_taxonomy();
		action_each_to_email();
		//action_data_img_src();
		action_data_bg_src();
		action_for_check_checked_all();

		// nếu chiều cao menu admin > window thì thêm class xác nhận
		/*
    current_admin_window_height = $(window).height();
    current_admin_menu_height = $('#sidebar .order-admin-menu').height();
    if (current_admin_menu_height > current_admin_window_height) {
        $('body').addClass('sidebar-height');
    }
    */

		//
		$(".text-submit-msg").click(function () {
			$(".text-submit-msg").fadeOut();
		});
		setTimeout(() => {
			$(".text-submit-msg").fadeOut();
		}, 30 * 1000);

		//
		WGR_set_nofollow();

		// tạo menu phụ trợ để tìm kiếm cho tiện
		$("#admin_menu_result").html($("#sidebar").html());
		// không dùng sub-menu -> do khi tìm sub-menu thì parent-menu có thể vẫn đang bị display none
		$("#admin_menu_result .sub-menu").each(function () {
			// chuyển các li ra ngang hàng với menu cha
			$(this).parent().after($(this).html());
			// xóa sub-menu
			$(this).remove();
		});
		// xóa bỏ các thuộc tính không cần thiết
		$("#admin_menu_result ul, #admin_menu_result li")
			.removeAttr("class")
			.removeAttr("style");
		// xóa bỏ icon
		//$("#admin_menu_result i").remove();
		// tạo key tìm kiếm cho li
		$("#admin_menu_result a").each(function () {
			let a = $(this).attr("href") || "";
			a += $(this).data("tag") || "";
			a += $(this).html();
			//console.log(a);
			a = g_func.non_mark_seo(a).replace(/\-/g, "");
			//console.log(a);

			//
			$(this).parent().attr({ "data-key": a });
		});

		//
		create_search_by_label();
		action_admin_menu_search();

		//
		$('a[href="users/logout"], a[href="./users/logout"]')
			.addClass("users-logout")
			.click(function () {
				//console.log(Math.random());
				// đặt tham số này để hủy bỏ chức năng đăng nhập tự động
				localStorage.setItem(
					"remove_rememberme_auto_login",
					window.location.href
				);
				// Xóa auto login qua firebase
				localStorage.removeItem("firebase_auto_login");
				return true;
			});

		//
		console.log("aim menu");
		//
		$("#sidebar ul").addClass("menu-aim");
		$("#sidebar ul ul").removeClass("menu-aim");
		//
		//$("#sidebar li").addClass("menu-li-aim");
		//$("#sidebar li li").removeClass("menu-li-aim");
		//
		$("#sidebar .menu-aim").menuAim({
			activate: activateSubmenu,
			deactivate: deactivateSubmenu,
		});

		//
		_global_js_eb.ebe_currency_format();
		_global_js_eb.ebe_number_format();
	})
	.keydown(function (e) {
		//console.log(e.keyCode);

		//
		if (e.keyCode == 27) {
			hide_if_esc();
		}
	});

// khi người dùng thay đổi kích thước window thì xác nhận lại chiều cao
/*
$(window).resize(function () {
    current_admin_window_height = $(window).height();
    current_admin_menu_height = $('#sidebar .order-admin-menu').height();
    if (current_admin_menu_height > current_admin_window_height) {
        $('body').addClass('sidebar-height');
    } else {
        $('body').removeClass('sidebar-height');
    }
});
*/
