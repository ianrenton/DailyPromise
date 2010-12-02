<?php

/* Load required lib files. */
session_start();
require_once('auth.php');
require_once('config.php');
require_once('common.php');

// Require authentication for this page
auth();

if ((isset($_POST['password'])) && (isset($_POST['password2']))) {
    mysql_connect(DB_SERVER,DB_USER,DB_PASS);
    @mysql_select_db(DB_NAME) or die( "Unable to select database");

    // Update the user's entry in the users table.
    $query = "SELECT * FROM users WHERE uid='" . mysql_real_escape_string($_SESSION['uid']) . "'";
    $result = mysql_query($query);
    if (mysql_num_rows($result) > 0) {
        if ($_POST['password'] == $_POST['password2']) {
            if ($_POST['password'] != "") {
                // OK to set password now
                $query = "UPDATE users SET password='" . md5($_POST['password']) . "' WHERE uid='" . mysql_real_escape_string($_SESSION['uid']) . "'";
                mysql_query($query);
                mysql_close();
                header('Location: /configure/passwordset');
            } else {
                // Can't remove password if none set
                $row = mysql_fetch_assoc($result);
                if ($row['password'] == "") {
                    mysql_close();
                    header('Location: /configure/blankpassword');
                    die();
                }
                // Blank string = please remove my password
                $query = "UPDATE users SET password='' WHERE uid='" . mysql_real_escape_string($_SESSION['uid']) . "'";
                mysql_query($query);
                mysql_close();
                header('Location: /configure/passwordremoved');
            }
            die();
        } else {
            mysql_close();
            header('Location: /configure/passwordmismatch');
            die();
        }
    } else {
        mysql_close();
        header('Location: /configure/error');
        die();
    }
    
} else {
    header('Location: /configure/error');
    die();
}
?>