//
function add_period__price() {
	//console.log(checkout_config.period_price.length);
	if (checkout_config.period_price.length > 0) {
		checkout_config.period_price.push(0);
		checkout_config.period_discount.push(0);
		checkout_config.period_bonus.push(0);
	} else {
		checkout_config.period_price = [0];
		checkout_config.period_discount = [0];
		checkout_config.period_bonus = [0];
	}

	//
	before_period_price_html();

	//
	jQuery("table.html-period-price tr:last input:first").focus();

	//
	return false;
}

function remove_period__price(i) {
	//
	if (typeof checkout_config.period_price[i] == "undefined") {
		WGR_alert("Cannot be determined properties #" + i, "error");
		return false;
	}
	// nếu mảng có giá trị -> hiển thị cảnh báo trước khi xóa
	if (
		checkout_config.period_price[i].toString() != "0" ||
		checkout_config.period_discount[i].toString() != "0" ||
		checkout_config.period_bonus[i].toString() != "0"
	) {
		if (confirm("Xác nhận xóa thuộc tính!") === false) {
			return false;
		}
	}
	// gán null cho các phần tử
	checkout_config.period_price[i] = null;
	checkout_config.period_discount[i] = null;
	checkout_config.period_bonus[i] = null;
	console.log(checkout_config);
	// loại bỏ các phần tử null
	checkout_config.period_price = jQuery.grep(
		checkout_config.period_price,
		(n) => n == 0 || n
	);
	checkout_config.period_discount = jQuery.grep(
		checkout_config.period_discount,
		(n) => n == 0 || n
	);
	checkout_config.period_bonus = jQuery.grep(
		checkout_config.period_bonus,
		(n) => n == 0 || n
	);
	console.log(checkout_config);

	//
	before_period_price_html();

	//
	return false;
}

function before_period_price_html() {
	create_period_price_html(
		checkout_config.period_price,
		checkout_config.period_discount,
		checkout_config.period_bonus
	);
}

function create_period_price_html(period_price, period_discount, period_bonus) {
	console.log(period_price);
	console.log(period_discount);
	console.log(period_bonus);

	//
	let str = [
		"<tr>",
		"<th>Giá trị</th>",
		"<th>Giảm giá</th>",
		"<th>Tặng thêm</th>",
		"<th>&nbsp;</th>",
		"</tr>",
	].join("");

	//
	str += (function (period_price, period_discount, period_bonus) {
		//
		let htm = "";
		for (let i = 0; i < period_price.length; i++) {
			if (period_price[i] === null) {
				continue;
			}

			//
			htm += "<tr>";
			htm +=
				'<td><input type="text" onchange="return change_period__price(this,' +
				i +
				');" value="' +
				period_price[i] +
				'"></td>';
			htm +=
				'<td><input type="text" onchange="return change_period__discount(this,' +
				i +
				');" value="' +
				period_discount[i] +
				'"></td>';
			htm +=
				'<td><input type="text" onchange="return change_period__bonus(this,' +
				i +
				');" value="' +
				period_bonus[i] +
				'"></td>';
			htm +=
				'<td><button type="button" onclick="return remove_period__price(' +
				i +
				');" class="btn btn-danger btn-small">Xóa</button></td>';
			htm += "</tr>";
		}

		//
		return htm;
	})(period_price, period_discount, period_bonus);

	//
	str = '<table class="html-period-price">' + str + "</table>";
	str +=
		'<div class="html-period-price"><button type="button" onclick="return add_period__price();" class="btn btn-primary btn-small"><i class="fa fa-plus"></i> Thêm mới</button></div>';

	//
	jQuery(".html-period-price").remove();
	jQuery("#data_period_price").before(str);
	// giả lập quá trình change để đồng bộ lại dữ liệu
	jQuery("table.html-period-price input").each(function () {
		jQuery(this).trigger("change");
	});
}

function change_period__value(k, i, v) {
	console.log(v);
	checkout_config[k][i] = v;
	console.log(checkout_config);

	//
	jQuery("#data_" + k)
		.val(checkout_config[k].join(";"))
		.trigger("change");
}

function sync_period__price(v) {
	// xóa bỏ mọi thể loại khoảng trắng
	v = v.replace(/\s|\t|\n/, "");
	// trim
	v = jQuery.trim(v.toString());
	// xóa bỏ số 0 ở đầu
	v = v.replace(/^0/, "");
	if (v == "") {
		v = "0";
	} else if (!isNaN(v * 1)) {
		v = g_func.number_format(v);
	}
	return v;
}

function change_period__price(obj, i) {
	obj.value = sync_period__price(obj.value);
	return change_period__value("period_price", i, obj.value);
}

function change_period__discount(obj, i) {
	obj.value = sync_period__price(obj.value);
	return change_period__value("period_discount", i, obj.value);
}

function change_period__bonus(obj, i) {
	obj.value = sync_period__price(obj.value);
	return change_period__value("period_bonus", i, obj.value);
}

//
jQuery(document).ready(function () {
	/**
	 * nạp danh sách ngân hàng
	 * Danh sách ngân hàng được tải định kỳ tại đây: https://api.vietqr.io/v2/banks
	 * https://www.vietqr.io/danh-sach-api/api-danh-sach-ma-ngan-hang
	 */
	jQuery.ajax({
		type: "GET",
		url: "wp-includes/libraries/banks-vietqr.json",
		dataType: "json",
		//crossDomain: true,
		timeout: 33 * 1000,
		error: function (jqXHR, textStatus, errorThrown) {
			jQueryAjaxError(jqXHR, textStatus, errorThrown, new Error().stack);
		},
		success: function (data) {
			console.log(data);

			//
			if (typeof data.data != "undefined") {
				let str = "",
					a = data.data;

				for (let i = 0; i < a.length; i++) {
					str +=
						'<option value="' +
						a[i].bin +
						'" data-logo="' +
						a[i].logo +
						'" data-swift_code="' +
						a[i].swift_code +
						'" data-name="' +
						a[i].name +
						'" data-short_name="' +
						a[i].short_name +
						'" data-code="' +
						a[i].code +
						'">' +
						a[i].shortName +
						" (" +
						a[i].code +
						") - " +
						a[i].name +
						"</option>";
				}
				jQuery("#data_bank_bin_code").append(str);
				jQuery("#data_bank_bin_code").change(function () {
					jQuery("#data_bank_logo")
						.val(jQuery("option:selected", this).data("logo") || "")
						.trigger("blur");
					jQuery("#data_bank_swift_code")
						.val(jQuery("option:selected", this).data("swift_code") || "")
						.trigger("blur");
					jQuery("#data_bank_name")
						.val(jQuery("option:selected", this).data("name") || "")
						.trigger("blur");
					jQuery("#data_bank_short_name")
						.val(jQuery("option:selected", this).data("short_name") || "")
						.trigger("blur");
					jQuery("#data_bank_code")
						.val(jQuery("option:selected", this).data("code") || "")
						.trigger("blur");
				});

				//
				let select_bank = jQuery("#data_bank_bin_code").data("select") || "";
				if (select_bank != "") {
					jQuery("#data_bank_bin_code").val(select_bank).trigger("change");
					//WGR_set_prop_for_select('#data_bank_bin_code');
				}

				//
				MY_select2("#data_bank_bin_code");
			}
		},
	});

	// tạo select các bước giá
	if (jQuery("#data_period_price").length > 0) {
		jQuery("#data_period_price").attr({
			type: "hidden",
		});
		before_period_price_html();
	}

	// chức năng bấm và copy link callback cho casso
	jQuery("#data_autobank_token")
		.attr({
			readonly: true,
			ondblclick: "click2Copy(this);",
		})
		.after(
			'<div><input type="text" value="' +
				web_link +
				'cassos/confirm" class="span10" onDblClick="click2Copy(this);" readonly="readonly" /></div>'
		)
		.dblclick(function () {
			jQuery(this).removeAttr("readonly");
		});

	// tạo mã token cho webhook casso
	jQuery("#data_autobank_token").after(
		'<div><button type="button" class="btn btn-info btn-small generate_autobank_token">Tạo token ngẫu nhiên 64 ký tự</button></div>'
	);
	// https://stackoverflow.com/questions/1349404/generate-random-string-characters-in-javascript
	jQuery(".generate_autobank_token").click(function () {
		let result = "",
			len = 64;
		const characters =
			"ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789";
		const charactersLength = characters.length;
		let counter = 0;
		while (counter < len) {
			result += characters.charAt(Math.floor(Math.random() * charactersLength));
			counter += 1;
		}

		//
		jQuery("#data_autobank_token")
			.val(result)
			.trigger("change")
			.trigger("dblclick");

		//
		return result;
	});

	//
	action_highlighted_code("#data_paypal_sdk_js");
});
