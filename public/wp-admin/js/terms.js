// do sử dụng aguilarjs đang không tạo được danh mục theo dạng đệ quy -> tự viết function riêng vậy
function term_tree_view(data, tmp, gach_ngang) {
	if (data.length <= 0) {
		return false;
	}
	if (typeof gach_ngang == "undefined") {
		gach_ngang = "";
	}
	//console.log('gach ngang:', gach_ngang);

	//
	var str = "";
	var arr = null;
	for (var i = 0; i < data.length; i++) {
		//console.log(data[i]);

		//
		if (data[i].parent > 0) {
			str = tmp;

			//
			str = replace_html_by_max_j("{{v.gach_ngang}}", str, gach_ngang);

			//
			arr = data[i];
			for (var x in arr) {
				//console.log(typeof arr[x], arr[x]);
				if (typeof arr[x] != "object") {
					str = replace_html_by_max_j("{{v." + x + "}}", str, arr[x]);
				} else {
					console.log(arr[x]);
				}
			}

			//
			//console.log(str);
			$('.each-to-child-term[data-id="' + arr.parent + '"]').after(str);
		}

		//
		//console.log(data[i].child_term.length);
		if (data[i].child_term.length > 0) {
			term_tree_view(data[i].child_term, tmp, gach_ngang + "&#8212; ");
		}
	}
}

function tmp_to_term_html(data, tmp, gach_ngang) {
	if (typeof gach_ngang == "undefined") {
		gach_ngang = "";
	}

	//
	var str = tmp;

	//
	str = replace_html_by_max_j("{{v.gach_ngang}}", str, gach_ngang);

	//
	var arr = data;
	for (var x in arr) {
		//console.log(typeof arr[x], arr[x]);
		if (typeof arr[x] != "object") {
			str = replace_html_by_max_j("{{v." + x + "}}", str, arr[x]);
		} else if (arr[x] != null) {
			//console.log(x + ':', arr[x]);
			for (var y in arr[x]) {
				//console.log(arr[x][y]);

				//
				str = replace_html_by_max_j("%" + x + "." + y + "%", str, arr[x][y]);
			}
		}
	}

	// xử lý dữ liệu dư thừa
	str = replace_html_by_max_j("%term_meta.custom_size%", str, "&nbsp;");

	//
	return str;
}

// thay thế dữ liệu dựa theo số lượng tmp có trong html
function replace_html_by_max_j(v_x, str, data) {
	var max_j = str.split(v_x).length;
	//console.log('max j (' + x + '):', max_j);
	for (var j = 0; j < max_j; j++) {
		str = str.replace(v_x, data);
	}
	return str;
}

function term_v2_tree_view(tmp, term_id, gach_ngang) {
	// lần đầu thì lấy nhóm cấp 1 trước
	if (typeof term_id == "undefined") {
		$("#admin_term_list").text("");
		term_id = 0;
		gach_ngang = "";
	} else {
		term_id *= 1;
	}
	//console.log('gach ngang:', gach_ngang);
	//console.log('term id:', term_id);
	//console.log('term_data:', term_data);

	//
	//var has_term = false;
	for (var i = 0; i < term_data.length; i++) {
		// không lấy các phần tử đã được set null
		if (term_data[i] === null) {
			continue;
		}
		// chỉ lấy những phần tử có parent trùng với dữ liệu truyền vào
		else if (term_data[i].parent * 1 !== term_id) {
			continue;
		}
		//console.log(term_data[i]);
		//has_term = true;

		// hiển thị nhóm hiện tại ra
		$("#admin_term_list").append(
			tmp_to_term_html(term_data[i], tmp, gach_ngang)
		);

		// nạp nhóm con luôn và ngay
		term_v2_tree_view(tmp, term_data[i].term_id, gach_ngang + "&#8212; ");

		//
		term_data[i] = null;
	}
	//if (term_id <= 0) console.log('has term:', has_term);
}

// tìm term cha của 1 term để xem cha của nó có tồn tại trong danh sách này không
function check_term_parent_by_id(parent_id) {
	var has_parent = false;
	// chạy vòng lặp để tìm xem 1 term có cha ở cùng trang không
	for (var j = 0; j < term_data.length; j++) {
		if (term_data[j] !== null && term_data[j].term_id * 1 === parent_id * 1) {
			// nếu có thì thử xem thằng cha này có cha không -> ông
			var has_granfather = check_term_parent_by_id(term_data[j].parent);
			//console.log('has granfather:', has_granfather);

			// nếu có ông, cụ, kị.... thì lấy phần tử đó
			if (has_granfather !== false) {
				has_parent = has_granfather;
			}
			// không thì trả về phần tử hiện tại luôn
			else {
				has_parent = j;
			}

			//
			break;
		}
	}
	return has_parent;
}

// hiển thị các term chưa bị null
function term_not_null_tree_view(tmp, gach_ngang) {
	if (typeof gach_ngang == "undefined") {
		// các nhóm ở đây không phải nhóm cấp 1 -> nên để ít nhất 1 gạch ngang
		//gach_ngang = '| ';
		gach_ngang = "";
	}
	//console.log('gach ngang:', gach_ngang);
	//console.log('term_data:', term_data);

	//
	for (var i = 0; i < term_data.length; i++) {
		// không lấy các phần tử đã được set null
		if (term_data[i] === null) {
			continue;
		}
		//console.log(term_data[i]);

		// thử xem nhóm này có đang là nhóm con của nhóm nào trong đây không
		var j = check_term_parent_by_id(term_data[i].parent);
		// tìm thấy cha thì in nhóm cha trước rồi mới in nhóm con
		if (j !== false) {
			$("#admin_term_list").append(
				tmp_to_term_html(term_data[j], tmp, gach_ngang)
			);

			// nạp nhóm con luôn và ngay
			term_v2_tree_view(tmp, term_data[j].term_id, gach_ngang + "&#8212; ");

			term_data[j] = null;
		}
		// không thấy cha thì in trực tiếp nó ra thôi
		else {
			$("#admin_term_list").append(
				tmp_to_term_html(term_data[i], tmp, gach_ngang)
			);

			// nạp nhóm con luôn và ngay
			term_v2_tree_view(tmp, term_data[i].term_id, gach_ngang + "&#8212; ");
		}

		//
		term_data[i] = null;
	}
}

function before_tree_view(tmp, max_i) {
	if (typeof max_i != "number") {
		max_i = 50;
	} else if (max_i < 0) {
		return false;
	}

	// chờ khi aguilar nạp xong html thì mới nạp tree view
	if ($("#admin_term_list tr.ng-scope").length == 0) {
		setTimeout(function () {
			before_tree_view(tmp, max_i - 1);
		}, 100);

		//
		return false;
	}

	//
	term_tree_view(term_data, tmp);
	//$('.this-child-term div[v-if]').remove();
}

function done_multi_add_term() {
	window.location = window.location.href;
}

function open_modal_add_multi_term(term_id) {
	$("#data_term_id").val(term_id);
	$("#multi_add_parent_name").html(
		$('.get-parent-term-name[data-id="' + term_id + '"]').attr("data-name") ||
			""
	);

	//
	setTimeout(function () {
		$("#data_term_name").focus();
	}, 600);
}

function record_status_color(id, term_status) {
	$('#admin_term_list .record-status-color[data-id="' + id + '"]').attr({
		"data-status": term_status,
	});
}

(function () {
	if (term_data.length <= 0) {
		// không có dữ liệu thì xóa template đi
		$("#admin_term_list").text("");
		return false;
	}

	//
	var tmp = $("#admin_term_list tr:first").html() || "";
	$("#admin_term_list").text("");
	if (tmp == "") {
		return false;
	}
	for (var i = 0; i < 10; i++) {
		tmp = tmp.replace("{{DeletedStatus_DELETED}}", DeletedStatus_DELETED);
		tmp = tmp.replace("{{for_action}}", for_action);
		tmp = tmp.replace("{{controller_slug}}", controller_slug);
	}
	tmp =
		'<tr data-id="{{v.term_id}}" data-level="{{v.term_level}}" class="each-to-child-term this-child-term">' +
		tmp +
		"</tr>";
	//console.log(tmp);

	/*
	 * phiên bản sử dụng aguilar js
	 */
	//before_tree_view(tmp);

	/*
	 * phiên bản sử dụng js thuần
	 */
	term_v2_tree_view(tmp);
	//console.log('term data:', term_data);

	// chạy rà soát lại những nhóm chưa được xác định -> not null
	term_not_null_tree_view(tmp);
	//console.log('term data:', term_data);

	// bỏ các term không có cha
	$('.parent-term-name[data-id="0"], .parent-term-name[data-id=""]')
		.remove()
		.hide();
	// thêm class nạp tên term cho các thẻ đủ điều kiện
	$('.parent-term-name[data-line=""]')
		.addClass("each-to-taxonomy")
		.addClass("parent-term-after");
})();

/*
 * thay đổi số thứ tự của term
 */
$(document).ready(function () {
	$(".change-update-term_order")
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
						url: "admin/asjaxs/update_term_order",
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
							$(".change-update-term_order").removeClass("pending");
						},
					});
				}
			}
		});
});
