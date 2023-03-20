/*
 * Chức năng upload ảnh bằng cách kéo thả
 * https://makitweb.com/drag-and-drop-file-upload-with-jquery-and-ajax/
 */

var max_width_height_for_upload = 1900;
// mảng tính số lượng các file đang được upload -> nếu hoàn tất hết thì sẽ tải lại trang
var arr_drop_uploading_file = [];

//
$(function () {
	if (document.getElementById("drop_upload_file") === null) {
		console.log("drop_upload_file not found!");
		return false;
	}

	// attr này dùng để tùy chỉnh thẻ drop cha -> đây là thẻ lúc drop vào thì sẽ hiển thị thông báo drop để upload
	// mặc định là ốp thẳng vào thẻ htm
	var parent_drop = $("#drop_upload_file").attr("data-parent_drop") || "";
	if (parent_drop == "") {
		parent_drop = "html";
	}

	// preventing page from redirecting
	$(parent_drop).on("dragover", function (e) {
		e.preventDefault();
		e.stopPropagation();

		//
		//$("h1").text("Drag here");
		$("body").addClass("droping");
	});

	$(parent_drop).on("drop", function (e) {
		e.preventDefault();
		e.stopPropagation();

		//
		$("body").removeClass("droping");
		$("body").addClass("droped");
	});

	// Drag enter
	$("#drop_upload_file").on("dragenter", function (e) {
		e.stopPropagation();
		e.preventDefault();
		//$("h1").text("Drop");
	});

	// Drag over
	$("#drop_upload_file").on("dragover", function (e) {
		e.stopPropagation();
		e.preventDefault();
		//$("h1").text("Drop");
	});

	// Drop
	$("#drop_upload_file").on("drop", function (e) {
		e.stopPropagation();
		e.preventDefault();

		//
		//$("h1").text("Upload");

		//
		change_drop_upload_media(e.originalEvent.dataTransfer.files);

		//
		$("body").removeClass("droping");
		$("body").addClass("droped");
	});

	// Open file selector on div click
	/*
	$("#drop_upload_file").click(function () {
		//$("#file").click();
		$("#file").trigger("click");
	});
	*/

	// file selected
	$("#file").change(function () {
		change_drop_upload_media($("#file")[0].files);
	});
});

function change_drop_upload_media(files) {
	console.log(files);

	//
	for (var i = 0; i < files.length; i++) {
		after_drop_upload_media(files[i]);
	}
}

function after_drop_upload_media(mediaData) {
	//console.log(mediaData);
	//console.log(mediaData.name);
	//return false;

	//
	arr_drop_uploading_file.push(false);

	// URL upload
	var action_upload = $("#drop_upload_file").attr("data-action") || "";
	if (action_upload == "") {
		// sử dụng URL cố định theo code
		if (
			typeof action_custom_upload != "undefined" &&
			action_custom_upload != ""
		) {
			action_upload = action_custom_upload;
		} else {
			// sử dụng URL động theo form
			action_upload = "uploads/image_push";
			// nếu trong admin thì dùng URL này
			if (typeof is_admin == "number" && is_admin > 0) {
				action_upload = "admin/uploads/drop_upload";
			}
		}
	}
	//console.log(arr_drop_uploading_file);
	//console.log(action_upload);
	//return false;

	//
	var reader = new FileReader();
	reader.onload = function (e) {
		//console.log(e);
		//return false;

		// upload luôn ảnh lên server -> kèm resize tại local cho nó nhẹ server
		ajax_push_image_to_server(
			{
				action: action_upload,
				data: e.target.result,
				file_name: mediaData.name,
				last_modified: Math.ceil(mediaData.lastModified / 1000),
				mime_type: mediaData.type,
				input_file: "#file",
				img_max_width: max_width_height_for_upload,
			},
			function (data) {
				console.log(data);
				//return false;

				//
				if (typeof data.error != "undefined") {
					WGR_alert(data.error, "error");

					// lỗi thì kiểm tra và nạp lại chậm 1 chút -> để còn kịp xem lỗi mà xử lý
					setTimeout(function () {
						check_and_reload_after_upload();
					}, 33 * 1000);
				} else {
					check_and_reload_after_upload();
				}
			}
		);
	};

	//
	reader.readAsDataURL(mediaData);
}

function check_and_reload_after_upload() {
	// xem còn file nào đang upload không
	for (var i = 0; i < arr_drop_uploading_file.length; i++) {
		if (arr_drop_uploading_file[i] === false) {
			arr_drop_uploading_file[i] = true;
			break;
		}
	}
	//console.log(arr_drop_uploading_file.length);
	//console.log(i);

	// nạp lại trang nếu toàn bộ các file đã được upload hoàn tất
	if (i >= arr_drop_uploading_file.length - 1) {
		if (WGR_config.cf_tester_mode > 0) {
			console.log("All drop file has uploaded...");
		} else {
			window.location = window.location.href;
		}
	}
}
