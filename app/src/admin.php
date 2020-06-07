<?php
session_start();

if ($_SESSION["wg_userid"] != 1) {
    echo "<div style='float:left;'><form method=\"post\" action=\"login.php\">";
    echo "<table border=\"0\">
    <tr><td>Passwort:</td><td><input type=\"password\" name=\"pw\" /></td></tr>
    </table><br />
    <center>
    <input type=\"submit\" value=\"Login\" style=\"font-weight: bold;\" />
    </center>
    </form></div>";
} else {
    include("mysql.php");
    if (is_numeric($_POST["accountid"])) {
        // edit
        if ($_POST["accountactive"] == "1") {
            $active = 1;
        } else {
            $active = 0;
        }
        if ($_POST["accountpw"]) {
            // change password
            qry("UPDATE wg_user SET pw = '".md5($_POST["accountpw"])."', active = '".$active."' WHERE id = '".$_POST["accountid"]."'");
        } else {
            qry("UPDATE wg_user SET active = '".$active."' WHERE id = '".$_POST["accountid"]."'");
        }
    } else if ($_POST["accountname"]) {
        // add
        $name = escape($_POST["accountname"]);
		qry("INSERT INTO `wg_user` (`name`, `pw`, `konto`, `active`, `wg`) VALUES ('".$name."', '".md5($_POST["accountpw"])."', 0.0, 1, '".$_SESSION["wg_wg"]."')");
    }
    echo "Änderung erfolgreich<br />
    <a href=\"index.php#admin\">» weiter »</a>
    <script language=\"javascript\">
    <!--
    window.location.href=\"index.php#admin\";
    // -->
    </script>";
}

?>