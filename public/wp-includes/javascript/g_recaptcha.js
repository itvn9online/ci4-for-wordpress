function my_grecaptcha_then(token) {
	// console.log(token);
	$("textarea[name='ebe_grecaptcha_response']").val(token);
}

//
var my_grecaptcha_focus = false;
$('input[type="text"], input[type="email"], textarea').on("focus", function () {
	if (my_grecaptcha_focus === false) {
		my_grecaptcha_focus = true;
		my_grecaptcha_ready();
		// tự động nạp lại sau mỗi 2 phút  do hết hạn
		setInterval(() => {
			my_grecaptcha_ready();
		}, 110 * 1000);
	}
});
