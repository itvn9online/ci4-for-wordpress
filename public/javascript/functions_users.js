function fix_textarea_height() {
	jQuery(".fix-textarea-height textarea, textarea.fix-textarea-height")
		.off("change")
		.change(function () {
			var a = jQuery(this).attr("data-resize") || "",
				min_height = jQuery(this).attr("data-min-height") || 60,
				add_height = jQuery(this).attr("data-add-height") || 20;
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
	if ($(for_id + " option").length < 10 || $(for_id).hasClass("has-select2")) {
		return false;
	}
	$(for_id).addClass("has-select2");
	$(for_id).select2();
}
