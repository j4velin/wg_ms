<?php
session_start();
header("Content-Type: text/html; charset=UTF-8");  
include("mysql.php");
if ($_SESSION["wg_userid"] == 0) { loginfirst(); }

$eineWoche = 3600 * 24 * 7;

if ($_POST["datum"]) { // neu eintragen

	list($tag, $monat, $jahr) = explode(".", $_POST["datum"]);
	
	$date = mktime(0,0,0,$monat,$tag,$jahr);
	if ($date < (time() - ($eineWoche*4))) {
		die("Fehler: Der Einkauf liegt mehr als 4 Wochen zurück!");
	}
	
	if (!($_POST["anzahl"] > 0)) {
		die("Fehler: Anzahl muss > 0 sein!");
	}
	
	if (strlen($_POST["ware"]) < 3) { die("Warenbezeichnung ist zu kurz"); }

	if (is_numeric($_POST["gewicht"])) {
		$gewicht = str_replace(',','.',$_POST["gewicht"]); 
	} else {
		$gewicht = 0;
	}

	$ware = escape($_POST["ware"]);
	$kaeufer = escape($_POST["kaeufer"]);
	$preis = escape($_POST["preis"]);
	$anzahl = escape($_POST["anzahl"]);

	qry("INSERT INTO wg_".$_SESSION["wg_wg"]."_einkaeufe (datum, ware, kaeufer, preis, anz, gewicht) VALUE ('".$date."','".$ware."','".$kaeufer."','".str_replace(',','.',$preis)."','".$anzahl."','".$gewicht."')");
	
	$sql = qry("SELECT LAST_INSERT_ID() AS id");
	$row = mysqli_fetch_assoc($sql);
	$id = $row["id"];
	
	if ($_POST["anzahl"] == 1) {
		$sql = qry("SELECT id FROM wg_user WHERE active > 0 AND wg = '".$_SESSION["wg_wg"]."'");
		while($row = mysqli_fetch_assoc($sql))
		{
			qry("INSERT INTO wg_".$_SESSION["wg_wg"]."_einkaeufe_mitzahlung (einkauf, user) VALUE (".$id.",'".$row["id"]."')");
		}
		
		twitter(getUserName($_POST["kaeufer"])." hat ".$ware." eingekauft (".$preis." EUR)");
	} else { // Anzahl > 1
		twitter(getUserName($_POST["kaeufer"])." hat ".$anzahl."x ".$ware." eingekauft (".$preis." EUR)");
	}

}
else if (is_numeric($_GET["del"])) // löschen
{
	qry("DELETE FROM wg_".$_SESSION["wg_wg"]."_einkaeufe WHERE id = '".$_GET["del"]."' AND kaeufer = '".$_SESSION["wg_userid"]."'");
	$row = mysqli_fetch_assoc(qry("SELECT ROW_COUNT() AS anz"));
	if ($row["anz"] > 0)
	{
		qry("DELETE FROM wg_".$_SESSION["wg_wg"]."_einkaeufe_mitzahlung WHERE einkauf = '".$_GET["del"]."'");
	}
	else
	{
		die("Fehler: Du kannst nur deine eigenen Einkäufe löschen!");
	}
}
else // Mitzahlung ändern
{
	$sql = qry("SELECT id, datum, ware, kaeufer, preis, anz FROM wg_".$_SESSION["wg_wg"]."_einkaeufe WHERE ((anz = '1' AND datum >= '".(time()-$eineWoche*4)."') OR (anz > '1' AND verbraucht = '0')) ORDER BY datum DESC, id DESC");
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
			$anz = escape($_POST["einkauf_".$eid."_anz"]);
			$uid = escape($_POST["einkauf_".$eid."_uid"]);
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
if ($_GET["liste"])
{
		qry("DELETE FROM wg_".$_SESSION["wg_wg"]."_einkaufsliste WHERE id = '".$_POST["delid"]."'");
		echo "Änderung erfolgreich!<br />
		<a href=\"index.php#einkaufsliste\">» weiter »</a>
		<script language=\"javascript\">
		<!--
		window.location.href=\"index.php#einkaufsliste\";
		// -->
		</script>";
}
else
{
		echo "Änderung erfolgreich!<br />
		<a href=\"index.php#einkaeufe\">» weiter »</a>
		<script language=\"javascript\">
		<!--
		window.location.href=\"index.php#einkaeufe\";
		// -->
		</script>";
}
?>
