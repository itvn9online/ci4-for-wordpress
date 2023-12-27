/*
 * https://github.com/FrancescoBorzi/Nestable
 */

function edit_menu_htmlentities(str) {
	return str.replace(/[\u00A0-\u9999<>\&]/g, function (i) {
		return "&#" + i.charCodeAt(0) + ";";
	});
}

function create_ul_menu_editer(a, sub_menu) {
	if (a.length == 0) {
		return "";
	}

	//
	console.log("create_ul_menu_editer:", a);
	let str = "";
	for (let i = 0; i < a.length; i++) {
		if (a[i].deleted * 1 !== 0) {
			continue;
		}

		// đồng bộ với bản cũ -> chưa có các dữ liệu này
		if (typeof a[i].content == "undefined") {
			a[i].content = "";
		}
		if (typeof a[i].css == "undefined") {
			a[i].css = "";
		}
		if (typeof a[i].icon == "undefined") {
			a[i].icon = "";
		}
		if (typeof a[i].img == "undefined") {
			a[i].img = "";
		}
		if (typeof a[i].rel == "undefined") {
			a[i].rel = "";
		}
		if (typeof a[i].target == "undefined") {
			a[i].target = "";
		}

		//
		let a_tag = "",
			menu_css = ["eb-menu-text"],
			menu_li_css = [];

		// nếu có menu con -> thêm css để định vị cho thẻ a
		if (typeof a[i].children != "undefined") {
			menu_css.push("a-sub-menu");
		}

		if (a[i].slug == "" || a[i].slug.substr(0, 1) == "#") {
			menu_css.push("eb-menu-onlytext");
			menu_li_css.push("eb-menu-li-onlytext");

			a_tag =
				'<span class="' + menu_css.join(" ") + '">' + a[i].name + "</span>";
		} else {
			//
			if (a[i].content != "") {
				a[i].content =
					'<span class="eb-menu-content">' + a[i].content + "</span>";
				menu_css.push("eb-menu-has-content");
			}

			//
			if (a[i].css != "") {
				menu_css.push(a[i].css);
			}

			//
			if (a[i].icon != "") {
				a[i].icon = '<i class="eb-menu-icon ' + a[i].icon + '">&nbsp;</i>';
				menu_css.push("eb-menu-has-icon");
			}

			//
			if (a[i].img != "") {
				a[i].img =
					'<span data-img="' + a[i].img + '" class="eb-menu-img">&nbsp;</span>';
				menu_css.push("eb-menu-has-img");
			}

			//
			if (a[i].rel != "") {
				a[i].rel = ' rel="' + a[i].rel + '"';
			}

			//
			if (a[i].target != "") {
				a[i].target = ' target="' + a[i].target + '"';
			}

			//
			a_tag =
				'<a href="' +
				a[i].slug +
				'" class="' +
				menu_css.join(" ") +
				'"' +
				a[i].rel +
				a[i].target +
				">" +
				a[i].icon +
				a[i].img +
				a[i].name +
				a[i].content +
				"</a>";
		}

		// nếu có menu con -> gọi luôn
		if (typeof a[i].children != "undefined") {
			a_tag += create_ul_menu_editer(a[i].children, "sub-menu");
			menu_li_css.push("has-sub-menu");
		}

		//
		str += '<li class="' + menu_li_css.join(" ") + '">' + a_tag + "</li>";
	}

	//
	if (typeof sub_menu == "undefined") {
		sub_menu = "";
	}
	sub_menu = jQuery.trim(sub_menu + " cf");

	//
	return '<ul class="' + sub_menu + '">' + str + "</ul>";
}

function create_html_menu_editer(max_i) {
	if (typeof max_i != "number") {
		max_i = 100;
	} else if (max_i < 0) {
		console.log("%c Không xác định được editer cho menu", "color: red");
		return false;
	}

	//
	let insert_to = "";
	if (jQuery("#Resolution_ifr").length === 1) {
		insert_to = "#Resolution_ifr";
	} else if (jQuery(".cke_wysiwyg_frame").length === 1) {
		insert_to = ".cke_wysiwyg_frame";
	} else {
		setTimeout(function () {
			create_html_menu_editer(max_i - 1);
		}, 200);
		return false;
	}
	try {
		if (arguments.callee.caller !== null) {
			console.log("Call in: " + arguments.callee.caller.name.toString());
		}
	} catch (e) {
		//
	}

	//
	let a = $("#json-output").val() || "";
	if (a != "") {
		try {
			a = JSON.parse(a);
		} catch (e) {
			WGR_show_try_catch_err(e);
			a = null;
		}

		//
		if (a !== null) {
			let str = create_ul_menu_editer(a);
			//console.log(str);

			//
			jQuery(insert_to).contents().find("body").html(str);
		}
	}

	//
	return true;
}

function get_json_add_menu(obj) {
	try {
		if (arguments.callee.caller !== null) {
			console.log("Call in: " + arguments.callee.caller.name.toString());
		}
	} catch (e) {
		//
	}

	//
	if ($("#addInputSlug").val() == "") {
		$("#addInputSlug").val("#");
	}

	//
	console.log("currentEditIdMenu:", currentEditIdMenu);

	if (currentEditIdMenu != "") {
		console.log("update menu");
		editMenuItem();
	} else {
		console.log("add new");
		addToMenu();
	}

	// xong thì reset them số edit
	$("#currentEditName, .show-for-edit-menu").hide();
	$(".hide-for-edit-menu").show();
	currentEditIdMenu = "";

	//
	// return false;

	// //
	// if ($.trim($("#addInputSlug").val() || "") == "") {
	// 	$("#addInputSlug").val("#").trigger("change");
	// 	return false;
	// }

	//
	return get_json_code_menu(obj);
}

function get_json_edit_menu(obj) {
	try {
		if (arguments.callee.caller !== null) {
			console.log("Call in: " + arguments.callee.caller.name.toString());
		}
	} catch (e) {
		//
	}

	//
	// if ($.trim($("#editInputSlug").val() || "") == "") {
	// 	$("#editInputSlug").val("#").trigger("change");
	// 	return false;
	// }

	//
	// return get_json_code_menu(obj);
	return editMenuItem();
}

// mỗi lần cập nhật menu -> tạo ra câu confirm để còn chờ nội dung menu được cập nhật
function action_before_submit_menu() {
	action_json_code_menu();

	//
	if (confirm("Xác nhận thay đổi nội dung cho menu này!") === false) {
		return false;
	}

	//
	return true;
}

function get_json_code_menu(obj) {
	setTimeout(function () {
		action_json_code_menu(obj);
	}, 200);

	return false;
}

function action_json_code_menu(obj) {
	let arr = $("#json-output").val();
	console.log(arr);

	//
	$("#data_post_excerpt").val(arr);

	//setTimeout(function () {
	create_html_menu_editer();
	//}, 200);

	//
	if (typeof obj != "undefined" && typeof obj.id != "undefined") {
		//console.log(obj.id);
		// xóa chữ trong các input của form tương ứng được truyền vào
		setTimeout(function () {
			$("#" + obj.id + ' input[type="text"]').val("");

			// tự động cập nhật menu
			console.log("Auto submit in " + ($(this).attr("id") || ""));
			document.admin_global_form.submit();
		}, 200);
	}

	return false;
}

var global_menu_jd = 1;
var global_menu_tmp = "";

function create_html_menu_nestable(a) {
	if (a.length == 0) {
		console.log("a length");
		return "";
	}
	console.log("a:", a);

	//
	let str = "",
		tmp = $(".dd-tmp-list").html() || "";
	if (tmp == "") {
		console.log("%c dd-tmp-list not found!", "color: red;");
		return false;
	}
	//console.log('tmp:', tmp);
	let arr_replace_class = {
		"dd-item": "dd-item",
		"dd-handle": "dd-handle",
		"button-delete": "button-delete btn btn-default btn-xs pull-right",
		"button-edit": "button-edit btn btn-default btn-xs pull-right",
		"fa-times": "fa fa-times-circle-o",
		"fa-pencil": "fa fa-pencil",
	};
	for (let x in arr_replace_class) {
		tmp = tmp.replace("%" + x + "%", arr_replace_class[x]);
	}
	//console.log('data class length:', tmp.split(' data-class=').length);
	tmp = tmp.replace(/\sdata\-class\=/gi, " class=");
	// console.log("tmp:", tmp);
	global_menu_tmp = tmp;

	//
	for (let i = 0; i < a.length; i++) {
		//console.log(a[i]);
		if (a[i].deleted * 1 !== 0) {
			continue;
		}

		//
		a[i].name = a[i].name.replaceAll('"', "&quot;");
		if (typeof a[i].content != "undefined" && a[i].content != "") {
			a[i].content = a[i].content.replaceAll('"', "&quot;");
		}

		//
		let htm = tmp;
		a[i]["id"] = global_menu_jd;
		global_menu_jd++;
		for (let x in a[i]) {
			//console.log(a[i]);
			if (typeof a[i].name == "undefined") {
				continue;
			}
			// let newText = JSON.parse(JSON.stringify(a[i]));
			// console.log(newText);
			//newText.newText = newText.name;

			// thay " thành &quot; để đỡ lỗi HTML
			// htm = htm.replaceAll("%newText%", newText.name);
			//console.log(a[i]);

			//
			htm = htm.replaceAll("%" + x + "%", a[i][x]);
		}

		// nếu có menu con -> gọi luôn
		let child_htm = "";
		if (typeof a[i].children != "undefined") {
			child_htm = create_html_menu_nestable(a[i].children);
		}
		htm = htm.replace("%child_htm%", child_htm);
		//console.log(htm);

		//
		str += htm;
	}

	// xóa các dữ liệu mẫu
	$("#menu-add input").each(function () {
		let x = $(this).data("set") || "";
		if (x != "") {
			str = str.replaceAll("%" + x + "%", "");
		}
	});
	// str = str.replaceAll("%content%", "");
	// str = str.replaceAll("%img%", "");
	// console.log("str:", str);

	//
	return '<ol class="dd-list">' + str + "</ol>";
}

function restore_json_menu_in_html_menu() {
	if (confirm("Xác nhận muốn khôi phục lại JSON menu từ HTML menu!") !== true) {
		return false;
	}

	// chạy vòng lặp lấy các attr theo input add menu
	let get_data_set = [];
	$("#menu-add input").each(function () {
		let x = $(this).data("set") || "";
		if (x != "") {
			get_data_set.push(x);
		}
	});
	console.log("get_data_set:", get_data_set);

	//
	let arr = [],
		_id = 1;
	$("#Resolution_ifr")
		.contents()
		.find("#tinymce a")
		.each(function () {
			let a_href = $(this).attr("href") || "";
			a_href = a_href.replace(/\.\.\//gi, "");
			if (a_href == "") {
				a_href = "#";
			}
			let a_text = $(this).text() || "";
			let a_push = {
				deleted: 0,
				new: 0,
				slug: a_href,
				name: $.trim(a_text),
				id: _id,
			};
			for (let i = 0; i < get_data_set.length; i++) {
				if (typeof a_push[get_data_set[i]] == "undefined") {
					a_push[get_data_set[i]] = "";
				}
			}
			console.log("a_push:", a_push);
			arr.push(a_push);

			//
			_id++;
		});
	console.log(arr);
	if (arr.length > 0) {
		WGR_alert("Tìm thấy " + arr.length + " menu");

		//
		$("body").addClass("show-hidden-menu");

		//
		$("#json-output, #data_post_excerpt").val(JSON.stringify(arr)).focus();
		WGR_alert("Bấm lưu lại sau đó chờ tải lại trang để nhận thay đổi mới");

		//
		$("#target_eb_iframe").on("load", function () {
			window.location = window.location.href;
		});
	}

	//
	return true;
}

function show_hide_if_edit_menu() {
	if (localStorage.getItem("admin-show-hidden-menu") === null) {
		if (confirm("Chức năng này chủ yếu để debug code!") === true) {
			$("body").addClass("show-hidden-menu");
			localStorage.setItem("admin-show-hidden-menu", 1);
		}
	} else {
		$("body").removeClass("show-hidden-menu");
		localStorage.removeItem("admin-show-hidden-menu");
	}
}
if (localStorage.getItem("admin-show-hidden-menu") !== null) {
	$("body").addClass("show-hidden-menu");
}

/*
 *
 */

//$(document).ready(function () {
//$('.hide-if-edit-menu').hide();

(function () {
	// tạo html cho việc chỉnh sửa menu
	let a = $("#data_post_excerpt").val() || "";
	if (a != "") {
		try {
			a = JSON.parse(a);
			//console.log(a);
		} catch (e) {
			WGR_show_try_catch_err(e);
			a = null;
		}

		//
		if (a !== null) {
			let str = create_html_menu_nestable(a);
			//console.log(str);

			//
			jQuery(".container-edit-menu .dd.nestable").html(str).show();
		}
	}
})();

//
//create_html_menu_editer();
//});

$("#quick_add_menu select").change(function () {
	let v = $(this).val() || "";
	// console.log(v);

	if (v != "") {
		let base_url = $("base").attr("href") || "";
		// console.log(base_url);
		if (base_url != "") {
			v = v.replace(base_url, "./");
		}

		//
		$("#addInputName").val($("option:selected", this).text());
	} else {
		$("#addInputName").val(v);
	}
	$("#addInputSlug").val(v);
	$("#addInputName").focus();

	// chuyển về add new
	$("#currentEditName, .show-for-edit-menu").hide();
	$(".hide-for-edit-menu").show();
	currentEditIdMenu = "";
});

//
$("#addInputIcon").change(function () {
	let a = $(this).val();
	a = $.trim(a);
	if (a != "" && a.split("fa").length < 2) {
		a = "fa fa-" + a;
		$(this).val(a);
	}
});

/*
$(document).ready(function () {
    MY_select2('#quick_add_menu select');
});
*/

//
add_and_show_post_avt("#addInputImg", 0, "medium");
// add_and_show_post_avt("#editInputImg", 0, "medium");
