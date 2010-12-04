<?php

session_start();
require_once('auth.php');
require_once('config.php');
require_once('common.php');

// Titlebar text
$titleText = " - Privacy Information";

$content = "<div class=\"centeredlistheader\">Daily Promise Privacy Information</div><div class=\"about\"";

$content .= '<p>Daily Promise is, first and foremost, not evil.</p>';
$content .= '<p>During registration and login, Daily Promise communicates with Twitter via OAuth, which means that it does not see your Twitter password at any point.  From Twitter, we receive and use your username, your profile picture, your bio, your friends list and your timezone.  We don\'t read your statuses or anything else.</p>';
$content .= '<p>We also don\'t send anything back to Twitter without your consent.  At various points while using Daily Promise, you\'ll see prompts asking if you\'d like to tweet what you\'ve just done.  These can be ignored if you like; nothing happens until you click "tweet".  Daily Promise will <em>never</em> update your status without you explicitly clicking a "tweet" button.</p>';
$content .= '<p>On registration, you\'ll be given the option to make your account invisible.  (You can also change this later.)  Invisible accounts will not show up anywhere to anyone except you - you\'ll never appear in friends or "top users" lists, and visitors to your profile will be shown a "User does not exist" message.</p>';
$content .= '<p>If you want to access Daily Promise from a location where Twitter is blocked, you can set yourself a password to use with the "alternate login" option.  We encrypt this password, of course, but we recommend you don\'t make it the same as your real Twitter password just in case.  If you forget this password, there\'s no recovery option because we don\'t know your e-mail address.  You can change your password by signing in using Twitter, then going to "Configuration" once you\'re back in your account.</p>';
$content .= '<p>If you decide you don\'t like Daily Promise, you can easily delete your account.  This leaves behind no trace of you ever having had an account here.</p>';

$content .= '</div>';

include('html.inc');

?>