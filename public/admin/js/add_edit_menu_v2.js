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
	//console.log(a);
	var str = "";
	for (var i = 0; i < a.length; i++) {
		if (a[i].deleted * 1 !== 0) {
			continue;
		}

		//
		var a_tag = "";
		if (a[i].slug == "") {
			a_tag = a[i].name;
		} else {
			a_tag = '<a href="' + a[i].slug + '">' + a[i].name + "</a>";
		}

		// nếu có menu con -> gọi luôn
		if (typeof a[i].children != "undefined") {
			a_tag += create_ul_menu_editer(a[i].children, "sub-menu");
		}

		//
		str += "<li>" + a_tag + "</li>";
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
	var insert_to = "";
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
	var a = $("#json-output").val() || "";
	if (a != "") {
		try {
			a = JSON.parse(a);
		} catch (e) {
			WGR_show_try_catch_err(e);
			a = null;
		}

		//
		if (a !== null) {
			var str = create_ul_menu_editer(a);
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
	if ($.trim($("#addInputSlug").val() || "") == "") {
		$("#addInputSlug").val("#").trigger("change");
		return false;
	}

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
	if ($.trim($("#editInputSlug").val() || "") == "") {
		$("#editInputSlug").val("#").trigger("change");
		return false;
	}

	//
	return get_json_code_menu(obj);
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

	return true;
}

function action_json_code_menu(obj) {
	var arr = $("#json-output").val();
	//console.log(arr);

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

	return true;
}

var global_menu_jd = 1;

function create_html_menu_nestable(a) {
	if (a.length == 0) {
		console.log("a length");
		return "";
	}

	//
	//console.log(a);
	var str = "";
	var tmp = $(".dd-tmp-list").html() || "";
	if (tmp == "") {
		console.log("%c dd-tmp-list not found!", "color: red;");
		return false;
	}
	//console.log('tmp:', tmp);
	var arr_replace_class = {
		"dd-item": "dd-item",
		"dd-handle": "dd-handle",
		"button-delete": "button-delete btn btn-default btn-xs pull-right",
		"button-edit": "button-edit btn btn-default btn-xs pull-right",
		"fa-times": "fa fa-times-circle-o",
		"fa-pencil": "fa fa-pencil",
	};
	for (var x in arr_replace_class) {
		tmp = tmp.replace("%" + x + "%", arr_replace_class[x]);
	}
	//console.log('data class length:', tmp.split(' data-class=').length);
	tmp = tmp.replace(/\sdata\-class\=/gi, " class=");
	console.log("tmp:", tmp);

	//
	for (var i = 0; i < a.length; i++) {
		//console.log(a[i]);
		if (a[i].deleted * 1 !== 0) {
			continue;
		}

		//
		var htm = tmp;
		a[i]["id"] = global_menu_jd;
		global_menu_jd++;
		for (var x in a[i]) {
			//console.log(a[i]);
			if (typeof a[i].name == "undefined") {
				continue;
			}
			var newText = JSON.parse(JSON.stringify(a[i]));
			//console.log(newText);
			//newText.newText = newText.name;

			// thay " thành &quot; để đỡ lỗi HTML
			for (var j = 0; j < 10; j++) {
				htm = htm.replace("%newText%", newText.name);
				a[i].name = a[i].name.replace('"', "&quot;");
			}
			//console.log(a[i]);

			//
			for (var j = 0; j < 10; j++) {
				htm = htm.replace("%" + x + "%", a[i][x]);
			}
		}

		// nếu có menu con -> gọi luôn
		var child_htm = "";
		if (typeof a[i].children != "undefined") {
			child_htm = create_html_menu_nestable(a[i].children);
		}
		htm = htm.replace("%child_htm%", child_htm);
		//console.log(htm);

		//
		str += htm;
	}

	//
	return '<ol class="dd-list">' + str + "</ol>";
}

function restore_json_menu_in_html_menu() {
	if (confirm("Xác nhận muốn khôi phục lại JSON menu từ HTML menu!") !== true) {
		return false;
	}

	//
	var arr = [];
	var _id = 1;
	$("#Resolution_ifr")
		.contents()
		.find("#tinymce a")
		.each(function () {
			var a_href = $(this).attr("href") || "";
			a_href = a_href.replace(/\.\.\//gi, "");
			if (a_href == "") {
				a_href = "#";
			}
			var a_text = $(this).html() || "";
			arr.push({
				deleted: 0,
				new: 0,
				slug: a_href,
				name: a_text,
				id: _id,
			});

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
	var a = $("#data_post_excerpt").val() || "";
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
			var str = create_html_menu_nestable(a);
			//console.log(str);

			//
			jQuery(".container-edit-menu .dd.nestable").html(str).show();
		}
	}
})();

//
//create_html_menu_editer();
//});

$("#quick_add_menu").change(function () {
	var v = $("#quick_add_menu").val() || "";

	if (v != "") {
		var base_url = $("base ").attr("href") || "";
		if (base_url != "") {
			v = v.replace(base_url, "./");
		}

		//
		$("#addInputName").val($("#quick_add_menu option:selected").text());
	} else {
		$("#addInputName").val(v);
	}
	$("#addInputSlug").val(v);
	$("#addInputName").focus();
});

/*
$(document).ready(function () {
    MY_select2('#quick_add_menu');
});
*/

//
add_and_show_post_avt("#addInputName", 1, "medium");
add_and_show_post_avt("#editInputName", 1, "medium");
