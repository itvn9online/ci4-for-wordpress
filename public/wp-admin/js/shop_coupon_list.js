$(document).ready(function () {
	// hiển thị tiền tệ
	$(".discount_type-to-currency").each(function () {
		let a = $(this).data("type") || "";
		if (a == "percent") {
			$(this).append("%");
		} else {
			$(this).addClass("ebe-currency");
		}
	});

	// cảnh báo ngày hết hạn
	let date_now = Date.now(),
		mot_ngay = 86400;
	$(".expiry_date-to-note").each(function () {
		let a = $.trim($(this).html());
		if (a != "" && a.length == 10 && a.split("-").length == 3) {
			let end_date = new Date(a).getTime();

			if (end_date < date_now) {
				$(this).addClass("redcolor");
			} else {
				$(this).addClass("greencolor");

				//
				let con_lai = Math.ceil((end_date - date_now) / 1000 / mot_ngay);
				$(this).append(" (~" + con_lai + " day)");
			}
		}
	});
});
