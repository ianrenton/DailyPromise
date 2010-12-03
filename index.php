<?php

session_start();
require_once('config.php');
require_once('common.php');

// Titlebar text
$titleText = "";

// DB connection
mysql_connect(DB_SERVER,DB_USER,DB_PASS);
@mysql_select_db(DB_NAME) or die( "Unable to select database");

if (isset($_GET['response'])) {
    if ($_GET['response'] == "accountdeleted") {
        $content .= '<p class="good">Your account has been removed, as requested.</p>';
    } else if ($_GET['response'] == "loginfailed") {
        $content .= '<p class="error">Alternative login failed.</p>';
    } else if ($_GET['response'] == "twitterauthfailed") {
        $content .= '<p class="error">Twitter failed to authenticate you.</p>';
    }
}

$contentPage = "homepage";

$content .= '<p class="homepagetitle">Welcome to Daily Promise.</p>';
$content .= '<p class="homepageintro">Whether it\'s chores, diets, jobs or simply finding time to relax, we\'ll help keep you in check!</p>';
$content .= '<p class="homepageintro">It works a bit like this:</p>';

$content .= '<div class="homepageright">';


// Show Login box if not logged in
if (!isset($_SESSION['uid'])) {
    $content .= '<div class="twitterloginbox"><p><a href="/twittersignin"><strong>To sign in or register, click here:</strong><br/><img src="./images/lighter.png" border="0" alt="Sign in with Twitter" title="Sign in with Twitter" style="margin-top:5px;" /></a></p></div>';
}

// Top users box
$content .= '<div class="topusersbox"><p>This week\'s top users</p><p class="topuserssince">since ' . date("l jS F", strtotime("last sunday +1 day")) . '</p>';
$query = "SELECT * FROM users WHERE visible='1' AND activepromises>'0' ORDER BY percentthisweek DESC LIMIT 5";
$userResult = mysql_query($query);
while ($user = mysql_fetch_assoc($userResult)) {
    $content .= '<div class="topuser"><div class="topuserpercentage">' . $user['percentthisweek'] . '%</div><div class="topusername"><a href="/user/' . $user['username'] . '">@' . $user['username'] . '</a></div><div class="topuserpromises">' . $user['activepromises'] . ' active promise' . (($user['activepromises'] != 1)?"s":"") . '</div></div>';
}
$content .= '</div>';

// Show alt Login box if not logged in
if (!isset($_SESSION['uid'])) {
    $content .= '<div class="altloginbox"><p>Alternate sign in</p>
        <form name="loginform" method="post" action="logincallback.php">
        <p>Username: <input name="username" type="text" id="username" style="width:100px"><br/>
        Password: <input name="password" type="password" id="password" style="width:100px"><br/>
        <input type="submit" name="Submit" value="Sign In"></p>
        </form></div>';
}
 
$content .= '</div><div class="homepageleft"><img src="/images/dailypromise.png"></div>';



include('html.inc');

mysql_close();

?>

