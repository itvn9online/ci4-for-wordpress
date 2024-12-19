jQuery("#upload_image").change(function () {
	//console.log("TEST upload...");
	//console.log(jQuery(this).val());
	//return false;
	jQuery("body").css({
		opacity: 0.33,
	});
	document.frm_global_upload.submit();
});

//
jQuery(".admin-search-form select").change(function () {
	document.frm_admin_search_controller.submit();
});

//
jQuery("a.click-set-mode").click(function () {
	var a = jQuery(this).data("mode") || "";

	//
	if (a != "") {
		jQuery("#mode_filter").val(a);
		document.frm_admin_search_controller.submit();
	}
});
