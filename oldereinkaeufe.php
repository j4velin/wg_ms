<?php
session_start();
include("mysql.php");
if ($_SESSION["wg_userid"] == 0) { loginfirst(); }
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="de" lang="de">
<head>
	<title>ältere Einkäufe</title>
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
				<td>Ware (Anzahl) [Gewicht]</td>
				<td>Preis</td>
				<td>&#216;&nbsp;Preis</td>
				<td>pro Person/Stück</td>
				<td>Käufer</td>

<?php

	$sql = qry("SELECT name FROM wg_user WHERE wg = '".$_SESSION["wg_wg"]."' ORDER BY name ASC");
	while($row = mysqli_fetch_assoc($sql))
	{
		echo "<td>".$row["name"]."</td>";
	}

echo "</tr>";
$sql = qry("SELECT id, datum, ware, kaeufer, preis, anz, gewicht FROM wg_".$_SESSION["wg_wg"]."_einkaeufe ORDER BY datum DESC, id DESC");
while($row = mysqli_fetch_assoc($sql))
{
	$sql2 = qry("SELECT user, anz FROM wg_".$_SESSION["wg_wg"]."_einkaeufe_mitzahlung WHERE einkauf = '".$row["id"]."' ORDER BY user ASC");
	while($row2 = mysqli_fetch_assoc($sql2))
	{
		$mitzahler[$row2["user"]] = $row2["anz"];
	}
	
	$anz = "";
	if (mysqli_num_rows($sql2) > 0) {
		if ($row["anz"] > 1)
		{
			$anz = " (".$row["anz"].")";
			$preisproperson = "<div align=\"right\">".number_format($row["preis"]/$row["anz"],2,',','.')."&nbsp;€</div>";
		} else {
			$preisproperson = number_format($row["preis"]/mysqli_num_rows($sql2),2,',','.')."&nbsp;€";
		}
	}
	else { $preisproperson = "&#8734;"; }
	
		// durchschnittspreis pro gewicht berechnen
		$sql3 = qry("SELECT AVG(preis/gewicht) AS av FROM wg_".$_SESSION["wg_wg"]."_einkaeufe WHERE ware = '".$row["ware"]."' AND gewicht != '0'");
		$row3 = mysqli_fetch_assoc($sql3);
		if (is_numeric($row3["av"])) // avg preis ex
		{
		$avg = $row3["av"] * $row["gewicht"]; // preis pro einkauf
			if ($row["gewicht"] == 0) // kein gewicht angegeben
			{
				$img = "fragezeichen";
				$avgstr1 = "<img src=\"images/".$img.".png\" title=\"".number_format($row3["av"],2,',','.')."€/kg\" />";
			} else {
			if (round($avg,2) > round($row["preis"],2)) // billiger gekauft
			{
				$img = "green";
			} else if (round($avg,2) < round($row["preis"],2)) // teurer gekauft
			{
				$img = "red";
			} else { // gleich
				$img = "blue";
			}
			$avgstr1 = "<img src=\"images/".$img.".png\" title=\"".number_format($row3["av"],2,',','.')."€/kg => ".number_format($row3["av"]*$row["gewicht"],2,',','.')."€/".str_replace(".",",",$row["gewicht"])."kg\" />";
		}
		} else { // kein avg 
			$avgstr1 = "";
		}
		
		
		// durchschnittspreis pro stück berechnen
		$sql3 = qry("SELECT AVG(preis/anz) AS av FROM wg_".$_SESSION["wg_wg"]."_einkaeufe WHERE ware = '".$row["ware"]."' AND anz > '1'");
		$row3 = mysqli_fetch_assoc($sql3);
		if (is_numeric($row3["av"])) // avg preis ex
		{
		$avg = $row3["av"] * $row["anz"]; // preis pro einkauf
			if ($row["anz"] == 1) // kein stück angegeben
			{
				$img = "fragezeichen";
				$avgstr2 = "<img src=\"images/".$img.".png\" title=\"".number_format($row3["av"],2,',','.')."€/Stück\" />";
			} else {
			 if (round($avg,2) > round($row["preis"],2)) // billiger gekauft
			{
				$img = "green";
			} else if (round($avg,2) < round($row["preis"],2)) // teurer gekauft
			{
				$img = "red";
			} else { // gleich
				$img = "blue";
			}
			$avgstr2 = "<img src=\"images/".$img.".png\" title=\"".number_format($row3["av"],2,',','.')."€/Stück => ".number_format($row3["av"]*$row["anz"],2,',','.')."€/".$row["anz"]."\" />";
		}
		} else { // kein avg 
			$avgstr2 = "";
		}
		
		if ($avgstr1 != "") {
			if ($avgstr2 != "") {
				$avgstr = "&nbsp;".$avgstr1.",".$avgstr2."";
			} else {
				$avgstr = "&nbsp;".$avgstr1."";
			}
		} else if ($avgstr2 != "") {
			$avgstr = "&nbsp;".$avgstr2."";
		} else {
			$avgstr = "";
		}
		
		if ($row["gewicht"] > 0)
		{
			$gewicht = " [".str_replace(".",",",$row["gewicht"])."kg]";
		} else {
			$gewicht = "";
		}

		echo "<tr><td>".date("d.m.Y",$row["datum"])."</td>
							<td>".$row["ware"].$anz.$gewicht."</td>
							<td>".number_format($row["preis"],2,',','.')."&nbsp;€</td>
							<td style=\"text-align: center;\">".$avgstr."</td>
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

	echo "</tr>";

	unset($mitzahler);
}


echo "</table></body></html>";

?>