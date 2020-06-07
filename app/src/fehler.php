<?php
session_start();
include("mysql.php");
if ($_SESSION["wg_userid"] == 0) { loginfirst(); }
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="de" lang="de">
<head>
	<title>fehlerhafte Einträge</title>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />

	<meta http-equiv="pragma" content="no-cache" />
	<meta http-equiv="cache-control" content="no-cache" />

	<link rel="shortcut icon" href="favicon.ico" type="image/x-icon" />
	<link rel="stylesheet" type="text/css" href="style.css" />
</head>
<body>
<table cellpadding="5" border="1" style="font-family: Arial; font-size: 0.8em; border: 1px solid black; border-collapse: collapse;">
<tr style="background-color: #848181;">
				<td>Datum</td>
				<td>Ware (Anzahl)</td>
				<td>Preis</td>
				<td>pro Person/Stück</td>
				<td>Käufer</td>
<?php

	$sql = qry("SELECT name FROM wg_user WHERE wg = '".$_SESSION["wg_wg"]."' ORDER BY name ASC");
	while($row = mysqli_fetch_assoc($sql))
	{
		echo "<td>".$row["name"]."</td>";
	}
	
$errors = false;
$spalten = 6 + mysqli_num_rows($sql);

echo "<td>Fehlerbeschreibung</td></tr>";
$sql = qry("SELECT id, datum, ware, kaeufer, preis, anz FROM wg_".$_SESSION["wg_wg"]."_einkaeufe ORDER BY datum DESC");
while($row = mysqli_fetch_assoc($sql))
{
	$verbraucht = 0;
	$fehler = "";	
	$anz = "";
	unset($mitzahler);
	
	$sql2 = qry("SELECT user, anz FROM wg_".$_SESSION["wg_wg"]."_einkaeufe_mitzahlung WHERE einkauf = '".$row["id"]."' ORDER BY user ASC");
	while($row2 = mysqli_fetch_assoc($sql2))
	{
		$mitzahler[$row2["user"]] = $row2["anz"];
		$verbraucht += $row2["anz"];
	}
	
	if ($row["anz"] > 1) {
		$anz = " (".$row["anz"].")";
		if ($row["anz"] != $verbraucht) {
		$fehler .= "Es wurden nicht alle Stücke verbraucht. ";
		}	
	}
	
	if (mysqli_num_rows($sql2) > 0) {
		$preisproperson = number_format($row["preis"]/mysqli_num_rows($sql2),2,',','.')."&nbsp;€";
	}
	else { $preisproperson = "&#8734;"; $fehler .= "Ware wurde von niemanden verbraucht. "; }
	
	if ($fehler == "") { continue; }
	
	$errors = true;

	echo "<tr><td>".date("d.m.Y",$row["datum"])."</td>
						<td>".$row["ware"].$anz."</td>
						<td>".number_format($row["preis"],2,',','.')."&nbsp;€</td>
						<td>".$preisproperson."</td>
						<td>".getUserName($row["kaeufer"])."</td>";

	$sql2 = qry("SELECT id FROM wg_user WHERE wg = '".$_SESSION["wg_wg"]."' ORDER BY name ASC");
	while($row2 = mysqli_fetch_assoc($sql2))
	{
		if ($mitzahler[$row2["id"]] >= 1) {
			$bg = "green";
			$checked = " checked=\"CHECKED\"";
		}
		else {
			$bg = "red";
			$checked = "";
		}
		echo "<td id=\"e".$row["id"]."_u".$row2["id"]."\" style=\"text-align: center; background-color: ".$bg.";\">";
		
		if ($row["anz"] == 1 ) {
			echo "<input onclick=\"javascript:void(0);\" type=\"checkbox\" DISABLED ".$checked."/>";
		} else {
			echo "<input style=\"text-align: center;\" type=\"text\" size=\"2\" value=\"".$mitzahler[$row2["id"]]."\" DISABLED />";
		}		
		
		echo "</td>";
	}

	echo "<td>".$fehler."</td></tr>";
}

if ($errors == false) {
echo "<tr><td colspan=\"".$spalten."\">Keine Fehler gefunden!</td></tr>";
}

echo "</table></body></html>";

?>