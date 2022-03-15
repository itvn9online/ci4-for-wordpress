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
    console.log(Math.random());

    //
    $('.dd .dd-item .button-delete').click(function () {
        console.log(Math.random());
        get_json_code_menu();
    });
});

//$('#addButton, #editButton, .btn.btn-success').click(function () {
$('.form-actions .btn.btn-success').click(function () {
    // nạp lại json
    $('#data_post_excerpt').val($('#json-output').val() || '');
    // tạo lại html menu
    create_html_menu_editer();

    //
    get_json_code_menu();
});

/*
$('#editButton').click(function () {
    console.log('Auto submit in ' + ($(this).attr('id') || ''));
    document.admin_global_form.submit();
});
*/
