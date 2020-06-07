<?php
session_start();
include("../mysql.php");

if ($_SESSION["wg_userid"] != 1) { die("Dieses Menü ist nur über den Admin Account erreichbar"); }

// add
echo "<fieldset style=\"border: 1px solid black; padding-top: 20px;\"><legend>Account hinzufügen</legend>
<form method=\"post\" action=\"admin.php\">
<table style=\"border: 0px; width: 70%;\">
<tr><td>Name:</td><td><input type=\"text\" size=\"15\" name=\"accountname\" /></td></tr>
<tr><td>Passwort:</td><td><input type=\"text\" size=\"15\" name=\"accountpw\" /></td></tr>
<tr><td></td><td><input type=\"submit\" value=\"hinzufügen\" /></td></tr>
</table>
</form>
</fieldset>
<br />";

// edit
echo "<fieldset style=\"border: 1px solid black; padding-top: 20px;\"><legend>Account bearbeiten</legend>
<form method=\"post\" action=\"admin.php\">
<table style=\"border: 0px; width: 70%;\">
<tr><td>Account:</td><td><select name=\"accountid\">";

$sql = qry("SELECT id, name, active, konto FROM wg_user WHERE wg = '".$_SESSION["wg_wg"]."' && id > 1 ORDER BY active DESC, name ASC");
while($row = mysqli_fetch_assoc($sql)) {
	if ($row["active"] == "1") {
		echo "<option value=\"".$row["id"]."\">".$row["name"]." (".$row["konto"]."€)</option>";
	} else {
		echo "<option value=\"".$row["id"]."\">".$row["name"]." (inactive, ".$row["konto"]."€)</option>";
	}
}

echo "</select></td></tr>
<tr><td>Neues Passwort:</td><td><input type=\"text\" size=\"15\" name=\"accountpw\" /></td></tr>
<tr><td>Account aktiv:</td><td><input type=\"checkbox\" name=\"accountactive\" value=\"1\" checked=\"true\"/></td></tr>
<tr><td></td><td><input type=\"submit\" value=\"speichern\" /></td></tr>
</table>
</form>
</fieldset>";

?>