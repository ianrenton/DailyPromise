<?php

session_start();
require_once('twitteroauth/twitteroauth.php');
require_once('config.php');
require_once('common.php');
require_once('cron.php'); // Run a normal cron first to update stats

// DB connection
mysql_connect(DB_SERVER,DB_USER,DB_PASS);
@mysql_select_db(DB_NAME) or die( "Unable to select database");

// Auth with Twitter
$access_token = unserialize(BOT_ACCESS_TOKEN);
$to = new TwitterOAuth(CONSUMER_KEY, CONSUMER_SECRET, $access_token['oauth_token'], $access_token['oauth_token_secret']);

// Get users
$query = "SELECT * FROM users WHERE visible='1' AND activepromises>'0'";
$userResult = mysql_query($query);
while ($user = mysql_fetch_assoc($userResult)) {
	// Remind people with no entries
	$query = "SELECT * FROM records WHERE uid='" . $user['uid'] . "' AND kept!='WAITING' ORDER BY date DESC";
	$recordResult = mysql_query($query);
	$latestRecord = mysql_fetch_assoc($recordResult);
	$daysSinceLast = (strtotime("today") -  strtotime($latestRecord['date'])) / 86400;
	if (($daysSinceLast > 1) && ($daysSinceLast < 7)) {
		$tweet = "@" . $user['username'] . ", it's been " . $daysSinceLast . " days since you last entered data on Daily Promise. You can do it at http://dp.onlydreaming.net/enter";
		$response = $to->post('statuses/update', array('status' => $tweet));
	} else if ($daysSinceLast == 7) {
		$tweet = "@" . $user['username'] . ", it's been a week since you last used Daily Promise. If you're leaving, don't worry - this is the last reminder we'll send.";
		$response = $to->post('statuses/update', array('status' => $tweet));
	}
}

//$response = $to->post('statuses/update', array('status' => $tweet));

mysql_close();

?>

