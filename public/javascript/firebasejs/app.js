/**
 * @return {!Object} The FirebaseUI config.
 * https://github.com/firebase/firebaseui-web
 * https://firebase.google.com/pricing
 */

// Initialize the FirebaseUI Widget using Firebase.
var ui = new firebaseui.auth.AuthUI(firebase.auth());

/**
 * Displays the UI for a signed in user.
 * @param {!firebase.User} user
 */
var handleSignedInUser = function (user) {
	if (user === null || typeof user == "undefined") {
		return false;
	}

	//
	//action_handleSignedInUser(user);
	jQuery("#user-signed-in").show();
	jQuery("#user-signed-out").hide();
	jQuery("#firebase_name").html(user.displayName);
	jQuery("#firebase_email").html(user.email);
	jQuery("#firebase_phone").html(user.phoneNumber);
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
		jQuery("#loading").hide();
		jQuery("#loaded").show();
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
