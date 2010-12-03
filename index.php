<?php

session_start();
require_once('config.php');
require_once('common.php');

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



if (!isset($_SESSION['uid'])) {
    $content .= '<div class="twitterloginbox"><p><a href="/twittersignin"><strong>To sign in or register, click here:</strong><br/><img src="./images/lighter.png" border="0" alt="Sign in with Twitter" title="Sign in with Twitter" style="margin-top:5px;" /></a></p></div>';

    $content .= '<div class="altloginbox"><p>Alternate sign in<br/>
        <form name="loginform" method="post" action="logincallback.php">
        <p>Username: <input name="username" type="text" id="username" style="width:100px"><br/>
        Password: <input name="password" type="password" id="password" style="width:100px"><br/>
        <input type="submit" name="Submit" value="Sign In"></p>
        </form></div>';
} else {
    // Already logged in.
}
 
$content .= '</div><div class="homepageleft"><img src="/images/dailypromise.png"></div>';

include('html.inc');

?>

