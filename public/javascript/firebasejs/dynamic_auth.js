/*
 * Thiết lập chức năng đăng nhập dựa theo config trong admin
 */
function dynamicUiConfig() {
	var a = [];
	a.push(firebase.auth.GoogleAuthProvider.PROVIDER_ID);
	a.push(firebase.auth.FacebookAuthProvider.PROVIDER_ID);
	a.push(firebase.auth.TwitterAuthProvider.PROVIDER_ID);
	a.push(firebase.auth.GithubAuthProvider.PROVIDER_ID);
	a.push(firebase.auth.EmailAuthProvider.PROVIDER_ID);
	a.push(firebaseui.auth.AnonymousAuthProvider.PROVIDER_ID);
	a.push(buildPhoneAuthProvider());
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
		//signInSuccessUrl: create_sign_in_success_url(),
		signInOptions: dynamicUiConfig(),
		// Privacy policy url/callback.
		privacyPolicyUrl: function () {
			action_privacyPolicyUrl();
		},
		// Terms of service url.
		tosUrl: build_tosUrl(),
	};
}
