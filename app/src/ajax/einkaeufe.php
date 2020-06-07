<?php
session_start();
include("../mysql.php");
setlocale(LC_ALL, 'de_DE');
date_default_timezone_set('Europe/Berlin');

if ($_SESSION["wg_userid"] == 0) { loginfirst(); }

echo "<fieldset style=\"border: 1px solid black; padding-top: 20px;\"><legend>Einkauf hinzufügen</legend>
<form method=\"post\" action=\"einkaeufe.php\" onsubmit=\"document.getElementById('submitware').value=document.getElementById('warenfeld').value;\">
<table style=\"border: 0px; width: 70%;\"><tr>
 
<td>Ware: <div id=\"vorschlaege\" style=\"display: none; position: absolute; background-color: white; overflow: auto; border: 1px solid black; margin-left: 46px; min-width: 127px; max-width: 500px; max-height: 300px;\"></div><input id=\"warenfeld\" type=\"text\" name=\"".time()."\" size=\"20\" onkeyup=\"get_content('vorschlaege','ajax/warenvorschlag.php?ware='+this.value);\" onfocus=\"document.getElementById('vorschlaege').style.display='block';\" onblur=\"document.getElementById('vorschlaege').style.display='none';\" /><input type=\"hidden\" id=\"submitware\" name=\"ware\" /></td>
<td>Preis: <input type=\"text\" size=\"3\" name=\"preis\" /> €</td>
<td><input type=\"submit\" value=\"hinzufügen\" /> <a class=\"menue\" href=\"javascript: void(0);\" onclick=\"javascript:document.getElementById('exEinkauf').style.display='block';\" style=\"font-size: 12px;\">[mehr Optionen]</a></td></tr></table>
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
</div></form></fieldset><br />";


echo "<form name=\"einkaeufe\" method=\"post\" action=\"einkaeufe.php\">";
echo "<div style=\"max-height: 400px; overflow: auto; width: 1160px;\">
<table cellpadding=\"5\" border=\"1\" style=\"border: 1px solid black; border-collapse: collapse; width: 100%;\">";

echo "<tr style=\"background-color: #848181;\">
				<td>Datum</td>
				<td>Ware&nbsp;(Anzahl)&nbsp;[Gewicht]</td>
				<td>Preis (&#216; pro kg, pro Stück)</td>
				<td>pro<br />Person/Stück</td>
				<td>Käufer</td>";

$sql2 = qry("SELECT name FROM wg_user WHERE wg = '".$_SESSION["wg_wg"]."' AND active > 0 ORDER BY name ASC");
while($row = mysqli_fetch_assoc($sql2))
{
	echo "<td>".$row["name"]."</td>";
}
echo "<td></td>";
echo "</tr>";
$zeit = 3600 * 24 * 7 * 4; // vier wochen
$sql = qry("SELECT id, datum, ware, kaeufer, preis, anz, gewicht FROM wg_".$_SESSION["wg_wg"]."_einkaeufe WHERE (datum >= '".(time()-$zeit)."' OR anz > '1' AND verbraucht = '0') ORDER BY datum DESC, id DESC");
if (mysqli_num_rows($sql) == 0) {
	$spalten = 6 + mysqli_num_rows($sql2);
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
			$preisproperson = "<div align=\"right\">".number_format($row["preis"]/$row["anz"],2,',','.')."&nbsp;€</div>";
		}
		else 
		{
			$anz = "";			
			if (mysqli_num_rows($sql2) > 0) {
				$preisproperson = number_format($row["preis"]/mysqli_num_rows($sql2),2,',','.')."&nbsp;€";
			}
			else { $preisproperson = "<b>&#8734;</b>"; }
		}
		
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
		if (is_numeric($row3["av"])) { // avg preis ex
			$avg = $row3["av"] * $row["anz"]; // preis pro einkauf
			if ($row["anz"] == 1) { // kein stück angegeben
				$img = "fragezeichen";
				$avgstr2 = "<img src=\"images/".$img.".png\" title=\"".number_format($row3["av"],2,',','.')."€/Stück\" />";
			}
			else {
				if (round($avg,2) > round($row["preis"],2)) { // billiger gekauft
					$img = "green";
				} 
				else if (round($avg,2) < round($row["preis"],2)) { // teurer gekauft
					$img = "red";
				} 
				else { // gleich
					$img = "blue";
				}
				$avgstr2 = "<img src=\"images/".$img.".png\" title=\"".number_format($row3["av"],2,',','.')."€/Stück => ".number_format($row3["av"]*$row["anz"],2,',','.')."€/".$row["anz"]."\" />";
			}
		} else { // kein avg 
			$avgstr2 = "";
		}
		
		if ($avgstr1 != "") {
			if ($avgstr2 != "") {
				$avgstr = "&nbsp;(".$avgstr1.",".$avgstr2.")";
			} else {
				$avgstr = "&nbsp;(".$avgstr1.")";
			}
		} else if ($avgstr2 != "") {
			$avgstr = "&nbsp;(".$avgstr2.")";
		} else {
			$avgstr = "";
		}
		
		
		// erste Spalte
		if ($row["gewicht"] > 0)
		{
			$gewicht = " [".str_replace(".",",",$row["gewicht"])."kg]";
		} else {
			$gewicht = "";
		}

	
		echo "<tr><td>".date("d.m.Y",$row["datum"])."</td>
							<td>".$row["ware"].$anz.$gewicht."</td>
							<td>".number_format($row["preis"],2,',','.')."&nbsp;€".$avgstr."</td>
							<td>".$preisproperson."</td>
							<td>".getUserName($row["kaeufer"])."</td>";
	
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
			
		echo "<td>";
		if ($row["kaeufer"] == $_SESSION["wg_userid"])
		{
			echo "<a href=\"einkaeufe.php?del=".$row["id"]."\" onClick=\"javascript:return(confirm('Wirklich unwiderruflich löschen?'))\" class=\"menue\"><img border=\"0\" src=\"images/delete.png\" alt=\"X\" /></a>";
		}
		echo "</td>";
		echo "</tr>";
	
		unset($mitzahler);
	}
}

echo "</table></div><br />";
echo "<table style='border: 0px; width: 100%;'><tr>
<td style='text-align: left;'>
<a href=\"javascript:document.einkaeufe.submit();\" class=\"menue\"><img border=\"0\" src=\"images/save.png\" alt=\"[save]\" /> Änderungen speichern</a>
</td><td style='text-align: right;'>
<a href=\"javascript:void(0)\"; onclick=\"window.open('oldereinkaeufe.php','einkaeufe','location=no,toolbar=no,menubar=no,status=no,scrollbars=yes,resizable=yes,width=800,height=700');\" class=\"menue\">ältere Einkäufe</a> 
| <a href=\"javascript:void(0)\"; onclick=\"window.open('preise.php','preise','location=no,toolbar=no,menubar=no,status=no,scrollbars=yes,resizable=yes,width=700,height=600');\" class=\"menue\">&#216;-Preise</a>
<!--| <a class=\"menue\" href=\"http://twitter.com/WG_MS\">Twitter</a>//-->
| <a href=\"javascript:void(0)\"; onclick=\"window.open('fehler.php','einkaeufe','location=no,toolbar=no,menubar=no,status=no,scrollbars=yes,resizable=yes,width=900,height=300');\" class=\"menue\">Fehler</a>
</td></tr></table>
</form>";

?>