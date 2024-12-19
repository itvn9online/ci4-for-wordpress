WGR_vuejs(
	"#for_vue",
	{
		for_action: json_params.for_action,
		controller_slug: json_params.controller_slug,
		data: json_data,
	},
	function () {
		jQuery(".orders-open-popup").click(function () {
			return order_details_set_iframe(
				jQuery(this).attr("data-id") || "",
				"sadmin/mailqueues?mail_id="
			);
		});
	}
);
