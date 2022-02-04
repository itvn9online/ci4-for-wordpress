// cập nhật full URL nếu chưa có
if (current_full_domain === null) {
    jQuery.ajax({
        type: 'GET',
        // lấy base URL từ link http thường (không phải https) -> để xem nó có redirect về https không
        url: web_link + 'home/check_ssl',
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
