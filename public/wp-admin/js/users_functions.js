function action_change_user_status() {
	//
	jQuery(".click-change-user-status").click(function () {
		console.log(controller_slug);

		//
		var data = {
			user_id: jQuery(this).data("id") || "",
			user_status: jQuery(this).data("status") || "",
		};
		//console.log(data);

		//
		jQuery.ajax({
			type: "POST",
			url: "sadmin/" + controller_slug + "/quick_status",
			data: data,
			success: function (data) {
				console.log(data);

				//
				if (typeof data.error != "undefined") {
					WGR_alert(data.error, "error");
				} else if (typeof data.result != "undefined" && data.result === false) {
					WGR_alert("Lỗi thực thi lệnh thay đổi trạng thái tài khoản", "error");
				} else if (typeof data.ok != "undefined") {
					WGR_alert(
						"Thay đổi Trạng thái " +
							data.member_name +
							" #" +
							data.ok +
							" thành công: " +
							data.user_status
					);

					//
					jQuery('.click-change-user-status[data-id="' + data.ok + '"]').attr({
						"data-status": data.user_status,
					});
				}
			},
		});
	});
}
