/*
 * file chứa các functon không dùng ngay, thường là các function chỉ dùng sau khi người dùng có thao tác bấm, ví dụ submit
 * chuyển file này xuống dưới footer và để defer
 */

/*
 * bấm chuột vào 1 input thì thực hiện copy text trong đó luôn
 */
function click2Copy(element, textShow) {
	element.focus();
	element.select();
	document.execCommand("copy");

	if (typeof textShow != "undefined" && textShow === true) {
		try {
			textShow = element.value;
			textShow = " " + $.trim(textShow);
		} catch (e) {
			textShow = "";
		}
	} else {
		textShow = "";
	}
	WGR_html_alert("Copied" + textShow);
}

/*
 * reload lại trang sau khi submit xong
 */
function done_action_submit(go_to) {
	if (typeof go_to != "undefined" && go_to != "") {
		window.location = go_to;
	} else {
		window.location = window.location.href;
	}
}

// chức năng đồng bộ dữ liệu liên quan đến post, term
function sync_ajax_post_term() {
	var k = "sync-ajax-post-term";
	var last_run = localStorage.getItem(k);
	if (last_run !== null) {
		last_run *= 1;
		last_run = Date.now() - last_run;
		last_run = Math.ceil(last_run / 1000);
		if (last_run < 600) {
			console.log("last run: " + k + ":", last_run);
			return false;
		}
	}
	if (WGR_config.cf_tester_mode > 0) {
		console.log(k);
	}

	//
	jQuery.ajax({
		type: "GET",
		url: "ajaxs/sync_ajax_post_term",
		dataType: "json",
		//crossDomain: true,
		//data: data,
		timeout: 33 * 1000,
		error: function (jqXHR, textStatus, errorThrown) {
			jQueryAjaxError(jqXHR, textStatus, errorThrown, new Error().stack);
		},
		success: function (data) {
			if (WGR_config.cf_tester_mode > 0) {
				console.log(data);
			}
			sessionStorage.setItem(k, JSON.stringify(data));

			//
			if (typeof data.post != "undefined" && data.post === false) {
				localStorage.setItem(k, Date.now());
			}
		},
	});
}
