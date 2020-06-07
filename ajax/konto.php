<?php
session_start();
if ($_SESSION["wg_userid"] == 0) { die("Bitte zuerst einloggen."); }
include("../mysql.php");

$kaeufe = 0;
$schulden = 0;
$sql = qry("SELECT id, preis, kaeufer, anz FROM wg_".$_SESSION["wg_wg"]."_einkaeufe");
while($row = mysqli_fetch_assoc($sql))
{
	if ($row["kaeufer"] == $_SESSION["wg_userid"])
	{
		$kaeufe += $row["preis"];
	}
	
	$sql2 = qry("SELECT einkauf, anz FROM wg_".$_SESSION["wg_wg"]."_einkaeufe_mitzahlung WHERE einkauf = '".$row["id"]."' && user = '".$_SESSION["wg_userid"]."'");
	
	if (mysqli_num_rows($sql2) != 0)
	{
		if ($row["anz"] == 1) {			
			$sql2 = qry("SELECT einkauf FROM wg_".$_SESSION["wg_wg"]."_einkaeufe_mitzahlung WHERE einkauf = '".$row["id"]."'");
			$mitzahler = mysqli_num_rows($sql2);
			
			if ($mitzahler > 0) { $schulden += $row["preis"] / $mitzahler; }
		} else {
			$row2 = mysqli_fetch_assoc($sql2);
			$schulden += ($row["preis"] / $row["anz"]) * $row2["anz"];		
		}
	}
}

$sql = qry("SELECT SUM(betrag) AS ausgleich FROM wg_".$_SESSION["wg_wg"]."_zahlungen WHERE absender = '".$_SESSION["wg_userid"]."'");
$row = mysqli_fetch_assoc($sql);
$ausgleich = $row["ausgleich"];

$sql = qry("SELECT SUM(betrag) AS ausgleich FROM wg_".$_SESSION["wg_wg"]."_zahlungen WHERE empfaenger = '".$_SESSION["wg_userid"]."'");
$row = mysqli_fetch_assoc($sql);
$ausgleich -= $row["ausgleich"];

$farbe = "red";
if ($kaeufe-$schulden+$ausgleich > 0) { $farbe = "green"; }

$aus_farbe = "red";
if ($ausgleich > 0) { $aus_farbe = "green"; }

echo "<table>
<tr><td>Käufe:</td><td style=\"text-align: right; color: green;\">".number_format($kaeufe,2,',','.')." €</td></tr>
<tr><td>Verbrauch:</td><td style=\"text-align: right; color: red;\">".number_format($schulden*(-1),2,',','.')." €</td></tr>
<tr><td><a class=\"menue\" href=\"#ausgleich\" onclick=\"get_content('content','ajax/ausgleich.php'); showKonto();\">Ausgleich:</></td><td style=\"text-align: right; color: ".$aus_farbe."\">".number_format($ausgleich,2,',','.')." €</td></tr>
</table>
<hr style=\"border: 1px solid black; width: 100%;\" />
<div style=\"width: 100%; text-align: right;\">Guthaben: <b style=\"color: ".$farbe.";\">".number_format($kaeufe-$schulden+$ausgleich,2,',','.')." €</b></div>";


?>