<?php
session_start();
include("../mysql.php");
if ($_SESSION["wg_userid"] == 0) { loginfirst(); }

$sql = qry("SELECT ware FROM wg_".$_SESSION["wg_wg"]."_einkaeufe WHERE ware LIKE '%".addslashes($_GET["ware"])."%' GROUP BY ware ORDER BY ware ASC");
while($row = mysqli_fetch_assoc($sql))
{
    echo "<div onmousedown=\"document.getElementById('warenfeld').value='".$row["ware"]."';\" onmouseout=\"this.style.backgroundColor='white';\" onmouseover=\"this.style.backgroundColor='orange';\">".str_replace(strtoupper($_GET["ware"]),"<b>".strtoupper($_GET["ware"])."</b>",str_replace(strtolower($_GET["ware"]),"<b>".strtolower($_GET["ware"])."</b>",$row["ware"]))."</div>";
}

?>