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

	headings.forEach((heading, index) => {
		let headingId = heading.id || `toc-heading-${index}`;
		heading.id = headingId;

		let listItem = document.createElement("li");
		listItem.setAttribute("data-href", `${headingId}`);
		// let link = document.createElement("a");
		// link.href = currentURL + `#${headingId}`;
		// link.textContent = heading.textContent;
		listItem.textContent = heading.textContent;

		// listItem.appendChild(link);
		tocList.appendChild(listItem);
	});

	//
	toc.prepend(tocList);

	//
	$(".wgr-toc-list").wrap('<div class="wgr-toc-container"></div>');

	//
	jQuery(".wgr-toc-list").prepend(
		'<div class="wgr-toc-title bold">Nội dung chính</div>'
	);

	//
	jQuery(".wgr-toc-list li").click(function (e) {
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
		}
	});
})(".global-details-content", ".global-details-content");
