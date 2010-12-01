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
    
    $content .= "<div>Currently running:</div><table>";
    
    while ($promise = mysql_fetch_assoc($promiseResult)) {
    
        $content .= "<tr><td>\"" . $promise['promise'] . "\"</td>";
    
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
            $content .= "<td>(No data for this promise yet.)</td>";
        } else {
            $content .= "<td>(" . round($pass/($pass+$fail)*100) . "% success rate since " . $earliestDate . ".)</td>";
        }
        $content .= "<td><a href=\"/deactivate/" . $promise['pid'] . "\">X</a></td></tr>";
    }
    
    $content .= "</table>";
    
    return $content;
}



function makeNewPromises() {
    
    $content .= "<div>Add another:</div><table>";
    
    $content .= "<form method=\"post\" action=\"managecallback.php\">";
    $content .= "<input type=\"text\" name=\"newpromise\" width=\"30\"/>";
    $content .= "<input type=\"submit\" name=\"Submit\" value=\"Submit\">";
    $content .= "</form>";
    
    return $content;
}



function makeOldPromises() {

    // Get all inactive promises
    $query = "SELECT * FROM promises WHERE uid='" . mysql_real_escape_string($_SESSION['uid']) . "' AND active='0'";
    $promiseResult = mysql_query($query);
    
    $content .= "<div>Fallen by the wayside:</div><table>";
    
    while ($promise = mysql_fetch_assoc($promiseResult)) {
    
        $content .= "<tr><td>\"" . $promise['promise'] . "\"</td>";
    
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
        
        $content .= "<td>(" . round($pass/($pass+$fail)*100) . "% success rate from " . $earliestDate . " to " . $latestDate . ".)</td>";
        $content .= "<td><a href=\"/activate/" . $promise['pid'] . "\">^</a></td></tr>";
    }
    
    $content .= "</table>";
    
    return $content;
}



function makeTweetBoxes($date) {

    $content = "<div>Thanks!</div>";
    
    // How many are complete today?
    $kept = 0;
    $unkept = 0;
    $waiting = 0;
    $query = "SELECT * FROM records WHERE uid='" . mysql_real_escape_string($_SESSION['uid']) . "' AND date='" . mysql_real_escape_string($date) . "'";
    $recordResult = mysql_query($query);
    while ($record = mysql_fetch_assoc($recordResult)) {
        if ($record['kept'] == "YES") $kept++;
        if ($record['kept'] == "NO") $unkept++;
        if ($record['kept'] == "WAITING") $waiting++;
    }
    $content .= "<div>";
    // Friendly date
    $today = date("Y-m-d", strtotime("today"));
    if ($date == $today) {
        $content = "Today";
    } else {
        $content = "On " . $date;
    }
    $content .= " you met " . $kept . " of your " . ($kept+$unkept) . " promise";
    $content .= (($kept+$unkept)==1)?".":"s.";
    if ($waiting > 0) {
        $content .= "We're still waiting for information on " . $waiting . " promise";
        $content .= ($waiting==1)?".":"s.";
    }
    $content .= "</div>";
    
    $content .= "<div><a href=\"/view\">Back to your monthly view</a></div>";
    
    return $content;
}

?>