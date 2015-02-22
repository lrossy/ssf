<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Home extends CI_Controller {

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
	function __construct()
	{
		parent::__construct();
		$this->load->library('ion_auth');
		$this->load->library('session');
		$this->load->helper('url');
		$this->load->model('home_model');
		$this->season='20102011';
		$this->gameType='3';
	}
	public function index()
	{
		//$this->load->driver('cache');
		$memcache = new Memcache;
		$memcache->connect('localhost', 311211);


		//print_r($memcache);
		//$this->cache->memcached->save('var_name', 'var_value', 60);
		//echo $this->cache->memcached->get('var_name'); 
		
		if (! $featuredGame = $memcache->get('featured'))
		{
			$featuredGame = $this->home_model->getFeatured();
//			//$this->memci->set('featured',$featuredGame,600);
			//$this->cache->memcached->save('featured',$featuredGame, 600);
			$memcache->set('featured', $featuredGame, false, 600);
			$data['feat'] = $featuredGame;
		}
		else{
			$data['feat'] = $featuredGame;			
		}
		
		//$data['feat'] = '2010201121216';

		$todList = $this->home_model->getGameInfoPlayed($data['feat']);
			//Player LEader Info
		//$data['standings'] = $this->getStandings();
		//$todList = $this->home_model->getGameInfoPlayed('2007200820050');
		$data['header'] = "Home";
		$data['gameInfo'] = $todList;
		$this->load->view('home/home', $data);
	}
	public function updToday(){
		//get todays games
		//find active game
		$array = $this->uri->uri_to_assoc(3);
		$todList = $this->home_model->findGames($array['gameID']);
		echo $todList;
	}
	public function updYest(){
		//find active game
		$array = $this->uri->uri_to_assoc(3);
		$todList = $this->home_model->findYestGames($array['gameID']);
		echo $todList;
	}
	public function getLeadTonight(){
		$array = $this->uri->uri_to_assoc(3);
		$allLeaders = $this->memci->get('tonightLeaders');
		if($allLeaders==null){
			$allLeaders = $this->home_model->getNightlyLeaders($season,$gameType,0);
			$this->memci->set('tonightLeaders',$allLeaders,600);
		}
		//print_r($allLeaders );

		echo $allLeaders;
	}
	public function getLeadYesterday(){
		$array = $this->uri->uri_to_assoc(3);
		$allLeaders = $this->memci->get('yesterdayLeaders');
		if($allLeaders==null){
			$allLeaders = $this->home_model->getNightlyLeaders($season,$gameType,2);
			$this->memci->set('yesterdayLeaders',$allLeaders,600);
		}
		//print_r($allLeaders );

		echo $allLeaders;
	}
	public function getLeadSeason($t=1){
		$array = $this->uri->uri_to_assoc(3);
		$allLeaders = $this->memci->get('seasonLeaders');
		if($allLeaders==null){
		
			$allLeaders = $this->home_model->getNightlyLeaders($season,$gameType,1);
			$this->memci->set('seasonLeaders',$allLeaders,600);
		}
		//print_r($allLeaders );
		if($t)
		echo $allLeaders;
		else return $allLeaders;
	}
	public function getLeadSeasonGoalies($t=1){

		$array = $this->uri->uri_to_assoc(3);
		$allLeaders = $this->home_model->getGoalieLeaders($season,$gameType,1);
		if($t)
		echo $allLeaders;
		else return $allLeaders;
	}
	public function gameInfo(){
		$array = $this->uri->uri_to_assoc(3);
		if(strlen($array['gameID']) =='13'){
		$todList = $this->memci->get('game_'.$array['gameID']);
			if($todList==null){
				$todList = $this->home_model->getGameInfoPlayed($array['gameID']);
				$this->memci->set('game_'.$array['gameID'],$todList,60);
				echo $todList;
				return 0;
			}
			else{
				echo $todList;
				return 0;
			}
		}
		else{
			$todList = $this->memci->get('game_'.$array['gameID']);
			if($todList==null){
				$todList = $this->home_model->getGameInfo($array['gameID']);
				$this->memci->set('game_'.$array['gameID'],$todList,60);
				echo $todList;
				return 0;

			}
			else{
				echo $todList;
				return 0;				
			}
		}
	}
	public function gameInfoNotPlayed(){
		$array = $this->uri->uri_to_assoc(3);

		$todList = $this->memci->get('game_'.$array['gameID']);
		if($todList==null){
			$todList = $this->home_model->getGameInfo($array['gameID']);
			$this->memci->set('game_'.$array['gameID'],$todList,60);
			echo $todList;
			return 0;

		}
		else{
			echo $todList;
			return 0;				
		}
		
	}
	public function getStandings(){
		$season = '20102011';
		$gameType = 2;
		$array = $this->uri->uri_to_assoc(3);
		$isStatic=0; 
		if(count($array) ==0){
			$isStatic=1;
			$array['selConf'] = 0;
			$array['selDiv'] = 1;
			$array['selWest'] = 0;
			$array['selEast'] = 1;
		}
		$todList = $this->memci->get('standings_'.$array['selConf'].$array['selDiv']);
		if($todList==null){
		$todList = $this->home_model->getStandings($array['selConf'],$array['selDiv'],$array['selWest'],$array['selEast'],$season,$gameType);
			$this->memci->set('standings_'.$array['selConf'].$array['selDiv'],$todList,600);
		}
		//print_r($todList);
		if($array['selDiv']){
			if($array['selWest']){
				$htmlOutDiv1 = '<div class="division"><div class="standTeamName col">Pacific</div><div class="standGP col">GP</div><div class="standPT col">PT</div></div>';
				$htmlOutDiv2 = '<div class="division"><div class="standTeamName col">NorthWest</div><div class="standGP col">GP</div><div class="standPT col">PT</div></div>';
				$htmlOutDiv3 = '<div class="division"><div class="standTeamName col">Central</div><div class="standGP col">GP</div><div class="standPT col">PT</div></div>';
				foreach ($todList as $team){
					switch($team['divison_name']){
						case 'Pacific':
							$htmlOutDiv1 .= "<div class='standTeamRow'><div class='standTeamName col'>$team[team_name]</div><div class='standGP col'>$team[GP]</div><div class='standPT col'>$team[points]</div></div>";
							break;
						case 'Northwest':
							$htmlOutDiv2 .= "<div class='standTeamRow'><div class='standTeamName col'>$team[team_name]</div><div class='standGP col'>$team[GP]</div><div class='standPT col'>$team[points]</div></div>";
							break;
						case 'Central':
							$htmlOutDiv3 .= "<div class='standTeamRow'><div class='standTeamName col'>$team[team_name]</div><div class='standGP col'>$team[GP]</div><div class='standPT col'>$team[points]</div></div>";
							break;
					}

				}
				$htmlOut = $htmlOutDiv1.$htmlOutDiv2.$htmlOutDiv3;
			}
			else if($array['selEast']){
			$htmlOutDiv1 = '<div class="division"><div class="standTeamName col">NorthEast</div><div class="standGP col">GP</div><div class="standPT col">PT</div></div>';
			$htmlOutDiv2 = '<div class="division"><div class="standTeamName col">Atlantic</div><div class="standGP col">GP</div><div class="standPT col">PT</div></div>';
			$htmlOutDiv3 = '<div class="division"><div class="standTeamName col">SouthEast</div><div class="standGP col">GP</div><div class="standPT col">PT</div></div>';
			foreach ($todList as $team){
				switch($team['divison_name']){
					case 'Northeast':
						$htmlOutDiv1 .= "<div class='standTeamRow'><div class='standTeamName col'>$team[team_name]</div><div class='standGP col'>$team[GP]</div><div class='standPT col'>$team[points]</div></div>";
						break;
					case 'Atlantic':
						$htmlOutDiv2 .= "<div class='standTeamRow'><div class='standTeamName col'>$team[team_name]</div><div class='standGP col'>$team[GP]</div><div class='standPT col'>$team[points]</div></div>";
						break;
					case 'Southeast':
						$htmlOutDiv3 .= "<div class='standTeamRow'><div class='standTeamName col'>$team[team_name]</div><div class='standGP col'>$team[GP]</div><div class='standPT col'>$team[points]</div></div>";
						break;
				}

			}
			$htmlOut = $htmlOutDiv1.$htmlOutDiv2.$htmlOutDiv3;
		}
		}
		elseif($array['selConf']){
			if($array['selWest']){

				$htmlOutDiv1 = '<div class="division"><div class="standTeamName col">Western</div><div class="standGP col">GP</div><div class="standPT col">PT</div></div>';
				$htmlOutWest='';
				$pacHigh = -1;$cenHigh = -1;$nwHigh = -1;
				$pacHighGP = -1;$cenHighGP = -1;$nwHighGP = -1;
				$y=1;
				foreach ($todList as $team){
					if($team['conference_name']=='Western'){
						//Find top from each then append the rest
						if( ($team['divison_name']=='Pacific') && ( $team['points'] > $pacHigh || ($team['points'] == $pacHigh && $team['GP'] < $pacHighGP))){
							$pacHigh= $team['points'];$pacHighGP= $team['GP'];
							$teamStandWest['pacific']['points'] = $team['points'];
							$teamStandWest['pacific']['GP'] = $team['GP'];
							$teamStandWest['pacific']['orderf'] = $team['points'] + (1-$team['GP']/100);
							$teamStandWest['pacific']['html'] = "<div class='standTeamRow pacLeader'><div class='standTeamName col'>$team[team_name]</div><div class='standGP col'>$team[GP]</div><div class='standPT col'>$team[points]</div><div class='clear'></div></div>";
						}
						elseif( ($team['divison_name']=='Northwest') && ( $team['points'] > $nwHigh || ($team['points'] == $nwHigh && $team['GP'] < $nwHighGP))){
							$nwHigh= $team['points'];$nwHighGP= $team['GP'];
							$teamStandWest['nw']['points'] = $team['points'];
							$teamStandWest['nw']['GP'] = $team['GP'];
							$teamStandWest['nw']['orderf'] = $team['points'] + (1-$team['GP']/100);

							$teamStandWest['nw']['html'] = "<div class='standTeamRow pacLeader'><div class='standTeamName col'>$team[team_name]</div><div class='standGP col'>$team[GP]</div><div class='standPT col'>$team[points]</div><div class='clear'></div></div>";
						}
						elseif( ($team['divison_name']=='Central') && ( $team['points'] > $cenHigh || ($team['points'] == $cenHigh && $team['GP'] < $cenHighGP))){
							$cenHigh= $team['points'];$cenHighGP= $team['GP'];
							$teamStandWest['cen']['points'] = $team['points'];
							$teamStandWest['cen']['GP'] = $team['GP'];
							$teamStandWest['cen']['orderf'] = $team['points'] + (1-$team['GP']/100);

							$teamStandWest['cen']['html'] = "<div class='standTeamRow pacLeader'><div class='standTeamName col'>$team[team_name]</div><div class='standGP col'>$team[GP]</div><div class='standPT col'>$team[points]</div><div class='clear'></div></div>";
						}else{
							if($y==5)$classSep = 'eighth';
							else $classSep = '';
							$teamStandWestRest[$y]['points'] = $team['points'];
							$teamStandWestRest[$y]['GP'] = $team['GP'];
							$teamStandWestRest[$y]['orderf'] = $team['points'] + (1-$team['GP']/100);
							$teamStandWestRest[$y]['html'] = "<div class='standTeamRow $classSep'><div class='standTeamName col'>$team[team_name]</div><div class='standGP col'>$team[GP]</div><div class='standPT col'>$team[points]</div><div class='clear'></div></div>";

							$y++;
						}
					}
					
				}
				$temp123 = $this->array_sort($teamStandWest, 'orderf', SORT_DESC);

				foreach($temp123 as $teamWestLeaders){
					$htmlOutDiv1 .= $teamWestLeaders['html'];
				}
				$temp3210 = $this->array_sort($teamStandWestRest, 'orderf', SORT_DESC);
				foreach($temp3210 as $teamWestLeadersRest){
					$htmlOutDiv1 .= $teamWestLeadersRest['html'];
				}
			}
			else if($array['selEast']){
				$htmlOutDiv1 = '<div class="division"><div class="standTeamName col">Eastern</div><div class="standGP col">GP</div><div class="standPT col">PT</div></div>';
				$htmlOutEast='';
				$neHigh = -1;$atHigh = -1;$seHigh = -1;
				$neHighGP = -1;$atHighGP = -1;$seHighGP = -1;
				$y=1;
				
				foreach ($todList as $team){
					if($team['conference_name']=='Eastern'){
						//print_r($team);
						if( ($team['divison_name']=='Atlantic') && ( $team['points'] > $atHigh || ($team['points'] == $atHigh && $team['GP'] < $atHighGP))){
							$atHigh= $team['points'];$atHighGP= $team['GP'];
							$teamStandEast['at']['points'] = $team['points'];
							$teamStandEast['at']['GP'] = $team['GP'];
							$teamStandEast['at']['orderf'] = $team['points'] + (1-$team['GP']/100);

							$teamStandEast['at']['html'] = "<div class='standTeamRow atLeader'><div class='standTeamName col'>$team[team_name]</div><div class='standGP col'>$team[GP]</div><div class='standPT col'>$team[points]</div><div class='clear'></div></div>";
						}
						elseif( ($team['divison_name']=='Northeast') && ( $team['points'] > $neHigh || ($team['points'] == $neHigh && $team['GP'] < $neHighGP))){
							$neHigh= $team['points'];$neHighGP= $team['GP'];
							$teamStandEast['ne']['points'] = $team['points'];
							$teamStandEast['ne']['GP'] = $team['GP'];
							$teamStandEast['ne']['orderf'] = $team['points'] + (1-$team['GP']/100);
							$teamStandEast['ne']['html'] = "<div class='standTeamRow neLeader'><div class='standTeamName col'>$team[team_name]</div><div class='standGP col'>$team[GP]</div><div class='standPT col'>$team[points]</div><div class='clear'></div></div>";
						}
						elseif( ($team['divison_name']=='Southeast') && ( $team['points'] > $seHigh || ($team['points'] == $seHigh && $team['GP'] < $seHighGP))){
							$seHigh= $team['points'];$seHighGP= $team['GP'];
							$teamStandEast['se']['points'] = $team['points'];
							$teamStandEast['se']['GP'] = $team['GP'];
							$teamStandEast['se']['orderf'] = $team['points'] + (1-$team['GP']/100);

							$teamStandEast['se']['html'] = "<div class='standTeamRow seLeader'><div class='standTeamName col'>$team[team_name]</div><div class='standGP col'>$team[GP]</div><div class='standPT col'>$team[points]</div><div class='clear'></div></div>";
						}
						else{
							if($y==5)$classSep = 'eighth';
							else $classSep = '';
							$teamStandEastRest[$y]['points'] = $team['points'];
							$teamStandEastRest[$y]['GP'] = $team['GP'];
							$teamStandEastRest[$y]['orderf'] = $team['points'] + (1-$team['GP']/100);
							$teamStandEastRest[$y]['html'] = "<div class='standTeamRow $classSep'><div class='standTeamName col'>$team[team_name]</div><div class='standGP col'>$team[GP]</div><div class='standPT col'>$team[points]</div><div class='clear'></div></div>";

							$y++;
							}
					}
				}
				$temp321 = $this->array_sort($teamStandEast, 'orderf', SORT_DESC);
				foreach($temp321 as $teamEastLeaders){
					$htmlOutDiv1 .= $teamEastLeaders['html'];
				}
				$temp3210 = $this->array_sort($teamStandEastRest, 'orderf', SORT_DESC);
				foreach($temp3210 as $teamEastLeadersRest){
					$htmlOutDiv1 .= $teamEastLeadersRest['html'];
				}
				//$htmlOutDiv1 .=$htmlOutEast;
			}
			$htmlOut =$htmlOutDiv1;
		}

		//print_r($todList);
		if(!$isStatic)
		echo $htmlOut;
		else return $htmlOut;
	}
	public function array_sort($array, $on, $order=SORT_ASC){
		$new_array = array();
		$sortable_array = array();

		if (count($array) > 0) {
			foreach ($array as $k => $v) {
				if (is_array($v)) {
					foreach ($v as $k2 => $v2) {
						if ($k2 == $on) {
							$sortable_array[$k] = $v2;
						}
					}
				} else {
					$sortable_array[$k] = $v;
				}
			}

			switch ($order) {
				case SORT_ASC:
					asort($sortable_array);
				break;
				case SORT_DESC:
					arsort($sortable_array);
				break;
			}

			foreach ($sortable_array as $k => $v) {
				$new_array[$k] = $array[$k];
			}
		}

		return $new_array;
	}
	public function fetchData(){

		/**
		Get all AJAX data for welcome page and send back as JSON string
		1 - Fetch Todays Games 
		2 - Fetch Secondary Games
		3 - Fetch Todays Game Data
		4 - Fetch Secondary Game Data
		5 - Fetch Leaders and Standings Data
		6 - Combine and return as JSON string
		**/
		$arrGameData = array();
		$arrTodaysGameData = array();
		$arrSecondaryGameData = array();

		//$today = $this->input->post('today');
		//$secondary = $this->input->post('secondary');
		$today =  date  ( 'Y-m-d', strtotime("-6 hours") );
		$secondary =  date  ( 'Y-m-d', strtotime("-30 hours") );

		//Step One -two
		$today = '2011-4-10';
		$secondary = '2011-4-9';

		$arrGameData['todayGameList'] = $this->getGameList($today);
		$arrGameData['secondaryGameList'] = $this->getGameList($secondary);


		//Step 3-4
		$arrGameData['gameData'] = $this->getGameData(array_merge($arrGameData['todayGameList'],$arrGameData['secondaryGameList']));

		//Step 5
		$arrGameData['leaders'] = $this->getAjaxLeaders($today,$secondary,0);

		//Step 5 Standings
		//	$todList = $this->home_model->getStandings($season,$gameType);

		$arrGameData['standings'] = $this->getAjaxStandings();

		//Step 6
		echo json_encode($arrGameData);
	}
	public function getAjaxStandings(){

		$arrStandings = array();
		$this->load->model('home_model');
		$arrStandings['season']['conf'] = $this->home_model->getAjaxStandings($this->season,$this->gameType, 'conf');
		$arrStandings['season']['div'] = $this->home_model->getAjaxStandings($this->season,$this->gameType, 'div');

		return $arrStandings;

	}
	public function getAjaxLeaders($date,$dateSecondary){
		$arrGamesList = array();
		
		$arrDate = explode('-',$date);
		if(checkdate($arrDate[1], $arrDate[2], $arrDate[0])){
			//1=Season,2=Tonight, 3= yesterday
			$arrLeaders['arrTnLeaders'] = $this->home_model->getNightlyLeadersAjax($this->season,$this->gameType,0,$arrDate[1],$arrDate[2],$arrDate[0]);
			$arrLeaders['arrGlTnLeaders'] = $this->home_model->getGoalieNightlyLeadersAjax($this->season,$this->gameType,0,$arrDate[1],$arrDate[2],$arrDate[0]);
		}
		$arrDateSec = explode('-',$dateSecondary);
		if(checkdate($arrDateSec[1], $arrDateSec[2], $arrDateSec[0])){
			//1=Season,2=Tonight, 3= yesterday
			$arrLeaders['arrYestLeaders'] = $this->home_model->getNightlyLeadersAjax($this->season,$this->gameType,2,$arrDate[1],$arrDate[2],$arrDate[0]);
			$arrLeaders['arrGlYestLeaders'] = $this->home_model->getGoalieNightlyLeadersAjax($this->season,$this->gameType,2,$arrDate[1],$arrDate[2],$arrDate[0]);

		}
		$arrLeaders['arrSeasLeaders'] = $this->home_model->getNightlyLeadersAjax($this->season,2,1);
		$arrLeaders['arrGlSeasLeaders'] = $this->home_model->getGoalieNightlyLeadersAjax($this->season,2,1);
		$arrLeaders['arrPlayoffLeaders'] = $this->home_model->getNightlyLeadersAjax($this->season,3,1);
		$arrLeaders['arrGlPlayoffLeaders'] = $this->home_model->getGoalieNightlyLeadersAjax($this->season,3,1);
		$dateStr =  date  ( 'Y-m-d', strtotime("-6 hours") );
		return $arrLeaders;
	}
	public function getGameList($date){
		$arrGamesList = array();
		
		$arrDate = explode('-',$date);
		if(checkdate($arrDate[1], $arrDate[2], $arrDate[0])){
			$arrGamesList = $this->home_model->arrayFindGames($date);
		}
		return $arrGamesList;
	}
	public function getGameData($arrGameList){
		
		//print_r($arrGameList);
		foreach($arrGameList as $game){
			
			$arrGames[$game['gameID']] = $this->home_model->buildGameArray($game);
		}
		return $arrGames;
	}
	public function exec_summary(){
		//$data['feat'] = '2007200820050';
		$data['page'] = $this->config->item('backendpro_template_public') . 'exec_summary';
		$data['module'] = 'welcome';
		//$this->load->view($this->_container,$data);
		header('Content-disposition: attachment; filename=ssf_es.pdf'); 
		header('Content-type: application/pdf'); 
		readfile( $_SERVER['DOCUMENT_ROOT'].'/files/ssf_es.pdf'); 
	}
}

/* End of file welcome.php */
/* Location: ./application/controllers/welcome.php */