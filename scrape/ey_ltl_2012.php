<?php
include_once 'ey_ltl_points.php';
$start=new Output(0,2);
//$start->scrape_day("http://gd2.mlb.com/components/game/mlb/year_2012/month_04/day_07/master_scoreboard.xml","2012-04-07");
$start->get_points_by_player();
?>