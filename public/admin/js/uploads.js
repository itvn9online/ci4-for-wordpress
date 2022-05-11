$('#upload_image').change(function () {
    $('body').css({
        'opacity': 0.33
    });
    document.frm_global_upload.submit();
});
