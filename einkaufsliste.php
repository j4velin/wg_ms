<?php
session_start();
header("Content-Type: text/html; charset=UTF-8"); 
include("mysql.php");
if ($_SESSION["wg_userid"] == 0) { loginfirst(); }

if (isset($_POST["ware"])) {

	qry("INSERT INTO wg_".$_SESSION["wg_wg"]."_einkaufsliste (datum, ware, user, privat) VALUE 
	('".time()."','".$_POST["ware"]."','".$_SESSION["wg_userid"]."', '".($_POST["privat"] == 1 ? 1 : 0)."')");
	if ($_POST["privat"] != 1) {
		twitter(getUserName($_SESSION["wg_userid"])." hat ".$_POST["ware"]." auf die Einkaufsliste gesetzt");
	}
}
else if (is_numeric($_GET["del"]))
{
	qry("DELETE FROM wg_".$_SESSION["wg_wg"]."_einkaufsliste WHERE id = '".$_GET["del"]."'");
}

		echo "Änderung erfolgreich!<br />
		<a href=\"index.php#einkaufsliste\">» weiter »</a>
		<script language=\"javascript\">
		<!--
		window.location.href=\"index.php#einkaufsliste\";
		// -->
		</script>";

?>