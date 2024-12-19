jQuery("#quick_add_menu select").change(function () {
	var v = jQuery(this).val() || "";
	if (v != "") {
		var base_url = jQuery("base").attr("href") || "";
		if (base_url != "") {
			v = v.replace(base_url, "./");
		}
	}
	jQuery("#post_meta_url_redirect").val(v).focus();
});
