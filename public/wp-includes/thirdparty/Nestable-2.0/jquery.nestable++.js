/*jslint browser: true, devel: true, white: true, eqeq: true, plusplus: true, sloppy: true, vars: true*/
/*global $ */

/*************** General ***************/

var first_auto_menu_update = false;

var updateOutput = function (e) {
	var list = e.length ? e : jQuery(e.target),
		output = list.data("output");
	if (window.JSON) {
		if (output) {
			//console.log(list);
			output.val(window.JSON.stringify(list.nestable("serialize")));

			//
			if (first_auto_menu_update === false) {
				first_auto_menu_update = true;
			} else {
				jQuery("#menu-add input").each(function () {
					if ((jQuery(this).data("set") || "") != "") {
						jQuery(this).val("");
					}
				});

				//
				// setTimeout(() => {
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

var nestableList = jQuery(".dd.nestable > .dd-list");

/***************************************/

/*************** Delete ***************/

var deleteFromMenuHelper = function (target) {
	if (target.data("new") == 1) {
		// if it's not yet saved in the database, just remove it from DOM
		target.fadeOut(function () {
			target.remove();
			updateOutput(
				jQuery(".dd.nestable").data("output", jQuery("#json-output"))
			);
		});
	} else {
		// otherwise hide and mark it for deletion
		target.appendTo(nestableList); // if children, move to the top level
		target.data("deleted", "1");
		target.fadeOut();
	}
};

var deleteFromMenu = function () {
	var targetId = jQuery(this).data("owner-id");
	var target = jQuery('li.dd-item[data-id="' + targetId + '"]');

	var result = confirm(
		"Delete " + target.data("name") + " and all its subitems ?"
	);
	if (!result) {
		return false;
	}
	console.log("delete From Menu");

	// Remove children (if any)
	target.find("li").each(function () {
		deleteFromMenuHelper(jQuery(this));
	});

	// Remove parent
	deleteFromMenuHelper(target);

	// update JSON
	updateOutput(jQuery(".dd.nestable").data("output", jQuery("#json-output")));
};

/***************************************/

/*************** Edit ***************/

// var menuEditor = jQuery("#menu-editor");
// var editButton = jQuery("#editButton");
// var editInputName = jQuery("#editInputName");
// var editInputSlug = jQuery("#editInputSlug");
// var currentEditName = jQuery("#currentEditName");
var currentEditIdMenu = "";

// Prepares and shows the Edit Form
var prepareEdit = function () {
	var targetId = jQuery(this).data("owner-id");
	currentEditIdMenu = targetId;
	// console.log(targetId);
	var target = jQuery('li.dd-item[data-id="' + targetId + '"]');
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
				jQuery(
					'#menu-add input[data-set="' + this.name.replace("data-", "") + '"]'
				).val(this.value);
			}
		});
	});
	// editInputName.val(target.data("name"));
	// editInputSlug.val(target.data("slug"));
	//
	// currentEditName.html(target.data("name"));
	jQuery("#currentEditName span").html(target.data("name"));
	jQuery("#currentEditName, .show-for-edit-menu").show();
	jQuery(".hide-for-edit-menu").hide();
	// editButton.data("owner-id", target.data("id"));

	// console.log("[INFO] Editing Menu Item:", editButton.data("owner-id"));
	console.log("[INFO] Editing Menu Item:", currentEditIdMenu);

	// menuEditor.fadeIn();
	// editInputName.focus();
	jQuery("#addInputName").focus();
};

// Edits the Menu item and hides the Edit Form
var editMenuItem = function () {
	// var targetId = jQuery(this).data("owner-id");
	var targetId = currentEditIdMenu;
	// console.log(targetId);
	var target = jQuery('li.dd-item[data-id="' + targetId + '"]');

	// var newName = editInputName.val();
	// var newName = jQuery("#addInputName").val();
	// var newSlug = editInputSlug.val();
	// var newSlug = jQuery("#addInputSlug").val();

	//
	jQuery("#menu-add input").each(function () {
		var x = jQuery(this).data("set") || "";
		if (x != "") {
			// console.log(x, jQuery(this).val());
			target.data(x, jQuery(this).val());
		}
	});

	//
	// target.data("name", newName);
	// target.data("slug", newSlug);

	target.find("> .dd-handle").html(jQuery("#addInputName").val());

	// menuEditor.fadeOut();

	// update JSON
	updateOutput(jQuery(".dd.nestable").data("output", jQuery("#json-output")));
	return false;
};

/***************************************/

/*************** Add ***************/

var newIdCount = 1;

var addToMenu = function () {
	// var newName = jQuery("#addInputName").val();
	// var newSlug = jQuery("#addInputSlug").val();
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
		jQuery("#menu-add input").each(function () {
			var x = jQuery(this).data("set") || "";
			if (x != "") {
				htm = htm.replaceAll("%" + x + "%", jQuery(this).val());
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
	updateOutput(jQuery(".dd.nestable").data("output", jQuery("#json-output")));

	// set events
	jQuery(".dd.nestable .button-delete").on("click", deleteFromMenu);
	jQuery(".dd.nestable .button-edit").on("click", prepareEdit);
};

/***************************************/

jQuery(function () {
	// output initial serialised data
	updateOutput(jQuery(".dd.nestable").data("output", jQuery("#json-output")));

	// set onclick events
	// editButton.on("click", editMenuItem);

	jQuery(".dd.nestable .button-delete").on("click", deleteFromMenu);

	jQuery(".dd.nestable .button-edit").on("click", prepareEdit);

	// jQuery("#menu-editor").submit(function (e) {
	// 	e.preventDefault();
	// });

	// jQuery("#menu-add").submit(function (e) {
	// 	e.preventDefault();
	// 	addToMenu();
	// });
});
