(function () {
	let st = document.getElementsByTagName("script");
	console.log(st.length);
	for (let i = 0; i < st.length; i++) {
		console.log(st[i].src, typeof st[i].src);
		if (st[i].src == "") {
			console.log(st[i].innerHTML);
		}
	}
})();
