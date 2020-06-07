<?php
session_start();
include("../mysql.php");

// RSS Feed
if ($_GET["rss"]) {

	include("include/feedcreator.class.php");

	$url = str_replace("ajax/einkaufsliste.php","index.php","http://".$_SERVER['SERVER_NAME'].$_SERVER['PHP_SELF']);

	$rss = new UniversalFeedCreator();
	$rss->useCached();
	$rss->title = "Einkaufsliste";
	$rss->description = "Die aktuelle WG Einkaufsliste";
	$rss->link = $url;
	$rss->syndicationURL = $url;


	$sql = qry("SELECT id, datum, ware, user FROM wg_".$_SESSION["wg_wg"]."_einkaufsliste WHERE privat = '0' OR (privat = '1' && user = '".$_GET["uid"]."') ORDER BY datum DESC");

	if (mysqli_num_rows($sql) > 0)
	{
		while($row = mysqli_fetch_assoc($sql))
		{
			//echo date("d.m.Y",$row["datum"])." ".$row["ware"]." ".getUserName($row["user"])."\n";
			
			// durchschnittspreis berechnen
			$sql2 = qry("SELECT AVG(preis/gewicht) AS av FROM wg_".$_SESSION["wg_wg"]."_einkaeufe WHERE ware = '".$row["ware"]."' AND gewicht != '0'");
			$row2 = mysqli_fetch_assoc($sql2);
			
			if ($row2["av"] == 0) {
				$av = "";
			} else {
				$av = number_format($row2["av"],2,',','.')." EUR/kg";
			}
			
			$item = new FeedItem();
			$item->title = utf8_decode($row["ware"]);
			$item->link = $url;
			$item->description = $av;
			$item->author = getUserName($row["user"]);
			$item->date = date("U",$row["datum"]);
			
			$rss->addItem($item);
		}
		//Valid parameters are RSS0.91, RSS1.0, RSS2.0, PIE0.1 (deprecated),
		// MBOX, OPML, ATOM, ATOM1.0, ATOM0.3, HTML, JS
		
		$rss->outputFeed($_GET["rss"]); 
	}
}
else // kein RSS
{
	if ($_SESSION["wg_userid"] == 0) { loginfirst(); }

	echo "<div style=\"font-size: 0.8em; float: right; width: 250px; padding: 5px;\"><b>Hinweis</b>: RSS Feeds sind öffentlich zugänglich und enthalten auch private Einträge!</div>
	<fieldset style=\"border: 1px solid black; width: 370px; padding-top: 20px;\"><legend>Einkauf hinzufügen</legend>
	<form method=\"post\" action=\"einkaufsliste.php\" onsubmit=\"document.getElementById('submitware').value=document.getElementById('warenfeld').value;\">
	Ware: <div id=\"vorschlaege\" style=\"display: none; position: absolute; background-color: white; overflow: auto; border: 1px solid black; margin-left: 46px; min-width: 127px; max-width: 500px; max-height: 100px;\"></div><input id=\"warenfeld\" type=\"text\" name=\"".time()."\" size=\"16\" onkeyup=\"get_content('vorschlaege','ajax/warenvorschlag.php?ware='+this.value);\" onfocus=\"document.getElementById('vorschlaege').style.display='block';\" onblur=\"document.getElementById('vorschlaege').style.display='none';\" /> 
	<input type=\"checkbox\" name=\"privat\" value=\"1\" /> Privat <input type=\"submit\" style=\"float:right;\" value=\"hinzufügen\" />
	<input type=\"hidden\" id=\"submitware\" name=\"ware\" />
	</form></fieldset><br />";


	$sql = qry("SELECT id, datum, ware, user, privat FROM wg_".$_SESSION["wg_wg"]."_einkaufsliste WHERE privat = '0' OR (privat = '1' && user = '".$_SESSION["wg_userid"]."') ORDER BY datum DESC");

	if (mysqli_num_rows($sql) > 0)
	{
		echo "<table cellpadding=\"5\" border=\"1\" style=\"border: 1px solid black; border-collapse: collapse;\">";
		
		echo "<tr style=\"background-color: #848181;\">
					<td>Datum</td>
					<td>Ware</td>
					<td>&#216; Preis/kg</td>
					<td>eingetragen von</td>
					<td>eingekauft (Anzahl, Preis, Gewicht)</td>
					<td></td></tr>";
		while($row = mysqli_fetch_assoc($sql))
		{
			// durchschnittspreis berechnen
			$sql2 = qry("SELECT AVG(preis/gewicht) AS av FROM wg_".$_SESSION["wg_wg"]."_einkaeufe WHERE ware = '".$row["ware"]."' AND gewicht != '0'");
			$row2 = mysqli_fetch_assoc($sql2);
			
			if ($row2["av"] == 0) {
				$av = "k.A.";
			} else {
				$av = number_format($row2["av"],2,',','.')."€";
			}
		
			if ($row["privat"] == 0) {
				echo "<tr>";
			} else {
				echo "<tr style=\"background-color: #adacac;\">";
			}
		
			echo "<td>".date("d.m.Y",$row["datum"])."</td>
								<td>".$row["ware"]."</td>
								<td>".$av."</td>
								<td>".getUserName($row["user"])."</td>";
								
			if ($row["privat"] == 0) {
				echo "<td><form method=\"post\" action=\"einkaeufe.php?liste=1\">
					<input type=\"hidden\" name=\"kaeufer\" value=\"".$_SESSION["wg_userid"]."\" />
					<input type=\"hidden\" name=\"datum\" value=\"".date("d.m.Y")."\" />
					<input type=\"hidden\" name=\"ware\" value=\"".$row["ware"]."\" />
					<input type=\"text\" size=\"1\" name=\"anzahl\" value=\"1\" />x
					<input type=\"text\" size=\"3\" name=\"preis\" />€ 
					<input type=\"text\" size=\"1\" name=\"gewicht\" />kg 
					<input type=\"submit\" value=\"gekauft\" />
					<input type=\"hidden\" name=\"delid\" value=\"".$row["id"]."\" />
					</form></td>";
			} else {
				echo "<td></td>";
			}
							

			echo "<td><a href=\"einkaufsliste.php?del=".$row["id"]."\" onClick=\"javascript:return(confirm('Wirklich unwiderruflich löschen?'))\" class=\"menue\"><img border=\"0\" src=\"images/delete.png\" alt=\"X\" /></a>";
			echo "</td></tr>";
		}
		echo "</table><br />";
	}
	else
	{
		echo "Keine Daten vorhanden.";
	}


	echo "<div style=\"font-size: 0.8em; width: 100%; text-align: right;\">
	<b>Feeds</b>: <a class=\"menue\" href=\"ajax/einkaufsliste.php?rss=RSS2.0&uid=".$_SESSION["wg_userid"]."\">RSS 2.0</a> | 
	<a class=\"menue\" href=\"ajax/einkaufsliste.php?rss=RSS1.0&uid=".$_SESSION["wg_userid"]."\">RSS 1.0</a> | 
	<a class=\"menue\" href=\"ajax/einkaufsliste.php?rss=RSS0.91&uid=".$_SESSION["wg_userid"]."\">RSS 0.91</a> | 
	<a class=\"menue\" href=\"ajax/einkaufsliste.php?rss=ATOM&uid=".$_SESSION["wg_userid"]."\">ATOM</a> | 
	<a class=\"menue\" href=\"ajax/einkaufsliste.php?rss=OPML&uid=".$_SESSION["wg_userid"]."\">OPML</a>
	</div>";

}

?>