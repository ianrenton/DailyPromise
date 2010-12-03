<?php

session_start();
require_once('twitteroauth/twitteroauth.php');
require_once('config.php');

// If we don't already have a session, try and get one from cookie or Twitter.
// If we do, this script does nothing.
function auth() {
	if (!isset($_SESSION['uid'])) {
	    // Bring in access token from cookie if it exists.
	    if (!empty($_COOKIE['access_token'])) {
	    	$_SESSION['access_token'] = unserialize($_COOKIE['access_token']);
	    }

	    /* If access tokens are not available redirect to connect page. */
	    if (empty($_SESSION['access_token']) || empty($_SESSION['access_token']['oauth_token']) || empty($_SESSION['access_token']['oauth_token_secret'])) {
	        header('Location: ./clearsessions.php');
	    	die();
	    }

	    // Get user access tokens out of the session.
	    $access_token = $_SESSION['access_token'];

	    // Create a TwitterOauth object with consumer/user tokens.
	    $to = new TwitterOAuth(CONSUMER_KEY, CONSUMER_SECRET, $access_token['oauth_token'], $access_token['oauth_token_secret']);

	    // Session-global the twitterOAuth object so jQuery-called PHP scripts can see it.
	    $_SESSION['to'] = $to;

	    // Session-global the stuff that doesn't depend on which column we're rendering
	    $auth = $to->get('account/verify_credentials', array());
	    $_SESSION['thisUser'] = $auth['screen_name'];
	    $_SESSION['utcOffset'] = $auth['utc_offset'];


	    // Auth error handling
	    if ($auth["error"] == '<') {
	    	// Not sure what it is with the '<' error, but reloading seems to make it go away.
	    	header('Location: ' . htmlentities($_SERVER['PHP_SELF']) );
	    	die();
	    } else if ($auth["error"] == 'Could not authenticate you.') {
	    	// If we couldn't authenticate, log out and try again.
	    	header('Location: /logout' );
	    	die();
	    }

	    // DB connection
	    mysql_connect(DB_SERVER,DB_USER,DB_PASS);
	    @mysql_select_db(DB_NAME) or die( "Unable to select database");

	    // Port users across from OAuth provider
	    $query = "SELECT * FROM users WHERE username='" . mysql_real_escape_string($_SESSION['thisUser']) . "'";
	    $result = mysql_query($query);
	    if (!mysql_num_rows($result) ) {
	        // If user is a first-time visitor, add a row for them.
	        $query = "INSERT INTO users VALUES ('', '" . mysql_real_escape_string($_SESSION['thisUser']) . "','','','1')";
	        mysql_query($query);
	        $firstTime = true;
	    } else {
	        // If user is in the users table, update their access token
	        $query = "UPDATE users SET auth_token = '" . mysql_real_escape_string(serialize($access_token)) . "' WHERE username = '" . mysql_real_escape_string($_SESSION['thisUser']) . "'";
	        mysql_query($query);
	        $firstTime = false;
	    }

	    // Get uid
	    $query = "SELECT * FROM users WHERE username='" . mysql_real_escape_string($_SESSION['thisUser']) . "'";
	    $result = mysql_query($query);
	    $userRow = mysql_fetch_assoc($result);
	    $_SESSION['uid'] = $userRow['uid'];

	    mysql_close();
    
	    // Point first-timers at the Manage page to set some promises.  Point returning
	    // users at their View.
	    if ($firstTime == true) {
	        header('Location: /manage/firsttime' );
	    	die();
	    } else {
	        header('Location: /view' );
	    	die();
	    }
	}
}
