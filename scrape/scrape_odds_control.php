<?php
include_once 'scrape_odds.php';
$start=new Scrape(0,2);
//$start->scrape_day("http://gd2.mlb.com/components/game/mlb/year_2012/month_04/day_07/master_scoreboard.xml","2012-04-07");
$start->getPinnacleOdds();
$start->getBetolineOdds();
$start->getBetolineOddsMLB();
$start->getPinnacleOddsMLB();
//$start->getWilliamHillOdds();
?>