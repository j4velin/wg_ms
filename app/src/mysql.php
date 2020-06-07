<?php

Global $link;

// host, user, password, database
$link = mysqli_connect(getenv("MYSQL_HOST"), getenv("MYSQL_USER"), getenv("MYSQL_PASSWORD"), getenv("MYSQL_DATABASE"));

mysqli_set_charset($link, "utf8");

//require_once('twitter/twitteroauth/twitteroauth.php');
//require_once('twitter/config.php');

function qry($abfrage)
{
	if ($abfrage == TRUE)
	{
		Global $link;
		$fehler = debug_backtrace();
		$ergebnis = mysqli_query($link, $abfrage);
		if (!$ergebnis)
		{
			echo $fehler[0]["file"]." ".$fehler[0]["line"]." ".mysqli_error($link)." ".mysqli_errno($link);
			//mail("WG_MS@j4velin.de","WG-MS SQL-Fehler",$abfrage." ".$fehler[0]["file"]." ".$fehler[0]["line"]." ".mysqli_error($link)." ".mysqli_errno($link),"From: WG_MS@j4velin.de\nReply-To: WG_MS@j4velin.de");
		}
		return $ergebnis;
  	}	
	else
	{
		$fehler = debug_backtrace();
		echo " MySQL-Query in Zeile: ".$fehler[0]["line"]." in: ".$fehler[0]["file"]." ist leer.<br/>";
  	}
}

function loginfirst() {
	die("<html><head></head><body>Bitte zuerst einloggen.<br />
			<a href=\"index.php\">» weiter »</a>
			<script language=\"javascript\">
			<!--
			window.location.href=\"index.php\";
			// -->
			</script></body></html>");
}

function escape($string) {
	Global $link;
	return mysqli_escape_string($link, $string);
}

function getUserName($id) {
	$row = mysqli_fetch_assoc(qry("SELECT name FROM wg_user WHERE id = '".$id."' LIMIT 1"));
	return $row["name"];
}

// tweets the given text
function twitter($text) {
	return;
	// $connection = new TwitterOAuth(CONSUMER_KEY, CONSUMER_SECRET, "", "");
	// $parameters = array('status' => crop(utf8_encode($text), 140));
	// $status = $connection->post('statuses/update', $parameters);
}

/*
function crop($str, $len) {
    if ( strlen($str) <= $len ) {
        return $str;
    }

    // find the longest possible match
    $pos = 0;
    foreach ( array('. ', '? ', '! ') as $punct ) {
        $npos = strpos($str, $punct);
        if ( $npos > $pos && $npos < $len ) {
            $pos = $npos;
        }
    }

    if ( !$pos ) {
        // substr $len-3, because the ellipsis adds 3 chars
        return substr($str, 0, $len-3) . '...'; 
    }
    else {
        // $pos+1 to grab punctuation mark
        return substr($str, 0, $pos+1);
    }
}
*/

?>
