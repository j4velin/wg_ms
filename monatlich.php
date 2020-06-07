<?php
session_start();
header("Content-Type: text/html; charset=UTF-8");  
include("mysql.php");
if ($_SESSION["wg_userid"] == 0) { loginfirst(); }

if (strlen($_POST["bezeichnung"]) > 0) { // neu eintragen
	
	$kosten = str_replace(".",",",$_POST["kosten"]);
	list($euro, $cent) = explode(",", $kosten);
	
	if (strpos($kosten, ",") !== FALSE) { // contains a ,
		$shortcents = substr($kosten, strpos($kosten, ",")+1);
		if (strlen($shortcents) == 1) {
			$cent = $shortcents * 10;
		}
	}
	
	$kosten = $euro * 100 + $cent;

	qry("INSERT INTO wg_".$_SESSION["wg_wg"]."_monatlich (bezeichnung, kosten, turnus) VALUE ('".$_POST["bezeichnung"]."','".$kosten."','".$_POST["turnus"]."')");
	
} else if (is_numeric($_GET["del"])) // löschen
{
	qry("DELETE FROM wg_".$_SESSION["wg_wg"]."_monatlich WHERE id = '".$_GET["del"]."'");
}
		echo "Änderung erfolgreich!<br />
		<a href=\"index.php#monatlich\">» weiter »</a>
		<script language=\"javascript\">
		<!--
		window.location.href=\"index.php#monatlich\";
		// -->
		</script>";
?>