/*
 * file chứa các functon không dùng ngay, thường là các function chỉ dùng sau khi người dùng có thao tác bấm, ví dụ submit
 * chuyển file này xuống dưới footer và để defer
 */

/*
 * bấm chuột vào 1 input thì thực hiện copy text trong đó luôn
 */
function click2Copy(element, textShow) {
    element.focus();
    element.select();
    document.execCommand('copy');

    if (typeof textShow != 'undefined' && textShow === true) {
        try {
            textShow = element.value;
            textShow = ' ' + $.trim(textShow);
        } catch (e) {
            textShow = ''
        }
    } else {
        textShow = ''
    }
    WGR_html_alert('Copied' + textShow);
}


/*
 * reload lại trang sau khi submit xong
 */
function done_action_submit(go_to) {
    if (typeof go_to != 'undefined' && go_to != '') {
        window.location = go_to;
    } else {
        window.location = window.location.href;
    }
}
