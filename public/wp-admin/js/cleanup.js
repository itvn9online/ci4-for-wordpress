function waiting_cleanup_cache() {
	jQuery("body").css({
		opacity: 0.1,
	});

	//
	jQuery("#target_eb_iframe").on("load", function () {
		jQuery("body").css({
			opacity: 1,
		});
	});
}
