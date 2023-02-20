function action_trans_label(arr, option_prefix) {
	//console.log(arr);
	if (typeof option_prefix == "undefined") {
		option_prefix = "";
	}

	//
	var data_lang = "";
	//console.log(data_lang);
	for (var x in arr) {
		data_lang = "data_" + option_prefix + x.replace("lang_", "");
		console.log(data_lang);

		//
		$('#for_vue label[for="' + data_lang + '"]')
			.html(arr[x])
			.addClass("bold");
		$("#" + data_lang).attr({
			placeholder: arr[x],
		});
	}
}
