<?php 
session_start();

if ($_SESSION["wg_userid"] == 0) { die(""); }


header("Content-Type: text/html; charset=UTF-8");  
include("../mysql.php");

if (isset($_POST["shoutText"])) {
	qry("INSERT INTO wg_".$_SESSION["wg_wg"]."_shouts (userid, text, stamp) VALUES ('".$_SESSION["wg_userid"]."', '".htmlentities($_POST["shoutText"])."', '".time()."')");
	twitter(utf8_decode(strip_tags(getUserName($_SESSION["wg_userid"]).": ".$_POST["shoutText"])));
}

echo "<table>";
$sql = qry("SELECT userid, text FROM wg_".$_SESSION["wg_wg"]."_shouts ORDER BY stamp DESC LIMIT 20");
while($row = mysqli_fetch_assoc($sql)) {
	echo "<tr><td>".getUserName($row["userid"]).":</td><td>".html_entity_decode($row["text"])."</td></tr>";
}
echo "</table>";

?>