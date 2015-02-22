<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Betting extends CI_Controller {

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
  //this line doesnt do anything!
  }

  public function hockey()
  {
    if (!$this->ion_auth->logged_in())
    {
      redirect('beta');
    }
    $this->load->model('gambling_model');
    // Get all the stat HTML
    //$data['g_StatDates'] = $this->stats_model->buildStatOptDate('0');
    //$data['g_StatGT'] = $this->gambling_model->buildStatOptGT('0');
    //HOME TEAM OPPONENT
    $data['g_StatHTO'] = $this->gambling_model->buildStatTA('0','Home');
    //AWAY TEAM OPPONENT
    $data['g_StatATO'] = $this->gambling_model->buildStatTA('0','Away');

    //$data['setCurTab'] = "switchtabs('tabGambML','moneyLineBut',1);";
    //NOT SURE IF WE NEED..
	$today = date("Y-m-d");
	$yesterday = strtotime('-1 day',strtotime($today));
	$yesterday = date('Y-m-d', $yesterday);
	$tomorrow = strtotime('+1 day',strtotime($today));
	$tomorrow = date('Y-m-d',$tomorrow);
	
	//$this->gambling_model->getWilliamHillOdds();
    //$data['g_TeamList'] = $this->gambling_model->getTeamList();
	$data['g_GameIDsYest'] = $this->gambling_model->getGameIDsFromDateinSchedual($yesterday);
	$data['g_GameIDsToday'] = $this->gambling_model->getGameIDsFromDateinSchedual($today);
	$data['g_GameIDsTomorrow'] = $this->gambling_model->getGameIDsFromDateinSchedual($tomorrow);
	
    $this->load->view('stats/betting_view', $data);
  }
  public function baseball()
  {
     if (!$this->ion_auth->logged_in())
    {
      redirect('beta');
    }
    $this->load->model('gambling_model');
	  $data['g_StatHTO'] = $this->gambling_model->buildStatTA_mlb('0','Home');
    //AWAY TEAM OPPONENT
    $data['g_StatATO'] = $this->gambling_model->buildStatTA_mlb('0','Away');
	$today = date("Y-m-d");
	$yesterday = strtotime('-1 day',strtotime($today));
	$yesterday = date('Y-m-d', $yesterday);
	$tomorrow = strtotime('+1 day',strtotime($today));
	$tomorrow = date('Y-m-d',$tomorrow);
	$data['g_GameIDsYest'] = $this->gambling_model->getGameIDsFromDateinSchedualMLB($yesterday);
	$data['g_GameIDsToday'] = $this->gambling_model->getGameIDsFromDateinSchedualMLB($today);
	$data['g_GameIDsTomorrow'] = $this->gambling_model->getGameIDsFromDateinSchedualMLB($tomorrow);
	$this->load->view('stats/betting_view', $data);
  }
  function compare()
  {
    $this->load->model('stats_model');
    $this->load->model('gambling_model');
    $moneyline = array();
    $puckline = array();
	$params = array();
    foreach ( $_POST as $key => $value )
    {
      $params[$key] = $this->input->post($key);
    }
    $params['betAmount'] = (is_float((float)$params['betAmount']) && (float)$params['betAmount'] > 0) ? (float)$params['betAmount'] : 10;
    $params['homeOdds']  = (is_int((int)$params['homeOdds']) && (int)$params['homeOdds'] != 0) ? (int)$params['homeOdds']  : 100;
    $params['awayOdds']  = (is_int((int)$params['awayOdds']) && (int)$params['awayOdds'] != 0) ? (int)$params['awayOdds']  : 100;
//	$params['homeSpread']  = (is_int((float)$params['homeSpread']) && (float)$params['homeSpread'] != 0) ? (float)$params['homeSpread']  : 100;
//	$params['awaySpread']  = (is_int((float)$params['awaySpread']) && (float)$params['awaySpread'] != 0) ? (float)$params['awaySpread']  : 100;

	$this->gambling_model->trackRequest($params);
	$sport = $this->uri->segment(3);
	switch($params['betType']){
	  case 'moneyline':
		if ($sport == "hockey")
		{
			$moneylineOut = $this->gambling_model->moneylineBetOnline($params);
		}
		else if ($sport == "baseball")
		{
			$moneylineOut = $this->gambling_model->moneylineBetOnlineMLB($params);
		}
		$cumProfit = $moneylineOut['cumProfit'];
		$avgProft = $moneylineOut['avgProfit'];
		$calcOdds = $moneylineOut['calcOdds'];
		$oddsPinnacle = $moneylineOut['oddsPinnacle'];
		$oddsBetOnline = $moneylineOut['oddsBetOnline'];
		$cumProfitBetOnline = $moneylineOut['cumProfitBetOnline'];
		$avgProftBetOnline = $moneylineOut['avgProfitBetOnline'];
		
		
		$moneyline[] = $moneylineOut['data'];
		$moneylineBetOnline2[] = $moneylineOut['dataBetOnline'];
		$csvText = $this->gambling_model->genCSVRecursiveCategory($moneyline);
		$csvTextBetOnline = $this->gambling_model->genCSVRecursiveCategory($moneylineBetOnline2);
		//echo "testing: $csvText";
		$return[0] = $csvText;
		//$return[1] = $csvTextBetOnline;
		$return[1] = "$".number_format ($cumProfit,2,'.',',');
		$return[2] = "$".number_format ($avgProft,2,'.',',');
		$return[3] = round($oddsPinnacle,2);
		$return[4] = $csvTextBetOnline;
		$return[5] = "$".number_format ($cumProfitBetOnline,2,'.',',');
		$return[6] = "$".number_format ($avgProftBetOnline,2,'.',',');
		$return[7] = round($oddsBetOnline,2);
		//print_r($return);
		break;
	  case 'puckline':
		if ($sport == "hockey")
		{	  
			$pucklineOut =	$this->gambling_model->pucklineBetOnline($params);
		}
		else if ($sport == "baseball")
		{	  
			$pucklineOut =	$this->gambling_model->pucklineBetOnlineMLB($params);
		}
		$cumProfit = $pucklineOut['cumProfit'];
		$avgProft = $pucklineOut['avgProfit'];
		$calcOdds = $pucklineOut['calcOdds'];
		$oddsPinnacle = $pucklineOut['oddsPinnacle'];
		$oddsBetOnline = $pucklineOut['oddsBetOnline'];
		$cumProfitBetOnline = $pucklineOut['cumProfitBetOnline'];
		$avgProftBetOnline = $pucklineOut['avgProfitBetOnline'];
		//print_r($puckline);
		//$return = $this->stats_model->genCSVRecursiveHigh($goals,0,$arrPlayers);
		$puckline[] = $pucklineOut['data'];
		$pucklineBetOnline[] = $pucklineOut['dataBetOnline'];
		$csvText = $this->gambling_model->genCSVRecursiveCategory($puckline);
		$csvTextBetOnline = $this->gambling_model->genCSVRecursiveCategory($pucklineBetOnline);
		$return[0] = $csvText;
		$return[1] = "$".number_format ($cumProfit,2,'.',',');
		$return[2] = "$".number_format ($avgProft,2,'.',',');
		$return[3] = round($oddsPinnacle,2);
		$return[4] = $csvTextBetOnline;
		$return[5] = "$".number_format ($cumProfitBetOnline,2,'.',',');
		$return[6] = "$".number_format ($avgProftBetOnline,2,'.',',');
		$return[7] = round($oddsBetOnline,2);
		break;
	  case 'gametotals':
		if ($sport == "hockey")
		{	  
			$gametotalsOut = $this->gambling_model->gametotalsBetOnline($params);
		}
		else if ($sport == "baseball")
		{
			$gametotalsOut = $this->gambling_model->gametotalsBetOnlineMLB($params);
		}
		$cumProfit = $gametotalsOut['cumProfit'];
		$avgProft = $gametotalsOut['avgProfit'];
		$calcOdds = $gametotalsOut['calcOdds'];
		$oddsPinnacle = $gametotalsOut['oddsPinnacle'];
		$oddsBetOnline = $gametotalsOut['oddsBetOnline'];
		$cumProfitBetOnline = $gametotalsOut['cumProfitBetOnline'];
		$avgProftBetOnline = $gametotalsOut['avgProfitBetOnline'];
		
		$gametotals[] = $gametotalsOut['data'];
		$gametotalsBetOnline[] = $gametotalsOut['dataBetOnline'];
		$csvText = $this->gambling_model->genCSVRecursiveCategory($gametotals);
		$csvTextBetOnline = $this->gambling_model->genCSVRecursiveCategory($gametotalsBetOnline);
		$return[0] = $csvText;
		$return[1] = "$".number_format ($cumProfit,2,'.',',');
		$return[2] = "$".number_format ($avgProft,2,'.',',');
		$return[3] = round($oddsPinnacle,2);
		$return[4] = $csvTextBetOnline;
		$return[5] = "$".number_format ($cumProfitBetOnline,2,'.',',');
		$return[6] = "$".number_format ($avgProftBetOnline,2,'.',',');
		$return[7] = round($oddsBetOnline,2);
		break;
	}
	
    echo json_encode($return);
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
  function getGames(){
    $currentUser = $this->ion_auth->get_user();
   // print_r($currentUser->group_id);
    $this->load->model('gambling_model');
    $json = array('code' => 0);
    $sport = $this->uri->segment(3);
	$arrPost = $this->uri->uri_to_assoc(4);
    $today = date('Y-m-d');
    $yesterday = strtotime('-1 day',strtotime($today));
    $yesterday = date('Y-m-d', $yesterday);
    $tomorrow = strtotime('+1 day',strtotime($today));
    $tomorrow = date('Y-m-d',$tomorrow);
   /* if ($arrPost['date'] != 'yesterday' && $currentUser->group_id == 1)
    {
     // $this->session->set_flashdata('message', 'You must be a premium member to view this page');
      //redirect('welcome/index');
      $json['code'] = 1;
      $json['message'] = 'You must be a member to view this content';
    }*/
	//echo "sport:=$sport";
	if ($sport == "baseball")
	{
		switch($arrPost['date'])
		{
		  case 'today':
			$json['htmlGames'] = $this->gambling_model->getGameIDsFromDateinSchedualMLB($today);
			break;
		  case 'tomorrow':
			$json['htmlGames'] = $this->gambling_model->getGameIDsFromDateinSchedualMLB($tomorrow);
			//print_r($json['htmlGames']);
			break;
		  default:
			$json['htmlGames'] = $this->gambling_model->getGameIDsFromDateinSchedualMLB($yesterday);
			//print_r($json['htmlGames']);
		}
	}
	else if ($sport == "hockey")
	{
		switch($arrPost['date'])
		{
		  case 'today':
			$json['htmlGames'] = $this->gambling_model->getGameIDsFromDateinSchedual($today);
			break;
		  case 'tomorrow':
			$json['htmlGames'] = $this->gambling_model->getGameIDsFromDateinSchedual($tomorrow);
			break;
		  default:
			$json['htmlGames'] = $this->gambling_model->getGameIDsFromDateinSchedual($yesterday);
		}
	}


    echo json_encode($json);
  }
}

/* End of file welcome.php */
/* Location: ./application/controllers/welcome.php */