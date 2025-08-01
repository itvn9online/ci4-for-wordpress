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

// Global variables
let firebase_recommend_login = null;

// Initialize the FirebaseUI Widget using Firebase.
var ui = new firebaseui.auth.AuthUI(firebase.auth());

/**
 * Displays the UI for a signed in user.
 * @param {!firebase.User} user
 */
var handleSignedInUser = function () {
	var user = firebase.auth().currentUser;
	//console.log(user);
	if (user === null) {
		return false;
	}

	//
	//user = action_handleSignedInUser();

	//
	jQuery("#user-signed-in").show();
	jQuery("#user-signed-out").hide();
	jQuery("#firebase_name").html(user.displayName);
	jQuery("#firebase_email").html(user.email);
	jQuery("#firebase_phone").html(user.phoneNumber);
	// nếu có tham số bắt buộc phải có số điện thoại -> kiểm tra xem có số điện thoại chưa
	//console.log(required_firebase_phone_number);
	if (
		typeof required_firebase_phone_number != "undefined" &&
		required_firebase_phone_number === true &&
		jQuery.trim(jQuery("#firebase_phone").html() || "") == ""
	) {
		WGR_alert("Phone number not found!", "error");
		jQuery("#sign-in").hide();
		return false;
	}
	//console.log(user);
	//console.log(user.phoneNumber);
	if (
		typeof user.photoURL != "undefined" &&
		user.photoURL != "" &&
		user.photoURL != null
	) {
		jQuery("#firebase_photo").html('<img src="' + user.photoURL + '" />');
	}
};

/**
 * Displays the UI for a signed out user.
 */
var handleSignedOutUser = function () {
	jQuery("#user-signed-in").hide();
	jQuery("#user-signed-out").show();
	ui.start("#firebaseui-container", getUiConfig());
};

// Listen to change in auth state so it displays the correct UI for when
// the user is signed in or not.
firebase.auth().onAuthStateChanged(
	function (user) {
		jQuery("#firebase-loading").hide();
		jQuery("#firebase-loaded").show();
		if (user) {
			// User is signed in.
			handleSignedInUser();

			// tự động đăng nhập
			// console.log(localStorage.getItem("firebase_auto_login"));
			if (localStorage.getItem("firebase_auto_login") !== null) {
				if (1 < 2) {
					continueSignIn();
				} else {
					WGR_alert("Auto login after 5 seconds...");
					firebase_recommend_login = setTimeout(() => {
						if (localStorage.getItem("firebase_auto_login") !== null) {
							continueSignIn();
						} else {
							WGR_alert("Auto login has been canceled!", "warning");
						}
					}, 5000);
				}
			}
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
						setTimeout(() => {
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
/*
var initApp = function () {
	//document.getElementById("sign-in").addEventListener("click", function () {});
	//document.getElementById("sign-out").addEventListener("click", function () {});
	document
		.getElementById("delete-account")
		.addEventListener("click", function () {});
};
window.addEventListener("load", initApp);
*/

//
if (typeof token_expires_time == "number") {
	console.log("Token expires time:", token_expires_time);
	setTimeout(() => {
		window.location.reload();
	}, token_expires_time * 1000);
}
