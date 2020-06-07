<?php
session_start();
include("../mysql.php");
if ($_SESSION["wg_userid"] == 0) { loginfirst(); }

// neue transaktion
if (is_numeric($_POST["person"]))
{
	if ($_POST["art"] == 1) {
		qry("INSERT INTO wg_".$_SESSION["wg_wg"]."_zahlungen (datum, absender, empfaenger, betrag, text) VALUES ('".time()."','".escape($_POST["person"])."','".$_SESSION["wg_userid"]."','".str_replace(",",".",escape($_POST["betrag"]))."', '".escape($_POST["text"])."')");
	} else if ($_POST["art"] == 2) {
		qry("INSERT INTO wg_".$_SESSION["wg_wg"]."_zahlungen (datum, absender, empfaenger, betrag, text) VALUES ('".time()."','".$_SESSION["wg_userid"]."','".escape($_POST["person"])."','".str_replace(",",".",escape($_POST["betrag"]))."', '".escape($_POST["text"])."')");
	}
		echo "Änderung erfolgreich!<br />
		<a href=\"../index.php#ausgleich\">» weiter »</a>
		<script language=\"javascript\">
		<!--
		window.location.href=\"../index.php#ausgleich\";
		// -->
		</script>";
}

// alle aktualisieren

$sql3 = qry("SELECT id FROM wg_user WHERE wg = '".$_SESSION["wg_wg"]."'");
while($row3 = mysqli_fetch_assoc($sql3))
{
	$kaeufe = 0;
	$schulden = 0;
	$sql = qry("SELECT id, preis, kaeufer, anz FROM wg_".$_SESSION["wg_wg"]."_einkaeufe");
	while($row = mysqli_fetch_assoc($sql))
	{
		if ($row["kaeufer"] == $row3["id"])
		{
			$kaeufe += $row["preis"];
		}
		
		$sql2 = qry("SELECT einkauf, anz FROM wg_".$_SESSION["wg_wg"]."_einkaeufe_mitzahlung WHERE einkauf = '".$row["id"]."' && user = '".$row3["id"]."'");
		
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
	
	$sql = qry("SELECT SUM(betrag) AS ausgleich FROM wg_".$_SESSION["wg_wg"]."_zahlungen WHERE absender = '".$row3["id"]."'");
	$row = mysqli_fetch_assoc($sql);
	$ausgleich = $row["ausgleich"];
	
	$sql = qry("SELECT SUM(betrag) AS ausgleich FROM wg_".$_SESSION["wg_wg"]."_zahlungen WHERE empfaenger = '".$row3["id"]."'");
	$row = mysqli_fetch_assoc($sql);
	$ausgleich -= $row["ausgleich"];
	
	qry("UPDATE wg_user SET konto = '".($kaeufe-$schulden+$ausgleich)."' WHERE id = '".$row3["id"]."'");
}


$sql = qry("SELECT konto FROM wg_user WHERE id = '".$_SESSION["wg_userid"]."'");
$row = mysqli_fetch_assoc($sql);

echo "Dein Kontostand ist <b>".number_format($row["konto"],2,',','.')." €</b>.<br />";

if ($row["konto"] < 0) { // user im minus
echo "Du solltest einkaufen gehen oder eine 
Ausgleichszahlung vornehmen.<br /><ul>";

$restschulden = $row["konto"]*(-1);
$sql = qry("SELECT id, konto FROM wg_user WHERE wg = '".$_SESSION["wg_wg"]."' AND konto > 0 ORDER BY konto DESC");
while(($row = mysqli_fetch_assoc($sql)) && $restschulden > 0) {
	if ($row["konto"] - $restschulden >= 0) {
		echo "<li>".number_format($restschulden,2,',','.')." € an ".getUserName($row["id"])."</li>";
		$restschulden = 0;
	} else {
		echo "<li>".number_format($row["konto"],2,',','.')." € an ".getUserName($row["id"])."</li>";
		$restschulden -= $row["konto"];
	}
}
echo "</ul>";

} else { // user im plus

echo "Du solltest mehr verbrauchen oder Geld eintreiben.<ul>";

$restguthaben = $row["konto"];
$sql = qry("SELECT id, konto FROM wg_user WHERE wg = '".$_SESSION["wg_wg"]."' AND konto < 0 ORDER BY konto ASC");
while(($row = mysqli_fetch_assoc($sql)) && $restguthaben > 0) {
	if ($row["konto"] + $restguthaben < 0) {
		echo "<li>".number_format($restguthaben,2,',','.')." € von ".getUserName($row["id"])."</li>";
		$restguthaben = 0;
	} else {
		echo "<li>".number_format($row["konto"]*(-1),2,',','.')." € von ".getUserName($row["id"])."</li>";
		$restguthaben += $row["konto"];
	}
}
echo "</ul>";

}

echo "<hr style=\"width: 100%; color: black; height: 1px; border: 1px solid black;\" />
	<fieldset style=\"border: 1px solid black; padding-top: 20px;\"><legend>Neue Transaktion</legend>
	<form method=\"post\" action=\"ajax/ausgleich.php\">
	Ich habe <select name=\"art\" onchange=\"selectChanged(this)\">
	<option value=\"1\">von</option>
	<option value=\"2\">an</option>
	</select>	<select name=\"person\">";

$sql = qry("SELECT id, name FROM wg_user WHERE wg = '".$_SESSION["wg_wg"]."' ORDER BY name ASC");
while($row = mysqli_fetch_assoc($sql)) {
	echo "<option value=\"".$row["id"]."\">".$row["name"]."</option>";
}

echo "</select> <input type=\"text\" size=\"3\" name=\"betrag\" />€ <span id='verb'>bekommen</span>.<br />
Kommentar: <input type='text' name='text' style='margin-top: 5px;' /> <input type=\"submit\" value=\"speichern\" style=\"float: right;\" />
</form></fieldset><br />";

echo "Die letzten dich betreffenden Transaktionen:<br />";

$sql = qry("SELECT datum, absender, empfaenger, betrag, text FROM wg_".$_SESSION["wg_wg"]."_zahlungen WHERE absender = '".$_SESSION["wg_userid"]."' OR empfaenger = '".$_SESSION["wg_userid"]."' ORDER BY datum DESC");
if (mysqli_num_rows($sql) <= 0) {
	echo "Keine Daten verfügbar.";
} else {
	echo "<table cellpadding=\"7\" border=\"1\" style=\"margin: 10px; border: 1px solid black; border-collapse: collapse; width: 100%;\">";
	echo "<tr style=\"background-color: #848181;\">
	<td>Datum</td>
	<td>von</td>
	<td>an</td>
	<td>Betrag</td>
	<td>Kommentar</td>
	</tr>";
	
	while($row = mysqli_fetch_assoc($sql)) {
		echo "<tr>
	<td>".date("d.m.Y",$row["datum"])."</td>
	<td>".getUserName($row["absender"])."</td>
	<td>".getUserName($row["empfaenger"])."</td>
	<td>".number_format($row["betrag"],2,',','.')."€</td>
	<td>".$row["text"]."</td>
		</tr>";
	}

	echo "</table>";
}


?>