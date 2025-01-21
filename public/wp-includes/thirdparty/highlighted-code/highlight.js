/**
 * https://codepen.io/WebCoder49/pen/dyNyraq
 * https://prismjs.com/
 */
var highlight = {
	update: function (text, fors_id) {
		let ele = highlight.my_element(fors_id);
		let result_element = document.querySelector("#" + ele.hl_content);
		// Handle final newlines (see article)
		if (text.slice(-1) == "\n") {
			text += " ";
		}
		// Update code
		result_element.innerHTML = text
			.replace(new RegExp("&", "g"), "&amp;")
			.replace(new RegExp("<", "g"), "&lt;"); /* Global RegExp */
		// Syntax Highlight
		Prism.highlightElement(result_element);

		//
		// console.log(fors_id);
		highlight.no_scroll(fors_id);
	},

	// trả về class hoặc id cần xử lý dữ liệu
	my_element: function (fors_id) {
		let a = fors_id.split(",");
		// console.log(a);
		return {
			for_id: a[0],
			highlighting: a[1],
			hl_content: a[2],
		};
	},

	show: function (fors_id) {
		let ele = highlight.my_element(fors_id);
		jQuery("#" + ele.highlighting).show();
	},

	no_scroll: function (fors_id) {
		let ele = highlight.my_element(fors_id);
		// console.log("for_id height", jQuery(ele.for_id).height());
		jQuery("#" + ele.highlighting).css({
			height: jQuery(ele.for_id).height(),
		});
	},

	sync_scroll: function (element, fors_id) {
		let ele = highlight.my_element(fors_id);
		/* Scroll result to scroll coords of event - sync with textarea */
		let result_element = document.querySelector("#" + ele.highlighting);
		// Get and set x and y
		result_element.scrollTop = element.scrollTop;
		result_element.scrollLeft = element.scrollLeft;

		//
		// highlight.no_scroll(fors_id);
	},

	// khi người dùng nhấn nút tab -> thay vì chuyển sang block khác thì tạo tab cho textarea
	check_tab: function (element, event, fors_id) {
		let code = element.value;
		if (event.key == "Tab") {
			/* Tab key pressed */
			event.preventDefault(); // stop normal
			let before_tab = code.slice(0, element.selectionStart); // text before tab
			let after_tab = code.slice(element.selectionEnd, element.value.length); // text after tab
			let cursor_pos = element.selectionStart + 1; // where cursor moves after tab - moving forward by 1 char to after tab
			element.value = before_tab + "\t" + after_tab; // add tab char
			// move cursor
			element.selectionStart = cursor_pos;
			element.selectionEnd = cursor_pos;
			highlight.update(element.value, fors_id); // Update text to include indent
		}
	},
};
