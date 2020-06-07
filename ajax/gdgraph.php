<?php

/***********************************************************************************
* Software: GDGraph                                                                *
* Version:  2.1.0                                                                  *
* Date:     2006-08-31                                                             *
* Author:   Makko Solutions                                                        *
* Contact:  gdgraph@makko.com.mx                                                   *
* License:  GPL License (see below)                                                *
* Desription: Create line, pie and bar graphs with PHP and GD installed.           *
***********************************************************************************/

/***********************************************************************************
* Copyright (C) 2006  Makko Solutions                                              *
*                                                                                  *
* This program is free software; you can redistribute it and/or                    *
* modify it under the terms of the GNU General Public License                      *
* as published by the Free Software Foundation; either version 2                   *
* of the License, or (at your option) any later version.                           *
*                                                                                  *
* This program is distributed in the hope that it will be useful,                  *
* but WITHOUT ANY WARRANTY; without even the implied warranty of                   *
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the                    *
* GNU General Public License for more details.                                     *
*                                                                                  *
* You should have received a copy of the GNU General Public License                *
* along with this program; if not, write to the Free Software                      *
* Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.  *
***********************************************************************************/

/*
	This software needs GD to be installed into PHP.
	Preferably the one bundled in, if it's PHP 4.3.0 or higher,
	since that's the one that it was made and tested with.
*/

/*
Corrections/Additions from 1.1.0:
	-	Added the posibility to add 3D width to each bar of the bar graph.
	-	Changed the way that line_graph shows a graph: it will now autoadjust to
		the values it has been given, showing more detail in the area in which
		there are values (kinda like autozoom), by putting asside the necessity
		of the x-axis always showing in it's 0 value. The X-axis will still always
		show, but it's now being dragged to up or down, depending of where the
		graph is being shown.
	-	Because of the last point, it was a natural choice to give line_graph
		the possibility to indicate which area to zoom in on, from both the x-axis
		and the y-axis, dragging both of them as necessary. If this is done though,
		it's very probable that the x-axis will not always be at the y-axis origin.
	-	Gave the y-axis in both bar and line graphs a dynamic x position, depending
		on the length of the y values.
	-	Fixed some bugs concerning the way the bar and line graph were drawing.

Corrections/Additions from 1.0.1:
	-	Added the posibility to make the background transparent (not effective with
		Internet Explorer).
	-	Added the posibility to change the surrounding line thickness and the line graph
		lines' thickness independently from each other.
	-	Error in graphing the positive values of any graph when negative values
		existed.
	-	Bug in _get_specs that didn't calculate correctly the max value of a line
		graph data array.
	-	Added the option to the user to include or not the dots in the line graph.
	-	Corrected many factors that made the line graph and bar graph put data
		outside the graph area, or waste too much space. Probably equivalent to a whole
		rewrite of both graph functions, but this new method is much faster, reliable and
		far more stable than the last one.
	-	Added the possibility to create a grid in the line and bar graph,
		with the given option of how "dark" or "filled" the user wants the grid to be.
	-	Fixed the fact that the argument that dictated the legend position was actually
		the top right corner of the legend not the documented top left corner.
	-	Added the possibility to draw a border around the legend.
	-	Added the possibility to add a 3D view to the pie graph, accepting a certain
		height for each slice, this has the potential to be used as another way to show
		another series of data in the same graph.
	-	Changed the default of the pie graph function for it to occupy only 90% of the
		image, not 100%. This way, by default, the slice can be up to 30 pixels thick
		without being cut off from the image.
	-	Added the possibility to rotate the pie graph giving a starting degree as a
		reference from which the whole graph will be drawn.
	-	Added the option to draw or not the outline of the slices in the bar graph.
	-	X Axis title switched to be printed above the X Axis (I know, I know, sorry...
		last time, I promise).

Corrections from 1.0.0:
	-	Fixed a neverending cycle when entering data arrays all with 0 values.
	-	X Axis title switched to be printed below the X Axis.
	-	Rounding error made the last value negative (in degrees) of pie data array.

*/

if(!class_exists('GDGRAPH')){
define('GDGRAPH_VERSION','2.1.0');
class GDGraph{
	//Protected properties
	var $image;					//actual image
	var $width;					//width of whole image
	var $height;				//height of whole image
	var $line_color;			//color of surrounding lines
	var $line_thickness;		//thickness of surrounding lines
	var $title;					//title of graph
	var $bg_color;				//background color
	var $bg_trans;				//background transparency
	var $left_border;			//distance from left border
	var $right_border;			//distance from right border
	var $top_border;			//distance from top border
	var $bottom_border;			//distance from bottom border
	var $font_color;			//font color
	var $legend;				//if it's going to include a legend
	var $legend_border;			//if it's going to print a border legend
	var $legend_x;				//x position of legend
	var $legend_y;				//y position of legend



	
	/****************** Public methods *******************/

	//Constructor.
	//	Defaults:
	//		With legend
	//		With legend border
	//		No titles
	//		White solid (non-transparent) background
	//		Black lines
	//		Black font
	//		1 pixel thick lines.
	function GDGraph($w, $h, $t="", $bg_c_r=255, $bg_c_g=255, $bg_c_b=255, $l_c_r=0, $l_c_g=0, $l_c_b=0, $str_c_r=0, $str_c_g=0, $str_c_b=0, $l=true,$l_x=NULL,$l_y=NULL,$l_border=true,$trans_back=false, $l_thickness=1){
		$this->width = $w+0;
		$this->height = $h+0;
		
		if ($this->width <= 0 || $this->height <= 0){
			die("GDGraph Error: Width nor height can be smaller or equal to 0.");
		}
		
		$this->image = imagecreate($this->width, $this->height);		
		$this->line_color = imagecolorallocate($this->image, ($l_c_r+0), ($l_c_g+0), ($l_c_b+0));
		$this->line_thickness = $l_thickness;
		$this->bg_color = imagecolorallocate($this->image, ($bg_c_r+0), ($bg_c_g+0), ($bg_c_b+0));
		$this->bg_trans = $trans_back;
		$this->font_color = imagecolorallocate($this->image, ($str_c_r+0), ($str_c_g+0), ($str_c_b+0));
		$this->title = $t."";
		$this->left_border = ceil($this->width * 0.05);
		$this->right_border = $this->width - $this->left_border;
		$this->top_border = ceil($this->height * 0.05);
		$this->bottom_border = $this->height - $this->top_border;

		$this->legend = ($l && true);
		$this->legend_border = ($l_border && true);
		$this->legend_x = $l_x;
		$this->legend_y = $l_y;

		//Activate background color
		imagefill($this->image, 0 ,0, $this->bg_color);

		//Activate transparency
		if ($this->bg_trans){
			imagecolortransparent($this->image, $this->bg_color);
		}

		//Activating line thickness
		imagesetthickness($this->image, $this->line_thickness);
	}

	//Line Graph.
	//	Arrays format:
	//		Data Array:
	//			Name of line => (value in t1, value in t2, value in t3 ...)
	//		Color Array:
	//			Name of line => (red, green, blue)
	//		X Axis Labels Array:
	//			t1, t2, t3 ...
	//		Line Thickness Array:
	//			Name of line => thickness of line
	//	Defaults:
	//		Black, 1 pixel thick lines
	//		No labels
	//		No limits
	function line_graph($data, $color=Array(), $x_ls=Array(), $x_t="", $y_t="", $inc_dot=true, $l_t=Array(), $g_per=0, $x_rl=NULL,  $x_ru=NULL, $y_rl=NULL,  $y_ru=NULL){
		if((((!is_null($x_rl)) && (!is_null($x_ru))) && ($x_rl >= $x_ru)) || (((!is_null($y_rl)) && (!is_null($y_ru))) && ($y_rl >= $y_ru)))
			die("Lower ranges of either X or Y can't be greater or equal to their upper ranges.");

		//Acknowledging the line graph limits in the X axis
		//We start by the color array... this is the same thing we're going to do
		//with the t values of each line's data array
		if ((!is_null($x_rl)) && (!is_null($x_ru))){
			$x_ls = array_slice($x_ls,$x_rl,($x_ru-$x_rl+1));
		}else if(!is_null($x_rl)){
			$x_ls = array_slice($x_ls,$x_rl);
		}else if(!is_null($x_ru)){
			$x_ls = array_slice($x_ls,0,$x_ru+1);
		}

		//Now we do that with the data array
		if ((!is_null($x_rl)) && (!is_null($x_ru))){
			foreach($data as $prod => $sub_data)
					$data[$prod] = array_slice($sub_data,$x_rl,($x_ru-$x_rl+1));
		}else if(!is_null($x_rl)){
			foreach($data as $prod => $sub_data)
				$data[$prod] = array_slice($sub_data,$x_rl);
		}else if(!is_null($x_ru)){
			foreach($data as $prod => $sub_data)
				$data[$prod] = array_slice($sub_data,0,$x_ru+1);
		}

		//Obtain all the specs of the data from the array,
		//including the Y axis length in data value without extra bottom nor top
		$specs = $this->_get_specs($data,"line");

		//Acknowledging the line graph limits in the Y axis
		//this is done by overriding the max_value and min_value specs
		if(!is_null($y_ru))
			$specs['max_value'] = $y_ru;

		if(!is_null($y_rl))
			$specs['min_value'] = $y_rl;
		
		if($specs['max_value'] < $specs['min_value'])
			die("Lower ranges of either X or Y can't be greater or equal to their upper ranges.");

		//This next section is
		//guaranteeing a completely scalable graph independently if graphing .001 or 1000
		$y_axis_slength = 0;
		$all_down = false;
		$all_up = false;
		if (($specs['max_value'] == 0) && ($specs['min_value'] == 0)){
			$y_axis_length = 10;
		}else{
			$y_axis_length = $specs["max_value"]-$specs['min_value'];

			//Getting the exponent to expand the y-axis length to cover
			$y_dig = 0;
			$y_division_value = $y_axis_length;
			if ($y_axis_length < 1){
				while ($y_division_value < 1){
					$y_dig++;
					$y_division_value *= 10;
				}
			}else {
				$y_dig = 1;
				while ($y_division_value >= 10){
					$y_dig--;
					$y_division_value /= 10;
				}
			}

			$y_dec = 0;
			if((!is_null($y_rl)) || (!is_null($y_ru))){
				//if some range was imposed, we make sure it's exact
				$y_t_min = abs($specs["min_value"])."";
				$y_t_array = explode(".",$y_t_min,2);
				if(array_key_exists(1, $y_t_array))
					$y_t_min = $y_t_array[1]."";
				else
					$y_t_min = "";

				$y_t_max = abs($specs["max_value"])."";
				$y_t_array = explode(".",$y_t_max,2);
				if(array_key_exists(1, $y_t_array))
					$y_t_max = $y_t_array[1]."";
				else
					$y_t_max = "";;

				$y_dec = max(strlen($y_t_max),strlen($y_t_min));
			}

			$y_dig = pow(10,$y_dig+$y_dec);

			$y_axis_length = ceil(($specs["max_value"]*$y_dig)-($specs['min_value']*$y_dig))/$y_dig;
			$y_axis_slength = $y_axis_length-(ceil($specs['max_value']*$y_dig)/$y_dig);

			//After this, if no range was imposed:
			//If the numerical length of the y_axis is 3,124, y_axis_length is 3,200
			//if it's 0.002292, y_axis_length is 0.0023, which will become the real
			//numerical length of they y-axis

			//Setting some flags
			if ($specs['max_value'] > 0){
				if ($specs['min_value'] >= 0){
					$all_up = true;
				}
			}else{
				$all_down = true;
			}
		}

		//Obtaining Y axis division length in data value
		if (!($all_down) && !($all_up)){
			$y_dig = 0;
			$y_division_value = $y_axis_length;
			if ($y_axis_length < 1){
				while ($y_division_value < 1){
					$y_dig--;
					$y_division_value *= 10;
				}
				$y_dig--;
			}else { 
				while ($y_division_value >= 10){
					$y_dig++;
					$y_division_value /= 10;
				}
				$y_dig--;
			}
	
			$y_division_value = $y_division_value * pow(10, $y_dig);
		}else
			$y_division_value = $y_axis_length/10;

		//If there are both negative and positive data, another y division
		//is going to be needed.
		if (!($all_down) && !($all_up) && (is_null($y_rl)) && (is_null($y_ru))){
			$y_axis_length += $y_division_value;
			$y_axis_slength += $y_division_value;
		}

		//Obtaining how many steps (going downward in the y axis) is the x-axis
		$x_axis_count = floor(($y_axis_length - $y_axis_slength)/$y_division_value);

		//Top and bottom of y-axis
		$y_axis_top = $this->top_border;
		$y_axis_bottom = $this->bottom_border;

		//Obtaining conversion factor from data to pixel count
		$conversion_factor = ($y_axis_bottom - $y_axis_top)/$y_axis_length;
		$y_division_mid = $y_division_value * ($y_axis_bottom - $y_axis_top)/$y_axis_length;

		//Obtaining max Y value
		if (!($all_down) && !($all_up) && (is_null($y_rl)) && (is_null($y_ru)))
			//Thanks to chris (chrego3-1 at yahoo dot fr) for this next line of code
			$max_y_value = ($x_axis_count+1) * ($y_division_value);
		else if(!($all_down) && !($all_up)){
			$x_axis_count--;
			$max_y_value = $specs["max_value"];
		}else
			$max_y_value = $specs["max_value"];


		$current_y_pos = $y_axis_top;
		$current_y_value = $max_y_value;

		//Defining dashed line style
		if($g_per){
			if($g_per > 10) $g_per = 10;
			else if($g_per < 0) $g_per = 0;
			$g_per = ($g_per*2)+80;
			$grid_style=array($this->line_color, $this->line_color);
			for($i=0; $i < 100-$g_per; $i++)
				array_push($grid_style, IMG_COLOR_TRANSPARENT);
			imagesetstyle($this->image, $grid_style);
		}
		
		$max_font_left_space = 0;
		

		//Painting the y-axis
		$line_count = 0;
		while($current_y_pos <= $y_axis_bottom){
			$font_left_space = strlen($current_y_value."")*5;
			if ($max_font_left_space < $font_left_space) $max_font_left_space = $font_left_space;
			if (!$all_down)
				$current_y_pos += $y_division_mid;
			if((($all_up) && ($line_count == ($x_axis_count-1))) || (!($all_up) && ($line_count == $x_axis_count))){
				if ($all_up || ((!$all_down) && (is_null($y_rl)) && (is_null($y_ru))))
					$current_y_value = 0;		//to avoid PHP putting something like "1628E-90" instead of 0
				else
		 			$current_y_value -= $y_division_value;
			}else{
	 			$current_y_value -= $y_division_value;
			}
			if ($all_down)
				$current_y_pos += $y_division_mid;
			$line_count++;
		}
		$font_left_space = strlen($current_y_value."")*5;
		if ($max_font_left_space < $font_left_space) $max_font_left_space = $font_left_space;

		$y_axis_x = $this->left_border+(($max_font_left_space-20));
		imageline($this->image, $y_axis_x, $y_axis_top, $y_axis_x, $y_axis_bottom, $this->line_color);


		//Painting the y divisions
		$y_division_left = $y_axis_x-5;
		$y_division_right = $y_axis_x+5;

		$current_y_pos = $y_axis_top;
		$current_y_value = $max_y_value;

		$x_axis_y = 0;		
		$x_axis_y_value=0;

		$line_count = 0;
		while($current_y_pos <= $y_axis_bottom){
			$font_left_space = strlen($current_y_value."")*5;
			imagestring($this->image, 1, $y_division_left-$font_left_space, $current_y_pos-4, $current_y_value."", $this->font_color);
			imageline($this->image, $y_division_left, $current_y_pos, $y_division_right, $current_y_pos, $this->line_color);
			if($g_per)
				imageline($this->image, $y_division_right, $current_y_pos, $this->right_border, $current_y_pos, IMG_COLOR_STYLED);
			if (!$all_down)
				$current_y_pos += $y_division_mid;
			if((($all_up) && ($line_count == ($x_axis_count-1))) || (!($all_up) && ($line_count == $x_axis_count))){
				$x_axis_y = $current_y_pos;
				if ($all_up || ((!$all_down) && (is_null($y_rl)) && (is_null($y_ru))))
					$current_y_value = 0;		//to avoid PHP putting something like "1628E-90" instead of 0
				else
		 			$current_y_value -= $y_division_value;
				$x_axis_y_value = $current_y_value;
			}else{
	 			$current_y_value -= $y_division_value;
			}
			if ($all_down)
				$current_y_pos += $y_division_mid;
			$line_count++;
		}

		//If there are both negative and positive data, another y division
		//is going to be needed.
		if (!($all_down) && !($all_up)){
			$font_left_space = strlen($current_y_value."")*5;
			imagestring($this->image, 1, $y_division_left-$font_left_space, $current_y_pos-4, $current_y_value."", $this->font_color);
			imageline($this->image, $y_division_left, $current_y_pos, $y_division_right, $current_y_pos, $this->line_color);
			if($g_per)
				imageline($this->image, $y_division_right, $current_y_pos, $this->right_border, $current_y_pos, IMG_COLOR_STYLED);
			$line_count++;
		}

		//Painting the small the extra parts in the above and lower parts
		//of the Y axis
		imageline($this->image, $y_axis_x, $y_axis_top-($y_division_mid/8), $y_axis_x, $y_axis_top, $this->line_color);
		imageline($this->image, $y_axis_x, $y_axis_bottom, $y_axis_x, $y_axis_bottom+($y_division_mid/8), $this->line_color);

		//Getting the y position of the x axis
		if (!($x_axis_y)){
			if($all_down){
				$x_axis_y = $y_axis_top;
			}else{
				$x_axis_y = $y_axis_bottom;
			}
		}else if($x_axis_y > $y_axis_bottom){
			$x_axis_y = $y_axis_bottom;
		}
		
		//Painting X axis
		$x_axis_left = $y_axis_x;
		$x_axis_right = $this->right_border;
		imageline($this->image, $x_axis_left, $x_axis_y, $x_axis_right, $x_axis_y, $this->line_color);
		$x_axis_left = $y_division_right+10;

		//Reversing the array that contains the x axis labels so that they can be obtained by
		//the 'array_pop' PHP function in the right order
		$x_label = array_reverse($x_ls);

		//Painting each line
		$x_division_width = ($x_axis_right - $x_axis_left)/($specs['length']-1);
		foreach($data as $prod => $sub_data){
			$currentline_color = imagecolorallocate($this->image, ($color[$prod][0]+0), ($color[$prod][1]+0), ($color[$prod][2]+0));
			$currentline_thickness = (is_array($l_t[$prod])) ? (($l_t[$prod][0]+0)==0) ? 1 : $l_t[$prod] : (($l_t[$prod]+0)==0) ? 1 : $l_t[$prod];
			$current_x_pos = $x_axis_left;
			$past_x = -1;
			$past_y = -1;
			foreach($sub_data as $i => $value){
				if ($all_down)
					$currentpoint_rel_height = ($value-$specs['max_value']) * $conversion_factor;
				else if ($all_up)
					$currentpoint_rel_height = ($value-$specs['min_value']) * $conversion_factor;
				else
					$currentpoint_rel_height = ($value-$x_axis_y_value) * $conversion_factor;
				
				$currentpoint_real_height = $x_axis_y - $currentpoint_rel_height;
				if($inc_dot)
					imagefilledrectangle($this->image, $current_x_pos-2, $currentpoint_real_height-2, $current_x_pos+2, $currentpoint_real_height+2, $currentline_color);
				if ($past_x != -1){
					//Activating the currentline thickness
					imagesetthickness($this->image, $currentline_thickness);
					imageline($this->image, $past_x, $past_y, $current_x_pos, $currentpoint_real_height, $currentline_color);
				}
				if(strcmp($specs['ref_length'],$prod)==0){
					//Reseting thickness of surrounding lines
					imagesetthickness($this->image, $this->line_thickness);
					imageline($this->image, $current_x_pos, $x_axis_y+5, $current_x_pos, $x_axis_y-5, $this->line_color);
					if($g_per){
						imagesetstyle($this->image, $grid_style);
						imageline($this->image, $current_x_pos, $x_axis_y-5, $current_x_pos, $this->top_border, IMG_COLOR_STYLED);
						imageline($this->image, $current_x_pos, $x_axis_y+5, $current_x_pos, $this->bottom_border, IMG_COLOR_STYLED);
					}
					
					//Printing X axis label
					$label = array_pop($x_label);
					$font_left_space = strlen($label."")*5/2;
					imagestring($this->image, 1, $current_x_pos-$font_left_space, $x_axis_y+1, $label."", $this->font_color);
				}
				$past_x = $current_x_pos;
				$past_y = $currentpoint_real_height;
				$current_x_pos += $x_division_width;
			}
		}

		//Reseting thickness of surrounding lines
		imagesetthickness($this->image, $this->line_thickness);

		//Printing Title
		$font_left_space = (strlen($this->title)*7)/2;
		imagestring($this->image, 3, ($this->width/2)-($font_left_space), 0, $this->title, $this->font_color);
		
		//Printing Y Axis Title
//		imagestring($this->image, 2, $y_division_left-$max_font_left_space, 0, $y_t."", $this->font_color);
		imagestring($this->image, 2, 0, 0, $y_t."", $this->font_color);

		//Printing X Axis Title
		$font_left_space = strlen($x_t."")*6;
		imagestring($this->image, 2, $this->width-$font_left_space, $x_axis_y-12, $x_t."", $this->font_color);

		//Legend
		if($this->legend){
			if (count($color)>0)
				$this->_do_legend($color);
			else{
				$key = array_keys($data);
				foreach($key as $key => $value){
					$color[$value] = Array(0,0,0);
				}
				$this->_do_legend($color);
			}
		}

		//Return image
		header('Content-type: image/png');
		imagepng($this->image);
		imagedestroy($this->image);
	}

	//Bar Graph.
	//	Array format:
	//		Name of bar=> (value of bar, red, green, blue)
	//	Defaults:
	//		Bar width/Region width = 90%
	//		Black color
	function bar_graph($data, $x_t="", $y_t="", $wi_p=90, $g_per=0, $b_ol=true){
		//Obtaining Y axis length in data value without extra bottom nor top
		$specs = $this->_get_specs($data,"bar");

		//This next section is
		//guaranteeing a completely scalable graph independently if graphing .001 or 1000
		$y_axis_slength = 0;
		$all_down = false;
		$all_up = false;
		if (($specs['max_value'] == 0) && ($specs['min_value'] == 0)){
			$y_axis_length = 10;
			$y_axis_slength = 0;
		}else{
			if ($specs['max_value'] > 0){
				$y_axis_length = $specs["max_value"];
				if ($specs['min_value'] < 0){
					$y_axis_length -= $specs['min_value'];
				}
			}else{
				$y_axis_length = abs($specs["min_value"]);
			}

			$y_dig = 0;
			$y_division_value = $y_axis_length;
			if ($y_axis_length < 1){
				while ($y_division_value < 1){
					$y_dig++;
					$y_division_value *= 10;
				}
			}else {
				$y_dig = 1;
				while ($y_division_value >= 10){
					$y_dig--;
					$y_division_value /= 10;
				}
			}
			
			$y_dig = pow(10,$y_dig+1);

			if ($specs['max_value'] > 0){
				if ($specs['min_value'] < 0){
					$y_axis_length = ceil(($specs["max_value"]*$y_dig)-($specs['min_value']*$y_dig))/$y_dig;
					$y_axis_slength = $y_axis_length-(ceil($specs['max_value']*$y_dig)/$y_dig);
				}else{
					$y_axis_length = ceil($specs["max_value"]*$y_dig)/$y_dig;
					$all_up = true;
				}
			}else{
				$y_axis_length = abs(floor($specs["min_value"]*$y_dig)/$y_dig);
				$y_axis_slength = $y_axis_length;
				$all_down = true;
			}

			//After this:
			//If the numerical length of the y_axis is 3,124, y_axis_length is 3,200
			//if it's 0.002292, y_axis_length is 0.0023, which will become the real
			//numerical length of they y-axis

		}
	
		//Obtaining Y axis division length in data value
		if (!($all_down) && !($all_up)){
			$y_dig = 0;
			$y_division_value = $y_axis_length;
			if ($y_axis_length < 1){
				while ($y_division_value < 1){
					$y_dig--;
					$y_division_value *= 10;
				}
				$y_dig--;
			}else { 
				while ($y_division_value >= 10){
					$y_dig++;
					$y_division_value /= 10;
				}
				$y_dig--;
			}
	
			$y_division_value = $y_division_value * pow(10, $y_dig);
		}else
			$y_division_value = $y_axis_length/10;

		//If there are both negative and positive data, another y division
		//is going to be needed.
		if (!($all_down) && !($all_up)){
			$y_axis_length += $y_division_value;
			$y_axis_slength += $y_division_value;
		}

		//Obtaining how many steps (going downward in the y axis) is the x-axis
		$x_axis_count = floor(($y_axis_length - $y_axis_slength)/$y_division_value);

		//Top and bottom of y-axis
		$y_axis_top = $this->top_border;
		$y_axis_bottom = $this->bottom_border;

		//Obtaining conversion factor from data to pixel count
		$conversion_factor = ($y_axis_bottom - $y_axis_top)/$y_axis_length;
		$y_division_mid = $y_division_value * ($y_axis_bottom - $y_axis_top)/$y_axis_length;

		//Obtaining max Y value
		if ($all_down)
			$max_y_value = 0;
		else if(!($all_down) && !($all_up)){
			//Thanks to chris (chrego3-1 at yahoo dot fr) for this next line of code
			$max_y_value = ($x_axis_count+1) * ($y_division_value);
		}else
			$max_y_value = $specs["max_value"];


		//Defining dashed line style
		if($g_per){
			if($g_per > 10) $g_per = 10;
			else if($g_per < 0) $g_per = 0;
			$g_per = ($g_per*2)+80;
			$style=array($this->line_color, $this->line_color);
			for($i=0; $i < 100-$g_per; $i++)
				array_push($style, IMG_COLOR_TRANSPARENT);
			imagesetstyle($this->image, $style);
		}
		
		$current_y_pos = $y_axis_top;
		$current_y_value = $max_y_value;
		$max_font_left_space = 0;

		//Painting the y-axis
		$line_count = 0;
		while($current_y_pos <= $y_axis_bottom){
			$font_left_space = strlen($current_y_value."")*5;
			if ($max_font_left_space < $font_left_space) $max_font_left_space = $font_left_space;
			if (!$all_down)
				$current_y_pos += $y_division_mid;
			if($line_count == ($x_axis_count-1)){
				$x_axis_y = $current_y_pos;
				if (!$all_down){
					$current_y_value = 0;		//to avoid PHP putting something like "1628E-90" instead of 0
				}else
		 			$current_y_value -= $y_division_value;
			}else{
				$current_y_value -= $y_division_value;
			}
			if ($all_down)
				$current_y_pos += $y_division_mid;
			$line_count++;
		}
		$font_left_space = strlen($current_y_value."")*5;
		if ($max_font_left_space < $font_left_space) $max_font_left_space = $font_left_space;

		$y_axis_x = $this->left_border+(($max_font_left_space-20));
		imageline($this->image, $y_axis_x, $y_axis_top, $y_axis_x, $y_axis_bottom, $this->line_color);


		//Painting y-divisions
		$y_division_left = $y_axis_x-5;
		$y_division_right = $y_axis_x+5;

		$current_y_pos = $y_axis_top;
		$current_y_value = $max_y_value;

		$x_axis_y = 0;		
		$x_axis_y_value=0;

		$line_count = 0;
		while($current_y_pos <= $y_axis_bottom){
			$font_left_space = strlen($current_y_value."")*5;
			imagestring($this->image, 1, $y_division_left-$font_left_space, $current_y_pos-4, $current_y_value."", $this->font_color);
			imageline($this->image, $y_division_left, $current_y_pos, $y_division_right, $current_y_pos, $this->line_color);
			if($g_per)
				imageline($this->image, $y_division_right, $current_y_pos, $this->right_border, $current_y_pos, IMG_COLOR_STYLED);

			if (!$all_down)
				$current_y_pos += $y_division_mid;
//			if((($all_up) && ($line_count == ($x_axis_count-1))) || (!($all_up) && ($line_count == $x_axis_count))){
			if($line_count == $x_axis_count){
				$x_axis_y = $current_y_pos;
				if (!$all_down)
					$current_y_value = 0;		//to avoid PHP putting something like "1628E-90" instead of 0
				else
		 			$current_y_value -= $y_division_value;
			}else{
				$current_y_value -= $y_division_value;
			}
			if ($all_down)
				$current_y_pos += $y_division_mid;
			$line_count++;
		}
		
		$max_font_left_space = 0;
		
		//If there are both negative and positive data, another y division
		//is going to be needed.
		if (!($all_down) && !($all_up)){
			$font_left_space = strlen($current_y_value."")*5;
			imagestring($this->image, 1, $y_division_left-$font_left_space, $current_y_pos-4, $current_y_value."", $this->font_color);
			imageline($this->image, $y_division_left, $current_y_pos, $y_division_right, $current_y_pos, $this->line_color);
			if($g_per)
				imageline($this->image, $y_division_right, $current_y_pos, $this->right_border, $current_y_pos, IMG_COLOR_STYLED);
			$line_count++;
			if ($max_font_left_space < $font_left_space) $max_font_left_space = $font_left_space;
		}

		//Painting the small the extra parts in the above and lower parts
		//of the Y axis
		imageline($this->image, $y_axis_x, $y_axis_top-($y_division_mid/8), $y_axis_x, $y_axis_top, $this->line_color);
		imageline($this->image, $y_axis_x, $y_axis_bottom, $y_axis_x, $y_axis_bottom+($y_division_mid/8), $this->line_color);

		//Obtaining the y position of the x axis
		if (!($x_axis_y)){
			if($all_down){
				$x_axis_y = $y_axis_top;
			}else{
				$x_axis_y = $y_axis_bottom;
			}
		}else if($x_axis_y > $y_axis_bottom){
			$x_axis_y = $y_axis_bottom;
		}

		//Painting X axis
		$x_axis_left = $y_division_right;
		$x_axis_right = $this->right_border;
		imageline($this->image, $x_axis_left, $x_axis_y, $x_axis_right, $x_axis_y, $this->line_color);

		//Painting each bar
		$bar_width = ($x_axis_right - $x_axis_left)/count($data);
		$bar_side_space = $bar_width*((100-abs($wi_p))/200);
		$current_x_pos = $x_axis_right - $bar_width;
		$data_rev = array_reverse($data);
		foreach($data_rev as $prod => $sub_data){
			$currentbar_rel_height = $sub_data[0] * $conversion_factor;
			$currentbar_color = imagecolorallocate($this->image, ($sub_data[1]+0), ($sub_data[2]+0), ($sub_data[3]+0));
			$currentbar_3dcolor = imagecolorallocate($this->image, ($sub_data[1]-50 < 0) ? 0 : $sub_data[1]-50, ($sub_data[2]-50 < 0) ? 0 : $sub_data[2]-50, ($sub_data[3]-50 < 0) ? 0 : $sub_data[3]-50);
			$currentbar_3dwidth = (array_key_exists(4,$sub_data)) ? $sub_data[4]+0 : 0;
			$currentbar_varedge = $x_axis_y-$currentbar_rel_height;
			if ($currentbar_varedge > $x_axis_y){
				//3D Effect
				for($i = 0; $i < $currentbar_3dwidth; $i++)
					imagefilledrectangle($this->image, $current_x_pos+$bar_side_space+$i, $x_axis_y+$i, $current_x_pos+$bar_width-$bar_side_space+$i, $currentbar_varedge+$i, $currentbar_3dcolor);
				imagefilledrectangle($this->image, $current_x_pos+$bar_side_space+$i, $x_axis_y+$i, $current_x_pos+$bar_width-$bar_side_space+$i, $currentbar_varedge+$i, $currentbar_color);
				if($b_ol){
					imagepolygon($this->image,Array(
							$current_x_pos+$bar_side_space, $x_axis_y,
							$current_x_pos+$bar_width-$bar_side_space,$x_axis_y,
							$current_x_pos+$bar_width-$bar_side_space+$i,$x_axis_y+$i,
							$current_x_pos+$bar_side_space+$i,$x_axis_y+$i
					),4,$this->line_color);
					imagepolygon($this->image,Array(
							$current_x_pos+$bar_side_space, $x_axis_y,
							$current_x_pos+$bar_side_space+$i,$x_axis_y+$i,
							$current_x_pos+$bar_side_space+$i,$currentbar_varedge+$i,
							$current_x_pos+$bar_side_space,$currentbar_varedge
					),4,$this->line_color);
					imagerectangle($this->image, $current_x_pos+$bar_side_space+$i, $x_axis_y+$i, $current_x_pos+$bar_width-$bar_side_space+$i, $currentbar_varedge+$i, $this->line_color);
				}
			}else{
				//3D Effect
				for($i = 0; $i < $currentbar_3dwidth; $i++)
					imagefilledrectangle($this->image, $current_x_pos+$bar_side_space+$i, $currentbar_varedge+$i, $current_x_pos+$bar_width-$bar_side_space+$i, $x_axis_y+$i, $currentbar_3dcolor);
				imagefilledrectangle($this->image, $current_x_pos+$bar_side_space+$i, $currentbar_varedge+$i, $current_x_pos+$bar_width-$bar_side_space+$i, $x_axis_y+$i, $currentbar_color);
				if($b_ol){
					imagepolygon($this->image,Array(
							$current_x_pos+$bar_side_space, $currentbar_varedge,
							$current_x_pos+$bar_width-$bar_side_space, $currentbar_varedge,
							$current_x_pos+$bar_width-$bar_side_space+$i, $currentbar_varedge+$i,
							$current_x_pos+$bar_side_space+$i, $currentbar_varedge+$i
					),4,$this->line_color);
					imagepolygon($this->image,Array(
							$current_x_pos+$bar_side_space, $x_axis_y,
							$current_x_pos+$bar_side_space+$i,$x_axis_y+$i,
							$current_x_pos+$bar_side_space+$i,$currentbar_varedge+$i,
							$current_x_pos+$bar_side_space,$currentbar_varedge
					),4,$this->line_color);
					imagerectangle($this->image, $current_x_pos+$bar_side_space+$i, $x_axis_y+$i, $current_x_pos+$bar_width-$bar_side_space+$i, $currentbar_varedge+$i, $this->line_color);
				}
			}
			$font_left_space = (strlen($prod)*5)/2;
			imagestring($this->image, 2, $current_x_pos+($bar_width/2)-($font_left_space)+$i, $x_axis_y+$i, $prod, $this->font_color);

			$current_x_pos -= $bar_width;
		}
		
		//Printing Title
		$font_left_space = (strlen($this->title)*7)/2;
		imagestring($this->image, 3, ($this->width/2)-($font_left_space), 0, $this->title, $this->font_color);
		
		//Printing Y Axis Title
		imagestring($this->image, 2, $y_division_left-$max_font_left_space, 0, $y_t."", $this->font_color);

		//Printing X Axis Title
		$font_left_space = strlen($x_t."")*6;
		imagestring($this->image, 2, $this->width-$font_left_space, $x_axis_y-12, $x_t."", $this->font_color);

		//Printing Legend
		if($this->legend){
			$legend_data = Array();
			foreach($data as $prod => $sub_data){
				$legend_data[$prod] = Array($sub_data[1],$sub_data[2],$sub_data[3]);
			}
			$this->_do_legend($legend_data);
		}

		//Return image
		header('Content-type: image/png');
		imagepng($this->image);
		imagedestroy($this->image);
	}

	//Pie Graph.
	//	Array format:
	//		Name of pie slice => (absolute value of slice, red, green, blue)
	//	Defaults:
	//		Black color
	//		Pie filling 90% of the image
	//		With piece labels
	//		Start at 0 degrees
	//		No 3D effect in anyone
	//		1 pixel line thickness
	function pie_graph($data, $p_o=90, $put_pieces=true, $degree_start=0, $put_l=true, $threed_thickness=Array()){
		//Get center of pie
		$pie_center_x = ($this->right_border + $this->left_border)/2;
		$pie_center_y = ($this->top_border + $this->bottom_border)/2;
		$pie_width = ($this->right_border - $this->left_border)*$p_o/100;
		$pie_height = ($this->bottom_border - $this->top_border)*$p_o/100;

		//Draw lines, fill with color and label each pie slice
		$specs = $this->_get_specs($data,"pie");
		$data_total_elements = count($data);
		
		$max_threed_thickness = 0;
		if (count($threed_thickness) > 0){
			$max_threed_thickness = max($threed_thickness)+0;
		}
		
		for ($i = 0; $i <= $max_threed_thickness; $i++){
			$total_degree_width = 0;
			$curr_ele = 1;
			foreach($data as $prod => $subarray){
				//Getting degree width of slice
				if ($curr_ele == $data_total_elements){
					$degree_width = 360 - $total_degree_width;
	
					//Thanks for Nam Phando for this next three lines.
					//The last value of the array can have a negative value
					//because of rounding errors, so these takes care of it.
					if ($degree_width < 0){
						$degree_width = 0;
					}
				}else{
					//Usage of round, recommended by Nam Phando.
					$degree_width = round((($subarray[0]+0) / $specs['total'])*360);
				}

				$curr_ele++;
								
				if ($i == $threed_thickness[$prod]+0){		
					//Obtaining the color of this slice
					$curr_color = imagecolorallocate($this->image,$subarray[1]+0,$subarray[2]+0,$subarray[3]+0);

					//Drawing slice with its color
					imagefilledarc($this->image, $pie_center_x, $pie_center_y-$threed_thickness[$prod], $pie_width, $pie_height, $degree_start+$total_degree_width, $degree_start+$total_degree_width+$degree_width, $curr_color,IMG_ARC_PIE);
		
					//Drawing top outline and side outline
					if($put_l){
						imagefilledarc($this->image, $pie_center_x, $pie_center_y-$threed_thickness[$prod], $pie_width, $pie_height, $degree_start+$total_degree_width, $degree_start+$total_degree_width+$degree_width, $this->line_color,IMG_ARC_NOFILL | IMG_ARC_EDGED);
						if ((floor(($degree_start+$total_degree_width)/180)%2) == 0){
							$ver_line_pos = $this->_ellipse_pos($degree_start+$total_degree_width, $pie_height, $pie_width, "");
							imageline($this->image, $pie_center_x+$ver_line_pos['x'], $pie_center_y+$ver_line_pos['y'], $pie_center_x+$ver_line_pos['x'], $pie_center_y+$ver_line_pos['y']-$threed_thickness[$prod], $this->line_color);
						}
						
						if ((floor(($degree_start+$total_degree_width+$degree_width)/180)%2) == 0){
							$ver_line_pos = $this->_ellipse_pos($degree_start+$total_degree_width+$degree_width, $pie_height, $pie_width, "");
							imageline($this->image, $pie_center_x+$ver_line_pos['x'], $pie_center_y+$ver_line_pos['y'], $pie_center_x+$ver_line_pos['x'], $pie_center_y+$ver_line_pos['y']-$threed_thickness[$prod], $this->line_color);
						}
					}
					//Printing label
					if($put_pieces){
						$label_pos = $this->_ellipse_pos($degree_start+$total_degree_width+($degree_width/2), $pie_height, $pie_width, $prod."");
						imagestring($this->image, 2, $pie_center_x+$label_pos['x'], $pie_center_y+$label_pos['y']-$threed_thickness[$prod], $prod."", $this->font_color);
					}
				}else if ($i < $threed_thickness[$prod]+0){
					//Obtaining the color of the 3D side of this slice
					$curr_3dcolor = imagecolorallocate($this->image, ($subarray[1]-50 < 0) ? 0 : $subarray[1]-50, ($subarray[2]-50 < 0) ? 0 : $subarray[2]-50, ($subarray[3]-50 < 0) ? 0 : $subarray[3]-50);

					//Drawing bottom outline of pie in 3d
					if($put_l and $i == 0)
						imagefilledarc($this->image, $pie_center_x, $pie_center_y, $pie_width+1, $pie_height+1, $degree_start+$total_degree_width, $degree_start+$total_degree_width+$degree_width, $this->line_color,IMG_ARC_NOFILL | IMG_ARC_EDGED);
					//Creating 3D effect
					imagefilledarc($this->image, $pie_center_x, $pie_center_y-$i, $pie_width, $pie_height, $degree_start+$total_degree_width, $degree_start+$total_degree_width+$degree_width, $curr_3dcolor,IMG_ARC_PIE);
				}

				//Getting starting point of next slice
				$total_degree_width+=$degree_width;
			}
		}

		//Printing title
		$font_left_space = (strlen($this->title)*7)/2;
		imagestring($this->image, 3, ($this->width/2)-($font_left_space), 0, $this->title, $this->font_color);

		//Printing legend
		if($this->legend){
			$legend_data = Array();
			foreach($data as $prod => $sub_data){
				$legend_data[$prod] = Array($sub_data[1],$sub_data[2],$sub_data[3]);
			}
			$this->_do_legend($legend_data);
		}
		
		//Return image
		header('Content-type: image/png');
		imagepng($this->image);
		imagedestroy($this->image);
	}





	
	/****************** Protected methods *******************/
	//Gets the highest value, lowest value, and average out of an array.
	function _get_specs($data, $type){
		switch($type){
			case "line":
				$longest = 0;
				$longest_index = "";

				//Thanks to Mathieu Davy for detecting the absence of this next line
				$max = $data[key($data)][0];
				$min = $data[key($data)][0];	//get the first element to search for
												//lowest and highest value
				foreach($data as $i => $sub_array){
					$this_max = max($sub_array);
					$this_min = min($sub_array);
					$length = count($sub_array);
					if($max < $this_max){
						$max = $this_max;
					}
					if($min > $this_min){
						$min = $this_min;
					}
					if($longest < $length){
						$longest = $length;
						$longest_index = $i;
					}
				}
				return Array("max_value" => $max,"min_value" => $min, "length" => $longest, "ref_length" => $longest_index);

			case "bar":
				$max = $data[key($data)][0];
				$min = $data[key($data)][0];	//get the first element to search for
												//lowest and highest value
				foreach($data as $i => $sub_array){
					if($max < $sub_array[0]){
						$max = $sub_array[0];
					}
					if($min > $sub_array[0]){
						$min = $sub_array[0];
					}
				}
				return Array("max_value" => $max,"min_value" => $min);
			
			case "pie":
				$total = 0;
				foreach($data as $i => $sub_array){
					$total += $sub_array[0];
				}
				return Array("total" => $total);
			break;
		}
	}
	
	//Displays the legend
	//	Array format:
	//		Name => (red, green, blue)
	function _do_legend($data){
		//Getting the width and height of the legend
		$longest_name_length = 0;
		$legend_height = 0;
		foreach($data as $name => $sub_data){
			if (strlen($name) > $longest_name_length) $longest_name_length = strlen($name);
			$legend_height += 10;
		}

		$legend_width = ($longest_name_length*5)+18;

		//Getting the x position of the left corner of the legend
		if (($this->legend_x > $this->right_border) || is_null($this->legend_x))
			$this->legend_x = $this->right_border - $legend_width;
		else if ($this->legend_x < $this->left_border)
			$this->legend_x = $this->left_border;
		
		$current_x_pos = $this->legend_x;

		//Getting the y position of the left corner of the legend
		if (($this->legend_y < ($this->top_border)) || is_null($this->legend_y))
			$this->legend_y = $this->top_border;
		else if ($this->legend_y > $this->bottom_border - $legend_height)
			$this->legend_y = $this->bottom_border - $legend_height;

		$current_y_pos = $this->legend_y + 1;

		//Printing the titles and colors of the data
		foreach($data as $name => $sub_data){
			$current_color = imagecolorallocate($this->image, ($sub_data[0]+0), ($sub_data[1]+0), ($sub_data[2]+0));
			imagefilledrectangle($this->image, $current_x_pos+3,$current_y_pos+1, $current_x_pos+8,$current_y_pos+6,$current_color);
			imagestring($this->image, 1, $current_x_pos+16, $current_y_pos, $name, $this->font_color);
			$current_y_pos += 10;
		}

		//Printing legend border
		if($this->legend_border)
			imagerectangle($this->image, $this->legend_x, $this->legend_y, $this->legend_x+$legend_width, $this->legend_y+$legend_height, $this->line_color);
	}
	
	//Returns the true position of a label given the height, width and radius of an ellipse
	function _ellipse_pos($degree, $ellipse_height, $ellipse_width, $prod){
		//Obtaining segments a and b from ellipse
		$ellipse_a = ceil($ellipse_width/2);
		$ellipse_b = ceil($ellipse_height/2);
		$ellipse_a2 = pow($ellipse_a,2);
		$ellipse_b2 = pow($ellipse_b,2);

		//Proportion from this ellipse between x and y
		$e = $ellipse_height/$ellipse_width;
		$circle_radius = ($ellipse_height > $ellipse_width) ? $ellipse_height : $ellipse_width;
	
		//Obtaining Y of the circle from which this ellipse was squished from
		$circle_angle_x = $circle_radius*cos(deg2rad($degree));
		$circle_angle_y = $circle_radius*sin(deg2rad($degree));
		if ($ellipse_height > $ellipse_width){
			//Obtaining x for this ellipse, because we know that if we're here, $circle_angle_y == $label_y
			$circle_angle_x = $circle_angle_x/$e;
		}else{
			//Obtaining y for this ellipse, because we know that if we're here, $circle_angle_x == $label_x
			$circle_angle_y = $circle_angle_y*$e;
		}
	
		$label_angle = rad2deg(atan($circle_angle_y/$circle_angle_x));
		if($circle_angle_y < 0 && $circle_angle_x < 0){
			$label_angle += 180;
		}else if($circle_angle_y < 0){
			$label_angle = 360 + $label_angle;
		}else if($circle_angle_x < 0){
			$label_angle += 180;
		}
		
		$label_radius = round(sqrt($ellipse_b2*$ellipse_a2/(($ellipse_b2*pow(cos(deg2rad($label_angle)),2))+($ellipse_a2*pow(sin(deg2rad($label_angle)),2)))));

		if (strlen($prod."") > 0){
			if($label_angle > 90 && $label_angle < 180){
				$label_radius += (abs(strlen($prod."")*3)*abs(sin(deg2rad($label_angle-90))));
			}else if($label_angle >= 180 && $label_angle <= 270){
				$label_radius += (abs(strlen($prod."")*3)*abs(sin(deg2rad($label_angle-90))))+(10*abs(sin(0.5*deg2rad($label_angle))));
			}else if($label_angle > 270){
				$label_radius -= (abs(strlen($prod."")*3/2)*abs(sin(deg2rad($label_angle-90))));
			}
		}

		return Array('x' => $label_radius*cos(deg2rad($label_angle)), 'y' => $label_radius*sin(deg2rad($label_angle)));
	}
}}

?>