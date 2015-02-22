<?php
error_reporting(E_PARSE); 
class Fantasy_Model extends CI_Model
{
	//Roach's League Key: 321.l.33565
	//select * from fantasysports.leagues.scoreboard where league_key='321.l.33565' and week='20'
	
	//Mike's League Key: 321.l.35241
	
	//public $yahoo_conn;
 
	function __construct()
    {
        // Call the Model constructor
        parent::__construct();
    }
	
	/*public function login_yahoo()
	{
		$this->load->model('fantasy_model');
		$this->load->library('fantasy_team_slots');
		$this->load->library('fantasy_team');
		$this->load->library('hockey_player');
		//login to Yahoo!
		
		$league_keys = $yahoo_conn->get_nhl_league_key();
		return $league_keys;
	}*/
	function check_login_state()
	{
		$yahoo_conn = new OAuth_MG();
		$yahoo_conn->connect_yahoo();
		$yahoo_conn->GetAccessToken($access_token);
		if($access_token['value'] == null)
		{
			return false;
		}
		else
		{
			return $yahoo_conn->get_nhl_league_key();;
		}
	}
	public function load_league()
	{
		$this->load->model('fantasy_model');
		$this->load->library('fantasy_team_slots');
		$this->load->library('fantasy_team');
		$this->load->library('hockey_player');
		$this->load->library('ion_auth');
		
		
		$user_logged_in = $this->ion_auth->get_user();
		$email = $user_logged_in->email;
		$sql="SELECT * from fantasy_league_data 
			WHERE user_email like '$email'
			AND analysis_run = 1;";
		
		$query = $this->db->query($sql);
		if(count($query->result())>=1)
		{	
			foreach ($query->result() as $row)
			{
				$_SESSION['fantasyTeam'] = $row->fantasy_team;
				$_SESSION['fantasyLeague'] = $row->fantasy_league;
				$_SESSION['settings'] = $row->yahoo_settings;
			}
			return true;
		}
		return false;
	}
	
	public function load_yahoo_settings($yahoo_conn, $league_key)
	{
		$this->load->model('fantasy_model');
		$this->load->library('fantasy_team_slots');
		$this->load->library('fantasy_team');
		$this->load->library('hockey_player');
		$this->load->library('ion_auth');
		
		$_SESSION['league_key'] = $league_key;
		
		//settings
		$settings = $yahoo_conn->get_fantasy_league_settings($league_key);
		$_SESSION['settings'] = serialize($settings);
		//getting the end of season - later remove the second line to keep it as the end of season
		//$endOfSeason = $settings->query->results->league->end_date;
		$current_week = $settings->query->results->league->current_week;
		$_SESSION['selectedWeek'] = $current_week;
		$week_matchup = $yahoo_conn->query_week_matchup($league_key, $current_week);
		foreach($week_matchup->query->results->league->scoreboard->matchups->matchup as $key=>$value)
		{
			$current_week = $value->week_start;
		}		
		
		$endOfSeason = strtotime("$current_week +1 weeks");
		$endOfSeason = date("Y-m-d",$endOfSeason);
		//echo $endOfSeason;
		$today = strtotime("$current_week");
		$today = date("Y-m-d", $today);
	}
	
	public function load_yahoo_teams($yahoo_conn)
	{
		$this->load->model('fantasy_model');
		$this->load->library('fantasy_team_slots');
		$this->load->library('fantasy_team');
		$this->load->library('hockey_player');
		$this->load->library('ion_auth');
		
		
		
		$settings = unserialize($_SESSION['settings']);
		$league_key = unserialize($_SESSION['league_key']);
		//get the fantasy teams into an array
		//logged in user's team
		$logged_user_team_key = $yahoo_conn->get_logged_in_users_team_key($league_key);
		$logged_in_users_team = $yahoo_conn->get_users_team_players($logged_user_team_key);
		$logged_in_users_team = $yahoo_conn->add_nhl_ids_to_team_players($logged_in_users_team);
		
		//other teams
		$other_team_keys = $yahoo_conn->get_nhl_team_keys($league_key, $logged_user_team_key);
		$other_teams_and_players = $yahoo_conn->get_all_other_team_players($other_team_keys);
		//print_r($other_teams_and_players);
		
		foreach ($other_teams_and_players as $key=>$team)
		{
			
			$other_teams_and_players[$key] = $yahoo_conn->add_nhl_ids_to_team_players($other_teams_and_players[$key]);
		}
		
		//this will pull from Yahoo! the number of slots for the league and the number of each
		$slots = $this->get_yahoo_slot_types_and_numbers($settings);	
		
		//this will create a new fantasy_team_slots object which will house all the data
		$teamSlots = new Fantasy_team_slots($slots);
		//this is for hockey specifically, it makes the Utility a group of Forward and Defense
		
		$teamSlots->set_position_groups("Util",Array("F","D"));
		$teamSlots->set_fantasy_league_style($this->get_nhl_league_type($settings));
		$_SESSION['leagueType'] = $teamSlots->fantasy_league_style;
		
		//this will get the default multipliers from Yahoo! for the different statistics
		$mults = $this->convert_yahoo_multipliers($settings);
		
		foreach ($mults[1] as $key=>$multipliers)
		{
			$teamSlots->set_multipliers($mults[0][$key], $multipliers);
		}
		
		//set the settings to have the weeks and match-ups for each week
		if ($teamSlots->headtohead)
		{
			$teamSlots->set_weeks_and_matchups($this->get_weeks_and_matchups($league_key, $settings, $yahoo_conn));
		}
		//this will create all the teams for the league
		$league_teams = $this->create_league_teams($other_teams_and_players);
		//print_r($league_teams);
		$i=0;
		
		$projection_start_date = date("Y-m-d",strtotime("-8 days"));
		$projection_to_date = date("Y-m-d",strtotime("-1 day"));
		$_SESSION['fantasyLeague'] = serialize($teamSlots);
		foreach ($league_teams as $key=>$team)
		{
			
			//echo "gets here1$key";
			//print_r($team);
			$fantasy_team = $this->create_fantasy_team($team, $teamSlots);
			$fantasy_team->fantasy_team_name = $key;
			$teamSlots->add_team_to_league($fantasy_team);
			//echo "gets here2$key";
			
		}
		
		$fantasyTeam[0] = $teamSlots->fantasy_league_teams[0];
		
		$_SESSION['fantasyTeam'] = serialize($fantasyTeam);
		$fantasyTeam = addslashes($_SESSION['fantasyTeam']);
		$_SESSION['fantasyLeague'] = serialize($teamSlots);
		$teamSlots = addslashes($_SESSION['fantasyLeague']);
		$settings = addslashes($_SESSION['settings']);
		$user_logged_in = $this->ion_auth->get_user();
		$email = $user_logged_in->email;
		$runTime = date("Y-m-d H:i:s");
		$sql="REPLACE INTO fantasy_league_data (user_email, league_key, yahoo_settings, fantasy_league, fantasy_team, analysis_run, date_time) values ('$email', '$league_key','$settings','$teamSlots', '$fantasyTeam','0', '$runTime')";
		$query = $this->db->query($sql);
	}
	
	public function setup_league($yahoo_conn, $league_key)
	{
		$this->load->model('fantasy_model');
		$this->load->library('fantasy_team_slots');
		$this->load->library('fantasy_team');
		$this->load->library('hockey_player');
		$this->load->library('ion_auth');
		
		//settings
		$settings = $yahoo_conn->get_fantasy_league_settings($league_key);
		$_SESSION['settings'] = serialize($settings);
		//getting the end of season - later remove the second line to keep it as the end of season
		//$endOfSeason = $settings->query->results->league->end_date;
		$current_week = $settings->query->results->league->current_week;
		$_SESSION['selectedWeek'] = $current_week;
		$week_matchup = $yahoo_conn->query_week_matchup($league_key, $current_week);
		foreach($week_matchup->query->results->league->scoreboard->matchups->matchup as $key=>$value)
		{
			$current_week = $value->week_start;
		}		
		
		$endOfSeason = strtotime("$current_week +1 weeks");
		$endOfSeason = date("Y-m-d",$endOfSeason);
		//echo $endOfSeason;
		$today = strtotime("$current_week");
		$today = date("Y-m-d", $today);
		
		//get the fantasy teams into an array
		//logged in user's team
		$logged_user_team_key = $yahoo_conn->get_logged_in_users_team_key($league_key);
		$logged_in_users_team = $yahoo_conn->get_users_team_players($logged_user_team_key);
		$logged_in_users_team = $yahoo_conn->add_nhl_ids_to_team_players($logged_in_users_team);
		
		//other teams
		$other_team_keys = $yahoo_conn->get_nhl_team_keys($league_key, $logged_user_team_key);
		$other_teams_and_players = $yahoo_conn->get_all_other_team_players($other_team_keys);
		//print_r($other_teams_and_players);
		
		
		
		foreach ($other_teams_and_players as $key=>$team)
		{
			
			$other_teams_and_players[$key] = $yahoo_conn->add_nhl_ids_to_team_players($other_teams_and_players[$key]);
		}
		
		//this will pull from Yahoo! the number of slots for the league and the number of each
		$slots = $this->get_yahoo_slot_types_and_numbers($settings);	
		
		//this will create a new fantasy_team_slots object which will house all the data
		$teamSlots = new Fantasy_team_slots($slots);
		//this is for hockey specifically, it makes the Utility a group of Forward and Defense
		
		$teamSlots->set_position_groups("Util",Array("F","D"));
		$teamSlots->set_fantasy_league_style($this->get_nhl_league_type($settings));
		$_SESSION['leagueType'] = $teamSlots->fantasy_league_style;
		
		
		
		//this will get the default multipliers from Yahoo! for the different statistics
		$mults = $this->convert_yahoo_multipliers($settings);
		
		foreach ($mults[1] as $key=>$multipliers)
		{
			$teamSlots->set_multipliers($mults[0][$key], $multipliers);
		}
		
		//set the settings to have the weeks and match-ups for each week
		if ($teamSlots->headtohead)
		{
			$teamSlots->set_weeks_and_matchups($this->get_weeks_and_matchups($league_key, $settings, $yahoo_conn));
		}
		//this will create all the teams for the league
		$league_teams = $this->create_league_teams($other_teams_and_players);
		//print_r($league_teams);
		$i=0;
		
		$projection_start_date = date("Y-m-d",strtotime("-8 days"));
		$projection_to_date = date("Y-m-d",strtotime("-1 day"));
		$_SESSION['fantasyLeague'] = serialize($teamSlots);
		foreach ($league_teams as $key=>$team)
		{
			
			//echo "gets here1$key";
			//print_r($team);
			$fantasy_team = $this->create_fantasy_team($team, $teamSlots);
			$fantasy_team->fantasy_team_name = $key;
			$teamSlots->add_team_to_league($fantasy_team);
			//echo "gets here2$key";
			
		}
		
		$fantasyTeam[0] = $teamSlots->fantasy_league_teams[0];
		
		$_SESSION['fantasyTeam'] = serialize($fantasyTeam);
		$fantasyTeam = addslashes($_SESSION['fantasyTeam']);
		$_SESSION['fantasyLeague'] = serialize($teamSlots);
		$teamSlots = addslashes($_SESSION['fantasyLeague']);
		$settings = addslashes($_SESSION['settings']);
		$user_logged_in = $this->ion_auth->get_user();
		$email = $user_logged_in->email;
		$runTime = date("Y-m-d H:i:s");
		$sql="REPLACE INTO fantasy_league_data (user_email, league_key, yahoo_settings, fantasy_league, fantasy_team, analysis_run, date_time) values ('$email', '$league_key','$settings','$teamSlots', '$fantasyTeam','0', '$runTime')";
		$query = $this->db->query($sql);
	}
	public function log_off_yahoo($conn)
	{
		//$conn = $this->OAuthLogin();
		if($conn.GetAccessToken())
		{
			return $conn->log_off_yahoo();
		}
		else return "was not logged in";
	}
	public function get_projection_dates($time_period, $start_custom, $end_custom)
	{
			switch($time_period)
			{
				case '7days': 
					$projection_start_date = date("Y-m-d",strtotime("-8 days"));
					$projection_to_date = date("Y-m-d",strtotime("-1 day"));
				break;
				case '30days': 
					$projection_start_date = date("Y-m-d",strtotime("-31 days"));
					$projection_to_date = date("Y-m-d",strtotime("-30 days"));
				break;
				case 'currentSeason': 
					$projection_start_date = date("Y-m-d",strtotime("2013-10-01"));
					$projection_to_date = date("Y-m-d",strtotime("-1 day"));
				break;
				case 'lastSeason': 
					$projection_start_date = date("Y-m-d",strtotime("2013-01-19"));
					$projection_to_date = date("Y-m-d",strtotime("2013-04-28"));
				break;
				case 'customPeriod': 
					$projection_start_date = date("Y-m-d",strtotime("$start_custom"));
					$projection_to_date = date("Y-m-d",strtotime("$end_custom"));
				break;
			}
			$projection_dates['projection_start_date']= $projection_start_date;
			$projection_dates['projection_to_date']= $projection_to_date;
			return $projection_dates;
	}
	
	public function recalc_projections($projection_start_date, $projection_to_date, $week_start)
	{
		$endOfSeason = strtotime("$week_start +1 week");
		$endOfSeason = date("Y-m-d",$endOfSeason);
		//echo $week_start." ".$endOfSeason;
		
		//later change this to actually be today
		$today = strtotime("$week_start");
		$today = date("Y-m-d", $today);
		$fantasyTeam = unserialize($_SESSION['fantasyTeam']);
		$teamSlots = unserialize($_SESSION['fantasyLeague']);
		foreach ($teamSlots->fantasy_league_teams as $fantasy_team)
		{
			$fantasy_team->create_stats_and_fantasy_points_for_team($today,$endOfSeason, $projection_start_date, $projection_to_date);
			//$teamSlots->add_team_to_league($fantasy_team);
		}
		$fantasyTeam[0] = $teamSlots->fantasy_league_teams[0];
		$_SESSION['fantasyTeam'] = serialize($fantasyTeam);
		$_SESSION['fantasyLeague'] = serialize($teamSlots); 
		
		$_SESSION['fantasyTeam'] = serialize($fantasyTeam);
		$fantasyTeam = addslashes($_SESSION['fantasyTeam']);
		$_SESSION['fantasyLeague'] = serialize($teamSlots);
		$teamSlots = addslashes($_SESSION['fantasyLeague']);
		$settings = addslashes($_SESSION['settings']);
		$user_logged_in = $this->ion_auth->get_user();
		$email = $user_logged_in->email;
		$sql="REPLACE INTO fantasy_league_data (user_email, yahoo_settings, fantasy_league, fantasy_team, analysis_run) values ('$email', '$settings','$teamSlots', '$fantasyTeam',1)";
		$query = $this->db->query($sql);
		
	}

	public function Create_OAuth_Object()
	{
		//$this->load->library('OAuth_MG');
		$yahoo_conn = new OAuth_MG();
		//$yahoo_conn->yahoo_login();
		return $yahoo_conn;
	}
		
	public function create_fantasy_team($team,$teamSlots)
	{
		$fantasyTeam = new Fantasy_team($team,$teamSlots);
		return $fantasyTeam;
	}
	function genCSVRecursiveCategory($arr,$f=0){
		$x=1;
		$csvDates ='';
		$arrDates = $arr;
		$arrLines = array();
		//foreach ($arr as $key=>$value){
		//	$arrDates = array_merge($arrDates,$arrList);
			//echo $arrList;
		//}
		
		ksort($arrDates);
		$arrDates = array_keys($arrDates);
		//print_r($arr);
		
		foreach ($arrDates as $date){
			$arrLines[0][] = date("d-M-y",strtotime($date));
			//$arrLines[0][] = $date;
	    }
	    
		foreach ($arr as $key=>$value)
		{
			$key = array_search($key, $arrDates);
			$key2 = strtotime($arrDates[$key]);
			$arrLines[$x][] = "[$key2,$value]";
			//echo "key:$key,value:$value";
		}
		//print_r($arrLines);
		
		$output='';
		foreach($arrLines as $line){
		    $output[] = implode(";",$line);
		}
		$output2 = implode("\n",$output);

		//return json_encode($arrLines);
		return $output2;
	}
	function genCSVRecursiveCategoryMultipleSeries($arr,$f=0){
		$x=1;
		$csvDates ='';
		$arrDates = array();
		$arrLines = array();
		foreach ($arr as $arrList){
		$arrDates = array_merge($arrDates,$arrList);
		}
        
		ksort($arrDates);
		$arrDates = array_keys($arrDates);
		//print_r($arr);
		
		foreach ($arrDates as $date){
			$arrLines[0][] = date("d-M-y",strtotime($date));
			//$arrLines[0][] = $date;
	    }
	    
		foreach ($arr as $series){
			foreach ($series as $key => $value){
                $key = array_search($key, $arrDates);
                $key2 = strtotime($arrDates[$key]);
				$arrLines[$x][] = "[$key2,$value]";
			}
			$x++;
		}
		
		$output='';
		foreach($arrLines as $line){
		    $output[] = implode(";",$line);
		}
		$output2 = implode("\n",$output);

		//return json_encode($arrLines);
		return $output2;
	}	
	
	function get_yahoo_slot_types_and_numbers($yahoo_settings)
	{
		$slots = Array();
		foreach ($yahoo_settings->query->results->league->settings->roster_positions as $key=>$position)
		{
			foreach ($position as $key2=>$pos_details)
			{
				$slots[$pos_details->position] = $pos_details->count;
			}
			
		}
		return $slots;
	}
	function get_nhl_league_type($yahoo_settings)
	{
		return $yahoo_settings->query->results->league->settings->scoring_type;			
	}
	
	function get_slot_types_and_numbers()
	{
		//set the number of slots for the team based on the Yahoo! data. These positions should align to the position names that the players use
		$slots['F'] = 2;
		$slots['D'] = 3;
		$slots['U'] = 2;
		$slots['G'] = 2;
		return $slots;
	}
	
	function convert_yahoo_multipliers($yahoo_settings)
	{
		//echo "<pre>";
		//print_r($yahoo_settings);
		//echo "</pre>";
		
		$stat_details = array();
		
		//create the array where the statistic details and multipliers will be kept
		foreach ($yahoo_settings->query->results->league->settings->stat_categories as $key=>$stat)
		{
			foreach ($stat->stat as $key2=>$statistic)
			{
				$stat_details[$statistic->stat_id]['name'] = $statistic->name;
				$stat_details[$statistic->stat_id]['display_name']= $statistic->display_name;
				$stat_details[$statistic->stat_id]['position_type']= $statistic->position_type;
				
				//if the type of league has fantasy multipliers
				if ($_SESSION['leagueType'] == "headpoint")
				{
					//Get the multipliers for each statistic from the other object in the yahoo data
					foreach ($yahoo_settings->query->results->league->settings->stat_modifiers as $key=>$stat_multiplier)
					{
						foreach ($stat_multiplier->stat as $key3=>$statistic_mult)
						{
							if ($key2 == $key3)
							{
								$stat_details[$statistic->stat_id]['mult_value'] = $statistic_mult->value;
							}
						}
					}
				}
				//otherwise if the league does not have fantasy multipliers, set all the multipliers to 1
				else
				{
					$stat_details[$statistic->stat_id]['mult_value'] = 1;
				}
			}
		}
		
		//get the names of the different types of positions for the multipliers
		$multiplierName = Array();
		foreach ($stat_details as $key=>$stat)
		{
			$exists = false;
			foreach ($multiplierName as $mult_name)
			{
				if ($mult_name == $stat['position_type'])
				{
					$exists = true;
				}
			}
			if ($exists == false)
			{
				$multiplierName[] = $stat['position_type'];
			}
		}
		
		
		//put the multipliers into an array for each of the position type
		$i=0;
		$multipliers = Array();
		foreach ($multiplierName as $key2=>$position)
		{
			foreach ($stat_details as $key=>$stat)
			{	
				if ($stat['position_type'] == $position)
				{
					$multipliers[$i][$stat['display_name']] = $stat['mult_value'];
				}
			}
			$i++;			
		}
		$output[0] = $multiplierName;
		$output[1] = $multipliers;		
		return $output;
	}
	
	/*function get_multipliers()
	{
		//this line sets the multipliers to convert them to fantasy points - this should be pulled from the Yahoo! data
		$playerMult = Array();
		$playerMult['goals'] = 2;
		$playerMult['assists'] = 1;
		$playerMult['sog'] = .1;
		$playerMult['plus_minus'] = .5;
		$playerMult['hits'] = .1;
		$playerMult['pims'] = .5;
		$playerMult['ppg'] = 1;
		$playerMult['ppa'] = 1;
		$playerMult['shg'] = 2;
		$playerMult['sha'] = 2;
		
		$goaliesMult = Array();
		$goaliesMult['wins'] = 3;
		$goaliesMult['saves'] = 0.1;
		$goaliesMult['shutouts'] = 6;
		$goaliesMult['goals_against'] = -0.25;
	
		$multiplier_names[0] = "default";
		$multiplier_names[1] = "G";
		
		$multipliers[0] = $playerMult;
		$multipliers[1] = $goaliesMult;
		
		$output[0] = $multiplier_names;
		$output[1] = $multipliers;
		
		return $output;
	}*/
	
	function create_primary_team($input_team)
	{
		foreach ($input_team as $key=>$player)
		{
			$team[$key] = $player['nhl_id'];
		}
		return $team;
	}
	function get_opponent($team_id, $week, $settings)
	{
		
	}
	
	
	function get_weeks_and_matchups($league_key, $yahoo_settings, $yahoo_conn)
	{
		$start_date = $yahoo_settings->query->results->league->start_date;
		$start_week = $yahoo_settings->query->results->league->start_week;
		$end_week = $yahoo_settings->query->results->league->end_week;
		$end_date = $yahoo_settings->query->results->league->end_date;
		$current_week = $yahoo_settings->query->results->league->current_week;
		for ($i=$start_week;$i<=$end_week;$i++)
		{
			$week_matchup = $yahoo_conn->query_week_matchup($league_key, $i);
			foreach($week_matchup->query->results->league->scoreboard->matchups->matchup as $key=>$value)
			{
				$output[$i]['week_start'] = $value->week_start;
				$output[$i]['week_end'] = $value->week_end;
				foreach ($value->teams->team as $key2=>$team)
				{
					$output[$i]['teams'][$key][$key2]['team_key'] = $team->team_key;
					$output[$i]['teams'][$key][$key2]['team_name'] = str_replace("'","",$team->name);
				}
				
			}
		}
		return $output;
	}
	
	function build_summary_table($fantasy_league)
	{
		$schedule = $fantasy_league->weeks_and_matchups;
		$yahoo_settings = unserialize($_SESSION['settings']);
		$current_week = $yahoo_settings->query->results->league->current_week;
		$_SESSION['selectedWeek'] = $current_week;
		$prefix = "<td><input type='radio' class='radioClass' name='selectedWeek' id='week";
		$suffix = "</label></td>";
		$label = "<label for='week";
		$html = "<table>";
		$html .= "<tr>";
		//$html = "<pre>".print_r($schedule,true)."</pre>".$week['week_start']."currentweek".$current_week;
		$html .= $prefix . (int)($current_week - 2) . "' value='".(int)($current_week - 2).".".$schedule[$current_week-2]['week_start']."'>" .$label.(int)($current_week - 2)."'>" .$schedule[$current_week-2]['week_start'] . $suffix;
		$html .= $prefix . (int)($current_week - 1) . "' value='".(int)($current_week - 1).".".$schedule[$current_week-1]['week_start']."'>" .$label.(int)($current_week - 1)."'>" .$schedule[$current_week-1]['week_start'] . $suffix;
		$html .= $prefix . (int)($current_week - 0) . "' value='".(int)($current_week - 0).".".$schedule[$current_week-0]['week_start']."'>" .$label.(int)($current_week - 0)."'>" .$schedule[$current_week-0]['week_start'] . $suffix;
		$html .= $prefix . (int)($current_week + 1) . "' value='".(int)($current_week + 1).".".$schedule[$current_week+1]['week_start']."'>" .$label.(int)($current_week + 1)."'>" .$schedule[$current_week+1]['week_start'] . $suffix;
		$html .= $prefix . (int)($current_week + 2) . "' value='".(int)($current_week + 2).".".$schedule[$current_week+2]['week_start']."'>" .$label.(int)($current_week + 2)."'>" .$schedule[$current_week+2]['week_start'] . $suffix;
		$html .= "</tr>";
		$html .= "</table>";
		return $html;
	}
	
	
	function create_league_teams($yahoo_teams)
	{
		$return_team = Array();
		foreach ($yahoo_teams as $key=>$team)
		{
			$i=0;
			foreach ($team as $player)
			{
				//print_r($player);
				$return_team[$key][$i]['nhl_id'] = $player['nhl_id'];
				$return_team[$key][$i]['position'] = $player['position'];
				$return_team[$key][$i]['status'] = $player['status'];
				$i++;
			}
		}
		//print_r($return_team);
		return $return_team;
		
		/*$team['MG-GHG'][0] = "8465009"; //Chara
		$team['MG-GHG'][1] = "8470877"; //Jones
		$team['MG-GHG'][2] = "8471859"; //S.Kostitsyn
		$team['MG-GHG'][3] = "8468509"; //Kronwall
		$team['MG-GHG'][4] = "8467329"; //Lecavalier
		$team['MG-GHG'][5] = "8468434"; //McDonald
		$team['MG-GHG'][6] = "8471717"; //McQuaid
		$team['MG-GHG'][7] = "8474574"; //Myers
		$team['MG-GHG'][8] = "8474157"; //Pacioretty
		$team['MG-GHG'][9] = "8470794"; //Pavelski
		$team['MG-GHG'][10] = "8465202"; //Salo
		$team['MG-GHG'][11] = "8457981"; //Selanne
		$team['MG-GHG'][12] = "8466378"; //St. Louis
		$team['MG-GHG'][13] = "8470595"; //Staal
		$team['MG-GHG'][14] = "8459670"; //Timonen
		$team['MG-GHG'][15] = "8468535"; //Vermette
		$team['MG-GHG'][16] = "8470273"; //White
		$team['MG-GHG'][17] = "8458537"; //Whitney
		$team['MG-GHG'][18] = "8468508"; //Williams
		
		$team['MG-GHG'][19] = "8471734"; //Quick
		$team['MG-GHG'][20] = "8460703"; //Thomas
		
		
		
		$team['Crazy C-ticker'][0] = "8473994";
		$team['Crazy C-ticker'][1] = "8470834";
		$team['Crazy C-ticker'][2] = "8460542";
		$team['Crazy C-ticker'][3] = "8474589";
		$team['Crazy C-ticker'][4] = "8473992";
		$team['Crazy C-ticker'][5] = "8467899";
		$team['Crazy C-ticker'][6] = "8466148";
		$team['Crazy C-ticker'][7] = "8448208"; //jagr - not in our dev db
		$team['Crazy C-ticker'][8] = "8471724"; //letang
		$team['Crazy C-ticker'][9] = "8473526";
		$team['Crazy C-ticker'][10] = "8474102";
		$team['Crazy C-ticker'][11] = "8471228";
		$team['Crazy C-ticker'][12] = "8462196";
		$team['Crazy C-ticker'][13] = "8467463";
		$team['Crazy C-ticker'][14] = "8474564";
		$team['Crazy C-ticker'][15] = "8460719";
		$team['Crazy C-ticker'][16] = "8468085";
		$team['Crazy C-ticker'][17] = "8470657";
		$team['Crazy C-ticker'][18] = "8471715";
		$team['Crazy C-ticker'][19] = "8471469";
		
		
		$team['Mister Bus'][0] = "8462038";
		$team['Mister Bus'][1] = "8471703";
		$team['Mister Bus'][2] = "8468064";
		$team['Mister Bus'][3] = "8470047";
		$team['Mister Bus'][4] = "8467370";
		$team['Mister Bus'][5] = "8471185";
		$team['Mister Bus'][6] = "8474578";
		$team['Mister Bus'][7] = "8471276";
		$team['Mister Bus'][8] = "8462225";
		$team['Mister Bus'][9] = "8473412";
		$team['Mister Bus'][10] = "8473473";
		$team['Mister Bus'][11] = "8466139";
		$team['Mister Bus'][12] = "8473465";
		$team['Mister Bus'][13] = "8466160";
		$team['Mister Bus'][14] = "8467876";
		$team['Mister Bus'][15] = "8474031";
		$team['Mister Bus'][16] = "8471735";
		$team['Mister Bus'][17] = "8468524";
		$team['Mister Bus'][18] = "8470880";
		$team['Mister Bus'][19] = "8470594";*/
	}

}
?>