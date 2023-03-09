// chức năng nạp lại URL preview nếu có
function reload_preview_if_isset() {
	if (typeof preview_url == "undefined" || preview_url == "") {
		return false;
	}
	document.getElementById("target_eb_iframe").src = preview_url;
}

function close_preview_mode() {
	$("body").removeClass("preview-url");
}

// tạo url preview nếu có
//console.log(preview_url);
if (typeof preview_url != "undefined" && preview_url != "") {
	$("body").addClass("preview-url");
	$(".preview-btn").html(
		[
			'<a href="' +
				preview_url +
				'" class="btn btn-primary"><i class="fa fa-arrow-left"></i> Quay lại</a>',
			'<button type="button" onclick="return close_preview_mode();" class="btn btn-danger"><i class="fa fa-eye-slash"></i> Tắt chế độ Preview</button>',
		].join(" ")
	);

	// nạp link preview
	$(document).ready(function () {
		$("#target_eb_iframe").height(Math.ceil($(window).height()));
		document.getElementById("target_eb_iframe").src = preview_url;
	});

	//
	$(window).resize(function () {
		$("#target_eb_iframe").height(Math.ceil($(window).height()));
	});
}
