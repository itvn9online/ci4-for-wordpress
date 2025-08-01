/**
 * Chức năng tự động focus và scroll tới label chứa thông tin cần hõ trợ
 */

// thay thế một giá trị nhất định trên URL
function change_url_tab(parameter, new_value) {
	if (typeof parameter == "undefined" || parameter == "") {
		console.log("parameter not found");
		return false;
	}

	// lấy các params hiện tại
	let current_params = window.location.href.split("?");
	let new_params = [];
	if (current_params.length > 1) {
		current_params = current_params[1].replace(/\&amp\;/gi, "&").split("&");

		//
		//console.log(current_params);
		for (let i = 0; i < current_params.length; i++) {
			if (current_params[i].split("=")[0] != parameter) {
				new_params.push(current_params[i]);
			}
		}
	}
	if (typeof new_value != "undefined" && new_value != "") {
		new_params.push(parameter + "=" + new_value);
	}
	//console.log(new_params);

	//
	let new_url = window.location.href.split("?")[0];
	if (new_params.length > 0) {
		new_url += "?" + new_params.join("&");
	}
	//console.log(new_url);

	//
	window.history.pushState("", document.title, new_url);
	return true;
}

//
var add_class_bg_for_tr_support = false;

// tạo for cho label nếu chưa có
(function () {
	let arr = [
		"#content .control-group input",
		"#content .control-group select",
		"#content .control-group textarea",
	];

	jQuery(arr.join(",")).each(function () {
		let get_for = jQuery(this)
			.parent(".controls")
			.parent(".control-group")
			.find("label");
		let check_for = get_for.attr("for") || "";
		//console.log('check for:', check_for);
		//console.log('name:', jQuery(this).attr('name'));
		//console.log('label:', jQuery(this).parent('label'));

		// chưa có thì mới tạo
		if (check_for == "") {
			let label_for = jQuery(this).attr("id") || "";
			if (label_for == "") {
				label_for = jQuery(this).attr("name") || "";
				if (label_for != "") {
					label_for = label_for.replace(/\[|\]/g, "_");

					// gán luôn ID cho filed nếu ID này chưa được sử dụng
					if (jQuery("#" + label_for).length < 1) {
						jQuery(this).attr({
							id: label_for,
						});
					}
				}
			}
			//console.log(label_for);

			if (label_for != "") {
				//label_for += '___auto';
				console.log("label for:", label_for);

				//
				get_for.attr({
					for: label_for,
					/*
                }).css({
                    'border': '1px #f00 solid'
                    */
				});

				//
				get_for.parent().addClass("control-" + label_for);
			}
		} else {
			console.log("check for:", check_for);
			get_for.parent().addClass("control-" + check_for);
		}
	});

	//
	setTimeout(create_search_by_label, 1000);
})();

// hiệu ứng mỗi khi bấm vào label -> tạo link support
jQuery("#content .control-group label").click(function () {
	add_class_bg_for_tr_support = true;

	//
	jQuery(".control-group").removeClass("current-selected-support");

	//
	let a = jQuery(this).attr("for") || "";
	if (a != "") {
		//console.log(a);

		// thay đổi URL để khi xuất hiện params tương ứng thì tự động scroll xuống ID này
		change_url_tab("support_tab", a);
	}
});

//
(function () {
	// tự động trỏ đến TR đang cần support
	setTimeout(() => {
		if (add_class_bg_for_tr_support == false) {
			let get_support_tab = window.location.href.split("&support_tab=");
			if (get_support_tab.length == 1) {
				get_support_tab = window.location.href.split("?support_tab=");
			}
			if (get_support_tab.length > 1 && jQuery(".control-group").length > 0) {
				get_support_tab = get_support_tab[1].split("&")[0].split("#")[0];
				// console.log(get_support_tab);

				//
				let lb = jQuery(
					'#content .control-group label[for="' + get_support_tab + '"]'
				);
				if (lb.length < 1) {
					lb = jQuery("#" + get_support_tab);
				}
				console.log("get_support_tab", get_support_tab, lb);

				// chạy và tìm thẻ TR có chứa cái thẻ label này
				if (get_support_tab != "" && lb.length > 0) {
					// cuộn chuột đến khu vực cần xem -> xem cho dễ
					window.scroll(0, lb.offset().top - 90);

					//
					lb.parents(".control-group").addClass("current-selected-support");
				}
			}
		}
	}, 600);
})();
