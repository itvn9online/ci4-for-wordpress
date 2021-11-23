$(document).ready(function () {
    // post
    $('.global-details-title').attr({
        'insert-before': 1
    });

    $('.echbay-blog li, .thread-list li, .eb-sub-menu, .global-details-title, .custom-bootstrap-post_type').each(function () {
        var jd = $(this).attr('data-id') || '';
        //console.log(jd);
        var type = $(this).attr('data-type') || '';
        var controller = $(this).attr('data-controller') || 'posts';
        //console.log(type);
        var insert_before = $(this).attr('insert-before') || '';
        //console.log(insert_before);

        //
        if (jd != '' && type != '') {
            var url = 'admin/' + controller + '/add?post_type=' + type + '&id=' + jd;
            //console.log(url);
            url = '<a href="' + url + '" target="_blank" rel="nofollow" class="click-goto-edit"><span><i class="fa fa-edit"></i></span></a>';

            if (insert_before != '') {
                $(this).before(url);
            } else {
                $(this).prepend(url);
            }
        }
    });

    // term
    $('.global-taxonomy-title, .echbay-widget-title, .custom-bootstrap-taxonomy').each(function () {
        var jd = $(this).attr('data-id') || '';
        //console.log(jd);
        var type = $(this).attr('data-type') || '';
        //console.log(type);

        //
        if (jd != '' && type != '') {
            var url = 'admin/terms/add?taxonomy=' + type + '&id=' + jd;
            //console.log(url);

            $(this).prepend('<a href="' + url + '" target="_blank" rel="nofollow" class="click-goto-edit"><span><i class="fa fa-edit"></i></span></a>');
        }
    });

    $('.web-logo').before('<a href="admin/configs?support_tab=data_logo" target="_blank" rel="nofollow" class="click-goto-edit"><span><i class="fa fa-edit"></i></span></a>');
});
