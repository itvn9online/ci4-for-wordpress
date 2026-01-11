/**
 * Closure Compiler EchBay - Auto Minify CSS/JS
 * Tự động nén các file CSS/JS thông qua Closure Compiler API
 */

// Biến theo dõi trạng thái
var isCompiling = false;

/**
 * Bắt đầu quá trình tự động nén file
 */
function start_closure_compiler_echbay() {
	var $btn = $(".start-compiler-closure");

	if (isCompiling) {
		// Dừng nén
		isCompiling = false;
		$btn
			.text("Bắt đầu nén file")
			.removeClass("btn-warning")
			.addClass("btn-primary");
		console.log("Đã dừng nén file");
	} else {
		// Bắt đầu nén
		var $firstFile = $("#for_vue a.closure-compiler-echbay").first();

		if ($firstFile.length === 0) {
			WGR_html_alert("Không có file nào cần nén!", "warning");
			return;
		}

		isCompiling = true;
		$btn.text("Dừng nén").removeClass("btn-primary").addClass("btn-warning");
		console.log("Bắt đầu nén file...");

		// Click vào file đầu tiên
		$firstFile[0].click();
	}
}

/**
 * Xử lý khi nén file thành công
 */
function after_closure_compiler_echbay(type, result_url = null) {
	// Tìm file đang được nén và xóa class và attr href tương ứng
	var $currentFile = $("#for_vue a.closure-compiler-echbay").first();
	$currentFile
		.removeClass("closure-compiler-echbay")
		.addClass("compiled-success");

	if (type === "error") {
		console.warn($currentFile.text());
		$currentFile.addClass("orgcolor");
	} else {
		console.log($currentFile.text());
		$currentFile.addClass("greencolor").removeAttr("href");
	}
	if (result_url !== null) {
		$currentFile.attr({
			href: result_url + "?v=" + new Date().getTime(),
			target: "_blank",
		});
	}

	// Nếu vẫn đang chạy, tìm file tiếp theo
	if (isCompiling) {
		var $nextFile = $("#for_vue a.closure-compiler-echbay").first();

		if ($nextFile.length > 0) {
			// Delay 300ms rồi nén file tiếp theo
			setTimeout(function () {
				$nextFile[0].click();
			}, 300);
		} else {
			// Đã hết file
			isCompiling = false;
			$(".start-compiler-closure")
				.text("Bắt đầu nén file")
				.removeClass("btn-warning")
				.addClass("btn-success");
			WGR_html_alert("Hoàn thành nén tất cả file!", "success", 0);

			// Reset về primary sau 3s
			setTimeout(function () {
				$(".start-compiler-closure")
					.removeClass("btn-success")
					.addClass("btn-primary");
			}, 3000);
		}
	}
}
