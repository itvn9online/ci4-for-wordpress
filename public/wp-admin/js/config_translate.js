console.log("%c Chạy vòng lặp thay thế text cho label", "color: green;");
if (typeof action_trans_label == "function") {
	action_trans_label(arr_meta_default, "lang_");
	action_trans_label(arr_trans_label, "lang_");
}

// tự động lưu bản dịch nếu có thay đổi
var before_change_translate = "";
$(".change-auto-save-translate")
	.on("focus", function () {
		before_change_translate = $(this).val();
	})
	.on("blur", function () {
		if (before_change_translate != $(this).val()) {
			document.admin_global_form.submit();
		}
	});
