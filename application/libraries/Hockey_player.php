<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
* Name:  Hockey_player
*
* Author: Michael Gross
*		  mikegross@gmail.com
*
* This object represents a hockey player as it relates to fantasy.
* The hockey player has identifiers like first name and last name, and it also holds statistics and fantasy statistics for each player for each date
* So, you can imagine each player being a 2x2 matrix of dates at the top and actual statistics on the left with fantasy conversion statistics on the left
* This way, we can look up any player's unique contribution to the fantasy team
*
*/

class Hockey_player extends CI_Model
{
	public $first_name;
	public $last_name;
	public $position;
	public $position2;
	public $team_name;
	public $team_id;
	public $id;
	public $age;
	public $avg_game_projection;
	public $image_url;
	
	public $injury_suspend_status;
	public $injury_suspend_start_date;
	public $injury_suspend_end_date;
	
	public $proj_by_date = Array(); //this has all the data by date of how many points the player is getting, whether they are playing, etc...
	public $zeroProjection = Array();
	//public $playing_status_by_date = Array();
	
	
	/*
	This function takes an nhl_id and creates a player object with the attributes of that player
	It does so by querying the nhl_players table in our database
	It also converts the positions R,L, and C to F in a position2 attribute
	*/
	public function __construct($nhl_id,$position,$status,$teamSlots)
	{
		$this->id = $nhl_id;
		$this->position2 = $position;
		$this->injury_suspend_status = $status;
		$this->injury_suspend_start_date = "01/01/1900";
		$this->injury_suspend_end_date = "12/31/2999";
		
		
		$sql = "SELECT * FROM nhl_players WHERE id = '".$this->id."'";
		//echo $sql;
		$query = $this->db->query($sql);
		foreach ($query->result() as $row){
			$this->first_name = $row->fname;
			$this->last_name = $row->lname;
			$this->position = $row->pos;
			
			if (!isset($position))
			{
				if ($row->pos == "R" || $row->pos == "L" || $row->pos == "C")
				{
					$this->position2 = "F";
				}
				else
				{
					$this->position2 = $this->position;
				}
				$this->injury_suspend_status = "Active";
			}
			//$this->avg_game_projection = substr($row->id,4,1);
			//$this->set_projection_by_date("2011-03-19",substr($row->id,4,1));
		}
		$sql = "SELECT * 
				FROM nhl_players nhl
				INNER JOIN new_player pl ON nhl.id = pl.nhl_id AND nhl.id = '$nhl_id'
				INNER JOIN new_event_summary es ON es.player_id = pl.id
				INNER JOIN new_team t ON t.team_id = es.team_id
				INNER JOIN new_game ga ON ga.id = es.game_id
				ORDER BY game_date DESC
				LIMIT 1";
		$query = $this->db->query($sql);
		foreach ($query->result() as $row){
			$this->team_name = $row->sched;
			$this->team_id = $row->team_id;
		}
		$this->image_url = addslashes("http://www.nhl.com/photos/mugs/$nhl_id.jpg");
		if ($this->injury_suspend_status == 1)
		{
			$this->injury_suspend_status = "Active";
		}
		//zero out the projections and fantasy stats - these should align to the stats for the sport
		$this->set_zero_stats($teamSlots);
	}

	
	/*this function takes 3 parameters, a date as a String, a $projection as an array of projected game values, e.g., goals, assists, etc...
	Lastly, it takes $fantasyPoints as an array which contains converted projected values into fantasy points
	*/
	public function set_projection_by_date($date,$projection,$fantasyPoints)
	{
		$this->proj_by_date[$date]['projection'] = $projection;
		$this->proj_by_date[$date]['fantasyPoints'] = $fantasyPoints;
		$this->set_playing($date,true);
		//print_r($proj_by_date);
		//echo "end testing!!!";
	}

	
	/*this function just checks to see whether the player is playing on a given date*/
	public function get_whether_player_playing($date)
	{
		$sched_name = $this->team_name;
		$sql = "SELECT * 
				FROM nhl_schedual
				WHERE date = '$date'
				AND (UPPER(away_team) = '$sched_name' or UPPER(home_team) = '$sched_name')";
		$query = $this->db->query($sql);
		if ($query->num_rows() > 0)
		{
			return true;
		}
		else
		{
			return false;
		}
	}
	
	//reset all projections
	public function reset_projections()
	{
		$this->proj_by_date = Array();
	}
	
	//if a player is playing, this will mark the boolean to say that player is playing that date. If not, it will mark the boolean and set all stats to 0.
	public function set_playing($date,$playingBool)
	{
		$this->proj_by_date[$date]['playing'] = $playingBool;
		if ($playingBool == false)
		{
			$this->proj_by_date[$date] = $this->zeroProjection;
			$this->proj_by_date[$date]['playing'] = false;
		}
	}
	
	//this function sets the zero parameter based on the stats included in the fantasy_team_slots model,
	public function set_zero_stats($teamSlots)
	{
		foreach ($teamSlots->multipliers as $key=>$value)
		{
			$this->zeroProjection['projection']["$key"]=0;
			$this->zeroProjection['fantasyPoints']["$key"]=0;
		}
	}
	
	public function set_playing_status($date,$status)
	{
		$this->proj_by_date[$date]['playingStatus'] = $status;
	}
	
	public function set_playing_slot($date,$slot)
	{
		$this->proj_by_date[$date]['slot'] = $slot;
	}
	
	public function set_injury_status($injuryStatus, $startDate=null, $endDate=null)
	{
		$this->injury_suspend_status = $injuryStatus;
		if (isset($startDate))
		{
			$this->injury_suspend_start_date = $startDate;
			$this->injury_suspend_end_date = $endDate;
		}
	}
	public function get_total_points()
	{
		$total=0;
		foreach ($this->proj_by_date as $date)
		{
			if ($date['playingStatus']=="Y")
			{
				$total += $date['fantasyPoints']['total'];
			}
		}
		return $total;
	}
	public function get_num_bench_games()
	{
		$total=0;
		foreach ($this->proj_by_date as $date)
		{
			if ($date['playingStatus']=="BN")
			{
				$total++;
			}
		}
		return $total;
	}
	public function get_num_games_played()
	{
		$total=0;
		foreach ($this->proj_by_date as $date)
		{
			if ($date['playingStatus']=="Y")
			{
				$total++;
			}
		}
		return $total;
	}
	public function get_basic_and_fantasy_stats_by_position($teamSlots)
	{
		$output = Array();
		$defaultString = 'P';
		foreach ($teamSlots->multipliers as $pos=>$multArr)
		{
			if ($this->position2 == $pos)
			{
				$defaultString = $this->position2;
			}
		}
		foreach ($this->proj_by_date as $date=>$arr)
		{
			if ($arr['playing']=="Y")
			{
				$output['projectionTotal']['gamesPlayed']++;
			}
			if ($arr['playingStatus']=="Y")
			{
				$output['fantasyPoints']['gamesPlayed']++;
			}
			foreach($teamSlots->multipliers[$defaultString] as $category=>$multiplier)
			{
				$output['projection'][$category] += $arr['projection'][$category];
				if ($arr['playingStatus']=="Y")
				{
					$output['fantasyPoints'][$category] += $arr['fantasyPoints'][$category];
					$output['fantasyPoints']['total'] += $arr['fantasyPoints'][$category];
				}
			}
		}
		return $output;
	}
	
	function create_projection_for_player($date_range_start,$date_range_end,$type,$teamSlots)
	{
		//$type denotes whether we are going to look at home games differently than away games and look at opponents differently as well
		
		$projection = Array();
		//check whether the projection type is player specific, otherwise go to the default
		$defaultBool = true;
		foreach ($teamSlots->multipliers as $pos=>$multArr)
		{
			if ($this->position2 == $pos)
			{
				$defaultBool = false;
			}
		}
		if ($defaultBool)
		{
			$nhlid = $this->id;

			$sql = "SELECT * FROM (
					SELECT AVG(goals) AS goals, AVG(assists) AS assists,AVG(plus_minus) AS plus_minus,AVG(sog) AS sog,AVG(hits_given) AS hits, AVG(blocked_shots) as blocked
					FROM new_event_summary es
					INNER JOIN new_player pl ON pl.id = es.player_id
					INNER JOIN new_game ga ON ga.id = es.game_id
					WHERE nhl_id = $nhlid
					AND game_date BETWEEN '$date_range_start' AND '$date_range_end'
					) AS es
					LEFT JOIN
					(
					SELECT goals/games AS ppg FROM
					(SELECT nhl_id, COUNT(*) AS goals
					FROM new_goal go
					INNER JOIN new_game ga ON go.game_id = ga.id
					INNER JOIN new_player pl ON pl.id = go.player_id
					WHERE game_date BETWEEN '$date_range_start' AND '$date_range_end'
					AND goal_strength LIKE '%PP'
					AND pl.nhl_id = $nhlid
					) AS goals
					INNER JOIN (
						SELECT nhl_id, COUNT(*) AS games
						FROM new_event_summary es
						INNER JOIN new_player pl ON pl.id = es.player_id
						INNER JOIN new_game ga ON es.game_id = ga.id	
						WHERE nhl_id = $nhlid
						AND game_date BETWEEN '$date_range_start' AND '$date_range_end'
					) gp ON goals.nhl_id = gp.nhl_id ) AS ppg ON 0=0
					LEFT JOIN
					(
					SELECT gwg/games AS gwg FROM
					(SELECT nhl_id, COUNT(*) AS gwg
					FROM new_goal go
					INNER JOIN new_game ga ON go.game_id = ga.id
					INNER JOIN new_player pl ON pl.id = go.player_id
					WHERE game_date BETWEEN '$date_range_start' AND '$date_range_end'
					AND gwg = 1
					AND pl.nhl_id = $nhlid
					) AS goals
					INNER JOIN (
						SELECT nhl_id, COUNT(*) AS games
						FROM new_event_summary es
						INNER JOIN new_player pl ON pl.id = es.player_id
						INNER JOIN new_game ga ON es.game_id = ga.id	
						WHERE nhl_id = $nhlid
						AND game_date BETWEEN '$date_range_start' AND '$date_range_end'
					) gp ON goals.nhl_id = gp.nhl_id ) AS gwg ON 0=0
					LEFT JOIN (
					SELECT goals/games AS shg FROM
					(SELECT nhl_id, COUNT(*) AS goals
					FROM new_goal go
					INNER JOIN new_game ga ON go.game_id = ga.id
					INNER JOIN new_player pl ON pl.id = go.player_id
					WHERE game_date BETWEEN '$date_range_start' AND '$date_range_end'
					AND goal_strength LIKE '%SH'
					AND pl.nhl_id = $nhlid
					) AS goals
					INNER JOIN (
						SELECT nhl_id, COUNT(*) AS games
						FROM new_event_summary es
						INNER JOIN new_player pl ON pl.id = es.player_id
						INNER JOIN new_game ga ON es.game_id = ga.id	
						WHERE nhl_id = $nhlid
						AND game_date BETWEEN '$date_range_start' AND '$date_range_end'
					) gp ON goals.nhl_id = gp.nhl_id
					) AS shg ON 0=0
					LEFT JOIN
					(
					SELECT assists/games AS ppa FROM
					(SELECT nhl_id, COUNT(*) AS assists
					FROM new_assist a
					INNER JOIN new_goal go ON go.id = a.goal
					INNER JOIN new_game ga ON go.game_id = ga.id
					INNER JOIN new_player pl ON pl.id = a.player_id
					WHERE game_date BETWEEN '$date_range_start' AND '$date_range_end'
					AND goal_strength LIKE '%PP'
					AND pl.nhl_id = $nhlid
					) AS assists
					INNER JOIN (
						SELECT nhl_id, COUNT(*) AS games
						FROM new_event_summary es
						INNER JOIN new_player pl ON pl.id = es.player_id
						INNER JOIN new_game ga ON es.game_id = ga.id	
						WHERE nhl_id = $nhlid
						AND game_date BETWEEN '$date_range_start' AND '$date_range_end'
					) gp ON assists.nhl_id = gp.nhl_id
					) AS ppa ON 0=0
					LEFT JOIN
					(
					SELECT assists/games AS shassists FROM
					(SELECT nhl_id, COUNT(*) AS assists
					FROM new_assist a
					INNER JOIN new_goal go ON go.id = a.goal
					INNER JOIN new_game ga ON go.game_id = ga.id
					INNER JOIN new_player pl ON pl.id = a.player_id
					WHERE game_date BETWEEN '$date_range_start' AND '$date_range_end'
					AND goal_strength LIKE '%SH'
					AND pl.nhl_id = $nhlid
					) AS assists
					INNER JOIN (
						SELECT nhl_id, COUNT(*) AS games
						FROM new_event_summary es
						INNER JOIN new_player pl ON pl.id = es.player_id
						INNER JOIN new_game ga ON es.game_id = ga.id	
						WHERE nhl_id = $nhlid
						AND game_date BETWEEN '$date_range_start' AND '$date_range_end'
					) gp ON assists.nhl_id = gp.nhl_id
					) AS shassist ON 0=0
					LEFT JOIN
					(
					SELECT pims/games AS pims FROM
					(SELECT nhl_id, SUM(pim) AS pims
					FROM new_penalty p
					INNER JOIN new_game ga ON p.game_id = ga.id
					INNER JOIN new_player pl ON pl.id = p.player_id
					WHERE game_date BETWEEN '$date_range_start' AND '$date_range_end'
					AND pl.nhl_id = $nhlid
					) AS pims
					INNER JOIN (
						SELECT nhl_id, COUNT(*) AS games
						FROM new_event_summary es
						INNER JOIN new_player pl ON pl.id = es.player_id
						INNER JOIN new_game ga ON es.game_id = ga.id	
						WHERE nhl_id = $nhlid
						AND game_date BETWEEN '$date_range_start' AND '$date_range_end'
					) gp ON pims.nhl_id = gp.nhl_id
					) AS pims ON 0=0";
			$query = $this->db->query($sql);
			//$projection['fantasyPoints'] = 0.0;
			foreach ($query->result() as $row)
			{
				foreach ($teamSlots->multipliers["P"] as $pos=>$multArr)
				{
					$projection[$pos] = 0;
					switch($pos)
					{
						case 'G':
							$projection[$pos] = $row->goals;
						break;
						case 'A':
							$projection[$pos] = $row->assists;
						break;
						case '+/-':
							$projection[$pos] = $row->plus_minus;
						break;
						case 'SOG':
							$projection[$pos] = $row->sog;
						break;
						case 'HIT':
							$projection[$pos] = $row->hits;
						break;
						case 'PPP':
							$projection[$pos] = $row->ppg + $row->ppa;						
						break;
						case 'SHP':
							$projection[$pos] = $row->shg + $row->shassists;					
						break;
						case 'PIM':
							$projection[$pos] = $row->pims;					
						break;
						case 'BLK':
							$projection[$pos] = $row->blocked;			
						break;
						case 'GWG':
							$projection[$pos] =  $row->gwg;			
						break;
					}
				}
			}
		}
		else
		{
			switch ($this->position2)
			{
				case "G":
					$nhlid = $this->id;
					$sql = "SELECT AVG(IF(w_l_indicator='W',1,0)) AS wins, AVG(total_sa) AS saves, AVG(total_ga) AS goals_against, AVG(IF(total_ga=0,1,0)) AS shutouts
							FROM new_goalies go
							INNER JOIN new_player pl ON pl.id = go.goalie_id
							INNER JOIN new_game ga ON ga.id = go.game_id
							WHERE nhl_id = $nhlid
							AND (w_l_indicator = 'W' OR w_l_indicator = 'L')
							AND game_date BETWEEN '$date_range_start' AND '$date_range_end'";
					$query = $this->db->query($sql);
					foreach ($query->result() as $row)
					{
						foreach ($teamSlots->multipliers["G"] as $pos=>$multArr)
						{
							$projection[$pos] = 0;
							switch($pos)
							{
								case 'W':
									$projection[$pos] = $row->wins;
								break;
								case 'SV':
									$projection[$pos] = $row->saves;
								break;
								case 'SHO':
									$projection[$pos] = $row->shutouts;
								break;
								case 'GA':
									$projection[$pos] = $row->goals_against;
								break;
								case 'HIT':
									$projection[$pos] = $row->hits;
								break;
								case 'PPP':
									$projection[$pos] = $row->ppg + $row->ppa;						
								break;
								case 'SHP':
									$projection[$pos] = $row->shg + $row->shassists;					
								break;
								case 'PIM':
									$projection[$pos] = $row->pims;					
								break;
								case 'BLK':
									$projection[$pos] = $row->blocked;			
								break;
								case 'GWG':
									$projection[$pos] =  $row->gwg;			
								break;
							}
						}							
					}
					break;
				default:
					break;
			}
		}
		return $projection;
	}
	
	function create_fantasy_points_from_projection($projection, $teamSlots)
	{
		$fantasyPoints = Array();
		$fantasyPoints['total'] = 0;
	
		//check whether the projection type is player specific, otherwise go to the default
		$playerType = "P";
		foreach ($teamSlots->multipliers as $pos=>$multArr)
		{
			if ($this->position2 == $pos)
			{
				$playerType = $pos;
			}
		}
	
		foreach($teamSlots->multipliers[$playerType] as $key=>$value)
		{
			$fantasyPoints[$key] = $value*$projection[$key];
			$fantasyPoints['total'] += $value*$projection[$key];
		}
		return $fantasyPoints;
	}
	//based on a single projection value (i.e., consistent for the season), add the projection to each player for every date of the season based on whether
	//they are playing that night or not.
	function set_projections_from_today_to_end($startDate,$endDate,$projection,$fantasyPoints)
	{
		$currentDay = $startDate;
		while (strtotime($currentDay) <= strtotime($endDate))
		{
			if ($this->get_whether_player_playing($currentDay) == true)
			{
				$this->set_projection_by_date($currentDay,$projection, $fantasyPoints);
			}
			else
			{
				$this->set_playing($currentDay,false);
			}
			//echo $currentDay.":";
			$currentDay = date("Y-m-d",strtotime($currentDay . " +1 day"));
		}
	}

	
}