/*
 * Thiết lập chức năng đăng nhập dựa theo config trong admin
 */
function dynamicUiConfig() {
	var a = [];
	if (firebase_dynamic_config.google == "on") {
		a.push(firebase.auth.GoogleAuthProvider.PROVIDER_ID);
	}
	if (firebase_dynamic_config.facebook == "on") {
		a.push(firebase.auth.FacebookAuthProvider.PROVIDER_ID);
	}
	if (firebase_dynamic_config.twitter == "on") {
		a.push(firebase.auth.TwitterAuthProvider.PROVIDER_ID);
	}
	if (firebase_dynamic_config.github == "on") {
		a.push(firebase.auth.GithubAuthProvider.PROVIDER_ID);
	}
	if (firebase_dynamic_config.apple == "on") {
		a.push("apple.com");
	}
	if (firebase_dynamic_config.microsoft == "on") {
		a.push("microsoft.com");
	}
	if (firebase_dynamic_config.yahoo == "on") {
		a.push("yahoo.com");
	}
	if (firebase_dynamic_config.email == "on") {
		a.push(firebase.auth.EmailAuthProvider.PROVIDER_ID);
	}
	if (firebase_dynamic_config.anonymous == "on") {
		a.push(firebaseui.auth.AnonymousAuthProvider.PROVIDER_ID);
	}
	if (firebase_dynamic_config.phone == "on") {
		a.push(buildPhoneAuthProvider());
	}
	//console.log(a);

	//
	return a;
}

function getUiConfig() {
	return {
		callbacks: {
			// Called when the user has been successfully signed in.
			signInSuccessWithAuthResult: function (authResult, redirectUrl) {
				return action_signInSuccessWithAuthResult(true);
			},
			signInFailure: function (error) {
				return action_signInFailure(error);
			},
			uiShown: function () {
				return action_uiShown();
			},
		},
		// Opens IDP Providers sign-in flow in a popup.
		signInFlow: "popup",
		signInSuccessUrl: action_signInSuccessUrl(),
		signInOptions: dynamicUiConfig(),
		// Privacy policy url/callback.
		privacyPolicyUrl: function () {
			action_privacyPolicyUrl();
		},
		// Terms of service url.
		tosUrl: build_tosUrl(),
	};
}
