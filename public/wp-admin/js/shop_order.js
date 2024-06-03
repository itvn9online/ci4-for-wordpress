$(document).ready(function () {
	$(".post_excerpt-to-products").each(function () {
		let a = $(this).html();
		a = JSON.parse(a);
		// console.log(a);

		//
		let str = [];
		for (let i = 0; i < a.length; i++) {
			str.push(
				'- <a href="' +
					web_link +
					"?p=" +
					a[i].ID +
					'" target="_blank">' +
					a[i].post_title +
					" (" +
					a[i]._price +
					" * " +
					a[i]._quantity +
					")</a>"
			);
		}
		$(this).html(str.join("<br />"));
	});
});
