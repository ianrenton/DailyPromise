<?php

session_start();
require_once('auth.php');
require_once('twitteroauth/twitteroauth.php');
require_once('config.php');
require_once('common.php');

// Titlebar text
$titleText = " - Friends";

// DB connection
mysql_connect(DB_SERVER,DB_USER,DB_PASS);
@mysql_select_db(DB_NAME) or die( "Unable to select database");

// Auth with Twitter
$access_token = $_SESSION['access_token'];
$to = new TwitterOAuth(CONSUMER_KEY, CONSUMER_SECRET, $access_token['oauth_token'], $access_token['oauth_token_secret']);

// Get current user's friends (as IDs)
$friendsArray = $to->get('friends/ids', array('user_id' => $_SESSION['twitter_uid']));

// Find out which ones are Daily Promise users
$query = "SELECT * FROM users WHERE ";
foreach($friendsArray as $friendID) {
	$query .= "twitter_uid=" . mysql_real_escape_string($friendID) . " OR ";
}
$query .= "0 ORDER BY username";
$result = mysql_query($query);

// Print fluff
$content .= "<div class=\"centeredlistheader\">How are my friends doing?</div><ul class=\"friendslist\">";

while ($friend = mysql_fetch_assoc($result)) {
	// Print user info for each match
	$content .= '<li class="friend"><div class="friendname"><a href="/user/' . $friend['username'] . '">@' . $friend['username'] . '</a></div><div class="friendpercentage">' . $friend['percentthisweek'] . '% of goals met this week</div><div class="friendpromises">' . $friend['activepromises'] . ' active promise' . (($friend['activepromises'] != 1)?"s":"") . '</div></li>';
}
$content .= "</ul>";

include('html.inc');

mysql_close();

?>

