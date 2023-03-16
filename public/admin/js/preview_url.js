// nạp src cho iframe
function set_preview_src() {
	document.getElementById("target_eb_iframe").src = preview_url;

	//
	var min_scroll = 90;
	if (
		typeof preview_offset_top != "undefined" &&
		preview_offset_top != "" &&
		preview_offset_top * 1 > min_scroll
	) {
		//console.log("preview offset top:", preview_offset_top);

		//
		var myIframe = document.getElementById("target_eb_iframe");
		myIframe.onload = function () {
			//$("#target_eb_iframe").contents().find("body").css("zoom", 0.7);

			//
			myIframe.contentWindow.scrollTo(
				0,
				Math.ceil(preview_offset_top) - min_scroll
			);
		};
	}
}

// chức năng nạp lại URL preview nếu có
function reload_preview_if_isset() {
	if (typeof preview_url == "undefined" || preview_url == "") {
		return false;
	}
	set_preview_src();
	unexpand_preview_mode();
}

function close_preview_mode() {
	$("body").removeClass("preview-url");
}

function expand_preview_mode() {
	$("body").toggleClass("preview-expand-url");
	auto_resize_ckeditor();
}

function unexpand_preview_mode() {
	$("body").removeClass("preview-expand-url");
	auto_resize_ckeditor();
}

function auto_resize_ckeditor() {
	$(".auto-ckeditor").each(function () {
		var a = $(this).attr("id") || "";
		if (a != "") {
			re_height_iframe_editer(a);
		}
	});
}

// tạo url preview nếu có
//console.log(preview_url);
if (typeof preview_url != "undefined" && preview_url != "") {
	$("body").addClass("preview-url");
	$("a.back-preview-mode").attr({
		href: preview_url,
	});

	// nạp link preview
	$(document).ready(function () {
		$("#target_eb_iframe").height(Math.ceil($(window).height()));
		set_preview_src();
	});

	//
	$(window).resize(function () {
		$("#target_eb_iframe").height(Math.ceil($(window).height()));
	});
}
