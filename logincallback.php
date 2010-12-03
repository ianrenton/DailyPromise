<?php

session_start();
require_once('twitteroauth/twitteroauth.php');
require_once('config.php');
require_once('common.php');

if ((isset($_POST['username'])) && (isset($_POST['password']))) {
    if (DB_SERVER != '') {
        mysql_connect(DB_SERVER,DB_USER,DB_PASS);
        @mysql_select_db(DB_NAME) or die( "Unable to select database");
        
        // Get the user's access token from the table
        $query = "SELECT * FROM users WHERE username='" . mysql_real_escape_string($_POST['username']) . "'";
        $result = mysql_query($query);
        
        // Username check
        if (mysql_num_rows($result) > 0) {
            // Password check
			$user = mysql_fetch_assoc($result);
            $storedPassword = $user['password'];
            if (strcmp($storedPassword, md5($_POST['password'])) == 0) {
                
                // Password match, so get access token
                $access_token = unserialize($user['auth_token']);
				$_SESSION['access_token'] = $access_token;

                // Save accesstoken cookie
                setcookie('access_token', serialize($access_token), mktime()+86400*365);

                $_SESSION['thisUser'] = $user['username'];
                $_SESSION['status'] = 'verified';
				// uid is not set here, this forces execution of auth() on next page load.
                
                header('Location: /view');
                die();
                
            } else {
                // Password didn't match
                header('Location: /home/loginfailed');
                die();
            }
        } else {
            // Username didn't match
            header('Location: /home/loginfailed');
            die();
        }
        
        mysql_close();

    }
} else {
    // Called without username/password POST.
    header('Location: /home/loginfailed');
    die();
}

?>
