/**
 * Optimized Firebase Functions
 *
 * Improvements:
 * - Better error handling
 * - Cleaner code structure
 * - Constants for magic values
 * - Improved security
 * - Better performance
 */

// Constants
const FIREBASE_CONSTANTS = {
	MIN_PHONE_LENGTH: 9,
	DEFAULT_COUNTRY: "VN",
	DEFAULT_LOGIN_HINT: "+84",
	CACHE_TIMEOUT: 33000, // 33 seconds
	AUTO_LOGIN_DELAY: 5000, // 5 seconds
	RELOAD_DELAY: 2000, // 2 seconds
};

// Configuration validation
if (typeof firebaseConfig === "undefined") {
	console.error("firebaseConfig not found!");
	throw new Error("Firebase configuration is required");
}

// Initialize Firebase
firebase.initializeApp(firebaseConfig);

// Global variables
let firebase_recommend_login = null;
let ui = new firebaseui.auth.AuthUI(firebase.auth());

/**
 * Utility Functions
 */
const FirebaseUtils = {
	/**
	 * Remove leading zero from phone number for international standard
	 */
	normalizePhoneNumber(phoneStr) {
		if (!phoneStr || typeof phoneStr !== "string") {
			return phoneStr;
		}

		if (
			phoneStr.length > FIREBASE_CONSTANTS.MIN_PHONE_LENGTH &&
			phoneStr.startsWith("0")
		) {
			return phoneStr.slice(1);
		}
		return phoneStr;
	},

	/**
	 * Set default value for empty config values
	 */
	getConfigValue(value, defaultValue = "") {
		return typeof value !== "undefined" && value !== "" ? value : defaultValue;
	},

	/**
	 * Create success URL for post-authentication redirect
	 */
	createSignInSuccessUrl() {
		if (
			typeof sign_in_params_success !== "undefined" &&
			typeof sign_in_params_success.success_url !== "undefined" &&
			sign_in_params_success.success_url !== ""
		) {
			return sign_in_params_success.success_url;
		}
		return window.location.href.split("?")[0].split("&")[0];
	},

	/**
	 * Validate required dependencies
	 */
	validateDependencies() {
		const required = ["firebase", "firebaseui", "jQuery"];
		const missing = required.filter(
			(dep) => typeof window[dep] === "undefined"
		);

		if (missing.length > 0) {
			throw new Error(`Missing required dependencies: ${missing.join(", ")}`);
		}
	},

	/**
	 * Safe jQuery selector with fallback
	 */
	safeJQuery(selector) {
		try {
			return jQuery(selector);
		} catch (e) {
			console.error(`jQuery selector failed: ${selector}`, e);
			return {
				show: () => {},
				hide: () => {},
				html: () => {},
				css: () => {},
			};
		}
	},
};

/**
 * Authentication State Management
 */
const AuthStateManager = {
	/**
	 * Handle signed in user display
	 */
	handleSignedInUser() {
		const user = firebase.auth().currentUser;
		if (!user) {
			return false;
		}

		this.updateUserInterface(user);
		this.validatePhoneRequirement(user);
		this.updateUserPhoto(user);
		this.handleAutoLogin();

		return true;
	},

	/**
	 * Update user interface with user data
	 */
	updateUserInterface(user) {
		const $ = FirebaseUtils.safeJQuery;

		$("#user-signed-in").show();
		$("#user-signed-out").hide();
		$("#firebase_name").html(user.displayName || "");
		$("#firebase_email").html(user.email || "");
		$("#firebase_phone").html(user.phoneNumber || "");
	},

	/**
	 * Validate phone number requirement if configured
	 */
	validatePhoneRequirement(user) {
		if (
			typeof required_firebase_phone_number !== "undefined" &&
			required_firebase_phone_number === true &&
			!user.phoneNumber
		) {
			WGR_alert("Phone number not found!", "error");
			FirebaseUtils.safeJQuery("#sign-in").hide();
			return false;
		}
		return true;
	},

	/**
	 * Update user photo if available
	 */
	updateUserPhoto(user) {
		if (user.photoURL) {
			const imgHtml = `<img src="${user.photoURL}" alt="User Photo" />`;
			FirebaseUtils.safeJQuery("#firebase_photo").html(imgHtml);
		}
	},

	/**
	 * Handle auto-login functionality
	 */
	handleAutoLogin() {
		if (localStorage.getItem("firebase_auto_login") === null) {
			return;
		}

		// Immediate login option (currently disabled)
		if (false) {
			// Change to true for immediate login
			this.continueSignIn();
		} else {
			this.scheduleAutoLogin();
		}
	},

	/**
	 * Schedule auto-login with delay
	 */
	scheduleAutoLogin() {
		WGR_alert("Auto login after 5 seconds...");
		firebase_recommend_login = setTimeout(() => {
			if (localStorage.getItem("firebase_auto_login") !== null) {
				this.continueSignIn();
			} else {
				WGR_alert("Auto login has been canceled!", "warning");
			}
		}, FIREBASE_CONSTANTS.AUTO_LOGIN_DELAY);
	},

	/**
	 * Continue with sign in process
	 */
	continueSignIn() {
		clearTimeout(firebase_recommend_login);
		AuthenticationHandler.signInSuccessWithAuthResult(true);
	},

	/**
	 * Handle signed out user display
	 */
	handleSignedOutUser() {
		FirebaseUtils.safeJQuery("#user-signed-in").hide();
		FirebaseUtils.safeJQuery("#user-signed-out").show();

		try {
			ui.start("#firebaseui-container", ConfigBuilder.getUiConfig());
		} catch (error) {
			console.error("Failed to start Firebase UI:", error);
			WGR_alert(
				"Authentication system error. Please refresh the page.",
				"error"
			);
		}
	},
};

/**
 * Authentication Handler
 */
const AuthenticationHandler = {
	/**
	 * Handle successful sign in with auth result
	 */
	signInSuccessWithAuthResult(successfully = false) {
		const user = firebase.auth().currentUser;
		if (!user) {
			return false;
		}

		return firebase
			.auth()
			.currentUser.getIdToken(true)
			.then((idToken) => {
				if (successfully !== false) {
					return this.processSignInWithToken(idToken, successfully);
				} else {
					return this.processTokenOnly(idToken);
				}
			})
			.catch((error) => {
				console.error("Token generation failed:", error);
				WGR_alert("Authentication failed. Please try again.", "error");
				return false;
			});
	},

	/**
	 * Process sign in with ID token
	 */
	processSignInWithToken(idToken, successfully) {
		const user = firebase.auth().currentUser;
		const jwt = this.parseJwt(idToken);

		if (!this.validateJwtToken(jwt, user)) {
			return false;
		}

		const requestData = this.buildRequestData(user, idToken, successfully);
		return this.sendAuthenticationRequest(requestData);
	},

	/**
	 * Process token-only request
	 */
	processTokenOnly(idToken) {
		// Implementation for token-only processing
		console.log("Processing token-only request");
		return true;
	},

	/**
	 * Parse JWT token safely
	 */
	parseJwt(token) {
		try {
			return Base64.decode(token.split(".")[1]);
		} catch (error) {
			console.error("JWT parsing failed:", error);
			return {};
		}
	},

	/**
	 * Validate JWT token
	 */
	validateJwtToken(jwt, user) {
		if (!jwt.user_id || jwt.user_id !== user.uid) {
			WGR_alert("Token validation failed!", "error");
			return false;
		}

		if (!jwt.aud || jwt.aud !== firebaseConfig.projectId) {
			WGR_alert("Token mismatched!", "error");
			return false;
		}

		return true;
	},

	/**
	 * Build request data for authentication
	 */
	buildRequestData(user, idToken, successfully) {
		let data = {
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

		return this.addSignInSuccessParams(data);
	},

	/**
	 * Add success parameters to request data
	 */
	addSignInSuccessParams(data) {
		if (typeof sign_in_params_success === "undefined") {
			return data;
		}

		for (let key in sign_in_params_success) {
			if (key !== "success_url" && key !== "token_url") {
				data[key] = sign_in_params_success[key];
			}
		}
		return data;
	},

	/**
	 * Send authentication request to server
	 */
	sendAuthenticationRequest(data) {
		FirebaseUtils.safeJQuery("body").css({ opacity: 0.1 });

		return jQuery.ajax({
			type: "POST",
			url: FirebaseUtils.createSignInSuccessUrl(),
			dataType: "json",
			data: data,
			timeout: FIREBASE_CONSTANTS.CACHE_TIMEOUT,
			error: (jqXHR, textStatus, errorThrown) => {
				this.handleAjaxError(jqXHR, textStatus, errorThrown);
			},
			success: (response) => {
				this.handleAuthenticationResponse(response);
			},
		});
	},

	/**
	 * Handle AJAX error
	 */
	handleAjaxError(jqXHR, textStatus, errorThrown) {
		console.error("Authentication request failed:", textStatus, errorThrown);
		FirebaseUtils.safeJQuery("body").css({ opacity: 1 });
		WGR_alert(
			"Network error. Please check your connection and try again.",
			"error"
		);
	},

	/**
	 * Handle authentication response
	 */
	handleAuthenticationResponse(data) {
		if (data.error) {
			this.handleServerError(data);
			return;
		}

		if (data.auto_logout) {
			this.signOut();
			return;
		}

		if (data.reload) {
			this.schedulePageReload();
			return;
		}

		if (data.ok) {
			this.handleSuccessfulAuth();
		}
	},

	/**
	 * Handle server error response
	 */
	handleServerError(data) {
		FirebaseUtils.safeJQuery("body").css({ opacity: 1 });

		if (data.code) {
			console.error(`Server error ${data.code}:`, data.error);
		}

		WGR_alert(data.error, "error");
	},

	/**
	 * Schedule page reload
	 */
	schedulePageReload() {
		console.log("Waiting reload page...");
		FirebaseUtils.safeJQuery("#firebase-loaded").css({ opacity: 0.1 });

		setTimeout(() => {
			window.location.reload();
		}, FIREBASE_CONSTANTS.RELOAD_DELAY);
	},

	/**
	 * Handle successful authentication
	 */
	handleSuccessfulAuth() {
		console.log("Authentication successful");
		FirebaseUtils.safeJQuery("body").css({ opacity: 1 });
		// Additional success handling can be added here
	},

	/**
	 * Sign out user
	 */
	signOut(message = "", redirectTo = "") {
		if (message) {
			WGR_alert(message);
		}

		firebase
			.auth()
			.signOut()
			.then(() => {
				if (redirectTo) {
					window.location.href = redirectTo;
				} else {
					window.location.reload();
				}
			})
			.catch((error) => {
				console.error("Sign out error:", error);
				WGR_alert("Sign out failed. Please try again.", "error");
			});
	},
};

/**
 * Configuration Builder
 */
const ConfigBuilder = {
	/**
	 * Build phone authentication provider configuration
	 */
	buildPhoneAuthProvider() {
		return {
			provider: firebase.auth.PhoneAuthProvider.PROVIDER_ID,
			recaptchaParameters: {
				type: "image",
				size: "invisible",
				badge: "bottomleft",
			},
			defaultCountry: FirebaseUtils.getConfigValue(
				firebase_dynamic_config?.default_country,
				FIREBASE_CONSTANTS.DEFAULT_COUNTRY
			),
			defaultNationalNumber: FirebaseUtils.normalizePhoneNumber(
				firebase_dynamic_config?.default_national_number
			),
			loginHint: FirebaseUtils.getConfigValue(
				firebase_dynamic_config?.login_hint,
				FIREBASE_CONSTANTS.DEFAULT_LOGIN_HINT
			),
		};
	},

	/**
	 * Build dynamic UI configuration based on admin settings
	 */
	buildDynamicSignInOptions() {
		const options = [];

		if (!firebase_dynamic_config) {
			console.warn("firebase_dynamic_config not found, using default options");
			return [firebase.auth.EmailAuthProvider.PROVIDER_ID];
		}

		const providerMap = {
			google: firebase.auth.GoogleAuthProvider.PROVIDER_ID,
			facebook: firebase.auth.FacebookAuthProvider.PROVIDER_ID,
			twitter: firebase.auth.TwitterAuthProvider.PROVIDER_ID,
			github: firebase.auth.GithubAuthProvider.PROVIDER_ID,
			apple: "apple.com",
			microsoft: "microsoft.com",
			yahoo: "yahoo.com",
			email: firebase.auth.EmailAuthProvider.PROVIDER_ID,
			anonymous: firebaseui.auth.AnonymousAuthProvider.PROVIDER_ID,
		};

		for (const [key, provider] of Object.entries(providerMap)) {
			if (firebase_dynamic_config[key] === "on") {
				options.push(provider);
			}
		}

		// Add phone provider if enabled
		if (firebase_dynamic_config.phone === "on") {
			options.push(this.buildPhoneAuthProvider());
		}

		return options.length > 0
			? options
			: [firebase.auth.EmailAuthProvider.PROVIDER_ID];
	},

	/**
	 * Get UI configuration
	 */
	getUiConfig() {
		return {
			callbacks: {
				signInSuccessWithAuthResult: (authResult, redirectUrl) => {
					return AuthenticationHandler.signInSuccessWithAuthResult(true);
				},
				signInFailure: (error) => {
					console.error("Sign in failure:", error);
					WGR_alert("Sign in failed. Please try again.", "error");
					return Promise.resolve();
				},
				uiShown: () => {
					console.log("Firebase UI shown");
				},
			},
			signInFlow: "popup",
			// signInSuccessUrl: FirebaseUtils.createSignInSuccessUrl(),
			signInSuccessUrl: action_signInSuccessUrl(),
			signInOptions: this.buildDynamicSignInOptions(),
			privacyPolicyUrl: () => {
				const url = FirebaseUtils.getConfigValue(
					firebase_dynamic_config?.privacy_policy_url,
					window.location.origin
				);
				window.location.assign(url);
			},
			tosUrl: FirebaseUtils.getConfigValue(
				firebase_dynamic_config?.terms_service_url,
				window.location.origin
			),
		};
	},
};

/**
 * Initialize everything when page loads
 */
document.addEventListener("DOMContentLoaded", function () {
	try {
		// Validate dependencies
		FirebaseUtils.validateDependencies();

		// Set up auth state listener
		firebase.auth().onAuthStateChanged(
			(user) => {
				FirebaseUtils.safeJQuery("#firebase-loading").hide();
				FirebaseUtils.safeJQuery("#firebase-loaded").show();

				if (user) {
					AuthStateManager.handleSignedInUser();
				} else {
					AuthStateManager.handleSignedOutUser();
				}
			},
			(error) => {
				console.error("Auth state change error:", error);
				WGR_alert(
					"Authentication system error. Please refresh the page.",
					"error"
				);
			}
		);

		// Handle token expiration if configured
		if (typeof token_expires_time === "number" && token_expires_time > 0) {
			console.log("Token expires time:", token_expires_time);
			setTimeout(() => {
				window.location.reload();
			}, token_expires_time * 1000);
		}
	} catch (error) {
		console.error("Firebase initialization error:", error);
		WGR_alert("Failed to initialize authentication system.", "error");
	}
});

// Export for global access
window.FirebaseAuth = {
	Utils: FirebaseUtils,
	AuthStateManager,
	AuthenticationHandler,
	ConfigBuilder,
};

// Legacy function support
window.handleSignedInUser = () => AuthStateManager.handleSignedInUser();
window.handleSignedOutUser = () => AuthStateManager.handleSignedOutUser();
window.continueSignIn = () => AuthStateManager.continueSignIn();
window.firebaseSignOut = (message, redirectTo) =>
	AuthenticationHandler.signOut(message, redirectTo);
window.deleteAccount = () => {
	if (confirm("Are you sure you want to delete your account?")) {
		firebase
			.auth()
			.currentUser.delete()
			.then(() => {
				WGR_alert("Account deleted successfully.", "success");
				window.location.reload();
			})
			.catch((error) => {
				console.error("Account deletion error:", error);
				if (error.code === "auth/requires-recent-login") {
					firebase
						.auth()
						.signOut()
						.then(() => {
							setTimeout(() => {
								WGR_alert(
									"Please sign in again to delete your account.",
									"error"
								);
							}, 100);
						});
				} else {
					WGR_alert("Failed to delete account. Please try again.", "error");
				}
			});
	}
};
