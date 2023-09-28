$("#quick_add_menu select").change(function () {
	var v = $(this).val() || "";
	if (v != "") {
		var base_url = $("base ").attr("href") || "";
		if (base_url != "") {
			v = v.replace(base_url, "./");
		}
	}
	$("#post_meta_url_redirect").val(v).focus();
});
