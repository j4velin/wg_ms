<?php

session_start();
include("../mysql.php");
if ($_SESSION["wg_userid"] == 0) { loginfirst(); }

$sql = qry("SELECT name FROM wg_user WHERE id = '".$_SESSION["wg_userid"]."' LIMIT 1");
$row = mysqli_fetch_assoc($sql);

echo "<form method=\"post\" action=\"account.php\"><table>
<tr><td>Name: </td><td>".$row["name"]." (ID: ".$_SESSION["wg_userid"].")</td></tr>
<tr><td>Neue Passwort:</td><td><input type=\"password\" name=\"pwa\" /></td></tr>
<tr><td>Passwort wiederholen:</td><td><input type=\"password\" name=\"pwb\" /></td></tr>
</table>
<br />
<input type=\"submit\" value=\"Änderungen speichern\" />
</form>";

?>