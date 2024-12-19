jQuery(document).ready(function () {
	// thông báo ngày hết hạn
	setTimeout(() => {
		let date_now = Date.now(),
			mot_ngay = 86400;
		jQuery("#_term_meta_expiry_date").change(function () {
			let a = jQuery.trim(jQuery(this).val()),
				str = "",
				cl = "";
			// console.log(a);
			if (a != "" && a.length == 10 && a.split("-").length == 3) {
				let end_date = new Date(a).getTime();

				if (end_date < date_now) {
					cl = "redcolor";
				} else {
					cl = "greencolor";

					//
					let con_lai = Math.ceil((end_date - date_now) / 1000 / mot_ngay);
					str = " (~" + con_lai + " day)";
				}
			}

			//
			if (jQuery(".note-term_meta_expiry_date").length < 1) {
				jQuery("#_term_meta_expiry_date").before(
					'<span class="note-term_meta_expiry_date"></span>'
				);
			}
			jQuery(".note-term_meta_expiry_date")
				.html(str)
				.removeClass("redcolor")
				.removeClass("greencolor")
				.addClass(cl);
		});
		jQuery("#_term_meta_expiry_date").trigger("change");

		jQuery("#term_meta_expiry_date").change(function () {
			jQuery("#_term_meta_expiry_date").trigger("change");
		});

		//
		jQuery("#term_meta_coupon_amount").change(function () {
			let a = jQuery.trim(jQuery(this).val()),
				currency = jQuery("#term_meta_discount_type").val() || "";
			// console.log(a);

			//
			if (jQuery(".note-term_meta_coupon_amount").length < 1) {
				jQuery("#term_meta_coupon_amount").after(
					'<span class="note-term_meta_coupon_amount"></span>'
				);
			}
			jQuery(".note-term_meta_coupon_amount").html(
				currency == "percent"
					? " %"
					: ' <span class="ebe-currency">&nbsp;</span>'
			);
		});
		jQuery("#term_meta_coupon_amount").trigger("change");

		jQuery("#term_meta_discount_type").change(function () {
			jQuery("#term_meta_coupon_amount").trigger("change");
		});
	}, 1000);
});
