<?php

/* Load required lib files. */
session_start();
require_once('auth.php');
require_once('config.php');
require_once('common.php');

// Require authentication for this page
auth();

if (isset($_POST['delete'])) {
    mysql_connect(DB_SERVER,DB_USER,DB_PASS);
    @mysql_select_db(DB_NAME) or die( "Unable to select database");

    // Account deletion
    $query = "SELECT * FROM users WHERE uid='" . mysql_real_escape_string($_SESSION['uid']) . "'";
    $result = mysql_query($query);
    if (mysql_num_rows($result) > 0) {
        // Delete the user's entry in the users table.
        if (($_POST['delete'] == "delete") || ($_POST['delete'] == "nuke")) {
            $query = "DELETE FROM users WHERE uid='" . mysql_real_escape_string($_SESSION['uid']) . "'";
            mysql_query($query);
        }
        
        // Nuke if requested
        if ($_POST['delete'] == "nuke") {
            $query = "DELETE FROM promises WHERE uid='" . mysql_real_escape_string($_SESSION['uid']) . "'";
            mysql_query($query);
            $query = "DELETE FROM records WHERE uid='" . mysql_real_escape_string($_SESSION['uid']) . "'";
            mysql_query($query);
        }
        mysql_close();
        header('Location: /home/deleted');
        die();
    } else {
        mysql_close();
        header('Location: /configure/deleteerror');
        die();
    }
    
} else {
    header('Location: /configure/deleteerror');
    die();
}
?>
