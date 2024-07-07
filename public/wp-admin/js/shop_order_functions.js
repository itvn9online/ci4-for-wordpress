function done_build_order_list() {
	//
	console.log("Tạo border cho đơn hàng theo từng ngày...", Math.random());
	let d = "",
		a = "";
	$("#admin_main_list tr").each(function () {
		a = $(this).data("post_date") || "";
		console.log(a, d);
		if (a != "") {
			if (d != "" && d != a) {
				$(this).addClass("border-post_date");
			}
			d = a;
		}
	});
}
