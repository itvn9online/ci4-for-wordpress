/*
 * luôn xóa phần pass email -> không cho hiển thị
 */
$('#data_smtp_host_show_pass').change(function () {
    $('#data_smtp_host_pass').val($(this).val());
}).focus(function () {
    $(this).val($('#data_smtp_host_pass').val() || '');
}).blur(function () {
    $('#data_smtp_host_pass').val($(this).val());
    $(this).val('');
});


/*
 * khi bấm test email -> kiểm tra trường bắt buộc
 */
$('.click-check-email-test').click(function () {
    if ($.trim($('#data_smtp_test_email').val()) == '') {
        HTV_alert('Vui lòng nhập email người nhận sau đó lưu lại rồi mới test', 'error');
        $('#data_smtp_test_email').focus();
        return false;
    }
    return true;
});
