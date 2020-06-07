<?php
require_once("gdgraph.php");
$gdg = new GDGraph(500,300,"Sales");

$arr = Array(
		'First Trimester' => Array(1000,200,100,1000,50),
		'Second  Trimester' => Array(1200,200,200,10,20),
		'Third  Trimester' => Array(-500,23,255,100,15)
		);

//Example 1
//$gdg->bar_graph($arr);

//Example 2 (uncomment this next line, and comment the past one)
$gdg->bar_graph($arr,"Trimesters","$",20,5,false);
?>
