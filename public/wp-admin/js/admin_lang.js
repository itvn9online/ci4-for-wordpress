(function () {
	// kiểm tra xem có file lang ko
	if (typeof system_admin_lang == "undefined") {
		// ko có thì bỏ qua
		return false;
	}

	// có thì chạy lệnh thay thế dữ liệu
	// console.log(system_admin_lang);
	console.log("%c" + "system_admin_lang", "color: green");

	//
	for (let x in system_admin_lang) {
		$(".lang-" + x).html(system_admin_lang[x]);
	}
})();
