$(document).ready(function () {
	jQuery.ajax({
		type: "POST",
		url: "actions/mail_my_queue",
		dataType: "json",
		//crossDomain: true,
		data: {
			nse: Math.random(),
		},
		timeout: 33 * 1000,
		error: function (jqXHR, textStatus, errorThrown) {
			jQueryAjaxError(jqXHR, textStatus, errorThrown, new Error().stack);
		},
		success: function (res) {
			console.log(res);
		},
	});
});
