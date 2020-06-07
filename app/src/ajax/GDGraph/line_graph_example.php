<?php
require_once("gdgraph.php");

$gdg = new GDGraph(500,300,"Sales");

$arr = Array(
				'First Trimester' => Array(1000,2000,-1000,1500),
				'Second Trimester' => Array(-1000,0,500,-200),
				'Third Trimester' => Array(500,23,255,100)
			);

$colors = Array(
				'First Trimester' => Array(50,50,50),
				'Second Trimester' => Array(250,100,100),
				'Third Trimester' => Array(50,255,0)
			);

$x_labels = Array('January','February','March','April');

$thicknesses = Array(
				'First Trimester' => 10,
				'Second Trimester' => 6,
				'Third Trimester' => 3
			);

//Example 1
$gdg->line_graph($arr, $colors, $x_labels);

//Example 2 (uncomment this next line, and comment the past one and the next one)
//$gdg->line_graph($arr, $colors, $x_labels,"Trimesters","$", false, $thicknesses, 5);

//Example 3 (uncomment this next line, and comment the pasts one)
//$gdg->line_graph($arr, $colors, $x_labels,"Trimesters","$", false, $thicknesses, 5, 0, 2, -900, 900);
?>
