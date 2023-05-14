/*
 * Thiết lập chức năng đăng nhập dựa theo config trong admin
 */
function dynamicUiConfig() {
	var a = [];
	if (firebase_auth_google == "on") {
		a.push(firebase.auth.GoogleAuthProvider.PROVIDER_ID);
	}
	if (firebase_auth_facebook == "on") {
		a.push(firebase.auth.FacebookAuthProvider.PROVIDER_ID);
	}
	if (firebase_auth_twitter == "on") {
		a.push(firebase.auth.TwitterAuthProvider.PROVIDER_ID);
	}
	if (firebase_auth_github == "on") {
		a.push(firebase.auth.GithubAuthProvider.PROVIDER_ID);
	}
	if (firebase_auth_email == "on") {
		a.push(firebase.auth.EmailAuthProvider.PROVIDER_ID);
	}
	if (firebase_auth_anonymous == "on") {
		a.push(firebaseui.auth.AnonymousAuthProvider.PROVIDER_ID);
	}
	if (firebase_auth_phone == "on") {
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
				return action_signInSuccessWithAuthResult(authResult, redirectUrl);
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
