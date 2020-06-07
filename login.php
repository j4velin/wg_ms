<?php
session_start();
include("mysql.php");

if (is_numeric($_POST["login"])) {

	$sql = qry("SELECT pw, active, wg FROM wg_user WHERE id='".$_POST["login"]."' LIMIT 1");
	$row = mysqli_fetch_assoc($sql);
	if ($row["active"] == 0 && $_POST["login"] != 4) {
		echo "Account ist nicht mehr aktiv."; 
	} else if ($row["pw"] == md5($_POST["pw"])) {
		$_SESSION["wg_userid"] = $_POST["login"];
		$_SESSION["wg_wg"] = $row["wg"];
		if ($_POST["save_login"] == 1) { $_SESSION["wg_setcookie"] = md5($_POST["pw"]); }
		echo "Login erfolgreich!<br />
		<a href=\"index.php\">» weiter »</a>
		<script language=\"javascript\">
		<!--
		window.location.href=\"index.php\";
		// -->
		</script>";
	}
	else {
		echo "Username/Passwort stimmt nicht überein."; 
	}
}
?>