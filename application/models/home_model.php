<?php
error_reporting(E_PARSE); 

class Home_Model extends CI_Model
{
    function __construct()
    {
        // Call the Model constructor
        parent::__construct();
    }
	function getFeatured(){
		$dateStr =  date  ( 'Y-m-d', strtotime("-30 hours") );
		$dateStr2 =  date  ( 'Y-m-d', strtotime("-6 hours") );
		$season = '20102011';
		//$gametype= '2';
		//$dateStr = '2011-4-10';

		$sql="SELECT id, game_date,away_team_id, home_team_id, home_score, away_score  FROM new_game WHERE game_date <= '$dateStr' ORDER BY game_date DESC limit 0,1";
		//echo $sql;
		$query = $this->db->query($sql);
		
		if ($query->num_rows() > 0)
		{
			foreach ($query->result() as $row)
			{
				$gameID  = $row->id;
				$htmlOut = $gameID;
			}
		}
		else $htmlOut = 0;
		return $htmlOut;
	}
	function getGameInfo($gameID){
		//Not played yet

		$season = '20102011';
		$gametype= '2';
					$gameID = substr($gameID,-4);

		$sql="SELECT id as theID, date,away_team, home_team,time  FROM nhl_schedual WHERE id = '$gameID'";
		//echo $sql;
		$query = $this->db->query($sql);
		if ($query->num_rows() > 0)
		{
			foreach ($query->result() as $row)
			{

				$date  = $row->date;
				$home_score  = 0;
				$away_score  = 0;
				$away_team  = $this->get_abbr($row->away_team,2);
				$home_team  = $this->get_abbr($row->home_team,2);
				$gameStatus = $row->time;
				$homeAbbr= $home_team['abbr'];
				$homeImgStr = strtolower($home_team['team_name']);
				$homeImgStr2 = str_replace(' ', '', $homeImgStr);
				$awayAbbr= $away_team['abbr'];
				$awayImgStr = strtolower($away_team['team_name']);
				$awayImgStr2 = str_replace(' ', '', $awayImgStr);

				$time = $row->time;
			}

		}
		//Setup Output

		$htmlOut = "<div id='scTop'>
		<div id='scHomeTeam' class='scTopRow'>
			<div class='scTeam'>$homeAbbr</div>
			<div class='scLogo'><img src='/assets/images/logo/$homeImgStr2.png'></div>
		</div>
		<div id='scHomeScores' class='scTopRow'>
			<table>
			<tr>
				<td class='score'>0</td>
			</tr>
			<tr>
				<td class='score'>0</td>			
			</tr>
			<tr>
				<td class='score'>0</td>			
			</tr>
			</table>
		</div>
		<div id='scScore' class='scTopRow'><div>0-0</div><div class='gameStatus'>$gameStatus</div></div>
		<div id='scAwayScores' class='scTopRow'>
			<table>
			<tr>
				<td class='score'>0</td>
			</tr>
			<tr>
				<td class='score'>0</td>			
			</tr>
			<tr>
				<td class='score'>0</td>			
			</tr>
			</table>		
		</div>
		<div id='scAwayTeam' class='scTopRow'>
			<div class='scTeam'>$awayAbbr</div>
			<div class='scLogo'><img src='/assets/images/logo/$awayImgStr2.png'></div>
		</div>
		<div class='clear'></div>
		</div>";
		return $htmlOut;
	}
	function getGameInfoPlayed($gameID){
		// played 

		$season = '20102011';
		$gametype= '2';
		$hm_period1count=0;
		$hm_period2count=0;
		$hm_period3count=0;
		$hm_period4count=0;
		$hm_periodSOcount=0;
		$aw_period1count=0;
		$aw_period2count=0;
		$aw_period3count=0;
		$aw_period4count=0;
		$aw_periodSOcount=0;

		$sql="SELECT id, game_date,away_team_id, home_team_id, home_score, away_score, isFinal,number_of_periods  FROM new_game WHERE id = '$gameID'";
		//echo $sql;
		$query = $this->db->query($sql);
		if ($query->num_rows() > 0)
		{
			foreach ($query->result() as $row)
			{

				$gameID  = $row->id;
				$date  = $row->date;
				$home_score  = $row->home_score;
				$away_score  = $row->away_score;
				$away_team_id = $row->away_team_id;
				$home_team_id = $row->home_team_id;
				$gameStatus = $row->isFinal;
				$html_extraPeriods='';
				$number_of_periods = $row->number_of_periods;
				if($number_of_periods=='SO'||$number_of_periods=='OT')
					$html_extraPeriods = $number_of_periods;
				elseif($number_of_periods>3)
					$html_extraPeriods = 'OT '.($number_of_periods-3);
				$away_team  = $this->get_abbr($row->away_team_id,3);
				$home_team  = $this->get_abbr($row->home_team_id,3);
				$homeAbbr= $home_team['abbr'];
				$homeImgStr = strtolower($home_team['team_name']);
				$homeImgStr2 = str_replace(' ', '', $homeImgStr);
				$awayAbbr= $away_team['abbr'];
				$awayImgStr = strtolower($away_team['team_name']);
				$awayImgStr2 = str_replace(' ', '', $awayImgStr);

				$time = $row->time;
			}


		//Now get all the goal and assist data

		$sql="SELECT new_goal.id,game_goal_number, period, time, goal_strength, team_against_id, scoring_team_id,player_id as scorer,full_name FROM new_goal, new_player WHERE new_goal.player_id = new_player.id AND game_id = '$gameID'";
		//echo $sql;
		$query = $this->db->query($sql);
		if ($query->num_rows() > 0)
		{
			
			foreach ($query->result() as $row)
			{
				$goal_id  = $row->id;
				$game_goal_number  = $row->game_goal_number;
				$period  = $row->period;
				$time  = $row->time;
				$goal_strength  = $row->goal_strength;
				$scorer  = $row->scorer;
			//	$assister  = $row->assister;
				$team_against_id  = $row->team_against_id;
				$scoring_team_id  = $row->scoring_team_id;
				$full_name  = trim($row->full_name);		
				$fnSplit = explode(' ',$full_name);
				$lastName ='';
				for($x=1;$x<=count($fnSplit);$x++){
					$lastName .= $fnSplit[$x].' ';
				}
				$fnReMake = substr($fnSplit[0],0,1).'.'.$lastName;
				$full_name = trim($fnReMake);
				//echo "$scoring_team_id==$home_team_id";
				if($scoring_team_id==$home_team_id){
					switch($period){
						case 1:
							$hm_period1count++;
							$homeData1[$game_goal_number]['scorer'] = $scorer;
							$homeAssists1 = $this->getGoalAssits($goal_id);
							$htmlHome1 .= "<div><div class='scScorer'>$full_name ($time)</div><div class='scAssists'>$homeAssists1</div></div>";
							break;
						case 2:
							$hm_period2count++;
							$homeData2[$game_goal_number]['scorer'] = $scorer;
							$homeAssists2 = $this->getGoalAssits($goal_id);
							$htmlHome2 .= "<div><div class='scScorer'>$full_name ($time)</div><div class='scAssists'>$homeAssists2</div></div>";
							break;
						case 3:
							$hm_period3count++;
							$homeData3[$game_goal_number]['scorer'] = $scorer;
							$homeAssists3 = $this->getGoalAssits($goal_id);
							$htmlHome3 .= "<div><div class='scScorer'>$full_name ($time)</div><div class='scAssists'>$homeAssists3</div></div>";

							break;
						case 'OT':
							$hm_period4count++;
							$homeData4[$game_goal_number]['scorer'] = $scorer;
							$homeAssists4 = $this->getGoalAssits($goal_id);
							$htmlHome4 .= "<div><div class='scScorer'>$full_name ($time)</div><div class='scAssists'>$homeAssists4</div></div>";

							break;
						case 'SO':
							$hm_periodSOcount++;
							$homeDataSO[$game_goal_number]['scorer'] = $scorer;
							$htmlHomeSO = "<div><div class='scScorer'>$full_name</div></div>";
							break;
						default:
							$hm_period4count++;
							$homeData4[$game_goal_number]['scorer'] = $scorer;
							$homeAssists4 = $this->getGoalAssits($goal_id);
							$htmlHome4 .= "<div><div class='scScorer'>$full_name ($time)</div><div class='scAssists'>$homeAssists4</div></div>";
					}
				}//End Home
				elseif($scoring_team_id==$away_team_id){
					switch($period){
						case 1:
							$aw_period1count++;
							$awayData1[$game_goal_number]['scorer'] = $scorer;
							$awayAssists1 = $this->getGoalAssits($goal_id);
							$htmlAway1 .= "<div><div class='scScorer'>($time) $full_name</div><div class='scAssists'>$awayAssists1</div></div>";
							break;
						case 2:
							$aw_period2count++;
							$awayData2[$game_goal_number]['scorer'] = $scorer;
							$awayAssists2 = $this->getGoalAssits($goal_id);
							$htmlAway2 .= "<div><div class='scScorer'>($time) $full_name</div><div class='scAssists'>$awayAssists2</div></div>";
							break;
						case 3:
							$aw_period3count++;
							$awayData3[$game_goal_number]['scorer'] = $scorer;
							$awayAssists3 = $this->getGoalAssits($goal_id);
							$htmlAway3 .= "<div><div class='scScorer'>($time) $full_name</div><div class='scAssists'>$awayAssists3</div></div>";
							break;
						case 'OT':
							$aw_period4count++;
							$awayData4[$game_goal_number]['scorer'] = $scorer;
							$awayAssists4 = $this->getGoalAssits($goal_id);
							$htmlAway4 .= "<div><div class='scScorer'>($time) $full_name</div><div class='scAssists'>$awayAssists4</div></div>";
							break;
						case 'SO':
							$aw_periodSOcount++;
							$awayDataSO[$game_goal_number]['scorer'] = $scorer;
							$htmlAwaySO .= "<div><div>$full_name</div></div>";
							break;
						default:
							$aw_period4count++;
							$awayData4[$game_goal_number]['scorer'] = $scorer;
							$awayAssists4 = $this->getGoalAssits($goal_id);
							$htmlAway4 .= "<div><div class='scScorer'>($time) $full_name</div><div class='scAssists'>$awayAssists4</div></div>";
					}
				}//End Home
				
			}
		}
		}
		else {
			$notPlayed = $this->getGameInfo($gameID);
			return $notPlayed;
		}
		if(empty($htmlHome1)){
			$htmlHome1 = '';
		}
		else{
			$htmlHome1 = "<div id='scHomePeriod1' class='h_per'>
				<span>Period 1</span>
				$htmlHome1
			</div>";
		}
		if(empty($htmlHome2)){
			$htmlHome2 = '';
		}
		else{
			$htmlHome2 = "<div id='scHomePeriod2' class='h_per'>
				<span>Period 2</span>
				$htmlHome2
			</div>";
		}
		if(empty($htmlHome3)){
			$htmlHome3 = '';
		}
		else{
			$htmlHome3 = "<div id='scHomePeriod3' class='h_per'>
				<span>Period 3</span>
				$htmlHome3
			</div>";
		}
		if(empty($htmlHome4)){
			$htmlHome4 = '';
		}
		else{
			$htmlHome4 = "<div id='scHomePeriod4' class='h_per'>
				<span>OT</span>
				$htmlHome4
			</div>";
		}
		if(empty($htmlHomeSO)){
			$htmlHomeSO = '';
		}
		else{
			$htmlHomeSO = "<div id='scHomePeriodSO' class='h_per'>
				<span>OT</span>
				$htmlHomeSO
			</div>";
		}
		if(empty($htmlAway1)){
			$htmlAway1 = '';
		}
		else{
			$htmlAway1 = "<div id='scAwayPeriod1' class='a_per'>
				<span>Period 1</span>
				$htmlAway1
			</div>";
		}
		if(empty($htmlAway2)){
			$htmlAway2 = '';
		}
		else{
			$htmlAway2 = "<div id='scAwayPeriod2' class='a_per'>
				<span>Period 2</span>
				$htmlAway2
			</div>";
		}
		if(empty($htmlAway3)){
			$htmlAway3 = '';
		}
		else{
			$htmlAway3 = "<div id='scAwayPeriod3' class='a_per'>
				<span>Period 3</span>
				$htmlAway3
			</div>";
		}
		if(empty($htmlAway4)){
			$htmlAway4 = '';
		}
		else{
			$htmlAway4 = "<div id='scAwayPeriod4' class='a_per'>
				<span>OT</span>
				$htmlAway4
			</div>";
		}
		if(empty($htmlAwaySO)){
			$htmlAwaySO = '';
		}
		else{
			$htmlAwaySO = "<div id='scAwayPeriodSO' class='a_per'>
				<span>OT</span>
				$htmlAwaySO
			</div>";
		}
		//print_r($homeData1);
		//Setup Output

		$htmlOut = "<div id='scTop'>
		<div id='scHomeTeam' class='scTopRow'>
			<div class='scTeam'>$homeAbbr</div>
			<div class='scLogo'><img src='/assets/images/logo/$homeImgStr2.png'></div>
		</div>
		<div id='scHomeScores' class='scTopRow'>
			<table>
			<tr>
				<td class='score'>$hm_period1count</td>
			</tr>
			<tr>
				<td class='score'>$hm_period2count</td>			
			</tr>
			<tr>
				<td class='score'>$hm_period3count</td>			
			</tr>
			</table>
		</div>
		<div id='scScore' class='scTopRow'><div>$home_score-$away_score</div><div class='gameStatus'>$gameStatus $html_extraPeriods</div></div>
		<div id='scAwayScores' class='scTopRow'>
			<table>
			<tr>
				<td class='score'>$aw_period1count</td>
			</tr>
			<tr>
				<td class='score'>$aw_period2count</td>			
			</tr>
			<tr>
				<td class='score'>$aw_period3count</td>			
			</tr>
			</table>		
		</div>
		<div id='scAwayTeam' class='scTopRow'>
			<div class='scTeam'>$awayAbbr</div>
			<div class='scLogo'><img src='/assets/images/logo/$awayImgStr2.png'></div>
		</div>
		<div class='clear'></div>
		</div>
		<div id='scPeriods'>
		<div id='scHomePeriods' class='ha_period'>
			$htmlHome1
			$htmlHome2
			$htmlHome3
			$htmlHome4
			$htmlHomeSO
		</div>
		<div id='scAwayPeriods' class='ha_period'>
			$htmlAway1
			$htmlAway2
			$htmlAway3
			$htmlAway4
			$htmlAwaySO
		</div>
		<div class='clear'></div>
		</div>
		<div id='scScroll'>
		<div id='scHomeScroll' class='ha_period'>
			<a href='#' id='hmScrollUp' class='ha_ScrollUp'><span>UP</span></a><a href='#'id='hmScrollDown' class='ha_ScrollDown'><span>DOWN</span></a> 
		</div>
		<div id='scAwayScroll' class='ha_period'>
			<a href='#' id='awScrollUp' class='ha_ScrollUp'><span>UP</span></a><a href='#' id='awScrollDown'  class='ha_ScrollDown'><span>DOWN</span></a> 		</div>
		<div class='clear'></div>
		</div>
		";
		return $htmlOut;
	}
	function buildGameArray($gameInfo){
		$season = '20102011';
		$gametype= '2';
		$arrOutput = array();
		$arrGoalAssistOutput = array();
		$gameId = $gameInfo['gameID'];
		$sql = "SELECT g.id, g.game_date,g.away_team_id, g.home_team_id, g.home_score, g.away_score, g.isFinal,g.number_of_periods,s.away_team, s.home_team, s.time,s.full_id,s.date
		FROM nhl_schedual s
		LEFT JOIN new_game g ON s.full_id = g.id
		WHERE s.full_id = '$gameId'";
		$query = $this->db->query($sql);
		if ($query->num_rows() > 0)
		{
			foreach ($query->result() as $row)
			{

				$arrOutput['gameID_played']  = $row->id;
				$arrOutput['gameID_sched']  = $row->full_id;
				$arrOutput['date']  = $row->date;
				$arrOutput['home_score']  = $row->home_score;
				$arrOutput['away_score']  = $row->away_score;
				$arrOutput['away_team_id'] = $row->away_team_id;
				$arrOutput['home_team_id'] = $row->home_team_id;
				$arrOutput['gameStatus'] = $row->isFinal;
				$arrOutput['html_extraPeriods'] = '';

				$number_of_periods = $row->number_of_periods;
				if($number_of_periods=='SO'||$number_of_periods=='OT')
					$arrOutput['html_extraPeriods'] = $number_of_periods;
				elseif($number_of_periods>3)
					$arrOutput['html_extraPeriods'] = 'OT '.($number_of_periods-3);
				//home and away are reversed on schedual
				$away_team  = $this->get_abbr($row->home_team,2);
				$home_team  = $this->get_abbr($row->away_team,2);
				$arrOutput['homeAbbr'] = $home_team['abbr'];
				$homeImgStr = strtolower($home_team['team_name']);
				$homeImgStr2 = str_replace(' ', '', $homeImgStr);
				$arrOutput['homeIMG'] = "$homeImgStr2.png";
				$arrOutput['awayAbbr'] = $away_team['abbr'];
				$awayImgStr = strtolower($away_team['team_name']);
				$awayImgStr2 = str_replace(' ', '', $awayImgStr);
				$arrOutput['awayIMG'] = "$awayImgStr2.png";
				$arrOutput['time'] = $row->time;
			}
		}

		//Now get all the goal and assist data

		$sql2="SELECT new_goal.id,game_goal_number, period, time, goal_strength, team_against_id, scoring_team_id,player_id as scorer,full_name FROM new_goal, new_player WHERE new_goal.player_id = new_player.id AND game_id = '$gameId'";
		//echo $sql;
		$query2 = $this->db->query($sql2);
		if ($query2->num_rows() > 0)
		{
			$i=0;
			foreach ($query2->result() as $row)
			{
				$arrGoalAssistOutput[$i]['goal_id']  = $row->id;
				$arrGoalAssistOutput[$i]['game_goal_number']  = $row->game_goal_number;
				$arrGoalAssistOutput[$i]['period']  = $row->period;
				$arrGoalAssistOutput[$i]['time']  = $row->time;
				$arrGoalAssistOutput[$i]['goal_strength']  = $row->goal_strength;
				$arrGoalAssistOutput[$i]['scorer']  = $row->scorer;
			//	$assister  = $row->assister;
				$arrGoalAssistOutput[$i]['team_against_id']  = $row->team_against_id;
				$arrGoalAssistOutput[$i]['scoring_team_id']  = $row->scoring_team_id;
				$full_name  = trim($row->full_name);		
				$fnSplit = explode(' ',$full_name);
				$lastName ='';
				for($x=1;$x<=count($fnSplit);$x++){
					$lastName .= $fnSplit[$x].' ';
				}
				$fnReMake = substr($fnSplit[0],0,1).'.'.$lastName;
				$arrGoalAssistOutput[$i]['full_name_frmatted'] = trim($fnReMake);
				$arrGoalAssistOutput[$i++]['assists'] = $this->getGoalAssits($row->id);

				//echo "$scoring_team_id==$home_team_id"			
			}
		}
		$arrOutput['goalsAssists'] =$arrGoalAssistOutput;
		return $arrOutput;

	}
	function getGoalAssits($goal_id){
		$sql="SELECT assist_number, goal, player_id as assister,full_name FROM new_assist, new_player WHERE new_assist.player_id = new_player.id AND goal = '$goal_id'";
		//echo $sql;
		$query = $this->db->query($sql);
		if ($query->num_rows() > 0)
		{
			$retFN = 'Assisted by: ';
			foreach ($query->result() as $row)
			{
				$goal_id  = $row->id;
				$assists_number  = $row->assists_number;
				$goal  = $row->goal;
				$player_id[]  = $row->assister;
				$full_name  = trim($row->full_name);		
				$fnSplit = explode(' ',$full_name);
				$lastName ='';
				for($x=1;$x<=count($fnSplit);$x++){
					$lastName .= $fnSplit[$x].' ';
				}
				$fnReMake = substr($fnSplit[0],0,1).'.'.$lastName;
				$full_name = trim($fnReMake);
				$retFN .= $full_name.', ';

			}
			$retFN = substr($retFN,0,-2);
			return $retFN;
		}
		else return 'Unassisted';
	}
	function findGames($activeGameID){

		$dateStr =  date  ( 'Y-m-d', strtotime("-6 hours") );

		$season = '20102011';
		$gametype= '2';
		$sql="SELECT id as theID, date,away_team, home_team,time,gametype,season  FROM nhl_schedual WHERE date = '$dateStr' ORDER BY time2";
		$htmlOut = '<ul>';
		
		$query = $this->db->query($sql);
		if ($query->num_rows() > 0)
		{
			foreach ($query->result() as $row)
			{
				$gameID  = $row->season.$row->gametype.$this->leading_zeros($row->theID,4);
				
				$date  = $row->date;
				$home_score  = 0;
				$away_score  = 0;
				$away_team  = $this->get_abbr($row->away_team,0);
				$home_team  = $this->get_abbr($row->home_team,0);
				$time = $row->time;
				//check if being played...
				$html = $this->isPlayed($gameID,$activeGameID);
				$timeNow = date("H:i:s");

				if(empty($html)){
					if(substr($activeGameID, -4) == substr($gameID, -4)) $sel = 'sel';
					else $sel ='';
					// if upcoming show this one
					$htmlOut .= "<li id = '$gameID' class='$sel'><span class='scoresHome'>$home_team</span><span class='scoresSpan'>-</span><span class='scoresAway'>$away_team</span></li>\n";
				}
				else $htmlOut .= $html;
			}
			$htmlOut .= '</ul>';
		}
		else $htmlOut .= "<ul><li><span>No games</span></li></ul>";
		return $htmlOut;
	}
	function arrayFindGames($date){

			//return $date;
		//$dateStr =  date  ('Y-m-d', strtotime("-6 hours",$date) );
		$dateStr =  date  ('Y-m-d',strtotime($date,"-6 hours"));
		$season = '20102011';
		$gametype= '2';
		$sql="SELECT g.id AS game_id, s.full_id AS theID, s.date ,s.away_team, s.home_team,s.TIME,s.gametype,s.season,g.away_team_id, g.home_team_id, g.home_score, g.away_score
		FROM nhl_schedual s
		LEFT JOIN new_game g ON s.full_id = g.id
		WHERE DATE = '$dateStr' 
		ORDER BY time2;
		";
		$arrOutput = array();
		
		$query = $this->db->query($sql);
		if ($query->num_rows() > 0)
		{
			$i=0;
			foreach ($query->result() as $row)
			{
				//$arrOutput[$i]['gameID'] = $row->season.$row->gametype.$this->leading_zeros($row->theID,4);
				$arrOutput[$i]['gameID'] = $row->theID;

				$arrOutput[$i]['date'] = $row->date;
				$arrOutput[$i]['home_score'] =	$row->home_score;
				$arrOutput[$i]['away_score'] =	$row->away_score;
				$arrOutput[$i]['home_team_id'] =	$row->home_team_id;
				$arrOutput[$i]['away_team_id'] =	$row->away_team_id;
				//home and away are reversed on schedual table
				$arrOutput[$i]['away_team']  = $this->get_abbr($row->home_team,0);
				$arrOutput[$i]['home_team']  = $this->get_abbr($row->away_team,0);
				$arrOutput[$i++]['time'] = $row->time;
			}
		}
		return $arrOutput;
	}

	function isPlayed($gameID,$activeGameID){

		$dateStr =  date  ( 'Y-m-d', strtotime("-6 hours") );

		$sql="SELECT id, game_date,away_team_id, home_team_id, home_score, away_score  FROM new_game WHERE id = '$gameID'";
		//	echo $sql;
		$query = $this->db->query($sql);
		if ($query->num_rows() > 0)
		{
			foreach ($query->result() as $row)
			{
				$gameID  = $row->id;
				$date  = $row->date;
				$home_score  = $row->home_score;
				$away_score  = $row->away_score;
				$away_team  = $this->get_abbr($row->away_team_id);
				$home_team  = $this->get_abbr($row->home_team_id);
					if(substr($activeGameID, -4) == substr($gameID, -4)) $sel = 'sel';
					else $sel ='';

				$htmlOut = "<li id = '$gameID' class='$sel'><span class='scoresHome'>$home_team <span>$home_score</span></span><span class='scoresSpan'>-</span><span class='scoresAway'><span>$away_score</span> $away_team</span></li>\n";
			}
		}
		else $htmlOut = "";
		return $htmlOut;
	}
	function findYestGames($activeGameID){
		$dateStr =  date  ( 'Y-m-d', strtotime("-30 hours") );
		$season = '20102011';
		$gametype= '2';

		$sql="SELECT id, game_date,away_team_id, home_team_id, home_score, away_score  FROM new_game WHERE game_date = '$dateStr'";
		$htmlOut = '<ul>';
		$query = $this->db->query($sql);
		
		if ($query->num_rows() > 0)
		{
			foreach ($query->result() as $row)
			{
				$gameID  = $row->id;
				$date  = $row->date;
				$home_score  = $row->home_score;
				$away_score  = $row->away_score;
				$away_team  = $this->get_abbr($row->away_team_id);
				$home_team  = $this->get_abbr($row->home_team_id);
				
				if($gameID==$activeGameID) $sel = 'sel';
				else $sel ='';
				$htmlOut .= "<li id = '$gameID' class ='$sel'><span class='scoresHome'>$home_team <span>$home_score</span></span><span class='scoresSpan'>-</span><span class='scoresAway'><span>$away_score</span> $away_team </span></li>\n";
			}
			$htmlOut .= '</ul>';
		}
		else $htmlOut .= "<ul><li><span>No games</span></li></ul>";
		return $htmlOut;
	}
	function get_abbr($value,$f=1){
		if($f==1|$f==3)
		$sql="SELECT team_id, game_summary_abbreviation,team_name  FROM new_team WHERE team_id = '$value'";
		else
		$sql="SELECT team_id, game_summary_abbreviation,team_name  FROM new_team WHERE sched = '$value'";
		$query = $this->db->query($sql);
			//echo $sql;

		foreach ($query->result() as $row)
		{
			$rowID  = $row->id;
			$abbr  = $row->game_summary_abbreviation;
			$team_name  = $row->team_name;

			$out2['abbr']  = $abbr;
			$out2['team_name']  = $team_name;
		}
		if($f==2|$f==3)
		return $out2;
		else
		return $abbr;
	}
	function leading_zeros($value, $places){
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
	function getTeamList(){
		$sql = "SELECT * FROM `new_team` order by location";
		
		$query = $this->db->query($sql);
		$value = "<select class='dropElement' multiple='multiple' id='teamAgainst' name='teamAgainst' >\n";
		foreach ($query->result() as $row)
		{
			$location  = $row->location;
			$team_name  = $row->team_name;
			$teamId = $row->team_id;
			$value .= "<option value='$teamId' selected=\"selected\">$location $team_name</option>\n";
		}
		$value .= "</select>";
		return $value;
	}
	function getLeaders($season, $gameType, $ajax=0){

		//Top Goals

		$sql = "SELECT p.full_name as full_name, g.player_id, count(g.id) as GO 
		FROM (
					  SELECT g.*
					  FROM new_goal g
					  INNER JOIN new_game ga on g.game_id = ga.id
					  WHERE ga.game_date > '2010-08-01'
					  AND period !='SO'
		) as g
		INNER JOIN new_player p ON p.id = g.player_id
		group by player_id
		order by GO DESC
		LIMIT 0,8";
		$query = $this->db->query($sql);
		$i =0;

		foreach ($query->result() as $row)
		{
					$full_name='';
				$full_name  = trim($row->full_name);		
				$fnSplit = explode(' ',$full_name);
				$lastName ='';
				for($x=1;$x<=count($fnSplit);$x++){
					$lastName .= $fnSplit[$x].' ';
				}
				$fnReMake = substr($fnSplit[0],0,1).'.'.$lastName;
				$full_name = trim($fnReMake);			
			$dataReturn['goals'][$i]['full_name']  = $full_name;
			$dataReturn['goals'][$i]['player_id']  = $row->player_id;
			$dataReturn['goals'][$i]['GO']  = $row->GO;

			//$id = substr($id, -2);
			$i++;
		}

		//Top Assists
		$sqlAssists = "SELECT  p.full_name as full_name, a.player_id, count(a.id) as A 
		FROM (
					  SELECT a.*
					  FROM new_assist a
					  INNER JOIN new_goal g on g.id = a.goal
					  INNER JOIN new_game ga on g.game_id = ga.id
					  WHERE ga.game_date > '2010-09-01'
		) as a
		INNER JOIN new_player p ON p.id = a.player_id
		group by player_id
		order by A DESC
		LIMIT 0,8";
		$queryAssists = $this->db->query($sqlAssists);
		$i =0;

		foreach ($queryAssists->result() as $rowAssists)
		{
					$full_name='';
				$full_name  = trim($rowAssists->full_name);		
				$fnSplit = explode(' ',$full_name);
				$lastName ='';
				for($x=1;$x<=count($fnSplit);$x++){
					$lastName .= $fnSplit[$x].' ';
				}
				$fnReMake = substr($fnSplit[0],0,1).'.'.$lastName;
				$full_name = trim($fnReMake);			
			$dataReturn['assists'][$i]['full_name']  = $full_name;
			$dataReturn['assists'][$i]['player_id']  = $rowAssists->player_id;
			$dataReturn['assists'][$i]['A']  = $rowAssists->A;

			//$id = substr($id, -2);
			$i++;
		}
		$sqlPoints = "SELECT p.full_name as full_name, es.player_id, sum(points) as P 
			FROM (
						  SELECT es.*
						  FROM new_event_summary es
						  INNER JOIN new_game g on es.game_id = g.id
						  WHERE g.game_date > '2010-09-01'
			) as es
			INNER JOIN new_player p ON p.id = es.player_id
			group by player_id
			order by P DESC
			LIMIT 0,8";

		$queryPoints = $this->db->query($sqlPoints);
		$i =0;

		foreach ($queryPoints->result() as $rowPoints)
		{
					$full_name='';
				$full_name  = trim($rowPoints->full_name);		
				$fnSplit = explode(' ',$full_name);
				$lastName ='';
				for($x=1;$x<=count($fnSplit);$x++){
					$lastName .= $fnSplit[$x].' ';
				}
				$fnReMake = substr($fnSplit[0],0,1).'.'.$lastName;
				$full_name = trim($fnReMake);			
			$dataReturn['points'][$i]['full_name']  = $full_name;
			$dataReturn['points'][$i]['player_id']  = $rowPoints->player_id;
			$dataReturn['points'][$i]['P']  = $rowPoints->P;

			//$id = substr($id, -2);
			$i++;
		}

		$sqlPM = "SELECT p.full_name as full_name, es.player_id, sum(plus_minus) as P_M 
		FROM (
					  SELECT es.*
					  FROM new_event_summary es
					  INNER JOIN new_game g on es.game_id = g.id
					  WHERE g.game_date > '2010-09-01'
		) as es
		INNER JOIN new_player p ON p.id = es.player_id
		group by player_id
		order by P_M DESC
		LIMIT 0,8";
		$queryPM = $this->db->query($sqlPM);
		$i =0;

		foreach ($queryPM->result() as $rowPM)
		{
					$full_name='';
				$full_name  = trim($rowPM->full_name);		
				$fnSplit = explode(' ',$full_name);
				$lastName ='';
				for($x=1;$x<=count($fnSplit);$x++){
					$lastName .= $fnSplit[$x].' ';
				}
				$fnReMake = substr($fnSplit[0],0,1).'.'.$lastName;
				$full_name = trim($fnReMake);			
			$dataReturn['p_m'][$i]['full_name']  = $full_name;
			$dataReturn['p_m'][$i]['player_id']  = $rowPM->player_id;
			$dataReturn['p_m'][$i]['P_M']  = $rowPM->P_M;

			//$id = substr($id, -2);
			$i++;
		}
			$sqlPIM = "SELECT p.full_name as full_name, pen.player_id, sum(pen.pim) as PIMs 
		FROM (
					  SELECT pen.*
					  FROM new_penalty pen
					  INNER JOIN new_game g on pen.game_id = g.id
					  WHERE g.game_date > '2010-09-01'
		) as pen
		INNER JOIN new_player p ON p.id = pen.player_id
		group by player_id
		order by PIMs DESC
		LIMIT 0,8";
		$queryPIM = $this->db->query($sqlPIM);
		$i =0;

		foreach ($queryPIM->result() as $rowPIM)
		{
					$full_name='';
				$full_name  = trim($rowPIM->full_name);		
				$fnSplit = explode(' ',$full_name);
				$lastName ='';
				for($x=1;$x<=count($fnSplit);$x++){
					$lastName .= $fnSplit[$x].' ';
				}
				$fnReMake = substr($fnSplit[0],0,1).'.'.$lastName;
				$full_name = trim($fnReMake);			
			$dataReturn['pim'][$i]['full_name']  = $full_name;
			$dataReturn['pim'][$i]['player_id']  = $rowPIM->player_id;
			$dataReturn['pim'][$i]['PIMs']  = $rowPIM->PIMs;

			//$id = substr($id, -2);
			$i++;
		}
		if($ajax)
		return json_encode($dataReturn);
		else return $dataReturn;

	}
	function getNightlyLeaders($season, $gameType,$type=1){
		//Top Goals
		if($type==0){
		$dateStr =  date  ( 'Y-m-d', strtotime("-6 hours") );
		$dateStr = "game_date = '$dateStr'";
		}
		elseif($type==2){
		$dateStr =  date  ( 'Y-m-d', strtotime("-30 hours") );
		$dateStr = "game_date = '$dateStr'";
		}
		else $dateStr = "game_date > '2010-09-01'";

		$topSG = "<td><div id='topPointsSeason'><ul>";

		$sqlPoints = "SELECT p.full_name as full_name, es.player_id, sum(points) as P 
			FROM (
						  SELECT es.*
						  FROM new_event_summary es
						  INNER JOIN new_game g on es.game_id = g.id
						  WHERE g.$dateStr 
			) as es
			INNER JOIN new_player p ON p.id = es.player_id
			group by player_id
			order by P DESC
			LIMIT 0,5";

		$queryPoints = $this->db->query($sqlPoints);
		$i =0;

		foreach ($queryPoints->result() as $rowPoints)
		{
					$full_name='';
				$full_name  = trim($rowPoints->full_name);		
				$fnSplit = explode(' ',$full_name);
				$lastName ='';
				for($x=1;$x<=count($fnSplit);$x++){
					$lastName .= $fnSplit[$x].' ';
				}
				$fnReMake = substr($fnSplit[0],0,1).'.'.$lastName;
				$full_name = trim($fnReMake);			
			$dataReturn['points'][$i]['full_name']  = $full_name;
			$dataReturn['points'][$i]['player_id']  = $rowPoints->player_id;
			$dataReturn['points'][$i]['P']  = $rowPoints->P;
			$topSG .= "<li><div class='topSeasonName'>$full_name</div><div class='topSeasonNum'>$rowPoints->P</div></li>";

			//$id = substr($id, -2);
			$i++;
		}

		$sql = "SELECT p.full_name as full_name, g.player_id, count(g.id) as GO 
		FROM (
					  SELECT g.*
					  FROM new_goal g
					  INNER JOIN new_game ga on g.game_id = ga.id
					  WHERE ga.$dateStr
					  AND period !='SO'
		) as g
		INNER JOIN new_player p ON p.id = g.player_id
		group by player_id
		order by GO DESC
		LIMIT 0,5";
		$query = $this->db->query($sql);
		$i =0;
		$topSG .= "</ul></div></td><td><div id='topGoalsSeason'><ul>";

		foreach ($query->result() as $row)
		{
			$full_name='';
			$full_name  = trim($row->full_name);		
			$fnSplit = explode(' ',$full_name);
			$lastName ='';
			for($x=1;$x<=count($fnSplit);$x++){
				$lastName .= $fnSplit[$x].' ';
			}
			$fnReMake = substr($fnSplit[0],0,1).'.'.$lastName;
			$full_name = trim($fnReMake);			
			$dataReturn['goals'][$i]['full_name']  = $full_name;
			$dataReturn['goals'][$i]['player_id']  = $row->player_id;
			$dataReturn['goals'][$i]['GO']  = $row->GO;
			$topSG .= "<li><div class='topSeasonName'>$full_name</div><div class='topSeasonNum'>$row->GO</div></li>";
			//$id = substr($id, -2);
			$i++;
		}
		$topSG .= "</ul></div></td><td><div id='topAssistsSeason'><ul>";
		//Top Assists
		$sqlAssists = "SELECT  p.full_name as full_name, a.player_id, count(a.id) as A 
		FROM (
					  SELECT a.*
					  FROM new_assist a
					  INNER JOIN new_goal g on g.id = a.goal
					  INNER JOIN new_game ga on g.game_id = ga.id
					  WHERE ga.$dateStr 
		) as a
		INNER JOIN new_player p ON p.id = a.player_id
		group by player_id
		order by A DESC
		LIMIT 0,5";
		$queryAssists = $this->db->query($sqlAssists);
		$i =0;

		foreach ($queryAssists->result() as $rowAssists)
		{
			$full_name='';
			$full_name  = trim($rowAssists->full_name);		
			$fnSplit = explode(' ',$full_name);
			$lastName ='';
			for($x=1;$x<=count($fnSplit);$x++){
				$lastName .= $fnSplit[$x].' ';
			}
			$fnReMake = substr($fnSplit[0],0,1).'.'.$lastName;
			$full_name = trim($fnReMake);			
			$dataReturn['assists'][$i]['full_name']  = $full_name;
			$dataReturn['assists'][$i]['player_id']  = $rowAssists->player_id;
			$dataReturn['assists'][$i]['A']  = $rowAssists->A;
			$topSG .= "<li><div class='topSeasonName'>$full_name</div><div class='topSeasonNum'>$rowAssists->A</div></li>";
			//$id = substr($id, -2);
			$i++;
		}

		$topSG .= "</ul></div></td><td><div id='topPIMSeason'><ul>";
			$sqlPIM = "SELECT p.full_name as full_name, pen.player_id, sum(pen.pim) as PIMs 
		FROM (
					  SELECT pen.*
					  FROM new_penalty pen
					  INNER JOIN new_game g on pen.game_id = g.id
					  WHERE g.$dateStr 
		) as pen
		INNER JOIN new_player p ON p.id = pen.player_id
		group by player_id
		order by PIMs DESC
		LIMIT 0,5";
		$queryPIM = $this->db->query($sqlPIM);
		$i =0;

		foreach ($queryPIM->result() as $rowPIM)
		{
					$full_name='';
				$full_name  = trim($rowPIM->full_name);		
				$fnSplit = explode(' ',$full_name);
				$lastName ='';
				for($x=1;$x<=count($fnSplit);$x++){
					$lastName .= $fnSplit[$x].' ';
				}
				$fnReMake = substr($fnSplit[0],0,1).'.'.$lastName;
				$full_name = trim($fnReMake);			
			$dataReturn['pim'][$i]['full_name']  = $full_name;
			$dataReturn['pim'][$i]['player_id']  = $rowPIM->player_id;
			$dataReturn['pim'][$i]['PIMs']  = $rowPIM->PIMs;
			$topSG .= "<li><div class='topSeasonName'>$full_name</div><div class='topSeasonNum'>$rowPIM->PIMs</div></li>";

			//$id = substr($id, -2);
			$i++;
		}
		$topSG .= "</ul></div></td><td><div id='topPMSeason'><ul>";

		$sqlPM = "SELECT p.full_name as full_name, es.player_id, sum(plus_minus) as P_M 
		FROM (
					  SELECT es.*
					  FROM new_event_summary es
					  INNER JOIN new_game g on es.game_id = g.id
					  WHERE g.$dateStr 
		) as es
		INNER JOIN new_player p ON p.id = es.player_id
		group by player_id
		order by P_M DESC
		LIMIT 0,5";
		$queryPM = $this->db->query($sqlPM);
		$i =0;

		foreach ($queryPM->result() as $rowPM)
		{
					$full_name='';
				$full_name  = trim($rowPM->full_name);		
				$fnSplit = explode(' ',$full_name);
				$lastName ='';
				for($x=1;$x<=count($fnSplit);$x++){
					$lastName .= $fnSplit[$x].' ';
				}
				$fnReMake = substr($fnSplit[0],0,1).'.'.$lastName;
				$full_name = trim($fnReMake);			
			$dataReturn['p_m'][$i]['full_name']  = $full_name;
			$dataReturn['p_m'][$i]['player_id']  = $rowPM->player_id;
			$dataReturn['p_m'][$i]['P_M']  = $rowPM->P_M;
			$topSG .= "<li class='playerRow'><div class='topSeasonName'>$full_name</div><div class='topSeasonNum'>$rowPM->P_M</div></li>";

			//$id = substr($id, -2);
			$i++;
		}


		$topSG .= "</ul></div></td>";
		//if($ajax)
		//return json_encode($dataReturn);
		//else return $dataReturn;
		return $topSG;
	}
	function getNightlyLeadersAjax($season, $gameType = 2,$type=1,$month='9',$day='1',$year='2011'){
		//Top Goals

		$dataReturn['points'] = array();
		$dataReturn['goals'] = array();
		$dataReturn['assists'] = array();
		$dataReturn['pim'] = array();
		$dataReturn['p_m'] = array();
		if($type==0){
		//$dateStr =  date  ( 'Y-m-d', strtotime("-6 hours") );
		$currentTime = mktime(date("H"), date("i"), date("s"), $month, $day, $year);
		$dateStr = date  ( 'Y-m-d', $currentTime - 14400); 
		$dateStr = "game_date = '$dateStr'";
		}
		elseif($type==2){
		//$dateStr =  date  ( 'Y-m-d', strtotime("-30 hours") );
		$currentTime = mktime(date("H"), date("i"), date("s"), $month, $day, $year);
		$dateStr = date  ( 'Y-m-d', $currentTime - 108000); 
		$dateStr = "game_date = '$dateStr'";
		}
		else $dateStr = "game_date > '2010-09-01'";

		$sqlPoints = "SELECT p.full_name as full_name, es.player_id, sum(points) as P 
			FROM (
						  SELECT es.*
						  FROM new_event_summary es
						  INNER JOIN new_game g on es.game_id = g.id
						  WHERE g.$dateStr 
						  AND gametype = $gameType
			) as es
			INNER JOIN new_player p ON p.id = es.player_id
			group by player_id
			order by P DESC
			LIMIT 0,5";

		$queryPoints = $this->db->query($sqlPoints);
		$i =0;

		foreach ($queryPoints->result() as $rowPoints)
		{
					$full_name='';
				$full_name  = trim($rowPoints->full_name);		
				$fnSplit = explode(' ',$full_name);
				$lastName ='';
				for($x=1;$x<=count($fnSplit);$x++){
					$lastName .= $fnSplit[$x].' ';
				}
				$fnReMake = substr($fnSplit[0],0,1).'.'.$lastName;
				$full_name = trim($fnReMake);			
			$dataReturn['points'][$i]['full_name']  = $full_name;
			$dataReturn['points'][$i]['player_id']  = $rowPoints->player_id;
			$dataReturn['points'][$i]['stat']  = $rowPoints->P;
			$i++;
		}

		$sql = "SELECT p.full_name as full_name, g.player_id, count(g.id) as GO 
		FROM (
					  SELECT g.*
					  FROM new_goal g
					  INNER JOIN new_game ga on g.game_id = ga.id
					  WHERE ga.$dateStr
					  AND period !='SO'
					  AND gametype = $gameType
		) as g
		INNER JOIN new_player p ON p.id = g.player_id
		group by player_id
		order by GO DESC
		LIMIT 0,5";
		$query = $this->db->query($sql);
		$i =0;
		foreach ($query->result() as $row)
		{
			$full_name='';
			$full_name  = trim($row->full_name);		
			$fnSplit = explode(' ',$full_name);
			$lastName ='';
			for($x=1;$x<=count($fnSplit);$x++){
				$lastName .= $fnSplit[$x].' ';
			}
			$fnReMake = substr($fnSplit[0],0,1).'.'.$lastName;
			$full_name = trim($fnReMake);			
			$dataReturn['goals'][$i]['full_name']  = $full_name;
			$dataReturn['goals'][$i]['player_id']  = $row->player_id;
			$dataReturn['goals'][$i]['stat']  = $row->GO;
			$i++;
		}
		//Top Assists
		$sqlAssists = "SELECT  p.full_name as full_name, a.player_id, count(a.id) as A 
		FROM (
					  SELECT a.*
					  FROM new_assist a
					  INNER JOIN new_goal g on g.id = a.goal
					  INNER JOIN new_game ga on g.game_id = ga.id
					  WHERE ga.$dateStr 
					  AND gametype = $gameType
		) as a
		INNER JOIN new_player p ON p.id = a.player_id
		group by player_id
		order by A DESC
		LIMIT 0,5";
		$queryAssists = $this->db->query($sqlAssists);
		$i =0;

		foreach ($queryAssists->result() as $rowAssists)
		{
			$full_name='';
			$full_name  = trim($rowAssists->full_name);		
			$fnSplit = explode(' ',$full_name);
			$lastName ='';
			for($x=1;$x<=count($fnSplit);$x++){
				$lastName .= $fnSplit[$x].' ';
			}
			$fnReMake = substr($fnSplit[0],0,1).'.'.$lastName;
			$full_name = trim($fnReMake);			
			$dataReturn['assists'][$i]['full_name']  = $full_name;
			$dataReturn['assists'][$i]['player_id']  = $rowAssists->player_id;
			$dataReturn['assists'][$i]['stat']  = $rowAssists->A;
			$i++;
		}

			$sqlPIM = "SELECT p.full_name as full_name, pen.player_id, sum(pen.pim) as PIMs 
		FROM (
					  SELECT pen.*
					  FROM new_penalty pen
					  INNER JOIN new_game g on pen.game_id = g.id
					  WHERE g.$dateStr 
					  AND gametype = $gameType
		) as pen
		INNER JOIN new_player p ON p.id = pen.player_id
		group by player_id
		order by PIMs DESC
		LIMIT 0,5";
		$queryPIM = $this->db->query($sqlPIM);
		$i =0;

		foreach ($queryPIM->result() as $rowPIM)
		{
					$full_name='';
				$full_name  = trim($rowPIM->full_name);		
				$fnSplit = explode(' ',$full_name);
				$lastName ='';
				for($x=1;$x<=count($fnSplit);$x++){
					$lastName .= $fnSplit[$x].' ';
				}
				$fnReMake = substr($fnSplit[0],0,1).'.'.$lastName;
				$full_name = trim($fnReMake);			
			$dataReturn['pim'][$i]['full_name']  = $full_name;
			$dataReturn['pim'][$i]['player_id']  = $rowPIM->player_id;
			$dataReturn['pim'][$i]['stat']  = $rowPIM->PIMs;
			//$id = substr($id, -2);
			$i++;
		}
		$sqlPM = "SELECT p.full_name as full_name, es.player_id, sum(plus_minus) as P_M 
		FROM (
					  SELECT es.*
					  FROM new_event_summary es
					  INNER JOIN new_game g on es.game_id = g.id
					  WHERE g.$dateStr 
					  AND gametype = $gameType
		) as es
		INNER JOIN new_player p ON p.id = es.player_id
		group by player_id
		order by P_M DESC
		LIMIT 0,5";
		$queryPM = $this->db->query($sqlPM);
		$i =0;

		foreach ($queryPM->result() as $rowPM)
		{
					$full_name='';
				$full_name  = trim($rowPM->full_name);		
				$fnSplit = explode(' ',$full_name);
				$lastName ='';
				for($x=1;$x<=count($fnSplit);$x++){
					$lastName .= $fnSplit[$x].' ';
				}
				$fnReMake = substr($fnSplit[0],0,1).'.'.$lastName;
				$full_name = trim($fnReMake);			
			$dataReturn['p_m'][$i]['full_name']  = $full_name;
			$dataReturn['p_m'][$i]['player_id']  = $rowPM->player_id;
			$dataReturn['p_m'][$i]['stat']  = $rowPM->P_M;
			$i++;
		}
		return $dataReturn;
	}
	function getGoalieNightlyLeadersAjax($season, $gameType = 2,$type=1,$month='9',$day='1',$year='2011'){
		//Top Goals

		$dataReturn['wins'] = array();
		$dataReturn['svp'] = array();
		$dataReturn['gaa'] = array();
		$dataReturn['so'] = array();
		if($type==0){
		//$dateStr =  date  ( 'Y-m-d', strtotime("-6 hours") );
		$currentTime = mktime(date("H"), date("i"), date("s"), $month, $day, $year);
		$dateStr = date  ( 'Y-m-d', $currentTime - 14400); 
		$dateStr = "game_date = '$dateStr'";
		}
		elseif($type==2){
		//$dateStr =  date  ( 'Y-m-d', strtotime("-30 hours") );
		$currentTime = mktime(date("H"), date("i"), date("s"), $month, $day, $year);
		$dateStr = date  ( 'Y-m-d', $currentTime - 108000); 
		$dateStr = "game_date = '$dateStr'";
		}
		else $dateStr = "game_date > '2010-09-01'";
		
		$sqlWins = "SELECT full_name,goalie_id, COUNT(*) AS W
			FROM new_goalies g
			INNER JOIN new_player p ON g.goalie_id = p.id
			INNER JOIN new_game ga ON g.game_id = ga.id
			WHERE w_l_indicator = 'W'
			AND gametype = $gameType
			AND ga.$dateStr
			GROUP BY p.id
			ORDER BY W DESC
			LIMIT 0,8";
		//echo $sqlWins;
		$queryWins = $this->db->query($sqlWins);
		$i =0;

		foreach ($queryWins->result() as $rowWins)
		{
					$full_name='';
				$full_name  = trim($rowWins->full_name);		
				$fnSplit = explode(' ',$full_name);
				$lastName ='';
				for($x=1;$x<=count($fnSplit);$x++){
					$lastName .= $fnSplit[$x].' ';
				}
				$fnReMake = substr($fnSplit[0],0,1).'.'.$lastName;
				$full_name = trim($fnReMake);			
			$dataReturn['wins'][$i]['full_name']  = $full_name;
			$dataReturn['wins'][$i]['player_id']  = $rowWins->goalie_id;
			$dataReturn['wins'][$i]['stat']  = $rowWins->W;
			$i++;
		}
		$sqlSVP = "SELECT full_name, goalie_id, SUM(total_ga) AS GA, SUM(total_sa) AS SA, (SUM(total_sa) - SUM(total_ga))/SUM(total_sa) AS SP  
		FROM new_goalies g
		INNER JOIN new_player p ON p.id = g.goalie_id
		INNER JOIN new_game ga ON g.game_id = ga.id
		INNER JOIN nhl_players nhlp ON p.nhl_id = nhlp.id
		WHERE ga.$dateStr
		AND gametype = $gameType
		GROUP BY nhlp.id
		ORDER BY SP DESC
		";
		$query = $this->db->query($sqlSVP);
		$i =0;

		foreach ($query->result() as $row)
		{
			$full_name='';
			$full_name  = trim($row->full_name);		
			$fnSplit = explode(' ',$full_name);
			$lastName ='';
			for($x=1;$x<=count($fnSplit);$x++){
				$lastName .= $fnSplit[$x].' ';
			}
			$SP = round($row->SP*100,1);
			$fnReMake = substr($fnSplit[0],0,1).'.'.$lastName;
			$full_name = trim($fnReMake);		
			if(($gameType==3 && $i<8 && $row->SA > '10')|($row->SA > '1000' && $i<8)){

			$dataReturn['svp'][$i]['full_name']  = $full_name;
			$dataReturn['svp'][$i]['player_id']  = $row->goalie_id;
			$dataReturn['svp'][$i]['stat']  = $SP;
			$i++;
			}
		}
		$sqlGAA = "SELECT full_name,goalie_id, SUM(total_ga) AS GA, SUM(TIME_TO_SEC(total_toi))/60 AS TOI, ROUND(SUM(total_ga) / (SUM(TIME_TO_SEC(total_toi))/60/60),2) AS GAA
		FROM new_goalies go
		INNER JOIN new_player pl ON pl.id = go.goalie_id
		INNER JOIN new_game ga ON ga.id = go.game_id
		INNER JOIN nhl_players nhlp ON nhlp.id = pl.nhl_id
		WHERE ga.$dateStr
		AND gametype = $gameType
		GROUP BY nhlp.id
		ORDER BY GAA ASC";
		$query = $this->db->query($sqlGAA);
		$i =0;

		foreach ($query->result() as $row)
		{
			$full_name='';
			$full_name  = trim($row->full_name);		
			$fnSplit = explode(' ',$full_name);
			$lastName ='';
			for($x=1;$x<=count($fnSplit);$x++){
				$lastName .= $fnSplit[$x].' ';
			}
			$fnReMake = substr($fnSplit[0],0,1).'.'.$lastName;
			$full_name = trim($fnReMake);	
			if(($gameType==3 && $i<8 && $row->TOI > '5')|($row->TOI > '1000' && $i<8) ){
			$dataReturn['gaa'][$i]['full_name']  = $full_name;
			$dataReturn['gaa'][$i]['player_id']  = $row->goalie_id;
			$dataReturn['gaa'][$i]['stat']  = $row->GAA;
			$i++;
			}			
		}
		$sqlSo = "SELECT full_name, COUNT(*) AS SO
			FROM
			(
			SELECT goalie_id, game_id
			FROM new_goalies go
			INNER JOIN new_game ga ON go.game_id = ga.id
			WHERE (w_l_indicator = 'L' OR w_l_indicator = 'W' OR w_l_indicator = 'OT')
			AND total_ga = 0 
			AND ga.$dateStr
			AND goalie_team_id = home_team_id
			AND away_score = 0
			AND gametype = $gameType

			UNION ALL

			SELECT goalie_id, game_id
			FROM new_goalies go
			INNER JOIN new_game ga ON go.game_id = ga.id
			WHERE (w_l_indicator = 'L' OR w_l_indicator = 'W' OR w_l_indicator = 'OT')
			AND total_ga = 0 
			AND ga.$dateStr
			AND goalie_team_id = away_team_id
			AND home_score = 0
			AND gametype = $gameType
			) AS shutouts
			INNER JOIN new_player pl ON pl.id = shutouts.goalie_id
			INNER JOIN nhl_players nhlp ON nhlp.id = pl.nhl_id
			GROUP BY nhl_id
			ORDER BY SO DESC
			LIMIT 0,8
			";
		$query = $this->db->query($sqlSo);
		$i =0;

		foreach ($query->result() as $row)
		{
			$full_name='';
			$full_name  = trim($row->full_name);		
			$fnSplit = explode(' ',$full_name);
			$lastName ='';
			for($x=1;$x<=count($fnSplit);$x++){
				$lastName .= $fnSplit[$x].' ';
			}
			$fnReMake = substr($fnSplit[0],0,1).'.'.$lastName;
			$full_name = trim($fnReMake);			
			$dataReturn['so'][$i]['full_name']  = $full_name;
			$dataReturn['so'][$i]['stat']  = $row->SO;
			$i++;
		}
		return $dataReturn;
	}
	function getGoalieLeaders($season, $gameType,$type=1){
		//Top Goals
		if($type==0){
		$dateStr =  date  ( 'Y-m-d', strtotime("-6 hours") );
		$dateStr = "game_date = '$dateStr'";
		}
		elseif($type==2){
		$dateStr =  date  ( 'Y-m-d', strtotime("-30 hours") );
		$dateStr = "game_date = '$dateStr'";
		}
		else $dateStr = "game_date > '2010-09-01'";

		$topGoalieLeaders = "<td><div id='topWinsSeason'><ul>";
		
		$sqlWins = "SELECT full_name,goalie_id, COUNT(*) AS W
			FROM new_goalies g
			INNER JOIN new_player p ON g.goalie_id = p.id
			INNER JOIN new_game ga ON g.game_id = ga.id
			WHERE w_l_indicator = 'W'
			AND (ga.game_date > '2010-8-1' AND ga.game_date < '2011-8-1')
			GROUP BY p.id
			ORDER BY W DESC
			LIMIT 0,5";

		$queryWins = $this->db->query($sqlWins);
		$i =0;

		foreach ($queryWins->result() as $rowWins)
		{
					$full_name='';
				$full_name  = trim($rowWins->full_name);		
				$fnSplit = explode(' ',$full_name);
				$lastName ='';
				for($x=1;$x<=count($fnSplit);$x++){
					$lastName .= $fnSplit[$x].' ';
				}
				$fnReMake = substr($fnSplit[0],0,1).'.'.$lastName;
				$full_name = trim($fnReMake);			
			$dataReturn['wins'][$i]['full_name']  = $full_name;
			$dataReturn['wins'][$i]['player_id']  = $rowWins->goalie_id;
			$dataReturn['wins'][$i]['W']  = $rowWins->P;
			$topGoalieLeaders .= "<li><div class='topSeasonName'>$full_name</div><div class='topSeasonNum'>$rowWins->W</div></li>";
			$i++;
		}
		$sqlSVP = "SELECT full_name, goalie_id, SUM(total_ga) AS GA, SUM(total_sa) AS SA, (SUM(total_sa) - SUM(total_ga))/SUM(total_sa) AS SP  
		FROM new_goalies g
		INNER JOIN new_player p ON p.id = g.goalie_id
		INNER JOIN new_game ga ON g.game_id = ga.id
		INNER JOIN nhl_players nhlp ON p.nhl_id = nhlp.id
		WHERE game_date > '2010-08-01'
		GROUP BY nhlp.id
		ORDER BY SP DESC
		";
		$query = $this->db->query($sqlSVP);
		$i =0;
		$topGoalieLeaders .= "</ul></div></td><td><div id='topSVP'><ul>";

		foreach ($query->result() as $row)
		{
			$full_name='';
			$full_name  = trim($row->full_name);		
			$fnSplit = explode(' ',$full_name);
			$lastName ='';
			for($x=1;$x<=count($fnSplit);$x++){
				$lastName .= $fnSplit[$x].' ';
			}
			$SP = round($row->SP*100,1);
			$fnReMake = substr($fnSplit[0],0,1).'.'.$lastName;
			$full_name = trim($fnReMake);			
			$dataReturn['svp'][$i]['full_name']  = $full_name;
			$dataReturn['svp'][$i]['player_id']  = $row->goalie_id;
			$dataReturn['svp'][$i]['SP']  = $SP;
			if($row->SA > '1000' && $i<5 ){

				$topGoalieLeaders .= "<li><div class='topSeasonName'>$full_name</div><div class='topSeasonNum'>$SP </div></li>";
				$i++;
			}
		}
		$sqlGAA = "SELECT full_name,goalie_id, SUM(total_ga) AS GA, SUM(TIME_TO_SEC(total_toi))/60 AS TOI, ROUND(SUM(total_ga) / (SUM(TIME_TO_SEC(total_toi))/60/60),2) AS GAA
		FROM new_goalies go
		INNER JOIN new_player pl ON pl.id = go.goalie_id
		INNER JOIN new_game ga ON ga.id = go.game_id
		INNER JOIN nhl_players nhlp ON nhlp.id = pl.nhl_id
		WHERE ga.game_date > '2010-8-1'
		GROUP BY nhlp.id
		ORDER BY GAA ASC";
		$query = $this->db->query($sqlGAA);
		$i =0;
		$topGoalieLeaders .= "</ul></div></td><td><div id='topGAA'><ul>";

		foreach ($query->result() as $row)
		{
			$full_name='';
			$full_name  = trim($row->full_name);		
			$fnSplit = explode(' ',$full_name);
			$lastName ='';
			for($x=1;$x<=count($fnSplit);$x++){
				$lastName .= $fnSplit[$x].' ';
			}
			$fnReMake = substr($fnSplit[0],0,1).'.'.$lastName;
			$full_name = trim($fnReMake);			
			$dataReturn['gaa'][$i]['full_name']  = $full_name;
			$dataReturn['gaa'][$i]['player_id']  = $row->goalie_id;
			$dataReturn['gaa'][$i]['GAA']  = $row->GAA;
			if($row->TOI > '1000' && $i<5 ){

				$topGoalieLeaders .= "<li><div class='topSeasonName'>$full_name</div><div class='topSeasonNum'>$row->GAA</div></li>";
				$i++;
			}
			//$id = substr($id, -2);
			
		}
		$sqlSo = 'SELECT full_name, COUNT(*) AS SO
			FROM
			(
			SELECT goalie_id, game_id
			FROM new_goalies go
			INNER JOIN new_game ga ON go.game_id = ga.id
			WHERE (w_l_indicator = "L" OR w_l_indicator = "W" OR w_l_indicator = "OT")
			AND total_ga = 0 
			AND game_date > "2010-08-01"
			AND goalie_team_id = home_team_id
			AND away_score = 0

			UNION ALL

			SELECT goalie_id, game_id
			FROM new_goalies go
			INNER JOIN new_game ga ON go.game_id = ga.id
			WHERE (w_l_indicator = "L" OR w_l_indicator = "W" OR w_l_indicator = "OT")
			AND total_ga = 0 
			AND game_date > "2010-08-01"
			AND goalie_team_id = away_team_id
			AND home_score = 0
			) AS shutouts
			INNER JOIN new_player pl ON pl.id = shutouts.goalie_id
			INNER JOIN nhl_players nhlp ON nhlp.id = pl.nhl_id
			GROUP BY nhl_id
			ORDER BY SO DESC
			LIMIT 0,5
			';
		$query = $this->db->query($sqlSo);
		$i =0;
		$topGoalieLeaders .= "</ul></div></td><td><div id='topSO'><ul>";

		foreach ($query->result() as $row)
		{
			$full_name='';
			$full_name  = trim($row->full_name);		
			$fnSplit = explode(' ',$full_name);
			$lastName ='';
			for($x=1;$x<=count($fnSplit);$x++){
				$lastName .= $fnSplit[$x].' ';
			}
			$fnReMake = substr($fnSplit[0],0,1).'.'.$lastName;
			$full_name = trim($fnReMake);			
			$dataReturn['so'][$i]['full_name']  = $full_name;
			$dataReturn['so'][$i]['SO']  = $row->SO;
			$topGoalieLeaders .= "<li><div class='topSeasonName'>$full_name</div><div class='topSeasonNum'>$row->SO</div></li>";
			//$id = substr($id, -2);
			$i++;
		}
		$topGoalieLeaders .= "</ul></div></td>";
		//if($ajax)
		//return json_encode($dataReturn);
		//else return $dataReturn;
		return $topGoalieLeaders;
	}
	function getStandings($selConf,$selDiv,$selWest,$selEast,$season='20102011',$gameType='2'){

		if($selConf){
			$sqlOrder = ' conference_name DESC ';
		}
		else{
			$sqlOrder = ' divison_name DESC ';
		}
		$sql = "
		SELECT games_played.team_name, divison_name, conference_name, teamid, GP, points FROM
		(
		SELECT team_name,teamid, COUNT(*) AS GP
		FROM
		(
		SELECT home_team_id AS teamid
		FROM new_game
		WHERE game_date > '2010-08-01'
		UNION ALL
		SELECT away_team_id AS teamid
		FROM new_game
		WHERE game_date > '2010-08-01'
		) AS games
		INNER JOIN new_team nt ON nt.team_id = games.teamid
		GROUP BY teamid
		) AS games_played
		INNER JOIN
		(
		SELECT team_name,totalPoints.team_id, divison_name, conference_name, SUM(points) AS points
		FROM (
		SELECT t.team_id ,(COUNT(*)*2) AS points
		FROM new_game g, new_team t 
		WHERE winner = t.team_id AND game_date > '2010-08-01' AND isFinal ='Final' 
		GROUP BY winner
		UNION ALL
		SELECT team_id, SUM(OTpoints) 
		FROM (
		SELECT home_team_id AS team_id, SUM(home_team_id <> winner) AS OTpoints FROM new_game
		WHERE game_date > '2010-08-01' AND (number_of_periods = 'OT' OR number_of_periods = 'SO')
		GROUP BY home_team_id
		UNION ALL
		SELECT away_team_id, SUM(away_team_id <> winner) FROM new_game
		WHERE game_date > '2010-08-01' AND (number_of_periods = 'OT' OR number_of_periods = 'SO')
		GROUP BY away_team_id
		) AS OTSOPoints
		GROUP BY team_id
		) AS totalPoints
		INNER JOIN new_team n ON totalPoints.team_id = n.team_id
		GROUP BY totalPoints.team_id
		ORDER BY points DESC
		) AS points ON points.team_id = games_played.teamid
		";

		$query = $this->db->query($sql);
		$i=0;
		foreach ($query->result() as $row)
		{
			$arrReturn[$i]['team_id']  = $row->team_id;
			$arrReturn[$i]['team_name']  = $row->team_name;
			$arrReturn[$i]['points']  = $row->points;
			$arrReturn[$i]['GP']  = $row->GP;
			$arrReturn[$i]['conference_name']  = $row->conference_name;
			$arrReturn[$i++]['divison_name']  = $row->divison_name;
		}
		return $arrReturn;
	}
	function getAjaxStandings($season='20102011', $gameType, $confOrDiv){
		$arrReturn = array();
		if($confOrDiv =='div')$orderBy = 'conference_name, divison_name, points DESC, _ROW DESC';
		else $orderBy = 'conference_name, points DESC, _ROW DESC';
		$sql = "
		SELECT games_played.team_name, divison_name, conference_name, teamid, GP, points, _ROW FROM
		(
		SELECT team_name,teamid, COUNT(*) AS GP
				FROM
				(
		SELECT home_team_id AS teamid
		FROM new_game
		WHERE game_date > '2010-08-01' AND game_date < '2011-04-13'
		UNION ALL
		SELECT away_team_id AS teamid
		FROM new_game
		WHERE game_date > '2010-08-01' AND game_date < '2011-04-13'
				) AS games
		INNER JOIN new_team nt ON nt.team_id = games.teamid
				GROUP BY teamid
		) AS games_played
		INNER JOIN
		(
				SELECT team_name,totalPoints.team_id, divison_name, conference_name, SUM(points) AS points, SUM(_ROW) AS _ROW
				FROM (
		SELECT t.team_id, (COUNT(*)*2) AS points, 0 AS _ROW
		FROM new_game g, new_team t 
		WHERE winner = t.team_id AND game_date > '2010-08-01' AND game_date < '2011-04-13' AND isFinal ='Final' AND number_of_periods = 'SO'
		GROUP BY winner
		UNION ALL
		SELECT t.team_id, (COUNT(*)*2) AS points, COUNT(*) AS _ROW
		FROM new_game g, new_team t 
		WHERE winner = t.team_id AND game_date > '2010-08-01' AND game_date < '2011-04-13' AND isFinal ='Final' AND number_of_periods <> 'SO'
		GROUP BY winner
		UNION ALL
		SELECT team_id, SUM(OTpoints), 0 AS _ROW
		FROM (
		SELECT home_team_id AS team_id, SUM(home_team_id <> winner) AS OTpoints FROM new_game
		WHERE game_date > '2010-08-01' AND game_date < '2011-04-13' AND (number_of_periods = 'OT' OR number_of_periods = 'SO')
		GROUP BY home_team_id
		UNION ALL
		SELECT away_team_id, SUM(away_team_id <> winner) FROM new_game
		WHERE game_date > '2010-08-01' AND game_date < '2011-04-13' AND (number_of_periods = 'OT' OR number_of_periods = 'SO')
		GROUP BY away_team_id
		) AS OTSOPoints
		GROUP BY team_id
				) AS totalPoints
				INNER JOIN new_team n ON totalPoints.team_id = n.team_id
				GROUP BY totalPoints.team_id
				ORDER BY points DESC
		 ) AS points ON points.team_id = games_played.teamid
		 ORDER BY $orderBy
		";

		$query = $this->db->query($sql);
		$i=0;
		foreach ($query->result() as $row)
		{
			$arrReturn[$i]['team_id']  = $row->team_id;
			$arrReturn[$i]['team_name']  = $row->team_name;
			$arrReturn[$i]['points']  = $row->points;
			$arrReturn[$i]['GP']  = $row->GP;
			$arrReturn[$i]['conference_name']  = $row->conference_name;
			$arrReturn[$i]['divison_name']  = $row->divison_name;
			if( $row->team_name=='Coyotes') $ROW = $row->_ROW+1;
			else  $ROW = $row->_ROW;
			$arrReturn[$i++]['ROW']  = $ROW;
		}
		return $arrReturn;
	}
}
?>