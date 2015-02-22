<?php 
$out2 = <<<EOT
	<?xml version="1.0" encoding="UTF-8"?>
	<!-- Value between [] brackets, for example [#FFFFFF] shows default value which is used if this parameter is not set -->
	<!-- This means, that if you are happy with this value, you can delete this line at all and reduce file size -->
	<!-- value or explanation between () brackets shows the range or type of values you should use for this parameter -->
	<!-- the top left corner has coordinates x = 0, y = 0                                                                -->
	<!-- "!" before x or y position (for example: <x>!20</x>) means that the coordinate will be calculated from the right side or the bottom -->

<settings><hide_bullets_count>18</hide_bullets_count><data_type>csv</data_type><plot_area><margins><left>50</left><right>40</right><top>55</top><bottom>30</bottom></margins></plot_area><grid><x><alpha>10</alpha><approx_count>8</approx_count></x><y_left><alpha>10</alpha></y_left></grid><axes><x><width>1</width><color>0D8ECF</color></x><y_left><width>1</width><color>0D8ECF</color></y_left></axes><indicator><color>0D8ECF</color><x_balloon_text_color>FFFFFF</x_balloon_text_color><line_alpha>50</line_alpha><selection_color>0D8ECF</selection_color><selection_alpha>20</selection_alpha></indicator><zoom_out_button><text_color_hover>FF0F00</text_color_hover></zoom_out_button><help><button><color>FCD202</color><text_color>000000</text_color><text_color_hover>FF0F00</text_color_hover></button><balloon><color>FCD202</color><text_color>000000</text_color></balloon></help><graphs><graph gid='0'><title>Anomaly</title><color>0D8ECF</color><color_hover>FF0F00</color_hover><selected>0</selected></graph><graph gid='1'><title>Smoothed</title><color>B0DE09</color><color_hover>FF0F00</color_hover><line_width>2</line_width><fill_alpha>30</fill_alpha><bullet>round</bullet></graph></graphs><labels><label lid='0'><text><![CDATA[<b>Temperature anomaly</b>]]></text><y>15</y><text_size>13</text_size><align>center</align></label></labels></settings>

EOT;
echo $out2;
?>
