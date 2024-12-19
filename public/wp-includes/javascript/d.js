// Đại viết -> thêm đoạn JS add màu cho fa-heart -> khách muốn đánh dấu các sản phẩm đã like
//console.log(js_favoriteProduct);
js_favoriteProduct = JSON.parse(js_favoriteProduct);
//console.log(js_favoriteProduct);
(function () {
	for (let i = 0; i < js_favoriteProduct.length; i++) {
		jQuery(
			'.product-detail[data-id="' +
				js_favoriteProduct[i].product_id +
				'"] .action-btn .btn-action-style .fa-heart'
		).addClass("redcolor");
	}
})();
