(function (contentSelector, tocSelector) {
	let content = document.querySelector(contentSelector);
	let toc = document.querySelector(tocSelector);

	if (!content || !toc) {
		console.error("Content or TOC container not found.");
		return;
	}

	let headings = content.querySelectorAll("h2, h3, h4, h5, h6");

	if (headings.length === 0) {
		console.warn("No headings found in the content.");
		return;
	}

	let tocList = document.createElement("ol");
	tocList.classList.add("wgr-toc-list");
	// Get the current URL
	// let currentURL = window.location.href;

	//
	let currentLevel = 2;
	let parents = [{ level: 2, ol: tocList }];

	headings.forEach((heading, index) => {
		let headingId = heading.id || `toc-heading-${index}`;
		heading.id = headingId;
		let headingLevel = parseInt(heading.tagName.substring(1));

		let listItem = document.createElement("li");
		let span = document.createElement("span");
		span.textContent = heading.textContent;
		span.setAttribute("data-href", `${headingId}`);
		listItem.appendChild(span);

		// Xử lý phân nhánh cha-con
		if (headingLevel > currentLevel) {
			// Tạo ol mới lồng vào li trước đó
			let newOl = document.createElement("ol");
			parents[parents.length - 1].ol.lastElementChild &&
				parents[parents.length - 1].ol.lastElementChild.appendChild(newOl);
			parents.push({ level: headingLevel, ol: newOl });
			currentLevel = headingLevel;
		} else if (headingLevel < currentLevel) {
			// Quay lại cấp cha tương ứng
			while (
				parents.length > 1 &&
				parents[parents.length - 1].level > headingLevel
			) {
				parents.pop();
			}
			currentLevel = headingLevel;
		}
		parents[parents.length - 1].ol.appendChild(listItem);
	});

	// nếu không có thẻ h2, h3, h4, h5, h6 nào thì không hiển thị toc
	if (tocList.children.length < 1) {
		return;
	}

	//
	toc.prepend(tocList);

	//
	$(".wgr-toc-list").wrap('<div class="wgr-toc-container"></div>');

	//
	jQuery(".wgr-toc-list").before(
		'<div class="wgr-toc-title bold"><i class="fa fa-list"></i> Nội dung chính</div>' +
			'<button class="wgr-toc-toggle-btn" type="button"><i class="fa fa-angle-down"></i></button>'
	);

	//
	jQuery(".wgr-toc-list li span").click(function (e) {
		// e.preventDefault();
		var headingId = jQuery(this).attr("data-href");
		// alert(headingId);
		// var target = jQuery('[data-scroll="' + headingId + '"]');
		var target = jQuery("#" + headingId);
		// alert(target.offset().top);
		if (target.length) {
			jQuery("html, body").animate(
				{
					scrollTop: target.offset().top - 100,
				},
				500
			);

			// Thêm class active cho mục đã click
			target.addClass("wgr-toc-active");
			// Xóa class active khỏi các mục khác
			jQuery(".wgr-toc-active").removeClass("wgr-toc-actived");
			// Thêm class actived cho mục đã click
			target.addClass("wgr-toc-actived");
		}
	});

	//
	jQuery(".wgr-toc-toggle-btn").click(function () {
		$(".wgr-toc-container").toggleClass("wgr-toc-collapsed");
	});
})(".global-details-content", ".global-details-content");
