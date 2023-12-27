<?php

// đơn vị tiền tệ cho phần giá sản phẩm
$ebe_currency = '';
if ($getconfig->currency_format != '') {
    $ebe_currency .= '
.ebe-currency:before,
.ebe-currency:after {
    content: "' . $getconfig->currency_format . '";
}';
}

// hiển thị đơn vị tiền ở phía trước
// var_dump($getconfig->currency_before_format);
if ($getconfig->currency_before_format == 'on') {
    $ebe_currency .= '
.ebe-currency::before {
    display: inline-block;
}
.ebe-currency:after {
    display: none;
}';
}
