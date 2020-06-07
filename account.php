<?php

session_start();
include("mysql.php");
if ($_SESSION["wg_userid"] == 0) { loginfirst(); }

if ($_POST["pwa"] == $_POST["pwb"])
{
	qry("UPDATE wg_user SET pw = '".md5($_POST["pwa"])."' WHERE id='".$_SESSION["wg_userid"]."'");
		echo "Änderung erfolgreich!<br />
		<a href=\"index.php#account\">» weiter »</a>
		<script language=\"javascript\">
		<!--
		window.location.href=\"index.php#account\";
		// -->
		</script>";
}
else
{
	echo "Passwörter stimmen nicht überein!";
}

?>