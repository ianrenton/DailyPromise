<?php

session_start();
require_once('config.php');
require_once('common.php');

$content .= "Daily Promise";

if (isset($_GET['response'])) {
    if ($_GET['response'] == "accountdeleted") {
        $content .= '<p class="good">Your account has been removed, as requested.</p>';
    } else if ($_GET['response'] == "loginfailed") {
        $content .= '<p class="error">Alternative login failed.</p>';
    } else if ($_GET['response'] == "twitterauthfailed") {
        $content .= '<p class="error">Twitter failed to authenticate you.</p>';
    }
}

if (!isset($_SESSION['uid'])) {
    $content .= '<p align="center" style="margin-top:100px;"><a href="/twittersignin" style="font-family:sans-serif; color:#aaa; text-decoration:none;"><strong>To log in or sign up, click here:</strong><br/><img src="./images/lighter.png" border="0" alt="Sign in with Twitter" title="Sign in with Twitter" style="margin-top:5px;" /></a></p>';

    $content .= '<form name="loginform" method="post" action="logincallback.php">
        <table border="0" align="center" cellpadding="5" cellspacing="5" style="margin:50px auto 0px auto;">
        <tr>
        <td><p style="margin:0; padding:0;">Twitter Username</p></td>
        <td><input name="username" type="text" id="username" style="width:200px"></td>
        </tr>
        <tr>
        <td><p style="margin:0; padding:0;">SuccessWhale Password</p></td>
        <td><input name="password" type="password" id="password" style="width:200px"></td>
        </tr>
        <tr>
        <td>&nbsp;</td>
        <td><input type="submit" name="Submit" value="Sign In"></td>
        </tr>
        </table>
        </form>';
}
 

include('html.inc');

?>

