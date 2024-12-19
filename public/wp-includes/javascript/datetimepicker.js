/**
 * nạp datetimepicker theo cách Ếch Bay
 */
if (typeof datetimepicker_loaded == "undefined") {
	(function () {
		let s1 = document.createElement("script"),
			s0 = document.getElementsByTagName("script")[0];
		s1.async = true;
		s1.src =
			web_link +
			"wp-includes/thirdparty/datetimepicker/jquery.datetimepicker.js";
		//s1.src = web_link + 'wp-includes/thirdparty/datetimepicker-2.3.7/build/jquery.datetimepicker.min.js';
		//s1.charset = 'UTF-8';
		//s1.setAttribute('crossorigin', '*');
		s0.parentNode.insertBefore(s1, s0);
	})();

	//
	(function () {
		// Get HTML head element
		let head = document.getElementsByTagName("HEAD")[0],
			// Create new link Element
			link = document.createElement("link");

		// set the attributes for link element
		link.rel = "stylesheet";
		link.type = "text/css";
		link.href =
			web_link +
			"wp-includes/thirdparty/datetimepicker/jquery.datetimepicker.css";
		//link.href = web_link + 'wp-includes/thirdparty/datetimepicker-2.3.7/build/jquery.datetimepicker.min.css';

		// Append link element to HTML head
		head.appendChild(link);
	})();
}
var datetimepicker_loaded = true;
var datetime_default_format = "Y-m-d";

// hàm chuyển đổi date string sang timestamp sau khi close
function datetimepicker_onClose(input_name, input_id, type) {
	let pick_name = "picker_" + input_name;
	//console.log('pick name:', pick_name);

	let pick_id = "picker_" + input_id;
	//console.log('pick id:', input_id);

	//
	let input_ = jQuery("#" + input_id);

	// ẩn input đi
	input_.attr({
		readonly: true,
		type: "hidden",
	});

	//
	let val = input_.val() || "",
		new_date = "";
	if (val != "") {
		//console.log('val:', val);
		val *= 1;
		//console.log('val:', val);
		if (val > 0) {
			let tzoffset = new Date().getTimezoneOffset() * 60000; // offset in milliseconds
			//tzoffset = 0;
			new_date = new Date(val * 1000 - tzoffset).toISOString();
			//console.log('new date:', new_date);

			// lấy ngày tháng năm và giờ
			if (type == "datetime") {
				new_date = new_date.split(".")[0].replace("T", " ");
			}
			// date -> chỉ lấy ngày tháng năm
			else {
				new_date = new_date.split("T")[0];
			}
			//console.log('new date:', new_date);
		}
	}

	//
	input_.before(
		'<input type="text" class="' +
			(input_.attr("class") || "") +
			" ebe-jquery-ui-" +
			type +
			'" placeholder="' +
			(input_.attr("placeholder") || "") +
			'" name="' +
			pick_name +
			'" id="' +
			pick_id +
			'" value="' +
			new_date +
			'" autocomplete="off">'
	);

	//
	jQuery("#" + pick_id).change(function () {
		let a = jQuery(this).val() || "";
		//console.log('value:', a);

		//
		if (a != "") {
			// định dạng ngày giờ theo kiểu Việt Nam
			//let s = a.split(' ');
			//let s1 = s[0].split('-');
			//let s2 = s[1].split(':');
			//let d = new Date(s1[2], s1[1] - 1, s1[0], s2[0], s2[1], s2[2]);
			//jQuery('#' + input_id).val(Math.ceil(d.getTime() / 1000));

			// -> xác định giờ theo múi giờ hiện tại của user
			let tzoffset = 0;
			//tzoffset = (new Date()).getTimezoneOffset() * 60000; // offset in milliseconds
			console.log("tzoffset:", tzoffset);
			let time_stamp = 0;

			// định dạng ngày giờ theo chuẩn quốc tế
			if (a.length == 10) {
				// chuyển ngày sang timestamp
				time_stamp = Date.parse(a + " 00:00:00");
			} else {
				// chuyển ngày giờ sang timestamp
				time_stamp = Date.parse(a);
			}
			time_stamp *= 1;
			console.log("time_stamp:", time_stamp);
			if (tzoffset !== 0) {
				time_stamp += tzoffset;
				console.log("time_stamp:", time_stamp);
			}
			time_stamp = Math.ceil(time_stamp / 1000);
			// nếu là pick date -> đưa về cuối ngày -> để nếu có hết hạn thì cũng cuối ngày mới bị khóa
			if (jQuery("#" + input_id).hasClass("datepicker")) {
				time_stamp += 86400 - 1;
				console.log("time_stamp:", time_stamp);
			}
			jQuery("#" + input_id).val(time_stamp);

			//
			console.log(
				"Test date:",
				new Date(jQuery("#" + input_id).val() * 1000).toISOString()
			);
		} else {
			jQuery("#" + input_id).val("0");
		}
	});
}

// khởi tạo đối tượng cho các kiểu date picker
function create_dynamic_datepicker(type) {
	jQuery('input[type="' + type + '"], input.' + type + "picker").each(
		function () {
			let a = jQuery(this).attr("type") || "";
			//console.log('type:', a);

			// nếu đây là dạng số -> conver sang timestamp khi close
			//if (type != 'time' && a == 'number') {
			if (a == "number") {
				let input_name = jQuery(this).attr("name") || "";
				if (input_name != "") {
					//console.log('input name:', input_name);
					input_name = input_name.replace(/\[|\]/gi, "_");
					//console.log('input name:', input_name);

					//
					let input_id = jQuery(this).attr("id") || "";
					//console.log('input id:', input_id);
					if (input_id == "") {
						input_id = input_name;
					}

					// ẩn input đi
					jQuery(this).attr({
						id: input_id,
					});

					//
					datetimepicker_onClose(input_name, input_id, type);
				}
			} else {
				jQuery(this)
					.addClass("ebe-jquery-ui-" + type)
					.attr({
						type: "text",
						autocomplete: "off",
					});
			}
		}
	);

	//
	return jQuery(".ebe-jquery-ui-" + type).length;
}

//
function EBE_load_datetimepicker(max_i) {
	if (typeof max_i != "number") {
		max_i = 100;
	} else if (max_i < 0) {
		return false;
	}

	//
	if (typeof jQuery().datetimepicker != "function") {
		setTimeout(() => {
			EBE_load_datetimepicker(max_i - 1);
		}, 100);
		return false;
	}

	// lấy định dạng ngày tháng trong config nếu có
	let getDateFormat = function () {
		let a = datetime_default_format;
		if (
			typeof WGR_config != "undefined" &&
			typeof WGR_config.date_format != "undefined" &&
			WGR_config.date_format != ""
		) {
			a = WGR_config.date_format;
		}
		return a;
	};

	// chỉ lấy ngày tháng
	let MY_datepicker = function (id) {
		MY_datetimepicker(id, {
			timepicker: false,
			format: getDateFormat(),
		});
	};

	// chỉ lấy giờ
	let MY_timepicker = function (id) {
		MY_datetimepicker(
			id,
			{
				datepicker: false,
				format: "H:i",
			},
			true
		);
	};

	// lấy ngày tháng và giờ
	let MY_datetimepicker = function (id, op, time_only) {
		if (typeof op != "object") {
			op = {};
		}
		// console.log("id", id);

		//
		let default_op = {
			lang: jQuery("html").attr("lang") || "vi",
			// lang: "en",
			timepicker: true,
			formatTime: "H:i",
			//format: 'd-m-Y H:i:s'
			// format: "Y-m-d H:i:s",
			// showTimezone: true,
			// closeText: "Close",
			// currentText: "Today",
		};
		for (let x in default_op) {
			if (typeof op[x] == "undefined") {
				op[x] = default_op[x];
			}
		}
		let date_format = getDateFormat();
		// console.log("date format", date_format);
		if (typeof op.format == "undefined" || op.format == "") {
			op.format = date_format + " H:i:s";
		}
		// console.log("op:", op);

		//
		jQuery(id).datetimepicker(op);
		jQuery(id).attr({
			// hiển thị định dạng ngày tháng ra placeholder
			placeholder: op.format
				.replace("Y-m-d", "yyyy-mm-dd")
				.replace("m-d-Y", "mm-dd-yyyy")
				.replace("d-m-Y", "dd-mm-yyyy")
				.toUpperCase(),
			// "data-format": op.format,
		});

		// Nếu có định dạng lại ngày tháng về chuẩn Y-m-d của máy tính
		// console.log("time_only", time_only);
		if (typeof time_only == "undefined" || time_only !== true) {
			// console.log("date_format", date_format);
			if (date_format != datetime_default_format) {
				jQuery(id).each(function () {
					let input_name = jQuery(this).attr("name") || "";
					if (input_name != "") {
						let jd =
							jQuery(this).attr("id") ||
							Math.random().toString(32).replace(/\./gi, "_");
						jd = "_" + jd;

						// thêm input ẩn để thay thế input date time mặc định
						jQuery(this).after(
							'<input type="hidden" name="' +
								input_name +
								'" value="' +
								(jQuery(this).val() || "") +
								'" id="' +
								jd +
								'">'
						);

						//
						jQuery(this)
							.attr({
								"data-for": jd,
							})
							.removeAttr("name")
							.change(function () {
								// console.log(Math.random());
								let jd = jQuery(this).attr("data-for") || "";
								if (jd != "") {
									let date_format = getDateFormat();
									if (date_format != datetime_default_format) {
										let a = jQuery(this).val() || "",
											b = "",
											gio = "";
										if (a != "") {
											// cắt theo dấu cách xem có phần giờ không
											a = a.split(" ");
											if (a.length > 1) {
												// nếu có thì giữ lại phần giờ
												gio = " " + a[1];
											}
											// xử lý ngày tháng
											a = a[0].split("-");

											// US date format
											if (date_format == "m-d-Y") {
												// m-d-Y ---> Y-m-d
												b = [a[2], a[0], a[1]].join("-");
											} else if (date_format == "d-m-Y") {
												// other date format
												// d-m-Y ---> Y-m-d
												b = [a[2], a[1], a[0]].join("-");
											}
										}
										// console.log(b + gio);
										jQuery("#" + jd).val(b + gio);
									}
								}
							});
					}
				});
			}
		}

		// nếu định dạng ngày tháng không phải dạng mặc định
		if (date_format != datetime_default_format) {
			// xem dữ liệu hiện tại trong input có không
			jQuery(id).each(function () {
				let a = jQuery(this).val() || "";
				if (a != "" && a.length >= 10) {
					// let ts = Date.parse(a);
					// console.log(ts);
					// let tzoffset = new Date().getTimezoneOffset() * 60000; // offset in milliseconds
					// console.log(new Date(ts - tzoffset).toISOString());

					//
					a = a.split(" ");
					// console.log(a);
					let b = "",
						gio = "";
					if (a.length > 1) {
						// nếu có thì giữ lại phần giờ
						gio = " " + a[1];
					}
					// xử lý ngày tháng
					a = a[0].split("-");
					// console.log("a", a);

					// US date format
					if (date_format == "m-d-Y") {
						// Y-m-d ---> m-d-Y
						b = [a[1], a[2], a[0]].join("-");
					} else if (date_format == "d-m-Y") {
						// other date format
						// Y-m-d ---> d-m-Y
						b = [a[2], a[1], a[0]].join("-");
					}
					// console.log(b + gio);
					jQuery(this).val(b + gio);
				}
			});
		}
	};

	// pick date
	if (create_dynamic_datepicker("date") > 0) {
		MY_datepicker(".ebe-jquery-ui-date");
	}
	// pick date time
	if (create_dynamic_datepicker("datetime") > 0) {
		MY_datetimepicker(".ebe-jquery-ui-datetime");
	}
	// pick time
	if (create_dynamic_datepicker("time") > 0) {
		MY_timepicker(".ebe-jquery-ui-time");
	}
}
EBE_load_datetimepicker();
