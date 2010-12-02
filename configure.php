<?php

// Load required lib files.
session_start();
require_once('auth.php');
require_once('config.php');
require_once('common.php');

// Require authentication for this page
auth();

// DB connection
mysql_connect(DB_SERVER,DB_USER,DB_PASS);
@mysql_select_db(DB_NAME) or die( "Unable to select database");

$query = "SELECT * FROM users WHERE uid='" . mysql_real_escape_string($_SESSION['uid']) . "'";
$userResult = mysql_query($query);
$row = mysql_fetch_assoc($userResult);
$username = $row['username'];

if ($row['password'] == "") {
    // Password config for non-registered users
    $content .= '<form class="centeredform" method="post" action="/registercallback.php">
            <h4>Log in without Twitter</strong></h4>
            <p>If you\'d like to log in to Daily Promise from computers where Twitter is blocked, you can create set a password for your Daily Promise.  This will allow you to use the "Alternate Login" button from the home page, bypassing the Twitter authentication.</p>
            <table cellspacing=5>
            <tr>
            <td>Twitter Username</td>
            <td>' . $username . '</td>
            </tr>
            <tr>
            <td>New Daily Promise Password</td>
            <td><input name="password" type="password" id="password" style="width:200px" autocomplete="off"></td>
            </tr>
            <tr>
            <td>Re-type Password</td>
            <td><input name="password2" type="password" id="password2" style="width:200px" autocomplete="off"></td>
            </tr>
            <tr>
            <td>&nbsp;</td>
            <td><input type="submit" name="Submit" value="Create Password"></td>
            </tr>
            </table>';
            
    if (isset($_GET['response'])) {
        if ($_GET['response'] == "passwordremoved") {
            $content .= '<p class="good">Password removed.</p>';
        } else if ($_GET['response'] == "blankpassword") {
            $content .= '<p class="error">You must enter two matching passwords.</p>';
        } else if ($_GET['response'] == "passwordmismatch") {
            $content .= '<p class="error">The two passwords did not match.</p>';
        } else if ($_GET['response'] == "error") {
            $content .= '<p class="error">An error occurred while trying to set a password.</p>';
        }
    }
    
    $content .= '</form>';
            
} else {
    // Password config for registered users
    $content .= '<form class="centeredform" method="post" action="/registercallback.php">
            <h4>Change Daily Promise password</strong></h4>
            <p>You have set a password for Daily Promise so that you can bypass Twitter on networks where it is blocked.  You can change your password with this form.</p>
            <p>If you want to remove your password and go back to relying on Twitter for authentication, leave the "New Password" fields blank.</p>
            <table cellspacing=5>
            <tr>
            <td>New Password</td>
            <td><input name="password" type="password" id="password" style="width:200px" autocomplete="off"></td>
            </tr>
            <tr>
            <td>Re-type New Password</td>
            <td><input name="password2" type="password" id="password2" style="width:200px" autocomplete="off"></td>
            </tr>
            <tr>
            <td>&nbsp;</td>
            <td><input type="submit" name="Submit" value="Change Password"></td>
            </tr>
            </table>';
    
    if (isset($_GET['response'])) {
        if ($_GET['response'] == "passwordset") {
            $content .= '<p class="good">Password set.</p>';
        } else if ($_GET['response'] == "passwordmismatch") {
            $content .= '<p class="error">The two passwords did not match.</p>';
        } else if ($_GET['response'] == "error") {
            $content .= '<p class="error">An error occurred while trying to set a password.</p>';
        }
    }
    
    $content .= '</form>';
}

// Account removal
$content .= '<form class="centeredform" method="post" action="/unregistercallback.php">
        <h4>Account Deletion</strong></h4>
        <p>If you\'re leaving Daily Promise, you have two options.</p>
        <p>"Delete my account" removes your profile but not your promise data.  No-one will be able to view your profile, search for it or see it in their friends list.  However, if you come back sometime and sign in again, your promises and records will be restored.</p>
        <p>"Nuke my account" removes your profile and all associated data.  We will keep no record of you having used the site.  If you log in again some other time, your promises and records will not be available.</p>
        <table cellspacing=5>
        <tr>
        <td><input type="radio" name="delete" id="delete" value="delete"><label for="delete">Delete my account</label></td>
        </tr>
        <tr>
        <td><input type="radio" name="delete" id="nuke" value="nuke"><label for="nuke">Nuke my account</label></td>
        </tr>
        <tr>
        <td><input type="submit" name="Submit" value="Confirm (this is final!)"></td>
        </tr>
        </table>
        </form>';
    
    if (isset($_GET['response'])) {
        if ($_GET['response'] == "deleteerror") {
            $content .= '<p class="error">An unexpected error occurred.  Your account has not been deleted.</p>';
        }
    }
    
    $content .= '</form>';

 
/* Include HTML to display on the page */
include('html.inc');

mysql_close();

?>
