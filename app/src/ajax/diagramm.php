<?php

if (is_numeric($_GET["legend"]))
{
	echo "<u>Legende:</u><br />
				<div style=\"margin-right: 5px; margin-top: 5px; float: left; height: 10px; width: 10px; background-color: black; border: 0px;\"></div>Ausgaben pro Tag<br />
				<div style=\"margin-right: 5px; margin-top: 5px; float: left; height: 10px; width: 10px; background-color: red; border: 0px;\"></div>Summe aller Ausgaben<br />
				<div style=\"margin-right: 5px; margin-top: 5px; float: left; height: 10px; width: 10px; background-color: green; border: 0px;\"></div>Mein Guthaben<br />
				<div style=\"margin-right: 5px; margin-top: 5px; float: left; height: 10px; width: 10px; background-color: blue; border: 0px;\"></div>Meine Käufe<br />
				<div style=\"margin-right: 5px; margin-top: 5px; float: left; height: 10px; width: 10px; background-color: yellow; border: 0px;\"></div>Mein Verbrauch<br />";
}
else
{
	session_start();
	include("../mysql.php");
	if ($_SESSION["wg_userid"] == 0) { die("Bitte zuerst einloggen."); }
	
	$sql = qry("SELECT datum FROM wg_".$_SESSION["wg_wg"]."_einkaeufe ORDER BY datum ASC LIMIT 1");
	$row = mysqli_fetch_assoc($sql);
	$min = $row["datum"];
	
	if ($min > 0) {
		$tage = round((time() - $min) / 86400);
	} else {
		$tage = 7;
	}

	if ($tage > 20) {
		$default = 20;
	} else {
		$default = $tage;
	}
	
	$sql2 = qry("SELECT datum FROM wg_".$_SESSION["wg_wg"]."_einkaeufe GROUP BY datum");
	if (mysqli_num_rows($sql2) < 2) {
		echo "Nicht genügend Daten";
	} else {
		echo "<img id='diag' src=\"ajax/diagramm_line.php?zeitraum=".$default."\" />
		<div style='position: absolute; bottom: 0px; width: 99%;'>
		<table style='width: 100%; font-size: xx-small;'><tr>
		<td style='width: 40px;'></td>
		<td style='text-align: left;'>Zeitraum: <select style='font-size: xx-small;' onChange=\"document.getElementById('diag').src='ajax/diagramm_line.php?zeitraum='+this.value;\">";
		
		for($i = 20; $i < $tage; $i += 20) {
			if ($i == $default) { echo "<option SELECTED>".$i."</option>"; }
			else { echo "<option>".$i."</option>"; }
		}
		
		echo "<option>".$tage."</option>";

		echo "</select> Tage</td>
		<td style='text-align: right;'>&copy; 2006  <a class=\"menue\" href=\"http://makko.com.mx/gden.php\">Makko Solutions</a> <a href=\"http://www.gnu.org/licenses/gpl.html\" class=\"menue\">GNU General Public License</a></td>
		</tr></table>
		</div>";
	}
}
?>