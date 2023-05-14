/*
 * Function dùng chung cho chức năng đăng nhập qua firebase
 */

// kiểm tra xem firebaseConfig được nhập liệu chưa
if (typeof firebaseConfig != "undefined") {
	firebase.initializeApp(firebaseConfig);
} else {
	console.log("firebaseConfig not found!");
}

// cắt bỏ số 0 ở đầu mỗi số điện thoại -> sử dụng số điện thoại theo tiêu chuẩn quốc tế
function cut_zero_first_in_phone_number(str) {
	//console.log(str);
	//console.log(str.substr(0, 1));
	if (str.length > 9 && str.substr(0, 1) == "0") {
		str = str.substr(1);
	}
	return str;
}

// thiết lập giá trị mặc định cho các config trống
function set_value_firebase_config(val, default_val) {
	if (typeof default_val == "undefined") {
		default_val = "";
	}
	return typeof val != "undefined" && val != "" ? val : default_val;
}

// tạo URL xác thực sau khi đăng nhập thành công
function create_sign_in_success_url() {
	// thêm chuỗi ngẫu nhiên
	if (typeof sign_in_success_url != "undefined") {
		//console.log(sign_in_success_url);
		return sign_in_success_url;
	}
	// loại bỏ mọi thể loại parameter
	return window.location.href.split("?")[0].split("&")[0];
}

/*
 * Tạo config đăng nhập qua số điện thoại
 */
function buildPhoneAuthProvider() {
	// The Provider you need for your app. We need the Phone Auth
	return {
		provider: firebase.auth.PhoneAuthProvider.PROVIDER_ID,
		recaptchaParameters: {
			//size: getRecaptchaMode()
			type: "image",
			size: "invisible",
			badge: "bottomleft",
		},
		// Set default country to the Vietnam (+84).
		defaultCountry: set_value_firebase_config(firebase_default_country, "VN"),
		// For prefilling the national number, set defaultNationNumber.
		// This will only be observed if only phone Auth provider is used since
		// for multiple providers, the NASCAR screen will always render first
		// with a 'sign in with phone number' button.
		//
		defaultNationalNumber: cut_zero_first_in_phone_number(
			default_national_number
		),
		// You can also pass the full phone number string instead of the
		// 'defaultCountry' and 'defaultNationalNumber'. However, in this case,
		// the first country ID that matches the country code will be used to
		// populate the country selector. So for countries that share the same
		// country code, the selected country may not be the expected one.
		// In that case, pass the 'defaultCountry' instead to ensure the exact
		// country is selected. The 'defaultCountry' and 'defaultNationaNumber'
		// will always have higher priority than 'loginHint' which will be ignored
		// in their favor. In this case, the default country will be 'GB' even
		// though 'loginHint' specified the country code as '+1'.
		//
		loginHint: set_value_firebase_config(firebase_login_hint, "+84"),
		// You can provide a 'whitelistedCountries' or 'blacklistedCountries' for
		// countries to select. It takes an array of either ISO (alpha-2) or
		// E164 (prefix with '+') formatted country codes. If 'defaultCountry' is
		// not whitelisted or is blacklisted, the default country will be set to
		// the first country available (alphabetical order). Notice that
		// 'whitelistedCountries' and 'blacklistedCountries' cannot be specified
		// at the same time.
		//
		//whitelistedCountries: ["US", "+44", "+84"],
	};
}

/*
 * Một số chức năng sau khi đăng nhập thành công thì viết chung vào function như này
 */
function action_handleSignedInUser() {
	if (WGR_config.cf_tester_mode > 0) {
		console.log(user);
		console.log(user.emailVerified);
		console.log(user.photoURL);
		console.log(user.uid);
		console.log(user.providerData);
	}
	user.getIdToken().then(function (accessToken) {
		//console.log(accessToken);
	});
}

// ngay sau khi đăng nhập thành công trên firebase -> thực hiện đăng nhập trên web thôi
function action_signInSuccessWithAuthResult(authResult, redirectUrl) {
	//console.log(Math.random());
	//console.log(redirectUrl);
	var user = authResult.user;
	//var credential = authResult.credential;
	//var isNewUser = authResult.additionalUserInfo.isNewUser;
	//var providerId = authResult.additionalUserInfo.providerId;
	//var operationType = authResult.operationType;
	// Do something with the returned AuthResult.
	// Return type determines whether we continue the redirect
	// automatically or whether we leave that to developer to handle.
	console.log(create_sign_in_success_url());
	//console.log(user.displayName);
	//console.log(user.email);
	//console.log(user.emailVerified);
	//console.log(user.phoneNumber);
	/*
	for (var x in user) {
		console.log(x + ":", user[x]);
	}
	*/
	var data = {
		name: user.displayName,
		email: user.email,
		verified: user.emailVerified,
		phone: user.phoneNumber,
		photo: user.photoURL,
		anonymous: user.isAnonymous,
		apikey: user.l,
		apiurl: user.s,
	};
	//console.log(data);
	//return false;

	//
	jQuery.ajax({
		type: "POST",
		url: create_sign_in_success_url(),
		dataType: "json",
		//crossDomain: true,
		data: data,
		timeout: 33 * 1000,
		error: function (jqXHR, textStatus, errorThrown) {
			console.log(jqXHR);
			if (typeof jqXHR.responseText != "undefined") {
				console.log(jqXHR.responseText);
			}
			console.log(errorThrown);
			console.log(textStatus);
			if (textStatus === "timeout") {
				//
			}
		},
		success: function (data) {
			console.log(data);
			if (typeof data.error != "undefined" && data.error != "") {
				WGR_alert(data.error, "error");
			} else if (typeof data.ok != "undefined" && data.ok * 1 > 0) {
				window.location = window.location.href;
			}
		},
	});
	return true;
}

function action_signInFailure(error) {
	// Some unrecoverable error occurred during sign-in.
	// Return a promise when error handling is completed and FirebaseUI
	// will reset, clearing any UI. This commonly occurs for error code
	// 'firebaseui/anonymous-upgrade-merge-conflict' when merge conflict
	// occurs. Check below for more details on this.
	return handleUIError(error);
}

function action_uiShown() {
	// The widget is rendered.
	// Hide the loader.
	//document.getElementById("loader").style.display = "none";
}

function action_privacyPolicyUrl() {
	window.location.assign(
		set_value_firebase_config(firebase_privacy_policy_url, window.location.href)
	);
}

function build_tosUrl() {
	return set_value_firebase_config(
		firebase_terms_service_url,
		window.location.href
	);
}
