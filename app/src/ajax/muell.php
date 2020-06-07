<?php

setlocale(LC_ALL, 'de_DE');
date_default_timezone_set('Europe/Berlin');

$BIO = "#33cc33";
$REST = "#ffcc33";
$WERT = "#0099ff";

session_start();
include("../mysql.php");
if ($_SESSION["wg_userid"] == 0) { loginfirst(); }

if (is_numeric($_GET["del"])) {
	qry("DELETE FROM wg_".$_SESSION["wg_wg"]."_muell WHERE datum = '".$_GET["del"]."' AND user = '".$_SESSION["wg_userid"]."' AND art = '".htmlentities($_GET["art"])."'");
	echo "Änderung erfolgreich!<br />
		<a href=\"../index.php#kuehlschrank\">weiter/a>
		<script language=\"javascript\">
		<!--
		window.location.href=\"../index.php#muell\";
		// -->
		</script>";
}

if ($_POST["datum"]) {

	list($tag, $monat, $jahr) = explode(".", $_POST["datum"]);

	$time = mktime(0,0,0,$monat,$tag,$jahr);
	if (time() - $time > 604800) {
		die("Eintragungsfrist verstrichen!");
	}
	else
	{
		$arten = escape($_POST["art"]);
		for($i = 0; $i < sizeof($arten); $i++) {
			qry("INSERT INTO wg_".$_SESSION["wg_wg"]."_muell (user, datum, art) VALUES ('".$_SESSION["wg_userid"]."','".$time."','".$arten[$i]."')");
		}

		echo "Änderung erfolgreich!<br />
		<a href=\"../index.php#kuehlschrank\">weiter/a>
		<script language=\"javascript\">
		<!--
		window.location.href=\"../index.php#muell\";
		// -->
		</script>";
	}
}

echo "<table border='0'><tr><td><table cellpadding=\"4\" cellspacing=\"1\"  style=\"width: 98%; border: 1px solid black;\">
          <tr style=\"background-color: #666666;\">
  					<td>Mo</td>
  					<td>Di</td>
  					<td>Mi</td>
  					<td>Do</td>
  					<td>Fr</td>
  					<td>Sa</td>
  					<td>So</td>
					</tr>";
					
$monat = date("n",time());
$jahr = date("Y",time());
$timestamp = mktime(0,0,0,$monat,1,$jahr);
while(date("w",$timestamp) != 1) // damit es mit montag anfängt, eventuell noch vom vormonat
{
	$timestamp -= 86400;
}
$endtimestamp = mktime(0,0,0,$monat+1,0,$jahr); // letzter tag des monats

while($timestamp < $endtimestamp)
{
	echo "<tr>";  
	for($i=0;$i<7;$i++)
	{
		$monat2 = date("n",$timestamp);
		echo "<td width=\"14%\" valign=\"top\" onclick=\"javascript:document.getElementById('datum').value='".date("d.m.Y",$timestamp)."';\" style=\"height: 5px; text-align: center;";
		if ($monat2 == $monat)
		{
			if (date("d.m.Y",$timestamp) == date("d.m.Y",time()))
			{
				echo " border: 3px solid #FF6633;";
			}
			echo "\">";

			$sql = qry("SELECT art FROM wg_".$_SESSION["wg_wg"]."_muell WHERE datum = '".$timestamp."'");
			if (mysqli_num_rows($sql) > 0) {
				echo "<div style='width: 100%; background-color: #FF6600; height: 100%;'>".date("j",$timestamp)."</div>";
			} else {
				echo date("j",$timestamp);
			}
			echo "</td>";
		}
		else
		{
			echo "background-color: #999999;\">".
			date("j",$timestamp)."</td>";
		}


		$timestamp += 86400;
	}
	echo "</tr>";
}
         
echo "</table></td><td style='padding-left: 50px; padding-right: 50px;'>";

?>
<form method="post" action="ajax/muell.php">
<input type="checkbox" name="art[]" value="bio" style="background-color: <?php echo $BIO; ?>;" /> <b>Bio</b><br />
<input type="checkbox" name="art[]" value="rest"  style="background-color: <?php echo $REST; ?>;" /> <b>Restmüll</b><br />
<input type="checkbox" name="art[]" value="wert" style="background-color: <?php echo $WERT; ?>;" /> <b>Wertstoff</b><br /><br />
<input type="text" name="datum" id="datum" size="12" value="<?php echo date("d.m.Y",time()); ?>" /> <input type="submit" value="geleert" />
</form>
</td><td>

<table cellpadding="5" border="1" style="border: 1px solid black; border-collapse: collapse;">
<tr><td></td><td><b>Bio</b></td><td><b>Restmüll</b></td><td><b>Wertstoff</b></td></tr>
<?php

$sql = qry("SELECT id, name FROM wg_user WHERE wg = '".$_SESSION["wg_wg"]."' ORDER BY name ASC");
while($row = mysqli_fetch_assoc($sql)) {
	$bio = mysqli_num_rows(qry("SELECT user FROM wg_".$_SESSION["wg_wg"]."_muell WHERE user = '".$row["id"]."' AND art = 'bio'"));
	$rest = mysqli_num_rows(qry("SELECT user FROM wg_".$_SESSION["wg_wg"]."_muell WHERE user = '".$row["id"]."' AND art = 'rest'"));
	$wert = mysqli_num_rows(qry("SELECT user FROM wg_".$_SESSION["wg_wg"]."_muell WHERE user = '".$row["id"]."' AND art = 'wert'"));
	
	echo "<tr><td>".$row["name"]."</td><td>".$bio."</td><td>".$rest."</td><td>".$wert."</td></tr>";
}
?>
</table>


</td></tr></table><br /><br />
<table border="0" width="100%"><tr><td>
<table cellpadding="5" border="1" style="border: 1px solid black; border-collapse: collapse; width: 100%;">
<?php
$lastdate = null;
$lastuser = null;
$sql = qry("SELECT * FROM wg_".$_SESSION["wg_wg"]."_muell ORDER BY datum DESC");
while($row = mysqli_fetch_assoc($sql)) {

echo "<tr>";

if ($lastdate != $row["datum"]) {
	echo "<td rowspan='".mysqli_num_rows(qry("SELECT datum FROM wg_".$_SESSION["wg_wg"]."_muell WHERE datum = '".$row["datum"]."'"))."'>".date("d.m.Y",$row["datum"])."</td>";
	$lastdate = $row["datum"];
	$lastuser = null;
}

if ($row["art"] == "bio") {
	echo "<td style='background-color: ".$BIO.";'>Bio";
} else if ($row["art"] == "rest") {
	echo "<td style='background-color: ".$REST.";'>Restmüll";
} else {
	echo "<td style='background-color: ".$WERT.";'>Wertstoff";
}

if ($row["user"] == $_SESSION["wg_userid"]) {
	echo "<a href=\"ajax/muell.php?del=".$row["datum"]."&art=".$row["art"]."\"><img src=\"images/delete.png\" alt=\"[x]\" style=\"float:right;\" /></a>";
}

echo "</td>";

	
	
if ($lastuser != $row["user"]) {
	echo "<td rowspan='".mysqli_num_rows(qry("SELECT user FROM wg_".$_SESSION["wg_wg"]."_muell WHERE user = '".$row["user"]."' AND datum = '".$row["datum"]."'"))."'>".getUserName($row["user"])."</td>";
	$lastuser = $row["user"];
}
	
echo "</tr>";

}
?>
</table></td><td style="padding-left: 50px;" valign="top">
Letzte Leerungen:
<?php

$sql = qry("SELECT datum FROM wg_".$_SESSION["wg_wg"]."_muell WHERE art = 'bio' ORDER BY datum DESC LIMIT 1");
$row = mysqli_fetch_assoc($sql);
$diffBio = floor((time() - $row["datum"]) / 86400);

if ($diffBio >= 7) {  $diffBio = "<font style='color: red;'><b>".$diffBio."</b></font>"; }

$sql = qry("SELECT datum FROM wg_".$_SESSION["wg_wg"]."_muell WHERE art = 'rest' ORDER BY datum DESC LIMIT 1");
$row = mysqli_fetch_assoc($sql);
$diffRest = floor((time() - $row["datum"]) / 86400);

if ($diffRest >= 7) {  $diffRest = "<font style='color: red;'><b>".$diffRest."</b></font>"; }

$sql = qry("SELECT datum FROM wg_".$_SESSION["wg_wg"]."_muell WHERE art = 'wert' ORDER BY datum DESC LIMIT 1");
$row = mysqli_fetch_assoc($sql);
$diffWert = floor((time() - $row["datum"]) / 86400);

if ($diffWert >= 7) {  $diffWert = "<font style='color: red;'><b>".$diffWert."</b></font>"; }

echo "<ul>
<li><b>Bio:</b> vor ".$diffBio." Tagen</li>
<li><b>Restmüll:</b> vor ".$diffRest." Tagen</li>
<li><b>Wertstoff:</b> vor ".$diffWert." Tagen</li>
</ul>";
?>

</td></tr></table>