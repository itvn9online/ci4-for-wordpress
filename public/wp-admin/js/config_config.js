// thêm chức năng add ảnh cho form
add_and_show_post_avt("#data_logo");
add_and_show_post_avt("#data_web_favicon");
add_and_show_post_avt("#data_logofooter");
add_and_show_post_avt("#data_logo_mobile");

//
$(document).ready(function () {
	action_highlighted_code("#data_html_header");
	action_highlighted_code("#data_html_body");

	//
	show_input_length_char("data_title");
	$("#data_title").trigger("change");

	//
	show_input_length_char("data_description");
	$("#data_description").trigger("change");
});
