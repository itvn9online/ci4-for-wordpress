// cập nhật full URL nếu chưa có
if (current_full_domain === null) {
    jQuery.ajax({
        type: 'GET',
        // lấy base URL từ link http thường (không phải https) -> để xem nó có redirect về https không
        url: 'admin/asjaxs/check_ssl',
        dataType: 'json',
        //crossDomain: true,
        //data: data,
        timeout: 33 * 1000,
        error: function (jqXHR, textStatus, errorThrown) {
            console.log(jqXHR);
            if (typeof jqXHR.responseText != 'undefined') {
                console.log(jqXHR.responseText);
            }
            console.log(errorThrown);
            console.log(textStatus);
            if (textStatus === 'timeout') {
                //
            }
        },
        success: function (data) {
            console.log(data);

            //
            sessionStorage.setItem('WGR-current-full-domain', data.http_response);
        }
    });
}

function done_unzip_system() {
    WGR_alert('DONE! giải nén system zip thành công');

    $('#unzipSystemModal').modal('hide');

    $('.hide-after-unzip-system').fadeOut();
}
