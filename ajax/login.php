<?php
session_start();
include("../mysql.php");

	echo "<div style='float:left;'><form method=\"post\" action=\"login.php\">";
	//echo "<img src='images/wg_1.jpg' alt='Karlsruhe' width='300'/><br /><br />";
	echo "<table border=\"0\">
	<tr><td>Login:</td><td><select name=\"login\">";
	
	$sql = qry("SELECT id, name FROM wg_user WHERE wg = '1' ORDER BY name ASC");
	while($row = mysqli_fetch_assoc($sql)) {
		echo "<option value=\"".$row["id"]."\">".$row["name"]."</option>";
	}
	
	echo "</select></td></tr>
	<tr><td>Passwort:</td><td><input type=\"password\" name=\"pw\" /></td></tr>
	<tr><td colspan=\"2\"><input type=\"checkbox\" name=\"save_login\" value=\"1\" checked=\"checked\" /> Login-Daten speichern</td></tr>
	</table><br />
	<center>
	<input type=\"submit\" value=\"Login\" style=\"font-weight: bold;\" />
	</center>
	</form></div>";
	
	// Uncomment to add a second WG
	/*
	echo "<div style='margin-left: 100px; float:right;'><form method=\"post\" action=\"login.php\">
	<img src='images/wg_2.jpg' alt='Nürnberg' width='300'/><br /><br />
	<table border=\"0\">
	<tr><td>Login:</td><td><select name=\"login\">";
	
	$sql = qry("SELECT id, name FROM wg_user WHERE wg = '2' ORDER BY name ASC");
	while($row = mysqli_fetch_assoc($sql)) {
		echo "<option value=\"".$row["id"]."\">".$row["name"]."</option>";
	}
	
	echo "</select></td></tr>
	<tr><td>Passwort:</td><td><input type=\"password\" name=\"pw\" /></td></tr>
	<tr><td colspan=\"2\"><input type=\"checkbox\" name=\"save_login\" value=\"1\" checked=\"checked\" /> Login-Daten speichern</td></tr>
	</table><br />
	<center>
	<input type=\"submit\" value=\"Login\" style=\"font-weight: bold;\" />
	</center>
	</form></div>";
	*/
?>