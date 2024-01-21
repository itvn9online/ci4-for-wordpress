$(document).ready(function () {
	$("#post_meta__regular_price, #post_meta__sale_price").change(function () {
		let a = $.trim($(this).val());
		if (a != "") {
			a = g_func.float_only(a);
			// console.log(a);
			if (a > 0) {
				$(this).val(g_func.money_format(a));
			} else {
				$(this).val("");
			}
		}
	});
	$("#post_meta__regular_price, #post_meta__sale_price").trigger("change");
});
