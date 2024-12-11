WGR_vuejs(
	"#for_vue",
	{
		data: json_data,
		vue_data: vue_data,
	},
	function () {
		//console.log(Math.random());
		$(".controls-comment_content").html(
			WGR_show_html_for_vuejs($(".controls-comment_content").html())
		);

		//
		show_popup_details_iframe();
	}
);
