<?php

// đơn vị tiền tệ cho phần giá sản phẩm
$ebe_currency = '';
// nếu có thiết lập đơn vị tiền tệ bằng javascript thì bỏ qua thiết lập bằng css
if ($getconfig->currency_locales_format != '') {
    $ebe_currency .= '.ebe-currency:before,
.ebe-currency:after {
    display: none;
}';
} else {
    if ($getconfig->currency_format != '') {
        $ebe_currency .= '.ebe-currency:before,
.ebe-currency:after {
    content: "' . str_replace('/', '\\', $getconfig->currency_format) . '";
}';
    }

    // hiển thị đơn vị tiền ở phía trước
    // var_dump($getconfig->currency_after_format);
    if ($getconfig->currency_after_format == 'on') {
        $ebe_currency .= '.ebe-currency::before {
    display: none;
}
.ebe-currency:after {
    display: inline-block;
}';
    }
}
