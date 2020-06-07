<?php
session_start();
include("../mysql.php");
setlocale(LC_ALL, 'de_DE');
date_default_timezone_set('Europe/Berlin');

if ($_SESSION["wg_userid"] == 0) { loginfirst(); }

echo "Das hier dient nur der Information und fließt in keinerlei Berechnungen mit ein!<br /><br />
<fieldset style=\"border: 1px solid black; padding-top: 20px;\"><legend>Regelmäßige Kosten hinzufügen</legend>
<form method=\"post\" action=\"monatlich.php\">
<table style=\"border: 0px;\"><tr>
<td>Bezeichnung: <input type=\"text\" name=\"bezeichnung\" size=\"20\" /></td>
<td>Kosten: <input type=\"text\" size=\"3\" name=\"kosten\" /> €</td>
<td>Turnus: <select name=\"turnus\">
<option value=\"1\">monatlich</option>
<option value=\"3\">pro Quartal</option>
<option value=\"6\">halbjährlich</option>
<option value=\"12\">jährlich</option>
</select></td><td><input type='submit' value='hinzufügen' \></td></tr></table></form></fieldset><br />";

echo "<div style=\"max-height: 400px; overflow: auto;\">
<table cellpadding=\"5\" border=\"1\" style=\"border: 1px solid black; border-collapse: collapse;\">";

echo "<tr style=\"background-color: #848181;\">
				<td>Bezeichnung</td>
				<td>Kosten</td>
				<td>Turnus</td><td></td>";
echo "</tr>";
$sql2 = qry("SELECT id, bezeichnung, kosten, turnus FROM wg_".$_SESSION["wg_wg"]."_monatlich ORDER BY bezeichnung ASC");
while($row = mysqli_fetch_assoc($sql2))
{
	switch ($row["turnus"]) {
		case 1:
			$turnus = "monatlich";
			break;
		case 3:
			$turnus = "pro Quartal";
			break;
		case 6:
			$turnus = "halbjährlich";
			break;
		case 12:
			$turnus = "järhlich";
			break;
	}
	echo "<tr><td>".$row["bezeichnung"]."</td><td>".number_format($row["kosten"]/100,2)." €</td><td>".$turnus."</td><td>";
	echo "<a href=\"monatlich.php?del=".$row["id"]."\" onClick=\"javascript:return(confirm('Wirklich unwiderruflich löschen?'))\" class=\"menue\"><img border=\"0\" src=\"images/delete.png\" alt=\"X\" /></a>";
	echo "</td></tr>";
}

echo "</table></div><br />";

?>