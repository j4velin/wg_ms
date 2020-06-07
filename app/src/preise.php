<?php
session_start();
include("mysql.php");
if ($_SESSION["wg_userid"] == 0) { loginfirst(); }
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="de" lang="de">
<head>
	<title>Durchschnitsspreise</title>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />

	<meta http-equiv="pragma" content="no-cache" />
	<meta http-equiv="cache-control" content="no-cache" />

	<link rel="shortcut icon" href="favicon.ico" type="image/x-icon" />
	<link rel="stylesheet" type="text/css" href="style.css" />
</head>
<body>
<table cellpadding="5" border="1" style="width: 98%; font-family: Arial; font-size: 0.8em; border: 1px solid black; border-collapse: collapse;">
<tr style="background-color: #848181;">
				<td>Ware</td>
				<td>&#216;-Preis</td>
				<td>billigster Preis (kg)</td>
				<td>billigster Preis (Stück)</td>
</tr>

<?php

		// durchschnittspreis berechnen
		$sql = qry("SELECT ware, SUM(gewicht) AS gewicht, AVG(preis/gewicht) AS av1, AVG(preis/anz) AS av2 FROM wg_".$_SESSION["wg_wg"]."_einkaeufe WHERE gewicht != '0' OR anz > 1 GROUP BY ware ORDER BY ware ASC");
		while($row = mysqli_fetch_assoc($sql))
		{
		
			echo "<tr><td>".$row["ware"]."</td><td>";
				$space = "";
			if ($row["gewicht"] > 0) {
				echo number_format($row["av1"],2,',','.')."&nbsp; €/kg";
				$space = ", ";
			} 
			if ($row["anz"] > 1) {
				echo $space.number_format($row["av2"],2,',','.')."&nbsp; €/Stück";
			}
			
			echo "</td>";
			
			$sql2 = qry("SELECT preis/gewicht AS av, kaeufer FROM wg_".$_SESSION["wg_wg"]."_einkaeufe WHERE gewicht != '0' AND ware = '".$row["ware"]."' ORDER BY preis/gewicht ASC LIMIT 1");
			$row2 = mysqli_fetch_assoc($sql2);
			
				if (mysqli_num_rows($sql2) > 0) {
					echo "<td><b>".number_format($row2["av"],2,',','.')." €</b> (".getUserName($row2["kaeufer"]).")</td>";
				} else {
					echo "<td></td>";
				}
			
			$sql2 = qry("SELECT preis/anz AS av, kaeufer FROM wg_".$_SESSION["wg_wg"]."_einkaeufe WHERE anz > '1' AND ware = '".$row["ware"]."' ORDER BY preis/anz ASC LIMIT 1");
			$row2 = mysqli_fetch_assoc($sql2);
			
				if (mysqli_num_rows($sql2) > 0) {
					echo "<td><b>".number_format($row2["av"],2,',','.')." €</b> (".getUserName($row2["kaeufer"]).")</td>";
				} else {
					echo "<td></td>";
				}	
				
				
			echo "</tr>";
		
		}


echo "</table></body></html>";

?>
