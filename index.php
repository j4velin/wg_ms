<?php
session_start();
require("mysql.php");

header("Content-Type: text/html; charset=UTF-8");  
setlocale(LC_ALL, 'de_DE');
date_default_timezone_set('Europe/Berlin');

// Cookie mit LoginDaten setzen
if (isset($_SESSION["wg_setcookie"])) { 
	setcookie("wg_userid",$_SESSION["wg_userid"], time()+1209600);
	setcookie("wg_pw",$_SESSION["wg_setcookie"], time()+1209600);
	unset($_SESSION["wg_setcookie"]);
}

if ($_COOKIE["wg_userid"] == TRUE AND ($_SESSION["wg_userid"] == 0 OR $_SESSION["wg_userid"] == FALSE))
{ // Logindaten aus Cookie
	$sql = qry("SELECT pw, wg FROM wg_user WHERE id = '".$_COOKIE["wg_userid"]."'");
	$row = mysqli_fetch_assoc($sql);
	if ($_COOKIE["wg_pw"] == $row["pw"])
	{
		$_SESSION["wg_userid"] = $_COOKIE["wg_userid"];
		$_SESSION["wg_wg"] = $row["wg"];
		setcookie("wg_userid",$_COOKIE["wg_userid"], time()+1209600);
		setcookie("wg_pw",$_COOKIE["wg_pw"], time()+1209600);
	}
} // Logindaten aus Cookie ENDE

if (!is_numeric($_SESSION["wg_userid"]))
{
	$_SESSION["wg_userid"] = 0;
}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="de" lang="de">
<head>
	<title>WG Management-System</title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />

	<meta http-equiv="pragma" content="no-cache" />
	<meta http-equiv="cache-control" content="no-cache" />

	<link rel="shortcut icon" href="favicon.ico" type="image/x-icon" />
	<link rel="stylesheet" type="text/css" href="style.css" />

	<script type="text/javascript" src="ajax.js"></script>
	<script type="text/javascript" src="funktionen.js"></script>
	<script type="text/javascript" language="JavaScript"><!--
		laufender_ajax_request = false;
	//--></script>

</head>
<body onload="get_content('menuebox','ajax/menue.php'); loadpage(); resizeShoutbox(); loadShouts();">
<div style="position: relative; margin: 0 auto; border: 1px solid black; background-image: url(images/bg.jpg); width: 1200px; height: 748px;">

<div id="menuebox">
</div>

<div id="content">
</div> 

<div id="content2">
</div> 

</div>
<div style="position: relative; margin: 0 auto; width: 1200px; font-family: Arial; padding: 5px;">
<?php if ($_SESSION["wg_userid"] > 0) { ?>
<input type='text' size='100' id='shoutText' onKeyPress="return submitenter(event)" /> <input type='button' value='send' onclick='javascript:shout();' />
<?php } ?>
<div id="shoutbox" style="overflow: auto; min-height:100px;">

</div>
</div>
</body>
</html>