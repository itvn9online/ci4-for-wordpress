function done_build_order_list() {
	//
	console.log("Tạo border cho đơn hàng theo từng ngày...", Math.random());
	let d = "",
		a = "";
	jQuery("#admin_main_list tr").each(function () {
		a = jQuery(this).data("post_date") || "";
		console.log(a, d);
		if (a != "") {
			if (d != "" && d != a) {
				jQuery(this).addClass("border-post_date");
			}
			d = a;
		}
	});
}
