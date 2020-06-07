<?php
session_start();
require("../mysql.php");

setlocale(LC_ALL, 'de_DE');
date_default_timezone_set('Europe/Berlin');

if (is_numeric($_POST["login"])) {

	$sql = qry("SELECT pw, active, wg FROM wg_user WHERE id='".$_POST["login"]."' LIMIT 1");
	$row = mysqli_fetch_assoc($sql);
	if ($row["active"] == 0 && $_POST["login"] != 4) {
		die("Account ist nicht mehr aktiv."); 
	} else if ($row["pw"] == md5($_POST["pw"])) {
		$_SESSION["wg_userid"] = $_POST["login"];
		$_SESSION["wg_wg"] = $row["wg"];
		if ($_POST["save_login"] == 1) { $_SESSION["wg_setcookie"] = md5($_POST["pw"]); }
	}
	else {
		die("Username/Passwort stimmt nicht überein."); 
	}
}

// Cookie mit LoginDaten setzen
if (isset($_SESSION["wg_setcookie"])) { 
	setcookie("wg_userid",$_SESSION["wg_userid"], time()+1209600);
	setcookie("wg_pw",$_SESSION["wg_setcookie"], time()+1209600);
	unset($_SESSION["wg_setcookie"]);
}

if ($_COOKIE["wg_userid"] == TRUE AND ($_SESSION["wg_userid"] == 0 OR $_SESSION["wg_userid"] == FALSE))
{ // Logindaten aus Cookie
	$sql = qry("SELECT pw, wg FROM wg_user WHERE id = '".$_COOKIE["wg_userid"]."'");
	$row = mysqli_fetch_assoc($sql);
	if ($_COOKIE["wg_pw"] == $row["pw"])
	{
		$_SESSION["wg_userid"] = $_COOKIE["wg_userid"];
		$_SESSION["wg_wg"] = $row["wg"];
		setcookie("wg_userid",$_COOKIE["wg_userid"], time()+1209600);
		setcookie("wg_pw",$_COOKIE["wg_pw"], time()+1209600);
	}
} // Logindaten aus Cookie ENDE

if (!is_numeric($_SESSION["wg_userid"]))
{
	$_SESSION["wg_userid"] = 0;
} else if ($_POST["datum"]) { // einkauf eintragen

	list($tag, $monat, $jahr) = explode(".", $_POST["datum"]);
	
	$date = mktime(0,0,0,$monat,$tag,$jahr);
	if ($date < (time() - (3600 * 24 * 7 * 4))) {
		die("Fehler: Der Einkauf liegt mehr als 4 Wochen zurück!");
	}
	
	if (!($_POST["anzahl"] > 0)) {
		die("Fehler: Anzahl muss > 0 sein!");
	}
	
	if (strlen($_POST["ware"]) < 3) { die("Warenbezeichnung ist zu kurz"); }

	qry("INSERT INTO wg_".$_SESSION["wg_wg"]."_einkaeufe (datum, ware, kaeufer, preis, anz, gewicht) VALUE ('".$date."','".$_POST["ware"]."','".$_POST["kaeufer"]."','".str_replace(',','.',$_POST["preis"])."','".$_POST["anzahl"]."','".str_replace(',','.',$_POST["gewicht"])."')");
	
	$sql = qry("SELECT LAST_INSERT_ID() AS id");
	$row = mysqli_fetch_assoc($sql);
	$id = $row["id"];
	
	if ($_POST["anzahl"] == 1) {
		$sql = qry("SELECT id FROM wg_user WHERE active > 0 AND wg = '".$_SESSION["wg_wg"]."'");
		while($row = mysqli_fetch_assoc($sql))
		{
			qry("INSERT INTO wg_".$_SESSION["wg_wg"]."_einkaeufe_mitzahlung (einkauf, user) VALUE (".$id.",'".$row["id"]."')");
		}
		
		twitter(getUserName($_POST["kaeufer"])." hat ".$_POST["ware"]." eingekauft (".$_POST["preis"]." EUR)");
	} else { // Anzahl > 1
		twitter(getUserName($_POST["kaeufer"])." hat ".$_POST["anzahl"]."x ".$_POST["ware"]." eingekauft (".$_POST["preis"]." EUR)");
	}
} else if (isset($_POST["mitzahlung"])) { // mitzahlung ändern
	$sql = qry("SELECT id, datum, ware, kaeufer, preis, anz FROM wg_".$_SESSION["wg_wg"]."_einkaeufe WHERE ((anz = '1' AND datum >= '".(time()-3600 * 24 * 7 * 4)."') OR (anz > '1' AND verbraucht = '0')) ORDER BY datum DESC, id DESC");
	while($row = mysqli_fetch_assoc($sql))
	{
		$eid = $row["id"];
		if ($row["anz"] == 1) {	
			qry("DELETE FROM wg_".$_SESSION["wg_wg"]."_einkaeufe_mitzahlung WHERE einkauf = '".$eid."'");
			$mitzahler = $_POST["einkauf_".$eid];
			for($i=0;$i<count($mitzahler);$i++)
			{
				qry("INSERT INTO wg_".$_SESSION["wg_wg"]."_einkaeufe_mitzahlung (einkauf, user) VALUES ('".$eid."','".$mitzahler[$i]."')");	
			}
		}	
		else {
			$anz = $_POST["einkauf_".$eid."_anz"];
			$uid = $_POST["einkauf_".$eid."_uid"];
			$gesamtanz = 0;
			foreach($anz as $curr) {
				$gesamtanz += $curr;
			}
			
			if ($row["anz"] < $gesamtanz) { die("Fehler: Bei Einkauf Nr. ".$eid." wurden mehr Waren verbraucht als verfügbar sind."); }
			else if ($row["anz"] == $gesamtanz) {
				qry("UPDATE wg_".$_SESSION["wg_wg"]."_einkaeufe SET verbraucht = '1' WHERE id = '".$eid."'");
			}
			
			qry("DELETE FROM wg_".$_SESSION["wg_wg"]."_einkaeufe_mitzahlung WHERE einkauf = '".$eid."'");
			for($i=0;$i<count($uid);$i++)
			{
				qry("INSERT INTO wg_".$_SESSION["wg_wg"]."_einkaeufe_mitzahlung (einkauf, user, anz) VALUES ('".$eid."','".$uid[$i]."','".$anz[$i]."')");	
			}
		}
	}
}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="de" lang="de">
<head>
	<title>WG Management-System</title>
	<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />

	<meta http-equiv="pragma" content="no-cache" />
	<meta http-equiv="cache-control" content="no-cache" />
	<meta name="viewport" content="width=480" />

	<link rel="shortcut icon" href="../favicon.ico" type="image/x-icon" />
	<style>
		input,select
			{
				font-family: Arial;
				color:#000000;
				word-spacing:0;
				text-indent:0;
				border:1px solid #000000;
				margin:0;
				background:#FFFFFF;
			}
	</style>
	
	<script type="text/javascript">
	function toggle(id)
	{
		color = document.getElementById(id).style.backgroundColor;
		if (color == "red" || color == "#ff0000")
		{
			document.getElementById(id).style.backgroundColor = "green";
		}
		else
		{
			document.getElementById(id).style.backgroundColor = "red";
		}
	}
	</script>

</head>
<body style="background-color: #cbcbcb; font-family: Arial; width: 460px; left: 10px; right: 10px;">

<?php 

if ($_SESSION["wg_userid"] > 0) {

	echo "<fieldset style=\"background-color: #ddd; border: 1px solid black;\"><legend>Einkauf hinzufügen</legend>
	<form method=\"post\" action=\"index.php\">
	<table width=\"100%\" border=\"0\"><tr>
    <td>Ware: <input type=\"text\" name=\"ware\" size=\"10\" /></td>
	<td>Preis: <input type=\"text\" size=\"3\" name=\"preis\" /> €</td>
	<td style=\"text-align: right;\"><input type=\"submit\" value=\"hinzufügen\" /> <a href=\"javascript: void(0);\" onclick=\"javascript:document.getElementById('exEinkauf').style.display='block';\" style=\"text-decoration: none; color: black; font-size: 12px;\">[+]</a></td></tr>
	</table>
	<div id=\"exEinkauf\" style=\"display: none;\">
	<table style=\"border: 0px; width: 70%;\"><tr>
	<td>Datum: <input type=\"text\" name=\"datum\" value=\"".date("d.m.Y")."\" size=\"6\" /></td>
	<td>Anzahl: <input type=\"text\" size=\"1\" name=\"anzahl\" value=\"1\" /></td>
	<td>Gewicht: <input type=\"text\" size=\"1\" name=\"gewicht\" /> kg</td>
	<td>Käufer: <select name=\"kaeufer\">";

	$sql = qry("SELECT id, name FROM wg_user WHERE wg = '".$_SESSION["wg_wg"]."' AND active > 0 ORDER BY name ASC");
	while($row = mysqli_fetch_assoc($sql)) {
		echo "<option";
		
		if ($row["id"] == $_SESSION["wg_userid"]) { echo " selected=\"selected\""; }
		
		echo " value=\"".$row["id"]."\">".$row["name"]."</option>";
	}
	
	echo "</select></td>
	</tr></table>
	</div></form></fieldset><br /><br />";
	
	
	echo "<fieldset style=\"background-color: #ddd; border: 1px solid black;\"><legend>Letzte Einkäufe</legend><form name=\"einkaeufe\" method=\"post\" action=\"index.php\"><input type=\"hidden\" name=\"mitzahlung\" value=\"1\" />";
	echo "<table cellpadding=\"5\" border=\"1\" style=\"border: 1px solid black; border-collapse: collapse; width: 100%;\">";

	echo "<tr style=\"background-color: #848181;\">
					<td>Ware</td>
					<td>Preis</td>";

		$sql2 = qry("SELECT name FROM wg_user WHERE wg = '".$_SESSION["wg_wg"]."' AND active > 0 ORDER BY name ASC");
		while($row = mysqli_fetch_assoc($sql2))
		{
			echo "<td>".$row["name"]."</td>";
		}
	echo "</tr>";
	$zeit = 3600 * 24 * 7 * 4; // vier wochen
	$sql = qry("SELECT id, ware, preis, anz FROM wg_".$_SESSION["wg_wg"]."_einkaeufe WHERE (datum >= '".(time()-$zeit)."' OR anz > '1' AND verbraucht = '0') ORDER BY datum DESC, id DESC");
	if (mysqli_num_rows($sql) == 0) {
		$spalten = 2 + mysqli_num_rows($sql2);
		echo "<tr><td colspan='".$spalten."'>Keine Einkäufe in den letzten 4 Wochen.</td></tr>";
	} else {
		while($row = mysqli_fetch_assoc($sql))
		{
			$sql2 = qry("SELECT user, anz FROM wg_".$_SESSION["wg_wg"]."_einkaeufe_mitzahlung WHERE einkauf = '".$row["id"]."' ORDER BY user ASC");
			while($row2 = mysqli_fetch_assoc($sql2))
			{
				$mitzahler[$row2["user"]] = $row2["anz"];
			}
			if ($row["anz"] > 1)
			{
				$anz = " (".$row["anz"].")";
			}
			else 
			{
				$anz = "";			
			}
		
			echo "<tr><td>".$row["ware"].$anz."</td><td>".number_format($row["preis"],2,',','.')."</td>";
		
			$sql2 = qry("SELECT id FROM wg_user WHERE wg = '".$_SESSION["wg_wg"]."' AND active > 0 ORDER BY name ASC");
			while($row2 = mysqli_fetch_assoc($sql2))
			{
				if (isset($mitzahler[$row2["id"]]) && $mitzahler[$row2["id"]] >= 1) {
					$bg = "green";
					$checked = " checked=\"CHECKED\"";
				}
				else {
					$bg = "red";
					$checked = "";
				}
				echo "<td id=\"e".$row["id"]."_u".$row2["id"]."\" style=\"text-align: center; background-color: ".$bg.";\">";
				if ($row["anz"] == 1 ) {
					echo "<input onclick=\"javascript:toggle('e".$row["id"]."_u".$row2["id"]."');\" type=\"checkbox\" name=\"einkauf_".$row["id"]."[]\" value=\"".$row2["id"]."\" ".$checked."/>";
				} else {
					echo "<input style=\"text-align: center;\" type=\"text\" size=\"2\" name=\"einkauf_".$row["id"]."_anz[]\" value=\"".$mitzahler[$row2["id"]]."\" />
								<input type=\"hidden\" name=\"einkauf_".$row["id"]."_uid[]\" value=\"".$row2["id"]."\" />";
				}			
				echo "</td>";
			}
			$sql3 = qry("SELECT id FROM wg_user WHERE wg = '".$_SESSION["wg_wg"]."' AND active < 1");
			while($row3 = mysqli_fetch_assoc($sql3))
			{
				if (isset($mitzahler[$row3["id"]]) && $mitzahler[$row3["id"]] >= 1) {
					echo "<input type=\"hidden\" name=\"einkauf_".$row["id"]."[]\" value=\"".$row3["id"]."\" />";
				}			
			}
				
			echo "</tr>";
		
			unset($mitzahler);
		}
	}

	echo "</table><br />";
	echo "<a href=\"javascript:document.einkaeufe.submit();\" style=\"text-decoration: none; color: black;\"><img border=\"0\" src=\"../images/save.png\" alt=\"[save]\" /> Speichern</a></form></fieldset>";


} else {

	echo "<form  method=\"post\" action=\"index.php\">
	<table border=\"0\" >
	<tr><td>Login:</td><td><select name=\"login\">";
	
	$sql = qry("SELECT id, name, wg FROM wg_user ORDER BY name ASC");
	while($row = mysqli_fetch_assoc($sql)) {
		echo "<option value=\"".$row["id"]."\">".$row["name"]." (".($row["wg"] == "ka" ? "Karlsruhe" : "Nürnberg").")</option>";
	}
	
	echo "</select></td></tr>
	<tr><td>Passwort:</td><td><input type=\"password\" name=\"pw\" /></td></tr>
	<tr><td colspan=\"2\"><input type=\"checkbox\" name=\"save_login\" value=\"1\" checked=\"checked\" /> Login-Daten speichern</td></tr>
	</table><br />
	<input type=\"submit\" value=\"Login\" style=\"font-weight: bold;\" />
	</form>";

}

?>
<br />
<center style="font-size: 10pt;">
&copy; WG Management System | <a href="../index.php" style="text-decoration: none; color: black;">Desktop site</a>
</center>
</body>
</html>