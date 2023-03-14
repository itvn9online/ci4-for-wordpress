/*
 * Chức năng upload ảnh bằng cách kéo thả
 * https://makitweb.com/drag-and-drop-file-upload-with-jquery-and-ajax/
 */

var max_width_height_for_upload = 1900;
// mảng tính số lượng các file đang được upload -> nếu hoàn tất hết thì sẽ tải lại trang
var arr_drop_uploading_file = [];

//
$(function () {
	// attr này dùng để tùy chỉnh thẻ drop cha -> đây là thẻ lúc drop vào thì sẽ hiển thị thông báo drop để upload
	// mặc định là ốp thẳng vào thẻ htm
	var parent_drop = $(".upload-area").attr("data-parent_drop") || "html";

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
	$(".upload-area").on("dragenter", function (e) {
		e.stopPropagation();
		e.preventDefault();
		//$("h1").text("Drop");
	});

	// Drag over
	$(".upload-area").on("dragover", function (e) {
		e.stopPropagation();
		e.preventDefault();
		//$("h1").text("Drop");
	});

	// Drop
	$(".upload-area").on("drop", function (e) {
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
	$("#uploadfile").click(function () {
		//$("#file").click();
		$("#file").trigger("click");
	});
	*/

	// file selected
	/*
	$("#file").change(function () {
		change_drop_upload_media($("#file")[0].files);
	});
	*/
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
	var action_upload = "uploads/image_push";
	// nếu có tùy chỉnh URL upload thì sử dụng URL này
	if (
		typeof action_custom_upload != "undefined" &&
		action_custom_upload != ""
	) {
		action_upload = action_custom_upload;
	}
	// nếu trong admin thì dùng URL này
	else if (typeof is_admin == "number" && is_admin > 0) {
		action_upload = "admin/uploads/drop_upload";
	}
	//console.log(arr_drop_uploading_file);
	//console.log(action_upload);

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

				//
				if (typeof data.error == "undefined") {
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
			}
		);
	};

	//
	reader.readAsDataURL(mediaData);
}

// Sending AJAX request and upload file
function uploadData(formdata) {
	console.log(formdata);
	$.ajax({
		url: "admin/uploads/drop_upload",
		type: "post",
		data: formdata,
		contentType: false,
		processData: false,
		dataType: "json",
		success: function (response) {
			console.log(response);
			//addThumbnail(response);
		},
	});
}

// Added thumbnail
function addThumbnail(data) {
	$("#uploadfile h1").remove();
	var len = $("#uploadfile div.thumbnail").length;

	var num = Number(len);
	num = num + 1;

	var name = data.name;
	var size = convertSize(data.size);
	var src = data.src;

	// Creating an thumbnail
	$("#uploadfile").append(
		'<div id="thumbnail_' + num + '" class="thumbnail"></div>'
	);
	$("#thumbnail_" + num).append(
		'<img src="' + src + '" width="100%" height="78%">'
	);
	$("#thumbnail_" + num).append('<span class="size">' + size + "<span>");
}

// Bytes conversion
function convertSize(size) {
	var sizes = ["Bytes", "KB", "MB", "GB", "TB"];
	if (size == 0) return "0 Byte";
	var i = parseInt(Math.floor(Math.log(size) / Math.log(1024)));
	return Math.round(size / Math.pow(1024, i), 2) + " " + sizes[i];
}
