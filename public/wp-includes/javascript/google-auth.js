/**
 * Google Identity Services Authentication
 * Đăng nhập trực tiếp bằng Google Sign-In API
 * Optimized version with better initialization handling
 */

// Global variables
window.google_auth_config = window.google_auth_config || null;
window.google_auth_initialized = false;

/**
 * Initialize Google Sign-In with retry mechanism
 */
function initializeGoogleAuth() {
	// Check if config is available
	if (!window.google_auth_config) {
		console.warn("Google auth config not ready, waiting...");
		return false;
	}

	// Check if Google library is loaded
	if (
		typeof google === "undefined" ||
		!google.accounts ||
		!google.accounts.id
	) {
		console.warn(
			"Google Identity Services library not loaded yet, retrying..."
		);
		return false;
	}

	// Prevent double initialization
	if (window.google_auth_initialized) {
		console.log("Google Sign-In already initialized");
		return true;
	}

	try {
		// Initialize Google Sign-In
		google.accounts.id.initialize({
			client_id: window.google_auth_config.client_id,
			callback: handleGoogleSignIn,
			auto_select: window.google_auth_config.auto_select || false,
			cancel_on_tap_outside: true,
			ux_mode: "popup",
			context: "signin",
		});

		// Render sign-in button if container exists
		const buttonContainer = document.getElementById("google-signin-button");
		if (buttonContainer) {
			google.accounts.id.renderButton(buttonContainer, {
				theme: "outline",
				size: "large",
				width: 250,
				text: "signin_with",
				shape: "rectangular",
				logo_alignment: "left",
			});
			console.log("Google Sign-In button rendered");
		}

		// Show One Tap if enabled (default is true)
		const showOneTap = window.google_auth_config.show_one_tap !== false;
		if (showOneTap) {
			// Configure One Tap with additional options
			google.accounts.id.prompt((notification) => {
				if (notification.isNotDisplayed()) {
					const reason = notification.getNotDisplayedReason();
					console.log("One Tap not displayed:", reason);

					// Reasons: opt_out_or_no_session, browser_not_supported,
					// invalid_client, suppressed_by_user, unregistered_origin
					if (reason === "suppressed_by_user") {
						console.log("User has suppressed One Tap");
					} else if (reason === "opt_out_or_no_session") {
						console.log("User opted out or no Google session");
					}
				} else if (notification.isSkippedMoment()) {
					const reason = notification.getSkippedReason();
					console.log("One Tap skipped:", reason);

					// Reasons: user_cancel, tap_outside, issuing_failed
				} else if (notification.isDismissedMoment()) {
					const reason = notification.getDismissedReason();
					console.log("One Tap dismissed:", reason);

					// Reasons: credential_returned, cancel
				}
			});
			console.log("✓ One Tap prompt displayed");
		} else {
			console.log("One Tap disabled in config");
		}

		window.google_auth_initialized = true;
		console.log("✓ Google Sign-In initialized successfully");

		// Dispatch custom event for other scripts
		window.dispatchEvent(new CustomEvent("googleAuthReady"));

		return true;
	} catch (error) {
		console.error("✗ Error initializing Google Sign-In:", error);
		return false;
	}
}

/**
 * Handle Google Sign-In response
 */
function handleGoogleSignIn(response) {
	if (!response || !response.credential) {
		console.error("No credential received from Google");
		handleGoogleSignInError("Không nhận được thông tin đăng nhập từ Google");
		return;
	}

	console.log("Google credential received");

	// Show loading state
	showGoogleAuthLoading(true);

	// Decode JWT to get user info (for display only)
	const userInfo = parseGoogleJWT(response.credential);

	if (userInfo) {
		console.log("Google user info:", {
			email: userInfo.email,
			name: userInfo.name,
			picture: userInfo.picture,
		});

		// Send to backend for verification
		sendGoogleTokenToBackend(response.credential, userInfo);
	} else {
		showGoogleAuthLoading(false);
		handleGoogleSignInError("Không thể đọc thông tin người dùng");
	}
}

/**
 * Parse Google JWT (client-side only, for display)
 */
function parseGoogleJWT(token) {
	try {
		const parts = token.split(".");
		if (parts.length !== 3) {
			throw new Error("Invalid JWT format");
		}

		const payload = parts[1];
		// Fix base64 padding
		const base64 = payload.replace(/-/g, "+").replace(/_/g, "/");
		const jsonPayload = decodeURIComponent(
			atob(base64)
				.split("")
				.map((c) => "%" + ("00" + c.charCodeAt(0).toString(16)).slice(-2))
				.join("")
		);

		return JSON.parse(jsonPayload);
	} catch (error) {
		console.error("Error parsing JWT:", error);
		return null;
	}
}

/**
 * Send Google token to backend
 */
function sendGoogleTokenToBackend(credential, userInfo) {
	if (!window.google_auth_config || !window.google_auth_config.callback_url) {
		console.error("Callback URL not configured");
		handleGoogleSignInError("Cấu hình không hợp lệ");
		return;
	}

	// SECURITY: Validate callback URL is same origin
	try {
		const callbackUrl = new URL(
			window.google_auth_config.callback_url,
			window.location.origin
		);
		if (callbackUrl.origin !== window.location.origin) {
			console.error("Callback URL must be same origin");
			handleGoogleSignInError("Cấu hình không hợp lệ");
			return;
		}
	} catch (e) {
		console.error("Invalid callback URL:", e);
		handleGoogleSignInError("Cấu hình không hợp lệ");
		return;
	}

	// SECURITY: Validate credential format
	if (
		!credential ||
		typeof credential !== "string" ||
		credential.length > 2048
	) {
		console.error("Invalid credential format");
		handleGoogleSignInError("Token không hợp lệ");
		return;
	}

	const formData = new FormData();
	formData.append("credential", credential);
	formData.append("id_token", credential);

	// Don't send full user info, backend will decode from token
	// formData.append("user_info", JSON.stringify(userInfo));

	// Add CSRF token if available
	if (typeof csrf_token !== "undefined" && csrf_token) {
		formData.append("csrf_token", csrf_token);
	}

	// Add timestamp for cache busting and replay attack prevention
	formData.append("timestamp", Date.now());

	console.log(
		"Sending token to backend:",
		window.google_auth_config.callback_url
	);

	fetch(window.google_auth_config.callback_url, {
		method: "POST",
		body: formData,
		headers: {
			"X-Requested-With": "XMLHttpRequest",
		},
		credentials: "same-origin",
		// SECURITY: Set timeout to prevent hanging requests
		signal: AbortSignal.timeout(30000), // 30 seconds timeout
	})
		.then((response) => {
			if (!response.ok) {
				throw new Error(`HTTP error! status: ${response.status}`);
			}
			return response.json();
		})
		.then((data) => {
			showGoogleAuthLoading(false);

			console.log("Backend response:", data);

			if (data.ok) {
				handleGoogleSignInSuccess(userInfo, data);
			} else {
				handleGoogleSignInError(
					data.error || data.message || "Đăng nhập thất bại"
				);
			}
		})
		.catch((error) => {
			showGoogleAuthLoading(false);
			console.error("Network error:", error);

			// SECURITY: Don't expose detailed error messages to users
			let userMessage = "Lỗi kết nối. Vui lòng thử lại.";
			if (error.name === "AbortError") {
				userMessage = "Yêu cầu hết thời gian. Vui lòng thử lại.";
			}

			handleGoogleSignInError(userMessage);
		});
}

/**
 * Handle successful sign-in
 */
function handleGoogleSignInSuccess(userInfo, backendData) {
	console.log("✓ Google Sign-In successful");

	// Update UI
	updateGoogleAuthUI(userInfo);

	// Trigger custom event
	window.dispatchEvent(
		new CustomEvent("googleSignInSuccess", {
			detail: { userInfo, backendData },
		})
	);

	// Redirect or perform actions
	const redirectUrl =
		window.google_auth_config.redirect_url || backendData?.redirect_url;

	if (redirectUrl && redirectUrl !== "") {
		setTimeout(() => {
			console.log("Redirecting to:", redirectUrl);
			if (window.self === window.top) {
				// Same-origin redirect
				window.location.href = redirectUrl;
			} else {
				// Cross-origin redirect
				window.top.location.href = redirectUrl;
			}
		}, 1500);
	} else {
		// Reload page to update login state
		setTimeout(() => {
			console.log("Reloading page...");
			if (window.self === window.top) {
				window.location.reload();
			} else {
				window.top.location.reload();
			}
		}, 1500);
	}
}

/**
 * Handle sign-in error
 */
function handleGoogleSignInError(errorMessage) {
	console.error("✗ Google Sign-In error:", errorMessage);

	// Trigger custom event
	window.dispatchEvent(
		new CustomEvent("googleSignInError", {
			detail: { error: errorMessage },
		})
	);

	// Show error message in UI
	const errorDiv = document.getElementById("google-auth-error");
	if (errorDiv) {
		errorDiv.innerHTML = `
            <strong>Lỗi đăng nhập:</strong><br>
            ${errorMessage}
        `;
		errorDiv.style.display = "block";

		// Auto hide after 5 seconds
		setTimeout(() => {
			errorDiv.style.display = "none";
		}, 5000);
	} else {
		// Fallback to alert
		alert("Lỗi đăng nhập: " + errorMessage);
	}
}

/**
 * Update UI after successful authentication
 */
function updateGoogleAuthUI(userInfo) {
	// Hide sign-in button
	const signInButton = document.getElementById("google-signin-button");
	if (signInButton) {
		signInButton.style.display = "none";
	}

	// Show success message with user avatar
	const successDiv = document.getElementById("google-auth-success");
	if (successDiv) {
		// SECURITY: Sanitize user inputs before displaying
		const safeName = escapeHtml(userInfo.name || "User");
		const safeEmail = escapeHtml(userInfo.email || "");

		// SECURITY: Only show avatar if it's from Google's CDN
		let avatarHtml = "";
		if (
			userInfo.picture &&
			userInfo.picture.startsWith("https://lh3.googleusercontent.com")
		) {
			const safePicture = escapeHtml(userInfo.picture);
			avatarHtml = `<img src="${safePicture}" alt="${safeName}" style="width:50px;height:50px;border-radius:50%;margin-right:10px;vertical-align:middle;">`;
		}

		successDiv.innerHTML = `
            <div class="alert alert-success" style="display:flex;align-items:center;">
                ${avatarHtml}
                <div>
                    <h5 style="margin:0;">Đăng nhập thành công!</h5>
                    <p style="margin:5px 0 0 0;">Chào mừng ${safeName}</p>
                </div>
            </div>
        `;
		successDiv.style.display = "block";
	}
}

/**
 * Escape HTML to prevent XSS
 */
function escapeHtml(text) {
	const div = document.createElement("div");
	div.textContent = text;
	return div.innerHTML;
}

/**
 * Show/hide loading state
 */
function showGoogleAuthLoading(show) {
	const loadingDiv = document.getElementById("google-auth-loading");
	const buttonDiv = document.getElementById("google-signin-button");
	const errorDiv = document.getElementById("google-auth-error");

	if (loadingDiv) {
		loadingDiv.style.display = show ? "block" : "none";
	}

	if (buttonDiv) {
		buttonDiv.style.display = show ? "none" : "block";
	}

	if (errorDiv && show) {
		errorDiv.style.display = "none";
	}
}

/**
 * Sign out from Google
 */
function googleSignOut(callback) {
	console.log("Signing out from Google...");

	if (typeof google !== "undefined" && google.accounts && google.accounts.id) {
		google.accounts.id.disableAutoSelect();
	}

	// Clear any local storage
	localStorage.removeItem("google_auth_token");
	localStorage.removeItem("google_user_info");

	// Trigger custom event
	window.dispatchEvent(new CustomEvent("googleSignOut"));

	console.log("✓ Google Sign-Out completed");

	if (typeof callback === "function") {
		callback();
	}
}

/**
 * Retry mechanism with exponential backoff
 */
function retryInitialization(attempts = 0, maxAttempts = 10) {
	const delay = Math.min(1000 * Math.pow(1.5, attempts), 5000); // Max 5s delay

	if (attempts >= maxAttempts) {
		console.error(
			"✗ Failed to initialize Google Sign-In after",
			maxAttempts,
			"attempts"
		);
		return;
	}

	if (initializeGoogleAuth()) {
		return; // Success
	}

	console.log(`Retry attempt ${attempts + 1}/${maxAttempts} in ${delay}ms...`);

	setTimeout(() => {
		retryInitialization(attempts + 1, maxAttempts);
	}, delay);
}

/**
 * Initialize when DOM is ready
 */
if (document.readyState === "loading") {
	document.addEventListener("DOMContentLoaded", function () {
		console.log("DOM ready, initializing Google Auth...");
		retryInitialization();
	});
} else {
	// DOM already loaded
	console.log("DOM already loaded, initializing Google Auth...");
	retryInitialization();
}

/**
 * Also try on window load as backup
 */
window.addEventListener("load", function () {
	if (!window.google_auth_initialized) {
		console.log("Window loaded, retrying Google Auth initialization...");
		retryInitialization();
	}
});

/**
 * Listen for config updates
 */
window.addEventListener("googleConfigReady", function () {
	console.log("Google config ready event received");
	if (!window.google_auth_initialized) {
		retryInitialization();
	}
});

// Export functions for external use
window.GoogleAuth = {
	initialize: initializeGoogleAuth,
	signOut: googleSignOut,
	isInitialized: () => window.google_auth_initialized,
};

console.log("Google Auth script loaded");
