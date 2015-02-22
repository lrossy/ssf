<?php 
error_reporting(E_ALL & ~E_NOTICE);
if ( ! defined('BASEPATH')) exit('No direct script access allowed'); 

//Start, End, Debug, Gametype//
//include_once 'functions.php';
require_once 'Cache/Lite.php'; 

/*************************
Scrape.class.php
Description: This class scrapes nhl.com event and game summaries and loads them into database
Example event summary : http://www.nhl.com/scores/htmlreports/20082009/ES020018.HTM
Example game summary: http://www.nhl.com/scores/htmlreports/20082009/GS020018.HTM
Last Modified: 6/15/2010
Functions:
	Scrape($start,$end=1230,$debug,$playoff_marker) -> Main class that loops through the event summaries
	function _scrape($gameId,$gameType) -> cURL connection to pull data from NHL.com returns HTML source, checks for timeout errors
	add_new_player($fname, $lname, $team, $position,$fullname) -> Inserts a player into the DB
	check_player($fname, $lname) -> Checks if player exists, if so, returns playerID
	check_team_id($teamName) -> Finds team ID from Game Summary team name
	get_event_summary_data($GameID,$debug) -> Parent function for insert_event_data, manages HTML source returned from cURL
	insert_event_data($Visdata,$GameID,$indicator,$team,$teamAgainst) -> finds and inserts event summary data into database
	get_game_data($GameID) - > Pulls game data for table games from game summary
Support Functions:
	leading_zeros($value, $places) -> adds leading zeros for properly formatted URLs
	dbConnect() -> Database connection
	strip_attributes($msg, $tag, $attr="", $suffix = "") -> Used to clean HTML from regex
*************************/
class Scrape_model extends CI_Model{
	//var $SeasonID = "20072008";
	//var $SeasonID = "20092010";
	var $SeasonID = "20082009";
	//var $SeasonID = "20082009";
	var $gameSumPre='http://www.nhl.com/scores/htmlreports/';
	var $boxScore= 'http://www.nhl.com/ice/boxscore.htm?id=';
	var $postFix='.HTM';
	var $gameFeed,$eventFeed;
 	var $useragent = "Mozilla/5.1 (compatible; Googlebot/2.1; +http://www.google.com/bot.html"; 
	var $frenchCheck ='Sommaire du Match';
	var $preES = 'ES0';
	var $preGS = 'GS0';
	var $debug2 = 0;
	var $playoff_marker = 3;
	var $BilAdjust = 0;
	var $gameType = '2';
	var $arrGameData ='';
	var $SEP_WEB ='<br />';
	var $SEP_CON ="\n";
	var $sep = '';
	//var $preGS = 'GS03';
    function __construct()
    {
        // Call the Model constructor
        parent::__construct();
		$this->sep = $this->SEP_WEB;
    }
	function dailyWrapUp(){
		$arrTG = $this->findGames();
		//$this->getNHLPlayers2();
		//Yesterdays games
		//print_r($arrTG);
		$this->start($arrTG,$this->playoff_marker,1);
		
		//Last Weeks games
		
		$arrTGLW = $this->findGames(0);
		$this->start($arrTGLW,$this->playoff_marker, 1);

	}
	function nightly(){
		//$this->debug = $debug;
		//TONiGHTS  GAMES
		
		$arrTGLW = $this->findGames(2);
		$this->start($arrTGLW ,$this->playoff_marker, 1);
		return $arrTGLW;
	}
	function rescrape($start,$end,$season, $debug2,$playoff_marker='2',$rescrape='0'){
		$this->debug2 = $debug2;
		$this->rescape = $rescrape;
		$this->is_playoff = $playoff_marker;
		$this->SeasonID= $season;
		if($this->is_playoff=='3'){
			//loop through round
			for($z=1;$z<=4;$z++)
			{
				switch ($z) {
				case 1:
					$a = 8;
					break;
				case 2:
					$a = 4;
					break;
				case 3:
					$a = 2;
					break;
				case 4:
					$a = 1;
					break;
				}
				//loop through series
				for($y=1;$y<=$a;$y++)
				{
					//loop through games
					for($x=1;$x<=7;$x++)
					{
						$gameID = '0'.$z.$y.$x;
					//	echo $gameID.'<br>';

					$gameSummary = $this->get_game_data($gameID);
					if($gameSummary){
					$eventSummary = $this->get_event_summary_data($gameID,$this->debug2);
					$this->get_goals_assist_data($gameID);
					$this->get_penalty_data($gameID);
					$this->get_goalie_data($gameID);
					}
					}
				}
			}
		}
		//Regular season
		else{
		for($i = $start;$i<=$end;$i++)
		{
			if( $this->SeasonID =='20082009' & ($currentID == 259 | $currentID== 409 | $currentID == 1077)){
				next;
			}
			$num= $this->leading_zeros($i,4);
			$this->start = $num;
			$gameSummary = $this->get_game_data($num);
			$eventSummary = $this->get_event_summary_data($num,$this->debug2);

			$this->get_goals_assist_data($num);
			$this->get_penalty_data($num);
			$this->get_goalie_data($num);
		}
		}
	}
	function findGames($f=1){
		if($f==2)
		$dateStr =  date  ( 'Y-m-d', strtotime("-6 hours") );
		elseif($f==1)
		$dateStr =  date  ( 'Y-m-d', strtotime("-1 days") );
		else
		$dateStr = date("Y-m-d", strtotime("-1 weeks"));

		$sql="SELECT id, date  FROM nhl_schedual WHERE date = '$dateStr'";
		$query = $this->db->query($sql);
		if(count($query->result())>=1)
		{	//echo $sql;
			foreach ($query->result() as $row)
			{
				$num= $this->leading_zeros($row->id,4);
				$gData[] = $num;
			}
			return $gData;
		}
		else return 0;
	}
	function getMinGameID($gData){
		$min = 9999;
		foreach ($gData as $gameID){
			if($gameID['id']<$min)
				$min = $gameID['id'];
		}
		return $min;
	}	
	function getMaxGameID($gData){
		$max = -1;
		foreach ($gData as $gameID){
			if($gameID['id']>$max)
				$max = $gameID['id'];
		}
		return $max;
	}
	function start($arrIds, $playoff_marker='2',$rescrape='0'){

		$this->rescape = $rescrape;
		//$this->start = $start;
		//$this->end = $end;

		$this->is_playoff = $playoff_marker;

		if($this->is_playoff=='3'){
			//loop through round
			for($z=1;$z<=4;$z++)
			{
				switch ($z) {
				case 1:
					$a = 8;
					break;
				case 2:
					$a = 4;
					break;
				case 3:
					$a = 2;
					break;
				case 4:
					$a = 1;
					break;
				}
				//loop through series
				for($y=1;$y<=$a;$y++)
				{
					//loop through games
					for($x=1;$x<=7;$x++)
					{
						$gameID = '0'.$z.$y.$x;
					//	echo $gameID.'<br>';

					$gameSummary = $this->get_game_data($gameID);
					if($gameSummary){
					$eventSummary = $this->get_event_summary_data($gameID,$this->debug2);
					$this->get_goals_assist_data($gameID);
					$this->get_penalty_data($gameID);
					}
					}
				}
			}
		}
		//Regular season
		//there are 15 * 82 = 1230 games in a season
		//loop through all 20082009 season
		else{
			foreach($arrIds as $currentID)
			{
				if( $this->SeasonID =='20082009' & ($currentID == 259 | $currentID== 409 | $currentID == 1077)){
					next;
				}
				//$num= $this->leading_zeros($currentID,4);
				$this->start = $currentID;
				$gameSummary = $this->get_game_data($currentID);
				$eventSummary = $this->get_event_summary_data($currentID,$this->debug2);

				$this->get_goals_assist_data($currentID);
				$this->get_penalty_data($currentID);
				$this->get_goalie_data($currentID);
			}
		}
	}
	function scrapeFile($filenameGS,$filenameES,$currentID){

		$gameSummary = $this->get_game_data($currentID,$filenameGS);
		$eventSummary = $this->get_event_summary_data($currentID,$this->debug2,$filenameES);
		$this->get_goals_assist_data($currentID);
		$this->get_penalty_data($currentID);
		$this->get_goalie_data($currentID);
	}
	function scrapeBoxScore($season, $gameType, $currentID,$gameDate,$playoff_marker='2',$debug =1){
		$this->debug2 = $debug;
		$this->SeasonID= $season;
		$this->is_playoff = $playoff_marker;
		$boxscore = $this->getBoxScoreData($currentID,$gameType,$gameDate);
		$eventSummary = $this->getBS_event_summary_data($currentID,$this->debug2,$filenameES);
		$this->getBS_goals_assist_data($currentID);
		$this->getBS_penalty_data($currentID);
		//$this->get_goalie_data($currentID);
	}
	function _scrape($gameId,$gameType,$file=0, $boxscore = 0, $gameDate){
		
		if($file){
			$filename = "/var/www/smartsportsfan.com/luke/$file";
			echo $filename;
			$handle = fopen($filename, "r");
			$contents = fread($handle, filesize($filename));
				$xml = str_replace("\t",'',$contents);
				$xml = str_replace("\n",'',$xml);
				$xml = str_replace("\r",'',$xml);
				$xml = preg_replace('/\s\s+/', ' ', $xml);
				return $xml;
		}else{
		$pageID =  $this->SeasonID . $gameType . $gameId;
		if(!$boxscore){
			
		$f ='';
			$scrape_url = "http://www.nhl.com/scores/htmlreports/" . $firstFour .'/'. $gameType . $gameId; 
		}
		else{
			$firstFour =substr($this->SeasonID, 0, 4);
			$f = 'bs';
			$scrape_url = $this->boxScore . $firstFour . $gameType . $gameId; 
		}
		$ch = curl_init();
		$headers = 'Content-type: application/x-www-form-urlencoded;charset=UTF-8'; 
		curl_setopt($ch, CURLOPT_USERAGENT, $this->useragent);
		curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 4);
		curl_setopt($ch, CURLOPT_TIMEOUT, 8);
		curl_setopt($ch, CURLOPT_URL, $scrape_url);
		//curl_setopt($ch, CURLOPT_HTTPHEADER, Array("Content-Type: text/html;charset=UTF-8")); 


		$options = array(
			'cacheDir' => "/var/data/cache/$this->SeasonID$f/",
			'lifeTime' => 31536000, //1 year
			'pearErrorMode' => CACHE_LITE_ERROR_DIE
		);
		$cache = new Cache_Lite($options);
		if(!$this->rescape){
			if ($xml = $cache->get($pageID)) {
				echo "Cache Hit for $gameId $this->sep Page : $scrape_url$this->sep";
				// Cache hit !
				// Content is in $data
				return $xml;

			} else { 
				echo "Adding Game $gameId $this->sep Page : $scrape_url$this->sep";
				// No valid cache found (you have to make and save the page)
				$xml = curl_exec ($ch);
			
				if(!curl_errno($ch))
				{
					$info = curl_getinfo($ch);
					if ($info['http_code'] == 302) {
						$data = "notok";
						echo 'Time Out ' . $info['total_time'] . ' can not send request to ' . $info['url'] . "$this->sep";
					//	$this->scrape($this->start,$this->end,$this->debug,$this->is_playoff);
					}
					echo 'Took ' . $info['total_time'] . ' seconds to send a request to ' . $info['url'] . "$this->sep";
					curl_close ($ch);
				}
				else{
					sleep(20);
					curl_close ($ch);
					//$this->scrape($this->start,$this->end,$this->debug,$this->is_playoff);
					return 0;
				}
				//print_r($xml);
				$xml = str_replace("\t",'',$xml);
				$xml = str_replace("\n",'',$xml);
				$xml = str_replace("\r",'',$xml);
				$xml = preg_replace('/\s\s+/', ' ', $xml);
				if(!$boxscore){
					$cache->save($xml,$pageID);
				}
			}
		}
		else
			{ 
				echo "Adding Game $gameId $this->sep Page : $scrape_url$this->sep";
				// No valid cache found (you have to make and save the page)
				$xml = curl_exec ($ch);
			
				if(!curl_errno($ch))
				{
					$info = curl_getinfo($ch);
					if ($info['http_code'] == 302) {
						$data = "notok";
						echo 'Time Out ' . $info['total_time'] . ' can not send request to ' . $info['url'] . "$this->sep";
					//	$this->scrape($this->start,$this->end,$this->debug,$this->is_playoff);
					}
					echo 'Took ' . $info['total_time'] . ' seconds to send a request to ' . $info['url'] . "$this->sep";
					curl_close ($ch);
				}
				else{
					sleep(20);
					curl_close ($ch);
					//$this->scrape($this->start,$this->end,$this->debug,$this->is_playoff);
					return 0;
				}
				//print_r($xml);
				$xml = str_replace("\t",'',$xml);
				$xml = str_replace("\n",'',$xml);
				$xml = str_replace("\r",'',$xml);
				$xml = preg_replace('/\s\s+/', ' ', $xml);
				$cache->save($xml,$pageID);
			}
		return $xml;
		}
	}
	function _scrape_sched($url){
		$scrape_url = $url; 
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_USERAGENT, $this->useragent);
		curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 4);
		curl_setopt($ch, CURLOPT_TIMEOUT, 8);
		curl_setopt($ch, CURLOPT_URL, $scrape_url);
		echo "Adding Game $gameId $this->sep Page : $scrape_url$this->sep";
		// No valid cache found (you have to make and save the page)
		$xml = curl_exec ($ch);
		if(!curl_errno($ch))
		{
			$info = curl_getinfo($ch);
			if ($info['http_code'] == 302) {
				$data = "notok";
				echo 'Time Out ' . $info['total_time'] . ' can not send request to ' . $info['url'] . "$this->sep";
			//	$this->scrape($this->start,$this->end,$this->debug,$this->is_playoff);
			}
			echo 'Took ' . $info['total_time'] . ' seconds to send a request to ' . $info['url'] . "$this->sep";
			curl_close ($ch);
		}
		else{
			sleep(20);
			curl_close ($ch);
			//$this->scrape($this->start,$this->end,$this->debug,$this->is_playoff);
			return 0;
		}
		//print_r($xml);
		$xml = str_replace("\t",'',$xml);
		$xml = str_replace("\n",'',$xml);
		$xml = str_replace("\r",'',$xml);
		$xml = preg_replace('/\s\s+/', ' ', $xml);
		return $xml;
	}
	function leading_zeros($value, $places){
		$leading ='';
		if(is_numeric($value)){
			for($x=1; $x <= $places; $x++){
				$ceiling=pow(10, $x);
				if($value < $ceiling){
					$zeros=$places - $x;
					for($y=1; $y <= $zeros; $y++){
						$leading .= "0";
					}
				$x=$places + 1;
				}
			}
			$output=$leading . $value;
		}
		else{
			$output=$value;
		}
		return $output;
	}
	function dbConnect(){
		$this->db_user="27_ssf432";
		$this->db_pass="l12321l";
		$this->database="27_ssf";
		$this->mysql_id =	mysql_connect('localhost', $this->db_user, $this->db_pass) or die("Cannot connect to mySQL");
		mysql_select_db($this->database,	$this->mysql_id) or die("Can't find database:	".$this->database);
	}
	function strip_attributes($msg, $tag, $attr="", $suffix = ""){
     $lengthfirst = 0;
     while (strstr(substr($msg, $lengthfirst), "<$tag ") != "") {
          $tag_start = $lengthfirst + strpos(substr($msg, $lengthfirst), "<$tag ");

          $partafterwith = substr($msg, $tag_start);

          $img = substr($partafterwith, 0, strpos($partafterwith, ">") + 1);
          $img = str_replace(" =", "=", $img);

          $out = "<$tag";
          for($i = 0; $i < count($attr); $i++) {
               if (empty($attr[$i])) {
                    continue;
               }
               $long_val =
               (strpos($img, " ", strpos($img, $attr[$i] . "=")) === false) ?
               strpos($img, ">", strpos($img, $attr[$i] . "=")) - (strpos($img, $attr[$i] . "=") + strlen($attr[$i]) + 1) :
               strpos($img, " ", strpos($img, $attr[$i] . "=")) - (strpos($img, $attr[$i] . "=") + strlen($attr[$i]) + 1);
               $val = substr($img, strpos($img, $attr[$i] . "=") + strlen($attr[$i]) + 1, $long_val);
               if (!empty($val)) {
                    $out .= " " . $attr[$i] . "=" . $val;
               }
          }
          if (!empty($suffix)) {
               $out .= " " . $suffix;
          }

          $out .= ">";
          $partafter = substr($partafterwith, strpos($partafterwith, ">") + 1);
          $msg = substr($msg, 0, $tag_start) . $out . $partafter;
          $lengthfirst = $tag_start + 3;
     }
     return $msg;
	}
	function getOldPlayer($fname, $lname){
		$scrape_url = "http://www.nhl.com/ice/search.htm?q=$fname+$lname&tab=players";
		$ch2 = curl_init();
		curl_setopt($ch2, CURLOPT_USERAGENT, $this->useragent);
		curl_setopt ($ch2, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch2, CURLOPT_CONNECTTIMEOUT, 4);
		curl_setopt($ch2, CURLOPT_TIMEOUT, 8);
		curl_setopt($ch2, CURLOPT_URL, $scrape_url);
		$xml = curl_exec ($ch2);
		curl_close ($ch2);
		//print_r($xml);
		$xml = str_replace("\t",'',$xml);
		$xml = str_replace("\n",'',$xml);
		$xml = str_replace("\r",'',$xml);
		$xml = preg_replace('/\s\s+/', ' ', $xml);

		preg_match_all('#<div class="search_header">(.*?)</div></div>#', $xml, $data, PREG_SET_ORDER); 

		$newData = str_replace("#&amp;",'ENDHERE',$data[0][1]);
		$newData = str_replace("player.htm?id=",'STARTHERE',$newData);
		preg_match_all('#STARTHERE(.*?)ENDHERE#', $newData, $playerID, PREG_SET_ORDER); 

		$newID = $playerID[0][1];
		$sql = "REPLACE INTO `nhl_players` (
		id,fname, lname, fullname)
		VALUES ('$newID',
		'$fname',
		'$lname',
		'$fname $lname')";
		
		//echo "$sql $this->sep";
		if(!$this->debug2 & !empty($newID)){
			$query = $this->db->query($sql);
		}
		return $newID;
	}
	function add_new_player($fname, $lname, $team, $position,$fullname,$number){
		  $fname = str_replace(".", "", $fname);
		  $lname = str_replace(".", "", $lname);
		  $actFullname = trim($fname) . ' ' . trim($lname);
		  $fullname = str_replace(".", "", $fullname);
		if (get_magic_quotes_gpc()) $fname = stripslashes($fname);
		$fname = mysql_escape_string($fname);
		if (get_magic_quotes_gpc()) $lname = stripslashes($lname);
		$lname = mysql_escape_string($lname);
		if (get_magic_quotes_gpc()) $fullname = stripslashes($fullname);
		$fullname = mysql_escape_string($fullname);
		if (get_magic_quotes_gpc()) $actFullname = stripslashes($actFullname);
		$actFullname = mysql_escape_string($actFullname);

		$sql2="SELECT * FROM nhl_players WHERE fname LIKE '$fname%' AND lname = '$lname'";
		$query2 = $this->db->query($sql2);
		if(count($query2->result())>=1)
		{	//echo $sql;
			foreach ($query2->result() as $row)
			{
				$NHL_ID = $row->id;
				$TEAM = $row->team;
			}
		$query2->free_result();
		}
		else{
			//rerun nhl players
			//$this->getNHLPlayers2();
			$NHL_ID = $this->getOldPlayer($fname, $lname);
		}
		if(empty($NHL_ID)){
			//different name
			//select from nhl_players where fname is only 2 or 3, insert into mapping
			$fnameShort3 = substr($fname,0,3);
			$sql3="SELECT * FROM nhl_players WHERE fname LIKE '$fnameShort3%' AND lname = '$lname'";
			//echo $sql3;
			$query3 = $this->db->query($sql3);
			if ($query3->num_rows() > 0)
			{	
				foreach ($query3->result() as $row3)
				{
					$NHL_ID = $row3->id;
					$TEAM = $row3->team;
				}
			}
			else{
				$fnameShort2 = substr($fname,0,2);
				$sql4="SELECT * FROM nhl_players WHERE fname LIKE '$fnameShort2%' AND lname = '$lname'";
				
				$query4 = $this->db->query($sql4);
				//echo  $query4->num_rows();
				if(count($query4->result())>=1)
				{	//echo $sql;
					foreach ($query4->result() as $row4)
					{
						$NHL_ID = $row4->id;
						$TEAM = $row4->team;
					}
				}

			}
			echo "diff name$this->sep";
		}
		$teamIDVer = $this->get_team_id_from_abbr($TEAM);


		$sql="INSERT into new_player (`id` ,`player_f_name` ,`player_l_name` ,`game_summary_mapping` ,`position` ,`team_id`,full_name,`number`,`nhl_id`)VALUES ('', '$fname', '$lname', '$fullname', '$position', '$team',' $actFullname',' $number','$NHL_ID');";
		echo "Adding player: $fname $lname -- $team -- $NHL_ID$this->sep";
		//$result = mysql_db_query($this->database, $sql) or die("A MySQL error has occurred.<br />Your Query:	" .	$sql . "<br	/> Error: (" . mysql_errno() . ") "	. mysql_error());  //do	the	query
		//if($result) return mysql_insert_id();
		//echo $sql;
		$query = $this->db->query($sql);
		return $this->db->insert_id();
	}
	function check_player($fname, $lname,$teamId, $number){
		  $fname = str_replace(".", "", $fname);
		  $lname = str_replace(".", "", $lname);
		if (get_magic_quotes_gpc()) $fname = stripslashes($fname);
		$fname = mysql_escape_string($fname);
		if (get_magic_quotes_gpc()) $lname = stripslashes($lname);
		$lname = mysql_escape_string($lname);	
		if($lname=='SUTTER'){
			$fname = substr($fname,0,3);
		}
		else{
			$fname = substr($fname,0,1);
		}
		
		
		$sql="SELECT * FROM new_player WHERE player_f_name LIKE '$fname%' AND player_l_name = '$lname' AND team_id='$teamId' AND number = $number";
		//echo $sql;
		$query = $this->db->query($sql);
		if(count($query->result())>=1)
		{	//echo $sql;
			foreach ($query->result() as $row)
			{
				$playerData['id'] = $row->id;
				$playerData['game_summary_mapping'] = $row->game_summary_mapping;
				$playerData['position'] = $row->position;
				$playerData['team_id']= $row->team_id;
			}
			return $playerData['id'];
		}
		else return false;
	}
	function check_playerNEW($fname, $lname,$teamId, $number){
		  $fname = str_replace(".", "", $fname);
		  $lname = str_replace(".", "", $lname);
		if (get_magic_quotes_gpc()) $fname = stripslashes($fname);
		$fname = mysql_escape_string($fname);
		if (get_magic_quotes_gpc()) $lname = stripslashes($lname);
		$lname = mysql_escape_string($lname);	
		//		if($lname=='SUTTER'){
		//			$fname = substr($fname,0,3);
		//		}
		//		else{
		//			$fname = substr($fname,0,1);
		//		}
		//!check nhl id
		//get ssf_id

		$sql2="SELECT * FROM nhl_players WHERE fname LIKE '$fname%' AND lname = '$lname'";
		//echo $sql2;
		$query2 = $this->db->query($sql2);
		if(count($query2->result())>=1)
		{	//echo $sql;
			foreach ($query2->result() as $row)
			{
				$NHL_ID = $row->id;
				$TEAM = $row->team;
			}
		}
		else{
			//fetch player info
		}
		$teamIDVer = $this->get_team_id_from_abbr($TEAM);

		$sql="SELECT * FROM new_player WHERE nhl_id = '$NHL_ID' AND team_id='$teamIDVer' AND number ='$number'";
		//echo $sql;
		$query = $this->db->query($sql);
		if(count($query->result())>=1)
		{	//echo $sql;
			foreach ($query->result() as $row)
			{
				$playerData['id'] = $row->id;
				$playerData['game_summary_mapping'] = $row->game_summary_mapping;
				$playerData['position'] = $row->position;
				$playerData['team_id']= $row->team_id;
				$playerData['number']= $row->number;
				$playerData['nhl_id']= $row->nhl_id;
			}
			return $playerData['id'];
		}
		else return false;
	}
	function checkNHLID($teamId, $nhlID){

		
		$sql="SELECT * FROM new_player WHERE team_id='$teamId' AND nhl_id = $nhlID";
		//echo $sql.";$this->sep";
		$query = $this->db->query($sql);
		if(count($query->result())>=1)
		{	//echo $sql;
			foreach ($query->result() as $row)
			{
				$playerData['id'] = $row->id;
				$playerData['game_summary_mapping'] = $row->game_summary_mapping;
				$playerData['position'] = $row->position;
				$playerData['team_id']= $row->team_id;
			}
			return $playerData['id'];
		}
		else return false;
	}
	function check_team_id($teamName){
		$id='';
		$sql="SELECT team_id,nhl FROM new_team WHERE ( game_summary_mapping = '$teamName' or extra = '$teamName' )";
		//echo $sql;
		$query = $this->db->query($sql);
		if(count($query->result())>=1)
		{	//echo $sql;
			foreach ($query->result() as $row)
			{
				$id= $row->team_id;
				$nhl= $row->nhl;
			}
		}
		return $id;
	}
	function get_team_id_from_abbr($teamAbbr){
		$id='';
		$sql="SELECT team_id,nhl FROM new_team WHERE  nhl = '$teamAbbr'";
		$query = $this->db->query($sql);
		if(count($query->result())>=1)
		{	//echo $sql;
			foreach ($query->result() as $row)
			{
				$id= $row->team_id;
				$nhl= $row->nhl;
			}
		}
		return $id;
	}
	function check_team_abbr($teamName){
		$id='';
		$sql="SELECT team_id FROM new_team WHERE game_summary_abbreviation = '$teamName'";

		$query = $this->db->query($sql);
		if(count($query->result())>=1)
		{	//echo $sql;
			foreach ($query->result() as $row)
			{
				$id= $row->team_id;
			}
		}
		return $id;
	}
	function get_event_summary_data($GameID,$debug,$file=0){
		$gameType = $this->gameType;
		$marker = $this->preES.$this->is_playoff;
		$data = $this->_scrape($GameID,$marker,$file);

		$pos = strpos($data, '<h1>Not Found</h1>');
		if ($pos !== false) {
		   return false;
		}
		//Step 1 Get Home and visitor teams 
		//	print_r($data);//	die();
		preg_match_all('#<td align="center" colspan="3"(.*?)</td>#', $data, $teams, PREG_SET_ORDER); 

		$awTeam  = explode(">", $teams[0][1]);
		$hoTeam  = explode(">", $teams[1][1]);
		//print_r($awTeam);print_r($hoTeam);
		$this->homeTeam = $hoTeam[1];
		$this->awayTeam = $awTeam[1];

		if(!strpos($data,$this->frenchCheck)){
			//Step 2: Loop though english
			$this->BilAdjust = 0;
			preg_match_all('#visitorsectionheading" width="0px">EV</td></tr>(.*?)TEAM TOTALS#', $data, $vistorData, PREG_SET_ORDER);
			if(empty($vistorData[0][1])){
				
				preg_match_all('#visitorsectionheading">EV</td></tr>(.*?)TEAM TOTALS#', $data, $vistorData, PREG_SET_ORDER);
				$Visdata = $vistorData[0][1];
				//print_r($vistorData);
			}else{
			$Visdata = $vistorData[0][1];
			}
			$this->insert_event_data($Visdata,$GameID,'A',$this->awayTeam,$this->homeTeam);
			preg_match_all('#homesectionheading" width="0px">EV</td></tr>(.*?)TEAM TOTALS#', $data, $homeData, PREG_SET_ORDER);
			if(empty($homeData[0][1])){
				preg_match_all('#homesectionheading">EV</td></tr>(.*?)TEAM TOTALS#', $data, $homeData, PREG_SET_ORDER);
				$Homdata = $homeData[0][1];
				//print_r($vistorData);
			}else{
			$Homdata = $homeData[0][1];
			}
			$this->insert_event_data($Homdata,$GameID,'H',$this->homeTeam,$this->awayTeam);

		}
		else{
			//Step 2: Loop though french
			$this->BilAdjust = 1;
			preg_match_all('#visitorsectionheading">FÉ/EV</td></tr>(.*?)TEAM TOTALS#', $data, $vistorData, PREG_SET_ORDER);
			$Visdata = $vistorData[0][1];
			$this->insert_event_data($Visdata,$GameID,'A',$this->awayTeam,$this->homeTeam);
			preg_match_all('#homesectionheading">FÉ/EV</td></tr>(.*?)TEAM TOTALS#', $data, $homeData, PREG_SET_ORDER);
			$Homdata = $homeData[0][1];
			$this->insert_event_data($Homdata,$GameID,'H',$this->homeTeam,$this->awayTeam);
		}
	}
	function insert_event_data($Visdata,$GameID,$indicator,$team,$teamAgainst){
		$Visdata = str_replace(' class="evenColor"','',$Visdata);
		$Visdata = str_replace(' class="oddColor"','',$Visdata);
		preg_match_all('#<tr>(.*?)</tr>#', $Visdata, $vistorDataSplit, PREG_SET_ORDER); 
		foreach ($vistorDataSplit as $vistor){
			$arrVisitor=$this->strip_attributes($vistor[1],'td');
			preg_match_all('#<td>(.*?)</td>#', $arrVisitor, $arrPlayer, PREG_SET_ORDER);
			$arrVisPlayer='';
			foreach($arrPlayer as $player){
			$arrVisPlayer[] = str_replace('&nbsp;','',$player[1]);
			}
			if($arrVisPlayer[1]=='G'){
				$teamId = $this->check_team_id($team);
				$this->arrGameData['goalies'][$teamId][]=$arrVisPlayer;
			}
			$arrVisitorPlayers[]=$arrVisPlayer;
		}
		//print_r($arrGameData);  
		//Step:3 'Pull in the current player name
		foreach($arrVisitorPlayers as $visitor){
			//print_r( $visitor)
			$name = explode(',',$visitor[2]);
			$PlayerFullName =trim($visitor[2]);
			$PlayerFName =trim($name[1]);
			$PlayerLName =trim($name[0]);
			$PlayerPos	= $visitor[1];
			//Go Find Player ID and input into Event Summary Table
			$teamId = $this->check_team_id($team);
			//$teamNHL_name = $this->check_team_id($team,0);
			$playerId = $this->check_player($PlayerFName, $PlayerLName,$teamId,$visitor[0]);

			//$playerId = $this->check_playerNEW($PlayerFName, $PlayerLName,$teamId,$visitor[0]);
			//echo "$playerId - ($PlayerFName, $PlayerLName,$teamId)";
			if(!$playerId){
				$playerId = $this->add_new_player($PlayerFName, $PlayerLName, $teamId, $PlayerPos,$PlayerFullName,$visitor[0]);
				//insert new player
			}
			//insert into game summary
			$teamAgainstId = $this->check_team_id($teamAgainst);

			$insGameID = $this->SeasonID. $this->is_playoff . $GameID;
			$primaryKey = $insGameID . $playerId;			
			$event_summary_SQL = "INSERT INTO `new_event_summary` (`id` ,`goals` ,`assists` ,`points` ,`plus_minus` ,`number_of_penalities` ,`total_toi` ,`num_shifts` ,`avg_time_shift` ,`pp_toi` ,`sh_toi` ,`es_toi` ,`ot_toi` ,`sog` ,`attempts_blocked` ,`missed_shots` ,`hits_given` ,`giveaways` ,`takeaways` ,`blocked_shots` ,`faceoffs_won` ,`faceoffs_lost` ,`faceoff_win_percentage` ,`home_away_indicator` ,`game_id` ,`team_id` ,`team_against_id` ,`player_id`) VALUES ( '$primaryKey', '$visitor[3]', '$visitor[4]', '$visitor[5]', '$visitor[6]', '$visitor[7]', '$visitor[9]', '$visitor[10]', '$visitor[11]', '$visitor[12]', '$visitor[13]', '$visitor[14]', '', '$visitor[15]', '$visitor[16]', '$visitor[17]', '$visitor[18]', '$visitor[19]', '$visitor[20]', '$visitor[21]', '$visitor[22]', '$visitor[23]', '$visitor[24]', '$indicator', '$insGameID', '$teamId', '$teamAgainstId', '$playerId');";

		   //echo "$event_summary_SQL $this->sep";
		   	if(!$this->debug2){
		   		$query = $this->db->query($event_summary_SQL);
			}
		}
	}
	function get_goalie_data($GameID){
		
		$game_id = $this->SeasonID.$this->is_playoff.$GameID;
		if(!$this->BilAdjust){
		
			preg_match_all('#<tr><td align="center" class="sectionheading" width="100%">GOALTENDER SUMMARY</td></tr><tr><td align="center" width="100%" class="tborder"><table border="0" cellpadding="0" cellspacing="0" width="100%">(.*?)</table></td></tr><tr><td width="100%" class="spacer">&nbsp;</td></tr><tr valign="top"><td align="center" width="100%" class="border"><table border="0" cellpadding="0" cellspacing="0" width="100%"><tr valign="top">#', $this->gameData, $goalieInfo, PREG_SET_ORDER); 
		}
		else{
			preg_match_all('#<tr><td align="center" class="sectionheading" width="100%">GARDIENS / GOALTENDER SUMMARY</td></tr><tr><td align="center" width="100%" class="tborder"><table border="0" cellpadding="0" cellspacing="0" width="100%">(.*?)</table></td></tr><tr><td width="100%" class="spacer">&nbsp;</td></tr><tr valign="top"><td align="center" width="100%" class="border"><table border="0" cellpadding="0" cellspacing="0" width="100%"><tr valign="top">#', $this->gameData, $goalieInfo, PREG_SET_ORDER); 

		}

		$goalieSummary = str_replace(' class="evenColor"',' goalieStatRow',$goalieInfo[0][1]);
		$goalieSummary = str_replace(' class="oddColor"',' goalieStatRow',$goalieSummary);

		preg_match_all('#<tr goalieStatRow>(.*?)</tr>#', $goalieSummary, $gameInfoSplit, PREG_SET_ORDER); 
		
		$i=0;
		foreach ($gameInfoSplit as $infoChunk){	
			
			$arrGameInfo=$this->strip_attributes($infoChunk[1],'td');
			preg_match_all('#<td>(.*?)</td>#', $arrGameInfo, $arrGoalie, PREG_SET_ORDER);
			$arrGIFinal='';
			foreach($arrGoalie as $gameinfoDataChunk){
			$arrGIFinal[] = str_replace('&nbsp;','',$gameinfoDataChunk[1]);
			}
			//get W/L indicator
			
			preg_match_all('#\((.*?)\)#', $arrGIFinal[2], $tempWL, PREG_SET_ORDER);

			//print_r($arrGIFinal[2]);
			if(count($tempWL)>0)
			$arrGIFinal[14] = $tempWL[0][1];
			else $arrGIFinal[14] = '';
			//first two are away, last 2 are home
				//goalie teamID
			if($i=='0'||$i=='1'){
				$arrGIFinal[12]=$this->awayTeamId;
				$arrGIFinal[13]=$this->hometeamId;
			}
			else{
				$arrGIFinal[12]=$this->hometeamId;
				$arrGIFinal[13]=$this->awayTeamId;
			}
			$name = explode('(',$arrGIFinal[2]);
			$arrGIFinal[2] = $name[0];
			$arrGIFinal[15] = $this->lookup_goalie_name($arrGIFinal[2],$arrGIFinal[12]);
			//split the shots/goals
			$periodOneGS = explode('-',$arrGIFinal[7]);
			$periodTwoGS = explode('-',$arrGIFinal[8]);
			$periodThreeGS = explode('-',$arrGIFinal[9]);
			if(empty($arrGIFinal[11])){
			$allPeriodsGS = explode('-',$arrGIFinal[10]);
			$periodOTGS[0] ='';$periodOTGS[1] ='';
			}
			else{
			$periodOTGS = explode('-',$arrGIFinal[10]);
			$allPeriodsGS = explode('-',$arrGIFinal[11]);

			}
			
			$arrGameInfoChuncks[]=$arrGIFinal;

			//fix for time fields
			$arrGIFinal[3] = $this->format_time($arrGIFinal[3]);
			$arrGIFinal[4] = $this->format_time($arrGIFinal[4]);
			$arrGIFinal[5] = $this->format_time($arrGIFinal[5]);
			$arrGIFinal[6] = $this->format_time($arrGIFinal[6]);

			//print_r($arrGIFinal);
			$goalie_SQL = "REPLACE INTO `new_goalies` (
			id, w_l_indicator, ev_toi, pp_toi, sh_toi, total_toi, p1_sa, p2_sa, p3_sa, ot_sa, p1_ga,
			p2_ga, p3_ga, ot_ga, total_sa, total_ga, game_id, goalie_id, goalie_team_id, against_team_id)
			VALUES ('$game_id$i',
			'$arrGIFinal[14]',
			'$arrGIFinal[3]',
			'$arrGIFinal[4]',
			'$arrGIFinal[5]',
			'$arrGIFinal[6]',
			'$periodOneGS[1]',
			'$periodTwoGS[1]', 
			'$periodThreeGS[1]',
			'$periodOTGS[1]',
			'$periodOneGS[0]',
			'$periodTwoGS[0]',
			'$periodThreeGS[0]',
			'$periodOTGS[0]',
			'$allPeriodsGS[1]',
			'$allPeriodsGS[0]',
			'$game_id',
			'$arrGIFinal[15]',
			'$arrGIFinal[12]',
			'$arrGIFinal[13]');";
			//print_r($arrGameInfoChuncks);
			echo "$goalie_SQL $this->sep";
			if(!$this->debug2){
				$query = $this->db->query($goalie_SQL);
			}
			$i++;
		}
	}
	function format_time($time){
		if(!empty($time)){
			$tempVar = explode(':',$time);
			//print_r($tempVar);
			if($tempVar[0]>=60){
				$hour = '01';
				$min = '0'.$tempVar[0]%60;
				$sec = $tempVar[1];
			}
			else {	
				$hour ='00';
				$min = $tempVar[0];
				$sec = $tempVar[1];
			}
		return $hour.':'.$min.':'.$sec;
		}
	}
	function get_game_data($GameID,$file=0){
		//Passed a GameID as a parameter, pull in the game data into the game table	
		//If french
		if($this->BilAdjust ==1){echo 'french';}
		//set period bool to false
		$gameType = $this->gameType;
		$marker = $this->preGS.$this->is_playoff;
		$this->gameData = $this->_scrape($GameID,$marker,$file);
		$pos = strpos($this->gameData, '<h1>Not Found</h1>');
		if ($pos !== false) {
		   return false;
		}
		$game_id = $this->SeasonID.$this->is_playoff.$GameID;
		//Find date
		preg_match_all('#<table id="GameInfo" border="0" cellpadding="0" cellspacing="0" align="center">(.*?)</table>#', $this->gameData, $gameInfo, PREG_SET_ORDER); 
		preg_match_all('#<tr>(.*?)</tr>#', $gameInfo[0][1], $gameInfoSplit, PREG_SET_ORDER); 
		foreach ($gameInfoSplit as $infoChunk){	
			$arrGameInfo=$this->strip_attributes($infoChunk[1],'td');
			preg_match_all('#<td>(.*?)</td>#', $arrGameInfo, $arrPlayer, PREG_SET_ORDER);
			$arrGIFinal='';
			foreach($arrPlayer as $gameinfoDataChunk){
			$arrGIFinal[] = str_replace('&nbsp;','',$gameinfoDataChunk[1]);
			}
			$arrGameInfoChuncks[]=$arrGIFinal;
		}
		//print_r($arrGameInfoChuncks);
		$gameDate = strtotime($arrGameInfoChuncks[3][0]);
		$gameDate = date  (  'Y-m-d'  ,$gameDate );
		$currentGameStatus = $arrGameInfoChuncks[7][0];
		//get VISITOR info
		preg_match_all('#<table id="Visitor" border="0" cellpadding="0" cellspacing="0" align="center">(.*?)</table>#', $this->gameData, $gameVisInfo, PREG_SET_ORDER); 
		preg_match_all('#<tr>(.*?)</tr>#', $gameVisInfo[0][1], $gameVisInfoSplit, PREG_SET_ORDER); 
		foreach ($gameVisInfoSplit as $visInfoChunk){	
			$arrVisGameInfo=$this->strip_attributes($visInfoChunk[1],'td');
			preg_match_all('#<td>(.*?)</td>#', $arrVisGameInfo, $arrVisGameInfoChunked, PREG_SET_ORDER);
			$arrVGIFinal='';
			foreach($arrVisGameInfoChunked as $visGameInfoDataChunk){
			$arrVGIFinal[] = str_replace('&nbsp;','',$visGameInfoDataChunk[1]);
			}
			$arrVisGameInfoChuncks[]=$arrVGIFinal;
		}
		preg_match_all('#alt="(.*?)"#', $arrVisGameInfoChuncks[1][0], $awayTeam2, PREG_SET_ORDER); 
		$awayTeam = $awayTeam2[0][1];
		$awayTeamId = $this->check_team_id($awayTeam);
		$awayScore = $arrVisGameInfoChuncks[1][1];
		$this->awayTeamId = $this->check_team_id($awayTeam);
		$this->awayScore = $arrVisGameInfoChuncks[1][1];
		//get HOME info
		preg_match_all('#<table id="Home" border="0" cellpadding="" cellspacing="0" align="center">(.*?)</table>#', $this->gameData, $gameHomeInfo, PREG_SET_ORDER); 
		preg_match_all('#<tr>(.*?)</tr>#', $gameHomeInfo[0][1], $gameHomeInfoSplit, PREG_SET_ORDER); 
		foreach ($gameHomeInfoSplit as $visInfoChunk){	
			$arrHomeGameInfo=$this->strip_attributes($visInfoChunk[1],'td');
			preg_match_all('#<td>(.*?)</td>#', $arrHomeGameInfo, $arrHomeGameInfoChunked, PREG_SET_ORDER);
			$arrVGIFinal='';
			foreach($arrHomeGameInfoChunked as $visGameInfoDataChunk){
			$arrVGIFinal[] = str_replace('&nbsp;','',$visGameInfoDataChunk[1]);
			}
			$arrHomeGameInfoChuncks[]=$arrVGIFinal;
		}
		preg_match_all('#alt="(.*?)"#', $arrHomeGameInfoChuncks[1][2], $HomeTeam2, PREG_SET_ORDER); 
		$HomeTeam = $HomeTeam2[0][1];
		$hometeamId = $this->check_team_id($HomeTeam);
		$HomeScore = $arrHomeGameInfoChuncks[1][1];		
		$this->hometeamId = $this->check_team_id($HomeTeam);
		$this->HomeScore = $arrHomeGameInfoChuncks[1][1];		
		//Find last period by checking scoring summary

		preg_match_all('#SCORING SUMMARY</td></tr></table></td></tr><tr><td class="border"><table border="0" cellpadding="0" cellspacing="0" width="100%">(.*?)</table></td></tr><tr><td width="100%" class="spacer#', $this->gameData, $scoringSummary, PREG_SET_ORDER); 

		$scoringSummary2 = str_replace(' class="evenColor"','',$scoringSummary[0][1]);
		$scoringSummary2 = str_replace(' class="oddColor"','',$scoringSummary2);
		preg_match_all('#<tr>(.*?)</tr>#', $scoringSummary2, $scoringSummarySplit, PREG_SET_ORDER); 
		foreach ($scoringSummarySplit as $scoringSummaryChunk){
			$arrscoringSummaryInfo=$this->strip_attributes(strip_tags($scoringSummaryChunk[1], '<td>'),'td');
			preg_match_all('#<td>(.*?)</td>#', $arrscoringSummaryInfo, $arrscoringSummaryChunked, PREG_SET_ORDER);
			$arrSSFinal='';
			foreach($arrscoringSummaryChunked as $arrscoringSummaryChunkedData){
			$arrSSFinal[] = str_replace('&nbsp;','',$arrscoringSummaryChunkedData[1]);
			}
			$arrSSChuncks[]=$arrSSFinal;
		}
		//print_r($arrSSChuncks);
		$total_goals = count($arrSSChuncks)-1;
		//for pre SO games
		if(is_int($arrSSChuncks[$total_goals][1])&$arrSSChuncks[$total_goals][1]>3) $number_of_periods = $arrSSChuncks[$total_goals][1];
		

		switch ($arrSSChuncks[$total_goals][1]) {
			case "OT":
				$number_of_periods =  "OT";
				break;
			case "SO":
				$number_of_periods =  "SO";		
				break;
			case 4:
				$number_of_periods =  4;		
				break;
			case 5:
				$number_of_periods =  5;		
				break;
			case 6:
				$number_of_periods =  6;		
				break;
			default:
				$number_of_periods =  "3";
		}
		//check winner
		$winner='';
		if (($currentGameStatus=='Final') &&( $HomeScore > $awayScore ) ){
			$winner = $hometeamId;
		}
		elseif(($currentGameStatus=='Final') &&( $awayScore > $HomeScore ) ){
			$winner = $awayTeamId;
		}
		$game_summary_SQL = "REPLACE INTO `new_game` (`id` ,`home_score` ,`away_score` ,`game_date` ,`number_of_periods` ,`home_team_id` ,`away_team_id`,`gametype`,`isFinal`,`winner`)VALUES ('$game_id', '$HomeScore', '$awayScore', '$gameDate', '$number_of_periods', '$hometeamId', '$awayTeamId','$this->is_playoff','$currentGameStatus','$winner');";

		//echo "$game_summary_SQL $this->sep";
		if(!$this->debug2){
		$query = $this->db->query($game_summary_SQL);
		}
		return true;
	}
	function lookup_player_name($name,$teamId){

		$goalScorerId = explode(' ',$name);
		$goalScorerId2 = explode('.',$name);

		if(count($goalScorerId)=='3' && count($goalScorerId2) < '3'){
		$goalScorerId = explode('.',$name);
		$goalScorerId2 = explode('(',$goalScorerId[1]);	
		//print_r($goalScorerId);
		$goalScorerId3 = explode(' ',$goalScorerId[0]);	
		$goalScorerId3[0] = $goalScorerId3[1];
		//	$goalScorerId3[1] = $goalScorerId[1] . '.' . $goalScorerId2[0];
		$goalScorerId3[1] =  $goalScorerId2[0];
		}
		elseif(count($goalScorerId)=='3' && count($goalScorerId2) == '3'){
		$goalScorerId = explode('.',$name);
		//print_r($goalScorerId);
		$goalScorerId2 = explode('(',$goalScorerId[2]);	
		$goalScorerId3 = explode(' ',$goalScorerId[0]);	
		$goalScorerId3[0] = trim($goalScorerId3[1]);
		//remove '.' here
		$goalScorerId3[1] = trim($goalScorerId[1]) . ' ' . trim($goalScorerId2[0]);
		}
		else{
		$goalScorerId2 = explode('(',$goalScorerId[1]);
		$goalScorerId3 = explode('.',$goalScorerId2[0]);
		}
		//		if (get_magic_quotes_gpc()) $first = stripslashes($goalScorerId3[0]);
		$first = $goalScorerId3[0];
		//		if (get_magic_quotes_gpc()) $last = stripslashes($goalScorerId3[1]);
		$last = $goalScorerId3[1];		
		$sql='SELECT * FROM new_player WHERE player_f_name LIKE "'.$first.'%" AND player_l_name LIKE "'.$last.'" AND team_id="'.$teamId.'"';
		//echo $sql."$this->sep";
		$query = $this->db->query($sql);
		if(count($query->result())>=1)
		{	
			foreach ($query->result() as $row)
			{
				$playerData['id'] = $row->id;
				$playerData['game_summary_mapping'] = $row->game_summary_mapping;
				$playerData['position'] = $row->position;
				$playerData['team_id']= $row->team_id;
			}
			//echo $playerData['id'];
			return $playerData['id'];
		}
		else return false;
	}
	function lookup_goalie_name($name,$teamId){

		$goalScorerId = explode(',',$name);
		//print_r($goalScorerId);
		$first = substr(trim($goalScorerId[1]),0,1);
		$last = trim($goalScorerId[0]);		
		$sql='SELECT * FROM new_player WHERE player_f_name LIKE "'.$first.'%" AND player_l_name LIKE "'.$last.'" AND team_id="'.$teamId.'"';
			//echo $sql;
		$query = $this->db->query($sql);
		if(count($query->result())>=1)
		{	
			foreach ($query->result() as $row)
			{
				$playerData['id'] = $row->id;
				$playerData['game_summary_mapping'] = $row->game_summary_mapping;
				$playerData['position'] = $row->position;
				$playerData['team_id']= $row->team_id;
			}
			return $playerData['id'];
		}
		else return false;
	}
	function lookup_player_nameNEW($name,$teamId){

		$goalScorerId = explode(' ',$name);
		$goalScorerId2 = explode('.',$name);

		if(count($goalScorerId)=='3' && count($goalScorerId2) < '3'){
		$goalScorerId = explode('.',$name);
		$goalScorerId2 = explode('(',$goalScorerId[1]);	
		//
		$goalScorerId3 = explode(' ',$goalScorerId[0]);	
		$goalScorerId3[0] = $goalScorerId3[1];
		//	$goalScorerId3[1] = $goalScorerId[1] . '.' . $goalScorerId2[0];
		$goalScorerId3[1] =  $goalScorerId2[0];
		}
		elseif(count($goalScorerId)=='3' && count($goalScorerId2) == '3'){
		$goalScorerId = explode('.',$name);
		//print_r($goalScorerId);
		$goalScorerId2 = explode('(',$goalScorerId[2]);	
		$goalScorerId3 = explode(' ',$goalScorerId[0]);	
		$goalScorerId3[0] = trim($goalScorerId3[1]);
		//remove '.' here
		$goalScorerId3[1] = trim($goalScorerId[1]) . ' ' . trim($goalScorerId2[0]);
		}
		else{
			//print_r($goalScorerId2);
		//$goalScorerId2 = explode('(',$goalScorerId[1]);
		$goalScorerId3 = $goalScorerId2;
		
		}
		//		if (get_magic_quotes_gpc()) $first = stripslashes($goalScorerId3[0]);
		$first = $goalScorerId3[0];
		//		if (get_magic_quotes_gpc()) $last = stripslashes($goalScorerId3[1]);
		$last = $goalScorerId3[1];		
		$sql='SELECT * FROM new_player WHERE player_f_name LIKE "'.$first.'%" AND player_l_name LIKE "'.$last.'" AND team_id="'.$teamId.'"';
			//echo $sql;
		
		$query = $this->db->query($sql);
		if(count($query->result())>=1)
		{	//echo $sql;
			foreach ($query->result() as $row)
			{
				$playerData['id'] = $row->id;
				$playerData['game_summary_mapping'] = $row->game_summary_mapping;
				$playerData['position'] = $row->position;
				$playerData['team_id']= $row->team_id;
			}
			return $playerData['id'];
		}
		else return false;
	}
	function get_goals_assist_data($GameID){
		//Passed a GameID as a parameter, pull in the goal data into the goal table and assist data into assist table
		$BilAdjust = $this->BilAdjust;
		$HomeGoalCount = 0;
		$AwayGoalCount = 0;
		$arrGAInfoChuncks = '';
		$GWGIndicator = false;
		preg_match_all('#Ice</td></tr>(.*?)</table>#', $this->gameData, $goalAssistInfo, PREG_SET_ORDER); 
		$goalAssistInfo2 = str_replace(' class="evenColor"','',$goalAssistInfo[0][1]);
		$goalAssistInfo2 = str_replace(' class="oddColor"','',$goalAssistInfo2);
		preg_match_all('#<tr>(.*?)</tr>#', $goalAssistInfo2, $goalAssistInfoSplit, PREG_SET_ORDER); 
		$i=0;

		foreach ($goalAssistInfoSplit as $goalAssistInfoChunk){
			if ($BilAdjust ==1){
				$goalAssistInfoChunk = str_replace('<td align="left" colspan="2">Penalty Shot</td>','<td align="left">Penalty Shot</td><td align="left" />',$goalAssistInfoChunk);
				$goalAssistInfoChunk = str_replace('<td align="left" />','<td align="left"></td>',$goalAssistInfoChunk);
				$goalAssistInfoChunk = str_replace('<td align="center" />','<td align="center"></td>',$goalAssistInfoChunk);

				//echo '1';
			}
			else{
				$goalAssistInfoChunk = str_replace('<td align="left" colspan="2">Penalty Shot</td>','<td align="left">Penalty Shot</td><td align="left" />',$goalAssistInfoChunk);
				$goalAssistInfoChunk = str_replace('<td align="left" /><td>','<td align="left"></td><td>',$goalAssistInfoChunk);
				$goalAssistInfoChunk = str_replace('<td align="center" />','<td align="center"></td>',$goalAssistInfoChunk);
			}
		
			$arrGameInfo=$this->strip_attributes(strip_tags($goalAssistInfoChunk[1], '<td>'),'td');
		
			preg_match_all('#<td>(.*?)</td>#', $arrGameInfo, $arrGoalAssist, PREG_SET_ORDER);
			$arrGAFinal='';
			foreach($arrGoalAssist as $arrGoalAssistChunk){
				$arrGAFinal[] = str_replace('&nbsp;','',$arrGoalAssistChunk[1]);
			}

			$arrGAInfoChuncks[$i]=$arrGAFinal;
		$i++;
		}
		
			//print_r($arrGAInfoChuncks);echo "$this->sep";
		//Done parsing SCORING SUMMARY, now loop through and append the remaing values b4 insert
		$this->homeTeamID = $this->check_team_id($this->homeTeam);
		$this->awayTeamID = $this->check_team_id($this->awayTeam);
		//print_r($this->hometeamId);print_r($this->HomeScore);
		//print_r($this->awayTeamId);print_r($this->awayScore);

		if(is_array($arrGAInfoChuncks)){
			foreach ($arrGAInfoChuncks as $gameGoal){
				
				if($gameGoal[0] !='-' and $gameGoal[1] !='SO'){
					//make sure it was a goal
					//set scoring team
					$gwgIndicator = '0';
					$gameGoal[10] = $this->check_team_abbr($gameGoal[4]);
					//set team against id
					if ($gameGoal[10] == $this->homeTeamID) {
						$teamAgainstId = $this->awayTeamID;
						$onIceAgainst = explode(",", $gameGoal[8]);
						$homeGoalCount++;
						if($homeGoalCount==($this->awayScore+1)){
							$gwgIndicator='1';
						}
						else{
							$gwgIndicator='0';
						}
					}
					else {
						$teamAgainstId = $this->homeTeamID;
						$onIceAgainst = explode(",", $gameGoal[9]);
						$awayGoalCount++;
						if($awayGoalCount==($this->HomeScore+1)){
							$gwgIndicator='1';
						}
						else{
							$gwgIndicator='0';
						}
					}
					$gameGoal[11]  = $teamAgainstId;

					//Get the Goal Scorer's ID
					$gameGoal[12] = $this->lookup_player_name($gameGoal[5],$gameGoal[10]);
					$arrGameGoal[] = $gameGoal;
					$gameIdSQL = $this->SeasonID.$this->is_playoff.$GameID;
					if($gameGoal[0]<10){$gameGoal[0]='0'.$gameGoal[0];}
					$goalId = $gameIdSQL.$gameGoal[0];
					//Get the Goalie ID
					//Loop through Player jersey #'s and find the goalie

					$goalieAgainstID = $this->checkGoalieAgainst($onIceAgainst,$teamAgainstId);

					//print_r($onIceAgainst);
					//print_r($teamAgainstId);
					$goalAssist_SQL = "INSERT INTO `new_goal` (`id` ,`game_goal_number` ,`period` ,`time` ,`goal_strength` ,`game_id` ,`team_against_id` ,`player_id` ,`scoring_team_id`,`gwg`,`goalie`)VALUES ('$goalId', '$gameGoal[0]', '$gameGoal[1]', '$gameGoal[2]', '$gameGoal[3]', '$gameIdSQL', '$teamAgainstId', '$gameGoal[12]', '$gameGoal[10]','$gwgIndicator','$goalieAgainstID');";
					//print_r($gameGoal);
				//	echo "$goalAssist_SQL $this->sep";
					if(!$this->debug2){
					$result = $this->db->query($goalAssist_SQL);
					}
					
					//now for assist data
					//Check if unassisted or Penalty shot
					//if not, continue
					if($gameGoal[6]=='unassisted' || $gameGoal[6]=='Unsuccessful Penalty Shot' || $gameGoal[6]=='Penalty Shot'  || $gameGoal[6]=='' ){
					}
					else{
						//1st assist
						$assist_number = '1';
						$assistId = $goalId.$assist_number ;
						$ass_player_id = $this->lookup_player_name($gameGoal[6],$gameGoal[10]);
						$assist_SQL = "INSERT INTO `new_assist` (`id` ,`assist_number` ,`goal` ,`player_id`)VALUES ('$assistId', '$assist_number', '$goalId', '$ass_player_id');";
						//echo "$assist_SQL $this->sep";
						if(!$this->debug2){
							$result = $this->db->query($assist_SQL);
						}
						//2nd assist
						if(!empty($gameGoal[7])){
						$assist_number = '2';
							$assistId = $goalId.$assist_number ;
							$ass_player_id2 = $this->lookup_player_name($gameGoal[7],$gameGoal[10]);
							$assist_SQL2 = "INSERT INTO `new_assist` (`id` ,`assist_number` ,`goal` ,`player_id`)VALUES ('$assistId', '$assist_number ', '$goalId', '$ass_player_id2');";;
							//echo "$assist_SQL2 $this->sep";
							if(!$this->debug2){
								$result = $this->db->query($assist_SQL2);						
							}
						}
					}
				}
				elseif($gameGoal[1] =='SO'){
					if(!empty($gameGoal[3])){
						$f =0;
					$gameGoal[10] = $this->check_team_abbr($gameGoal[3]);
					}
					else{$f =1;
					$gameGoal[10] = $this->check_team_abbr($gameGoal[4]);
					}
					//set team against id
					if ($gameGoal[10] == $this->homeTeamID) $teamAgainstId = $this->awayTeamID;
					else $teamAgainstId = $this->homeTeamID;
					$gameGoal[11]  = $teamAgainstId;
					//Get the Goal Scorer's ID
					if($f)
					$gameGoal[12] = $this->lookup_player_name($gameGoal[5],$gameGoal[10]);
					else
					$gameGoal[12] = $this->lookup_player_name($gameGoal[4],$gameGoal[10]);
					$arrGameGoal[] = $gameGoal;
					$gameIdSQL = $this->SeasonID.$this->is_playoff.$GameID;
					if($gameGoal[0]<10){$gameGoal[0]='0'.$gameGoal[0];}
					$goalId = $gameIdSQL.$gameGoal[0];

					$goalAssist_SQL = "INSERT INTO `new_goal` (`id` ,`game_goal_number` ,`period` ,`time` ,`goal_strength` ,`game_id` ,`team_against_id` ,`player_id` ,`scoring_team_id`)VALUES ('$goalId', '$gameGoal[0]', '$gameGoal[1]', '', '', '$gameIdSQL', '$teamAgainstId', '$gameGoal[12]', '$gameGoal[10]');";
					//print_r($gameGoal);
					//echo "$goalAssist_SQL $this->sep";
					if(!$this->debug2){
						$result = $this->db->query($goalAssist_SQL);						
					}
				}
				//print_r($gameGoal);
			}
		}
	}
	function checkGoalieAgainst($onIceAgainst,$teamAgainstId){
		/**
		Find the goalie who was on when the goal was scored
		*/
		$goalie='';
		//print_r($onIceAgainst);
		//print_r($this->arrGameData['goalies']);
		
		//echo "$teamAgainstId";
		foreach($onIceAgainst as $playerAgainst){
			$playerIDs[] =  trim($playerAgainst);
		}
		foreach($this->arrGameData['goalies'][$teamAgainstId] as $goalieAgainst){
			$jerseyNum[] =  $goalieAgainst[0];
		}
		$goalieNumber = array_intersect($jerseyNum,$playerIDs);
		//print_r( $goalieNumber);
		$thegoalieNum = (!empty($goalieNumber[0]))?$goalieNumber[0]:$goalieNumber[1];
		foreach($this->arrGameData['goalies'][$teamAgainstId] as $goalieAgainst){
			
			if($thegoalieNum==$goalieAgainst[0]){
				
			$goalie = $this->lookup_goalie_name( $goalieAgainst[2],$teamAgainstId) ;
			//print_r($goalie);
			}
		}
		return $goalie;
	}
	function get_penalty_data($GameID){
		$homeFlag = 0;
		//narrow down source
		preg_match_all('#<table id="PenaltySummary" border="0" cellpadding="0" cellspacing="0" width="100%">(.*?)</table></td></tr></table></td></tr></table></td></tr><tr><td width="100%" class="spacer"#', $this->gameData, $penalityInfo, PREG_SET_ORDER); 
		
		if($this->SeasonID == '20102011'){
			//echo $this->SeasonID;
		preg_match_all('#<td width="50%" valign="top" align="center" class="lborder \+ rborder \+ tborder">(.*?)</td></tr></table></td><td>&nbsp;</td><td>&nbsp;</td>#', $penalityInfo[0][1], $penInfo, PREG_SET_ORDER); 
		}

		else{
		preg_match_all('#<table border="0" cellpadding="0" cellspacing="0" width="100%">(.*?)<td class="bold">TOT</td>#', $penalityInfo[0][1], $penInfo, PREG_SET_ORDER); 
		}
		//print_r($penInfo);
		//$penInfo2 = $penInfo[0];

		foreach ($penInfo as $awayHome){
			//print_r($awayHome);
			$arrPENInfoChuncks = '';
			$awayHome = str_replace('class="evenColor"','playerRow',$awayHome[0]);
			
			$awayHome = str_replace('class="oddColor"','playerRow',$awayHome);
			//need to remove table for name

			
			$awayHome = str_replace('<table cellpadding="0" cellspacing="0" border="0"><tr><td width="15" align="right">','',$awayHome);
			
			$awayHome = str_replace('<td>&nbsp;</td>',' ',$awayHome);
			//print_r($awayHome);
			$awayHome = str_replace('</td></tr></table>','',$awayHome);
			$awayHome = $awayHome . '</tr>';
			
			//print_r($awayHome);
			//split tds into array
			preg_match_all('#<tr playerRow>(.*?)</tr>#', $awayHome, $playerRow, PREG_SET_ORDER); 
			$i=0;
				foreach ($playerRow as $chunk){
					$arrPenInfo=$this->strip_attributes(strip_tags($chunk[1], '<td>'),'td');
					preg_match_all('#<td>(.*?)</td>#', $arrPenInfo, $arrPen, PREG_SET_ORDER);
					$arrPenFinal='';			
					foreach($arrPen as $arrPenInfoChunk){
						$arrPenFinal[] = str_replace('&nbsp;','',$arrPenInfoChunk[1]);

					}
					$arrPENInfoChuncks[$i]=$arrPenFinal;
				$i++;
				}
				
			
			//now we have a list of all the penalties, need to insert them
			if(is_array($arrPENInfoChuncks)){
				foreach ($arrPENInfoChuncks as $gamePenalty){
					//print_r($gamePenalty);
					//echo count($gamePenalty);
					if (count($gamePenalty)=='7'){
						
						//print_r($gamePenalty);
						$id = $this->SeasonID.$this->is_playoff.$GameID.$homeFlag.$gamePenalty[0];
						if($gamePenalty[0]<10){
						$game_id =  $this->SeasonID.$this->is_playoff.$GameID;
						$id = $game_id.$homeFlag.'0'.$gamePenalty[0];}
						$period = $gamePenalty[1];
						$time = $gamePenalty[2];
						$pim = $gamePenalty[5];
						
						$penalty_id = $this->get_penalty_id_from_name($gamePenalty[6]);
						if($homeFlag=='0') {
							$team_for_id = $this->awayTeamID;
							$team_against_id = $this->homeTeamID;
							if($this->SeasonID == '20102011'){
							$player_id = $this->lookup_player_nameNEW($gamePenalty[4],$this->awayTeamID);
							}else
							$player_id = $this->lookup_player_nameNEW($gamePenalty[4],$this->awayTeamID);

							//echo $player_id;
							}
						else {
							$team_for_id = $this->homeTeamID;
							$team_against_id = $this->awayTeamID;
							if($this->SeasonID == '20102011'){
							$player_id = $this->lookup_player_nameNEW($gamePenalty[4],$this->homeTeamID);
							}else
							$player_id = $this->lookup_player_nameNEW($gamePenalty[4],$this->homeTeamID);
							//echo $player_id;
						}
						$sql = "INSERT INTO `new_penalty` (`id` ,`period` ,`time` ,`pim` ,`penalty_id` ,`player_id` ,`game_id` ,`team_for_id` ,`team_against_id`)VALUES ('$id', '$period', '$time', '$pim', '$penalty_id', '$player_id', '$game_id', '$team_for_id', '$team_against_id');";
						//echo "$sql $this->sep";
						if(!$this->debug2){
							$result = $this->db->query($sql);						
						}
					}
				}
			}
			$homeFlag = 1;
		}
	}
	function get_penalty_id_from_name($penaltyName){
		//this sub will get the penalty ID from a penalty name
		if (get_magic_quotes_gpc()) $penaltyName = stripslashes($penaltyName);
		$penaltyName = mysql_escape_string($penaltyName);		
		$penaltyName = str_replace(' - ','-',$penaltyName);
		$sql="SELECT * FROM new_penalty_mapping WHERE  penalty_name = '$penaltyName'";
		//echo $sql;

		$query = $this->db->query($sql);
		if(count($query->result())>=1)
		{	//echo $sql;
			foreach ($query->result() as $row)
			{
				$penaltyData['name'] = $row->penalty_name;
				$penaltyData['penalty_id'] = $row->penalty_id;
			}
			return $penaltyData['penalty_id'];
		}

		else if ($num_rows=='0'){
			$sql="INSERT into new_penalty_type  (penalty_name ,id)VALUES ('$penaltyName', '');";
			//echo $sql."$this->sep";
			$result = $this->db->query($sql);
			if($result){
				$newId = $this->db->insert_id();
				$sql2="INSERT into new_penalty_mapping  (penalty_name,penalty_id,penalty_type_id)VALUES ('$penaltyName','$newId','$newId');";
				//echo $sql2."$this->sep";
				$result2 = $this->db->query($sql2);
			}
		}
	}
	function getNHLSchedual($gameType){
		$db_user="27_ssf432";
		$db_pass="l12321l";
		$database =	"27_ssf";
		$mysql_id =	mysql_connect('localhost', $db_user, $db_pass) or die("Cannot connect to mySQL");
		mysql_select_db($database,	$mysql_id) or die("Can't find database:	".$this->database);
		$i = '0';
		$z = $pred_start;
		//$url='http://www.nhl.com/nhl/app?service=page&page=SubseasonSchedule';
		if($gameType ==2)
		$url='http://www.nhl.com/schedules/20102011.html';
		else
		$url = 'http://www.nhl.com/ice/app?formids=PropertySelection%2CPropertySelection_0%2CPropertySelection_1%2CPropertySelection_2%2CPropertySelection_3&component=%24SimpleForm&page=schedulebyseason&service=direct&submitmode=&submitname=&PropertySelection=0&PropertySelection_0=2&PropertySelection_1=0&PropertySelection_2=0&PropertySelection_3=0';
		//$url='http://www.nhl.com/ice/schedulebymonth.htm';
		
		$feed =  $this->_scrape_sched($url);
		$feed = str_replace("bignetwork",'network',$feed);
		$feed = str_replace("Premiere",'',$feed);
		$feed = str_replace("Face Off",'',$feed);
		$feed = str_replace("dateevent",'date',$feed);

		//print_r($feed);
		preg_match_all('#<div class="skedDataRow" style="background-color: \#fff;">(.*?)</div></a></div></div>#', $feed, $schedual, PREG_SET_ORDER); 
		$i=0;
		foreach ($schedual as $gameday){
			//get date
			preg_match_all('#<div class="skedDataRow date">(.*?)</div>#', $gameday[1], $datetime, PREG_SET_ORDER);
			//get Teams
			preg_match_all('#\.nhl\.com">(.*?)</a>#', $gameday[1], $teams, PREG_SET_ORDER);

			preg_match_all('#<div id="skedStartTimeEST" style="display:block;">(.*?)</div>#', $gameday[1], $startTime, PREG_SET_ORDER);
			preg_match_all('#<div class="skedDataRow network">(.*?)</div>#', $gameday[1], $network, PREG_SET_ORDER);
			preg_match_all('#<a href="/ice/preview.htm\?id=201003(.*?)">#', $gameday[1], $gameId, PREG_SET_ORDER);
			preg_match_all('#&quot;sked-3-(.*?)-v&quot#', $gameday[1], $gameIdNew, PREG_SET_ORDER);
			//print_r($gameId);
			$dateSub = explode('&nbsp;',$datetime[0][1]);

			$arrOut[$i]['date']=date('Y-m-d',strtotime($dateSub[1]));
			$arrOut[$i]['startTime']= $startTime[0][1];
			$temp= explode(' ',$startTime[0][1]);
			$arrOut[$i]['time2']= $temp[0];

			$arrOut[$i]['network']= $network[0][1];
			//$arrOut[$i]['gameIdNew']= $gameIdNew[0][1];
			$arrOut[$i]['gameId']= $gameIdNew[0][1];
			$arrOut[$i]['homeTeam']= $teams[0][1];
						$num= $this->leading_zeros($arrOut[$i]['gameId'],4);
			$arrOut[$i]['full_id']= '20100'.$gameType.$num;
				$awayTeam = str_replace("<br>",'',$teams[1][1]);
			$arrOut[$i++]['awayTeam']= $awayTeam;

			
		}
		$nl	= count($arrOut);
		if($nl == 0){
				echo "nothing found";
		}
		elseif ($nl	> 0){
			foreach($arrOut as $gameDay){
				$date		=	$gameDay['date'];
				$tv_nat		=	$gameDay['network'];
				$time		=	$gameDay['startTime'];
				$gameID		=   $gameDay['gameId'];
				$homeTeam		=	$gameDay['homeTeam'];
				$awayTeam		=   $gameDay['awayTeam'];
				$time2		=   $gameDay['time2'];
				$full_id		=   $gameDay['full_id'];

				
				$currentSeason = $this->SeasonID;
				//$gameIDNew		=   $gameDay['gameIdNew'];
				$sql="REPLACE INTO	nhl_schedual (id, date, time, gametype,tv_nat,home_team, away_team,season,full_id,time2) VALUES ('$full_id', '$date', '$time', '$gameType','$tv_nat','$homeTeam','$awayTeam','$currentSeason','$gameID','$time2')";
				print_r($sql);
				$z++;
				if(!$this->debug2){
						 echo "$this->sep";
					mysql_db_query($database, $sql) or die("A MySQL error has occurred.<br />Your Query:	" .	$sql . "<br/> Error: (" . mysql_errno() . ") "	. mysql_error());  //do	the	query
				}
				
			}
			echo "$this->sep Finished updating Schedual $this->sep";
		}
	}
	function getNHLSchedualPlayed($gameType){
		$db_user="27_ssf432";
		$db_pass="l12321l";
		$database =	"27_ssf";
		$mysql_id =	mysql_connect('localhost', $db_user, $db_pass) or die("Cannot connect to mySQL");
		mysql_select_db($database,	$mysql_id) or die("Can't find database:	".$this->database);
		$i = '0';
		$z = $pred_start;
		//$url='http://www.nhl.com/nhl/app?service=page&page=SubseasonSchedule';
			$url='http://www.nhl.com/schedules/20102011.html';

		//$url='http://www.nhl.com/ice/schedulebymonth.htm';
		
		$feed =  $this->_scrape_sched($url);
		$feed = str_replace("bignetwork",'network',$feed);
		$feed = str_replace("Premiere",'',$feed);
		$feed = str_replace("Face Off",'',$feed);
		$feed = str_replace("dateevent",'date',$feed);

		preg_match_all('#<div class="skedDataRow" style="background-color: \#E4E4E4;">(.*?)</div></a></div></div>#', $feed, $schedual, PREG_SET_ORDER); 
		$i=0;
		foreach ($schedual as $gameday){

			preg_match_all('#<div class="skedDataRow date">(.*?)</div>#', $gameday[1], $datetime, PREG_SET_ORDER);
			//print_r( $datetime );
			preg_match_all('#\.nhl\.com">(.*?)</a>#', $gameday[1], $teams, PREG_SET_ORDER);

			preg_match_all('#<div id="skedStartTimeEST" style="display:block;">(.*?)</div>#', $gameday[1], $startTime, PREG_SET_ORDER);
			preg_match_all('#<div class="skedDataRow network">(.*?)</div>#', $gameday[1], $network, PREG_SET_ORDER);
			preg_match_all('#<a href="/ice/preview.htm\?id=201002(.*?)">#', $gameday[1], $gameId, PREG_SET_ORDER);
			preg_match_all('#&quot;sked-2-(.*?)-v&quot#', $gameday[1], $gameIdNew, PREG_SET_ORDER);
		
			$dateSub = explode('&nbsp;',strip_tags($datetime[0][1]));

			$arrOut[$i]['date']=date('Y-m-d',strtotime($dateSub[1]));
			$arrOut[$i]['startTime']= $startTime[0][1];
			$temp= explode(' ',$startTime[0][1]);
			$arrOut[$i]['time2']= $temp[0];
			//$arrOut[$i]['network']= $network[0][1];
		//	$arrOut[$i]['gameId']= $gameId[0][1];
			$arrOut[$i]['network']= $network[0][1];
			$arrOut[$i]['gameId']= $gameIdNew[0][1];
			$arrOut[$i]['homeTeam']= $teams[0][1];
						$num= $this->leading_zeros($arrOut[$i]['gameId'],4);
			$arrOut[$i]['full_id']= $this->SeasonID.$gameType.$num;

			$awayTeam = str_replace("<br>",'',$teams[1][1]);
			$awayTeam = explode('(',$awayTeam);
			$awayTeam = $awayTeam[0];
			$arrOut[$i++]['awayTeam']= $awayTeam;

		}
		$nl	= count($arrOut);
		if($nl == 0){
				echo "nothing found";
		}
		elseif ($nl	> 0){
			foreach($arrOut as $gameDay){
				$date		=	$gameDay['date'];
				$tv_nat		=	$gameDay['network'];
				$time		=	$gameDay['startTime'];
				$gameID		=   $gameDay['gameId'];
				$homeTeam		=	$gameDay['homeTeam'];
				$awayTeam		=   $gameDay['awayTeam'];
				$time2		=   $gameDay['time2'];
				$full_id		=   $gameDay['full_id'];
				$currentSeason = $this->SeasonID;
				//$gameIDNew		=   $gameDay['gameIdNew'];
				$gameType = '2';
				$sql="REPLACE INTO	nhl_schedual (id, date, time, gametype,tv_nat,home_team, away_team,season,full_id,time2) VALUES ('$full_id', '$date', '$time', '$gameType','$tv_nat','$homeTeam','$awayTeam','$currentSeason','$gameID','$time2')";
				print_r($sql); echo "$this->sep";
				$z++;
				if(!$this->debug2){
					mysql_db_query($database, $sql) or die("A MySQL error has occurred.<br />Your Query:	" .	$sql . "<br/> Error: (" . mysql_errno() . ") "	. mysql_error());  //do	the	query
				}
			}
		}
	}
	function getNHLPlayers2($debug=1){
		$alphabet = array ("A","B","C","D","E","F","G","H","I","J","K","L","M","N","O","P","Q","R","S","T","U","V","W","X","Y","Z");
		//$alphabet = array ("S");
			for ( $k = 0; $k <= 25; $k ++) {
			$url="http://www.nhl.com/ice/playersearch.htm?letter=$alphabet[$k]";
			//print_r($url);
			$feed =	implode('',	file($url));
			$feed=str_replace("\n",'',$feed );
			$feed=str_replace("\r",'',$feed );
			$feed=str_replace("\t",'',$feed );
			$feed=str_replace("<br />",'<br>',$feed );
			$feed = preg_replace( "{[ \t]+}", ' ', $feed );
			//check number of pages
			preg_match_all('/of (.*?) results/s', $feed, $results, PREG_SET_ORDER);
			$numPages = ceil($results[0][1] / 30);
			//print_r($numPages);
			for ( $j = 1; $j <= $numPages; $j ++) {
				$url="http://www.nhl.com/ice/playersearch.htm?letter=$alphabet[$k]&pg=$j";
				echo $url;
				$feed =	implode('',	file($url));
				$feed=str_replace("\n",'',$feed );
				$feed=str_replace("\r",'',$feed );
				$feed=str_replace("\t",'',$feed );
				$feed=str_replace("<br />",'<br>',$feed );
				$feed = preg_replace( "{[ \t]+}", ' ', $feed );
				$splitFeed = (split('<!-- player search -->', $feed)); //Get only 2nd half of html source
				$splitF2 = explode('</table>',$splitFeed[1]);
				//print_r($splitF2[0]);
			//	$splitFeed=(split('<tr>',$splitF2[0])); 
				$splitFeed2=explode('<tr>', $splitF2[0]); //split on new row
				foreach($splitFeed2 as $player){
					//$players[] = explode('<td>',$player);
					$player = str_replace('</td>', '|</td>', $player );
					$players[] = strip_tags($player,'<a>');
				}
				$i=0;
				//print_r($players);

				foreach($players as $player){
						$playerStep2[$i] = explode('|',$player);
						preg_match_all('/id=(.*?)\"/s', $playerStep2[$i][0], $playerId, PREG_SET_ORDER);
						preg_match_all('/\((.*?)\)/s', $playerStep2[$i][0], $pos, PREG_SET_ORDER);
						$playerStep2[$i][0] = strip_tags($playerStep2[$i][0]);
						$playerStep2[$i][0] = substr($playerStep2[$i][0], 0, -4); 
						$playerStep2[$i][6]=$pos[0][1];
						$playerStep2[$i][5]=$playerId[0][1];
						$i++;
						}
				//print_r($playerStep2);
				foreach($playerStep2 as $player){
					if(!empty($player[5])){
					$nhlId	=$player[5];
					$namePre=split(', ',$player[0]);
					$fname = str_replace(".", "", $namePre[1]);
					$lname = str_replace(".", "", $namePre[0]);
					$lname	=mysql_escape_string(trim($lname));
					$fname	=mysql_escape_string(trim($fname));
					$actFullname = $fname . ' ' . $lname;
					$team	=mysql_escape_string(trim($player[1]));
					$dob		=	date("Y-m-d", strtotime($player[2]));

					$city	=mysql_escape_string($player[3]);
					$pos		=$player[6];
					$sql="REPLACE INTO	nhl_players (id, fname,lname,team, dob , homecity, pos,fullname) VALUES ('$nhlId', '$fname', '$lname', '$team', '$dob', '$city','$pos','$actFullname')$this->sep";
					print_r($sql);
					if(!$this->debug2){
						$query = $this->db->query($sql);
					}
						unset($playerStep2);unset($players);
					}
				}
			}
		}
	}
	function getPlayerMugs(){

		$sql2="SELECT * FROM nhl_players where hasMug='0' limit 0,1";
		$query2 = $this->db->query($sql2);
		if(count($query2->result())>=1)
		{	//echo $sql;
			foreach ($query2->result() as $row)
			{
				$id = $row->id;
				$TEAM = $row->team;
				$ch = curl_init ("http://www.nhl.com/photos/mugs/$id.jpg");
				curl_setopt($ch, CURLOPT_HEADER, 0);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
				curl_setopt($ch, CURLOPT_BINARYTRANSFER,1);
				$rawdata=curl_exec ($ch);
				curl_close ($ch);

				$fp = fopen("$id.jpg",'w');
				fwrite($fp, $rawdata);
				fclose($fp);
			}
		$query2->free_result();
		}
	}
	function getBoxScoreData($GameID,$gameType,$gameDate){
		//Passed a GameID as a parameter, pull in the game data into the game table	
		//If french
		if($this->BilAdjust ==1){echo 'french';}
		//set period bool to false
		$this->gameType = $gameType;
		$marker = $this->is_playoff;
		$this->gameData = $this->_scrape($GameID,$gameType,$file, 1,$gameDate);
		
		$pos = strpos($this->gameData, '<h1>Not Found</h1>');
		if ($pos !== false) {
		   return false;
		}
		$game_id = $this->SeasonID.'2'.$GameID;

		//Get home and away teams and final score. 
		preg_match_all('#<div id="gcGmSbWrap">(.*?)</span></span></div></div>#', $this->gameData, $gameInfo, PREG_SET_ORDER); 
		preg_match_all('#<div class="team away">(.*?)</div></div>#', $this->gameData, $awayTeam, PREG_SET_ORDER);
		$awayTeam = strip_tags($this->strip_attributes($awayTeam[0][1],'div'),'<br/>');
		$awayTeam = str_replace('<br/>',' ',$awayTeam);
		preg_match_all('#<div class="team home">(.*?)</div></div>#', $this->gameData, $homeTeam, PREG_SET_ORDER);
		$homeTeam = strip_tags($this->strip_attributes($homeTeam[0][1],'div'),'<br/>');
		$homeTeam = str_replace('<br/>',' ',$homeTeam);
		//status
		preg_match_all('#<div class="status">(.*?)</span>#', $this->gameData, $gameStatus, PREG_SET_ORDER);
		$gameStatus = strip_tags($gameStatus[0][1]);
		//period
		preg_match_all('#<span class="prd">(.*?)</span>#', $this->gameData, $gamePeriod, PREG_SET_ORDER);
		$gamePeriod = strip_tags($gamePeriod[0][1]);
		preg_match_all('#<div class="awayScrCell">(.*?)</div>#', $this->gameData, $awayScore, PREG_SET_ORDER);
		$awayScore = strip_tags($awayScore[0][1]);
		preg_match_all('#<div class="homeScrCell">(.*?)</div>#', $this->gameData, $homeScore, PREG_SET_ORDER);
		$homeScore = strip_tags($homeScore[0][1]);

		//print_r($gameInfo);
		$this->awayTeamId = $this->check_team_id($awayTeam);
		$this->awayScore = $awayScore;

		$this->hometeamId = $this->check_team_id($homeTeam);
		$this->HomeScore = $homeScore;		
		print("Hometeam:$homeTeam($this->hometeamId) $this->sep");
		print("Awayteam:$awayTeam($this->awayTeamId) $this->sep");
		print("Status:$gameStatus $this->sep");
		print("Period:$gamePeriod $this->sep");
		print("Date:$gameDate $this->sep");
		print("HomeScore:$homeScore $this->sep");
		print("AwayScore:$awayScore $this->sep");
		switch ($gamePeriod) {
			case "OT":
				$number_of_periods =  "OT";
				break;
			case "SO":
				$number_of_periods =  "SO";		
				break;
			case "OT2":
				$number_of_periods =  4;		
				break;
			case "OT3":
				$number_of_periods =  5;		
				break;
			case "OT4":
				$number_of_periods =  6;		
				break;
			default:
				$number_of_periods =  3;
		}
		//check winner
		$winner='';
		if ((strtoupper($gameStatus)=='FINAL') &&( $this->HomeScore > $this->awayScore ) ){
			$this->winner = $this->hometeamId;
			$this->loser = $this->awayTeamId;
		}
		elseif((strtoupper($gameStatus)=='FINAL') &&( $this->awayScore > $this->HomeScore ) ){
			$this->winner = $this->awayTeamId;
			$this->loser = $this->hometeamId;
		}
		$game_summary_SQL = "REPLACE INTO `new_game` (`id` ,`home_score` ,`away_score` ,`game_date` ,`number_of_periods` ,`home_team_id` ,`away_team_id`,`gametype`,`isFinal`,`winner`)VALUES ('$game_id', '$homeScore', '$awayScore', '$gameDate', '$number_of_periods', '$this->hometeamId', '$this->awayTeamId','2','$gameStatus','$this->winner');";

		
		if(!$this->debug2){
			echo "$game_summary_SQL $this->sep";
			$query = $this->db->query($game_summary_SQL);
		}
		return true;
	}
	function getBS_event_summary_data($GameID,$debug,$file=0){
		echo "$this->sep Inserting game Summary data $this->sep";
		$currentPlayer = array();
		$gameType = $this->gameType;
		$data = $this->gameData;
		$insGameID = $this->SeasonID. $this->is_playoff . $GameID;
		echo "$this->sep gameID: $insGameID $this->sep";
		$this->hometeamId;
		$i = 0;
		
		$pos = strpos($data, '<h1>Not Found</h1>');
		if ($pos !== false) {
		   return false;
		}
		//Step 1 Get Home and visitor teams 
		//	print_r($data);//	die();
		preg_match_all('#<table class="data multiBody noHead boxscore skaters">(.*?)</table>#', $data, $playerSummary, PREG_SET_ORDER); 
		

		//Split into home and away
		preg_match_all('#<tbody>(.*?)</tbody>#', $playerSummary[0][1], $hwES_Data, PREG_SET_ORDER); 
		//Away first
		echo "$this->sep Away ES DATA $this->sep";
		$teamId = $this->awayTeamId;
		$teamAgainstId = $this->hometeamId;
		//Split into players
		preg_match_all('#<tr>(.*?)</tr>#', $hwES_Data[0][1], $awayPlayerES, PREG_SET_ORDER); 
		array_shift($awayPlayerES);
		
		foreach($awayPlayerES as $ESPlayer){
			$ESPlayer=$this->strip_attributes($ESPlayer[1],'td');
			$teamId = $this->awayTeamId;
			preg_match_all('#<td>(.*?)</td>#', $ESPlayer, $awayPlayer, PREG_SET_ORDER);
			foreach($awayPlayer as $playerStat){
				$currentPlayer[$i][] = $playerStat[1];
			}
			$i++;
		}

		//format and insert visitor data
		foreach ($currentPlayer as &$preInsAway){
			
			preg_match_all('#id=(.*?)">#', $preInsAway[1], $playerID, PREG_SET_ORDER);
			$NHLID = $playerID[0][1];
			$nameFull = strip_tags($preInsAway[1]);
			$name = explode('.',$nameFull);
			$PlayerFullName =trim($nameFull);
			$PlayerFName =trim($name[0]);
			$PlayerLName =trim($name[1]);
			//echo "$PlayerFName, $PlayerLName,$teamId,$NHLID $this->sep";
			$ssf_playerId = $this->checkNHLID($teamId,$NHLID);
			if(!$ssf_playerId){
				$ssf_playerId = $this->add_new_player($PlayerFName, $PlayerLName, $teamId,	$preInsAway[2],$nameFull,$preInsAway[0]);
				echo "$this->sep New Player: $PlayerFName, $PlayerLName, teamID: $teamId, nhlID: NHLID $this->sep";
				//insert new player
			}
			$preInsAway[1] = $ssf_playerId;

			$primaryKey = $insGameID . $ssf_playerId;
			//echo "PrimaryKey: $primaryKey";
			$event_summary_SQL = "INSERT INTO `new_event_summary` (`id` ,`goals` ,`assists` ,`points` ,`plus_minus` ,`total_toi` ,`pp_toi` ,`sh_toi` ,`es_toi`,`sog`,`home_away_indicator` ,`game_id` ,`team_id` ,`team_against_id` ,`player_id`) VALUES ( '$primaryKey', '$preInsAway[3]', '$preInsAway[4]', '$preInsAway[5]', '$preInsAway[6]', '$preInsAway[12]', '$preInsAway[10]', '$preInsAway[11]', '$preInsAway[9]', '$preInsAway[8]', 'A', '$insGameID', '$teamId', '$teamAgainstId', '$ssf_playerId');";

			echo "$event_summary_SQL $this->sep";
			if(!$this->debug2){
				$query = $this->db->query($event_summary_SQL);
			}
		}

		//Home
		$currentPlayerHm = array();
		echo "$this->sep Home ES DATA $this->sep";
		$teamId = $this->hometeamId;
		$teamAgainstId = $this->awayTeamId;
		//Split into players
		preg_match_all('#<tr>(.*?)</tr>#', $hwES_Data[1][1], $homePlayerES, PREG_SET_ORDER); 
		array_shift($homePlayerES);
		
		foreach($homePlayerES as $ESPlayer){
			$ESPlayer=$this->strip_attributes($ESPlayer[1],'td');
			preg_match_all('#<td>(.*?)</td>#', $ESPlayer, $homePlayer, PREG_SET_ORDER);
			foreach($homePlayer as $playerStat){
				$currentPlayerHm[$i][] = $playerStat[1];
			}
			$i++;
		}
		//print_r($currentPlayerHm);
		//format and insert visitor data
		foreach ($currentPlayerHm as &$preInsAway){
			
			preg_match_all('#id=(.*?)">#', $preInsAway[1], $playerID, PREG_SET_ORDER);
			$NHLID = $playerID[0][1];
			$nameFull = strip_tags($preInsAway[1]);
			$name = explode('.',$nameFull);
			$PlayerFullName =trim($nameFull);
			$PlayerFName =trim($name[0]);
			$PlayerLName =trim($name[1]);
			//echo "$PlayerFName, $PlayerLName,$teamId,$NHLID $this->sep";
			$ssf_playerId = $this->checkNHLID($teamId,$NHLID);
			if(!$ssf_playerId){
				$ssf_playerId = $this->add_new_player($PlayerFName, $PlayerLName, $teamId,	$preInsAway[2],$nameFull,$preInsAway[0]);
				echo "$this->sep New Player: $PlayerFName, $PlayerLName, teamID: $teamId, nhlID: NHLID $this->sep";
				//insert new player
			}
			$preInsAway[1] = $ssf_playerId;

			$primaryKey = $insGameID . $ssf_playerId;
			//echo "PrimaryKey: $primaryKey";
			$event_summary_SQL = "INSERT INTO `new_event_summary` (`id` ,`goals` ,`assists` ,`points` ,`plus_minus` ,`total_toi` ,`pp_toi` ,`sh_toi` ,`es_toi`,`sog`,`home_away_indicator` ,`game_id` ,`team_id` ,`team_against_id` ,`player_id`) VALUES ( '$primaryKey', '$preInsAway[3]', '$preInsAway[4]', '$preInsAway[5]', '$preInsAway[6]', '$preInsAway[12]', '$preInsAway[10]', '$preInsAway[11]', '$preInsAway[9]', '$preInsAway[8]', 'H', '$insGameID', '$teamId', '$teamAgainstId', '$ssf_playerId');";

			echo "$event_summary_SQL $this->sep";
			if(!$this->debug2){
				$query = $this->db->query($event_summary_SQL);
			}
		}
		
	}
	function getBS_goals_assist_data($GameID){
		//Passed a GameID as a parameter, pull in the goal data into the goal table and assist data into assist table
		echo "$this->sep Inserting Goals and Assists $this->sep";
		$BilAdjust = $this->BilAdjust;
		$HomeGoalCount = 0;
		$AwayGoalCount = 0;
		$arrGAInfoChuncks = '';
		$currentGoal =  array();
		$GWGIndicator = false;
		$i = 0;
		//select the 'SCORING SUMMARY' table
		preg_match_all('#<table class="data multiBody noHead boxscore scoring smallIcons">(.*?)</table>#', $this->gameData, $goalAssistInfo, PREG_SET_ORDER); 
		//split into periods. 
		preg_match_all('#<tbody>(.*?)</tbody>#', $goalAssistInfo[0][1], $goalPeriods, PREG_SET_ORDER); 
		$awayTeam = $this->awayTeamId;
		$homeTeam = $this->hometeamId;
		//Split into goals 
		foreach($goalPeriods as $period){
			$tmpGoals = array();
			preg_match_all('#<tr>(.*?)</tr>#', $period[1], $goalAssist, PREG_SET_ORDER); 
			$period = array_shift($goalAssist);
			$period = strip_tags($period[1]);
			if($period !='OT SO'){
			//$tmp = $this->strip_attributes($goalAssist[0][1],'a');
			foreach($goalAssist as $tmpgoalData){

				$tmpgoalData[1] .= "<td>$period</td>";
				$tmp = $this->strip_attributes($tmpgoalData[1],'div');
				$tmpGoals[] = $this->strip_attributes($tmp,'td');

			}
			$goalsPer[] = $tmpGoals;
			}
			
		}
		//print_r($goalsPer);
		//format goals
		foreach($goalsPer as $goalPer){
			foreach($goalPer as $goal){
				preg_match_all('#<td>(.*?)</td>#', $goal, $goalChunks, PREG_SET_ORDER);
				
				if(count($goalChunks) != 2){
					foreach($goalChunks as $goalChunk){
						$currentGoal[$i][] = $goalChunk[1];
					}
					$i++;
				}
			}
		}
		//format goals2
		$winningCount = 0;
		if($this->loser ==$this->awayTeamId){
			$losingTeamScore = $this->awayScore;
		}
		else $losingTeamScore = $this->HomeScore;
		//print_r($currentGoal);
		foreach($currentGoal as &$goal){
			
			$tmpAssist = array();
			//teamID
			$goal[2] = $this->get_team_id_from_abbr($goal[2]);
			//GWG?
			$goal[9] ='0';
			switch($goal[4]){
				case '1st Period':
					$goal[4] = 1;
					break;
				case '2nd Period':
					$goal[4] = 2;
					break;
				case '3rd Period':
					$goal[4] = 3;
					break;
				case 'OT Period':
					$goal[4] = 'OT';
					break;
				case 'OT SO':
					$goal[4] = 'SO';
					break;
			}
			
			if($this->winner == $goal[2]){
				//if this is the winning team, and this is the losingteam+1th goal, its a gwg
				$winningCount++;
				$tmp21 = intval($losingTeamScore+1);
				if(intval($winningCount)==$tmp21){
					$goal[9] ='1';
				}
			}

			$tmp = explode(" -", $goal[3]);
			if(count($tmp)==3){
				preg_match_all('#id=(.*?)">#', $tmp[1], $playerNHLID, PREG_SET_ORDER);
				$NHLID = $playerNHLID[0][1];
				echo "$goal[2],$NHLID";
				$playerName = strip_tags($tmp[1]);
				$goal[5] = $this->checkNHLID($goal[2],$NHLID);
				$goal[6] = $tmp[2];
				switch($tmp[0]){
					case 'PPG':
						$goal[7] = 'PP';
						break;
					case 'EN':
						$goal[7] = 'EV-EN';
						break;
					case 'SHG':
						$goal[7] = 'SH';
						break;
					case 'PS':
						$goal[7] = 'PS';
						break;
					default:
						$goal[7] = $tmp[0];
						break;
				}
				$goal[8] = $playerName;
			}
			else{
				//$pos = strpos($tmp[1], 'ASST:NONE');
				$pos = strpos($tmp[0], 'scoringPlayerLinkData');
				if ($pos === false) {
					preg_match_all('#id=(.*?)">#', $tmp[1], $playerNHLID, PREG_SET_ORDER);
				}
				else {
					preg_match_all('#id=(.*?)">#', $tmp[0], $playerNHLID, PREG_SET_ORDER);
				}
				$NHLID = $playerNHLID[0][1];
				$playerName = strip_tags($tmp[0]);
				$goal[5] = $this->checkNHLID($goal[2],$NHLID);
				$goal[6] = $tmp[1];
				$goal[7] = 'EV';
				$goal[8] = $playerName;
			}
			//format assists
			preg_match_all('#id=(.*?)">#', $goal[6], $assists, PREG_SET_ORDER);
			foreach($assists as $assist){
				$tmpAssist[]=$this->checkNHLID($goal[2],$assist[1]);
			}
			$goal[10] = $tmpAssist;
			unset($goal[1]);
			unset($goal[3]);
			unset($goal[6]);
		}
		//if the count is 3, that means it was either PPG SHG EN 
		
		$j=0;
		if(is_array($currentGoal)){
			
			foreach ($currentGoal as $fngoal){
				
				$gameIdSQL = $this->SeasonID.$this->is_playoff.$GameID;
				$goalNum = ++$j;
				$goalId = $gameIdSQL.$goalNum;
				//Get the Goalie ID NOT POSSIBLE IN BOXSCORE
				if($this->awayTeamId ==$fngoal[2]){
					$teamAgainstId = $this->hometeamId;
				}
				else $teamAgainstId = $this->awayTeamId;
				$goalAssist_SQL = "INSERT INTO `new_goal` (`id` ,`game_goal_number` ,`period` ,`time` ,`goal_strength` ,`game_id` ,`team_against_id` ,`player_id` ,`scoring_team_id`,`gwg`,`goalie`)VALUES ('$goalId', '$goalNum', '$fngoal[4]', '$fngoal[0]', '$fngoal[7]', '$gameIdSQL', '$teamAgainstId', '$fngoal[5]', '$fngoal[2]','$fngoal[9]',0);";
				//print_r($fngoal);
				echo "$goalAssist_SQL $this->sep";
				if(!$this->debug2){
				$result = $this->db->query($goalAssist_SQL);
				}
				
				//now for assist data
				//Check if unassisted or Penalty shot
				//if not, continue
				if(count($fngoal[10])>=1){
					$assist_number =1;
					foreach ($fngoal[10] as $fnAssist){
						//echo $fnAssist;
						$assistId = $goalId.$assist_number;
						$assist_SQL = "INSERT INTO `new_assist` (`id` ,`assist_number` ,`goal` ,`player_id`)VALUES ('$assistId', '$assist_number', '$goalId', '$fnAssist');";

						echo "$assist_SQL $this->sep";
						if(!$this->debug2){
							$result = $this->db->query($assist_SQL);
						}
						$assist_number++;
					}
				}
			}
		}
	}
	function getBS_penalty_data($GameID){
		echo "$this->sep Inserting Penalties $this->sep";

		//narrow down source
		preg_match_all('#<table class="data multiBody noHead boxscore penalties">(.*?)</table>#', $this->gameData, $penalityInfo, PREG_SET_ORDER); 
		
		//split into periods. 
		preg_match_all('#<tbody>(.*?)</tbody>#', $penalityInfo[0][1], $penPeriods, PREG_SET_ORDER); 
		$awayTeam = $this->awayTeamId;
		$homeTeam = $this->hometeamId;
		//Split into goals 
		
		foreach($penPeriods as $period){
			$tmpPenalty = array();
			preg_match_all('#<tr>(.*?)</tr>#', $period[1], $penRow, PREG_SET_ORDER); 
			$period = array_shift($penRow);
			$period = strip_tags($period[1]);
			//$tmp = $this->strip_attributes($goalAssist[0][1],'a');
			
			foreach($penRow as $tmpPenData){
				
				$tmpPenData[1] .= "<td>$period</td>";
				//$tmp = str_replace('Â','',$this->strip_attributes($tmpPenData[1],'div'));
				$tmp = $this->replaceSpecial($this->strip_attributes($tmpPenData[1],'div'));
				$tmpPenalty[] = $this->strip_attributes($tmp,'td');

			}
			
			$pensPer[] = $tmpPenalty;
		}
			
		$i = 0;
		foreach($pensPer as $penPer){
			foreach($penPer as $penalty){
				preg_match_all('#<td>(.*?)</td>#', $penalty, $penChunks, PREG_SET_ORDER);
				if(count($penChunks) != 2){
					foreach($penChunks as $penChunk){
						$currentPen[$i][] = $penChunk[1];
					}
					$i++;
				}
			}
		}
		$a=1;
		$h=1;
		//print_r($currentPen);
		foreach($currentPen as &$penalty){
			
			//teamID
			$penalty[4] = $this->get_team_id_from_abbr($penalty[1]);
			switch($penalty[3]){
				case '1st Period':
					$penalty[5] = 1;
					break;
				case '2nd Period':
					$penalty[5] = 2;
					break;
				case '3rd Period':
					$penalty[5] = 3;
					break;
				case 'OT Period':
					$penalty[5] = 'OT';
					break;
				default:
					$penalty[5] = 'OT';
					break;
			}
			preg_match_all('#id=(.*?)">#', $penalty[2], $playerNHLID, PREG_SET_ORDER);
			$NHLID = $playerNHLID[0][1];
			$tmp = explode(" -", $penalty[2]);
			$penalty[6] = $this->checkNHLID($penalty[4],$NHLID);
			
			$penalty[2] = strip_tags($penalty[2]);
			//echo $penalty[2];
			$tmp = array_pop($tmp);
			preg_match_all('#-(.*?)min#', $tmp, $tmpMin, PREG_SET_ORDER);
			//print_r( array_pop($tmp));
			$tmpMin = explode("-", $tmpMin[0][1]);
			if(count($tmpMin)==1){
				$pim = $tmpMin[0];
			}
			else $pim = $tmpMin[1];
			//print_r($tmpMin);
			//$pim = str_replace("min",'',$tmpMin[1]);
			$tmp = explode(":", $penalty[2]);
			$penType =trim(substr($tmp[1], 0, -5));
			$penalty_id = $this->get_penalty_id_from_name($penType);
			$penalty[2] = $penalty_id;
			$game_id =  $this->SeasonID.$this->is_playoff.$GameID;
			if($this->awayTeamId ==$penalty[4]){
				$homeFlag = 0;
				$x = $a;
				$a++;
				$team_for_id=$this->awayTeamId;
				$team_against_id=$this->hometeamId;
			}
			else {
				$homeFlag = 1;
				$x = $h;
				$h++;
				$team_for_id=$this->hometeamId;
				$team_against_id=$this->awayTeamId;
			}
			$id = $game_id.$homeFlag.'0'.$x;
			$sql = "INSERT INTO `new_penalty` (`id` ,`period` ,`time` ,`pim` ,`penalty_id` ,`player_id` ,`game_id` ,`team_for_id` ,`team_against_id`)VALUES ('$id', '$penalty[5]', '$penalty[0]', '$pim', '$penalty[2]', '$penalty[6]', '$game_id', '$team_for_id', '$team_against_id');";
			echo "$sql $this->sep";
			if(!$this->debug2){
				$result = $this->db->query($sql);						
			}
			$x++;
		}
		//print_r($currentPen);
	}
	function replaceSpecial($str){
		$chunked = str_split($str,1);
		$str = ""; 
		foreach($chunked as $chunk){
			$num = ord($chunk);
			// Remove non-ascii & non html characters
			if ($num >= 32 && $num <= 123){
					$str.=$chunk;
			}
		}   
		return $str;
	} 
}
?>
