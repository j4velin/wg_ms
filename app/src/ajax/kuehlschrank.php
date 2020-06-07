<?php
session_start();
include("../mysql.php");
if ($_SESSION["wg_userid"] == 0) { loginfirst(); }

if (is_numeric($_POST["einkaufid"])) {
	if (mysqli_num_rows(qry("SELECT anz FROM wg_".$_SESSION["wg_wg"]."_einkaeufe_mitzahlung WHERE einkauf = '".$_POST["einkaufid"]."' AND user = '".$_SESSION["wg_userid"]."'")) == 0) {
			qry("INSERT INTO wg_".$_SESSION["wg_wg"]."_einkaeufe_mitzahlung (einkauf, user, anz) VALUES ('".$_POST["einkaufid"]."','".$_SESSION["wg_userid"]."','".escape($_POST["anz"])."')");
	} else {
			qry("UPDATE wg_".$_SESSION["wg_wg"]."_einkaeufe_mitzahlung SET anz = anz + '".escape($_POST["anz"])."' WHERE einkauf = '".$_POST["einkaufid"]."' AND user = '".$_SESSION["wg_userid"]."'");
	}
	echo "Änderung erfolgreich!<br />
	<a href=\"../index.php#kuehlschrank\">» weiter »</a>
	<script language=\"javascript\">
	<!--
	window.location.href=\"../index.php#kuehlschrank\";
	// -->
	</script>";
}

$sql = qry("SELECT id, datum, ware, kaeufer, anz FROM wg_".$_SESSION["wg_wg"]."_einkaeufe WHERE anz > 1 ORDER BY datum DESC");

echo "<div style='width: 600px;'>Diese Tabelle stellt <b>keine</b> vollständige Übersicht aller noch vorhandener Gemeinschaftsgüter dar. 
Es werden lediglich die Waren angezeigt, deren Anzahl mit einem Wert > 1 eingetragen wurde und die noch nicht 
vollständig verbaucht wurden. <br /><br />";

if (mysqli_num_rows($sql) > 0)
{
	echo "<table cellpadding=\"5\" border=\"1\" style=\"border: 1px solid black; border-collapse: collapse;\">";
	
	echo "<tr style=\"background-color: #848181;\">
				<td>Datum</td>
				<td>Käufer</td>
				<td>Ware</td>
				<td>noch vorhanden</td>
				<td>verbraucht</td>
				</tr>";
	while($row = mysqli_fetch_assoc($sql))
	{
		$sql2 = qry("SELECT SUM(anz) AS anz FROM wg_".$_SESSION["wg_wg"]."_einkaeufe_mitzahlung WHERE einkauf = '".$row["id"]."'");
		$row2 = mysqli_fetch_assoc($sql2);
		if ($row2["anz"] < $row["anz"]) {
			echo "<tr><td>".date("d.m.Y",$row["datum"])."</td>
								<td>".getUserName($row["kaeufer"])."</td>
								<td>".$row["ware"]."</td>
								<td>".($row["anz"] - $row2["anz"])."</td>
								<td><form method=\"post\" action=\"ajax/kuehlschrank.php\">
								<input type=\"hidden\" name=\"einkaufid\" value=\"".$row["id"]."\" />
								
								<select name=\"anz\" onchange=\"this.form.submit();\">";
			for($i=0;$i<=($row["anz"] - $row2["anz"]);$i++)
			{
				echo "<option value=\"".$i."\">".$i."</option>";
			}	
			echo "</select></form></td>
			</tr>";
		}
	}

	echo "</table><br />";

}
else
{
	echo "Keine Daten vorhanden.";
}
echo "</div>";
?>