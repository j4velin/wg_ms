<?php
require_once("gdgraph.php");
$gdg = new GDGraph(500,300,"Sales");

$arr = Array(
			'First Trimester' => Array(1000,200,100,1000),
			'Second Trimester' => Array(1200,200,200,10),
			'Third Trimester' => Array(500,23,255,100)
			);

$arr_3D = Array(
			'First Trimester' => 15,
			'Second Trimester' => 0,
			'Third Trimester' => 5
			);

//Example 1
$gdg->pie_graph($arr);

//Example 2 (uncomment this next line, and comment the past one)
//$gdg->pie_graph($arr,90,false,200,false,$arr_3D);
?>
