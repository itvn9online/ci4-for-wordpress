//
jQuery('.menu-edit-input input[type="text"]').change(function () {
	jQuery(this).val(jQuery.trim(jQuery(this).val()));
});

//
jQuery(".dd.nestable")
	.nestable({
		maxDepth: 5,
	})
	.on("change", updateOutput);

jQuery(".dd").on("change", function () {
	get_json_code_menu();
	console.log(Math.random());

	//
	jQuery(".dd .dd-item .button-delete").click(function () {
		console.log(Math.random());
		get_json_code_menu();
	});
});

//jQuery('#addButton, #editButton, .btn.btn-success').click(function () {
jQuery(".form-actions .btn.btn-success").click(function () {
	// nạp lại json
	jQuery("#data_post_excerpt").val(jQuery("#json-output").val() || "");
	// tạo lại html menu
	create_html_menu_editer();

	//
	get_json_code_menu();
});

/*
jQuery('#editButton').click(function () {
    console.log('Auto submit in ' + (jQuery(this).attr('id') || ''));
    document.admin_global_form.submit();
});
*/
