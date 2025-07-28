/**
 * Simplified Firebase Configuration
 * Use this instead of multiple separate JS files
 */

// Check if this is phone-only authentication
const isPhoneOnlyAuth = window.location.pathname.includes("phone_auth");

/**
 * Get UI Configuration based on authentication type
 */
function getUiConfig() {
	if (isPhoneOnlyAuth) {
		// Phone-only authentication
		return {
			callbacks: {
				signInSuccessWithAuthResult: function (authResult, redirectUrl) {
					return window.FirebaseAuth.AuthenticationHandler.signInSuccessWithAuthResult(
						true
					);
				},
				signInFailure: function (error) {
					console.error("Sign in failure:", error);
					WGR_alert("Sign in failed. Please try again.", "error");
					return Promise.resolve();
				},
				uiShown: function () {
					console.log("Firebase UI shown");
				},
			},
			signInFlow: "popup",
			signInSuccessUrl: window.FirebaseAuth.Utils.createSignInSuccessUrl(),
			signInOptions: [
				window.FirebaseAuth.ConfigBuilder.buildPhoneAuthProvider(),
			],
			privacyPolicyUrl: function () {
				const url = window.FirebaseAuth.Utils.getConfigValue(
					firebase_dynamic_config?.privacy_policy_url,
					window.location.origin
				);
				window.location.assign(url);
			},
			tosUrl: window.FirebaseAuth.Utils.getConfigValue(
				firebase_dynamic_config?.terms_service_url,
				window.location.origin
			),
		};
	} else {
		// Dynamic authentication (multiple providers)
		return window.FirebaseAuth.ConfigBuilder.getUiConfig();
	}
}

// Override the global getUiConfig function
window.getUiConfig = getUiConfig;
