//
function ajax_push_image_to_server(params, __callBack, __errorCallBack) {
	if (typeof params != "object") {
		WGR_alert("typeof params is not OBJECT!", "error");
		return false;
	}

	// các tham số bắt buộc
	var require_params = [
		// action xử lý việc upload
		"action",
		// dữ liệu ảnh để upload
		"data",
		// thiết lập file name
		"file_name",
		// input select file đầu vào -> dùng để reset form sau khi upload thành công
		"input_file",
	];
	for (var i = 0; i < require_params.length; i++) {
		if (
			typeof params[require_params[i]] == "undefined" ||
			params[require_params[i]] == ""
		) {
			WGR_alert(
				"ERROR! Parameter " +
					require_params[i].replace(/\_/gi, " ") +
					" is EMPTY...",
				"error"
			);
			return false;
		}
	}

	// các tham số không bắt buộc -> không có thì để trống -> không phải làm gì
	var option_params = [
		// thiết lập ảnh làm bg sau khi upload thành công
		"set_bg",
		// thay đổi src cho img sau khi upload thành công
		"set_src",
		// thiết lập thumbnail
		"set_thumb",
		"set_webp",
		// thiết lập ảnh lớn
		"set_val",
		// thiết lập ảnh gốc
		"set_origin",
		// nếu có tham số ảnh xem trước -> dùng ảnh này thay cho val
		"set_preview",
		// thời gian chỉnh sửa file -> để tránh trùng lặp
		"last_modified",
		// định dạng file -> dùng để xác định kiểu convert -> mặc định là kiểu JPG
		"mime_type",
	];
	for (var i = 0; i < option_params.length; i++) {
		if (typeof params[option_params[i]] == "undefined") {
			params[option_params[i]] = "";
		}
	}

	//
	if (params["mime_type"].split("/")[0] != "image") {
		WGR_alert(
			"Định dạng file chưa được hỗ trợ: " + params["mime_type"],
			"error"
		);
		WGR_alert("Chức kéo thả hiện tại upload được file ảnh!", "warning");
		return false;
	}

	//
	if (WGR_config.cf_tester_mode > 0) console.log("params:", params);

	// định dạng file name về 1 mối chuẩn chỉ
	var file_name = params["file_name"].split(".");
	if (file_name.length > 1) {
		file_name[file_name.length - 1] = "";
		file_name = file_name.join(".");
	} else {
		file_name = file_name[0];
	}
	file_name = g_func.non_mark_seo(file_name);
	if (WGR_config.cf_tester_mode > 0) console.log("file name:", file_name);

	//
	var img = document.createElement("img");
	img.src = params["data"];

	//
	if (typeof params["img_max_width"] != "number") {
		params["img_max_width"] = 999;
	} else if (params["img_max_width"] < 90) {
		params["img_max_width"] = 90;
	} else if (params["img_max_width"] > 1366) {
		params["img_max_width"] = 1366;
	}

	//
	if (typeof params["img_max_height"] != "number") {
		params["img_max_height"] = params["img_max_width"];
	}

	//
	setTimeout(function () {
		var width = img.width;
		var height = img.height;
		if (width > 0 && height > 0) {
			var MAX_WIDTH = params["img_max_width"];
			var MAX_HEIGHT = params["img_max_height"];
			var has_resize = false;
			if (width > height) {
				if (width > MAX_WIDTH) {
					height *= MAX_WIDTH / width;
					width = MAX_WIDTH;
					has_resize = true;
				}
			} else {
				if (height > MAX_HEIGHT) {
					width *= MAX_HEIGHT / height;
					height = MAX_HEIGHT;
					has_resize = true;
				}
			}
			if (WGR_config.cf_tester_mode > 0 && has_resize === true) {
				console.log("has resize:", params["img_max_width"]);
			}
			width = Math.ceil(width);
			height = Math.ceil(height);
			if (has_resize === true) {
				var canvas = document.createElement("canvas");
				canvas.width = width;
				canvas.height = height;
				canvas
					.getContext("2d")
					.drawImage(img, 0, 0, canvas.width, canvas.height);
				console.log("mime type:", params["mime_type"]);
				// nếu là png thì cho phép giữ nguyên png
				if (params["mime_type"] == "image/png") {
					var dataurl = canvas.toDataURL("image/png");
				}
				// còn lại cho hết sang jpg
				else {
					var dataurl = canvas.toDataURL("image/jpeg", 1.0);
				}
				params["data"] = dataurl;
			}
		}
		if (WGR_config.cf_tester_mode > 0) console.log(params);

		//
		WGR_alert("Updating...");

		//
		$.ajax({
			type: "POST",
			url: params["action"],
			data: {
				img: params["data"],
				file_name: file_name,
				mime_type: params["mime_type"],
				last_modified: params["last_modified"],
				update_avt: params["update_avt"],
			},
			timeout: 33 * 1000,
			error: function (jqXHR, textStatus, errorThrown) {
				WGR_alert("ERROR uploading... Please re-check!", "error");

				//
				jQueryAjaxError(jqXHR, textStatus, errorThrown, new Error().stack);

				// nếu có function báo lỗi quá trình upload thì báo ở đây
				if (typeof __errorCallBack == "function") {
					__errorCallBack();
				}
			},
			success: function (data) {
				//
				if (typeof data.img_large != "undefined") {
					//console.log(typeof data.img_large);
					data.img_large += "?v=" + data.last_modified;
					data.img_thumb += "?v=" + data.last_modified;
					data.img_webp += "?v=" + data.last_modified;

					//
					var show_img = "";

					// nếu dùng thumbnail thì thiết lập tham số set thumbnail
					if (params["set_webp"] != "") {
						$(params["set_webp"]).val(data.img_webp);
						show_img = data.img_webp;
					} else if (params["set_thumb"] != "") {
						$(params["set_thumb"]).val(data.img_thumb);
						show_img = data.img_thumb;
					}
					// muốn dùng ảnh cỡ lớn thì thiết lập set val
					if (params["set_val"] != "") {
						$(params["set_val"]).val(data.img_large);
						show_img = data.img_large;
					}
					// muốn dùng ảnh gốc thì thiết lập set val
					if (params["set_origin"] != "") {
						$(params["set_origin"]).val(data.img);
						show_img = data.img;
					}
					// nếu có tham số ảnh xem trước -> dùng ảnh này thay cho val
					if (params["set_preview"] == "origin") {
						show_img = data.img;
					} else if (params["set_preview"] == "large") {
						show_img = data.img_large;
					} else if (params["set_preview"] == "thumb") {
						show_img = data.img_thumb;
					}

					//
					if (params["set_bg"] != "") {
						$(params["set_bg"]).css({
							"background-image": "url(" + show_img + ")",
						});
					}

					//
					if (params["set_src"] != "") {
						$(params["set_src"]).attr({
							src: show_img,
						});
					}
				}

				//
				if (typeof data.update_avt != "undefined" && data.update_avt * 1 > 0) {
					WGR_alert("Updated!");
				}

				//
				$(params["input_file"]).val("");

				//
				if (typeof __callBack == "function") {
					__callBack(data);
				} else {
					console.log("%c __callBack is not FUNCTION", "color: orange;");
				}

				//
				if (WGR_config.cf_tester_mode > 0) console.log(data);
			},
		});
	}, 200);
}
