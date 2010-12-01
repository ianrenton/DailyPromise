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
$content = makeOldEntryChecker();
$content .= makeTodaysEntry($date);

// If we're coming back from the callback
if (isset($_GET['done'])) {
    $content .= makeTweetBoxes($date);
}

include('html.inc');

mysql_close();



function makeOldEntryChecker() {
    // Find the earliest "WAITING" record.
    $query = "SELECT * FROM records WHERE uid='" . mysql_real_escape_string($_SESSION['uid']) . "' AND kept='WAITING' ORDER BY date ASC";
    $recordResult = mysql_query($query);
    if (mysql_num_rows($recordResult) > 0) {
        $record = mysql_fetch_assoc($recordResult);
        $earliestDate = $record['date'];
        // If it's not today, prompt to fill in a past date.
        $today = date("Y-m-d", strtotime("today"));
        if ($earliestDate != $today) {
            $content .= "<div>You still have missing entries from " . $earliestDate . ".  <a href=\"/enter/" . $earliestDate . "\">Click here to complete them!</a></div>";
        }
    }
    return $content;
}





function makeTodaysEntry($date) {

    $query = "SELECT * FROM promises WHERE uid='" . mysql_real_escape_string($_SESSION['uid']) . "' AND active='1'";
    $promiseResult = mysql_query($query);
    
    // Friendly date header
    $today = date("Y-m-d", strtotime("today"));
    if ($date == $today) {
        $content = "<div>Today I...</div>";
    } else {
        $content = "<div>On " . $date . " I...</div>";
    }
    
    $content .= "<form method=\"post\" action=\"/entercallback.php\"><ul>";
    
    while ($promise = mysql_fetch_assoc($promiseResult)) {
    
        // Fetch the record from the DB in case there's already an entry for today.
        $query = "SELECT * FROM records WHERE uid='" . mysql_real_escape_string($_SESSION['uid']) . "' AND pid='" . mysql_real_escape_string($promise['pid']) . "' AND date='" . mysql_real_escape_string($date) . "'";
        $recordResult = mysql_query($query);
        if (mysql_num_rows($recordResult) > 0) {
            $row = mysql_fetch_assoc($recordResult);
            if ($row['kept'] == "YES") {
                $yes = " checked";
                $no = "";
            } else if ($row['kept'] == "NO") {
                $yes = "";
                $no = " checked";
            } else /* "WAITING" */ {
                $yes = "";
                $no = "";
            }
        } else {
            $yes = "";
            $no = "";
        }
    
        $content .= "<li>" . $promise['promise'] . "<span>";
        $content .= "<input type=\"radio\" name=\"" . $promise['pid'] . "\" id=\"" . $promise['pid'] . "yes\" value=\"true\" " . $yes . " /><label for=\"" . $promise['pid'] . "yes\">Yes</label>";
        $content .= "<input type=\"radio\" name=\"" . $promise['pid'] . "\" id=\"" . $promise['pid'] . "no\" value=\"false\" " . $no . " /><label for=\"" . $promise['pid'] . "no\">No</label>";
        $content .= "</span></li>";
    }
    
    $content .= "</ul>";
    $content .= "<input type=\"hidden\" name=\"date\" value=\"" . $date . "\">";
    $content .= "<input type=\"submit\" name=\"Submit\" value=\"Submit\">";
    $content .= "</form>";
    
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
    $content .= " you met " . $kept . " of your " . ($kept+$unkept+$waiting) . " promise";
    $content .= (($kept+$unkept+$waiting)==1)?".":"s.";
    if ($waiting > 0) {
        $content .= "We're still waiting for information on " . $waiting . " of them.";
    }
    $content .= "</div>";
    
    $content .= "<div><a href=\"/view\">Back to your monthly view</a></div>";
    
    return $content;
}

?>