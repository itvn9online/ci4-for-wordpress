function fix_textarea_height() {
	jQuery(".fix-textarea-height textarea, textarea.fix-textarea-height")
		.off("change")
		.change(function () {
			//var t = jQuery(this).val().split("\n");
			//console.log(t.length);
			jQuery(this).attr({ rows: jQuery(this).val().split("\n").length });

			// đoạn này bỏ -> sử dụng rows nó nét hơn
			var a = jQuery(this).data("resize") || "",
				min_height = jQuery(this).data("min-height") || 60,
				add_height = jQuery(this).data("add-height") || 20;
			//		console.log(min_height);

			if (a == "") {
				jQuery(this).height(20);

				//
				var new_height = jQuery(this).get(0).scrollHeight || 0;
				new_height -= 0 - add_height;
				if (new_height < min_height) {
					new_height = min_height;
				}

				//
				jQuery(this).height(new_height);

				//
				console.log(
					"Fix textarea height #" +
						(jQuery(this).attr("name") || jQuery(this).attr("id") || "NULL")
				);
			}
		})
		.off("click")
		.click(function () {
			jQuery(this).change();
		})
		.each(function () {
			jQuery(this).change();
		});
}

function MY_select2(for_id) {
	if (
		jQuery(for_id + " option").length < 10 ||
		jQuery(for_id).hasClass("has-select2")
	) {
		return false;
	}
	jQuery(for_id).addClass("has-select2");
	jQuery(for_id).select2();
}
