jQuery(document).ready(function () {
	// hiển thị tiền tệ
	jQuery(".discount_type-to-currency").each(function () {
		let a = jQuery(this).data("type") || "";
		if (a == "percent") {
			jQuery(this).append("%");
		} else {
			jQuery(this).addClass("ebe-currency");
		}
	});

	// cảnh báo ngày hết hạn
	let date_now = Date.now(),
		mot_ngay = 86400;
	jQuery(".expiry_date-to-note").each(function () {
		let a = jQuery.trim(jQuery(this).html());
		if (a != "" && a.length == 10 && a.split("-").length == 3) {
			let end_date = new Date(a).getTime();

			if (end_date < date_now) {
				jQuery(this).addClass("redcolor");
			} else {
				jQuery(this).addClass("greencolor");

				//
				let con_lai = Math.ceil((end_date - date_now) / 1000 / mot_ngay);
				jQuery(this).append(" (~" + con_lai + " day)");
			}
		}
	});
});
