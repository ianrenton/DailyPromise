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

// Get top users
$query = "SELECT * FROM users WHERE visible='1' AND activepromises>'0' ORDER BY percentthisweek DESC LIMIT 3";
$userResult = mysql_query($query);
$tweet = "Congratulations to this week's best promise-keepers: ";
$user = mysql_fetch_assoc($userResult);
$tweet .= "@" . $user['username'] . " (" . $user['percentthisweek'] . "%), ";
$user = mysql_fetch_assoc($userResult);
$tweet .= "@" . $user['username'] . " (". $user['percentthisweek'] . "%) and ";
$user = mysql_fetch_assoc($userResult);
$tweet .= "@" . $user['username'] . " (". $user['percentthisweek'] . "%)!";


$response = $to->post('statuses/update', array('status' => $tweet));

mysql_close();

?>

