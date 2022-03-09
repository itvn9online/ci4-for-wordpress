//
$('.menu-edit-input input[type="text"]').change(function () {
    $(this).val($.trim($(this).val()));
});

//
$('.dd.nestable').nestable({
    maxDepth: 5
}).on('change', updateOutput);

$('.dd').on('change', function () {
    get_json_code_menu();
});

//$('#addButton, #editButton, .btn.btn-success').click(function () {
$('.form-actions .btn.btn-success').click(function () {
    get_json_code_menu();
});

/*
$('#editButton').click(function () {
    console.log('Auto submit in ' + ($(this).attr('id') || ''));
    document.admin_global_form.submit();
});
*/
