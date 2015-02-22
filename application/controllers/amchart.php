<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
error_reporting(E_PARSE); 
class Amchart extends CI_Controller {

	/**
	 * Index Page for this controller.
	 *
	 * Maps to the following URL
	 * 		http://example.com/index.php/welcome
	 *	- or -  
	 * 		http://example.com/index.php/welcome/index
	 *	- or -
	 * Since this controller is set as the default controller in 
	 * config/routes.php, it's displayed at http://example.com/
	 *
	 * So any other public methods not prefixed with an underscore will
	 * map to /index.php/welcome/<method_name>
	 * @see http://codeigniter.com/user_guide/general/urls.html
	 */
	public function index()
	{

	}
	public function getXML(){
header ("Content-Type:text/xml"); 
		$name1 = $this->uri->segment(3);
		$stat =$this->uri->segment(4);
		$estat =$this->uri->segment(5);
		$title =$this->uri->segment(6);
//$name1 = $_GET['n'];
//$title = $_GET['t'];
//$stat = $_GET['s'];
//$estat = $_GET['es'];
$currentStat = '';
switch ($stat){
    case 'tabShots':
        $currentStat =  '$';
        break;
    case 'tabGoal':
        $currentStat =  "Goals";
        break;
    case 'tabAssist':
        $currentStat =  "Assists";
        break;
    case 'tabPoints':
        $currentStat =  "Points";
        break;
    case 'tabEventstats':
        $currentStat =  $estat;
		switch ($estat){
			case 'pm':
				$currentStat =  "Plus Minus";
				break;
			case 'sog':
				$currentStat =  "Shots on goal";
				break;
			case 'ab':
				$currentStat =  "Attempts Blocked";
				break;
			case 'ms':
				$currentStat =  "Missed shots";
				break;
			case 'hg':
				$currentStat =  "Hits Given";
				break;
			case 'gv':
				$currentStat =  "Giveaways";
				break;
			case 'tk':
				$currentStat =  "Takeaways";
				break;
			case 'bs':
				$currentStat =  "Blocked Shots";
				break;
			case 'fw':
				$currentStat =  "Faceoffs Won";
				break;
			case 'fl':
				$currentStat =  "Faceoffs Lost";
				break;
			case 'np':
				$currentStat =  "Number of penalties";
				break;
			case 'ns':
				$currentStat =  "Number of Shifts";
				break;
			case 'sp':
			case 'fp':
				$currentStat =  "%";
				break;
		}
        break;
    case 'tabPims':
        $currentStat =  "PIM";
        break;

}
		$arrPlayers= explode(':',$name1);
		$playerCount = count($arrPlayers);
//Old color array $colors = array("stat_player_name1" =>'#CC9900',"stat_player_name2" =>'#FF0000',"stat_player_name3" =>'#C0C0C0',"stat_player_name4" =>'#00FFFF', "stat_player_name5" =>'#000000',"stat_player_name6" =>'#0000FF',"stat_player_name7" =>'#00FF00',"stat_player_name8" =>'#FF00FF');

$colors = array("stat_player_name1" =>'#448AD9',"stat_player_name2" =>'#98E460',"stat_player_name3" =>'#D285EB',"stat_player_name4" =>'#5EE6FF', "stat_player_name5" =>'#FEDD00',"stat_player_name6" =>'#FF2E78');
$out2='';
$c = 1;

if($stat !=='tabShots'){
	foreach($arrPlayers as $player){
		if($c>0){
			$blockPlayer= explode('|',$player);
			$playerName = $blockPlayer[0];
			$playerColor = $colors[$blockPlayer[1]];
	$out2 .= <<<EOT
			<graph gid="$c">                                                            
		  <axis>right</axis>
		  <title>$playerName</title>              
		  <color>$playerColor</color>
		  <color_hover></color_hover>
		  <line_alpha>100</line_alpha> 
		  <line_width>2</line_width>                             
		  <fill_alpha></fill_alpha>
		  <fill_color></fill_color>
		  <balloon_color></balloon_color>
		  <balloon_alpha></balloon_alpha>                           
		  <balloon_text_color>#000000</balloon_text_color>             
		  <bullet></bullet>
		  <bullet_size></bullet_size>
		  <bullet_color></bullet_color>
		  <bullet_alpha></bullet_alpha>
		  <hidden></hidden>
		  <selected></selected>  
		  <balloon_text>
			<![CDATA[<div style='text-align:center'><b>{value}</b></div><br />$playerName]]>
		  </balloon_text>
		  <data_labels>
			<![CDATA[]]> 
		  </data_labels>  
		  <data_labels_text_color></data_labels_text_color>       
		  <data_labels_text_size></data_labels_text_size>         
		  <data_labels_position></data_labels_position>                 
		  <vertical_lines></vertical_lines>                       
		  <visible_in_legend>false</visible_in_legend>   
		</graph>
EOT;
	}
	$c++;
	}
}
else{
			$blockPlayer= explode('|',$player);
			$playerColor = $colors[$blockPlayer[1]];
	$out2 ="
			<graph gid='1'>                                                            
		  <axis>right</axis>
		  <title>Team Shots</title>              
		  <color>$playerColor</color>
		  <color_hover></color_hover>
		  <line_alpha>100</line_alpha> 
		  <line_width>2</line_width>                             
		  <fill_alpha></fill_alpha>
		  <fill_color></fill_color>
		  <balloon_color></balloon_color>
		  <balloon_alpha></balloon_alpha>                           
		  <balloon_text_color>#000000</balloon_text_color>             
		  <bullet></bullet>
		  <bullet_size></bullet_size>
		  <bullet_color></bullet_color>
		  <bullet_alpha></bullet_alpha>
		  <hidden></hidden>
		  <selected></selected>  
		  <balloon_text>
			<![CDATA[<div style='text-align:center'><b>\$ {value}</b>  $currentStat</div>]]>
		  </balloon_text>
		  <data_labels>
			<![CDATA[]]> 
		  </data_labels>  
		  <data_labels_text_color></data_labels_text_color>       
		  <data_labels_text_size></data_labels_text_size>         
		  <data_labels_position></data_labels_position>                 
		  <vertical_lines></vertical_lines>                       
		  <visible_in_legend>false</visible_in_legend>   
		</graph>";


}
$out =<<<EOT
<?xml version="1.0" encoding="UTF-8"?>
<!-- Value between [] brackets, for example [#FFFFFF] shows default value which is used if this parameter is not set -->
<!-- This means, that if you are happy with this value, you can delete this line at all and reduce file size -->
<!-- value or explanation between () brackets shows the range or type of values you should use for this parameter -->
<!-- the top left corner has coordinates x = 0, y = 0                                                                -->
<!-- "!" before x or y position (for example: <x>!20</x>) means that the coordinate will be calculated from the right side or the bottom -->

<settings> 
  <data_type>csv</data_type>                                     <!-- [xml] (xml / csv) -->
  <csv_separator></csv_separator>                             <!-- [;] (string) csv file data separator (you need it only if you are using csv file for your data) -->
  <skip_rows></skip_rows>                                     <!-- [0] (Number) if you are using csv data type, you can set the number of rows which should be skipped here -->       
  <font></font>                                               <!-- [Arial] (font name) use device fonts, such as Arial, Times New Roman, Tahoma, Verdana... -->
  <text_size>9</text_size>                                     <!-- [11] (Number) text size of all texts. Every text size can be set individually in the settings below -->
  <text_color></text_color>                                   <!-- [#000000] (hex color code) main text color. Every text color can be set individually in the settings below-->
  <decimals_separator></decimals_separator>                   <!-- [,] (string) decimal separator. Note, that this is for displaying data only. Decimals in data xml file must be separated with dot -->
  <thousands_separator></thousands_separator>                 <!-- [ ] (string) thousand separator. use "none" if you don't want to separate -->
  <digits_after_decimal>0</digits_after_decimal>              <!-- [] (Number) if your value has less digits after decimal then is set here, zeroes will be added -->
  <scientific_min></scientific_min>                           <!-- [0.000001] If absolute value of your number is equal or less then scientific_min, this number will be formatted using scientific notation, for example: 0.0000023 -> 2.3e-6 -->
  <scientific_max></scientific_max>                           <!-- [1000000000000000] If absolute value of your number is equal or bigger then scientific_max, this number will be formatted using scientific notation, for example: 15000000000000000 -> 1.5e16 -->
  <redraw></redraw>                                           <!-- [false] (true / false) if your chart's width or height is set in percents, and redraw is set to true, the chart will be redrawn then screen size changes -->
                                                              <!-- Legend, buttons labels will not be repositioned if you set your x and y values for these objects -->  
  <reload_data_interval></reload_data_interval>               <!-- [0] (Number) how often data should be reloaded (time in seconds) If you are using this feature I strongly recommend to turn off zoom function (set <zoomable>false</zoomable>) -->
  <preloader_on_reload></preloader_on_reload>                 <!-- [false] (true / false) Whether to show preloaded when data or settings are reloaded -->
  <add_time_stamp></add_time_stamp>                           <!-- [false] (true / false) if true, a unique number will be added every time flash loads data. Mainly this feature is useful if you set reload _data_interval >0 -->
  <precision></precision>                                     <!-- [2] (Number) shows how many numbers should be shown after comma for calculated values (percents, used only in stacked charts) -->
  <connect></connect>                                         <!-- [false] (true / false) whether to connect points if y data is missing -->
  <hide_bullets_count></hide_bullets_count>                   <!-- [] (Number) if there are more then hideBulletsCount points on the screen, bullets can be hidden, to avoid mess. Leave empty, or 0 to show bullets all the time. This rule doesn't influence if custom bullet is defined near y value, in data file -->
  <link_target></link_target>                                 <!-- [] (_blank, _top ...) -->
  <start_on_axis></start_on_axis>                             <!-- [true] (true / false) if set to false, graph is moved 1/2 of one series interval from Y axis -->
  <colors></colors>                                           <!-- [#FF0000,#0000FF,#00FF00,#FF9900,#CC00CC,#00CCCC,#33FF00,#990000,#000066,#555555] Colors of graphs. if the graph color is not set, color from this array will be used -->  
  <rescale_on_hide></rescale_on_hide>                         <!-- [true] (true/false) When you show or hide graphs, the chart recalculates min and max values (rescales the chart). If you don't want this, set this to false. -->
  <js_enabled></js_enabled>                                   <!-- [true] (true / false) In case you don't use any flash - JavaScript communication, you shuold set this setting to false - this will save some CPU and will disable the security warning message which appears when opening the chart from hard drive. -->

  <background>                                                <!-- BACKGROUND -->
    <color></color>                                           <!-- [#FFFFFF] (hex color code) Separate color codes with comas for gradient -->
    <alpha></alpha>                                           <!-- [0] (0 - 100) use 0 if you are using custom swf or jpg for background -->
    <border_color></border_color>                             <!-- [#000000] (hex color code) -->
    <border_alpha></border_alpha>                             <!-- [0] (0 - 100) -->
    <file></file>                                             <!-- [] (filename) swf or jpg file of a background. Do not use progressive jpg file, it will be not visible with flash player 7 -->
                                                              <!-- The chart will look for this file in "path" folder ("path" is set in HTML) -->
  </background>
  
 
  <plot_area>                                                 <!-- PLOT AREA (the area between axes) -->
    <color></color>                                           <!-- [#FFFFFF](hex color code) Separate color codes with comas for gradient-->
    <alpha></alpha>                                           <!-- [0] (0 - 100) if you want it to be different than background color, use bigger than 0 value -->
    <border_color></border_color>                             <!-- [#000000] (hex color code) -->                                        
    <border_alpha>100</border_alpha>                             <!-- [0] (0 - 100) -->
    <margins>                                                 <!-- plot area margins -->
      <left>40</left>                                           <!-- [60](Number / Number%) --> 
      <top>10</top>                                             <!-- [60](Number / Number%) --> 
      <right>60</right>                                         <!-- [60](Number / Number%) --> 
      <bottom>40</bottom>                                       <!-- [80](Number / Number%) --> 
    </margins>
  </plot_area>

  <scroller>
    <enabled></enabled>                                       <!-- [true] (true / false) whether to show scroller when chart is zoomed or not -->
    <y></y>                                                   <!-- [] (Number) Y position of scroller. If not set here, will be displayed above plot area -->    
    <color></color>                                           <!-- [#DADADA] (hex color code) scrollbar color. Separate color codes with comas for gradient -->
    <alpha></alpha>                                           <!-- [100] (Number) scrollbar alpha -->
    <bg_color></bg_color>                                     <!-- [#F0F0F0] (hex color code) scroller background color. Separate color codes with comas for gradient -->
    <bg_alpha></bg_alpha>                                     <!-- [100] (Number) scroller background alpha -->
    <height></height>                                         <!-- [10] (Number) scroller height -->    
  </scroller>
  
  <grid>                                                      <!-- GRID -->
    <x>                                                       <!-- vertical grid -->
      <enabled></enabled>                                     <!-- [true] (true / false) -->                                                     
      <color></color>                                         <!-- [#000000] (hex color code) -->
      <alpha></alpha>                                         <!-- [15] (0 - 100) -->
      <dashed></dashed>                                       <!-- [false](true / false) -->
      <dash_length></dash_length>                             <!-- [5] (Number) -->  
      <approx_count>10</approx_count>                           <!-- [4] (Number) approximate number of gridlines -->
    </x>
    <y_left>                                                  <!-- horizontal grid, Y left axis. Visible only if there is at least one graph assigned to left axis -->
      <enabled></enabled>                                     <!-- [true] (true / false) -->          
      <color></color>                                         <!-- [#000000] (hex color code) -->
      <alpha></alpha>                                         <!-- [15] (0 - 100) -->
      <dashed></dashed>                                       <!-- [false] (true / false) -->
      <dash_length></dash_length>                             <!-- [5] (Number) -->
      <approx_count></approx_count>                           <!-- [10] (Number) approximate number of gridlines -->
      <fill_color></fill_color>                               <!-- [#FFFFFF] (hex color code) every second area between gridlines will be filled with this color (you will need to set fill_alpha > 0) -->
      <fill_alpha></fill_alpha>                               <!-- [0] (0 - 100) opacity of fill -->      
    </y_left>
    <y_right>                                                 <!-- horizontal grid, Y right axis. Visible only if there is at least one graph assigned to right axis -->
      <enabled></enabled>                                     <!-- [true] (true / false) -->          
      <color></color>                                         <!-- [#000000] (hex color code) -->
      <alpha></alpha>                                         <!-- [15] (0 - 100) -->
      <dashed></dashed>                                       <!-- [false] (true / false) -->
      <dash_length></dash_length>                             <!-- [5] (Number) -->
      <approx_count></approx_count>                           <!-- [10] (Number) approximate number of gridlines -->
      <fill_color></fill_color>                               <!-- [#FFFFFF] (hex color code) every second area between gridlines will be filled with this color (you will need to set fill_alpha > 0) -->
      <fill_alpha></fill_alpha>                               <!-- [0] (0 - 100) opacity of fill -->      
    </y_right>        
  </grid>
  
  <values>                                                    <!-- VALUES -->
    <x>                                                       <!-- x axis -->
      <enabled></enabled>                                     <!-- [true] (true / false) -->
      <rotate>60</rotate>                                       <!-- [0] (0 - 90) angle of rotation. If you want to rotate by degree from 1 to 89, you must have font.swf file in fonts folder -->      
      <frequency></frequency>                                 <!-- [1] (Number) how often values should be placed, 1 - near every gridline, 2 - near every second gridline... -->
      <skip_first></skip_first>                               <!-- [false] (true / false) to skip or not first value -->
      <skip_last></skip_last>                                 <!-- [false] (true / false) to skip or not last value -->
      <color></color>                                         <!-- [text_color] (hex color code) -->
      <text_size></text_size>                                 <!-- [text_size] (Number) -->
      <inside></inside>                                       <!-- [false] (true / false) if set to true, axis values will be displayed inside plot area. This setting will not work for values rotated by 1-89 degrees (0 and 90 only) -->    
    </x>
    <y_left>                                                  <!-- y left axis -->
      <enabled></enabled>                                     <!-- [true] (true / false) -->
      <reverse></reverse>                                     <!-- [false] (true / false) whether to reverse this axis values or not. If set to true, values will start from biggest number and will end with a smallest number -->    
      <rotate></rotate>                                       <!-- [0] (0 - 90) angle of rotation. If you want to rotate by degree from 1 to 89, you must have font.swf file in fonts folder -->
      <min></min>                                             <!-- [] (Number) minimum value of this axis. If empty, this value will be calculated automatically. -->
      <max></max>                                             <!-- [] (Number) maximum value of this axis. If empty, this value will be calculated automatically -->
      <strict_min_max></strict_min_max>                       <!-- [false] (true / false) by default, if your values are bigger then defined max (or smaller then defined min), max and min is changed so that all the chart would fit to chart area. If you don't want this, set this option to true. -->
      <frequency></frequency>                                 <!-- [1] (Number) how often values should be placed, 1 - near every gridline, 2 - near every second gridline... -->
      <skip_first></skip_first>                               <!-- [true] (true / false) to skip or not first value -->
      <skip_last></skip_last>                                 <!-- [false] (true / false) to skip or not last value -->
      <color></color>                                         <!-- [text_color] (hex color code) --> 
      <text_size></text_size>                                 <!-- [text_size] (Number) -->
      <unit></unit>                                           <!-- [] (text) unit which will be added to values on y axis-->
      <unit_position></unit_position>                         <!-- [right] (left / right) -->
      <integers_only>true</integers_only>                         <!-- [false] (true / false) if set to true, values with decimals will be omitted -->
      <inside></inside>                                       <!-- [false] (true / false) if set to true, axis values will be displayed inside plot area. This setting will not work for values rotated by 1-89 degrees (0 and 90 only) -->
      <duration></duration>                                   <!-- [] (ss/mm/hh/DD) In case you want your axis to display formatted durations instead of numbers, you have to set the unit of the duration in your data file. For example, if your values in data file represents seconds, set "ss" here.-->
    </y_left>
    <y_right>                                                 <!-- y right axis -->
      <enabled></enabled>                                     <!-- [true] (true / false) -->
      <reverse></reverse>                                     <!-- [false] (true / false) whether to reverse this axis values or not. If set to true, values will start from biggest number and will end with a smallest number -->    
      <rotate></rotate>                                       <!-- [0] (0 - 90) angle of rotation. If you want to rotate by degree from 1 to 89, you must have font.swf file in fonts folder -->
      <min></min>                                             <!-- [] (Number) minimum value of this axis. If empty, this value will be calculated automatically -->
      <max></max>                                             <!-- [] (Number) maximum value of this axis. If empty, this value will be calculated automatically -->    
      <strict_min_max></strict_min_max>                       <!-- [false] (true / false) by default, if your values are bigger then defined max (or smaller then defined min), max and min is changed so that all the chart would fit to chart area. If you don't want this, set this option to true. -->
      <frequency></frequency>                                 <!-- [1] (Number) how often values should be placed, 1 - near every gridline, 2 - near every second gridline... -->
      <skip_first></skip_first>                               <!-- [true] (true / false) to skip or not first value -->
      <skip_last></skip_last>                                 <!-- [false] (true / false) to skip or not last value -->
      <color></color>                                         <!-- [text_color] (hex color code) -->
      <text_size>12</text_size>                                 <!-- [text_size] (Number) -->
      <unit></unit>                                           <!-- [] (text) unit which will be added to values on y axis-->
      <unit_position></unit_position>                         <!-- [right] (left / right) -->
      <integers_only>true</integers_only>                         <!-- [false] (true / false) if set to true, values with decimals will be omitted -->
      <inside></inside>                                       <!-- [false] (true / false) if set to true, axis values will be displayed inside plot area. This setting will not work for values rotated by 1-89 degrees (0 and 90 only) -->
      <duration></duration>                                   <!-- [] (ss/mm/hh/DD) In case you want your axis to display formatted durations instead of numbers, you have to set the unit of the duration in your data file. For example, if your values in data file represents seconds, set "ss" here.-->                  
    </y_right>
  </values>
  
  <axes>                                                      <!-- axes -->
    <x>                                                       <!-- X axis -->
      <color></color>                                         <!-- [#000000] (hex color code) -->
      <alpha></alpha>                                         <!-- [100] (0 - 100) -->
      <width>0</width>                                         <!-- [2] (Number) line width, 0 for hairline -->
      <tick_length></tick_length>                             <!-- [7] (Number) -->
    </x>
    <y_left>                                                  <!-- Y left axis, visible only if at least one graph is assigned to this axis -->
      <type></type>                                           <!-- [line] (line, stacked, 100% stacked) -->    
      <color></color>                                         <!-- [#000000] (hex color code) -->
      <alpha></alpha>                                         <!-- [100] (0 - 100) -->
      <width>0</width>                                         <!-- [2] (Number) line width, 0 for hairline -->
      <tick_length></tick_length>                             <!-- [7] (Number) -->
      <logarithmic></logarithmic>                             <!-- [false] (true / false) If set to true, this axis will use logarithmic scale instead of linear -->
    </y_left>
    <y_right>                                                 <!-- Y right axis, visible only if at least one graph is assigned to this axis -->
      <type></type>                                           <!-- [line] (line, stacked, 100% stacked) -->    
      <color></color>                                         <!-- [#000000] (hex color code) -->
      <alpha></alpha>                                         <!-- [100] (0 - 100) -->
      <width>0</width>                                         <!-- [2] (Number) line width, 0 for hairline -->
      <tick_length></tick_length>                             <!-- [7] (Number) -->
      <logarithmic></logarithmic>                             <!-- [false] (true / false) If set to true, this axis will use logarithmic scale instead of linear -->
    </y_right>
  </axes>  
  
  <indicator>                                                 <!-- INDICATOR -->
    <enabled></enabled>                                       <!-- [true] (true / false) -->
    <zoomable></zoomable>                                     <!-- [true] (true / false) -->
    <color></color>                                           <!-- [#BBBB00] (hex color code) line and x balloon background color -->
	 <unit>%</unit>  
    <line_alpha></line_alpha>                                 <!-- [100] (0 - 100) -->
    <selection_color></selection_color>                       <!-- [#BBBB00] (hex color code) -->
    <selection_alpha></selection_alpha>                       <!-- [25] (0 - 100) -->
    <x_balloon_enabled></x_balloon_enabled>                   <!-- [true] (true / false) -->
    <x_balloon_text_color></x_balloon_text_color>             <!-- [text_color] (hex color code) -->
  </indicator>
    
  <balloon>                                                   <!-- BALLOON -->
    <enabled></enabled>                                       <!-- [true] (true / false) -->
    <only_one></only_one>                                     <!-- [false] (true / false) if set to true, only one balloon at a time will be displayed -->
    <on_off></on_off>                                         <!-- [true] (true/false) whether it will be possible to turn on or off y balloons by clicking on a legend or on a graph -->
    <color></color>                                           <!-- [] (hex color code) balloon background color. If not set, graph.balloon_color will be used.  -->
    <alpha>65</alpha>                                           <!-- [] (0 - 100) balloon background opacity. If not set, graph.balloon_alpha will be used. -->
    <text_color></text_color>                                 <!-- [] (hex color code) baloon text color. If not set, graph.balloon_text_color will be used -->
    <text_size>14</text_size>                                   <!-- [text_size] (Number) -->
    <max_width></max_width>                                   <!-- [] (Number) The maximum width of a balloon. If not set, half width of plot area will be used -->
    <corner_radius>5</corner_radius>                           <!-- [0] (Number) Corner radius of a balloon. If you set it > 0, the balloon will not display arrow -->
    <border_width>2</border_width>                             <!-- [0] (Number) -->
    <border_alpha></border_alpha>                             <!-- [balloon.alpha] (Number) -->
    <border_color></border_color>                             <!-- [balloon.color] (hex color code) -->
  </balloon>    
    
  <legend>                                                    <!-- LEGEND -->
    <enabled></enabled>                                       <!-- [true] (true / false) -->
    <x></x>                                                   <!-- [] (Number / Number% / !Number) if empty, will be equal to left margin -->
    <y></y>                                                   <!-- [] (Number / Number% / !Number) if empty, will be 20px below x axis values -->
    <width></width>                                           <!-- [] (Number / Number%) if empty, will be equal to plot area width -->
    <max_columns></max_columns>                               <!-- [] (Number) the maximum number of columns in the legend --> 
    <color></color>                                           <!-- [#FFFFFF] (hex color code) background color. Separate color codes with comas for gradient -->
    <alpha></alpha>                                           <!-- [0] (0 - 100) background alpha -->
    <border_color></border_color>                             <!-- [#000000] (hex color code) border color -->
    <border_alpha></border_alpha>                             <!-- [0] (0 - 100) border alpha -->
    <text_color></text_color>                                 <!-- [text_color] (hex color code) -->   
    <text_color_hover></text_color_hover>                     <!-- [#BBBB00] (hex color code) -->    
    <text_size></text_size>                                   <!-- [text_size] (Number) -->
    <spacing></spacing>                                       <!-- [10] (Number) vertical and horizontal gap between legend entries -->
    <margins></margins>                                       <!-- [0] (Number) legend margins (space between legend border and legend entries, recommended to use only if legend border is visible or background color is different from chart area background color) -->    
    <graph_on_off></graph_on_off>                             <!-- [true] (true / false) if true, color box gains "checkbox" function - it is possible to make graphs visible/invisible by clicking on this checkbox -->
    <reverse_order></reverse_order>                           <!-- [false] (true / false) whether to sort legend entries in a reverse order -->
    <align></align>                                           <!-- [left] (left / center / right) alignment of legend entries -->    
    <key>                                                     <!-- KEY (the color box near every legend entry) -->
      <size></size>                                           <!-- [16] (Number) key size-->
      <border_color></border_color>                           <!-- [] (hex color code) leave empty if you don't want to have border-->
      <key_mark_color></key_mark_color>                       <!-- [#FFFFFF] (hex color code) key tick mark color -->
    </key>
    <values>                                                  <!-- VALUES -->          
      <enabled>true</enabled>                                 <!-- [false] (true / false) whether to show values near legend entries or not -->
      <width>44</width>                                       <!-- [80] (Number) width of text field for value -->
      <align>left</align>                                     <!-- [right] (right / left) -->
      <text><![CDATA[:{value}]]></text>                     <!-- [{value}] ({title} {value} {series} {description} {percents}) You can format any text: {value} will be replaced with value, {description} - with description and so on. You can add your own text or html code too. -->
     </values>
  </legend>
  
  <vertical_lines>                                            <!-- line chart can also display vertical lines/columns (set <vertical_lines>true</vertical_lines> in graph settings for that). If you also set <line_alpha>0</line_alpha> your line chart will become column chart -->
    <width></width>                                           <!-- [0] (0 - 100) width of vertical line in percents. 0 for hairline. Set > 0 if you want to have column -->
    <alpha></alpha>                                           <!-- [100] (0 - 100) -->
    <clustered></clustered>                                   <!-- [false] in case you have more then one graph with vertical lines enabled, you might want to place your columns next to each other, set true for that. -->
    <mask></mask>                                             <!-- [true] (true / false) as line chart by default starts on axis, and your column width is >0, then some part of first and last column will be outside plot area (incase you don't set <start_on_axis>false</false> Mask will cut off the part outside the plot area. Set to false if you don't want this. -->
  </vertical_lines>    
  
  <zoom_out_button>
    <x></x>                                                   <!-- [] (Number / Number% / !Number) x position of zoom out button, if not defined, will be aligned to right of plot area -->
    <y></y>                                                   <!-- [] (Number / Number% / !Number) y position of zoom out button, if not defined, will be aligned to top of plot area -->
    <color></color>                                           <!-- [#BBBB00] (hex color code) background color -->
    <alpha></alpha>                                           <!-- [0] (0 - 100) background alpha -->
    <text_color></text_color>                                 <!-- [text_color] (hex color code) button text and magnifying glass icon color -->
    <text_color_hover></text_color_hover>                     <!-- [#BBBB00] (hex color code) button text and magnifying glass icon roll over color -->    
    <text_size></text_size>                                   <!-- [text_size] (Number) button text size -->
    <text></text>                                             <!-- [Show all] (text) -->    
  </zoom_out_button> 
   
  <help>                                                      <!-- HELP button and balloon -->  
    <button>                                                  <!-- help button is only visible if balloon text is defined -->
      <x></x>                                                 <!-- [] (Number / Number% / !Number) x position of help button, if not defined, will be aligned to right of chart area -->
      <y></y>                                                 <!-- [] (Number / Number% / !Number) y position of help button, if not defined, will be aligned to top of chart area -->
      <color></color>                                         <!-- [#000000] (hex color code) background color -->
      <alpha></alpha>                                         <!-- [100] (0 - 100) background alpha -->
      <text_color></text_color>                               <!-- [#FFFFFF] (hex color code) button text color -->
      <text_color_hover></text_color_hover>                   <!-- [#BBBB00](hex color code) button text roll over color -->    
      <text_size></text_size>                                 <!-- [] (Number) button text size -->
      <text></text>                                           <!-- [?] (text) -->                                 
    </button>    
    <balloon>                                                 <!-- help balloon -->
      <color></color>                                         <!-- [#000000] (hex color code) background color -->
      <alpha></alpha>                                         <!-- [100] (0 - 100) background alpha -->
      <width></width>                                         <!-- [300] (Number) -->
      <text_color></text_color>                               <!-- [#FFFFFF] (hex color code) button text color -->
      <text_size></text_size>                                 <!-- [] (Number) button text size -->
      <text><![CDATA[]]></text>                               <!-- [] (text) some html tags may be used (supports <b>, <i>, <u>, <font>, <br/>. Enter text between []: <![CDATA[your <b>bold</b> and <i>italic</i> text]]>-->
    </balloon>    
  </help> 
  
  <export_as_image>                                           <!-- export_as_image feature works only on a web server -->
    <file>/amline/export.php</file>                                             <!-- [] (filename) if you set filename here, context menu (then user right clicks on flash movie) "Export as image" will appear. This will allow user to export chart as an image. Collected image data will be posted to this file name (use amline/export.php or amline/export.aspx) -->
    <target>_blank</target>                                         <!-- [] (_blank, _top ...) target of a window in which export file must be called -->
    <x>510</x>                                                   <!-- [0] (Number / Number% / !Number) x position of "Collecting data" text -->
    <y>1</y>                                                   <!-- [] (Number / Number% / !Number) y position of "Collecting data" text. If not set, will be aligned to the bottom of flash movie -->
    <color></color>                                           <!-- [#BBBB00] (hex color code) background color of "Collecting data" text -->
    <alpha></alpha>                                           <!-- [0] (0 - 100) background alpha -->
    <text_color></text_color>                                 <!-- [text_color] (hex color code) -->
    <text_size></text_size>                                   <!-- [text_size] (Number) -->
  </export_as_image>
  
  <error_messages>                                            <!-- "error_messages" settings will be applied for all error messages except the one which is showed if settings file wasn't found -->
    <enabled></enabled>                                       <!-- [true] (true / false) -->
    <x></x>                                                   <!-- [] (Number / Number% / !Number) x position of error message. If not set, will be aligned to the center -->
    <y></y>                                                   <!-- [] (Number / Number% / !Number) y position of error message. If not set, will be aligned to the center -->
    <color></color>                                           <!-- [#BBBB00] (hex color code) background color of error message. Separate color codes with comas for gradient -->
    <alpha></alpha>                                           <!-- [100] (0 - 100) background alpha -->
    <text_color></text_color>                                 <!-- [#FFFFFF] (hex color code) -->
    <text_size></text_size>                                   <!-- [text_size] (Number) -->
  </error_messages>  
  
  <strings>
    <no_data></no_data>                                       <!-- [No data for selected period] (text) if data for selected period is missing, this message will be displayed -->
    <export_as_image></export_as_image>                       <!-- [Export as image] (text) text for right click menu -->
    <error_in_data_file></error_in_data_file>                 <!-- [Error in data file] (text) this text is displayed if there is an error in data file or there is no data in file. "There is no data" means that there should actually be at least one space in data file. If data file will be completly empty, it will display "error loading file" text -->
    <collecting_data></collecting_data>                       <!-- [Collecting data] (text) this text is displayed while exporting chart to an image -->
    <wrong_zoom_value></wrong_zoom_value>                     <!-- [Incorrect values] (text) this text is displayed if you set zoom through JavaScript and entered from or to value was not find between series -->
    <!-- the strings below are only important if you format your axis values as durations -->
    <ss></ss>                                                <!-- [] unit of seconds -->
    <mm></mm>                                                <!-- [:] unit of minutes -->
    <hh></hh>                                                <!-- [:] unit of hours -->
    <DD></DD>                                                <!-- [d. ] unit of days -->        
  </strings>
  
  <context_menu>                                              <!-- context menu allows you to controll right-click menu items. You can add custom menu items to create custom controls -->                                                              
                                                              <!-- "function_name" specifies JavaScript function which will be called when user clicks on this menu. You can pass variables, for example: function_name="alert('something')" -->
                                                              <!-- "title" sets menu item text. Do not use for title: Show all, Zoom in, Zoom out, Print, Settings... -->
                                                              <!-- you can have any number of custom menus. Uncomment the line below to enable this menu and add apropriate JS function to your html file. -->
     
     <!-- <menu function_name="printChart" title="Print chart"></menu> -->
     
     <default_items>
       <zoom></zoom>                                          <!-- [false] (true / false) to show or not flash players zoom menu -->
       <print></print>                                        <!-- [true] (true / false) to show or not flash players print menu -->
     </default_items>
  </context_menu>  
  
  
  
  
  <labels>                                                    <!-- LABELS -->
                                                              <!-- you can add as many labels as you want. Some html tags supported: <b>, <i>, <u>, <font>, <a href=""> -->
                                                              <!-- labels can also be added in data xml file, using exactly the same structure like it is here -->
    <label lid="0">
      <x></x>                                                 <!-- [0] (Number / Number% / !Number) -->
      <y>20</y>                                               <!-- [0] (Number / Number% / !Number) -->
      <rotate></rotate>                                       <!-- [false] (true / false) -->
      <width>650</width>                                      <!-- [] (Number / Number%) if empty, will stretch from left to right untill label fits -->
      <align>center</align>                                   <!-- [left] (left / center / right) -->  
      <text_color></text_color>                               <!-- [text_color] (hex color code) button text color -->
      <text_size></text_size>                                 <!-- [text_size](Number) button text size -->
      <text>                                                  <!-- [] (text) html tags may be used (supports <b>, <i>, <u>, <font>, <a href="">, <br/>. Enter text between []: <![CDATA[your <b>bold</b> and <i>italic</i> text]]>-->
        <![CDATA[<b>$title</b>]]>
      </text>        
    </label>    
    <label lid="1">
      <text><![CDATA[<b>$title</b>]]></text>
      <x>560</x>
      <y>220</y>
      <text_size>24</text_size>
      <rotate>1</rotate>
    </label>

  </labels>
  
  
  <graphs>                                                    <!-- GRAPHS SETTINGS. These settings can also be specified in data file, as attributes of <graph>, in this case you can delete everything from <graphs> to </graphs> (including) -->
                                                              <!-- It is recommended to have graph settings here if you don't want to mix data with other params -->
                                                              <!-- copy <graph>...</graph> (including) as many times as many graphs you have and edit settings individually -->
                                                              <!-- if graph settings are defined both here and in data file, the ones from data file are used -->

    $out2

  </graphs>  
  
	<guides>	                                                 <!-- guides are straight lines drawn through all plot area at a give value. Can also be filled with color -->
	 <max_min></max_min>                                       <!-- [false] (true / false) whether to include guides' values when calculating min and max of a chart -->	
	 <guide>                                                   <!-- there can be any number of quides. guides can also be set in data xml file, using the same syntax as here -->
	   <axis></axis>                                           <!-- [left] (left / right) y axis of a guide. There should be at least one graph assigned to this axis in order guide to be visible --> 
	   <start_value></start_value>                             <!-- (Number) value at which guide should be placed -->
	   <end_value></end_value>                                 <!-- (Number) if you set value here too, another quide will be drawn. If you set fill alpha > 0, then the area between these quides will be filled with color -->
	   <title></title>                                         <!-- [] (String) text which will be displayed near the guide -->
	   <width></width>                                         <!-- [0] (Number) width of a guide line (0 for hairline) -->
	   <color></color>                                         <!-- [#000000] (hex color code) color of guide line -->   
	   <alpha></alpha>                                         <!-- [100] (0 - 100) opacity of guide line -->
	   <fill_color></fill_color>                               <!-- [guide.color] (hex color code) fill color. If not defined, color of a guide will be used. Separate color codes with comas for gradient -->
	   <fill_alpha></fill_alpha>                               <!-- [0] (0 - 100) opacity of a fill -->                                  
	   <inside></inside>                                       <!-- [values.y_{axis}.inside] whether to place title inside plot area -->
	   <centered></centered>                                   <!-- [true] (true / false) if you have start and end values defined, title can be placed in the middle between these values. If false, it will be placed near start_value -->
	   <rotate></rotate>                                       <!-- [values.y_{axis}.rotate](0 - 90) angle of rotation of title -->
	   <text_size></text_size>                                 <!-- [values.y_{axis}.text_size] (Number) -->
	   <text_color></text_color>                               <!-- [values.y_{axis}.color](hex color code) -->
     <dashed></dashed>                                       <!-- [false] (true / false) -->
     <dash_length></dash_length>                             <!-- [5] (Number) -->     	   
	 </guide>  
	</guides>
	
	<!-- DATA can also be set in settings file. Just place data in CSV or XML format between <data></data> -->
	
	<!--<data>
	</data> -->	
</settings>

EOT;
echo $out;
	}
}

/* End of file welcome.php */
/* Location: ./application/controllers/welcome.php */