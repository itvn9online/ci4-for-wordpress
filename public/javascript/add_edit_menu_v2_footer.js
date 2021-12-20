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

$('#addButton, #editButton, .btn.btn-success').click(function () {
    get_json_code_menu();
});

$('#editButton').click(function () {
    document.admin_global_form.submit();
});
