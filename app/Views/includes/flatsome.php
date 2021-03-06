<?php

/*
 * daidq (2022-06-03)
 * tạo file include thư viện javascript flatsome riêng ra, theme nào dùng thì include vào
 */

?>
<script type='text/javascript' id='flatsome-js-js-extra'>
/* <![CDATA[ */
var flatsomeVars = {
    "ajaxurl": "",
    "rtl": "",
    "sticky_height": "70",
    "assets_url": "",
    "lightbox": {
        "close_markup": "<button title=\"%title%\" type=\"button\" class=\"mfp-close\"><svg xmlns=\"http:\/\/www.w3.org\/2000\/svg\" width=\"28\" height=\"28\" viewBox=\"0 0 24 24\" fill=\"none\" stroke=\"currentColor\" stroke-width=\"2\" stroke-linecap=\"round\" stroke-linejoin=\"round\" class=\"feather feather-x\"><line x1=\"18\" y1=\"6\" x2=\"6\" y2=\"18\"><\/line><line x1=\"6\" y1=\"6\" x2=\"18\" y2=\"18\"><\/line><\/svg><\/button>",
        "close_btn_inside": false
    },
    "user": {
        "can_edit_pages": false
    },
    "i18n": {
        "mainMenu": "Main Menu"
    },
    "options": {
        "cookie_notice_version": "1",
        "swatches_layout": false,
        "swatches_box_select_event": false,
        "swatches_box_behavior_selected": false,
        "swatches_box_update_urls": "1",
        "swatches_box_reset": false,
        "swatches_box_reset_extent": false,
        "swatches_box_reset_time": 300,
        "search_result_latency": "0"
    }
};
/* ]]> */
</script>
<?php

//
$base_model->add_js( 'javascript/flatsome.js', [
    //'get_content' => 1,
    //'preload' => 1,
    'cdn' => CDN_BASE_URL,
], [
    'defer'
] );