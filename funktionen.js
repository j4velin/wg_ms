function loadpage() // zwecks Lesezeichen wiederherstellen
{
	if (location.hash.length > 1)
	{
	  var page = unescape(location.hash.substring(1));
	  get_content('content','ajax/'+page+'.php');
	  if (page == "einkaeufe" || page == "ausgleich")
	  {
			showKonto();
	  }
	  else if (page == "diagramm")
	  {
			showLegend();
	  }
	}
	else
	{
		get_content('content','ajax/start.php');
	}
}

function toggle(id)
{
	color = document.getElementById(id).style.backgroundColor;
	if (color == "red" || color == "#ff0000")
	{
		document.getElementById(id).style.backgroundColor = "green";
	}
	else
	{
		document.getElementById(id).style.backgroundColor = "red";
	}
}

function showKonto()
{
		document.getElementById('content2').style.top='10px';
	  get_content('content2','ajax/konto.php');
		document.getElementById('content2').style.display='block';
}

function showLegend()
{
		document.getElementById('content2').style.top='140px';
	  get_content('content2','ajax/diagramm.php?legend=1');
		document.getElementById('content2').style.display='block';
}

function resizeShoutbox() {
		document.getElementById('shoutbox').style.maxHeight=window.innerHeight-748-50+"px";
}

function submitenter(e)
{
var keycode;
if (window.event) keycode = window.event.keyCode;
else if (e) keycode = e.which;
else return true;

if (keycode == 13)
   {
   shout();
   return false;
   }
else
   return true;
}

function selectChanged(select) {
			if (select.value == 1) {
				document.getElementById('verb').innerHTML='bekommen';
			} else {
				document.getElementById('verb').innerHTML='gegeben';
			}
}