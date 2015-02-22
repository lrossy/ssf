<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
require(APPPATH.'/libraries/newOAuth/http.php');
require(APPPATH.'/libraries/newOAuth/oauth_client.php');
class Fantasy extends CI_Controller {

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
  //public $yahoo_conn;
  function __construct()
  {
    parent::__construct();
    $this->load->library('ion_auth');
    $this->load->library('session');
    $this->load->library('form_validation');
    $this->load->helper('url');
	$this->load->model('fantasy_model');
	$this->load->library('OAuth_MG');
	
  //this line doesnt do anything!
  }

  public function hockey()
  {
    $this->load->model('fantasy_model');
	$this->load->library('fantasy_team_slots');
	$this->load->library('fantasy_team');
	$this->load->library('hockey_player');
	
	if (!$this->ion_auth->logged_in())
    {
		redirect('beta');
    }
	unset($_SESSION['fantasyTeam']);
	unset($_SESSION['fantasyLeague']);
	unset($_SESSION['settings']);
	if ($this->fantasy_model->load_league())
	{
		$data['league_in_db'] = "<img src='/images/Database.png'> Load league stored in the database (FAST)";
	}
	$data['yahoo_logged_in']="<span id='logon_yahoo' class='div_img_button' onclick='javascript:logon_yahoo();'>
						<img src='/images/Yahoo-fantasy-logo.png'> Click to log on to your Yahoo! league</span>";
	if ($leagues=$this->fantasy_model->check_login_state())
	{
		$dropdownHTML = "<select name='league_key'>";
		foreach ($leagues as $league)
		{
			$league_key = $league['league_key'];
			$league_name = $league['name'];
			$dropdownHTML .= "<option value='$league_key'>$league_name</option>";
		}
		$dropdownHTML .= "</select>";
		$data['yahoo_logged_in'] = "<span id='logon_yahoo' class='div_img_button' onclick='javascript:logon_yahoo();'>
						<img src='/images/Yahoo-fantasy-logo.png'> Logged In </span><img src='/images/checkmark.png'>
								<span style='font-size:0.7em;' id='logoff_yahoo' class='div_img_button' onclick='javascript:logoff_yahoo();'>
									Click here to log off of Yahoo!
								</span>
								</div>
								<div>
										$dropdownHTML <span id='reload_league2' class='div_img_button' onclick='javascript:reloadLeaguePieces();'><img src='/images/reload_league.png'> Load/reload your league from Yahoo! </span>";
	}
	//set_time_limit(120);
	//$this->log_on_yahoo();
	$this->load->view('stats/fantasy_view',$data);
	$_SESSION['outputTest'] = "";
  }
  
  function log_on_yahoo()
  {
	$this->load->library('ion_auth');
    $this->load->library('session');
    $this->load->library('form_validation');
    $this->load->helper('url');
    $this->load->helper('form');
	$this->load->model('fantasy_model');
	$this->load->library('fantasy_team_slots');
	$this->load->library('fantasy_team');
	$this->load->library('hockey_player');


	//$this->fantasy_model->login_yahoo();
	$yahoo_conn = new OAuth_MG();
	$yahoo_conn->connect_yahoo();
	$yahoo_conn->yahoo_login();
  }
  
  function log_off_yahoo()
  {
	//$this->load->model('fantasy_model');
	//$return_val = $this->fantasy_model->log_off_yahoo($_SESSION['yahoo_conn']);
	//echo json_encode($return_val);
	$yahoo_conn = new OAuth_MG();
	$yahoo_conn->connect_yahoo();
	$yahoo_conn->GetAccessToken($access_token);
	if($access_token['value'] != null)
	{
		$yahoo_conn->log_off_yahoo();
		$yahoo_conn->GetAccessToken($access_token);
		//print_r($access_token);
	}
  }
  
  function setup_league()
  {
	$this->fantasy_model->load_league();
  }
  
  //function that loads the entire set of data from yahoo! (deprecated)
  function reload_league()
  {	
	$yahoo_conn = new OAuth_MG();
	$yahoo_conn->connect_yahoo();
	echo json_encode($this->fantasy_model->setup_league($yahoo_conn, $_POST['league_key']));
  }
  
  function load_yahoo_settings()
  {	
	$yahoo_conn = new OAuth_MG();
	$yahoo_conn->connect_yahoo();
	echo json_encode($this->fantasy_model->load_yahoo_settings($yahoo_conn, $_POST['league_key']));
  }
  function load_yahoo_teams()
  {	
	$yahoo_conn = new OAuth_MG();
	$yahoo_conn->connect_yahoo();
	echo json_encode($this->fantasy_model->load_yahoo_teams($yahoo_conn));
  }
  
  function run_analysis()
  {
	$this->load->model('fantasy_model');
	$this->load->library('fantasy_team_slots');
	$this->load->library('fantasy_team');
	$this->load->library('hockey_player');

	$fantasyTeam = unserialize($_SESSION['fantasyTeam']);
	$fantasyLeague = unserialize($_SESSION['fantasyLeague']);
	$settings = unserialize($_SESSION['settings']);
	$arr[0] = $fantasyTeam[0]->accumulate_nightly_points();
	$arr[0] = $fantasyTeam[0]->add_html_for_each_player($arr[0]);
	$team_names[] = $fantasyTeam[0]->fantasy_team_name;

	if ($fantasyLeague->headtohead)
	{
		foreach($fantasyLeague->weeks_and_matchups[$_SESSION['selectedWeek']]['teams'] as $key=>$matchup)
		{
			if ($matchup[0]['team_name'] == $fantasyTeam[0]->fantasy_team_name)
			{
				$opponent = $matchup[1]['team_name'];
				
			}
			if ($matchup[1]['team_name'] == $fantasyTeam[0]->fantasy_team_name)
			{
				$opponent = $matchup[0]['team_name'];
				
			}
		}
		foreach ($fantasyLeague->fantasy_league_teams as $team)
		{
			if  ($opponent == $team->fantasy_team_name)
			{
				$opponentTeam = $team;
			}
		}
		$arr[1] = $opponentTeam->accumulate_nightly_points();
		$arr[1] = $opponentTeam->add_html_for_each_player($arr[1]);
		$team_names[] = $opponentTeam->fantasy_team_name;
	}
		

	$arr = $this->fantasy_model->genCSVRecursiveCategoryMultipleSeries($arr);
	
	$output[0] = $team_names;
	$output[1] = $arr;
	//print_r(unserialize($_SESSION['fantasyTeam']));
	echo json_encode($output);
	//echo json_encode("testing");
  }
  function get_summary()
  {
	$this->load->model('fantasy_model');
	$this->load->library('fantasy_team_slots');
	$this->load->library('fantasy_team');
	$this->load->library('hockey_player');
	
	$fantasyLeague = unserialize($_SESSION['fantasyLeague']);
	$output = $this->fantasy_model->build_summary_table($fantasyLeague);
	echo json_encode($output);
  }
  function get_player_stats()
  {	
	$this->load->model('fantasy_model');
	$this->load->library('fantasy_team_slots');
	$this->load->library('fantasy_team');
	$this->load->library('hockey_player');

	$fantasyTeam = unserialize($_SESSION['fantasyTeam']);
	$fantasyLeague = unserialize($_SESSION['fantasyLeague']);
	if ($_POST['reset'] == 'true')
	{
		unset($_SESSION['fantasyTeam']);
		unset($fantasyTeam);
		
		$fantasyTeam = Array();
		$fantasyTeam[0] = $fantasyLeague->fantasy_league_teams[0];
		$_SESSION['fantasyTeam'] = serialize($fantasyTeam);
	}
	$i=0;
	foreach($fantasyTeam as $currentTeam)
	{
		if ($_POST['trade'] == 'true')
		{
			if ($i % 2 == 1)
			{
				$compareBool = true;
				$compareTeam = $fantasyTeam[$i-1];
				$urlArr[$i] = $currentTeam->get_summary_and_player_html($compareBool, $compareTeam, $fantasyLeague, false);
			}
		}
		else
		{
			if ($i == 0)
			{
				$compareBool = false;
				$compareTeam = $fantasyTeam[1];
			}
			else
			{
				$compareBool = true;
				$compareTeam = $fantasyTeam[0];
			}
			$urlArr[$i] = $currentTeam->get_summary_and_player_html($compareBool, $compareTeam, $fantasyLeague, false);
		}
		$i++;
	}
	echo json_encode($urlArr);
  }
  function compare_teams()
  {
	//getting the end of season - later remove the second line to keep it as the end of season
	$endOfSeason = strtotime("+1 week");
	$endOfSeason = date("Y-m-d",$endOfSeason);
	
	//later change this to actually be today
	$today = date("Y-m-d", now());
	
  	$this->load->model('fantasy_model');
	$this->load->model('stats_model');
	$this->load->library('fantasy_team_slots');
	$this->load->library('fantasy_team');
	$this->load->library('hockey_player');
	
	$fantasyTeam = unserialize($_SESSION['fantasyTeam']);
	
	//create a deep copy of the object
	$fantasyTeam[1] = unserialize(serialize($fantasyTeam[0]));
	$i=0;
    foreach ( $_POST as $key => $value )
    {
      $params[$key] = $this->input->post($key);
    }
	$params['players_to_add'] = json_decode($params['players_to_add']);
	$i=0;
	foreach ($params['dropCheck'] as $drop_nhl_id)
	{
		$removeArr[$i]['nhl_id'] = $drop_nhl_id;
		$i++;
	}
	foreach($params['players_to_add'] as $player_to_add)
	{
		$addArr[$i] = $this->stats_model->getPlayerNHLID($player_to_add);
		$i++;
	}
	
	$add_players = Array();
	
	foreach ($addArr as $key2=>$trade_player)
	{
		$add_players[$key2]['nhl_id'] = $addArr[$key2];
	}
	
	$fantasyTeam[1]->fantasy_team_name = "Comparison Team";
	$fantasyTeam[1]->replace_players($removeArr, $add_players);
	$projection = $this->fantasy_model->get_projection_dates($_POST['projTimePeriod']);
	$fantasyTeam[1]->create_stats_and_fantasy_points_for_team($today,$endOfSeason, $projection['projection_start_date'], $projection['projection_to_date']);	
	
	$_SESSION['fantasyTeam'] = serialize($fantasyTeam);
	
	$arr1 = $fantasyTeam[0]->accumulate_nightly_points();
	$arr1 = $fantasyTeam[0]->add_html_for_each_player($arr1);
	
	$arr2 = $fantasyTeam[1]->accumulate_nightly_points();
	$arr2 = $fantasyTeam[1]->add_html_for_each_player($arr2);

	$seriesArr = Array($arr1,$arr2);

	$team_names[] = $fantasyTeam[0]->fantasy_team_name;
	$team_names[] = $fantasyTeam[1]->fantasy_team_name;
	
	$arr = $this->fantasy_model->genCSVRecursiveCategoryMultipleSeries($seriesArr);
	$output[0]=$team_names; 
	$output[1] = $arr;
	echo json_encode($output);
	
	//print_r($arr);
	
  }
  
  //for the players that are added to the analysis, get their image and return it to the front end
  function get_player_image_URL_from_name()
  {
	$this->load->model('stats_model');
	$this->load->library('hockey_player');
	$player_name = $this->input->post('term');
	$nhl_id = $this->stats_model->getPlayerNHLID($player_name);
	//$img_url = addslashes("http://www.nhl.com/photos/mugs/$nhl_id.jpg");
	$tempPlayer = new Hockey_player($nhl_id);
	$html = "<div class='playerDivs'>";
	$html .="<div class='playerNameDiv'>".$tempPlayer->position2."</div>";			
	$html .="<div class='playerNameDiv'>".$tempPlayer->last_name."</div>";
	$html .= "<img src='".$tempPlayer->image_url."' />";
	$html .= "</div>";
	echo json_encode($html);
  }
  
  function change_team()
  {
  	$this->load->model('stats_model');
	$this->load->library('hockey_player');
	$this->load->library('fantasy_team_slots');
	$this->load->library('fantasy_team');
	$fantasy_team_selected_name = $this->input->post('term');
	$fantasyLeague = unserialize($_SESSION['fantasyLeague']);
	foreach($fantasyLeague->fantasy_league_teams as $cur_fantasy_team)
	{
		if ($fantasy_team_selected_name == $cur_fantasy_team->fantasy_team_name)
		{
			unset($_SESSION['fantasyTeam']);
			$_SESSION['fantasyTeam'][0] = $cur_fantasy_team;
			$_SESSION['fantasyTeam'] = serialize($_SESSION['fantasyTeam']);
		}
	}
  }
  
	function recalculate_projections()
	{
		$this->load->model('fantasy_model');
		$this->load->library('fantasy_team_slots');
		$this->load->library('fantasy_team');
		$this->load->library('hockey_player');
		
		$arr = explode(".",$_POST['selectedWeek']);
		$_SESSION['selectedWeek'] = $arr[0];
		$selectedDate = $arr[1];
	
		$projection = $this->fantasy_model->get_projection_dates($_POST['projTimePeriod'],$_POST['startDate'], $_POST['endDate']);
		$this->fantasy_model->recalc_projections($projection['projection_start_date'], $projection['projection_to_date'], $selectedDate);
	}
  
  function get_cur_date()
  {
	echo json_encode($_SESSION['currentDay']);
  }
  
  function make_trade()
  {
	$this->load->model('stats_model');
	$this->load->library('hockey_player');
	$this->load->library('fantasy_team_slots');
	$this->load->library('fantasy_team');
	$trade_team = $this->input->post('term');
	$fantasyTeam = unserialize($_SESSION['fantasyTeam']);
	$fantasyLeague = unserialize($_SESSION['fantasyLeague']);
	$compareBool = false;
	
	
	$team_selected == false;
	foreach ($fantasyLeague->fantasy_league_teams as $cur_fan_team)
	{
		if ($trade_team=="")
		{
			if ($fantasyTeam[0]->fantasy_team_name != $cur_fan_team->fantasy_team_name && $team_selected == false)
			{
				$urlArr[1] = $cur_fan_team->get_summary_and_player_html($compareBool, $compareTeam, $fantasyLeague, true,$fantasyTeam[0]->fantasy_team_name);
				$team_selected = true;
			}
		}
		else
		{
			if ($cur_fan_team->fantasy_team_name == $trade_team)
			{
				$urlArr[1] = $cur_fan_team->get_summary_and_player_html($compareBool, $compareTeam, $fantasyLeague, true,$fantasyTeam[0]->fantasy_team_name);
			}
		}
	}
	echo json_encode($urlArr);
  }
  function execute_trade()
  {
	//getting the end of season - later remove the second line to keep it as the end of season
	$endOfSeason = strtotime("+1 week");
	$endOfSeason = date("Y-m-d",$endOfSeason);
	
	//later change this to actually be today
	
	$today = date("Y-m-d", now());
	
  	$this->load->model('fantasy_model');
	$this->load->model('stats_model');
	$this->load->library('fantasy_team_slots');
	$this->load->library('fantasy_team');
	$this->load->library('hockey_player');
	
	foreach ( $_POST as $key => $value )
    {
      $params[$key] = $this->input->post($key);
    }
	/*foreach($params['dropCheck'] as $trade_primary)
	{
		echo $trade_primary;
	}*/

	$fantasyTeam = unserialize($_SESSION['fantasyTeam']);
	$fantasyLeague = unserialize($_SESSION['fantasyLeague']);
	
	//this will make the changes for the primary team
	$fantasyTeam[1] = unserialize(serialize($fantasyTeam[0]));
	//$fantasyTeam[1]->fantasy_team_name .= "(updated)";
	//this will make the changes for the secondary team
	foreach($fantasyLeague->fantasy_league_teams as $cur_fantasy_team)
	{
		if ($cur_fantasy_team->fantasy_team_name == $params['fan_team_dropdowntrade'])
		{
			$fantasyTeam[2] = unserialize(serialize($cur_fantasy_team));
		}
	}
	$fantasyTeam[3] = unserialize(serialize($fantasyTeam[2]));
	//$fantasyTeam[3]->fantasy_team_name .= "(updated)";


	$trade_players_from_1 = Array();
	foreach($fantasyTeam[1] as $key=>$team)
	{
		$i=0;
		foreach ($team as $player)
		{
			foreach ($params['dropCheck'] as $key2=>$trade_player)
			{
				if ($player->id == $trade_player)
				{
					$trade_players_from_1[$i]['nhl_id'] = $player->id;
					$trade_players_from_1[$i]['position'] =  $player->position2;
					$trade_players_from_1[$i]['status'] = $player->injury_suspend_status;
					$i++;
				}
			}
		}
	}
	
	$trade_players_from_2 = Array();
	foreach($fantasyTeam[3] as $key=>$team)
	{
		$i=0;
		foreach ($team as $player)
		{
			foreach ($params['dropChecktrade'] as $key2=>$trade_player)
			{
				if ($player->id == $trade_player)
				{
					$trade_players_from_2[$i]['nhl_id'] = $player->id;
					$trade_players_from_2[$i]['position'] =  $player->position2;
					$trade_players_from_2[$i]['status'] = $player->injury_suspend_status;
					$i++;
				}
			}
		}
	}
	$projection = $this->fantasy_model->get_projection_dates($_POST['projTimePeriod']);
	
	$fantasyTeam[1]->replace_players($trade_players_from_1, $trade_players_from_2);
	$fantasyTeam[1]->create_stats_and_fantasy_points_for_team($today,$endOfSeason, $projection['projection_start_date'], $projection['projection_to_date']);	
	$fantasyTeam[3]->replace_players($trade_players_from_2,$trade_players_from_1);
	$fantasyTeam[3]->create_stats_and_fantasy_points_for_team($today,$endOfSeason, $projection['projection_start_date'], $projection['projection_to_date']);	
	
	//$_SESSION['fantasyTeam'] = serialize($fantasyTeam);
	
	$arr1 = $fantasyTeam[0]->accumulate_nightly_points();
	$arr1 = $fantasyTeam[0]->add_html_for_each_player($arr1);
	
	$arr2 = $fantasyTeam[1]->accumulate_nightly_points();
	$arr2 = $fantasyTeam[1]->add_html_for_each_player($arr2);
	
	unset($_SESSION['fantasyTeam']);
	
	//print_r($fantasyTeam);
	$_SESSION['fantasyTeam'] = serialize($fantasyTeam);
	
	$arr3 = $fantasyTeam[2]->accumulate_nightly_points();
	$arr3 = $fantasyTeam[2]->add_html_for_each_player($arr3);
	//print_r($arr3);
	
	$arr4 = $fantasyTeam[3]->accumulate_nightly_points();
	$arr4 = $fantasyTeam[3]->add_html_for_each_player($arr4);
	
	$team_names[] = $fantasyTeam[1]->fantasy_team_name;
	$team_names[] = $fantasyTeam[3]->fantasy_team_name;
	
	$seriesArr = Array($arr2,$arr4);
	
	$arr = $this->fantasy_model->genCSVRecursiveCategoryMultipleSeries($seriesArr);
	$output[0] = $team_names;
	$output[1] = $arr;
	echo json_encode($output);
  }
  
  function compare_all_teams()
  {
	$this->load->model('fantasy_model');
	$this->load->model('stats_model');
	$this->load->library('fantasy_team_slots');
	$this->load->library('fantasy_team');
	$this->load->library('hockey_player');
	$fantasyLeague = unserialize($_SESSION['fantasyLeague']);
	$i=0;
	foreach ($fantasyLeague->fantasy_league_teams as $cur_fan_team)
	{
		$arr[$i] = $cur_fan_team->accumulate_nightly_points();
		$arr[$i] = $cur_fan_team->add_html_for_each_player($arr[$i]);
		$i++;
		$team_names[] = $cur_fan_team->fantasy_team_name;
	}
	$arr = $this->fantasy_model->genCSVRecursiveCategoryMultipleSeries($arr);
	$output[0] = $team_names;
	$output[1] = $arr;
	echo json_encode($output);
  }
  
}

/* End of file welcome.php */
/* Location: ./application/controllers/welcome.php */