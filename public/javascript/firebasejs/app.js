/**
 * @return {!Object} The FirebaseUI config.
 * https://github.com/firebase/firebaseui-web
 * https://firebase.google.com/pricing
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

/*
 * https://github.com/firebase/firebaseui-web#configure-phone-provider
 */
function getUiConfig() {
	return {
		callbacks: {
			// Called when the user has been successfully signed in.
			/*
			signInSuccess: function (user, credential, redirectUrl) {
				handleSignedInUser(user);
				// Do not redirect.
				return false;
			},
      		*/
			signInSuccessWithAuthResult: function (authResult, redirectUrl) {
				// If a user signed in with email link, ?showPromo=1234 can be obtained from
				// window.location.href.
				return false;
			},
		},
		// Opens IDP Providers sign-in flow in a popup.
		signInFlow: "popup",
		signInOptions: [
			// The Provider you need for your app. We need the Phone Auth
			//firebase.auth.GoogleAuthProvider.PROVIDER_ID,
			//firebase.auth.FacebookAuthProvider.PROVIDER_ID,
			//firebase.auth.TwitterAuthProvider.PROVIDER_ID,
			//firebase.auth.GithubAuthProvider.PROVIDER_ID,
			//firebase.auth.EmailAuthProvider.PROVIDER_ID,
			//firebase.auth.PhoneAuthProvider.PROVIDER_ID,
			//firebaseui.auth.AnonymousAuthProvider.PROVIDER_ID,
			{
				provider: firebase.auth.PhoneAuthProvider.PROVIDER_ID,
				recaptchaParameters: {
					//size: getRecaptchaMode()
					type: "image",
					size: "invisible",
					badge: "bottomleft",
				},
				// Set default country to the Vietnam (+84).
				defaultCountry: set_value_firebase_config(
					g_firebase_default_country,
					"VN"
				),
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
				loginHint: set_value_firebase_config(g_firebase_login_hint, "+84"),
				// You can provide a 'whitelistedCountries' or 'blacklistedCountries' for
				// countries to select. It takes an array of either ISO (alpha-2) or
				// E164 (prefix with '+') formatted country codes. If 'defaultCountry' is
				// not whitelisted or is blacklisted, the default country will be set to
				// the first country available (alphabetical order). Notice that
				// 'whitelistedCountries' and 'blacklistedCountries' cannot be specified
				// at the same time.
				//
				//whitelistedCountries: ["US", "+44", "+84"],
			},
		],
		// Privacy policy url/callback.
		privacyPolicyUrl: function () {
			window.location.assign(
				set_value_firebase_config(
					firebase_privacy_policy_url,
					window.location.href
				)
			);
		},
		// Terms of service url.
		tosUrl: set_value_firebase_config(
			firebase_terms_service_url,
			window.location.href
		),
	};
}

// Initialize the FirebaseUI Widget using Firebase.
var ui = new firebaseui.auth.AuthUI(firebase.auth());

/**
 * Displays the UI for a signed in user.
 * @param {!firebase.User} user
 */
var handleSignedInUser = function (user) {
	if (WGR_config.cf_tester_mode > 0) console.log(user);
	document.getElementById("user-signed-in").style.display = "block";
	document.getElementById("user-signed-out").style.display = "none";
	document.getElementById("firebase_name").textContent = user.displayName;
	document.getElementById("firebase_email").textContent = user.email;
	document.getElementById("firebase_phone").textContent = user.phoneNumber;
	if (document.getElementById("firebase_photo") !== null) {
		if (user.photoURL) {
			document.getElementById("firebase_photo").src = user.photoURL;
			document.getElementById("firebase_photo").style.display = "block";
		} else {
			document.getElementById("firebase_photo").style.display = "none";
		}
	}
};

/**
 * Displays the UI for a signed out user.
 */
var handleSignedOutUser = function () {
	document.getElementById("user-signed-in").style.display = "none";
	document.getElementById("user-signed-out").style.display = "block";
	ui.start("#firebaseui-container", getUiConfig());
};

// Listen to change in auth state so it displays the correct UI for when
// the user is signed in or not.
firebase.auth().onAuthStateChanged(
	function (user) {
		document.getElementById("loading").style.display = "none";
		document.getElementById("loaded").style.display = "block";
		if (user) {
			// User is signed in.
			handleSignedInUser(user);
		} else {
			// User is signed out.
			handleSignedOutUser();
		}
	},
	function (error) {
		console.log(error);
	}
);

/**
 * Deletes the user's account.
 */
var deleteAccount = function () {
	firebase
		.auth()
		.currentUser.delete()
		.catch(function (error) {
			if (error.code == "auth/requires-recent-login") {
				// The user's credential is too old. She needs to sign in again.
				firebase
					.auth()
					.signOut()
					.then(function () {
						// The timeout allows the message to be displayed after the UI has
						// changed to the signed out state.
						setTimeout(function () {
							WGR_alert(
								"Please sign in again to delete your account.",
								"error"
							);
						}, 100);
					});
			}
		});
};

/**
 * Initializes the app.
 */
var initApp = function () {
	document.getElementById("sign-out").addEventListener("click", function () {
		firebase.auth().signOut();
	});
	document
		.getElementById("delete-account")
		.addEventListener("click", function () {
			deleteAccount();
		});
};

window.addEventListener("load", initApp);
