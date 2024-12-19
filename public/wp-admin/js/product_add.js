jQuery(document).ready(function () {
	jQuery("#post_meta__regular_price, #post_meta__sale_price").change(
		function () {
			let a = jQuery.trim(jQuery(this).val());
			if (a != "") {
				a = g_func.float_only(a);
				// console.log(a);
				if (a > 0) {
					jQuery(this).val(g_func.money_format(a));
				} else {
					jQuery(this).val("");
				}
			}
		}
	);
	jQuery("#post_meta__regular_price, #post_meta__sale_price").trigger("change");
});
