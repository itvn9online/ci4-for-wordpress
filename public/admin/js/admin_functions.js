function fix_textarea_height() {
	jQuery(".fix-textarea-height textarea, textarea.fix-textarea-height")
		.off("change")
		.change(function () {
			var a = jQuery(this).attr("data-resize") || "",
				min_height = jQuery(this).attr("data-min-height") || 60,
				add_height = jQuery(this).attr("data-add-height") || 20;
			//		console.log(min_height);

			if (a == "") {
				jQuery(this).height(20);

				//
				var new_height = jQuery(this).get(0).scrollHeight || 0;
				new_height -= 0 - add_height;
				if (new_height < min_height) {
					new_height = min_height;
				}

				//
				jQuery(this).height(new_height);

				//
				console.log(
					"Fix textarea height #" +
						(jQuery(this).attr("name") || jQuery(this).attr("id") || "NULL")
				);
			}
		})
		.off("click")
		.click(function () {
			jQuery(this).change();
		})
		.each(function () {
			jQuery(this).change();
		});
}

function create_div_grid_layout(class_css) {
	return [
		'<div class="col ' + class_css + '">',
		'<div class="col-inner">div content</div>',
		"</div>",
	].join(" ");
}

var current_textediter_insert_to = "";

function WgrWp_popup_upload(insert_to, add_img_tag, img_size, input_type) {
	if (
		current_textediter_insert_to != insert_to &&
		$("oi_wgr_wp_upload_iframe").length == 0
	) {
		current_textediter_insert_to = insert_to;

		//
		if (typeof add_img_tag == "undefined" || add_img_tag == "") {
			add_img_tag = 0;
		}
		if (typeof img_size == "undefined") {
			//img_size = 'full';
			img_size = "large";
		}
		if (typeof input_type == "undefined") {
			input_type = "text";
		}

		//
		$("body").append(
			'<div class="hide-if-esc wgr-wp-upload"><iframe id="oi_wgr_wp_upload_iframe" name="oi_wgr_wp_upload_iframe" src="admin/uploads?quick_upload=1&insert_to=' +
				insert_to +
				"&add_img_tag=" +
				add_img_tag +
				"&img_size=" +
				img_size +
				"&input_type=" +
				input_type +
				'" width="95%" height="' +
				($(window).height() / 100) * 90 +
				'" frameborder="0">AJAX form</iframe></div>'
		);
	}

	//
	$("body").addClass("no-scroll");
	$(".wgr-wp-upload").show();
}

// nạp ảnh đại diện cho các input
function add_and_show_post_avt(for_id, add_img_tag, img_size, input_type) {
	if ($(for_id).length != 1) {
		console.log(for_id + " not found! (length != 1)");
		return false;
	}
	//console.log(Math.random());
	if (typeof add_img_tag == "undefined" || add_img_tag == "") {
		add_img_tag = 0;
	}
	if (typeof img_size == "undefined") {
		//img_size = 'full';
		img_size = "large";
	}
	if (typeof input_type == "undefined") {
		input_type = "text";
	}

	//
	var str = [];
	//str.push(' <input type="button" class="btn btn-info" value="Chọn ảnh" onclick="BrowseServer( \'Images:/\', \'' + for_id.substr(1) + '\' );"/>');
	str.push(
		' <button type="button" data-for="' +
			for_id.substr(1) +
			'" class="btn btn-info add-image-' +
			for_id.replace(/\#|\./gi, "-") +
			'" onclick="WgrWp_popup_upload( \'' +
			for_id.substr(1) +
			"', " +
			add_img_tag +
			", '" +
			img_size +
			"', '" +
			input_type +
			"' );\">Thêm Media</button> "
	);

	//
	$(".for-" + for_id).remove();

	//
	if (input_type != "textediter") {
		var img = $(for_id).val() || "";
		if (img != "") {
			str.push(
				'<p class="show-img-if-change for-' +
					for_id.substr(1) +
					'"><img src="' +
					img +
					'" onclick="return trigger_click_open_media(\'' +
					for_id.substr(1) +
					'\');" class="cur control-group-avt" /></p>'
			);
		}
	}

	//
	console.log("Add button image for:", for_id);
	$(for_id).after(str.join(" "));
}

// mở form upload ảnh để upload cho tiện
function trigger_click_open_media(for_id) {
	$("button[data-for='" + for_id + "']").trigger("click");
}

function click_set_img_for_input(img_id) {
	var img = $('.media-attachment-img[data-id="' + img_id + '"]');
	var insert_to = img.attr("data-insert") || "";

	//
	if (insert_to == "") {
		return false;
	}
	//console.log(insert_to);
	var mime_type = img.attr("data-mime_type") || "";
	var file_type = "";
	var file_ext = "";
	//console.log(file_type);
	if (mime_type != "") {
		file_type = mime_type.split("/");
		if (file_type.length > 1) {
			file_ext = file_type[1];
		} else {
			file_ext = file_type[0];
		}
		file_type = file_type[0];
	}
	//console.log(file_type);

	/*
    if (top.$('#' + insert_to).length === 1) {
        insert_to = '#' + insert_to;
    } else if (top.$('.' + insert_to).length === 1) {
        insert_to = '.' + insert_to;
    } else {
        insert_to = '';
    }
    */
	//console.log(insert_to);

	//
	if (insert_to != "") {
		var add_img_tag = img.attr("data-add_img_tag") || "";
		add_img_tag *= 1;

		//var data_size = img.attr('data-size') || 'full';
		var data_size = img.attr("data-size") || "large";
		if (data_size == "") {
			//data_size = 'full';
			data_size = "large";
		}
		var data_src = img.data(data_size) || "";

		// lấy các thuộc tính của ảnh -> tối ưu SEO
		var img_attr = [];
		var data_srcset = img.attr("data-srcset") || "";
		if (data_srcset != "") {
			img_attr.push('data-to-srcset="' + data_srcset + '"');
		}
		var data_sizes = img.attr("data-sizes") || "";
		if (data_sizes != "") {
			img_attr.push('sizes="' + data_sizes + '"');
		}
		var data_width = img.attr("data-width") || "";
		if (data_width != "") {
			img_attr.push('width="' + data_width + '"');
		}
		var data_height = img.attr("data-height") || "";
		if (data_height != "") {
			img_attr.push('height="' + data_height + '"');
		}

		if (data_src == "") {
			data_src = img.attr("data-thumbnail") || "";
			if (data_src == "") {
				alert("Không xác định được URL của ảnh!");
				return false;
			}
		}
		var input_type = img.attr("data-input_type") || "";
		//console.log(input_type);
		// insert ảnh vào text area
		if (input_type == "textediter") {
			if (data_src.split("//").length == 1) {
				data_src = $("base").attr("href") + data_src;
			}
			data_src = data_src.replace(".daidq-ext", "");

			//
			var return_html = "";
			// nếu là video thì nhúng video
			if (file_type == "video") {
				return_html =
					'<video controls width="560" height="315"><source src="' +
					data_src +
					'" type="' +
					mime_type +
					'">Your browser does not support the video tag.</video>';
			}
			// audio thì nhúng audio
			else if (file_type == "audio") {
				return_html =
					'<audio controls><source src="' +
					data_src +
					'" type="' +
					mime_type +
					'">Your browser does not support the audio element.</audio>';
			}
			// mặc định thì trả về ảnh
			else {
				return_html =
					'<img src="' +
					data_src +
					'"' +
					img_attr.join(" ") +
					' class="eb-push-img" />';
			}
			top.tinymce.get(insert_to).insertContent(return_html);
		} else {
			// riêng với ảnh đại diện
			var arr_all_srcs = {};
			if (insert_to == "post_meta_image") {
				// các kích cỡ khác
				var arr_all_sizes = [
					//"full",
					"thumbnail",
					"medium",
					"medium_large",
					"large",
				];
				//console.log(arr_all_sizes);
				for (var i = 0; i < arr_all_sizes.length; i++) {
					arr_all_srcs[arr_all_sizes[i]] = img.data(arr_all_sizes[i]) || "";
				}
				//console.log(arr_all_srcs);
			}

			// thay ảnh hiển thị -> gọi đến function trên top
			top.WGR_show_real_post_avt(insert_to, data_src, arr_all_srcs);

			//
			if (add_img_tag === 1) {
				data_src = '<img src="' + data_src + '" />';
			}

			//
			top
				.$("#" + insert_to)
				.val(data_src)
				.trigger("focus");
		}
	}
	hide_if_esc();
}

function WGR_show_real_post_avt(insert_to, data_src, arr_all_srcs) {
	//console.log('.show-img-if-change.for-' + insert_to);
	//console.log('data src:', data_src);
	//console.log('.show-img-if-change.for-' + data_src);
	//console.log('.show-img-if-change.for-' + insert_to);
	//console.log($('.show-img-if-change.for-' + insert_to + ' img').length);
	if ($(".show-img-if-change.for-" + insert_to + " img").length > 0) {
		$(".show-img-if-change.for-" + insert_to + " img").attr({
			src: data_src,
		});

		// nếu đang thiết lập ảnh đại diện chính
		if (insert_to == "post_meta_image") {
			//console.log(insert_to);
			//console.log(arr_all_srcs);

			// xóa bỏ các ảnh phụ trợ
			var arr = [
				"image_medium",
				"image_thumbnail",
				"image_webp",
				"image_medium_large",
				"image_large",
			];
			for (var i = 0; i < arr.length; i++) {
				$("#post_meta_" + arr[i]).val("");
			}

			// thiết lập ảnh mới
			for (var x in arr_all_srcs) {
				$("#post_meta_image_" + x).val(arr_all_srcs[x]);
			}
		}

		//
		return true;
	}
	return false;
}

function WGR_load_textediter(for_id, ops) {
	if (typeof ops == "undefined") {
		ops = {};
	}
	if (typeof ops["height"] == "undefined") {
		ops["height"] = 250;
	}
	if (typeof ops["plugins"] == "undefined") {
		ops["plugins"] = [
			"textcolor colorpicker",
			"print preview paste importcss searchreplace autolink autosave save directionality",
			"code visualblocks visualchars fullscreen image link media template codesample table hr",
			"pagebreak nonbreaking anchor toc insertdatetime advlist lists wordcount imagetools textpattern",
			"noneditable help charmap emoticons",
		];
	}
	if (typeof ops["toolbar"] == "undefined") {
		var arr_toolbar = [
			"undo redo",
			"bold italic underline strikethrough",
			"fontselect fontsizeselect formatselect",
			//'sub sup',
			"alignleft aligncenter alignright alignjustify",
			//'justifyleft justifycenter justifyright justifyfull',
			"forecolor backcolor",
			"bullist numlist outdent indent",
			"image media",
			"link table",
			//'insertdate inserttime',
			//'showcomments addcomment',
			"preview removeformat fullscreen code",
			"help",
		];
		ops["toolbar"] = arr_toolbar.join(" | ");
	}
	//console.log(ops);

	//
	tinymce.init({
		/*
        editor_encoding: "raw",
        apply_source_formatting: true,
        encoding: 'html',
        allow_html_in_named_anchor: true,
        element_format: 'xhtml',
        */
		selector: "textarea" + for_id,
		height: ops["height"],
		//menubar: false,
		plugins: ops["plugins"],
		//a11y_advanced_options: true,
		//
		image_title: true,
		image_caption: true,
		image_advtab: true,
		//imagetools_toolbar: "rotateleft rotateright | flipv fliph | editimage imageoptions",
		//quickbars_selection_toolbar: 'bold italic | quicklink h2 h3 blockquote quickimage quicktable',
		//toolbar_mode: 'sliding',
		//contextmenu: 'link image imagetools table',
		//
		link_class_list: [
			{ title: "None", value: "" },
			{ title: "Button primary", value: "btn btn-primary" },
			{ title: "Button secondary", value: "btn btn-secondary" },
			{ title: "Button success", value: "btn btn-success" },
			{ title: "Button danger", value: "btn btn-danger" },
			{ title: "Button warning", value: "btn btn-warning" },
			{ title: "Button info", value: "btn btn-info" },
		],
		// rel cho thẻ A
		rel_list: [
			{ title: "None", value: "" },
			{ title: "No Referrer", value: "noreferrer" },
			{ title: "No Follow", value: "nofollow" },
			{ title: "External Link", value: "external" },
		],
		//
		toolbar: ops["toolbar"],
		//toolbar_sticky: true,
		templates: [
			{
				title: "New Table",
				description: "creates a new table",
				content: [
					'<div class="mce-table-tmpl">',
					'<table width="98%%" border="0" cellspacing="0" cellpadding="0">',
					"<tr>",
					'<th scope="col"> th </th>',
					'<th scope="col"> th </th>',
					"</tr>",
					"<tr>",
					"<td> td </td>",
					"<td> td </td>",
					"</tr>",
					"</table>",
					"</div>",
				].join(" "),
			},
			{
				title: "Starting my story",
				description: "A cure for writers block",
				content: "Once upon a time...",
			},
			{
				title: "New list with dates",
				description: "New List with dates",
				content: [
					'<div class="mce-list-tmpl">',
					'<span class="cdate">cdate</span><br /><span class="mdate">mdate</span>',
					"<h2>My List</h2>",
					"<ul>",
					"<li> li </li>",
					"<li> li </li>",
					"</ul>",
					"</div>",
				].join(" "),
			},
			{
				title: "DIV grid",
				description: "Add DIV grid using bootstrap",
				content: [
					'<div class="row">',
					create_div_grid_layout("small-12 medium-4 large-4"),
					create_div_grid_layout("small-12 medium-4 large-4"),
					create_div_grid_layout("small-12 medium-4 large-4"),
					"</div>",
				].join(" "),
			},
			{
				title: "Button inline",
				description: "New new button inline",
				content: '<a href="#" class="btn btn-primary">Button inline</a>',
			},
			{
				title: "Button new-line",
				description: "New new button new-line",
				content: [
					'<p class="mce-btn-tmpl">',
					'<a href="' +
						web_link +
						'" class="btn btn-primary">Button new-line</a>',
					"</p>",
				].join(" "),
			},
			{
				title: "Phone icon",
				description: "Phone icon awesome",
				content: '<i class="fa fa-phone"><!-- icon --></i>',
			},
		],
		//table_use_colgroups: true,
		// không cho phép reszie với table -> để tối ưu với reponsive
		table_resize_bars: false,
		// thuộc tính mặc định của table
		table_default_attributes: {
			width: "100%",
			border: "0",
			cellspacing: "0",
			cellpadding: "0",
		},
		// style mặc định của table
		table_default_styles: {},
		//
		template_cdate_format: "[Date Created (CDATE): %d/%m/%Y : %H:%M:%S]",
		template_mdate_format: "[Date Modified (MDATE): %d/%m/%Y : %H:%M:%S]",
		// tắt chức năng chuyển thẻ i thành em
		extended_valid_elements: "i[class]",
		// thêm 1 số link css để tạo định dạng trong quá trình soạn thảo
		content_css: [
			web_link + "thirdparty/bootstrap/css/bootstrap.min.css",
			web_link + "admin/css/bootstrap-for-tiny-editer.css",
			web_link + "thirdparty/awesome47/css/font-awesome.before.css?v=4.7",
			web_link + "thirdparty/awesome47/css/font-awesome.min.css?v=4.7",
		].join(","),
		setup: function (ed) {
			// sự kiện khi khi nhấp đúp chuột
			ed.on("DblClick", function (e) {
				//console.log("Double click event:", e.target);
				//console.log("Double click event:", e.target.className);

				//
				var target_nodeName = e.target.nodeName.toLocaleLowerCase();
				console.log("Double click event:", target_nodeName);
				// nếu là hình ảnh -> mở hộp thoại sửa ảnh
				if (target_nodeName == "img") {
					var mce_object = e.target.getAttribute("data-mce-object");
					console.log("data mce object:", mce_object);
					if (mce_object == "video" || mce_object == "audio") {
						tinymce.activeEditor.execCommand("mceMedia");
					} else {
						tinymce.activeEditor.execCommand("mceImage");
					}
				}
				// nếu là URL -> mở hộp chỉnh sửa URL
				else if (target_nodeName == "a") {
					tinymce.activeEditor.execCommand("mceLink");
				}
				// nếu mở thẻ của font awesome
				else if (target_nodeName == "i") {
					var i_class_name = e.target.className;
					if (i_class_name.split("fa ").length > 1) {
						//console.log("i tag dblclick:", i_class_name);
						let new_class_name = prompt("Font awesome class", i_class_name);
						if (new_class_name != null && new_class_name != i_class_name) {
							// xóa các class cũ
							var i_class_name = i_class_name.split(" ");
							for (var i = 0; i < i_class_name.length; i++) {
								e.target.classList.remove(i_class_name[i]);
							}
							// thay class mới
							i_class_name = new_class_name.split(" ");
							for (var i = 0; i < i_class_name.length; i++) {
								e.target.classList.add(i_class_name[i]);
							}
						}
					}
				}
			});
		},
		init_instance_callback: function (editor) {
			editor.addShortcut("ctrl+s", "Custom Ctrl+S", "custom_ctrl_s");
			editor.addCommand("custom_ctrl_s", function () {
				// tự động submit form nếu bấm ctrl + s ở trong editer
				//console.log(typeof document.admin_global_form);
				if (typeof document.admin_global_form != "undefined") {
					//WGR_alert("234");
					// nếu có function này
					if (typeof action_before_submit_post == "function") {
						// phải gọi đến nó trước rồi mới submit
						if (action_before_submit_post() === true) {
							document.admin_global_form.submit();
						}
					} else {
						document.admin_global_form.submit();
					}
					return false;
				}
			});
		},
		//content_style: 'body { font-family:Helvetica,Arial,sans-serif; font-size:14px }'
	});

	// tạo nút xóa style trong text-editer
	btn_remove_editer_style(for_id);
	// tạo nút nhúng media vào text-editer
	add_and_show_post_avt(for_id, 1, "", "textediter");
	// tính toán lại chiều cao của iframe
	setTimeout(function () {
		re_height_iframe_editer(for_id);
	}, 1000);
}

// tạo nút xóa một số attr trong editer để tránh xung đột mã HTML với website -> hay gặp khi copy nội dung từ web khác về
function btn_remove_editer_style(for_id) {
	console.log(for_id);
	$(for_id).after(
		"<button type='button' onclick=\"return cleanup_style_in_editer('" +
			for_id.substr(1) +
			'\');" class="btn btn-secondary">Xóa style</button> '
	);
	$(for_id).after(
		"<button type='button' onclick=\"return cleanup_class_in_editer('" +
			for_id.substr(1) +
			'\');" class="btn btn-secondary">Xóa class CSS</button> '
	);
}

function cleanup_style_in_editer(for_id) {
	cleanup_attr_in_editer(for_id, "style");
	cleanup_attr_in_editer(for_id, "data-mce-st");
}

function cleanup_class_in_editer(for_id) {
	cleanup_attr_in_editer(for_id, "class");
}

// tìm và xóa toàn bộ thẻ style trong text-editer
function cleanup_attr_in_editer(for_id, rm_attr) {
	for_id = "#" + for_id + "_ifr";
	console.log(for_id);
	if ($(for_id).length <= 0) {
		WGR_alert("Không xác định được iframe ID " + for_id);
		return false;
	}

	//
	jQuery(for_id).contents().find("body").find("*").removeAttr(rm_attr);
}

// gán src cho thẻ img từ data-img -> dùng cho angularjs
function action_data_img_src() {
	$(".each-to-img-src").each(function () {
		var a = $(this).attr("data-src") || "";
		if (a != "") {
			$(this).attr({
				src: a,
			});
		}
	});
}

function action_data_bg_src() {
	$(".each-to-bg-src").each(function () {
		var a = $(this).attr("data-src") || "";
		if (a != "") {
			$(this).css({
				"background-image": "url(" + a + ")",
			});
		}
	});
}

function click_a_delete_record() {
	return confirm("Xác nhận xóa bản ghi này?");
}

function click_a_restore_record() {
	return true;
	//return confirm('Xác nhận phục hồi bản ghi này?');
}

function click_a_remove_record() {
	return confirm("Xác nhận XÓA hoàn toàn bản ghi này?");
}

function click_delete_record() {
	if ($("#is_deleted").length !== 1) {
		console.log("%c ERROR is_deleted.length", "color: red;");
	}

	if (click_a_delete_record() === false) {
		return false;
	}

	$("#is_deleted").val(1);
	document.admin_global_form.submit();

	// hủy lệnh nếu code có lỗi
	setTimeout(function () {
		$("#is_deleted").val(0);
	}, 600);
}

function click_duplicate_record() {
	if ($("#is_duplicate").length !== 1) {
		WGR_alert("ERROR is_duplicate.length", "warning");
		return false;
	}

	if (confirm("Bạn thực sự muốn nhân bản bản ghi này?") === false) {
		return false;
	}

	$("#is_duplicate").val(1);
	document.admin_global_form.submit();

	// hủy lệnh nếu code có lỗi
	setTimeout(function () {
		$("#is_duplicate").val(0);
	}, 600);
}

// phần thiết lập thông số của size -> chỉnh về 1 định dạng
function convert_size_to_one_format() {
	jQuery(
		"#post_meta_custom_size, #term_meta_custom_size, #data_cf_posts_size, #data_cf_products_size, #term_meta_taxonomy_custom_post_size, #data_main_banner_size, #data_second_banner_size"
	)
		.off("change")
		.change(function () {
			var a = jQuery(this).val() || "";
			a = jQuery.trim(a);
			if (a != "") {
				// kích thước dùng chung
				if (a.split("%").length == 3) {
					//
				} else {
					a = a.replace(/\s/g, "");

					// kích thước tự động thì cũng bỏ qua luôn
					if (a == "auto" || a == "full") {
						//
					} else {
						// nếu có dấu x -> chuyển về định dạng của Cao/ Rộng
						if (a.split("x").length > 1) {
							a = a.split("x");

							if (a[0] == a[1]) {
								a = 1;
							} else {
								a = a[1] + "/" + a[0];
							}
						}
						a = a.toString().replace(/[^0-9\/]/g, "");
					}
				}

				jQuery(this).val(a);
			}
		})
		.off("blur")
		.blur(function () {
			jQuery(this).change();
		});

	jQuery(".fixed-width-for-config")
		.off("change")
		.change(function () {
			var a = jQuery(this).val() || "";
			if (a != "") {
				a = a.replace(/\s/g, "");

				if (a != "") {
					a = a * 1;

					// nếu giá trị nhập vào nhỏ hơn 10 -> tính toán tự động số sản phẩm trên hàng theo kích thước tiêu chuẩn
					if (a < 10) {
						// lấy kích thước tiêu chuẩn
						var b = jQuery(this).attr("data-width") || "";
						if (b != "") {
							// tính toán
							jQuery(this).val(Math.ceil(b / a) - 5);
						}
					}
				}
			}
		})
		.off("blur")
		.blur(function () {
			jQuery(this).change();
		});
}

/*
 * tạo menu actived cho admin
 */
function remove_last_url_segment(w) {
	//console.log(w);
	if (w.substr(w.length - 1) == "/") {
		w = w.substr(0, w.length - 1);
		//console.log(w);
	}
	w = w.split("/");
	//console.log(w);
	if (w.length > 1) {
		//console.log(w);
		w[w.length - 1] = "";
		//console.log(w);
		return w.join("/");
	}
	return "";
}

function get_last_url_segment(a) {
	return g_func.non_mark_seo(a);
}

// thêm class active cho menu -> nếu có thì trả về true
function set_last_url_segment(last_w) {
	console.log(last_w);

	//
	$('#sidebar a[data-segment="' + last_w + '"]')
		.parents("li")
		.addClass("active");

	// nếu có rồi thì không cần đoạn so khớp đằng sau nữa
	if ($("#sidebar li.active").length > 0) {
		console.log("active for admin menu by segment:", last_w);
		return true;
	}
	return false;
}

//
var loading_term_select_option = {};
var arr_all_taxonomy = {};

function load_term_select_option(a, jd, _callBack, max_i) {
	if (arguments.callee.caller !== null) {
		console.log("Call in: " + arguments.callee.caller.name.toString());
	}
	//console.log(a);
	//console.log(arr_all_taxonomy);

	// nếu term này được nạp rồi thì chờ đợi
	if (typeof loading_term_select_option[a] != "undefined") {
		if (typeof max_i != "number") {
			max_i = 100;
		} else if (max_i < 0) {
			console.log("%c max_i in load_term_select_option", "color: red;");
			return false;
		}

		//
		if (typeof arr_all_taxonomy[a] != "undefined") {
			console.log("%c using arr_all_taxonomy", "color: blue;");
			if (typeof _callBack == "function") {
				_callBack(arr_all_taxonomy[a], jd);
			}
			return false;
		}

		//
		setTimeout(function () {
			return load_term_select_option(a, jd, _callBack, max_i - 1);
		}, 500);
		return false;
	}
	loading_term_select_option[a] = true;

	//
	jQuery.ajax({
		type: "POST",
		url: "admin/asjaxs/get_taxonomy_by_taxonomy",
		dataType: "json",
		//crossDomain: true,
		data: {
			taxonomy: a,
			lang_key: typeof post_lang_key != "undefined" ? post_lang_key : "",
		},
		timeout: 33 * 1000,
		error: function (jqXHR, textStatus, errorThrown) {
			jQueryAjaxError(jqXHR, textStatus, errorThrown, new Error().stack);
		},
		success: function (data) {
			//console.log(data);
			//console.log(data.length);

			//
			if (typeof data.error != "undefined") {
				console.log("%c " + data.error, "color: red;");
			} else {
				arr_all_taxonomy[a] = data;

				//
				if (typeof _callBack == "function") {
					_callBack(data, jd);
				} else {
					console.log(data);
				}
				//console.log('arr_all_taxonomy:', arr_all_taxonomy);
			}
		},
	});
}

/*
 * tạo danh sách option cho các select term
 * limit_deep: giới hạn độ sâu của vòng lặp tìm các mảng con (child-data)
 * current_deep: độ sâu hiện tại của vòng lặp -> dùng để thoát vòng lặp nếu đạt đủ độ sâu cần thiết
 */
function create_term_select_option(arr, space, limit_deep, current_deep) {
	if (arguments.callee.caller !== null) {
		console.log("Call in: " + arguments.callee.caller.name.toString());
	}
	//console.log(arr);

	//
	if (typeof space == "undefined") {
		space = "";
	}
	if (typeof limit_deep != "number") {
		limit_deep = 999;
	}
	if (typeof current_deep != "number") {
		current_deep = 0;
	}

	//
	var str = "";
	for (var i = 0; i < arr.length; i++) {
		if (
			typeof arr[i].term_shortname == "undefined" ||
			arr[i].term_shortname == ""
		) {
			arr[i].term_shortname = arr[i].name;
		}

		//
		if (typeof arr[i].count != "undefined" && arr[i].count * 1 > 0) {
			arr[i].term_shortname += " (" + arr[i].count + ")";
		}

		//
		str +=
			'<option data-count="' +
			arr[i].count +
			'" value="' +
			arr[i].term_id +
			'">' +
			space +
			arr[i].term_shortname +
			"</option>";

		//
		if (arr[i].child_term.length > 0 && current_deep < limit_deep) {
			str += create_term_select_option(
				arr[i].child_term,
				"&#160 &#160 " + space,
				limit_deep,
				current_deep + 1
			);
		}
	}
	//console.log(str);

	//
	return str;
}

/*
 * chức năng select all user và chỉnh sửa nhanh
 */
var arr_check_checked_all = [];

function get_check_checked_all_value() {
	$(".input-checkbox-control").parents("tr").removeClass("redcolor");

	//
	arr_check_checked_all = [];
	$(".input-checkbox-control").each(function () {
		if ($(this).is(":checked")) {
			arr_check_checked_all.push($(this).val());
			$(this).parents("tr").addClass("redcolor");
		}
	});
	//console.log(arr_check_checked_all);

	//
	if (arr_check_checked_all.length > 0) {
		$(".quick-edit-form").fadeIn();
	} else {
		$(".quick-edit-form").fadeOut();
	}
}

//
function action_for_check_checked_all() {
	$(".input-checkbox-all").change(function () {
		// checked cho tất cả select liên quan
		$(".input-checkbox-control").prop("checked", $(this).is(":checked"));
		get_check_checked_all_value();
	});
	//$('.input-checkbox-all').prop('checked', true).trigger('change');

	// select từng input
	$(".input-checkbox-control").change(function () {
		get_check_checked_all_value();
	});
}

// khi thay đổi checkbox trong form submit
// -> thì bổ sung hoặc xóa 1 input hidden tương ứng -> do checkbox uncheck không nhận giá trị khi submit
function for_admin_global_checkbox(max_i) {
	if (typeof max_i != "number") {
		max_i = 100;
	} else if (max_i < 0) {
		return false;
	}

	//
	if ($('form#admin_global_form input[type="checkbox"]').length == 0) {
		setTimeout(function () {
			for_admin_global_checkbox(max_i - 1);
		}, 100);
		return false;
	}

	//
	setTimeout(function () {
		$('form#admin_global_form input[type="checkbox"]').change(function () {
			var a = $(this).attr("name") || "";
			//console.log(a);

			// chỉ xử lý với các checkbox của data chính
			if (a.split("data[").length > 1) {
				// xử lý phần tên -> bỏ giá trị kiểu mảng đi
				var default_a = a.split("]")[0];
				default_a = default_a.replace("data[", "");
				default_a = "data[default_post_data][" + default_a + "]";
				//console.log(default_a);
				if (
					$(
						'form#admin_global_form input.remove-if-checkbox-checked[name="' +
							default_a +
							'"]'
					).length == 0
				) {
					console.log("add hidden input:", default_a);
					// -> thêm 1 input hidden để xóa giá trị lúc submit
					$("form#admin_global_form").prepend(
						'<input type="hidden" name="' +
							default_a +
							'" value="" class="remove-if-checkbox-checked" />'
					);
				}
			}
		});
	}, 2000);
}

/*
 * sau khi XÓA sản phẩm thành công thì xử lý ẩn bản ghi bằng javascript
 */
function done_delete_restore(id, redirect_to) {
	if ($('#admin_main_list tr[data-id="' + id + '"]').length > 0) {
		$('#admin_main_list tr[data-id="' + id + '"]').fadeOut();
	} else if ($('#admin_main_list li[data-id="' + id + '"]').length > 0) {
		$('#admin_main_list li[data-id="' + id + '"]').fadeOut();
	} else if (typeof redirect_to != "undefined" && redirect_to != "") {
		window.location = redirect_to;
	}
}

/*
 * chức năng XÓA, RESTORE... nhiều bản ghi 1 lúc
 */
function action_delete_restore_checked(
	method_control,
	method_name,
	controller_slug
) {
	if (confirm("Xác nhận " + method_name + " các bản ghi đã chọn!") !== true) {
		return false;
	}
	//console.log(arr_check_checked_all);

	//
	jQuery.ajax({
		type: "POST",
		url: "admin/" + controller_slug + "/" + method_control,
		dataType: "json",
		data: {
			ids: arr_check_checked_all.join(","),
		},
		success: function (data) {
			console.log(data);
			console.log(arr_check_checked_all);

			//
			if (typeof data.error != "undefined") {
				WGR_alert(data.error + " - Code: " + data.code, "error");
			} else if (typeof data.result != "undefined") {
				if (data.result === true) {
					WGR_alert(method_name + " các bản ghi đã chọn thành công");
					console.log(arr_check_checked_all);

					//
					for (var i = 0; i < arr_check_checked_all.length; i++) {
						// bỏ check cho các checkbox
						$(
							'.input-checkbox-control[value="' +
								arr_check_checked_all[i] +
								'"]'
						)
							.hide()
							.remove();
						// xóa luôn TR đi
						$('#admin_main_list tr[data-id="' + arr_check_checked_all[i] + '"]')
							.hide()
							.remove();
					}
					//arr_check_checked_all = [];
					$(".input-checkbox-all").prop("checked", false);
					get_check_checked_all_value();
				} else {
					WGR_alert(
						"Có lỗi trong quá trình " + method_name + " bản ghi",
						"warning"
					);
					//console.log(data);
				}
			}
		},
	});
}

function click_delete_checked(controller_slug) {
	action_delete_restore_checked("delete_all", "Lưu trữ", controller_slug);
}

function click_restore_checked(controller_slug) {
	action_delete_restore_checked("restore_all", "Khôi phục", controller_slug);
}

function click_remove_checked(controller_slug) {
	action_delete_restore_checked("remove_all", "XÓA", controller_slug);
}

// từ ID -> địa chỉ email
function action_each_to_email() {
	var ids = [];

	// lấy các ID có
	$(".each-to-email").each(function () {
		var a = $(this).attr("data-id") || "";

		if (a != "") {
			ids.push(a);
		}
		//console.log(a);
	});
	// nếu không có ID nào cẩn xử lý thì bỏ qua đoạn sau luôn
	if (ids.length == 0) {
		return false;
	}

	// chạy ajax nạp dữ liệu của taxonomy
	jQuery.ajax({
		type: "POST",
		url: "admin/asjaxs/get_users_by_ids",
		dataType: "json",
		//crossDomain: true,
		data: {
			ids: ids.join(","),
		},
		timeout: 33 * 1000,
		error: function (jqXHR, textStatus, errorThrown) {
			jQueryAjaxError(jqXHR, textStatus, errorThrown, new Error().stack);
		},
		success: function (data) {
			//console.log(data);

			//
			for (var i = 0; i < data.length; i++) {
				$('.each-to-email[data-id="' + data[i].ID + '"]').html(
					data[i].user_email
				);
			}
			$(".each-to-email")
				.addClass("each-to-email-done")
				.removeClass("each-to-email");
		},
	});
}

function WGR_body_opacity(val) {
	$("body").css({
		opacity: typeof val != "number" ? 1 : val,
	});
	return true;
}
