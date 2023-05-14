function action_change_user_status() {
	//
	$(".click-change-user-status").click(function () {
		console.log(controller_slug);

		//
		var data = {
			user_id: $(this).attr("data-id") || "",
			user_status: $(this).attr("data-status") || "",
		};
		//console.log(data);

		//
		$.ajax({
			type: "POST",
			url: "admin/" + controller_slug + "/quick_status",
			data: data,
			success: function (data) {
				console.log(data);

				//
				if (typeof data.error != "undefined") {
					WGR_alert(data.error, "error");
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
					$('.click-change-user-status[data-id="' + data.ok + '"]').attr({
						"data-status": data.user_status,
					});
				}
			},
		});
	});
}
