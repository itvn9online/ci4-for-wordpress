//
var admin_menu_show = false;

// khi người dùng bấm nút lên trong ô tìm kiếm admin menu
function admin_prev_search_menu() {
	var len = $("#admin_menu_result li[data-show=1]").length;
	if (len < 1) {
		return false;
	}

	//
	var default_select =
			"#admin_menu_result li[data-show=1]:last a, #admin_menu_result li[data-show=1]:last label",
		for_class =
			"#admin_menu_result li[data-show=1] a, #admin_menu_result li[data-show=1] label";

	// xác định thẻ a.up-down đang được đánh dấu
	// -> nếu có rồi
	if (
		$("#admin_menu_result li[data-show=1] a.up-down").length < 1 &&
		$("#admin_menu_result li[data-show=1] label.up-down").length < 1
	) {
		// nếu chưa có -> đánh dấu cái cuối
		//console.log(0);
		return $(default_select).addClass("up-down");
	}
	//console.log(1);

	// nếu có nhiều hơn 1 link
	if (len > 1) {
		var get_href = "";
		$(for_class).each(function () {
			if ($(this).hasClass("up-down")) {
				//console.log($(this).attr("href"));
				return false;
			}
			get_href = $(this).attr("href") || $(this).attr("for") || "";
		});
		//console.log("get_href:", get_href);

		//
		after_set_up_down_class(get_href, default_select);
	} else {
		// còn lại chỉ cần tích 1 cái là được
		$(for_class).addClass("up-down");
	}
}

// khi người dùng bấm nút xuống trong ô tìm kiếm admin menu
function admin_next_search_menu() {
	var len = $("#admin_menu_result li[data-show=1]").length;
	if (len < 1) {
		return false;
	}

	//
	var default_select =
			"#admin_menu_result li[data-show=1]:first a, #admin_menu_result li[data-show=1]:first label",
		for_class =
			"#admin_menu_result li[data-show=1] a, #admin_menu_result li[data-show=1] label";

	// xác định thẻ a.up-down đang được đánh dấu
	// -> nếu có rồi
	if (
		$("#admin_menu_result li[data-show=1] a.up-down").length < 1 &&
		$("#admin_menu_result li[data-show=1] label.up-down").length < 1
	) {
		// nếu chưa có -> đánh dấu cái cuối
		//console.log(0);
		return $(default_select).addClass("up-down");
	}
	//console.log(1);

	// nếu có nhiều hơn 1 link
	if (len > 1) {
		var get_href = "";
		var has_class = false;
		$(for_class).each(function () {
			if (has_class === true) {
				get_href = $(this).attr("href") || $(this).attr("for") || "";
				return false;
			}

			//
			if ($(this).hasClass("up-down")) {
				//console.log($(this).attr("href"));
				has_class = true;
			}
		});
		//console.log("get_href:", get_href);

		//
		after_set_up_down_class(get_href, default_select);
	} else {
		// còn lại chỉ cần tích 1 cái là được
		$(for_class).addClass("up-down");
	}
}

// xác khi xác định được menu cần trỏ tới thì thực hiện tại đổi class
function after_set_up_down_class(get_href, default_select) {
	$("#admin_menu_result a, #admin_menu_result label").removeClass("up-down");
	if (get_href == "") {
		//console.log(0);
		$(default_select).addClass("up-down");
	} else {
		//console.log(1);
		$(
			"#admin_menu_result a[href='" +
				get_href +
				"'], #admin_menu_result label[for='" +
				get_href +
				"']"
		).addClass("up-down");
	}
}

// khi người dùng bấm nút enter trong ô tìm kiếm admin menu
function admin_enter_search_menu() {
	var get_href = $("#admin_menu_result a.up-down").attr("href") || "";
	//console.log(get_href);
	if (get_href != "") {
		if (get_href.split("//").length === 1) {
			//console.log(get_href.substr(0, 1));
			if (get_href.substr(0, 1) == "/") {
				get_href = get_href.substr(1);
			}
			get_href = web_link + get_href;
		}
		console.log("Go to URL: " + get_href);
		window.location = get_href;
	}
	// thử theo label
	else if ($("#admin_menu_result label.up-down").length > 0) {
		console.log("Click to label.up-down");
		$("#admin_menu_result label.up-down").trigger("click");
	}
}

function action_admin_menu_search() {
	// khi người dùng gõ tìm kiếm
	$("#admin_menu_search")
		.focus(function (e) {
			admin_menu_show = true;
			$(".admin-menu-result").show();
		})
		.focusout(function (e) {
			setTimeout(function () {
				admin_menu_show = false;
				$(".admin-menu-result").hide();
			}, 200);
		})
		.keyup(function (e) {
			//console.log(e.keyCode);

			// bấm nút xuống
			if (e.keyCode == 40) {
				admin_next_search_menu();
			}
			// bấm nút lên
			else if (e.keyCode == 38) {
				admin_prev_search_menu();
			}
			// bấm enter
			else if (e.keyCode == 13) {
				admin_enter_search_menu();
			}
			// các ký tự khác
			else {
				var k = $(this).val();
				$(".admin-menu-key").text(k);
				k = g_func.non_mark_seo(k).replace(/\-/g, "");
				//console.log(k);

				//
				if (k.length > 2) {
					// nếu autofocus được thiết lập thì dễ bị mất hiệu ứng focus -> đoạn này để định vị lại focus cho khung search
					if (admin_menu_show === false) {
						admin_menu_show = true;
						$(".admin-menu-result").show();
					}

					//
					$(".admin-menu-result").addClass("actived");
					var has_menu = false;
					$("#admin_menu_result li")
						.removedata("show")
						.each(function () {
							var a = $(this).data("key") || "";
							if (a != "" && a.split(k).length > 1) {
								//$(this).show();
								$(this).attr({ "data-show": "1" });
								has_menu = true;
							}
						});

					//
					if (has_menu === false) {
						$(".admin-menu-result").addClass("noned");
						//$(".admin-menu-none").show();
						$(".admin-menu-header").hide();
					} else {
						$(".admin-menu-result").removeClass("noned");
						//$(".admin-menu-none").hide();
						$(".admin-menu-header").show();
					}
				} else {
					$(".admin-menu-result").removeClass("actived").removeClass("noned");
					$(".admin-menu-header").hide();
				}
			}
		});
}
