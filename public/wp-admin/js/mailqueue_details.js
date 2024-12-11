WGR_vuejs(
	"#for_vue",
	{
		data: json_data,
	},
	function () {
		//console.log(Math.random());
		$(".controls-content").html(
			WGR_show_html_for_vuejs($(".controls-content").html())
		);

		//
		show_popup_details_iframe();
	}
);
