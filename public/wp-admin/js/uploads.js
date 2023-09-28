$("#upload_image").change(function () {
	//console.log("TEST upload...");
	//console.log($(this).val());
	//return false;
	$("body").css({
		opacity: 0.33,
	});
	document.frm_global_upload.submit();
});

//
$(".admin-search-form select").change(function () {
	document.frm_admin_search_controller.submit();
});

//
$("a.click-set-mode").click(function () {
	var a = $(this).attr("data-mode") || "";

	//
	if (a != "") {
		$("#mode_filter").val(a);
		document.frm_admin_search_controller.submit();
	}
});
