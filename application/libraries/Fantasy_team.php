<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
* Name:  Fantasy_team
*
* Author: Michael Gross
*		  mikegross@gmail.com
*
* This object represents a fantasy team.
* At its most basic level, it can just be an array of player objects. As it gets more complex, it can contain attributes like fantasy team name, and 
* as it gets even more complex, it can contain history of trades and drops / pickups
* 
*
*/

class Fantasy_team extends CI_Model
{
	public $players = Array();
	public $nightlyPoints = Array();
	public $totalGamesPlayed;
	public $fantasy_team_name;
	//public $teamSlots;
	
	public function __construct($nhl_ids,$teamSlots)
	{
		$this->load->library('hockey_player');
		$i=0;
		foreach ($nhl_ids as $nhl_id)
		{
			$this->players[$i] = new Hockey_player($nhl_id['nhl_id'],$nhl_id['position'],$nhl_id['status'],$teamSlots);
			$i++;
		}
	}
	
	
	//here we do the calculations for a fantasy team
	public function create_stats_and_fantasy_points_for_team($from_date,$to_date,$projection_from_date, $projection_to_date)
	{
		$teamSlots = unserialize($_SESSION['fantasyLeague']);
		$this->nightlyPoints = Array();
		foreach ($this->players as $player)
		{
			$player->reset_projections();
			//create the projection of the basic statistics for the players
			$daily_projection = Array();
			
			$daily_projection = $player->create_projection_for_player($projection_from_date,$projection_to_date,0, $teamSlots);
			
			$fantasy_points = $player->create_fantasy_points_from_projection($daily_projection, $teamSlots);
			
			//for a given date, take a projection and a fantasy projection and add it to the player object array
			//$player->set_projection_by_date($today,$daily_projection,$fantasy_points);
			
			//for the remainder of the season, set the projections for each player
			$player->set_projections_from_today_to_end($from_date,$to_date,$daily_projection,$fantasy_points);
			
		}
		$this->order_team_for_dates($from_date,$to_date);
		$this->calculate_nightly_fantasy_points($from_date,$to_date);
	}
		
	public function order_team_for_dates($startDate, $endDate)
	{
		$currentDay = $startDate;
		while (strtotime($currentDay) <= strtotime($endDate))
		{
			$this->order_team_for_day($currentDay,$team);
			$currentDay = date("Y-m-d",strtotime($currentDay . " +1 day"));
			$_SESSION['currentDay'] = $currentDay;
		}
	}

	public function order_team_for_day($date)
	{
		//$_SESSION['currentTime'] = time() - $_SESSION['currentTime'];
		//$_SESSION['outputTest'] .= "time=".$_SESSION['currentTime'];
		$teamSlots = unserialize($_SESSION['fantasyLeague']);
		$i=0;
		$daysTeam = Array();
		//get the projected points that the player will get based on whether they are playing
		foreach ($this->players as $player)
		{
			$daysTeam[$i]['player_name'] = $player->last_name;
			$daysTeam[$i]['position'] = $player->position2;
			$daysTeam[$i]['player'] = $player;
			$curteam = $player->team_name;
			$daysTeam[$i]['points'] = $player->proj_by_date[$date]['fantasyPoints']['total'];
			$daysTeam[$i]['playing'] = $player->proj_by_date[$date]['playing'];
			$i++;
		}
		
		//this will order the array
		foreach ($daysTeam as $key => $row) {
			$points[$key]  = $row['points']; 
			// of course, replace 0 with whatever is the date field's index
		}
		//array_multisort($points, SORT_DESC, $daysTeam);
		foreach ($daysTeam as $key => $row) {
			$position[$key]  = $row['position']; 
			// of course, replace 0 with whatever is the date field's index
		}
		array_multisort($position, $points, SORT_DESC, $daysTeam);

		//echo "<br/><br/>";
		//print_r($daysTeam);		
		//based on the slots on the team, remove people who cannot play since they are at the bottom of the list
		$posCount = array();
		foreach ($daysTeam as $key => $player)
		{
			foreach ($teamSlots->slots as $key2 => $slotPositionCount)
			{
				//first check to see if the player is injured or suspended for this period
				if (($player['player']->injury_suspend_status == "IR" || $player['player']->injury_suspend_status == "NA") && (strtotime($date) >= strtotime($player['player']->injury_suspend_start_date) && strtotime($date) <= strtotime($player['player']->injury_suspend_end_date)))
				{
					$player['player']->set_playing_status($date,$player['player']->injury_suspend_status);	
					$player['player']->set_playing_slot($date,$player['player']->injury_suspend_status);	
				}
				//then check whether they should play in a slot
				elseif ($player['position'] == $key2 && $player['playing'] != 0)
				{
					$posCount[$key2]++;
					if ($posCount[$key2] > $slotPositionCount)
					{
						$player['player']->set_playing_status($date,"BN");
						$player['player']->set_playing_slot($date,"BN");
					}
					else
					{
						$player['player']->set_playing_status($date,"Y");
						$player['player']->set_playing_slot($date,$key2);
					}
				}
				elseif ($player['playing'] == 0)
				{
					$daysTeam[$key]['player']->set_playing_status($date,"N");
					$player['player']->set_playing_slot($date,"");
				}
			}
		}
		
		
		//check to see if they can play in a group spot (for the players currently on the bench). A group spot is like a utility that can be a forward or defense
		
		//first go through all the different types of groups
		foreach($teamSlots->positionGroups as $group=>$positions_in_group)
		{
			$i=0;
			//go through each position in each group
			foreach($positions_in_group as $position_in_group)
			{
				//build an array of players in that group who are on the bench currently
				foreach ($daysTeam as $key => $player)
				{
					if (($player['position'] == $position_in_group) && ($player['player']->proj_by_date[$date]['playingStatus']=="BN"))
					{	
						$currentGroup[$i] = $player;
						$i++;
					}
				}
			}
			
			//this will order the array of the players in a specific group who are on the bench by their points
			foreach ($currentGroup as $key => $row) {
				$points[$key]  = $row['points']; 
			}

			array_multisort($points, SORT_DESC, $currentGroup);
			
			foreach ($currentGroup as $key => $player)
			{
				$posCount[$group]++;
				if ($posCount[$group] > $teamSlots->slots[$group])
				{

				}
				else
				{
					$player['player']->set_playing_status($date,"Y");
					$player['player']->set_playing_slot($date,$group);
				}
			}
		
		}
		
		//echo "DAYS TEAM LAST<br/><br/>";
		//print_r($daysTeam);
	}
	
	//here we can directly set the team fantasy points for a given night, but it is best to calculate it from the below function called calculate_nightly_fantasy_points
	public function set_nightly_points($date,$fantasyPoints)
	{
		$this->nightlyPoints[$date] = $fantasyPoints;
	}
	
	
	//this will calculate the total fantasy points for the team on a given night and place it into the nightlyPoints array
	public function calculate_nightly_fantasy_points($startDate,$endDate)
	{
		$this->totalGamesPlayed=0;
		$currentDay = $startDate;
		while (strtotime($currentDay) <= strtotime($endDate))
		{
			//go through each player on each day
			$this->nightlyPoints[$currentDay] = 0;
			foreach($this->players as $player)
			{
				//if the player is playing and is not on the bench, add his score to the overall sum
				if ($player->proj_by_date[$currentDay]['playingStatus']=='Y')
				{
					$this->nightlyPoints[$currentDay] += $player->proj_by_date[$currentDay]['fantasyPoints']['total'];
					$this->totalGamesPlayed++;
				}
			}
			$currentDay = date("Y-m-d",strtotime($currentDay . " +1 day"));
		}
	}
	
	//builds the html for the fantasy summary and the player stats

	public function get_summary_and_player_html($compare_bool, $compare_team, $fantasy_league, $trade_bool, $primary_team_name)
	{
		$urlArr = Array();
		$i=0;
		$totalGP=0;
		$totalPts=0;
		$totalBN=0;
		$tempArr = array();
		$old_players = array();
		$number_of_added_players = 0;
		foreach ($this->players as $player)
		{
			
			$tempArr[$i]['Position'] = $player->position2;
			$tempArr[$i]['GP'] = $player->get_num_games_played();
			$tempArr[$i]['fantasyPoints'] = $player->get_total_points();
			$tempArr[$i]['BN'] = $player->get_num_bench_games();
			$tempArr[$i]['imgURL'] = $player->image_url;
			$tempArr[$i]['injury_suspend_status'] = $player->injury_suspend_status;
			$tempArr[$i]['lastName'] = $player->last_name;
			$tempArr[$i]['nhlid'] = $player->id;
			
			$tempArr[$i]['tooltipHTML'] = $this->build_tooltip_html($player,false);
			$tempArr[$i]['compare_player_exists'] = false;

			//if we are making a comparison to a possible team change, then show the positive or negative change of each player
			if ($compare_bool == true)
			{
				$tempArr[$i]['comparePoints']=0;
				$tempArr[$i]['compareGP']=0;
				$tempArr[$i]['compareBN']=0;
				foreach ($compare_team->players as $compare_player)
				{
					if ($compare_player->id == $player->id)
					{
						$tempArr[$i]['comparePoints']=number_format($tempArr[$i]['fantasyPoints']-$compare_player->get_total_points(),1);
						$tempArr[$i]['compareGP']=number_format($tempArr[$i]['GP']-$compare_player->get_num_games_played(),1);
						$tempArr[$i]['compareBN']=number_format($tempArr[$i]['BN']-$compare_player->get_num_bench_games(),1);
						$tempArr[$i]['tooltipHTML'] = $this->build_tooltip_html($player,true,$compare_player);
						$tempArr[$i]['compare_player_exists'] = true;
					}
				}
				if ($tempArr[$i]['compare_player_exists'] == false)
				{
					$number_of_added_players++;
				}
			}
			
			//this is a little complex - if it is not the compare team, but the top team is being compared, see which players were dropped 
			//so, when it is cycling through the top team, identify which players don't exist in the bottom team
			//(so that they can be highlighted)
			elseif (isset($compare_team))
			{
				foreach($compare_team->players as $compare_player)
				{
					if ($compare_player->id == $player->id)
					{
						$tempArr[$i]['compare_player_exists'] = true;
					}
				}
			}
			$i++;
		}
		
		//calculate total amount of points for each of the players who have been removed
		//figure out the average based on the number of players added
		if ($compare_bool == true)
		{
			$compare_points_total = 0;
			$compare_GP_total = 0;
			$compare_BN_total = 0;
			$compare_player_array = Array();
			$i=0;
			foreach ($compare_team->players as $compare_player)
			{
				$comparePlayerExists = false;
				foreach ($this->players as $player)
				{
					if ($player->id == $compare_player->id)
					{
						$comparePlayerExists = true;
					}
				}
				if ($comparePlayerExists == false)
				{
					$compare_points_total += $compare_player->get_total_points();
					$compare_GP_total += $compare_player->get_num_games_played();
					$compare_BN_total += $compare_player->get_num_bench_games();
					$compare_player_array[$i] = $compare_player;
					$i++;
				}
			}
			if ($number_of_added_players > 0)
			{
				$compare_points_avg = $compare_points_total/$number_of_added_players;
				$compare_GP_avg = $compare_GP_total/$number_of_added_players;
				$compare_BN_avg = $compare_BN_total/$number_of_added_players;
			}
			foreach ($tempArr as $key=>$player)
			{
				if ($player['compare_player_exists'] == false)
				{
					$tempArr[$key]['comparePoints']=number_format($player['fantasyPoints']-$compare_points_avg,1);
					$tempArr[$key]['compareGP']=number_format($player['GP']-$compare_GP_avg,1);
					$tempArr[$key]['compareBN']=number_format($player['BN']-$compare_BN_avg,1);
					if ($number_of_added_players > 0)
					{
						foreach($this->players as $player_obj)
						{
							if ($player_obj->id == $tempArr[$key]['nhlid'])
							{
								$tempArr[$key]['tooltipHTML'] = $this->build_tooltip_html($player_obj,true,$compare_player_array, $number_of_added_players);
							}
						}
					}
					//echo $player['lastName'].$player['fantasyPoints'].":".$player['comparePoints'];
				}
			}
		}
		

		
		foreach ($tempArr as $key2 => $row) {
			$injury_suspend_status[$key2] = $row['injury_suspend_status']; 
		}
			
		foreach ($tempArr as $key2 => $row) {
			$position[$key2]  = $row['Position']; 
		}
		foreach ($tempArr as $key2 => $row) {
			$fantasyPoints[$key2]  = $row['fantasyPoints']; 
		}
		array_multisort($injury_suspend_status, SORT_ASC, $position, SORT_DESC,$fantasyPoints, SORT_DESC, $tempArr);	
		
		$i=0;
		foreach ($tempArr as $player)
		{
			if ($compare_bool == true)
			{
				$comparePoints = null;
				$compareGP = null;
				$compareBN = null;
				if ($player['comparePoints']>0)
				{
					$comparePoints = "<span class='positiveCompare'> (+".$player['comparePoints'].")</span>";
				}
				elseif ($player['comparePoints']<0)
				{
					$comparePoints = "<span class='negativeCompare'> (".$player['comparePoints'].")</span>";
				}
				if ($player['compareGP']>0)
				{
					$compareGP = "<span class='positiveCompare'> (+".$player['compareGP'].")</span>";
				}
				elseif ($player['compareGP']<0)
				{
					$compareGP = "<span class='negativeCompare'> (".$player['compareGP'].")</span>";
				}
				if ($player['compareBN']>0)
				{
					$compareBN = "<span class='negativeCompare'> (+".$player['compareBN'].")</span>";
				}
				elseif ($player['compareBN']<0)
				{
					$compareBN = "<span class='positiveCompare'> (".$player['compareBN'].")</span>";
				}
			}
			$playerGP = $player['GP'];
			$playerPts = number_format($player['fantasyPoints'],1,".",",");
			$playerBN = $player['BN'];
			$IRtext = "";
			if ($player['injury_suspend_status']=="IR")
			{
				$IRtext = " (IR)";
			}
			
			
			//this checks to see if the player was added and adds him to the newPlayer html class if so - to get a green border, it also checks to see if a 
			//player was removed from the above team and makes his border red
			if ($player['compare_player_exists'] == true || !isset($compare_team))
			{
				$urlArr['players'][$i]="<div class='playerDivs'>";
			}
			elseif ($compare_bool == true)
			{
				$urlArr['players'][$i]="<div class='playerDivs newPlayer'>";
			}
			else
			{
				$urlArr['players'][$i]="<div class='playerDivs removedPlayer'>";
			}
			$urlArr['players'][$i].="<div class='playerNameDiv'>".$player['Position'].$IRtext."</div>";			
			$urlArr['players'][$i].="<div class='playerNameDiv'>".$player['lastName']."</div>";
			$urlArr['players'][$i].="<img src='".$player['imgURL']."' class='playerImageID'/>";
			$urlArr['players'][$i].="<div class='newToolTip' >".$player['tooltipHTML']."</div>";
			$urlArr['players'][$i].="<div>Pts: ".$playerPts."<br />".$comparePoints."</div>";
			$urlArr['players'][$i].="<div>GP: ".$playerGP."<br />".$compareGP."</div>";
			$urlArr['players'][$i].="<div>BN: ".$playerBN."<br />".$compareBN."</div>";
			if ($trade_bool == true)
			{
				$checkbox_div_string = "trade";
			}
			if (!isset($compare_team))
			{	
				$urlArr['players'][$i].="<div class='playerCheckboxDiv'><input type='checkbox' name='dropCheck".$checkbox_div_string."[".$player['nhlid']."]' id='check".$player['nhlid']."' value = '".$player['nhlid']."'/><label for='check".$player['nhlid']."'>Drop</label></div>";
			}
			$urlArr['players'][$i].="</div>";
			
			$totalGP += $playerGP;
			$totalPts += $playerPts;
			$totalBN += $playerBN;
			//echo $urlArr[$i];
			$i++;
		}
		if ($compare_bool == true)
		{
			$compareTotalGP = 0;
			$compareTotalFantasyPoints =0;
			$compareTotalBenchGames = 0;
			foreach ($compare_team->players as $compare_player)
			{
				$compareTotalGP += $compare_player->get_num_games_played();
				$compareTotalFantasyPoints += $compare_player->get_total_points();
				$compareTotalBenchGames += $compare_player->get_num_bench_games();
			}
			$totalGPCompare = number_format($totalGP-$compareTotalGP,1);
			$totalFantasyCompare = number_format($totalPts-$compareTotalFantasyPoints,1);
			$totalBNCompare = number_format($totalBN-$compareTotalBenchGames,1);
			if ($totalGPCompare>0)
			{
				$GPCompareSpan = "<span class='positiveCompare'> (+$totalGPCompare)</span>";
			}
			elseif ($totalGPCompare<0)
			{
				$GPCompareSpan = "<span class='negativeCompare'> ($totalGPCompare)</span>";
			}
			if ($totalFantasyCompare>0)
			{
				$FPointsCompareSpan = "<span class='positiveCompare'> (+$totalFantasyCompare)</span>";
			}
			elseif ($totalFantasyCompare<0)
			{
				$FPointsCompareSpan = "<span class='negativeCompare'> ($totalFantasyCompare)</span>";
			}
			if ($totalBNCompare>0)
			{
				$BNCompareSpan = "<span class='negativeCompare'> (+$totalBNCompare)</span>";
			}
			elseif ($totalBNCompare<0)
			{
				$BNCompareSpan = "<span class='positiveCompare'> ($totalBNCompare)</span>";
			}
		}
		//$urlArr['summary']= "<div class='summaryTitleDiv'>Projected Fantasy Team Summary</div>";
		//$urlArr['summary'].="<table id='fantasySummaryTable'>";
		//$urlArr['summary'].="<tr><th>Projected Points</th><th>Projected Games Played</th><th>Projected Benched Games</th></tr>";
		//$urlArr['summary'].="<tr><td>$totalPts</td><td>$totalGP</td><td>$totalBN</td></tr>";
		$urlArr['summary']="<div class='playerDivs teamSummary'>";
		$urlArr['summary'].="<div class='playerNameDiv'>";
		if ($compare_bool == true)
		{
			$urlArr['summary'].= $this->fantasy_team_name;
		}
		else
		{		
			if ($trade_bool == true)
			{
				$trade_string = "<select name='fan_team_dropdowntrade' id='fan_team_dropdowntrade' onchange='change_trade_team(this);'>";
			}
			else
			{
				$trade_string = "<select name='fan_team_dropdown' id='fan_team_dropdown' onchange='change_team_select(this);'>";
			}
			$urlArr['summary'].= "$trade_string<option value='".$this->fantasy_team_name."'>".$this->fantasy_team_name."</option>";
			foreach ($fantasy_league->fantasy_league_teams as $fan_team)
			{
				if ($fan_team->fantasy_team_name != $this->fantasy_team_name && $fan_team->fantasy_team_name != $primary_team_name)
				{
					$urlArr['summary'].= "<option value='".$fan_team->fantasy_team_name."'>".$fan_team->fantasy_team_name."</option>";
				}
			}
			$urlArr['summary'].="</select>";
		}
		$urlArr['summary'].="</div>";
		$urlArr['summary'].="<div>Points: $totalPts<br />$FPointsCompareSpan</div>";
		$urlArr['summary'].="<div>Games Played: $totalGP<br />$GPCompareSpan</div>";
		$urlArr['summary'].="<div>Bench Games: $totalBN<br />$BNCompareSpan</div>";
		$urlArr['summary'].="</div>";
		//$urlArr['summary'].="</table>";
		//$urlArr['summary'].="<div class = 'summaryTitleDiv'>Player Summary</div>";
		
		
		return $urlArr;
	}
	public function build_tooltip_html($player,$compare_bool,$compare_player,$number_of_new_players)
	{
		$teamSlots = unserialize($_SESSION['fantasyLeague']);
		$playerStatCompareCombined = Array();
		$playerStats = $player->get_basic_and_fantasy_stats_by_position($teamSlots);
		if ($compare_bool == true)
		{
			//this is a little complex. If the players are new, and an array of replaced players are passed, then sum up the replaced players values and
			//divide by the number of newly added players.
			//echo "getshere:".$number_of_new_players;
			if ($number_of_new_players > 0)
			{
				//echo "getshere";
				foreach($compare_player as $comp_player)
				{
					$playerStatsCompare = $comp_player->get_basic_and_fantasy_stats_by_position($teamSlots);
					foreach ($playerStatsCompare['projection'] as $category=>$projection)
					{
						$playerStatCompareCombined['projection'][$category] += $projection;
					}
					foreach ($playerStatsCompare['fantasyPoints'] as $category=>$fantasy)
					{
						$playerStatCompareCombined['fantasyPoints'][$category] += $fantasy;
						//echo $fantasy;
					}
				}
				
				//for each category sum and divide by the number of new players
				foreach($playerStatCompareCombined['projection'] as $category=>$projection)
				{
					$playerStatCompareCombined['projection'][$category] = $playerStatCompareCombined['projection'][$category]/$number_of_new_players;
				}
				foreach($playerStatCompareCombined['fantasyPoints'] as $category=>$fantasy)
				{
					$playerStatCompareCombined['fantasyPoints'][$category] = $playerStatCompareCombined['fantasyPoints'][$category]/$number_of_new_players;
				}
				
				$playerStatsCompare = $playerStatCompareCombined;
			}
			else
			{
				$playerStatsCompare = $compare_player->get_basic_and_fantasy_stats_by_position($teamSlots);
			}
		}
		
		
		$html = "<strong>".$player->first_name." ".$player->last_name."</strong>";
		$html .= "<div>Projected Statistics & Fantasy Points</div>";
		$html .= "<table>";
		$html .= "<tr><th/><th>Actual</th><th>Fantasy</th></tr>";
		
		if ($compare_bool == true)
		{
			$compareGP = number_format($playerStats['fantasyPoints']['gamesPlayed'] - $playerStatsCompare['fantasyPoints']['gamesPlayed'],1);

			if ($compareGP>0)
			{	
				$compareGPString = "<span class = 'positiveCompare'> (+$compareGP)</span>";
			}
			elseif ($compareGP<0)
			{
				$compareGPString  = "<span class = 'negativeCompare'> ($compareGP)</span>";
			}
		}
		$html .= "<td><strong>Games Played: </strong></td><td>".$playerStats['projectionTotal']['gamesPlayed']."</td><td>".$playerStats['fantasyPoints']['gamesPlayed']."$compareGPString</td>";
		$html .= "<tr><th/><th>Actual</th><th>Fantasy Pts</th></tr>";
		foreach ($playerStats['projection'] as $category=>$stat)
		{
			if ($compare_bool == true)
			{
				$compareProjection = number_format($playerStats['projection'][$category]-$playerStatsCompare['projection'][$category],1);
				$compareFantasy = number_format($playerStats['fantasyPoints'][$category]-$playerStatsCompare['fantasyPoints'][$category],1);
				if ($compareFantasy>0)
				{	
					$compareFantasyString = "<span class = 'positiveCompare'> (+$compareFantasy)</span>";
				}
				elseif ($compareFantasy<0)
				{
					$compareFantasyString = "<span class = 'negativeCompare'> ($compareFantasy)</span>";
				}
			}
			$html .= "<tr>";
			$html .= "<td><strong>$category: </strong></td><td>".number_format($playerStats['projection'][$category],1)."$compareProjectionString</td><td>".number_format($playerStats['fantasyPoints'][$category],1)."$compareFantasyString</td>";
			$html .= "</tr>";
		}
		if ($compare_bool == true)
		{
			if ($compareTotal>0)
			{	
				$compareTotalString = "<span class = 'positiveCompare'> (+$compareTotal)</span>";
			}
			elseif ($compareTotal<0)
			{
				$compareTotalString = "<span class = 'negativeCompare'> ($compareTotal)</span>";
			}
		}
		$html .= "</table>";
		$html .= "<strong>Projected Total Fantasy Points: </strong>".number_format($playerStats['fantasyPoints']['total'],1).$compareTotalString;
		return $html;
	}
	public function replace_players($remove_arr, $add_arr)
	{
		//print_r($remove_arr);
		//print_r($add_arr);
		$teamSlots = unserialize($_SESSION['fantasyLeague']);
		$lastkey = 0;
		foreach ($remove_arr as $nhl_id)
		{
			foreach ($this->players as $key=>$player)
			{
				if ($player->id == $nhl_id['nhl_id'])
				{
					unset($this->players[$key]);
				}
			}
		}
		foreach ($add_arr as $nhl_id)
		{
			$duplicate=false;
			foreach ($this->players as $key=>$player)
			{
				$lastkey = $key;
				if ($player->id == $nhl_id['nhl_id'])
				{
					$duplicate=true;
				}
			}
			if ($duplicate==false)
			{
				$lastkey++;
				$this->players[$lastkey] = new Hockey_player($nhl_id['nhl_id'],$nhl_id['position'],$nhl_id['status'],$teamSlots);
			}
		}
	}
	
	//accumulates the points
	function accumulate_nightly_points()
	{
		$nightly_points= Array();
		foreach ($this->nightlyPoints as $key=>$value)
		{
			$tempVal = $value;
			$nightly_points[$key] = $previous_val+$value;
			$previous_val = $nightly_points[$key];
		}
		return $nightly_points;
	}
	
	//adds the date to the third field of the data

	function add_html_for_each_player($arr)
	{
		foreach ($arr as $key=>$value)
		{
			//header
			$arr[$key] .= ",".$key;
			$arr[$key] .= "<table>";
			
			//each player
			
			$arr[$key] .= "<tr><th>Player</th><th>Pos</th><th></th><th>Points</th></tr>";
			
			//sort the array by who's playing, then by the position slots
			$tempArr = Array();
			$i=0;
			foreach ($this->players as $player)
			{
				$tempArr[$i]['last_name'] = $player->last_name;
				$tempArr[$i]['slot'] = $player->proj_by_date[$key]['slot'];
				$tempArr[$i]['position'] = $player->position2;
				$tempArr[$i]['playingStatus'] = $player->proj_by_date[$key]['playingStatus'];
				if (number_format($player->proj_by_date[$key]['fantasyPoints']['total'], 2, '.', '') != 0)
				{
					$tempArr[$i]['fantasyPoints'] = number_format($player->proj_by_date[$key]['fantasyPoints']['total'], 2, '.', '');
				}
				$i++;
			}
			foreach ($tempArr as $key2 => $row) {
				$playingStatus[$key2]  = $row['playingStatus']; 
			}
			
			foreach ($tempArr as $key2 => $row) {
				$position[$key2]  = $row['position']; 
			}
			foreach ($tempArr as $key2 => $row) {
				$fantasyPoints[$key2]  = $row['fantasyPoints']; 
			}
			array_multisort($position, SORT_DESC, $playingStatus, SORT_DESC,$fantasyPoints, SORT_DESC, $tempArr);			

			
			
			foreach ($tempArr as $player)
			{
				$arr[$key] .= "<tr>";
				$arr[$key] .= "<td>";
				$arr[$key] .= $player['last_name'];
				$arr[$key] .= "</td>";
				$arr[$key] .= "<td>";
				$arr[$key] .= $player['position'];
				$arr[$key] .= "</td>";
				$arr[$key] .= "<td>";
				$arr[$key] .= $player['slot'];
				$arr[$key] .= "</td>";
				$arr[$key] .= "<td>";
				$arr[$key] .= $player['fantasyPoints'];
				$arr[$key] .= "</td>";
				$arr[$key] .= "</tr>";
			}
			
			//footer
			$arr[$key] .= "</table>";
			$arr[$key] .= "Nightly Points: ".number_format($this->nightlyPoints[$key], 2, '.', '');;
		}
		return $arr;
	}
	

}