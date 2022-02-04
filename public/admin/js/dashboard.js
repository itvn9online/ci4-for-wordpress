// cập nhật full URL nếu chưa có
if (current_full_domain === null) {
    jQuery.ajax({
        type: 'GET',
        // lấy base URL từ link http thường (không phải https) -> để xem nó có redirect về https không
        url: web_link + 'ajax/check_ssl',
        dataType: 'json',
        //crossDomain: true,
        //data: data,
        success: function (data) {
            console.log(data);

            //
            sessionStorage.setItem('WGR-current-full-domain', data.base_url);
        }
    });
}

function done_unzip_system() {
    WGR_alert('DONE! giải nén system zip thành công');

    $('#unzipSystemModal button.btn-close').trigger('click');

    $('.hide-after-unzip-system').fadeOut();
}
