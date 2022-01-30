//
(function () {
    var current_page = $('.public-part-page span.current').attr('data-page') || '';
    if (current_page != '') {
        current_page *= 1;

        //
        console.log('current page:', current_page);
        current_page++;
        setTimeout(function () {
            window.location = web_link + 'admin/uploads/optimize?page_num=' + current_page;
        }, 5000);
    }
})();
