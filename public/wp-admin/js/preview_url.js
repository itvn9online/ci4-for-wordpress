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
			// jQuery("#target_eb_iframe").contents().find("body").css("zoom", 0.7);

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
	/*
	window.history.pushState(
		"",
		document.title,
		window.location.href.split("&preview_offset_top=")[0]
	);
	jQuery("body").removeClass("preview-url");
	*/
	window.location = window.location.href.split("&preview_offset_top=")[0];
}

function expand_preview_mode() {
	jQuery("body").toggleClass("preview-expand-url");
	auto_resize_ckeditor();
}

function unexpand_preview_mode() {
	jQuery("body").removeClass("preview-expand-url");
	auto_resize_ckeditor();
}

function auto_resize_ckeditor() {
	jQuery(".auto-ckeditor").each(function () {
		var a = jQuery(this).attr("id") || "";
		if (a != "") {
			re_height_iframe_editer(a);
		}
	});
}

// tạo url preview nếu có
//console.log(preview_url);
if (typeof preview_url != "undefined" && preview_url != "") {
	jQuery("body").addClass("preview-url");
	jQuery("a.back-preview-mode").attr({
		href: preview_url,
	});

	// nạp link preview
	jQuery(document).ready(function () {
		jQuery("#target_eb_iframe").height(Math.ceil(jQuery(window).height()));
		set_preview_src();
	});

	//
	jQuery(window).resize(function () {
		jQuery("#target_eb_iframe").height(Math.ceil(jQuery(window).height()));
	});
}
