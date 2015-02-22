<?php
include_once 'mlb_scrape.php';
$start=new Scrape(0,2);
//$start->scrape_day("http://gd2.mlb.com/components/game/mlb/year_2012/month_04/day_07/master_scoreboard.xml","2012-04-07");
$start->scrape_date_range(date("Y-m-d",strtotime(date("Y-m-d") . " -2 days")),date("Y-m-d",strtotime(date("Y-m-d") . " +5 days")));

?>