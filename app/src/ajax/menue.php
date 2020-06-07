<?php
session_start();
if (is_numeric($_SESSION["wg_userid"]) AND $_SESSION["wg_userid"] > 0)
{
	if ($_SESSION["wg_userid"] == 1) {
		// admin menu
		echo "<a class=\"menue\" href=\"#admin\" onclick=\"get_content('content','ajax/admin.php'); document.getElementById('content2').style.display='none';\">Benutzerverwaltung</a> | 
		<a class=\"menue\" href=\"#account\" onclick=\"get_content('content','ajax/account.php'); document.getElementById('content2').style.display='none';\">Admin Account</a>";
	} else {
		// normal menu
		echo "<a class=\"menue\" href=\"#einkaeufe\" onclick=\"get_content('content','ajax/einkaeufe.php'); showKonto();\">Einkäufe</a> |
		<a class=\"menue\" href=\"#ausgleich\" onclick=\"get_content('content','ajax/ausgleich.php'); showKonto();\">Ausgleich</a> | 
		<a class=\"menue\" href=\"#monatlich\" onclick=\"get_content('content','ajax/monatlich.php'); showKonto();\">Regelmäßige Kosten</a> | 
		<a class=\"menue\" href=\"#einkaufsliste\" onclick=\"get_content('content','ajax/einkaufsliste.php'); document.getElementById('content2').style.display='none';\">Einkaufsliste</a> |
		<a class=\"menue\" href=\"#kuehlschrank\" onclick=\"get_content('content','ajax/kuehlschrank.php'); document.getElementById('content2').style.display='none';\">Kühlschrank</a> |
		<a class=\"menue\" href=\"#muell\" onclick=\"get_content('content','ajax/muell.php'); showKonto();\">Müll</a> |
		<a class=\"menue\" href=\"#diagramm\" onclick=\"get_content('content','ajax/diagramm.php'); showLegend();\">Diagramm</a> |
		<a class=\"menue\" href=\"#account\" onclick=\"get_content('content','ajax/account.php'); document.getElementById('content2').style.display='none';\">Meine Daten</a> |
		<a class=\"menue\" href=\"logout.php\" onclick=\"document.getElementById('content2').style.display='none';\">Logout</a>";
	}
}
else
{
	echo "<a class=\"menue\" href=\"#start\" onclick=\"get_content('content','ajax/start.php'); document.getElementById('content2').style.display='none';\">Start</a> | 
	<a class=\"menue\" href=\"#login\" onclick=\"get_content('content','ajax/login.php'); document.getElementById('content2').style.display='none';\">Login</a> | 
	<a class=\"menue\" href=\"#impressum\" onclick=\"get_content('content','ajax/impressum.php'); document.getElementById('content2').style.display='none';\">Impressum</a>";
}
?>