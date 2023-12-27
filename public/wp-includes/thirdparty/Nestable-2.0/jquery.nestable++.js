/*jslint browser: true, devel: true, white: true, eqeq: true, plusplus: true, sloppy: true, vars: true*/
/*global $ */

/*************** General ***************/

var first_auto_menu_update = false;

var updateOutput = function (e) {
	var list = e.length ? e : $(e.target),
		output = list.data("output");
	if (window.JSON) {
		if (output) {
			//console.log(list);
			output.val(window.JSON.stringify(list.nestable("serialize")));

			//
			if (first_auto_menu_update === false) {
				first_auto_menu_update = true;
			} else {
				$("#menu-add input").each(function () {
					if (($(this).data("set") || "") != "") {
						$(this).val("");
					}
				});

				//
				// setTimeout(function () {
				// 	document.admin_global_form.submit();
				// }, 200);
			}
		} else {
			console.log("output:", output);
		}
	} else {
		alert("JSON browser support required for this page.");
	}
};

var nestableList = $(".dd.nestable > .dd-list");

/***************************************/

/*************** Delete ***************/

var deleteFromMenuHelper = function (target) {
	if (target.data("new") == 1) {
		// if it's not yet saved in the database, just remove it from DOM
		target.fadeOut(function () {
			target.remove();
			updateOutput($(".dd.nestable").data("output", $("#json-output")));
		});
	} else {
		// otherwise hide and mark it for deletion
		target.appendTo(nestableList); // if children, move to the top level
		target.data("deleted", "1");
		target.fadeOut();
	}
};

var deleteFromMenu = function () {
	var targetId = $(this).data("owner-id");
	var target = $('li.dd-item[data-id="' + targetId + '"]');

	var result = confirm(
		"Delete " + target.data("name") + " and all its subitems ?"
	);
	if (!result) {
		return false;
	}
	console.log("delete From Menu");

	// Remove children (if any)
	target.find("li").each(function () {
		deleteFromMenuHelper($(this));
	});

	// Remove parent
	deleteFromMenuHelper(target);

	// update JSON
	updateOutput($(".dd.nestable").data("output", $("#json-output")));
};

/***************************************/

/*************** Edit ***************/

// var menuEditor = $("#menu-editor");
// var editButton = $("#editButton");
// var editInputName = $("#editInputName");
// var editInputSlug = $("#editInputSlug");
// var currentEditName = $("#currentEditName");
var currentEditIdMenu = "";

// Prepares and shows the Edit Form
var prepareEdit = function () {
	var targetId = $(this).data("owner-id");
	currentEditIdMenu = targetId;
	// console.log(targetId);
	var target = $('li.dd-item[data-id="' + targetId + '"]');
	//console.log(target.offset().top);
	/*
	menuEditor.css({
		top: Math.ceil(target.offset().top) + "px",
	});
	*/

	// lấy toàn bộ attr của menu đang dược kích hoạt
	target.each(function () {
		$.each(this.attributes, function () {
			// this.attributes is not a plain object, but an array
			// of attribute nodes, which contain both the name and value
			if (this.specified) {
				// console.log(this.name, this.value);
				// console.log(this.name.replace("data-", ""), this.value);
				// gán giá trị cho các input có data-set tương ứng
				$(
					'#menu-add input[data-set="' + this.name.replace("data-", "") + '"]'
				).val(this.value);
			}
		});
	});
	// editInputName.val(target.data("name"));
	// editInputSlug.val(target.data("slug"));
	//
	// currentEditName.html(target.data("name"));
	$("#currentEditName span").html(target.data("name"));
	$("#currentEditName, .show-for-edit-menu").show();
	$(".hide-for-edit-menu").hide();
	// editButton.data("owner-id", target.data("id"));

	// console.log("[INFO] Editing Menu Item:", editButton.data("owner-id"));
	console.log("[INFO] Editing Menu Item:", currentEditIdMenu);

	// menuEditor.fadeIn();
	// editInputName.focus();
	$("#addInputName").focus();
};

// Edits the Menu item and hides the Edit Form
var editMenuItem = function () {
	// var targetId = $(this).data("owner-id");
	var targetId = currentEditIdMenu;
	// console.log(targetId);
	var target = $('li.dd-item[data-id="' + targetId + '"]');

	// var newName = editInputName.val();
	// var newName = $("#addInputName").val();
	// var newSlug = editInputSlug.val();
	// var newSlug = $("#addInputSlug").val();

	//
	$("#menu-add input").each(function () {
		var x = $(this).data("set") || "";
		if (x != "") {
			// console.log(x, $(this).val());
			target.data(x, $(this).val());
		}
	});

	//
	// target.data("name", newName);
	// target.data("slug", newSlug);

	target.find("> .dd-handle").html($("#addInputName").val());

	// menuEditor.fadeOut();

	// update JSON
	updateOutput($(".dd.nestable").data("output", $("#json-output")));
	return false;
};

/***************************************/

/*************** Add ***************/

var newIdCount = 1;

var addToMenu = function () {
	// var newName = $("#addInputName").val();
	// var newSlug = $("#addInputSlug").val();
	var newId = "new-" + newIdCount;
	// var str =
	// 	'<li class="dd-item" ' +
	// 	'data-id="' +
	// 	newId +
	// 	'" ' +
	// 	'data-name="' +
	// 	newName +
	// 	'" ' +
	// 	'data-slug="' +
	// 	newSlug +
	// 	'" ' +
	// 	'data-new="1" ' +
	// 	'data-deleted="0">' +
	// 	'<div class="dd-handle">' +
	// 	newName +
	// 	"</div> " +
	// 	'<span class="button-delete btn btn-default btn-xs pull-right" ' +
	// 	'data-owner-id="' +
	// 	newId +
	// 	'"> ' +
	// 	'<i class="fa fa-times-circle-o" aria-hidden="true"></i> ' +
	// 	"</span>" +
	// 	'<span class="button-edit btn btn-default btn-xs pull-right" ' +
	// 	'data-owner-id="' +
	// 	newId +
	// 	'">' +
	// 	'<i class="fa fa-pencil" aria-hidden="true"></i>' +
	// 	"</span>" +
	// 	"</li>";

	//
	// console.log(global_menu_tmp);
	var str = (function (htm) {
		// htm = htm.replaceAll("%newText%", "%name%");
		$("#menu-add input").each(function () {
			var x = $(this).data("set") || "";
			if (x != "") {
				htm = htm.replaceAll("%" + x + "%", $(this).val());
			}
		});
		return htm;
	})(global_menu_tmp);
	str = str.replace("%child_htm%", "");
	str = str.replaceAll("%id%", newId);
	console.log(str);

	//
	nestableList.append(str);

	newIdCount++;

	// update JSON
	updateOutput($(".dd.nestable").data("output", $("#json-output")));

	// set events
	$(".dd.nestable .button-delete").on("click", deleteFromMenu);
	$(".dd.nestable .button-edit").on("click", prepareEdit);
};

/***************************************/

$(function () {
	// output initial serialised data
	updateOutput($(".dd.nestable").data("output", $("#json-output")));

	// set onclick events
	// editButton.on("click", editMenuItem);

	$(".dd.nestable .button-delete").on("click", deleteFromMenu);

	$(".dd.nestable .button-edit").on("click", prepareEdit);

	// $("#menu-editor").submit(function (e) {
	// 	e.preventDefault();
	// });

	// $("#menu-add").submit(function (e) {
	// 	e.preventDefault();
	// 	addToMenu();
	// });
});
