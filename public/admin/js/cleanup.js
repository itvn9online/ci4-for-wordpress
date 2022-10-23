function waiting_cleanup_cache() {
    $('body').css({
        opacity: 0.1
    });

    //
    $('#target_eb_iframe').on("load", function () {
        $('body').css({
            opacity: 1
        });
    });
}