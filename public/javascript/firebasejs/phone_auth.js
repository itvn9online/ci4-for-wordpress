/*
 * Thiết lập chức năng đăng nhập bằng số điện thoại
 * https://github.com/firebase/firebaseui-web#configure-phone-provider
 */

var required_firebase_phone_number = true;

function phoneUiConfig() {
	var a = [];
	if (firebase_dynamic_config.verify_phone == "on") {
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
		signInOptions: phoneUiConfig(),
		// Privacy policy url/callback.
		privacyPolicyUrl: function () {
			action_privacyPolicyUrl();
		},
		// Terms of service url.
		tosUrl: build_tosUrl(),
	};
}
