// tạo select box các nhóm dữ liệu cho khung tìm kiếm
function each_to_group_taxonomy() {
	if ($(".each-to-group-taxonomy").length == 0) {
		return false;
	}

	//
	$(".each-to-group-taxonomy").each(function () {
		var a = $(this).attr("data-taxonomy") || "";
		var jd = $(this).attr("id") || "";
		if (jd == "") {
			jd = "_" + Math.random().toString(32).replace(".", "_");

			//
			$(this).attr({
				id: jd,
			});
		}
		//console.log(a);
		//console.log(jd);

		// chạy ajax nạp dữ liệu của taxonomy
		load_term_select_option(a, jd, function (data, jd) {
			console.log(data);
			if (data.length > 0) {
				// tạo select
				$("#" + jd)
					.removeClass("set-selected")
					.append(create_term_select_option(data));

				// xóa các option không có count -> đỡ phải lọc
				$("#" + jd + " option[data-count='0']").remove();
				// tạo lại selected
				WGR_set_prop_for_select("#" + jd);
				MY_select2("#" + jd);
			} else {
				$("#" + jd)
					.parent(".hide-if-no-taxonomy")
					.hide();
			}
		});
	});
}

//
$(document).ready(function () {
	//console.log(a);

	//
	each_to_group_taxonomy();

	// thay đổi số thứ tự của post
	$(".change-update-menu_order")
		.attr({
			type: "number",
		})
		.on("dblclick", function () {
			$(this).select();
		})
		.change(function () {
			var a = $(this).attr("data-id") || "";
			if (a != "") {
				var v = $(this).val();
				v *= 1;
				if (!isNaN(v)) {
					if (v <= 0) {
						v = 0;
					}
					//console.log(a + ":", v);

					//
					$(this).addClass("pending").val(v);

					//
					jQuery.ajax({
						type: "POST",
						url: "admin/asjaxs/update_menu_order",
						dataType: "json",
						data: {
							id: a * 1,
							order: v,
						},
						timeout: 33 * 1000,
						error: function (jqXHR, textStatus, errorThrown) {
							jQueryAjaxError(
								jqXHR,
								textStatus,
								errorThrown,
								new Error().stack
							);
						},
						success: function (data) {
							console.log(data);
							if (typeof data.error != "undefined") {
								WGR_alert(data.error, "error");
							} else {
								WGR_alert("OK");
							}
							$(".change-update-menu_order").removeClass("pending");
						},
					});
				}
			}
		});
});
