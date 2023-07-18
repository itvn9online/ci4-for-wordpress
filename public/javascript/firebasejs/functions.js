/*
 * Function dùng chung cho chức năng đăng nhập qua firebase
 */

// kiểm tra xem firebaseConfig được nhập liệu chưa
if (typeof firebaseConfig != "undefined") {
	firebase.initializeApp(firebaseConfig);
} else {
	console.log("firebaseConfig not found!");
}

var firebase_recommend_login = null;

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
function create_signInSuccessUrl() {
	// thêm chuỗi ngẫu nhiên
	if (
		typeof sign_in_success_params != "undefined" &&
		typeof sign_in_success_params["success_url"] != "undefined" &&
		sign_in_success_params["success_url"] != ""
	) {
		//console.log(sign_in_success_params);
		return sign_in_success_params["success_url"];
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
		defaultCountry: set_value_firebase_config(
			firebase_dynamic_config.default_country,
			"VN"
		),
		// For prefilling the national number, set defaultNationNumber.
		// This will only be observed if only phone Auth provider is used since
		// for multiple providers, the NASCAR screen will always render first
		// with a 'sign in with phone number' button.
		//
		defaultNationalNumber: cut_zero_first_in_phone_number(
			firebase_dynamic_config.default_national_number
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
		loginHint: set_value_firebase_config(
			firebase_dynamic_config.login_hint,
			"+84"
		),
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
	var user = firebase.auth().currentUser;
	if (user === null) {
		return false;
	}
	console.log(user);
	console.log(user.emailVerified);
	console.log(user.photoURL);
	console.log(user.uid);
	console.log(user.providerData);
	user.getIdToken().then(function (accessToken) {
		console.log(accessToken);
	});

	//
	return user;
}

function test_result_user_data(user) {
	for (var x in user) {
		console.log(x + ":", user[x]);
	}
}

function addSignInSuccessParams(data) {
	// chạy vòng lặp bổ sung tham số bảo mật
	for (var x in sign_in_success_params) {
		// bỏ qua tham số URL
		if (x == "success_url" || x == "token_url") {
			continue;
		}
		data[x] = sign_in_success_params[x];
	}
	return data;
}

function afterRequestTokenSignIn(data) {
	// có lỗi thì thông báo lỗi
	if (typeof data.error != "undefined" && data.error != "") {
		//console.log(data);
		if (typeof data.code != "undefined" && data.code > 0) {
			data.error += " (#" + data.code + ")";
		}

		//
		WGR_alert(data.error, "error");
	}

	// logout session nếu có yêu cầu
	if (typeof data.auto_logout != "undefined" && data.auto_logout > 0) {
		firebaseSignOut();
	}

	// nạp lại trang nếu có yêu cầu
	if (typeof data.reload != "undefined" && data.reload * 1 > 0) {
		console.log("Waiting reload page...");
		jQuery("#firebase-loaded").css({
			opacity: 0.1,
		});
		setTimeout(function () {
			login_reload();
		}, 2000);
	}
	// trả về true nếu quá trình đăng nhập ok
	else if (typeof data.ok != "undefined" && data.ok * 1 > 0) {
		return true;
	}
	return false;
}

function parseJwt(token) {
	//console.log(token);
	token = token.split(".");
	if (token.length > 2) {
		return Base64.decode(token[1]);
	}
	return {};
}

// ngay sau khi đăng nhập thành công trên firebase -> thực hiện đăng nhập trên web thôi
function action_signInSuccessWithAuthResult(successfully) {
	//console.log(Math.random());
	var user = firebase.auth().currentUser;
	if (user === null) {
		return false;
	}
	if (typeof successfully == "undefined") {
		successfully = false;
	}

	//
	firebase
		.auth()
		.currentUser.getIdToken(true)
		.then(function (idToken) {
			//console.log(idToken);
			//return false;
			// Send token to your backend via HTTPS
			if (successfully !== false) {
				var data = {
					token_url: Math.random(),
					uid: user.uid,
					id_token: idToken,
				};
				data = addSignInSuccessParams(data);
				//console.log(data);
				//return false;

				//
				jQuery.ajax({
					type: "POST",
					//url: sign_in_success_params["token_url"],
					url: create_signInSuccessUrl(),
					dataType: "json",
					//crossDomain: true,
					data: data,
					timeout: 33 * 1000,
					error: function (jqXHR, textStatus, errorThrown) {
						jQueryAjaxError(jqXHR, textStatus, errorThrown, new Error().stack);
					},
					success: function (data) {
						//console.log(data);
						//return false;

						//
						if (typeof data.id_token != "undefined" && data.id_token != "") {
							action_signInSuccessWithIdToken(data.id_token, true);
						} else {
							afterRequestTokenSignIn(data);
							console.log(Math.random());
						}
					},
				});
			} else {
				action_signInSuccessWithIdToken(idToken, successfully);
			}
		})
		.catch(function (error) {
			console.log(error);
		});
}

function action_signInSuccessWithIdToken(idToken, successfully) {
	//console.log(idToken);
	var user = firebase.auth().currentUser;
	if (user === null) {
		return false;
	}

	// dịch ngược token và kiểm tra qua thông số trước
	var jwt = parseJwt(idToken);
	//console.log(jwt);
	//return false;
	if (
		typeof jwt.user_id == "undefined" ||
		jwt.user_id != user.uid ||
		typeof jwt.aud == "undefined" ||
		jwt.aud != firebaseConfig.projectId
	) {
		//console.log(jwt);
		WGR_alert("token mismatched!", "error");
		return false;
	}

	//
	if (typeof successfully == "undefined") {
		successfully = false;
	}
	//var credential = authResult.credential;
	//var isNewUser = authResult.additionalUserInfo.isNewUser;
	//var providerId = authResult.additionalUserInfo.providerId;
	//var operationType = authResult.operationType;
	// Do something with the returned AuthResult.
	// Return type determines whether we continue the redirect
	// automatically or whether we leave that to developer to handle.
	//console.log(create_signInSuccessUrl());
	//console.log(sign_in_success_params);
	//console.log(user.displayName);
	//console.log(user.email);
	//console.log(user.emailVerified);
	//console.log(user.phoneNumber);
	//
	//test_result_user_data(user);

	//
	var data = {
		uid: user.uid,
		name: user.displayName,
		email: user.email,
		verified: user.emailVerified,
		phone: user.phoneNumber,
		photo: user.photoURL,
		anonymous: user.isAnonymous,
		id_token: idToken,
		project_id: firebaseConfig.projectId,
		apikey: user.l,
		apiurl: user.s,
		successfully: successfully,
	};
	data = addSignInSuccessParams(data);

	//
	/*
	if (successfully !== false) {
		localStorage.setItem("fb_signin_success_params", JSON.stringify(data));
	}
	*/

	//
	//console.log(data);
	//console.log(data.id_token.length);
	//return false;

	//
	jQuery("body").css({
		opacity: 0.1,
	});

	//
	jQuery.ajax({
		type: "POST",
		url: create_signInSuccessUrl(),
		dataType: "json",
		//crossDomain: true,
		data: data,
		timeout: 33 * 1000,
		error: function (jqXHR, textStatus, errorThrown) {
			jQueryAjaxError(jqXHR, textStatus, errorThrown, new Error().stack);
		},
		success: function (data) {
			//console.log(data);
			//return false;

			//
			jQuery("body").css({
				opacity: 1,
			});

			//
			if (afterRequestTokenSignIn(data) === true) {
				var a = WGR_get_params("login_redirect");
				if (a != "") {
					//console.log(a);
					a = decodeURIComponent(a);
					//console.log(a);
					if (a.split("//").length <= 1) {
						a = set_value_firebase_config(
							firebase_dynamic_config.sign_in_redirect_to,
							web_link
						);
					}
				} else {
					if (
						typeof data.redirect_to != "undefined" &&
						data.redirect_to != ""
					) {
						a = data.redirect_to;
					} else {
						a = set_value_firebase_config(
							firebase_dynamic_config.sign_in_redirect_to,
							web_link
						);
					}
				}

				// thoát phiên firebase ngay sau khi đăng nhập thành công -> gia tăng độ bảo mật
				if (firebase_dynamic_config.save_session != "on") {
					firebaseSignOut("", a);
				} else {
					window.location = a;
				}
			}
		},
	});
	return true;
}

function login_reload() {
	window.location = window.location.href;
}

function continueSignIn() {
	clearTimeout(firebase_recommend_login);
	action_signInSuccessWithAuthResult(true);
}

function firebaseSignOut(m, redirect_to) {
	if (typeof m != "undefined" && m != "") {
		if (confirm(m) !== true) {
			return false;
		}
	}

	//
	firebase
		.auth()
		.signOut()
		.then(() => {
			// Sign-out successful.
			if (typeof redirect_to != "undefined" && redirect_to != "") {
				window.location = redirect_to;
			}
		})
		.catch((error) => {
			// An error happened.
			console.log(error);
		});
}

function firebaseDeleteAccountt(m) {
	if (confirm(m) === true) {
		deleteAccount();
	}
}

function action_signInSuccessUrl() {
	var a = WGR_get_params("login_redirect");
	if (a != "") {
		//console.log(a);
		a = decodeURIComponent(a);
		//console.log(a);
		if (a.split("//").length > 1) {
			return a;
		}
	}
	//return window.location.href;
	return set_value_firebase_config(
		firebase_dynamic_config.sign_in_redirect_to,
		web_link
	);
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
	//console.log(Math.random());
}

function action_privacyPolicyUrl() {
	window.location.assign(
		set_value_firebase_config(
			firebase_dynamic_config.privacy_policy_url,
			web_link
		)
	);
}

function build_tosUrl() {
	return set_value_firebase_config(
		firebase_dynamic_config.terms_service_url,
		web_link
	);
}
