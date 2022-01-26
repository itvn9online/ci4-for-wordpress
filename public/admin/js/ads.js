$('#quick_add_menu').change(function () {
    var v = $('#quick_add_menu').val() || '';
    if (v != '') {
        var base_url = $('base ').attr('href') || '';
        if (base_url != '') {
            v = v.replace(base_url, './');
        }
    }
    $('#post_meta_url_redirect').val(v).focus();
});
