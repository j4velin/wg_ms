function GetXmlHttpObject()
{
  var xmlHttp=null;
  try
    {
    // Firefox, Opera 8.0+, Safari
    xmlHttp=new XMLHttpRequest();
    }
  catch (e)
    {
    // Internet Explorer
    try
      {
      xmlHttp=new ActiveXObject("Msxml2.XMLHTTP");
      }
    catch (e)
      {
      xmlHttp=new ActiveXObject("Microsoft.XMLHTTP");
      }
    }
  if (xmlHttp==null)
  {
	  alert ("Your browser does not support AJAX!");
	  return false;
  }  
  return xmlHttp;
}

function get_content(id, page)
{
	document.getElementById(id).innerHTML = "loading...";
	
	if (laufender_ajax_request == true)
	{
		window.setTimeout("get_content('"+id+"','"+page+"')", 100);
	}
	else
	{
		laufender_ajax_request = true;
	
		xmlHttp=GetXmlHttpObject();
		xmlHttp.open("GET",page,true);
		xmlHttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded; charset=UTF-8");
		xmlHttp.send(null);
	      
	  xmlHttp.onreadystatechange=function()
	  {
		  if(xmlHttp.readyState==4)
		  {
		  	document.getElementById(id).innerHTML = xmlHttp.responseText;
		  	laufender_ajax_request = false;
		  }
	  }
	}     
}


function loadShouts() {

if (laufender_ajax_request != true)
	{
		laufender_ajax_request = true;
	
		xmlHttp=GetXmlHttpObject();
		xmlHttp.open("GET",'ajax/shoutbox.php',true);
		xmlHttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded; charset=UTF-8");
		xmlHttp.send(null);
	      
	  xmlHttp.onreadystatechange=function()
	  {
		  if(xmlHttp.readyState==4)
		  {
		  	document.getElementById('shoutbox').innerHTML = xmlHttp.responseText;
		  	laufender_ajax_request = false;
		  }
	  }
	  window.setTimeout('loadShouts()', 2000);
	}
else {
window.setTimeout('loadShouts()', 100);
}
		
}

function shout() {
	laufender_ajax_request = true;
	var params = "shoutText="+document.getElementById('shoutText').value.replace("&", "und").replace("+", "und");
	
	xmlHttp=GetXmlHttpObject();
	xmlHttp.open("POST","ajax/shoutbox.php",true);
	xmlHttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded; charset=UTF-8");
	xmlHttp.send(params);
	     
	xmlHttp.onreadystatechange=function()
	{
	  if(xmlHttp.readyState==4)
	  {
	  	document.getElementById("shoutbox").innerHTML = xmlHttp.responseText;
	  	document.getElementById("shoutText").value='';
	  	laufender_ajax_request = false;
	  }
	}
}