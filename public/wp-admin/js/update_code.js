//
function done_submit_update_code(file_name) {
	WGR_body_opacity();
	document.frm_global_upload.reset();
	if (typeof file_name != "undefined" && file_name != "") {
		file_name = " (" + file_name + ")";
	}
	WGR_alert("Update và giải nén code thành công" + file_name);

	//
	jQuery("#confirm_is_coder").prop("checked", false);
	jQuery("#confirm_is_super_coder").prop("checked", false);
}

function auto_submit_update_code() {
	var fullPath = jQuery("#upload_code").val() || "";
	//console.log(fullPath);

	//
	if (fullPath == "") {
		WGR_alert("Cannot be determined file upload", "error");
		return false;
	}

	//
	//fullPath = fullPath.split('.')[0];

	// kiểm tra file upload phải đúng với tên theme hiện tại
	if (
		fullPath.includes(themeName) == false &&
		// hoặc file code chính là: ci4-for-wordpress
		fullPath.includes("ci4-for-wordpress.") == false &&
		// hoặc system của codeigniter
		fullPath.includes("system.") == false
	) {
		WGR_alert(
			"Theme này chỉ hỗ trợ upload file trùng tên với theme là: " + themeName,
			"warning"
		);
		return false;
	}

	//
	WGR_body_opacity(0.1);
	document.frm_global_upload.submit();
	return true;
}

// chức năng download code thì không cần thiết
function before_start_download_in_github() {
	if (jQuery("#confirm_is_coder").is(":checked") == false) {
		jQuery("#confirm_is_coder")
			.parent("p")
			.addClass("redcolor")
			.addClass("bold")
			.addClass("medium18");
		return false;
	}

	//
	WGR_body_opacity(0.1);
	return true;
}

// chức năng reset code -> yêu cầu xác nhận 2 lần
function before_start_reset_in_github() {
	if (before_start_download_in_github() !== true) {
		return false;
	}
	WGR_body_opacity(1);

	//
	if (jQuery("#confirm_is_super_coder").is(":checked") == false) {
		jQuery("#confirm_is_super_coder")
			.parent("p")
			.addClass("redcolor")
			.addClass("bold")
			.addClass("medium18");
		return false;
	}

	//
	WGR_body_opacity(0.1);
	return true;
}

function done_submit_restore_code() {
	jQuery("#restoreModal, #cleanupModal").modal("hide");
	window.location.reload();
}

function before_unzip_thirdparty() {
	return WGR_body_opacity(0.1);
}

function after_unzip_thirdparty() {
	return WGR_body_opacity();
}

function done_unzip_system() {
	return done_submit_restore_code();
}

// chuyển sang các step để update từng tiến trình và in ra thông báo
function next_step_update_code(step, msg, uri) {
	if (typeof step == "undefined" || step == "") {
		return false;
	}
	if (typeof msg != "undefined" && msg != null && msg != "") {
		WGR_alert(msg, null, 33);
	}

	// cắt bỏ ký tự / ở đầu uri nêu có
	if (uri.startsWith("/")) {
		uri = uri.substring(1);
	}
	uri = web_link + uri;
	console.log(step, uri);

	// nếu có step thì chuyển sang step đó
	var url = new URL(uri);
	url.searchParams.set("step", step);
	// window.location.href = url.toString();
	// mở url trong iframe #target_eb_iframe
	jQuery("#target_eb_iframe").attr("src", url.toString());
	console.log("Open iframe: " + url.toString());
}
