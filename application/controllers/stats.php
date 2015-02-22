<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Stats extends CI_Controller {

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
		$this->load->library('form_validation');
		$this->load->helper('url');
		$this->load->helper('form');
	}

	public function index($playersToLoad = false)
	{
	    if (!$this->ion_auth->logged_in())
		{
			redirect('beta');
		}
		$this->load->model('stats_model');
		$f1 = false;
		$val = $this->uri->segment(3);
		$secret = $this->uri->segment(4);
		if(!empty($val)){
			switch ($val) {
				case 'goals':
					$f1 = $this->stats_model->loadPreMadeData('goals');
					$preMade = 1;
					break;
				case 'assists':
					$f1 = $this->stats_model->loadPreMadeData('assists');
					$preMade = 1;
					break;
				case 'plus_minus':
					$f1 = $this->stats_model->loadPreMadeData('plus_minus');
					$preMade = 1;
					break;
				case 'points':
					$f1 = $this->stats_model->loadPreMadeData('points');
					$preMade = 1;
					break;
				case 'pim':
					$f1 = $this->stats_model->loadPreMadeData('pim');
					$preMade = 1;
					break;
				default:
				   $f1 = $this->stats_model->loadGraphData($userID,$val,$secret);
			}
		}
		//print_r($f1);
		// Get all the stat HTML
		if(!$f1){
			//if false
		$data['g_StatDates'] = $this->stats_model->buildStatOptDate('0');
		$data['g_StatGT'] = $this->stats_model->buildStatOptGT('0');
		$data['g_StatLoc'] = $this->stats_model->buildStatOptLoc('0');
		$data['g_StatPer'] = $this->stats_model->buildStatOptPer('0');
		$data['g_StatStr'] = $this->stats_model->buildStatOptStr('0');
		$data['g_StatTA'] = $this->stats_model->buildStatTA('0');
		//$data['playersToAdd'] = "addPlayer('Alex Ovechkin1');addPlayer('Sidney Crosby2');";
		$data['playersToAdd'] = $this->stats_model->buildStatPlayers();
		$data['g_StatPENADV'] = $this->stats_model->buildStatPEN('1');
		$data['g_StatPEN'] = $this->stats_model->buildStatPEN('0');
		$data['g_StatES'] = '';//$this->stats_model->buildStatES('0');
		$data['g_StatES_adv'] = $this->stats_model->buildStatES2('0');
		$data['svGID'] = 0;
		$data['setCurTab'] = "switchtabs('tabGoal','goalBut',1);";

		}
		else{
			//if true
			
		$data['g_StatDates'] = $this->stats_model->buildStatOptDate($f1['dates']);
		$data['g_StatGT'] = $this->stats_model->buildStatOptGT($f1['gt_vals']);
		$data['g_StatLoc'] = $this->stats_model->buildStatOptLoc($f1['location']);
		$data['g_StatPer'] = $this->stats_model->buildStatOptPer($f1['goalPeriods']);
		$data['g_StatStr'] = $this->stats_model->buildStatOptStr($f1['Strength']);
		$data['g_StatTA'] = $this->stats_model->buildStatTA($f1['teamAgainst']);
		$data['playersToAdd'] = $this->stats_model->buildStatPlayers($f1['pvals']);
		$data['setCurTab'] = $this->stats_model->buildStatTab($f1['statistic']);
		$data['g_StatPENADV'] = $this->stats_model->buildStatPEN('1',$f1['teamPenalties']);
		$data['g_StatPEN'] = $this->stats_model->buildStatPEN('0',$f1['teamPenalties']);
		$data['g_StatES'] = $this->stats_model->buildStatES($f1['es_vals']);
		$data['g_StatES_adv'] = $this->stats_model->buildStatES2('0');

		if(!$preMade)
			$data['svGID'] = $f1['id'];
		else
			$data['svGID'] = 0;
		}
		$data['g_TeamList'] = $this->stats_model->getTeamList();
		$this->load->view('stats/stats', $data);

	}
	function gambling()
	{
		// Display Page
		if (!$this->ion_auth->is_admin())
		{
			$this->session->set_flashdata('message', 'You must be an admin to view this page');
			redirect('stats/index');
		}
		$f1=0;
		$this->load->library('table');
		$data['header'] = "Stats Central";
		$this->load->model('stats_model');
		$userID=$this->session->userdata('id');


		$data['g_StatDates'] = $this->stats_model->buildStatOptDate('0');
		$data['g_StatGT'] = $this->stats_model->buildStatOptGT('0');
		$data['g_StatLoc'] = $this->stats_model->buildStatOptLoc('0');
		$data['g_StatStr'] = $this->stats_model->buildStatOptStr('0');
		$data['g_StatTA'] = $this->stats_model->buildStatTA('0');
		$data['g_StatPer'] = $this->stats_model->buildStatOptPer('0');

		//$data['playersToAdd'] = "addPlayer('Alex Ovechkin1');addPlayer('Sidney Crosby2');";
		$data['playersToAdd'] = $this->stats_model->buildStatPlayers();
		$data['svGID'] = 0;
		$data['setCurTab'] = "switchtabs('tabShots','gamb_MS_But',1);";
		$data['g_TeamList'] = $this->stats_model->getTeamList();
		//$data['g_PenList'] = $this->stats_model->getPenList(1);
		//$data['g_PenList_adv'] = $this->stats_model->getPenList('0');
		$data['menu'] = $this->stats_model->genMenu($userID);
		$data['menuDel'] = $this->stats_model->genMenuDel($userID);
		//$data['g_ESList'] = $this->stats_model->getESList();
		//flashMsg('warning','This page is currently under contruction, Stay tuned.');
		//$this->load->view($this->_container,$data);
		$this->load->view('stats/sm_gamlbing', $data);
	}
	function premade()
	{
		// Display Page
		$f1=0;
		$this->load->helper('url');
		$data['header'] = "Stats Central";
		$this->load->model('stats_model');
		$val = $this->uri->segment(3);
		if(!empty($val)){
		$f1 = $this->stats_model->loadPreMadeData($val);
		}
		//print_r($f1);
		// Get all the stat HTML
		if(!$f1){
			//if false
		redirect('/stats');

		}
		else{
			//if true
			
		$data['g_StatDates'] = $this->stats_model->buildStatOptDate($f1['dates']);
		$data['g_StatGT'] = $this->stats_model->buildStatOptGT($f1['gt_vals']);
		$data['g_StatLoc'] = $this->stats_model->buildStatOptLoc($f1['location']);
		$data['g_StatPer'] = $this->stats_model->buildStatOptPer($f1['goalPeriods']);
		$data['g_StatStr'] = $this->stats_model->buildStatOptStr($f1['Strength']);
		$data['g_StatTA'] = $this->stats_model->buildStatTA($f1['teamAgainst']);
		$data['playersToAdd'] = $this->stats_model->buildStatPlayers($f1['pvals']);
		$data['setCurTab'] = $this->stats_model->buildStatTab($f1['statistic']);
		$data['g_StatPENADV'] = $this->stats_model->buildStatPEN('1',$f1['teamPenalties']);
		$data['g_StatPEN'] = $this->stats_model->buildStatPEN('0',$f1['teamPenalties']);
		$data['g_StatES'] = $this->stats_model->buildStatES($f1['es_vals']);
		}
		$data['g_TeamList'] = $this->stats_model->getTeamList();
		//$data['g_PenList'] = $this->stats_model->getPenList(1);
		//$data['g_PenList_adv'] = $this->stats_model->getPenList('0');
		$data['menu'] = $this->stats_model->genMenu($userID);
		$data['menuDel'] = $this->stats_model->genMenuDel($userID);
		//$data['g_ESList'] = $this->stats_model->getESList();
		$data['page'] = $this->config->item('backendpro_template_public') . 'stats';

		$data['module'] = 'stats';
		$this->load->view($this->_container,$data);
	}
	function compare()
	{
		$this->load->model('stats_model');
		$player_name = urldecode($this->uri->segment(5));
		$player_name = str_replace("_", " ", $player_name);
		$player_name = str_replace("|", "'", $player_name);
		$arrPlayers= explode(':',$player_name);

		$playerCount = count($arrPlayers);

		$stattype = urldecode($this->uri->segment(3));
		$dates =urldecode($this->uri->segment(4));
		//$end_date = $this->uri->segment(5));				
		$strength = urldecode($this->uri->segment(6));				
		$period = urldecode($this->uri->segment(7));				
		$teamAgainst = urldecode($this->uri->segment(8));		
		$gameType = urldecode($this->uri->segment(9));	
		$penalities = urldecode($this->uri->segment(10));
		$esStat = urldecode($this->uri->segment(11));
		$location = urldecode($this->uri->segment(12));
		if($stattype=='tabShots'){
			$team = urldecode($this->uri->segment(13));
			$odds = urldecode($this->uri->segment(14));
			$betSize = urldecode($this->uri->segment(15));
			$numShots = urldecode($this->uri->segment(16));
			$betType=urldecode( $this->uri->segment(17));
		}
		$header = array();
		switch($stattype){
			case 'tabGoal':
				for($i=0;$i<=($playerCount-1);$i++){
					$nhlID = null;
					$teamID = null;
					$nhlID = $this->stats_model->getPlayerNHLID($arrPlayers[$i]);
					if(empty($nhlID)){
						$teamID = $this->stats_model->getTeamListID($arrPlayers[$i]);
					}
					//if empty, then team
					if($teamID){
						$goals[] =	$this->stats_model->getTeamGoals($teamID,$dates,$strength,$period,$teamAgainst,$gameType,$location);
					}
					elseif(!empty($nhlID)){
						$goals[] =	$this->stats_model->getPlayerGoals($arrPlayers[$i],$dates,$strength,$period,$teamAgainst,$gameType,$location);
					}
				}
				//print_r($goals);
				//$return = $this->stats_model->genCSVRecursiveHigh($goals,0,$arrPlayers);
				$return = $this->stats_model->genCSVRecursiveCategory($goals);
				break;
			case 'tabAssist':
				for($i=0;$i<=($playerCount-1);$i++){
					$nhlID = null;
					$teamID = null;
					$nhlID = $this->stats_model->getPlayerNHLID($arrPlayers[$i]);
					if(empty($nhlID)){
						$teamID = $this->stats_model->getTeamListID($arrPlayers[$i]);
					}
					//if empty, then team
					if($teamID){
						$assists[] = $this->stats_model->getTeamAssists($teamID,$dates,$strength,$period,$teamAgainst,$gameType,$location);
					}
					elseif(!empty($nhlID)){
						$assists[] = $this->stats_model->getPlayerAssists($arrPlayers[$i],$dates,$strength,$period,$teamAgainst,$gameType,$location);
					}

				}
				$return = $this->stats_model->genCSVRecursiveCategory($assists);
				break;
			case 'tabPoints':
				for($i=0;$i<=($playerCount-1);$i++){
					$nhlID = null;
					$teamID = null;
					$nhlID = $this->stats_model->getPlayerNHLID($arrPlayers[$i]);
					if(empty($nhlID)){
						$teamID = $this->stats_model->getTeamListID($arrPlayers[$i]);
					}
					//if empty, then team
					if($teamID){
						$points[] = $this->stats_model->getTeamPoints($teamID,$dates,$strength,$period,$teamAgainst,$gameType,$location);
					}
					elseif(!empty($nhlID)){
						$points[] = $this->stats_model->getPlayerPoints($arrPlayers[$i],$dates,$strength,$period,$teamAgainst,$gameType,$location);
					}
				}
				$return = $this->stats_model->genCSVRecursiveCategory($points);
				break;
			case 'tabPims':
				for($i=0;$i<=($playerCount-1);$i++){
					$nhlID = null;
					$teamID = null;
					$nhlID = $this->stats_model->getPlayerNHLID($arrPlayers[$i]);
					if(empty($nhlID)){
						$teamID = $this->stats_model->getTeamListID($arrPlayers[$i]);
					}
					//if empty, then team
					if($teamID){

						$pims[] = $this->stats_model->getTeamPims($teamID,$dates,$strength,$period,$teamAgainst,$gameType,$penalities,$location);
					}
					elseif(!empty($nhlID)){
						$pims[] = $this->stats_model->getPlayerPims($arrPlayers[$i],$dates,$strength,$period,$teamAgainst,$gameType,$penalities,$location);
					}
				}
				$return = $this->stats_model->genCSVRecursiveCategory($pims);
				break;
			case 'tabEventstats':
				for($i=0;$i<=($playerCount-1);$i++){
					$nhlID = null;
					$teamID = null;
					$nhlID = $this->stats_model->getPlayerNHLID($arrPlayers[$i]);
					if(empty($nhlID)){
						$teamID = $this->stats_model->getTeamListID($arrPlayers[$i]);
					}
					//if empty, then team
					if($teamID){
						$pims[] = $this->stats_model->getTeamES($teamID,$dates,$teamAgainst,$gameType,$esStat,$location);
					}
					elseif(!empty($nhlID)){
						$pims[] = $this->stats_model->getPlayerES($arrPlayers[$i],$dates,$teamAgainst,$gameType,$esStat,$location);
					}

				}
				$return = $this->stats_model->genCSVRecursiveCategory($pims);
				break;
			case 'tabShots':

				$return = $this->stats_model->getTeamShots($team, $odds, $betSize, $numShots,$dates,$teamAgainst,$gameType,$location,$betType);
				break;
		}
		//$pnames = implode(';',$arrPlayers);
		//$header = "Categories".';'.$pnames."\n";

		//echo $header;
		echo $return;
	}
	function playerData()
	{
		$q = $this->uri->segment(3);
		explode('-',$q);
		$q = str_replace("_", " ", $q);
		$q = str_replace("|", "'", $q);
		$arrPlayers= explode(':',$q);
		$playerCount = count($arrPlayers);
		$season = $this->uri->segment(4);
		$gametype = $this->uri->segment(5);
		//$curDiv = $this->uri->segment(6);
		$this->load->model('stats_model');
		for($i=0;$i<=($playerCount-1);$i++){
			$curDiv = $arrPlayers[$i][strlen($arrPlayers[$i])-1];
			//print_r($last);	print_r($arrPlayers[$i]);
			$name = substr( $arrPlayers[$i],0,-1); 
			$pData[] = $this->stats_model->_playerData($name,$gametype,$season,$curDiv);
		}
		$return = json_encode($pData);
		//print_r($return)
		if(!empty($return))
		{
			echo $return;
		}
	}
	function playerData2()
	{
		$this->load->model('stats_model');
		$player_name = $this->uri->segment(5);
		$player_name = str_replace("_", " ", $player_name);

		$player_name = str_replace("|", "'", $player_name);
		$arrPlayers= explode(':',$player_name);
		$playerCount = count($arrPlayers);

		$stattype = $this->uri->segment(3);
		$dates =$this->uri->segment(4);
		//$end_date = $this->uri->segment(5);				
		$strength = $this->uri->segment(6);				
		$period = $this->uri->segment(7);				
		$teamAgainst = $this->uri->segment(8);		
		$gameType = $this->uri->segment(9);	
		$penalities = $this->uri->segment(10);
		$esStat = $this->uri->segment(11);
		$location = $this->uri->segment(12);
		for($i=0;$i<=($playerCount-1);$i++){
			$curDiv = $arrPlayers[$i][strlen($arrPlayers[$i])-1];
			//print_r($last);	print_r($arrPlayers[$i]);
			$name = substr( $arrPlayers[$i],0,-1); 
			$pData[] = $this->stats_model->_playerData_v2(urldecode($name),$gameType,$dates,$curDiv, $stattype,$strength,$period,$teamAgainst,$location,$penalities,$esStat);
		}
		$return = json_encode($pData);
		if(!empty($return))
		{
			echo $return;
		}
	}
	function gameData()
	{
		$player1ID = $this->uri->segment(3);
		$player1ID = str_replace("_", " ", $player1ID);
		$player1ID = str_replace("|", "'", $player1ID);

		$date = $this->uri->segment(4);
		$this->load->model('stats_model');

		$gData = $this->stats_model->_gameData($player1ID,$date);
		//echo $gData;
	}
	function getACPlayerList(){

		$player1ID = $this->input->post('term');
		$date = $this->input->post('d');
		$date = $this->uri->segment(4);
		$this->load->model('stats_model');
		$data['pData'] = $this->stats_model->getPlayerList($player1ID,$date);
	}
	function checkPlayer(){
				
		$player1ID = $this->input->post('term');
		//if(is_user() | $player1ID == 'Alex Ovechkin' | $player1ID == 'Sidney Crosby'){
		$this->load->model('stats_model');
		$data['pData'] = $this->stats_model->checkPlayerList($player1ID);
		//}
		//else {
		//	$value['name'] =  'error';
		//	$value['message'] =  'You must be logged in to add new players';
		//	echo json_encode($value);
		//}

	}
	function getLeaders(){
				
		$this->load->model('stats_model');
		$ascDesc = $this->input->post('leaderAscDesc');
		if ($ascDesc == "Descending") $ascDesc = "DESC";
		else $ascDesc = "ASC";
		$teamPlayers = $this->input->post('leaderTeamsPlayers'); 
		//$data['pData'] = $this->stats_model->checkPlayerList($player1ID);

		$inputs['stattype'] = urldecode($this->uri->segment(3));
		$inputs['dates'] =urldecode($this->uri->segment(4));
		//$end_date = $this->uri->segment(5));				
		$inputs['strength'] = urldecode($this->uri->segment(6));				
		$inputs['period'] = urldecode($this->uri->segment(7));				
		$inputs['teamAgainst'] = urldecode($this->uri->segment(8));		
		$inputs['gameType'] = urldecode($this->uri->segment(9));	
		$inputs['penalties'] = urldecode($this->uri->segment(10));
		$inputs['esStat'] = urldecode($this->uri->segment(11));
		$inputs['location'] = urldecode($this->uri->segment(12));
	
	/*	if($stattype=='tabShots'){
			$team = urldecode($this->uri->segment(13));
			$odds = urldecode($this->uri->segment(14));
			$betSize = urldecode($this->uri->segment(15));
			$numShots = urldecode($this->uri->segment(16));
			$betType=urldecode( $this->uri->segment(17));
		}*/
		
		switch($inputs['stattype'])
		{
			case 'tabGoal':
				if ($teamPlayers == "players")
				{
					$playersArray = $this->stats_model->getLeadersGoals($inputs, $ascDesc);
				}
				else
				{
					$playersArray = $this->stats_model->getLeadersGoalsTeams($inputs, $ascDesc);
				}
				break;
			case 'tabAssist':
				if ($teamPlayers == "players")
				{
					$playersArray = $this->stats_model->getLeadersAssists($inputs, $ascDesc);
				}
				else
				{
					$playersArray = $this->stats_model->getLeadersAssistsTeams($inputs, $ascDesc);
				}
				break;
			case 'tabPoints':
				if ($teamPlayers == "players")
				{
					$playersArray = $this->stats_model->getLeadersPoints($inputs, $ascDesc);
				}
				else
				{
					$playersArray = $this->stats_model->getLeadersPointsTeams($inputs, $ascDesc);
				}
				break;
			case 'tabPims':
				if ($teamPlayers == "players")
				{
					$playersArray = $this->stats_model->getLeadersPims($inputs, $ascDesc);
				}
				else
				{
					$playersArray = $this->stats_model->getLeadersPimsTeams($inputs, $ascDesc);
				}
				break;
			case 'tabEventstats':
				if ($teamPlayers == "players")
				{
					$playersArray = $this->stats_model->getLeadersEventstats($inputs, $ascDesc);
				}
				else
				{
					$playersArray = $this->stats_model->getLeadersEventstatsTeams($inputs, $ascDesc);
				}
				break;
		}
		echo json_encode($playersArray);
	}	
	function delete_graph(){
		if(!is_user()) {echo 'You must be logged in to use this feature'; return false;}
		$this->load->model('stats_model');
		$userID=$this->session->userdata('id');
		$username=$this->session->userdata('username');
		$gId = $this->uri->segment(3);
		$lastID = $this->stats_model->delGraph($gId,$userID);
		if($lastID > 0 ) echo "Data Deleted Successfully";
		else echo "You do not have permissions for this operation";


	}
	function save(){
		if(!is_user()) {echo 'You must be logged in to use this feature'; return false;}
		$this->load->model('stats_model');
		$player_name = $this->uri->segment(5);
		$player_name = str_replace("_", " ", $player_name);
		$player_name = str_replace("|", "'", $player_name);
		$arrPlayers= explode(':',$player_name);
		$playerCount = count($arrPlayers);

		$stattype = $this->uri->segment(3);
		$dates =$this->uri->segment(4);
		$strength = $this->uri->segment(6);				
		$period = $this->uri->segment(7);				
		$teamAgainst = $this->uri->segment(8);		
		$gameType = $this->uri->segment(9);	
		$penalities = $this->uri->segment(10);	
		$esStat = $this->uri->segment(11);
		$location = $this->uri->segment(12);
		$graphName = $this->uri->segment(13);
		$graphID = $this->uri->segment(14);

		$userID=$this->session->userdata('id');
		$username=$this->session->userdata('username');
		
		$lastID = $this->stats_model->save($stattype,$player_name,$dates,$strength,$period,$teamAgainst,$gameType, $penalities,$esStat,$location,$userID,$graphID,$graphName);
		
		if($lastID ==1) echo "Data Saved Successfully";
		else echo "There was a problem, if you are not the owner of this graph you must use SaveAs to create a new save record";
	}
	function saveAs(){
		if(!is_user()) {echo 'You must be logged in to use this feature'; return false;}
		$this->load->model('stats_model');
		$player_name = $this->uri->segment(5);
		$player_name = str_replace("_", " ", $player_name);
		$player_name = str_replace("|", "'", $player_name);
		$arrPlayers= explode(':',$player_name);
		$playerCount = count($arrPlayers);

		$stattype = $this->uri->segment(3);
		$dates =$this->uri->segment(4);
		$strength = $this->uri->segment(6);				
		$period = $this->uri->segment(7);				
		$teamAgainst = $this->uri->segment(8);		
		$gameType = $this->uri->segment(9);	
		$penalities = $this->uri->segment(10);	
		$esStat = $this->uri->segment(11);
		$location = $this->uri->segment(12);

		$graphName = $this->uri->segment(13);
		$userID=$this->session->userdata('id');
		$username=$this->session->userdata('username');
		$strrnd = $this->genRandomString();
		$lastID = $this->stats_model->saveAs($stattype,$player_name,$dates,$strength,$period,$teamAgainst,$gameType, $penalities,$esStat,$location,$userID,$username,$graphName,$strrnd);
		if($lastID >=1) echo "Data Saved Successfully";
		else echo "There was a problem, try again shortly";
	}
	function embed(){
		$this->load->model('stats_model');
		$secret = $this->uri->segment(3);
		$embedID =$this->uri->segment(4);
		$embedData = $this->stats_model->readEmbed($secret);
				$embedData2  = $embedData[0];
		$data['statistic'] = $embedData2->statistic;
		$data['seasons'] = $embedData2->startDate;
		$data['endDate'] = $embedData2->endDate;
		$data['pvals'] = str_replace("'", ' ', $embedData2->pvals);;
		$data['Strength'] = $embedData2->Strength;
		$data['goalPeriods'] = $embedData2->goalPeriods;
		$data['teamAgainst'] = $embedData2->teamAgainst;
		$data['gt_vals'] = $embedData2->gt_vals;
		$data['teamPenalties'] = $embedData2->teamPenalties;
		$data['location'] = $embedData2->location;
		$data['date_added'] = $embedData2->date_added;
		$data['es_stat'] = $embedData2->es_stat;
		$data['playersToAdd'] = $this->stats_model->buildStatPlayers($data['pvals']);
		$this->load->view('stats/embed', $data);
	}
	function buildEmbed(){
		// amcharts.com export to image utility
		// set image type (gif/png/jpeg)
		if(!is_user()) {echo 'You must be logged in to use this feature'; return false;}
		$this->load->model('stats_model');
		$player_name = $this->uri->segment(5);
		$player_name = str_replace("_", "'", $player_name);
		$arrPlayers= explode(':',$player_name);
		$playerCount = count($arrPlayers);
		$stattype = $this->uri->segment(3);
		$dates =$this->uri->segment(4);
		//$end_date = $this->uri->segment(5);				
		$strength = $this->uri->segment(6);				
		$period = $this->uri->segment(7);				
		$teamAgainst = $this->uri->segment(8);		
		$gameType = $this->uri->segment(9);	
		$penalities = $this->uri->segment(10);	
		$esStat = $this->uri->segment(11);
		$location = $this->uri->segment(12);

		$userID=$this->session->userdata('id');
		$username=$this->session->userdata('username');
		$currentEmbed = $this->stats_model->chkEmbed($userID);
		//echo "current embed numb:".$currentEmbed." for user ID : ".$userID."<br>";
		$imgtype = 'jpeg';
		$currentEmbed++;
		// set image quality (from 0 to 100, not applicable to gif)
		$imgquality = 100;

		// get data from $_POST or $_GET ?
		$data = &$_POST;

		// get image dimensions
		$width  = (int) $data['width'];
		$height = (int) $data['height'];

		// create image object
		$img = imagecreatetruecolor($width, $height);

		// populate image with pixels
		for ($y = 0; $y < $height; $y++) {
		  // innitialize
		  $x = 0;
		  
		  // get row data
		  $row = explode(',', $data['r'.$y]);
		  
		  // place row pixels
		  $cnt = sizeof($row);
		  for ($r = 0; $r < $cnt; $r++) {
			// get pixel(s) data
			$pixel = explode(':', $row[$r]);
			
			// get color
			$pixel[0] = str_pad($pixel[0], 6, '0', STR_PAD_LEFT);
			$cr = hexdec(substr($pixel[0], 0, 2));
			$cg = hexdec(substr($pixel[0], 2, 2));
			$cb = hexdec(substr($pixel[0], 4, 2));
			
			// allocate color
			$color = imagecolorallocate($img, $cr, $cg, $cb);
			
			// place repeating pixels
			$repeat = isset($pixel[1]) ? (int) $pixel[1] : 1;
			for ($c = 0; $c < $repeat; $c++) {
			  // place pixel
			  imagesetpixel($img, $x, $y, $color);
			  
			  // iterate column
			  $x++;
			}
		  }
		}
		$shrtName = $username.$currentEmbed.".jpeg";
		 $filename = getcwd()."/embed_images/".$shrtName;

		if ( imagejpeg ( $img, $filename, 75 ) ){
			$error =  "Image resaved successfully - $filename";
			$domain = $_SERVER['HTTP_HOST'];
			$strrnd = $this->genRandomString();
		$lastID = $this->stats_model->addEmbed($stattype,$player_name,$dates,$strength,$period,$teamAgainst,$gameType, $penalities,$shrtName,$userID,$strrnd,$location,$esStat);
			$str = <<<EOD
<h3>Get Embed Code</h3>
		<div  id="mp4_embed_code">
			Large : <input id="embed_code_l" type="text" size="60" value='<iframe src="$domain/stats/embed/$strrnd/$lastID/b" width="640" height="400" scrolling="no" frameborder="0"></iframe>' 								
			/><br />
			Small : <input id="embed_code_s"  type="text" size="60" value='<iframe src="$domain/stats/embed/$strrnd/$lastID/s" width="483" height="380" scrolling="no" frameborder="0"></iframe>' />
			</div>
EOD;
			echo  $str ;

		}
		else
			$error =  "Image couldn't be written over (Check permissions)!";
		imagedestroy($img);
	}
	function genRandomString() {
		$length = 6;
		$characters = '0123456789abcdefghijklmnopqrstuvwxyz';
		$string = '';    

		for ($p = 0; $p < $length; $p++) {
			$string .= $characters[mt_rand(0, strlen($characters))];
		}

		return $string;
	}
}

/* End of file welcome.php */
/* Location: ./application/controllers/welcome.php */