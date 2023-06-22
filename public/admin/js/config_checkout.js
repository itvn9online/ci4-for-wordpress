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
	$("table.html-period-price tr:last input:first").focus();

	//
	return false;
}

function remove_period__price(i) {
	//
	if (typeof checkout_config.period_price[i] == "undefined") {
		WGR_alert("Không xác định được thuộc tính #" + i, "error");
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
	checkout_config.period_price = $.grep(
		checkout_config.period_price,
		(n) => n == 0 || n
	);
	checkout_config.period_discount = $.grep(
		checkout_config.period_discount,
		(n) => n == 0 || n
	);
	checkout_config.period_bonus = $.grep(
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
	var str = "";
	str += "<tr>";
	str += "<th>Giá trị</th>";
	str += "<th>Giảm giá</th>";
	str += "<th>Tặng thêm</th>";
	str += "<th>&nbsp;</th>";
	str += "</tr>";

	//
	str += (function (period_price, period_discount, period_bonus) {
		//
		var htm = "";
		for (var i = 0; i < period_price.length; i++) {
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
	$(".html-period-price").remove();
	$("#data_period_price").before(str);
	// giả lập quá trình change để đồng bộ lại dữ liệu
	$("table.html-period-price input").each(function () {
		$(this).trigger("change");
	});
}

function change_period__value(k, i, v) {
	console.log(v);
	checkout_config[k][i] = v;
	console.log(checkout_config);

	//
	$("#data_" + k)
		.val(checkout_config[k].join(";"))
		.trigger("change");
}

function sync_period__price(v) {
	// xóa bỏ mọi thể loại khoảng trắng
	v = v.replace(/\s|\t|\n/, "");
	// trim
	v = $.trim(v.toString());
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
$(document).ready(function () {
	/*
	 * nạp danh sách ngân hàng
	 * Danh sách ngân hàng được tải định kỳ tại đây: https://api.vietqr.io/v2/banks
	 * https://www.vietqr.io/danh-sach-api/api-danh-sach-ma-ngan-hang
	 */
	jQuery.ajax({
		type: "GET",
		url: "libraries/banks-vietqr.json",
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
				var str = "";
				var a = data.data;

				for (var i = 0; i < a.length; i++) {
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
				$("#data_bank_bin_code").append(str);
				$("#data_bank_bin_code").change(function () {
					$("#data_bank_logo")
						.val($("option:selected", this).attr("data-logo") || "")
						.trigger("blur");
					$("#data_bank_swift_code")
						.val($("option:selected", this).attr("data-swift_code") || "")
						.trigger("blur");
					$("#data_bank_name")
						.val($("option:selected", this).attr("data-name") || "")
						.trigger("blur");
					$("#data_bank_short_name")
						.val($("option:selected", this).attr("data-short_name") || "")
						.trigger("blur");
					$("#data_bank_code")
						.val($("option:selected", this).attr("data-code") || "")
						.trigger("blur");
				});

				//
				var select_bank = $("#data_bank_bin_code").attr("data-select") || "";
				if (select_bank != "") {
					$("#data_bank_bin_code").val(select_bank).trigger("change");
					//WGR_set_prop_for_select('#data_bank_bin_code');
				}

				//
				MY_select2("#data_bank_bin_code");
			}
		},
	});

	// tạo select các bước giá
	if ($("#data_period_price").length > 0) {
		$("#data_period_price").attr({
			type: "hidden",
		});
		before_period_price_html();
	}
});
