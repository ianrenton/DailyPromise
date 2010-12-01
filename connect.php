<?php

require_once('config.php');
require_once('common.php');

$content .= '<p align="center" style="margin-top:100px;"><a href="/twittersignin" style="font-family:sans-serif; color:#aaa; text-decoration:none;"><strong>To log in or sign up, click here:</strong><br/><img src="./images/lighter.png" border="0" alt="Sign in with Twitter" title="Sign in with Twitter" style="margin-top:5px;" /></a></p>';
if (DB_SERVER != '') {
    $content .= '<p align="center" style="margin-top:100px; font-size:90%;"><a href="./login.php">Retrieve Cached Authentication Token</a></p>';
}
$content .= '<p align="center" style="margin-top:50px">A few notes for new users:</p>';
 
/* Include HTML to display on the page */
include('html.inc');

?>
