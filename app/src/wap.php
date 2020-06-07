<?php

header("Content-Type: text/vnd.wap.wml;charset=iso-8859-1");
echo "<?xml version='1.0'?>
<!DOCTYPE wml PUBLIC '-//WAPFORUM//DTD WML 1.1//EN' 'http://www.wapforum.org/DTD/wml_1.1.xml'>
<wml><card>";

include("mysql.php");

$sql = qry("SELECT id, datum, ware, user FROM wg_".$_SESSION["wg_wg"]."_einkaufsliste ORDER BY datum DESC");

if (mysqli_num_rows($sql) > 0)
{

	while($row = mysqli_fetch_assoc($sql))
	{
		echo "<p>".$row["ware"]." (".getUserName($row["user"])."@".date("d.m.Y",$row["datum"])."</p>";
	}

}
else
{
	echo "<p>Keine Daten vorhanden.</p>";
}
?>
</card></wml>