function action_update_link_redirect() {
	$(".change-update-link-redirect").change(function (e) {
		// e.preventDefault();

		//
		let a = $(this).data("id") || "";
		if (a == "") {
			WGR_alert("ID not found", "error");
			return false;
		}

		//
		let b = $(this).val() || "";

		// gọi tới ajax để cập nhật
		jQuery.ajax({
			type: "POST",
			// link TEST
			url: "sadmin/config404s",
			dataType: "json",
			//crossDomain: true,
			data: {
				link_id: a,
				link_image: b,
			},
			timeout: 33 * 1000,
			error: function (jqXHR, textStatus, errorThrown) {
				jQueryAjaxError(jqXHR, textStatus, errorThrown, new Error().stack);
			},
			success: function (data) {
				console.log(data);

				//
				if (typeof data.error != "undefined") {
					WGR_alert(data.error, "error");
				} else if (typeof data.msg != "undefined") {
					WGR_alert(data.msg);

					//
					$(".change-update-link-redirect[data-id='" + data.link_id + "']").val(
						data.link_image
					);
				}
			},
		});
	});
}

WGR_vuejs(
	"#app",
	{
		data: vue_data,
		top_request: top_request,
	},
	function () {
		action_update_link_redirect();
	}
);
