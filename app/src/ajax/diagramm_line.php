<?php
session_start();
include("../mysql.php");
if ($_SESSION["wg_userid"] == 0) { loginfirst(); }

require_once("GDGraph/gdgraph.php");

// GDGraph(width, height [, title [, red_bg [, green_bg [, blue_bg [, red_line [, green_line [, blue_line [red_font [, green_font [, blue_font [, legend [, legend_x [, legend_y [, legend_border [, transparent_background [, line_thickness]]]]]]]]]]]]]]]]); 
$gdg = new GDGraph(950,565,"",255,255,255,0,0,0,0,0,0,false,0,0,true,true,0);

$daten = array();
$ausgabenprotag = array();
$sumprotag = array();
$guthaben = array();
$schuldenarr = array();
$kaeufearr = array();
$summe = 0;
$prev = 0;
$kaeufe = 0;
$schulden = 0;
$ausgleich = 0;

$dreiWochen = 3600 * 24 * 7 * 3;
$sql = qry("SELECT id, datum, preis, kaeufer, anz FROM wg_".$_SESSION["wg_wg"]."_einkaeufe ORDER BY datum ASC");
while($row = mysqli_fetch_assoc($sql))
{

	$sql5 = qry("SELECT SUM(betrag) AS ausgleich FROM wg_".$_SESSION["wg_wg"]."_zahlungen WHERE absender = '".$_SESSION["wg_userid"]."' AND datum <= '".$row["datum"]."' AND datum > '".$prev."'");
	$row5 = mysqli_fetch_assoc($sql5);
	$ausgleich += $row5["ausgleich"];
	
	$sql5 = qry("SELECT SUM(betrag) AS ausgleich FROM wg_".$_SESSION["wg_wg"]."_zahlungen WHERE empfaenger = '".$_SESSION["wg_userid"]."' AND datum <= '".$row["datum"]."' AND datum > '".$prev."'");
	$row5 = mysqli_fetch_assoc($sql5);
	$ausgleich -= $row5["ausgleich"];

	
	if ($prev > 0)
	{
		while($prev+86400 < $row["datum"])
		{
				$prev += 86400;
				array_push($guthaben,round($kaeufe-$schulden+$ausgleich,2));
				array_push($schuldenarr,round($schulden,2));
				array_push($kaeufearr,round($kaeufe,2));
		}
	}
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
	

	
	if ($prev != $row["datum"])
	{
		array_push($guthaben,round($kaeufe-$schulden+$ausgleich,2));
		array_push($schuldenarr,round($schulden,2));
		array_push($kaeufearr,round($kaeufe,2));
	}
	else
	{
		$guthaben[count($guthaben)-1] = round($kaeufe-$schulden+$ausgleich,2);
		$schuldenarr[count($schuldenarr)-1] = round($schulden,2);
		$kaeufearr[count($kaeufearr)-1] = round($kaeufe,2);
	}
	
	$prev = $row["datum"];
}

$prev = 0;

$sql = qry("SELECT datum, SUM(preis) AS ausgaben FROM wg_".$_SESSION["wg_wg"]."_einkaeufe GROUP BY datum ORDER BY datum ASC");
while($row = mysqli_fetch_assoc($sql))
{
	if ($prev > 0)
	{
		while($prev+86400 < $row["datum"])
		{
				$prev += 86400;
				//array_push($daten,date("d.m.",$prev));
				array_push($daten,"");
				array_push($ausgabenprotag,0);
				array_push($sumprotag,round($summe,2));
		}
	}

	$prev = $row["datum"];
	//array_push($daten,date("d.m.",$row["datum"]));
	array_push($daten,"");
	array_push($ausgabenprotag,round($row["ausgaben"],2));
	$summe += $row["ausgaben"];
	array_push($sumprotag,round($summe,2));
}

	while($prev+86400 < time())
	{
			$prev += 86400;
			//array_push($daten,date("d.m.",$prev));
			array_push($daten,"");
			array_push($ausgabenprotag,0);
			array_push($sumprotag,round($summe,2));
			array_push($guthaben,round($kaeufe-$schulden,2));
			array_push($schuldenarr,round($schulden,2));
			array_push($kaeufearr,round($kaeufe,2));
	}

$laenge = min($_GET["zeitraum"],sizeof($ausgabenprotag));
$offset = sizeof($ausgabenprotag)-$laenge;

$arr = Array(
				'Ausgaben pro Tag' => array_slice($ausgabenprotag,$offset,$laenge),
				'Summe aller Ausgaben' => array_slice($sumprotag,$offset,$laenge),
				'Mein Guthaben' => array_slice($guthaben,$offset,$laenge),
				'Meine Käufe' => array_slice($kaeufearr,$offset,$laenge),
				'Mein Verbrauch' => array_slice($schuldenarr,$offset,$laenge)
			);


$colors = Array(
				'Ausgaben pro Tag' => Array(50,50,50),
				'Summe aller Ausgaben' => Array(250,100,100),
				'Mein Guthaben' => Array(100,250,100),
				'Meine Käufe' => Array(100,100,250),
				'Mein Verbrauch' => Array(250,250,100)
			);

$thicknesses = Array(
				'Ausgaben pro Tag' => 3,
				'Summe aller Ausgaben' => 3,
				'Mein Guthaben' => 3,
				'Meine Käufe' => 3,
				'Mein Verbrauch' => 3
			);

//line_graph(data [, colors [, x_labels [, x_title [, y_title [, paint_dots [,lines_thickness [, x_lower_value [, x_upper_value [, y_lower_value [, y_upper_value]]]]]]]]]]); 
$gdg->line_graph($arr, $colors, $daten, "Zeit", "EUR", false, $thicknesses);

?>