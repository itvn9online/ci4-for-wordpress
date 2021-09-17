jEBE_multi_slider('.topmainslider .widget-run-slider', {
    autoplay: true,
    //swipemobile : true,
    swipemobile: false,
    // nếu số giây tự chuyển slider nhỏ quá -> chuyển sang tính theo giây
    speedNext: 5000,

    buttonListNext: true,
    sliderArrow: true,
    version: 1,

    //thumbnail : '.banner-ads-media',
    size: jQuery('.topmainslider .widget-run-slider li:first .ti-le-global').attr('data-size') || ''
});
