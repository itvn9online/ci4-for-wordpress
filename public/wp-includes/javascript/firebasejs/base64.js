/**
 *
 *  Base64 encode / decode
 *  http://www.webtoolkit.info/
 *  https://stackoverflow.com/questions/38552003/how-to-decode-jwt-token-in-javascript-without-using-a-library
 *
 **/
var Base64 = {
	// public method for encoding
	encode: function (input) {},

	// public method for decoding
	decode: function (input) {
		var b64 = input.replace(/-/g, "+").replace(/_/g, "/");
		var jsonPayload = decodeURIComponent(
			window
				.atob(b64)
				.split("")
				.map(function (c) {
					return "%" + ("00" + c.charCodeAt(0).toString(16)).slice(-2);
				})
				.join("")
		);

		return JSON.parse(jsonPayload);
	},
};
