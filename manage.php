<?php

session_start();
require_once('auth.php');
require_once('config.php');
require_once('common.php');

// DB connection
mysql_connect(DB_SERVER,DB_USER,DB_PASS);
@mysql_select_db(DB_NAME) or die( "Unable to select database");

// Get requested date, using today if none / future specified.
$date = date("Y-m-d", strtotime("today"));
if (isset($_GET['date'])) {
    if ($_GET['date'] < $date) {
        $date = $_GET['date'];
    }
}

// Build the main display
$content .= makeCurrentPromises();
$content .= makeNewPromises();
$content .= makeOldPromises();

// If we're coming back from the callback
if (isset($_GET['done'])) {
    $content .= makeTweetBoxes($date);
}

include('html.inc');

mysql_close();





function makeCurrentPromises() {

    // Get all active promises
    $query = "SELECT * FROM promises WHERE uid='" . mysql_real_escape_string($_SESSION['uid']) . "' AND active='1'";
    $promiseResult = mysql_query($query);
    
    $content .= "<div class=\"centeredlistheader\">Currently running:</div><ul class=\"centeredlist\">";
    
    if (mysql_num_rows($promiseResult) == 0) {
        $content .= "<li class=\"noitem\">You don't have any promises set up.  Use the \"Add another\" box below to add one.</li>";
    }
    
    while ($promise = mysql_fetch_assoc($promiseResult)) {
    
        $content .= "<li class=\"item\">";
    
        // Work out number of passes
        $query = "SELECT COUNT(*) FROM records WHERE uid='" . mysql_real_escape_string($_SESSION['uid']) . "' AND pid='" . mysql_real_escape_string($promise['pid']) . "' AND kept='YES'";
        $result = mysql_query($query);
        $row = mysql_fetch_assoc($result);
        $pass = $row['COUNT(*)'];
        // Work out number of fails
        $query = "SELECT COUNT(*) FROM records WHERE uid='" . mysql_real_escape_string($_SESSION['uid']) . "' AND pid='" . mysql_real_escape_string($promise['pid']) . "' AND kept='NO'";
        $result = mysql_query($query);
        $row = mysql_fetch_assoc($result);
        $fail = $row['COUNT(*)'];
        // Work out earliest date
        $query = "SELECT MIN(date) FROM records WHERE uid='" . mysql_real_escape_string($_SESSION['uid']) . "' AND pid='" . mysql_real_escape_string($promise['pid']) . "'";
        $result = mysql_query($query);
        $row = mysql_fetch_assoc($result);
        $earliestDate = $row['MIN(date)'];

        if ($pass+$fail <= 0) {
            $content .= "<span class=\"floatright\">(No data for this promise yet.)";
        } else {
            $content .= "<span class=\"floatright\">(" . round($pass/($pass+$fail)*100) . "% success rate since " . date("j M Y", strtotime($earliestDate)) . ".)";
        }
        $content .= "<a href=\"/deactivate/" . $promise['pid'] . "\" class=\"remove\">Remove</a></span>";
        $content .= "" . $promise['promise'] . "</li>";
    }
    
    $content .= "</ul>";
    
    return $content;
}



function makeNewPromises() {
    
    $content .= "<div class=\"centeredlistheader\">Add another:</div>";
    $content .= "<p class=\"publicwarning\">Just so you know, other users can see what promises you have set. Please don't enter anything really personal.</p>";
    
    $content .= "<form class=\"centeredform\" method=\"post\" action=\"/managecallback.php\">";
    $content .= "<input type=\"text\" name=\"newpromise\" class=\"newpromisefield\"/>";
    $content .= "<input type=\"submit\" name=\"Submit\" value=\"Add\"><br/>";
    $content .= "<input type=\"checkbox\" name=\"doyesterday\" id=\"doyesterday\" checked/><label for=\"doyesterday\">I want to get started right away - let me add yesterday's data!</label>";
    $content .= "</form>";
    
    return $content;
}



function makeOldPromises() {

    // Get all inactive promises
    $query = "SELECT * FROM promises WHERE uid='" . mysql_real_escape_string($_SESSION['uid']) . "' AND active='0'";
    $promiseResult = mysql_query($query);
    
    $content .= "<div class=\"centeredlistheader\">Fallen by the wayside:</div><ul class=\"centeredlist\">";
    
    if (mysql_num_rows($promiseResult) == 0) {
        $content .= "<li class=\"noitem\">You haven't deactivated any of your promises. Well done!</li>";
    }
    
    while ($promise = mysql_fetch_assoc($promiseResult)) {
    
        $content .= "<li class=\"item\">";
    
        // Work out number of passes
        $query = "SELECT COUNT(*) FROM records WHERE uid='" . mysql_real_escape_string($_SESSION['uid']) . "' AND pid='" . mysql_real_escape_string($promise['pid']) . "' AND kept='YES'";
        $result = mysql_query($query);
        $row = mysql_fetch_assoc($result);
        $pass = $row['COUNT(*)'];
        // Work out number of fails
        $query = "SELECT COUNT(*) FROM records WHERE uid='" . mysql_real_escape_string($_SESSION['uid']) . "' AND pid='" . mysql_real_escape_string($promise['pid']) . "' AND kept='NO'";
        $result = mysql_query($query);
        $row = mysql_fetch_assoc($result);
        $fail = $row['COUNT(*)'];
        // Work out earliest and latest date
        $query = "SELECT MIN(date), MAX(date) FROM records WHERE uid='" . mysql_real_escape_string($_SESSION['uid']) . "' AND pid='" . mysql_real_escape_string($promise['pid']) . "'";
        $result = mysql_query($query);
        $row = mysql_fetch_assoc($result);
        $earliestDate = $row['MIN(date)'];
        $latestDate = $row['MAX(date)'];
        
        $content .= "<span class=\"floatright\">(" . round($pass/($pass+$fail)*100) . "% success rate from " . date("j M Y", strtotime($earliestDate)) . " to " . date("j M Y", strtotime($latestDate)) . ".)";
        $content .= "<a href=\"/activate/" . $promise['pid'] . "\" class=\"add\">Add</a></span>";
        $content .= "" . $promise['promise'] . "</li>";
    }
    
    $content .= "</ul>";
    
    return $content;
}



function makeTweetBoxes($date) {
    
    $content .= "<div class=\"backtoview\"><a href=\"/view\">Back to your monthly view</a></div>";
    
    return $content;
}

?>