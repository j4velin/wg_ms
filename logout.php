<?php
session_start();

session_destroy();
setcookie("wg_userid","",0);
setcookie("wg_pw","",0);

		echo "Logout erfolgreich!<br />
		<a href=\"index.php\">» weiter »</a>
		<script language=\"javascript\">
		<!--
		window.location.href=\"index.php\";
		// -->
		</script>";

?>