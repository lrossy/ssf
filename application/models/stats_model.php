<?php
error_reporting(E_PARSE); 
class Stats_Model extends CI_Model
{
    function __construct()
    {
        // Call the Model constructor
        parent::__construct();
    }

	function _playerData_v2($fullname,$gametype='2', $season='20092010',$curDiv='1', $stattype='tabGoal',$strength= 'EV-SH-PP',$period='f-s-t-ot-gt',$teamAgainst='0',$location='hm-aw',$penalities,$esStat){
		$teamID = false;
		$nhlID = $this->getPlayerNHLID($fullname);
		
		//if empty, then team
		if(empty($nhlID)){
			$teamID = $this->getTeamListID($fullname);
		}
		if(!$teamID){
			//playerData
			$returnVal = $this->_getPlayerData($fullname,$gametype, $season,$curDiv, $stattype,$strength,$period,$teamAgainst,$location,$penalities,$esStat,$nhlID);
		}
		else{
			//teamData
			$returnVal = $this->_getTeamData($fullname,$gametype, $season,$curDiv, $stattype,$strength,$period,$teamAgainst,$location,$penalities,$esStat,$teamID);
		}
//echo $fullname;
		return $returnVal;
	}
	function _getTeamData($fullname,$gametype='2', $season='20092010',$curDiv='1', $stattype='tabGoal',$strength= 'EV-SH-PP',$period='f-s-t-ot-gt',$teamAgainst='0',$location='hm-aw',$penalities,$esStat,$teamID){
		$sql = "SELECT team_id, location, team_name, game_summary_mapping,game_summary_abbreviation, divison_name, conference_name FROM new_team WHERE team_id = '$teamID'";
			
		$query = $this->db->query($sql);
		//echo $sql;
		if(count($query->result())>0)
		{	
			foreach ($query->result() as $row)
			{
				$myLeagues['lname']= $row->team_name;
				$myLeagues['team_id']= $row->team_id;
				$myLeagues['location']= $row->location;
				$myLeagues['team_name']= $row->team_name;
				$myLeagues['game_summary_mapping']= $row->game_summary_mapping;
				$myLeagues['game_summary_abbreviation']= $row->game_summary_abbreviation;
				$myLeagues['division_name']= $row->division_name;
				$myLeagues['conference_name']= $row->conference_name;
				$myLeagues['curDivID']=$curDiv;
			}
		}
		else {return 0;}
		$myLeagues['stat'] = $stattype; 

		switch($stattype){
			case 'tabGoal':
				$goals[] = $this->stats_model->getTeamGoals($myLeagues['team_id'],$season,$strength,$period,$teamAgainst,$gametype,$location);
				$return = $this->stats_model->genCSVRecursive($goals,1);
				$myLeagues['statVal'] = substr($return,0,-1); 
				//echo $return;
				break;
			case 'tabAssist':
				$assists[] = $this->stats_model->getTeamAssists($myLeagues['team_id'],$season,$strength,$period,$teamAgainst,$gametype,$location);
				$return = $this->stats_model->genCSVRecursive($assists,1);
				$myLeagues['statVal'] = substr($return,0,-1); 
				break;
			case 'tabPoints':
				$points[] = $this->stats_model->getTeamPoints($myLeagues['team_id'],$season,$strength,$period,$teamAgainst,$gametype,$location);
				$return = $this->stats_model->genCSVRecursive($points,1);
				$myLeagues['statVal'] = substr($return,0,-1); 

				break;
			case 'tabPims':
				$pims[] = $this->stats_model->getTeamPims($myLeagues['team_id'],$season,$strength,$period,$teamAgainst,$gametype,$penalities,$location);
				$return = $this->stats_model->genCSVRecursive($pims,1);
				$myLeagues['statVal'] = substr($return,0,-1); 
				break;
			case 'tabEventstats':
				$pims[] = $this->stats_model->getTeamES($myLeagues['team_id'],$season,$teamAgainst,$gametype,$esStat,$location);
				$return = $this->stats_model->genCSVRecursive($pims,1);
				$myLeagues['statVal'] = substr($return,0,-1); 
				break;
				default:
				$myLeagues['statVal'] = 0;
		}
		$returnVal = $myLeagues;
		return $returnVal;
	}
	function _getPlayerData($fullname,$gametype='2', $season='20092010',$curDiv='1', $stattype='tabGoal',$strength= 'EV-SH-PP',$period='f-s-t-ot-gt',$teamAgainst='0',$location='hm-aw',$penalities,$esStat,$nhlID){
		$sql = "SELECT id, fname, lname, pos,team,DATE_FORMAT(dob, '%b %d, %Y') as dob,homecity,DATE_FORMAT(NOW(), '%Y') - DATE_FORMAT(dob, '%Y') - (DATE_FORMAT(NOW(), '00-%m-%d') < DATE_FORMAT(dob, '00-%m-%d')) AS age FROM nhl_players WHERE id = '$nhlID'";
		//print_r($sql);
			
		$query = $this->db->query($sql);
		//echo $sql;
		if(count($query->result())>0)
		{	
			foreach ($query->result() as $row)
			{
				$myLeagues['id']= $row->id;
				$myLeagues['fname']= $row->fname;
				$myLeagues['lname']= $row->lname;
				$myLeagues['pos']= $row->pos;
				$myLeagues['team']= $row->team;
				$myLeagues['dob']= $row->dob;
				$myLeagues['age']= $row->age;
				$myLeagues['homecity']= $row->homecity;
				$myLeagues['curDivID']=$curDiv;
			}
		}
		else {return 0;}
		$myLeagues['stat'] = $stattype; 

		switch($stattype){
			case 'tabGoal':
				$goals[] = $this->stats_model->getPlayerGoals($fullname,$season,$strength,$period,$teamAgainst,$gametype,$location);
				$return = $this->stats_model->genCSVRecursive($goals,1);
				$myLeagues['statVal'] = substr($return,0,-1); 
				//echo $return;
				break;
			case 'tabAssist':
				$assists[] = $this->stats_model->getPlayerAssists($fullname,$season,$strength,$period,$teamAgainst,$gametype,$location);
				$return = $this->stats_model->genCSVRecursive($assists,1);
				$myLeagues['statVal'] = substr($return,0,-1); 
				break;
			case 'tabPoints':
				$points[] = $this->stats_model->getPlayerPoints($fullname,$season,$strength,$period,$teamAgainst,$gametype,$location);
				$return = $this->stats_model->genCSVRecursive($points,1);
				$myLeagues['statVal'] = substr($return,0,-1); 

				break;
			case 'tabPims':
				$pims[] = $this->stats_model->getPlayerPims($fullname,$season,$strength,$period,$teamAgainst,$gametype,$penalities,$location);
				$return = $this->stats_model->genCSVRecursive($pims,1);
				$myLeagues['statVal'] = substr($return,0,-1); 
				break;
			case 'tabEventstats':
				$pims[] = $this->stats_model->getPlayerES($fullname,$season,$teamAgainst,$gametype,$esStat,$location);
				$return = $this->stats_model->genCSVRecursive($pims,1);
				$myLeagues['statVal'] = substr($return,0,-1); 
				break;
				default:
				$myLeagues['statVal'] = 0;
		}
		$returnVal = $myLeagues;
		return $returnVal;
	}


	function _playerData($fullname,$gametype='2', $season='20092010',$curDiv='1'){

		$pNHLID = $this->getPlayerNHLID($fullname);

		$sql = "SELECT id, fname, lname, pos,team,DATE_FORMAT(dob, '%b %d, %Y') as dob,homecity,DATE_FORMAT(NOW(), '%Y') - DATE_FORMAT(dob, '%Y') - (DATE_FORMAT(NOW(), '00-%m-%d') < DATE_FORMAT(dob, '00-%m-%d')) AS age FROM nhl_players WHERE id = '$pNHLID'";
			
		$query = $this->db->query($sql);
		//echo $sql;
		if(count($query->result())>0)
		{	
			foreach ($query->result() as $row)
			{
				$myLeagues['id']= $row->id;
				$myLeagues['fname']= $row->fname;
				$myLeagues['lname']= $row->lname;
				$myLeagues['pos']= $row->pos;
				$myLeagues['team']= $row->team;
				$myLeagues['dob']= $row->dob;
				$myLeagues['age']= $row->age;
				$myLeagues['homecity']= $row->homecity;
			}
		}
		else {return 0;}
				$sqlDates = $this->findDates($season);
				if (count(explode('-',$season))>1) $myLeagues['seasonText'] = 'Multiple';
				else $myLeagues['seasonText'] = $season;
				$myLeagues['curDivID']=$curDiv;
				$sqlGametype = $this->findGT($gametype);
			//	echo $sqlGametype;
				$gametypeHeader = str_replace("-", " & ", $gametype);
				$gametypeHeader = str_replace("re", "Regular Season", $gametypeHeader);
				$gametypeHeader = str_replace("pl", "Playoffs", $gametypeHeader);
				$myLeagues['gametypeHeader']=$gametypeHeader;
				$newLastName = $myLeagues['lname'];
			if (get_magic_quotes_gpc()) $newLastName = stripslashes($newLastName);
			$newLastName = mysql_escape_string($newLastName);

				$sqlDatesSpecial = str_replace("WHERE", "", $sqlDates);
			$sql3 = "SELECT p.player_f_name, p.player_l_name, count(es.id) as GP, sum(es.goals) as Goals, sum(es.assists) as Assists, sum(es.points) as Points, sum(es.plus_minus) as PlusMinus, penalty.pim2 as PIM, sum(es.sog) as SOG 

                       FROM new_event_summary es
                       INNER JOIN new_player p ON p.id = es.player_id
                       INNER JOIN new_game g ON g.id = es.game_id
                       LEFT JOIN
						(
							 SELECT sum(pen.pim) as pim2, play.id as id
							 FROM new_penalty pen
							 INNER JOIN new_player play ON play.id = pen.player_id
							 INNER JOIN new_game g ON g.id = pen.game_id
							 WHERE play.nhl_id = '$pNHLID'
							  AND $sqlDatesSpecial 
							  $sqlGametype
							 GROUP BY play.id 
						) penalty ON penalty.id = p.id

						$sqlDates
						$sqlGametype
                       AND p.nhl_id = '$pNHLID'
                       GROUP BY p.nhl_id";


		//	echo $sql3;
		$query2 = $this->db->query($sql3);
		if(count($query2->result())>0)
		{	
			foreach ($query2->result() as $row2)
			{
				$myLeagues['SOG']= $row2->SOG;
				$myLeagues['GP']= $row2->GP;
				$myLeagues['Goals']= $row2->Goals;
				$myLeagues['Assists']= $row2->Assists;
				$myLeagues['Points']= $row2->Points;
				$myLeagues['PlusMinus']= $row2->PlusMinus;
				$myLeagues['PIM']= (empty($row2->PIM))?'0':$row2->PIM;
			}
		}
		else	{
				$myLeagues['SOG']= 0;
				$myLeagues['GP']= 0;
				$myLeagues['Goals']= 0;
				$myLeagues['Assists']= 0;
				$myLeagues['Points']= 0;
				$myLeagues['PlusMinus']= 0;
				$myLeagues['PIM']= 0;
			}
		$returnVal = $myLeagues;
		return $returnVal;
	}
	function _playerDataOLD($fullname,$gametype='2', $season='20092010',$curDiv='1'){
		echo $this->getPlayerNHLID($fullname);
		$name_pieces = explode(" ", $fullname);
		if(count($name_pieces)>=3){
			//print_r($name_pieces);
			$first = $name_pieces['0'];
			$last = $name_pieces['1'].' '.$name_pieces['2'];
			if (get_magic_quotes_gpc()) $first = stripslashes($first);
			$first = mysql_escape_string($first);
			$first = substr($first,0,3);
			if (get_magic_quotes_gpc()) $last = stripslashes($last);
			$last = mysql_escape_string($last);
			$sql = "SELECT id, fname, lname, pos,team,DATE_FORMAT(dob, '%b %d, %Y') as dob,homecity,DATE_FORMAT(NOW(), '%Y') - DATE_FORMAT(dob, '%Y') - (DATE_FORMAT(NOW(), '00-%m-%d') < DATE_FORMAT(dob, '00-%m-%d')) AS age FROM nhl_players WHERE fname LIKE '$first%' AND lname LIKE '$last'";
			$query = $this->db->query($sql);
			//echo $sql;
			if(count($query->result())<=0){
			$sql = "SELECT id, fname, lname, pos,team,DATE_FORMAT(dob, '%b %d, %Y') as dob,homecity,DATE_FORMAT(NOW(), '%Y') - DATE_FORMAT(dob, '%Y') - (DATE_FORMAT(NOW(), '00-%m-%d') < DATE_FORMAT(dob, '00-%m-%d')) AS age FROM nhl_players WHERE fullname LIKE '$fullname'";
			}
		}
		else{
			$first = $name_pieces['0'];
			$last = $name_pieces['1'];
			$player_name = $first.$last;
			if (get_magic_quotes_gpc()) $first = stripslashes($first);
			$first = mysql_escape_string($first);
			$first = substr($first,0,3);
			if (get_magic_quotes_gpc()) $last = stripslashes($last);
			$last = mysql_escape_string($last);
			$sql = "SELECT id, fname, lname, pos,team,DATE_FORMAT(dob, '%b %d, %Y') as dob,homecity,DATE_FORMAT(NOW(), '%Y') - DATE_FORMAT(dob, '%Y') - (DATE_FORMAT(NOW(), '00-%m-%d') < DATE_FORMAT(dob, '00-%m-%d')) AS age FROM nhl_players WHERE fname LIKE '$first%' AND lname LIKE '$last'";
		}
		$query = $this->db->query($sql);
		//echo $sql;
		if(count($query->result())>0)
		{	
			foreach ($query->result() as $row)
			{
				$myLeagues['id']= $row->id;
				$myLeagues['fname']= $row->fname;
				$myLeagues['lname']= $row->lname;
				$myLeagues['pos']= $row->pos;
				$myLeagues['team']= $row->team;
				$myLeagues['dob']= $row->dob;
				$myLeagues['age']= $row->age;
				$myLeagues['homecity']= $row->homecity;
			}
		}
		else {return 0;}
				$sqlDates = $this->findDates($season);
				if (count(explode('-',$season))>1) $myLeagues['seasonText'] = 'Multiple';
				else $myLeagues['seasonText'] = $season;
				$myLeagues['curDivID']=$curDiv;
				$sqlGametype = $this->findGT($gametype);
			//	echo $sqlGametype;
				$gametypeHeader = str_replace("-", " & ", $gametype);
				$gametypeHeader = str_replace("re", "Regular Season", $gametypeHeader);
				$gametypeHeader = str_replace("pl", "Playoffs", $gametypeHeader);
				$myLeagues['gametypeHeader']=$gametypeHeader;
				$newLastName = $myLeagues['lname'];
			if (get_magic_quotes_gpc()) $newLastName = stripslashes($newLastName);
			$newLastName = mysql_escape_string($newLastName);

				$sqlDatesSpecial = str_replace("WHERE", "", $sqlDates);
			$sql3 = "SELECT p.player_f_name, p.player_l_name, count(es.id) as GP, sum(es.goals) as Goals, sum(es.assists) as Assists, sum(es.points) as Points, sum(es.plus_minus) as PlusMinus, penalty.pim2 as PIM, sum(es.sog) as SOG 

                       FROM new_event_summary es
                       INNER JOIN new_player p ON p.id = es.player_id
                       INNER JOIN new_game g ON g.id = es.game_id
                       LEFT JOIN
						(
							 SELECT sum(pen.pim) as pim2, play.id as id
							 FROM new_penalty pen
							 INNER JOIN new_player play ON play.id = pen.player_id
							 INNER JOIN new_game g ON g.id = pen.game_id
							 WHERE play.player_f_name LIKE '$first%'
							 AND play.player_l_name LIKE '$newLastName'
							  AND $sqlDatesSpecial 
							  $sqlGametype
							 GROUP BY play.id 
						) penalty ON penalty.id = p.id

						$sqlDates
						$sqlGametype
                       AND p.player_f_name LIKE '$first%'
                       AND p.player_l_name LIKE '$newLastName'
                       GROUP BY p.player_f_name, p.player_l_name";


			//echo $sql3;
		$query2 = $this->db->query($sql3);
		if(count($query2->result())>0)
		{	
			foreach ($query2->result() as $row2)
			{
				$myLeagues['SOG']= $row2->SOG;
				$myLeagues['GP']= $row2->GP;
				$myLeagues['Goals']= $row2->Goals;
				$myLeagues['Assists']= $row2->Assists;
				$myLeagues['Points']= $row2->Points;
				$myLeagues['PlusMinus']= $row2->PlusMinus;
				$myLeagues['PIM']= (empty($row2->PIM))?'0':$row2->PIM;
			}
		}
		else	{
				$myLeagues['SOG']= 0;
				$myLeagues['GP']= 0;
				$myLeagues['Goals']= 0;
				$myLeagues['Assists']= 0;
				$myLeagues['Points']= 0;
				$myLeagues['PlusMinus']= 0;
				$myLeagues['PIM']= 0;
			}
		$returnVal = $myLeagues;
		return $returnVal;
	}
	function getTeamShots($team=1000011, $odds='-120', $betSize=10, $numShots=33.5,$dates,$teamAgainst='0',$gameType,$location,$betType)
	{
		$sqlDates = $this->findDates($dates);
		$sqlGametype = $this->findGT($gameType);
		$sqlDates = $this->findDates($dates);
		$sqlGametype = $this->findGT($gameType);
		$sqlLocation = $this->findLOC($location);
		$SQLteamAgainst = $this->findTA($teamAgainst,'es','team_against_id');
		//print_r( $sqlStrength );

		$sql = "SELECT g.id AS GAME_ID,g.game_date, g.home_team_id AS HOME_TEAM_ID, g.away_team_id AS AWAY_TEAM_ID, SUM(sog) AS SHOTS_PER_GAME
				FROM new_game g
				INNER JOIN new_event_summary es ON es.game_id = g.id
				INNER JOIN new_team agteam ON agteam.team_id = es.team_against_id
				$sqlDates
				AND es.team_id = $team
				 $sqlGametype
				$sqlStrength
				$sqlPeriod
				$SQLteamAgainst
				$sqlLocation
				GROUP BY g.id";
		//echo $sql;
		$query = $this->db->query($sql);

		if(count($query->result())==0){
		$out= '0';
		}else{
			$total=0;
			//$arrShots = $this->parseData($query,'SHOTS_PER_GAME');
			foreach ($query->result() as $row){
				if($betType == 'toRisk'){
					//Fav win
					if( $odds < 0 && $row->SHOTS_PER_GAME > $numShots){
						$calc = 100/abs($odds)*$betSize;
						$arrData[$row->game_date] = round($calc,2);
					}
					if( $odds < 0 && $row->SHOTS_PER_GAME < $numShots){
						$calc = $betSize *-1;
						$arrData[$row->game_date] =round($calc,2);
					}
					if( $odds > 0 && $row->SHOTS_PER_GAME > $numShots){
						$calc = (abs($odds)/100)*$betSize;
						$arrData[$row->game_date] = round($calc,2);
					}
					if( $odds > 0 && $row->SHOTS_PER_GAME < $numShots){
						$calc =  $betSize *-1;
						$arrData[$row->game_date] = round($calc,2);
					}
				}
				if($betType == 'toWin'){

					//Fav win
					if( $odds < 0 && $row->SHOTS_PER_GAME > $numShots){
						$calc = $betSize;
						$arrData[$row->game_date] = round($calc,2);
					}
					if( $odds < 0 && $row->SHOTS_PER_GAME < $numShots){
						$calc = $betSize *(abs($odds)/100)*-1;
						$arrData[$row->game_date] = round($calc,2);
					}
					if( $odds > 0 && $row->SHOTS_PER_GAME > $numShots){
						$calc = $betSize;
						$arrData[$row->game_date] = round($calc,2);
					}
					if( $odds > 0 && $row->SHOTS_PER_GAME < $numShots){
						$calc = $betSize / (abs($odds)/100)*-1;
						$arrData[$row->game_date] = round($calc,2);
					}
				}
				//Sum up the values over time..

			}
			foreach ($arrData as $key => $value)
			{
				//$value = (empty($row->$field))?$lastValue:$row->$field;
				$total += $value;
				$arrData2[$key] =$total;	
				//$lastValue= $value;
			}
			$out='';
			foreach($arrData2 as $key=>$val){
				//$valueFinal = substr_replace($val ,"",-1);
				$out .= $key.";".$val."\n";
			}
		}
			//print_r($arrData);
			//print_r($arrData2);
		return $out;
	}
	function getPlayerGoals($playerID,$dates,$strength = 'EV-SH-PP', $period='f-s-t-ot-gt',$teamAgainst='0',$gameType,$location){
		$player_name = $playerID;
		//if($player_name =='P J Axelsson'
		$arrName = $this->verifyPlayer($player_name);
		$playerNHLID = $this->getPlayerNHLID($player_name);
		
		if(!$arrName['error']){
			$fname = $arrName['fname'];
			$last = $arrName['lname'];
		}
		else{
			$name_pieces = explode(" ", $player_name);
			if(count($name_pieces)>=3){
				$first = $name_pieces['0'];
				$last = $name_pieces['1'].' '.$name_pieces['2'];
			}else{
			$fname = substr($name_pieces['0'],0,2);
			$last = $name_pieces['1'];
			}
			$player_name = $fname.$last;
		}
		if (get_magic_quotes_gpc()) $fname = stripslashes($fname);
		$fname = mysql_escape_string($fname);
		if (get_magic_quotes_gpc()) $last = stripslashes($last);
		$last = mysql_escape_string($last);
		
		$sqlStrength =$this->findSTR($strength);
		$sqlDates = $this->findDates($dates);
		$sqlGametype = $this->findGT($gameType);
		$sqlLocation = $this->findLOC($location);
		$sqlPeriod = $this->findPeriod($period);
		$SQLteamAgainst = $this->findTA($teamAgainst);

		$sql = "SELECT g.id, g.game_date, COUNT(go.id) AS goals
				FROM (
					 SELECT *
					 FROM new_player p
					 WHERE nhl_id = $playerNHLID
					 ) as player
				INNER JOIN (
					SELECT es.*, pl.nhl_id 
					FROM new_event_summary es 
					INNER JOIN new_player pl ON es.player_id = pl.id AND pl.nhl_id = $playerNHLID	
				) es ON es.nhl_id = player.nhl_id
				INNER JOIN new_team agteam ON agteam.team_id = es.team_against_id
				INNER JOIN new_game g ON g.id = es.game_id
				LEFT JOIN new_goal go ON g.id = go.game_id AND go.player_id = player.id
				$sqlStrength
				$sqlPeriod
				$sqlDates
				$SQLteamAgainst
				$sqlLocation
				$sqlGametype 
				GROUP BY g.id";
		//echo $sql;
		$query = $this->db->query($sql);
		
        $start_date = $this->getStartDate($dates,$gameType);

		if(count($query->result())==0){
		    
		$arrGoals[$start_date] = '0';
		
		}else{
		$arrGoals = $this->parseData($query,'goals');
		}

		
		//$arrGoals1 = $this->genData($arrGoals,'goals');
		return $arrGoals;
	}
	function getTeamGoals($teamID,$dates,$strength = 'EV-SH-PP', $period='f-s-t-ot-gt',$teamAgainst='0',$gameType,$location){

		$sqlStrength =$this->findSTR($strength,'g');
		$sqlDates = $this->findDates($dates,'game');
		$sqlGametype = $this->findGT($gameType,'game');
		$sqlLocation = $this->findLOC($location);
		$sqlPeriod = $this->findPeriod($period,'g');
		$SQLteamAgainst = $this->findTA($teamAgainst,'es','team_against_id');

		$sql = "SELECT game.id, game.game_date, COUNT(g.id) AS goals, game.home_team_id, game.away_team_id,
			game.team_name
			FROM new_goal g
			INNER JOIN new_team t ON g.scoring_team_id = t.team_id AND t.team_id = $teamID
			LEFT JOIN new_event_summary es ON es.player_id = g.player_id AND es.game_id = g.game_id
			RIGHT JOIN (
			SELECT *
			FROM new_game game
			INNER JOIN new_team t ON (t.team_id = game.home_team_id OR t.team_id =
			game.away_team_id) AND t.team_id = $teamID AND game.game_date 
			) game ON g.game_id = game.id
			$sqlDates
			$sqlLocation
			$SQLteamAgainst
			$sqlGametype
			$sqlPeriod
			$sqlStrength
			GROUP BY game.id";
		//echo $sql;
		$query = $this->db->query($sql);
		
        $start_date = $this->getStartDate($dates,$gameType);

		if(count($query->result())==0){
		    
		$arrGoals[$start_date] = '0';
		
		}else{
		$arrGoals = $this->parseData($query,'goals');
		}

		
		//$arrGoals1 = $this->genData($arrGoals,'goals');
		return $arrGoals;
	}
	function getStartDate($arrSeason,$gameType){
	    $dates=explode('-',$arrSeason);
        switch($dates[0]){
            case '20132014':
                if($gameType == 'pl'){
                    $return = '2014-04-30';
                }
                else{
                    $return = '2013-10-01';
                }
                break;

			case '20122013':
                if($gameType == 'pl'){
                    $return = '2013-04-30';
                }
                else{
                    $return = '2012-10-01';
                }
                break;
            case '20112012':
                if($gameType == 'pl'){
                    $return = '2012-04-13';
                }
                else{
                    $return = '2011-10-07';
                }
                break;
            case '20102011':
                if($gameType == 'pl'){
                    $return = '2011-04-13';
                }
                else{
                    $return = '2010-10-07';
                }
                break;
            case '20092010':
                if($gameType == 'pl'){
                    $return = '2010-04-14';
                }
                else{
                    $return = '2009-10-01';
                }
                break;
            case '20082009':
                if($gameType == 'pl'){
                    $return = '2009-04-15';
                }
                else{
                    $return = '2008-10-04';
                }
                break;
            case '20072008':
                if($gameType == 'pl'){
                    $return = '2008-04-09';
                }
                else{
                    $return = '2007-09-29';
                }
                break;
        }
        return $return;
	}
	function findTA($ta,$prefix='agteam', $col = 'team_id'){
		if($ta=='-1'){
			$SQLteamAgainst = " AND $prefix.$col IN ('') ";
		}
		//Against team build (1000011-1000021)
		elseif ($ta!='0'){
			$SQLteamAgainst = " AND $prefix.$col IN (";
			//$str = explode('-',$strength);
			$ta = str_replace("-", ",", $ta);
			$SQLteamAgainst .= $ta.' ) ';
		}
		else $SQLteamAgainst ='';
		return $SQLteamAgainst;
	}
	function findSTR($strength,$prefix='go'){
		//strength build (EV-SH-PP)
		if($strength=='0'){
			$sqlStrength = " AND $prefix.goal_strength IN ('') ";
		}
		if ($strength!='EV-SH-PP'){
			$sqlStrength = " AND $prefix.goal_strength IN (";
			$str = explode('-',$strength);
			$strcount = count($str) -1;
			if($str[0]!='0'){
			for($k=0;$k<=$strcount;$k++){
			if($str[$k] =='EV'){
			
				$sqlStrength .= " 'EV' , 'EV-EN' ";
			}
			elseif($str[$k] =='PP'){
			
				if($k==1|$k==2) $sqlStrength .= ",";
				$sqlStrength .= " 'PP' , 'PP-EN' ";
			}
			elseif($str[$k] =='SH'){
				
				if($k==1|$k==2) $sqlStrength .= ",";
				$sqlStrength .= "  'SH' , 'SH-EN' ";
			}
			}
			}
			else $sqlStrength .= " '' ";
			$sqlStrength .= ' ) ';
		}
		else $sqlStrength ='';
		return $sqlStrength;
	}
	function findPeriod($period,$prefix='go'){
		//Period build (f-s-t-ot-gtot)
		if($period=='0'){
			$sqlPeriod = " AND $prefix.period IN ('') ";
		}
		elseif ($period!='f-s-t-ot'){
			$sqlPeriod = " AND $prefix.period IN (";
			//$str = explode('-',$strength);
			$str = str_replace("-", ",", $period);
			$str = str_replace("ot", "'OT'", $str);
		//	$str = str_replace("gt", "'OT2'", $str);
			$str = str_replace("f", "'1'", $str);
			$str = str_replace("s", "'2'", $str);
			$str = str_replace("t", "'3'", $str);
			$sqlPeriod .= $str.' ) ';
		}
		else $sqlPeriod =" AND $prefix.period NOT IN ('SO') ";
		return $sqlPeriod;
	}
	function findLOC($location,$prefix='go'){
		if($location=='0'){
			$sqlLocation = " AND es.home_away_indicator IN ('') ";
		}
		elseif ($location!='hm-aw'){
			$sqlLocation = ' AND es.home_away_indicator IN (';
			$str = str_replace("hm", "'H'", $location);
			$str = str_replace("aw", "'A'", $str);
			$sqlLocation .= $str.' ) ';
		}
		else $sqlLocation ='';

		return $sqlLocation;
	}
	function findGT($gameType,$prefix='g'){
		//gametype build (re-pl)

		if ($gameType=='0'){
			$sqlGametype = " AND $prefix.gametype IN ('') ";
		}
		elseif ($gameType!='re-pl'){
			$sqlGametype = " AND $prefix.gametype IN (";
			$gt = explode('-',$gameType);
			$strcount = count($gt) -1;
			for($k=0;$k<=$strcount;$k++){
			if($gt[$k] =='re'){
			
				$sqlGametype .= " '2'";
			}
			elseif($gt[$k] =='pl'){
				if($k==1|$k==2) $sqlGametype .= ",";
				$sqlGametype .= " '3'";
			}
			}
			$sqlGametype .= ' ) ';
		}
		else $sqlGametype ='';

		return $sqlGametype;
	}
	function findDates($dates = '20072008-20082009-20092010-20102011-2011-2012',$prefix = 'g', $f = 1){

		$dates = str_replace("-", " OR ", $dates);
		$dates = str_replace("20072008", " $prefix.game_date BETWEEN '2007-08-01' AND '2008-06-31' ", $dates);
		$dates = str_replace("20082009", " $prefix.game_date BETWEEN '2008-08-01' AND '2009-06-31' ", $dates);
		$dates = str_replace("20092010", " $prefix.game_date BETWEEN '2009-08-01' AND '2010-06-31' ", $dates);
		$dates = str_replace("20102011", " $prefix.game_date BETWEEN '2010-08-01' AND '2011-06-31' ", $dates);
		$dates = str_replace("20112012", " $prefix.game_date BETWEEN '2011-08-01' AND '2012-06-31' ", $dates);
		$dates = str_replace("20122013", " $prefix.game_date BETWEEN '2012-08-01' AND '2013-06-31' ", $dates);
		$dates = str_replace("20132014", " $prefix.game_date BETWEEN '2013-08-01' AND '2014-06-31' ", $dates);
		if($f){
			return 'WHERE ( '.$dates.' )';
		}
		else
			return 'IN ( '.$dates.' )';
	}
	function getPlayerES($playerID,$dates,$teamAgainst='0',$gameType,$esStat,$location){
		$player_name = $playerID;
		//if($player_name =='P J Axelsson'
		$arrName = $this->verifyPlayer($player_name);
		$playerNHLID = $this->getPlayerNHLID($player_name);
		if(!$arrName['error']){
			$fname = $arrName['fname'];
			$last = $arrName['lname'];
		}
		else{
			$name_pieces = explode(" ", $player_name);
			if(count($name_pieces)>=3){
				$first = $name_pieces['0'];
				$last = $name_pieces['1'].' '.$name_pieces['2'];
			}else{
			$fname = substr($name_pieces['0'],0,2);
			$last = $name_pieces['1'];
			}
			$player_name = $fname.$last;
		}
		if (get_magic_quotes_gpc()) $fname = stripslashes($fname);
		$fname = mysql_escape_string($fname);
		if (get_magic_quotes_gpc()) $last = stripslashes($last);
		$last = mysql_escape_string($last);


		$sqlDates = $this->findDates($dates);
		$sqlGametype = $this->findGT($gameType);
		$sqlLocation = $this->findLOC($location);
		$SQLteamAgainst = $this->findTA($teamAgainst,'t');

		switch($esStat) {
			case 'pm':
				$sqlES = "plus_minus";
				break;
			case 'sog':
				$sqlES = "sog";
				break;
			case 'ab':
				$sqlES = "attempts_blocked";
				break;
			case 'ms':
				$sqlES = "missed_shots";
				break;
			case 'hg':
				$sqlES = "hits_given";
				break;
			case 'gv':
				$sqlES = "giveaways";
				break;
			case 'tk':
				$sqlES = "takeaways";
				break;
			case 'bs':
				$sqlES = "blocked_shots";
				break;
			case 'fw':
				$sqlES = "faceoffs_won";
				break;
			case 'fl':
				$sqlES = "faceoffs_lost";
				break;
			case 'np':
				$sqlES = "number_of_penalities";
				break;
			case 'ns':
				$sqlES = "num_shifts";
				break;
			case 'sp':
				$sqlES = "goals as returnStat2, sog";
				break;
			case 'fp':
				$sqlES = "faceoffs_won as returnStat2, faceoffs_lost+faceoffs_won ";
				break;
		}
		$sql = "SELECT player_id, game_date, $sqlES as returnStat
				FROM (
					 SELECT p.id
					 FROM new_player p 
					 WHERE nhl_id = $playerNHLID
                    ) as player
				INNER JOIN new_event_summary es ON es.player_id = player.id
				INNER JOIN new_game g ON es.game_id = g.id $sqlGametype 
				INNER JOIN new_team t ON es.team_against_id = t.team_id

				$sqlDates
				$SQLteamAgainst
				$sqlLocation
				GROUP BY g.id";
		//echo $sql;
		$query = $this->db->query($sql);

        $start_date = $this->getStartDate($dates,$gameType);

		if(count($query->result())==0){
		    
		$arrGoals[$start_date] = '0';
		
		}else{
			if($esStat =='sp' || $esStat =='fp'){
				$arrGoals = $this->parseDataSP($query);
			}
			else
				$arrGoals = $this->parseData($query,'returnStat');
		}
		//$arrGoals1 = $this->genData($arrGoals,'goals');
		//print_r($arrGoals);
		return $arrGoals;
	}
	function getTeamES($teamID,$dates,$teamAgainst='0',$gameType,$esStat,$location){

		$sqlDates = $this->findDates($dates,'game');
		$sqlGametype = $this->findGT($gameType,'game');
		$sqlLocation = $this->findLOC($location);
		$SQLteamAgainst = $this->findTA($teamAgainst,'es', 'team_against_id');

		switch($esStat) {
			case 'pm':
				$sqlES = "plus_minus";
				break;
			case 'sog':
				$sqlES = "sog";
				break;
			case 'ab':
				$sqlES = "attempts_blocked";
				break;
			case 'ms':
				$sqlES = "missed_shots";
				break;
			case 'hg':
				$sqlES = "hits_given";
				break;
			case 'gv':
				$sqlES = "giveaways";
				break;
			case 'tk':
				$sqlES = "takeaways";
				break;
			case 'bs':
				$sqlES = "blocked_shots";
				break;
			case 'fw':
				$sqlES = "faceoffs_won";
				break;
			case 'fl':
				$sqlES = "faceoffs_lost";
				break;
			case 'np':
				$sqlES = "number_of_penalities";
				break;
			case 'ns':
				$sqlES = "num_shifts";
				break;
			case 'sp':
				$sqlES = "SUM(goals) as returnStat2,SUM(sog) as returnStat ";
				break;
			case 'fp':
				$sqlES = "SUM(faceoffs_won) as returnStat2, SUM(faceoffs_lost+faceoffs_won) as returnStat";
				break;
		}
		if($esStat !='sp' && $esStat !='fp'){
			$sqlR = "SUM($sqlES) AS returnStat";
		}
		else{
			$sqlR = $sqlES;
		}

		$sql = "SELECT game.id, game.game_date, $sqlR ,game.home_team_id,
				game.away_team_id, game.team_name
				FROM new_event_summary es
				INNER JOIN new_team t ON es.team_id = t.team_id AND t.team_id = $teamID  
				INNER JOIN (
				SELECT *
				FROM new_game game
				INNER JOIN new_team t ON (t.team_id = game.home_team_id OR t.team_id =
				game.away_team_id) AND t.team_id = $teamID AND game.game_date
				) game ON es.game_id = game.id
				$sqlDates
				$sqlLocation
				$SQLteamAgainst
				$sqlGametype
				GROUP BY game.id";
		//echo $sql;
		$query = $this->db->query($sql);

        $start_date = $this->getStartDate($dates,$gameType);

		if(count($query->result())==0){
		    
		$arrGoals[$start_date] = '0';
		
		}else{
			if($esStat =='sp' || $esStat =='fp'){
				$arrGoals = $this->parseDataSP($query);
			}
			else
				$arrGoals = $this->parseData($query,'returnStat');
		}
		//$arrGoals1 = $this->genData($arrGoals,'goals');
		//print_r($arrGoals);
		return $arrGoals;
	}
	function getPlayerAssists($playerID,$dates,$strength = 'EV-SH-PP', $period='f-s-t-ot-gt',$teamAgainst='0',$gameType,$location){
		$player_name = $playerID;
		$arrName = $this->verifyPlayer($player_name);
		$playerNHLID = $this->getPlayerNHLID($player_name);
		if(!$arrName['error']){
			$fname = $arrName['fname'];
			$last = $arrName['lname'];
		}
		else{
			$name_pieces = explode(" ", $player_name);
			if(count($name_pieces)>=3){
				$first = $name_pieces['0'];
				$last = $name_pieces['1'].' '.$name_pieces['2'];
			}else{
			$fname = substr($name_pieces['0'],0,2);
			$last = $name_pieces['1'];
			}
			$player_name = $fname.$last;
		}

		if (get_magic_quotes_gpc()) $fname = stripslashes($fname);
		$fname = mysql_escape_string($fname);
		if (get_magic_quotes_gpc()) $last = stripslashes($last);
		$last = mysql_escape_string($last);
		$sqlStrength =$this->findSTR($strength);
		$sqlDates = $this->findDates($dates);
		$sqlLocation = $this->findLOC($location);
		$sqlGametype = $this->findGT($gameType);
		$sqlPeriod = $this->findPeriod($period);
		$SQLteamAgainst = $this->findTA($teamAgainst);


		$sql = "
			SELECT g.id, g.game_date, COUNT(assists.id) AS assists
			FROM (
					 SELECT *
					 FROM new_player p
					 WHERE nhl_id = $playerNHLID) as player
			INNER JOIN new_event_summary es ON es.player_id = player.id
			INNER JOIN new_team agteam ON es.team_against_id = agteam.team_id
			LEFT JOIN new_game g ON g.id = es.game_id $sqlGametype
			LEFT JOIN (
					 SELECT player2.id as player_id2, a.*, go.game_id
					 FROM (
								SELECT *
								FROM new_player p2
								WHERE nhl_id = $playerNHLID) as player2
					 INNER JOIN new_assist a ON a.player_id = player2.id
					 INNER JOIN new_goal go ON go.id = a.goal
					$sqlStrength
					$sqlPeriod
			) as assists ON g.id = assists.game_id
			$sqlDates
			$SQLteamAgainst
			$sqlLocation
			GROUP BY g.id";
		//echo $sql;
		$query = $this->db->query($sql);
        $start_date = $this->getStartDate($dates,$gameType);

		if(count($query->result())==0){
		    
		$arrAssist[$start_date] = '0';
		
		}else{
		$arrAssist = $this->parseData($query,'assists');
		//print_r($arrAssist); 
		}

		return $arrAssist;
	}
	function getTeamAssists($teamID,$dates,$strength = 'EV-SH-PP', $period='f-s-t-ot-gt',$teamAgainst='0',$gameType,$location){

		$sqlStrength =$this->findSTR($strength,'g');
		$sqlDates = $this->findDates($dates,'game');
		$sqlGametype = $this->findGT($gameType,'game');
		$sqlLocation = $this->findLOC($location);
		$sqlPeriod = $this->findPeriod($period,'g');
		$SQLteamAgainst = $this->findTA($teamAgainst,'es','team_against_id');

		$sql = "
			SELECT game.id, game.game_date, COUNT(a.id) as assists, game.home_team_id, game.away_team_id,
			game.team_name
			FROM new_assist a
			INNER JOIN new_goal g ON a.goal = g.id
			LEFT JOIN new_event_summary es ON es.player_id = g.player_id AND es.game_id = g.game_id
			INNER JOIN new_team t ON g.scoring_team_id = t.team_id AND t.team_id = $teamID
			RIGHT JOIN (
			SELECT *
			FROM new_game game
			INNER JOIN new_team t ON (t.team_id = game.home_team_id OR t.team_id =
			game.away_team_id) AND t.team_id = $teamID AND game.game_date
			) game ON g.game_id = game.id
			$sqlDates
			$sqlLocation
			$SQLteamAgainst
			$sqlGametype
			$sqlPeriod
			$sqlStrength
			GROUP BY game.id
		";
		//echo $sql;
		$query = $this->db->query($sql);
        $start_date = $this->getStartDate($dates,$gameType);

		if(count($query->result())==0){
		    
		$arrAssist[$start_date] = '0';
		
		}else{
		$arrAssist = $this->parseData($query,'assists');
		//print_r($arrAssist); 
		}

		return $arrAssist;
	}
	function getPlayerPoints($playerID,$dates,$strength = 'EV-SH-PP', $period='f-s-t-ot-gt',$teamAgainst='0',$gameType,$location){
		$player_name = $playerID;
		$arrName = $this->verifyPlayer($player_name);
		$playerNHLID = $this->getPlayerNHLID($player_name);
		if(!$arrName['error']){
			$fname = $arrName['fname'];
			$last = $arrName['lname'];
		}
		else{
			$name_pieces = explode(" ", $player_name);
			if(count($name_pieces)>=3){
				$first = $name_pieces['0'];
				$last = $name_pieces['1'].' '.$name_pieces['2'];
			}else{
			$fname = substr($name_pieces['0'],0,2);
			$last = $name_pieces['1'];
			}
			$player_name = $fname.$last;
		}
		if (get_magic_quotes_gpc()) $fname = stripslashes($fname);
		$fname = mysql_escape_string($fname);
		if (get_magic_quotes_gpc()) $last = stripslashes($last);
		$last = mysql_escape_string($last);

		$sqlStrength =$this->findSTR($strength,'pts');
		$sqlDates = $this->findDates($dates);
		$sqlLocation = $this->findLOC($location);
		$sqlGametype = $this->findGT($gameType);
		$sqlPeriod = $this->findPeriod($period,'pts');
		$SQLteamAgainst = $this->findTA($teamAgainst);

		$sql = "
			SELECT g.id, g.game_date, COUNT(pts.id) AS points
			FROM (
					 SELECT *
					 FROM new_player p
					 WHERE nhl_id = $playerNHLID) as player
				INNER JOIN (
					SELECT es.*, pl.nhl_id 
					FROM new_event_summary es 
					INNER JOIN new_player pl ON es.player_id = pl.id AND pl.nhl_id = $playerNHLID	
				) es ON es.nhl_id = player.nhl_id
			INNER JOIN new_team agteam ON agteam.team_id = es.team_against_id
			INNER JOIN new_game g ON g.id = es.game_id $sqlGametype
			LEFT JOIN (
			SELECT go.id, go.game_goal_number, go.period, go.time, go.goal_strength, go.game_id, go.team_against_id, a.player_id, go.scoring_team_id
			FROM (
					 SELECT *
						   FROM new_player p
						   WHERE nhl_id = $playerNHLID) as player
						   INNER JOIN new_assist a ON a.player_id = player.id
						   INNER JOIN new_goal go ON go.id = a.goal
			UNION ALL
			SELECT go.id, go.game_goal_number, go.period, go.time, go.goal_strength, go.game_id, go.team_against_id, go.player_id, go.scoring_team_id
			FROM (
						   SELECT *
						   FROM new_player p
						   WHERE nhl_id = $playerNHLID
				) as player2
			INNER JOIN new_goal go ON go.player_id = player2.id) as pts ON g.id = pts.game_id AND pts.player_id = player.id
			$sqlStrength
			$sqlPeriod
			$sqlDates
			$SQLteamAgainst
			$sqlLocation 
			GROUP BY g.id";
				//	echo $sql;
		$query = $this->db->query($sql);

        $start_date = $this->getStartDate($dates,$gameType);

		if(count($query->result())==0){
		    
		$arrPoints[$start_date] = '0';
		
		}else{
		$arrPoints = $this->parseData($query,'points');
		}
		return $arrPoints;
	}
	function getTeamPoints($teamID,$dates,$strength = 'EV-SH-PP', $period='f-s-t-ot-gt',$teamAgainst='0',$gameType,$location){
		
		$sqlStrength =$this->findSTR($strength,'g');
		$sqlDates = $this->findDates($dates,'game');
		$sqlGametype = $this->findGT($gameType,'game');
		$sqlLocation = $this->findLOC($location);
		$sqlPeriod = $this->findPeriod($period,'g');
		$SQLteamAgainst = $this->findTA($teamAgainst,'es','team_against_id');

		$sql = "
			SELECT id, game_date, SUM(points) AS points, home_team_id, away_team_id, team_name
			FROM(
			SELECT game.id, game.game_date, COUNT(g.id) AS points, game.home_team_id, game.away_team_id, game.team_name
			FROM new_goal g
			INNER JOIN new_team t ON g.scoring_team_id = t.team_id AND t.team_id =$teamID
			LEFT JOIN new_event_summary es ON es.player_id = g.player_id AND es.game_id = g.game_id 
			RIGHT JOIN (
			SELECT *
			FROM new_game game
			INNER JOIN new_team t ON (t.team_id = game.home_team_id OR t.team_id = game.away_team_id) AND
			t.team_id = $teamID
			) game ON g.game_id = game.id
			$sqlDates
			$sqlLocation
			$SQLteamAgainst
			$sqlGametype
			$sqlPeriod
			$sqlStrength
			GROUP BY game.id

			UNION ALL

			SELECT game.id, game.game_date, COUNT(a.id), game.home_team_id, game.away_team_id, game.team_name
			FROM new_assist a
			INNER JOIN new_goal g ON a.goal = g.id
			LEFT JOIN new_event_summary es ON es.player_id = g.player_id AND es.game_id = g.game_id
			INNER JOIN new_team t ON g.scoring_team_id = t.team_id AND t.team_id = $teamID
			RIGHT JOIN (
			SELECT *
			FROM new_game game
			INNER JOIN new_team t ON (t.team_id = game.home_team_id OR t.team_id = game.away_team_id) AND
			t.team_id = $teamID
			) game ON g.game_id = game.id
			$sqlDates
			$sqlLocation
			$SQLteamAgainst
			$sqlGametype
			$sqlPeriod
			$sqlStrength
			GROUP BY game.id
			) AS goalsandassists

			GROUP BY id";
		//	echo $sql;
		$query = $this->db->query($sql);

        $start_date = $this->getStartDate($dates,$gameType);

		if(count($query->result())==0){
		    
		$arrPoints[$start_date] = '0';
		
		}else{
		$arrPoints = $this->parseData($query,'points');
		}
		return $arrPoints;
	}
	function getPlayerPims($playerID,$dates,$strength = 'EV-SH-PP', $period='f-s-t-ot-gt',$teamAgainst='0',$gameType,$penalities,$location){
		$player_name = $playerID;
		$arrName = $this->verifyPlayer($player_name);
		$playerNHLID = $this->getPlayerNHLID($player_name);
		if(!$arrName['error']){
			$fname = $arrName['fname'];
			$last = $arrName['lname'];
		}
		else{
			$name_pieces = explode(" ", $player_name);
			if(count($name_pieces)>=3){
				$first = $name_pieces['0'];
				$last = $name_pieces['1'].' '.$name_pieces['2'];
			}else{
			$fname = substr($name_pieces['0'],0,2);
			$last = $name_pieces['1'];
			}
			$player_name = $fname.$last;
		}

		if (get_magic_quotes_gpc()) $fname = stripslashes($fname);
		$fname = mysql_escape_string($fname);
		if (get_magic_quotes_gpc()) $last = stripslashes($last);
		$last = mysql_escape_string($last);
		$sqlStrength =$this->findSTR($strength);
		$sqlDates = $this->findDates($dates);
		$sqlGametype = $this->findGT($gameType);
		$sqlLocation = $this->findLOC($location);
		$sqlPeriod = $this->findPeriod($period,'penalties');
		$SQLteamAgainst = $this->findTA($teamAgainst);

		//penality  build (01-67)
		if ($penalities!='0'){
			$SQLteamPenalty = ' AND type.id IN (';
			//$str = explode('-',$strength);
			$penalities = str_replace("-", ",", $penalities);
			$SQLteamPenalty .= $penalities.' ) ';
		}
		else $SQLteamPenalty ='';
		
		$sql = "
		SELECT g.id, g.game_date, sum(penalties.pim) AS PIMS
		FROM (
				 SELECT *
				 FROM new_player p
				 WHERE nhl_id = $playerNHLID) as player
		INNER JOIN new_event_summary es ON es.player_id = player.id
		INNER JOIN new_team agteam ON agteam.team_id = es.team_against_id
		INNER JOIN new_game g ON g.id = es.game_id $sqlGametype
		LEFT JOIN (
				 SELECT player2.id as player_id2, pen.*
				 FROM (
						   SELECT *
						   FROM new_player p2
						   WHERE nhl_id = $playerNHLID) as player2
				 INNER JOIN new_penalty pen ON pen.player_id = player2.id 
				 INNER JOIN new_penalty_type as type ON type.id = pen.penalty_id
				 $SQLteamPenalty 
		) penalties ON g.id = penalties.game_id
		$sqlPeriod
		$sqlDates
		$SQLteamAgainst
		$sqlLocation
		GROUP BY g.id";
					//echo $sql;
		$query = $this->db->query($sql);

        $start_date = $this->getStartDate($dates,$gameType);

		if(count($query->result())==0){
		    
		$arrPIM[$start_date] = '0';
		
		}else{
		$arrPIM = $this->parseData($query,'PIMS');
		}
		return $arrPIM;
	}
	function getTeamPims($teamID,$dates,$strength = 'EV-SH-PP', $period='f-s-t-ot-gt',$teamAgainst='0',$gameType,$penalities,$location){
		
		$sqlStrength =$this->findSTR($strength,'g');
		$sqlDates = $this->findDates($dates,'game');
		$sqlGametype = $this->findGT($gameType);
		$sqlLocation = $this->findLOC($location);
		$sqlPeriod = $this->findPeriod($period,'p');
		$SQLteamAgainst = $this->findTA($teamAgainst,'es','team_against_id');

		//penality  build (01-67)
		if ($penalities!='0'){
			$SQLteamPenalty = 'AND type.id IN (';
			//$str = explode('-',$strength);
			$penalities = str_replace("-", ",", $penalities);
			$SQLteamPenalty .= $penalities.' ) ';
		}
		else $SQLteamPenalty ='';

		$sql = "
			SELECT game.id, game.game_date, SUM(p.pim) AS pims, game.home_team_id, game.away_team_id, game.team_name
			FROM new_penalty p
			INNER JOIN new_team t ON p.team_for_id = t.team_id AND t.team_id = $teamID 
			LEFT JOIN new_event_summary es ON es.player_id = p.player_id AND es.game_id = p.game_id 
			RIGHT JOIN (
			SELECT *
			FROM new_game game
			INNER JOIN new_team t ON (t.team_id = game.home_team_id OR t.team_id = game.away_team_id) AND t.team_id = $teamID
			AND game.game_date 
			) game ON p.game_id = game.id
			INNER JOIN new_penalty_type as type ON type.id = p.penalty_id
			$sqlDates
			$sqlPeriod
			$sqlLocation
			$SQLteamAgainst
			$SQLteamPenalty
			GROUP BY game.id
		";
					//echo $sql;
		$query = $this->db->query($sql);

        $start_date = $this->getStartDate($dates,$gameType);

		if(count($query->result())==0){
		    
		$arrPIM[$start_date] = '0';
		
		}else{
		$arrPIM = $this->parseData($query,'pims');
		}
		return $arrPIM;
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
	function parseData($query, $field){
		$lastValue = '0';
		
		if(count($query->result())>0){
		foreach ($query->result() as $row)
		{
			//print_r($row);
			//$value = (empty($row->$field))?$lastValue:$row->$field;
			//$total += $value;
			//  $out .= $row['game_date1'].";".$goal."\n";
			$arrData[$row->game_date] = $row->$field;
			//$lastValue= $value;
		}
			//print_r( $arrData);
		$total=0;

		foreach ($arrData as $key => $value)
		{
			//$value = (empty($row->$field))?$lastValue:$row->$field;
			$total += $value;
			$arrData2[$key] =$total;	
			//$lastValue= $value;
		}
		//print_r($arrData2);
		return $arrData2;
		}
	}
	function parseDataSP($query){
		$lastValue = '0';
		
		if(count($query->result())>0){
		foreach ($query->result() as $row)
		{
			//$value = (empty($row->$field))?$lastValue:$row->$field;
			//$total += $value;
			//  $out .= $row['game_date1'].";".$goal."\n";
			$arrData[$row->game_date] = array($row->returnStat2, $row->returnStat);
			//$lastValue= $value;
		}
			//print_r( $arrData);
		$totalShots=0;
		$totalGoals=0;
		$cumAvg = 0;
		foreach ($arrData as $key => $value)
		{
			//$value = (empty($row->$field))?$lastValue:$row->$field;
			$totalShots += $value[1];
			$totalGoals += $value[0];
			$cumAvg = round($totalGoals/$totalShots,3)*100;
			$arrData2[$key] =$cumAvg; 
			//$lastValue= $value;
		}
		//print_r($arrData2);
		return $arrData2;
		}
	}

	function genData($query, $field){
		$lastValue = '0';
		foreach ($query as $row)
		{
			$value = (empty($row->$field))?$lastValue:$row->$field;
			//  $out .= $row['game_date1'].";".$goal."\n";
			$arrData[$row->game_date1] = $value;
			$lastValue= $value;
		}
		return $arrData;
	}
	function genCSV($arr, $arr2){
		$out ='';
		$arrData1 = 0;
		$arrData2=0;
		$result = array_merge($arr, $arr2);
		$arrDates = array_keys($result);
		sort($arrDates);
		//print_r( $arr2);

		foreach( $arrDates as $date ){
			//$pts = $arr[$date]+$arr2[$date];
			if (array_key_exists($date, $arr)) {
				$arrData1=$arr[$date];
			}
			if (array_key_exists($date, $arr2)) {
				$arrData2=$arr2[$date];
			}
			$out .= $date.";".$arrData1.";".$arrData2."\n";
			}
			return $out;
	}
	function genCSV_multi($arr, $arr2, $arr3){
		$out ='';
		$arrData1 = 0;
		$arrData2=0;
		$arrData3=0;

		$result = array_merge($arr, $arr2, $arr3);
		$arrDates = array_keys($result);
		sort($arrDates);
		//print_r( $arr2);

		foreach( $arrDates as $date ){
			//$pts = $arr[$date]+$arr2[$date];
			if (array_key_exists($date, $arr)) {
				$arrData1=$arr[$date];
			}
			if (array_key_exists($date, $arr2)) {
				$arrData2=$arr2[$date];
			}
			if (array_key_exists($date, $arr3)) {
				$arrData3=$arr3[$date];
			}
			$out .= $date.";".$arrData1.";".$arrData2.";".$arrData3."\n";
			}
			return $out;
	}
	function genCSVsolo($arr){
		$out ='';
		$arrData1 = 0;
		if(count($arr)>0){
		$arrDates = array_keys($arr);
		sort($arrDates);
		//print_r( $arr2);

		foreach( $arrDates as $date ){
			//$pts = $arr[$date]+$arr2[$date];
			if (array_key_exists($date, $arr)) {
				$arrData1=$arr[$date];
			}
			$out .= $date.";".$arrData1."\n";
			}
			return $out;
		}
	}
	function genCSVRecursive_Old($arr){
		$out ='';
		$arrData1 = 0;
		$arrDates = $this->createDateRangeArray($start, $end);
		
		foreach ($arr as $arrList){
			$arrData1 ='';
			foreach ($arrDates as $date){
				if (array_key_exists($date, $arrList)) {
					$arrData1=$arrList[$date];
					$tmp[$date] .= $arrData1.";";
				}
				else $tmp[$date] .= "$arrData1;";
			}
		}
		ksort($tmp);
		//cleanup
		foreach($tmp as $key=>$val){
			$valueFinal = substr_replace($val ,"",-1);
			$out .= $key.";".$valueFinal."\n";
		}
		//print_r( $out);
		return $out;
	}
	function genCSVRecursiveCategory($arr,$f=0){
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
		    $arrLines[0][] =$date;
	    }
	    
		foreach ($arr as $series){
			foreach ($series as $key => $value){
                $key = array_search($key, $arrDates);
                $arrLines[$x][] = "[$key,$value]";
			}
			$x++;
		}
		
		$output='';
		foreach($arrLines as $line){
		    $output[] = implode(";",$line);
		}
		$output2 = implode("\n",$output);
		//print_r($arrLines);
		//return json_encode($arrLines);
		return $output2;
	}

	function genCSVRecursive($arr,$f=0){
		$out ='';
		$arrData1 = 0;
		$arrDates = array();
		//print_r($arr);
		//step one, merge all arrays to create datelist
		foreach ($arr as $arrList){
		$arrDates = array_merge($arrDates,$arrList);
		}

		ksort($arrDates);
		
		$arrDates = array_keys($arrDates);
		foreach($arrDates as $tmpDate){
			$tmpDate2 = explode("-",$tmpDate);
			//print_r($tmpDate);
			$arrDates2[] = "'$tmpDate2[0] , $tmpDate2[1]-1 , $tmpDate2[2]'";
		}
		
		//print_r( $arr);
		foreach ($arr as $arrList){
			$arrData1 ='';
					
			foreach ($arrDates as $date){

				if (array_key_exists($date, $arrList)) {
				//	echo 'date: ' . $date . ' | val: '.$arrList[$date]."\n";
					$arrData1=$arrList[$date];
					$tmp[$date] .= $arrData1.";";
					//print_r($arrList);
				}
				else {$tmp[$date] .= "$arrData1;";
				//echo 'date:'.$date.' | val: '.$arrData1."\n";
				}

			}
		}


		ksort($tmp);
		//cleanup
		if($f) return end($tmp);
		foreach($tmp as $key=>$val){
			$valueFinal = substr_replace($val ,"",-1);
			$out .= $key.";".$valueFinal."\n";
		}
		
		return $out;
	}
	function genCSVRecursiveHigh2($arr,$f=0,$arrPlayers){
		$out ='';
		$arrData1 = 0;
		//print_r($arr);
		//One array of dates, one of each player. 
		//$arrDatesStart = array(0 => 'Categories');
		$arrDates = array();
		//step one, merge all arrays to create datelist
		foreach ($arr as $arrList){
		$arrDates = array_merge($arrDates,$arrList);
		}
				
		ksort($arrDates);
		$arrDates = array_keys($arrDates);
		//$arrDates = array_merge($arrDatesStart,$arrDates);
		foreach($arrDates as $tmpDate){
			$dateex = explode("-",$tmpDate);
			$jstime[] = "Date.UTC($dateex[0] , $dateex[1]-1 , $dateex[2])";
		}
		//$csvDates .= "'Categories';";
		$csvDates .= implode(';',$arrDates);
		
		//print_r( $arr);
		$x=0;
		foreach ($arr as $arrList){
			$arrData1 ='';
			
			foreach ($arrDates as $date){

				if (array_key_exists($date, $arrList)) {
				//	echo 'date: ' . $date . ' | val: '.$arrList[$date]."\n";
					$arrData1=$arrList[$date];
					$tmp[$date] .= $arrData1.";";
					$playerData[$x][]=$arrData1;
					//print_r($arrList);
				}
				else {
					$tmp[$date] .= "$arrData1;";
					if($arrData1>0)
					$playerData[$x][]=$arrData1;
					else $playerData[$x][]='0';
				}
			}
			$csvDates .= "\n"."'".$arrPlayers[$x]."';".implode(';',$playerData[$x]);
			$x++;
		}
		return $csvDates;
	}
	function genCSVRecursiveHigh($arr,$f=0,$arrPlayers){
		$x=0;
		$csvDates ='';
		$arrLines = array();
		//1 - foreach player, make a series in this format  [Date.UTC(2010, 0, 1), 29.9],[Date.UTC(2010, 2, 1), 71.5]....

		foreach ($arr as $series){
			
			foreach ($series as $key => $value){
				$date = explode('-',$key);
				$ts = strtotime($key)*1000;
				$tmp = array();
				//$tmp[0] = 'Date.UTC('.$date[0].','.$date[1].','.$date[2].')';
				$tmp[0] = $ts;
				$tmp[1] = $value;
				$arrLines[$x][] = $tmp;
				unset($tmp);
			}
			$x++;
		}
		//print_r($arrLines);
		return json_encode($arrLines);
	}
	function createDateRangeArray($strDateFrom,$strDateTo) {
	  // takes two dates formatted as YYYY-MM-DD and creates an
	  // inclusive array of the dates between the from and to dates.

	  // could test validity of dates here but I'm already doing
	  // that in the main script

	  $aryRange=array();

	  $iDateFrom=mktime(1,0,0,substr($strDateFrom,5,2),     substr($strDateFrom,8,2),substr($strDateFrom,0,4));
	  $iDateTo=mktime(1,0,0,substr($strDateTo,5,2),     substr($strDateTo,8,2),substr($strDateTo,0,4));

	  if ($iDateTo>=$iDateFrom) {
		array_push($aryRange,date('Y-m-d',$iDateFrom)); // first entry

		while ($iDateFrom<$iDateTo) {
		  $iDateFrom+=86400; // add 24 hours
		  array_push($aryRange,date('Y-m-d',$iDateFrom));
		}
	  }
	  return $aryRange;
	}
	function _gameData($player_name,$date){
		$a = array(
		'Washington Capitals' => 'capitals',
		'Pittsburgh Penguins' => 'penguins',
		'Tampa Bay Lightning' => 'lightning',
		'Ottawa Senators' => 'senators',
		'New York Rangers' => 'rangers',
		'Detroit Red Wings' => 'redwings',
		'Toronto Maple Leafs' => 'mapleleafs',
		'Colorado Avalanche' => 'avalanche',
		'Boston Bruins' => 'bruins',
		'Calgary Flames' => 'flames',
		'Vancouver Canucks' => 'canucks',
		'San Jose Sharks' => 'sharks',
		'Anaheim Mighty Ducks' => 'mightyducks',
		'New Jersey Devils' => 'devils',
		'New York Islanders' => 'islanders',
		'New York Rangers' => 'rangers',
		'Chicago Blackhawks' => 'blackhawks',
		'Carolina Hurricanes' => 'hurricanes',
		'Florida Panthers' => 'panthers',
		'Buffalo Sabres' => 'sabres',
		'Canadiens Montreal' => 'canadiens',
		'Atlanta Thrashers' => 'thrashers',
		'St.Louis Blues' => 'blues',
		'Nashville Predators' => 'predators',
		'Dallas Stars' => 'stars',
		'Columbus Blue Jackets' => 'bluejackets',
		'Philadelphia Flyers' => 'flyers',
		'Minnesota Wild' => 'wild',
		'Phoenix Coyotes' => 'coyotes',
		'Los Angeles Kings'=>'kings'
		);

		$sql1 = "SELECT fullname
		FROM nhl_players
		WHERE id = '$player_name'";
		$query = $this->db->query($sql1);
		foreach ($query->result() as $row)
			{
			$fullname = $row->fullname;
			}
		$name_pieces = explode(" ", $fullname);
		$first = substr($name_pieces['0'], 0, 1).'.';
		$last = $name_pieces['1'];
		$player_name2 = $first.$last;
		$sql2 = "SELECT *
		FROM games g, stats s
		WHERE s.game_id = g.game_id
		AND g.db_date = '$date'
		AND s.name = '$fullname'";
		//echo $sql2;
		$query2 = $this->db->query($sql2);
		if(count($query2->result())>0)
		{	//echo $sql;
			foreach ($query2->result() as $row2)
			{
			$gameData['home_team'] = $row2->home_team;
			$gameData['home_goals'] = $row2->home_goals;
			$gameData['away_team']  = $row2->away_team;
			$gameData['away_goals'] = $row2->away_goals;
			$gameData['game_date'] = $row2->game_date;
			$gameData['plus_minus'] = $row2->plus_minus;
			$gameData['num_of_shifts']  = $row2->number_of_shifts;
			$gameData['toi'] = $row2->time_on_ice;
			$gameData['shots_total'] = $row2->total_shots;
			$gameData['PIM']  = $row2->pim;
			$gameData['toi'] = $row2->time_on_ice;
			$gameData['hits'] = $row2->hits;
			$gameData['takeaways'] = $row2->takeaways;
			$gameData['giveaways'] = $row2->giveaways;
			$gameData['blocked_shots'] = $row2->blocked_shots;
			$gameData['error'] ='0';
			$gameData['home_img'] =$a[$gameData['home_team']];
			$gameData['away_img'] =$a[$gameData['away_team']];
			}
		}
		else
		{
			$gameData['error'] = '1';
		}
		$json_value = json_encode($gameData);
		echo $json_value;
	}
	function getPlayerList($player_name,$date){
		$value = '';
		$pieces = explode(' ',$player_name);

		if(count($pieces)==1){
						if (get_magic_quotes_gpc()) $player_name = stripslashes($player_name);
		$player_name = mysql_escape_string($player_name);

		$sql2 = "SELECT DISTINCT player_f_name,	player_l_name FROM new_player WHERE player_f_name LIKE '%$player_name%' OR player_l_name LIKE '%$player_name%' LIMIT 5";


		}
		
		elseif(count($pieces)==2){
		$first = $pieces[0];
		$last = $pieces[1];
				if (get_magic_quotes_gpc()) $first = stripslashes($first);
		$first = mysql_escape_string($first);
				if (get_magic_quotes_gpc()) $last = stripslashes($last);
		$last = mysql_escape_string($last);

		$sql2 = "SELECT DISTINCT player_f_name,	player_l_name FROM new_player WHERE player_f_name LIKE '%$first%' AND player_l_name LIKE '%$last%' LIMIT 5";
		}
		elseif(count($pieces)>2){
			if (get_magic_quotes_gpc()) $player_name = stripslashes($player_name);
			$player_name = mysql_escape_string($player_name);
			$sql2 = "SELECT DISTINCT player_f_name,	player_l_name FROM new_player WHERE full_name LIKE '%$player_name%' LIMIT 5";
		}
		$query2 = $this->db->query($sql2);
		if(count($query2->result())>0)
		{	//echo $sql;
			foreach ($query2->result() as $row2)
			{
			//$gameData['id'] = $row2->player_f_name;
			$gameData['player_l_name'] = $row2->player_l_name;
			//$gameData['game_summary_mapping']  = $row2->game_summary_mapping;
			//$gameData['position'] = $row2->position;
			//$gameData['team_id'] = $row2->team_id;
			$value[]= $row2->player_f_name.' '.$row2->player_l_name;
			}
		}
		else
		{
				$value =  '';
		}
		$arrTeams = $this->getTeamListJSON($player_name);
		if($arrTeams){
			foreach($arrTeams as $team){
				$value[]= $team;
			}
		}
		echo json_encode($value);
	}
	function getTeamListJSON($val){
		$value = false;
		if(!$val) {
			return false;
		}
		$sql = "SELECT * FROM `new_team` WHERE game_summary_mapping LIKE '%$val%' order by game_summary_mapping ASC";
		
		$query = $this->db->query($sql);
		if(count($query->result())>0)
		{
			$value = array();

			foreach ($query->result() as $row)
			{
				$value[] = $row->game_summary_mapping;
			}
		}
		return $value;
	}
	function getTeamListID($val){
		$value = false;
		if(!$val) {
			return false;
		}
		$sql = "SELECT * FROM `new_team` WHERE game_summary_mapping = '$val'";

		$query = $this->db->query($sql);
		if(count($query->result())==1)
		{	
			foreach ($query->result() as $row)
			{
				$value = $row->team_id;
			}
		}
		return $value;
	}
	function getPlayerNHLID($player_name){
		$value = '';

		if (get_magic_quotes_gpc()) $player_name = stripslashes($player_name);
		$player_name = mysql_escape_string($player_name);
		$sql2 = "SELECT DISTINCT nhl_id, player_f_name,	player_l_name FROM new_player WHERE full_name LIKE '%$player_name' LIMIT 20";
		$query2 = $this->db->query($sql2);
		if(count($query2->result())>0)
		{	//echo $sql;
			foreach ($query2->result() as $row2)
			{
			//$gameData['id'] = $row2->player_f_name;
			$value = $row2->nhl_id;
			}
		}
		else
		{
				$value =  '';
		}
		return $value;
	}
	function checkPlayerList($player_name){
		$value = array();
		$pieces = explode(' ',$player_name);

		if(count($pieces)==1){
		if (get_magic_quotes_gpc()) $player_name = stripslashes($player_name);
		$player_name = mysql_escape_string($player_name);

		$sql2 = "SELECT DISTINCT player_f_name,	player_l_name FROM new_player WHERE player_f_name LIKE '%$player_name%' OR player_l_name LIKE '%$player_name%' LIMIT 20";
		}
		
		elseif(count($pieces)==2){
		$first = $pieces[0];
		$last = $pieces[1];
				if (get_magic_quotes_gpc()) $first = stripslashes($first);
		$first = mysql_escape_string($first);
				if (get_magic_quotes_gpc()) $last = stripslashes($last);
		$last = mysql_escape_string($last);

		$sql2 = "SELECT DISTINCT player_f_name,	player_l_name FROM new_player WHERE player_f_name LIKE '%$first%' AND player_l_name LIKE '%$last' LIMIT 20";
		}
		elseif(count($pieces)>2){
			if (get_magic_quotes_gpc()) $player_name = stripslashes($player_name);
			$player_name = mysql_escape_string($player_name);
			$sql2 = "SELECT DISTINCT player_f_name,	player_l_name FROM new_player WHERE full_name LIKE '%$player_name%' LIMIT 20";
		}
		$query2 = $this->db->query($sql2);
		//echo $sql2;
		if(count($query2->result())==1)
		{	//echo $sql;
			foreach ($query2->result() as $row2)
			{
			//$gameData['id'] = $row2->player_f_name;
			$gameData['player_l_name'] = $row2->player_l_name;
			//$gameData['game_summary_mapping']  = $row2->game_summary_mapping;
			//$gameData['position'] = $row2->position;
			//$gameData['team_id'] = $row2->team_id;
			$value['name']= $row2->player_f_name.' '.$row2->player_l_name;
			}
		}
		if(count($value)<1)
		{
			//Maybe its a team?
			$arrTeam = $this->getTeamListJSON($player_name);
			if($arrTeam){
				$value['name']= current($arrTeam);
			}
			else{
				$value['name'] =  'error';
				$value['message'] =  'Your Query was too ambiguous, please type in the full player name, or select it from the drop down';
			}
		}
		//if(is_user()){
		echo json_encode($value);
//		}
//		elseif($value['name'] == 'ALEXANDER OVECHKIN' | $value['name'] == 'SIDNEY CROSBY'){
//		echo json_encode($value);
//		}
//		else {
//			$value['name'] =  'error';
//			$value['message'] =  'You must be logged in to add new players';
//			echo json_encode($value);
//		}
	}

	function getPenList($adv='0'){
		if($adv=='1') $sqlAdvanced = " WHERE adv ='1' "; 
		else $sqlAdvanced = " WHERE adv ='0' "; 
		$sql = "SELECT * FROM new_penalty_mapping $sqlAdvanced order by penalty_name ";	
		$query = $this->db->query($sql);
		foreach ($query->result() as $row)
		{
			$id  = $row->penalty_id;
			//$id = substr($id, -2); 
			$penalty_name  = $row->penalty_name;
			$value .= "
				<li>
					<input type='checkbox' name='teamPenalty' value='$id' checked='checked' />
					<a class='checkbox-select' href='#'>$penalty_name</a>
					<a class='checkbox-deselect' href='#'>$penalty_name</a>
				</li>
				\n";
		}
		return $value;
	}
	function chkEmbed($userID){
		$sql = "SELECT count(id) as numEmbed FROM new_embed where user_id='$userID'";

		$query = $this->db->query($sql);
		foreach ($query->result() as $row)
		{
			$numEmbed  = $row->numEmbed;
		}
		return $numEmbed;
	}
	function readEmbed($secret){
		$sql = "SELECT * FROM new_embed where secret='$secret'";
		$query = $this->db->query($sql);
		foreach ($query->result() as $row)
		{
			$data[]  = $row;
		}
		return $data;
	}
	function addEmbed($stattype,$playerID,$dates,$strength = 'EV-SH-PP', $period='f-s-t-ot-gt',$teamAgainst='0',$gameType,$penalities, $sqlfilename,$userID,$strrnd,$location,$esstats){
		if (get_magic_quotes_gpc()) $stattype = stripslashes($stattype);
		$stattype = mysql_escape_string($stattype);
		if (get_magic_quotes_gpc()) $playerID = stripslashes($playerID);
		$playerID = mysql_escape_string($playerID);
		if (get_magic_quotes_gpc()) $start_date = stripslashes($start_date);
		$start_date = mysql_escape_string($start_date);
		if (get_magic_quotes_gpc()) $end_date = stripslashes($end_date);
		$end_date = mysql_escape_string($end_date);
		if (get_magic_quotes_gpc()) $strength = stripslashes($strength);
		$strength = mysql_escape_string($strength);
		if (get_magic_quotes_gpc()) $period = stripslashes($period);
		$period = mysql_escape_string($period);
		if (get_magic_quotes_gpc()) $teamAgainst = stripslashes($teamAgainst);
		$teamAgainst = mysql_escape_string($teamAgainst);
		if (get_magic_quotes_gpc()) $penalities = stripslashes($penalities);
		$penalities = mysql_escape_string($penalities);
		if (get_magic_quotes_gpc()) $sqlfilename = stripslashes($sqlfilename);
		$sqlfilename = mysql_escape_string($sqlfilename);
		if (get_magic_quotes_gpc()) $gameType = stripslashes($gameType);
		$gameType = mysql_escape_string($gameType);
		if (get_magic_quotes_gpc()) $location = stripslashes($location);
		$location = mysql_escape_string($location);
		//Against team build (1000011-1000021)
		if ($teamAgainst!='0'){
			//$str = explode('-',$strength);
			$teamAgainst = str_replace("-", ",", $teamAgainst);
		}
		else $SQLteamAgainst ='';

		$sql3 = "SELECT location, team_name FROM new_team WHERE team_id IN ($teamAgainst);";
		$query3 = $this->db->query($sql3);
		if(count($query3->result())>=1)
		{	//echo $sql;
			foreach ($query3->result() as $row3)
			{
			$teamLoaction .= $row3->location .' '. $row3->team_name .'|';
			}
		}

		$sql = "INSERT INTO new_embed (user_id, statistic, startDate,pvals,Strength,goalPeriods,teamAgainst,gt_vals,teamPenalties,secret,image,location,es_stat) VALUES ('$userID','$stattype','$dates','$playerID','$strength','$period','$teamLoaction','$gameType','$penalities','$strrnd','$sqlfilename','$location','$esstats')";
		//echo $sql;
		$query = $this->db->query($sql);
		return $this->db->insert_id();
	}
	function save($stattype,$players,$dates,$strength = 'EV-SH-PP', $period='f-s-t-ot-gt',$teamAgainst='0',$gameType,$penalities,$esStat, $location,$userID,$graphID,$graphName){
		if (get_magic_quotes_gpc()) $stattype = stripslashes($stattype);
		$stattype = mysql_escape_string($stattype);
		if (get_magic_quotes_gpc()) $players = stripslashes($players);
		$players = mysql_escape_string($players);
		if (get_magic_quotes_gpc()) $dates = stripslashes($dates);
		$dates = mysql_escape_string($dates);
		if (get_magic_quotes_gpc()) $strength = stripslashes($strength);
		$strength = mysql_escape_string($strength);
		if (get_magic_quotes_gpc()) $period = stripslashes($period);
		$period = mysql_escape_string($period);

		if (get_magic_quotes_gpc()) $teamAgainst = stripslashes($teamAgainst);
		$teamAgainst = mysql_escape_string($teamAgainst);
		if (get_magic_quotes_gpc()) $penalities = stripslashes($penalities);
		$penalities = mysql_escape_string($penalities);
		if (get_magic_quotes_gpc()) $esStat = stripslashes($esStat);
		$esStat = mysql_escape_string($esStat);
		if (get_magic_quotes_gpc()) $location = stripslashes($location);
		$location = mysql_escape_string($location);
		if (get_magic_quotes_gpc()) $gameType = stripslashes($gameType);
		$gameType = mysql_escape_string($gameType);
		if (get_magic_quotes_gpc()) $graphName = stripslashes($graphName);
		$graphName = mysql_escape_string($graphName);


		$sql = "UPDATE new_saved_graphs SET statistic = '$stattype', dates = '$dates', location = '$location', pvals = '$players', Strength = '$strength', goalPeriods = '$period', teamAgainst = '$teamAgainst', gt_vals = '$gameType', teamPenalties = '$penalities', es_vals = '$esStat', graphName = '$graphName' WHERE id = '$graphID' AND user_id = '$userID'";
		//echo $sql;
		$query = $this->db->query($sql);
		return mysql_affected_rows();
	}
	function delGraph($gId,$uId){


		$sql = "DELETE FROM new_saved_graphs WHERE id = '$gId' AND user_id = '$uId'";
		//echo $sql;
		$query = $this->db->query($sql);
		return mysql_affected_rows();
	}
	function saveAs($stattype,$players,$dates,$strength = 'EV-SH-PP', $period='f-s-t-ot-gt',$teamAgainst='0',$gameType,$penalities,$esStat, $location,$userID,$username,$graphName,$secret){
		if (get_magic_quotes_gpc()) $stattype = stripslashes($stattype);
		$stattype = mysql_escape_string($stattype);
		if (get_magic_quotes_gpc()) $players = stripslashes($players);
		$players = mysql_escape_string($players);
		if (get_magic_quotes_gpc()) $dates = stripslashes($dates);
		$dates = mysql_escape_string($dates);
		if (get_magic_quotes_gpc()) $strength = stripslashes($strength);
		$strength = mysql_escape_string($strength);
		if (get_magic_quotes_gpc()) $period = stripslashes($period);
		$period = mysql_escape_string($period);

		if (get_magic_quotes_gpc()) $teamAgainst = stripslashes($teamAgainst);
		$teamAgainst = mysql_escape_string($teamAgainst);
		if (get_magic_quotes_gpc()) $penalities = stripslashes($penalities);
		$penalities = mysql_escape_string($penalities);
		if (get_magic_quotes_gpc()) $esStat = stripslashes($esStat);
		$esStat = mysql_escape_string($esStat);
		if (get_magic_quotes_gpc()) $location = stripslashes($location);
		$location = mysql_escape_string($location);
		if (get_magic_quotes_gpc()) $gameType = stripslashes($gameType);
		$gameType = mysql_escape_string($gameType);
		if (get_magic_quotes_gpc()) $graphName = stripslashes($graphName);
		$graphName = mysql_escape_string($graphName);


		$sql = "INSERT INTO new_saved_graphs 
		(user_id, statistic, dates,location,pvals,Strength,goalPeriods,teamAgainst,gt_vals,teamPenalties,es_vals,graphName, secret) VALUES 
		('$userID','$stattype',
		'$dates','$location','$players','$strength','$period','$teamAgainst','$gameType','$penalities','$esStat','$graphName','$secret')";
		//echo $sql;
		$query = $this->db->query($sql);
		return $this->db->insert_id();
	}
	function loadGraphData($uid,$saveID, $s){
		$sql = "SELECT id,statistic,dates,location,pvals,Strength,goalPeriods,teamAgainst,gt_vals,teamPenalties,date_added,es_vals,name,graphName FROM new_saved_graphs where secret ='$s' and id ='$saveID'";
		$query = $this->db->query($sql);
		if(count($query->result())>=1)
		{	
			foreach ($query->result() as $row)
			{
				$ret['id']= $row->id;
				$ret['statistic']= $row->statistic;
				$ret['dates']= $row->dates;
				$ret['location']= $row->location;
				$ret['pvals']= $row->pvals;
				$ret['Strength']= $row->Strength;
				$ret['goalPeriods']= $row->goalPeriods;
				$ret['teamAgainst']= $row->teamAgainst;
				$ret['gt_vals']= $row->gt_vals;
				$ret['teamPenalties']= $row->teamPenalties;
				$ret['es_vals']= $row->es_vals;
			}
			
			return $ret;
		}
		else return false;
	}
	function loadPreMadeData($type){
		$sql = "SELECT id,statistic,dates,location,pvals,Strength,goalPeriods,teamAgainst,gt_vals,teamPenalties,date_updated,es_vals,graphName FROM new_premade_graphs where graphName ='$type'";
		$query = $this->db->query($sql);
		if(count($query->result())>=1)
		{	
			foreach ($query->result() as $row)
			{
				$ret['id']= $row->id;
				$ret['statistic']= $row->statistic;
				$ret['dates']= $row->dates;
				$ret['location']= $row->location;
				$ret['pvals']= $row->pvals;
				$ret['Strength']= $row->Strength;
				$ret['goalPeriods']= $row->goalPeriods;
				$ret['teamAgainst']= $row->teamAgainst;
				$ret['gt_vals']= $row->gt_vals;
				$ret['teamPenalties']= $row->teamPenalties;
				$ret['es_vals']= $row->es_vals;
			}
			
			return $ret;
		}
		else return false;
	}
	function verifyPlayer($player_name){
		$value = '';
		$pieces = explode(' ',$player_name);

		if(count($pieces)==1){
		if (get_magic_quotes_gpc()) $player_name = stripslashes($player_name);
		$player_name = mysql_escape_string($player_name);

		$sql2 = "SELECT DISTINCT player_f_name,	player_l_name FROM new_player WHERE player_f_name LIKE '%$player_name%' OR player_l_name LIKE '%$player_name%' LIMIT 20";
		}
		
		elseif(count($pieces)==2){
		$first = $pieces[0];
		$last = $pieces[1];
				if (get_magic_quotes_gpc()) $first = stripslashes($first);
		$first = mysql_escape_string($first);
				if (get_magic_quotes_gpc()) $last = stripslashes($last);
		$last = mysql_escape_string($last);

		$sql2 = "SELECT DISTINCT player_f_name,	player_l_name FROM new_player WHERE player_f_name LIKE '%$first%' AND player_l_name LIKE '%$last%' LIMIT 20";
		}
		elseif(count($pieces)>2){
			if (get_magic_quotes_gpc()) $player_name = stripslashes($player_name);
			$player_name = mysql_escape_string($player_name);
			$sql2 = "SELECT DISTINCT player_f_name,	player_l_name FROM new_player WHERE full_name LIKE '%$player_name%' LIMIT 20";
		}
		$query2 = $this->db->query($sql2);
		//echo $sql2;
		if(count($query2->result())==1)
		{	//echo $sql;
			foreach ($query2->result() as $row2)
			{
			//$gameData['id'] = $row2->player_f_name;
			$value['fname'] = $row2->player_f_name;
			//$gameData['game_summary_mapping']  = $row2->game_summary_mapping;
			//$gameData['position'] = $row2->position;
			//$gameData['team_id'] = $row2->team_id;
			$value['lname']= $row2->player_l_name;
			$value['error'] =  0;
			}
		}
		else
		{
			$value['error'] =  1;
		}
		return $value;
	}
	function genMenu($userID){
		$c = '500001';
		if(!$userID) return "<ul width='180'><li id='500001'><a href='#'>No saved graphs $userID</a></li></ul>";

		$sql = "SELECT * FROM new_saved_graphs where user_id='$userID'";
		$query = $this->db->query($sql);
		$output = '<ul width="180">';
		foreach ($query->result() as $row)
		{
			if(empty($row->graphName)) $gName = '<empty>';
			else $gName =$row->graphName;
			if (strlen($gName)>22){
			$gName = substr($gName,0,21).'...';
			}
			$output .= "<li id='$c' jsFunction='loadGraph($row->id,\"$row->secret\")'><a href='#'>$gName</a></li>";
			$c++;
		}
		$output .= '</ul>';
		return $output;

	}
	function genMenuDel($userID){
		$c = '700001';
		if(!$userID) return "<ul width='180'><li id='500001'><a href='#'>No saved graphs $userID</a></li></ul>";

		$sql = "SELECT * FROM new_saved_graphs where user_id='$userID'";
		$query = $this->db->query($sql);
		$output = '<ul width="180">';
		foreach ($query->result() as $row)
		{
			if(empty($row->graphName)) $gName = '<empty>';
			else $gName =$row->graphName;
			if (strlen($gName)>22){
			$gName = substr($gName,0,21).'...';
			}
			$output .= "<li id='$c' jsFunction='delGraph($row->id)'><a href='#'>$gName</a></li>";
			$c++;
		}
		$output .= '</ul>';
		return $output;

	}
	function buildStatOptDate($compareDate = 0){
		$out='';
		if($compareDate=='0')$arrCompDates= array('20132014');
		else{
			$arrCompDates = explode('-',$compareDate);
		}
		$arrDates = array('0'=>'2007-2008','1'=>'2008-2009','2'=>'2009-2010','3'=>'2010-2011','4'=>'2011-2012','5'=>'2012-2013','6'=>'2013-2014');
		$arrDatesVal = array('0'=>'20072008','1'=>'20082009','2'=>'20092010','3'=>'20102011','4'=>'20112012','5'=>'20122013','6'=>'20132014');
		$x=0;
		foreach($arrDatesVal as $date){
			if(in_array($date,$arrCompDates)){
			$ch = 'checked="checked"';
			}
			else {$ch = '';}
			$dateValue = $arrDates[$x];
			$out .="<li>
				<input type='checkbox' name='seasonDateType' value='$date' $ch />
				<a class='checkbox-select' href='#'>$dateValue</a>
				<a class='checkbox-deselect' href='#'>$dateValue</a>
			</li>";
			$x++;
		}
	return $out;
	}
	function buildStatOptGT($compareGT = 're'){
		$out='';
		if($compareGT=='0')$arrCompGT= array('re');
		else{
			$arrCompGT = explode('-',$compareGT);
		}
		$arrGT = array('0'=>'re','1'=>'pl');
		$arrGTNames = array('0'=>'Reg. Season','1'=>'Playoffs');
		$i=0;
		foreach($arrGT as $gt){
			if(in_array($gt,$arrCompGT)){
			$ch = 'checked="checked"';
			}
			else {$ch = '';}
			$out .="<li>
				<input type='checkbox' name='GameType' value='$gt' $ch />
				<a class='checkbox-select' href='#'>$arrGTNames[$i]</a>
				<a class='checkbox-deselect' href='#'>$arrGTNames[$i]</a>
			</li>";
			$i++;
		}
	return $out;
	}
	function buildStatOptLoc($compareLoc = 'hm-aw'){
		$out='';
		if($compareLoc=='0')$arrCompLoc= array('hm','aw');
		else{
			$arrCompLoc = explode('-',$compareLoc);
		}
		$arrLoc = array('0'=>'hm','1'=>'aw');
		$arrLocNames = array('0'=>'Home','1'=>'Away');
		$i=0;
		foreach($arrLoc as $loc){
			if(in_array($loc,$arrCompLoc)){
			$ch = 'checked="checked"';
			}
			else {$ch = '';}
			$out .="<li>
				<input type='checkbox' name='Location' value='$loc' $ch />
				<a class='checkbox-select' href='#'>$arrLocNames[$i]</a>
				<a class='checkbox-deselect' href='#'>$arrLocNames[$i]</a>
			</li>";
			$i++;
		}
	return $out;
	}
	function buildStatOptPer($comparePer = 'f-s-t-ot'){
		$out='';
		if($comparePer=='0')$arrCompPer= array('f','s','t','ot');
		else{
			$arrCompPer = explode('-',$comparePer);
		}
		$arrPer = array('0'=>'f','1'=>'s','2'=>'t','3'=>'ot');
		$arrPerNames = array('0'=>'1st','1'=>'2nd','2'=>'3rd','3'=>'OT');
		$i=0;
		foreach($arrPer as $per){
			if(in_array($per,$arrCompPer)){
			$ch = 'checked="checked"';
			}
			else {$ch = '';}
			$out .="<li>
				<input type='checkbox' name='Period' value='$per' $ch />
				<a class='checkbox-select' href='#'>$arrPerNames[$i]</a>
				<a class='checkbox-deselect' href='#'>$arrPerNames[$i]</a>
			</li>";
			$i++;
		}
	return $out;
	}
	function buildStatOptStr($compareStr = 'EV-SH-PP'){
		$out='';
		if($compareStr=='0')$arrCompStr= array('EV','SH','PP');
		else{
			$arrCompStr = explode('-',$compareStr);
		}
		$arrStr = array('0'=>'EV','1'=>'SH','2'=>'PP');
		$arrStrNames = array('0'=>'EV','1'=>'SH','2'=>'PP');
		$i=0;
		foreach($arrStr as $str){
			if(in_array($str,$arrCompStr)){
			$ch = 'checked="checked"';
			}
			else {$ch = '';}
			$out .="<li>
				<input type='checkbox' name='Strength' value='$str' $ch />
				<a class='checkbox-select' href='#'>$arrStrNames[$i]</a>
				<a class='checkbox-deselect' href='#'>$arrStrNames[$i]</a>
			</li>";
			$i++;
		}
	return $out;
	}
	function buildStatPlayers2($comparePlayers='PATRICK KANE:EVGENI MALKIN'){
		$out='';
		if($comparePlayers=='0')$arrCompPlayers= array('PATRICK KANE','EVGENI MALKIN');
		else{
			$arrCompPlayers = explode(':',$comparePlayers);
		}

		$i=1;
		foreach($arrCompPlayers as $player){
			$player = str_replace("'", "\'", $player);

			$out .="addPlayer('$player".$i."');";
			$i++;
		}

	return $out;
	}
	function buildStatPlayers($comparePlayers='PATRICK KANE:EVGENI MALKIN'){
		$out='';
		if($comparePlayers=='0')$arrCompPlayers= array('PATRICK KANE','EVGENI MALKIN');
		else{
			$arrCompPlayers = explode(':',$comparePlayers);
		}
		
		for($i=1;$i<=6;$i++){
			if(!empty($arrCompPlayers[$i-1])){
				$outVal = $arrCompPlayers[$i-1];
			}
			else $outVal = "empty";
			$out .="<input type='hidden' class='pn' name='stat_player_name$i' id='stat_player_name$i' value=\"$outVal\" />
			<input type='hidden' class='isgraphed' name='graphed_player_name$i' id='graphed_player_name$i' value=\"1\" />";

		}

	return $out;
	}
	function buildStatTab($compareStat='tabGoal'){
		$out='';
		switch($compareStat){
			case 'tabGoal':
				return "switchtabs('tabGoal','goalBut',1);";
				break;
			case 'tabAssist':
				return "switchtabs('tabAssist','assistBut',1);";
				break;
			case 'tabPoints':
				return "switchtabs('tabPoints','pointsBut',1);";
				break;
			case 'tabPims':
				return "switchtabs('tabPims','pimBut',1);";
				break;
			case 'tabEventstats':
				return "switchtabs('tabEventstats','eventBut',1);";
				break;
			case 'tabGoalies':
				return "switchtabs('tabGoalies','goalieBut',1);";
				break;
			default:
				return "switchtabs('tabGoal','goalBut',1);";
		}
	}
	function buildStatTA($compareTA = '1000015-1000024-1000006-1000022-1000023-1000009-1000008-1000030-1000027-1000010-1000011-1000025-1000029-1000028-1000012-1000014-1000013-1000002-1000026-1000003-1000007-1000019-1000018-1000004-1000005-1000021-1000017-1000016-1000001-1000020'){
		$out='';
		if($compareTA=='0')$arrCompTA= array('1000015','1000024','1000006','1000022','1000023','1000009','1000008','1000030','1000027','1000010','1000011','1000025','1000029','1000028','1000012','1000014','1000013','1000002','1000026','1000003','1000007','1000019','1000018','1000004','1000005','1000021','1000017','1000016','1000001','1000020');
		else{
			$arrCompTA = explode('-',$compareTA);
		}
		$arrTA = array('1000015','1000024','1000006','1000022','1000023','1000009','1000008','1000030','1000027','1000010','1000011','1000025','1000029','1000028','1000012','1000014','1000013','1000002','1000026','1000003','1000007','1000019','1000018','1000004','1000005','1000021','1000017','1000016','1000001','1000020');
		$arrTANames = array('Blackhawks','Blue Jackets','Red Wings','Predators','Blues','Flames','Avalanche','Oilers','Wild','Canucks','Ducks','Stars','Kings','Coyotes','Sharks','Devils','Islanders','Rangers','Flyers','Penguins','Briuns','Sabres','Canadiens','Senators','Maple Leafs','Jets','Hurricanes','Panthers','Lightning','Capitals');

		//go through conf by conf
		$arrTA_WC = array('1000015','1000024','1000006','1000022','1000023');
		$arrTA_WC_Names = array('Blackhawks','Blue Jackets','Red Wings','Predators','Blues');
		$out = "<div id='westTeams'>
			<div class='confTitle'><a id='westConfSel'>Western Conference</a></div>
				<div id='centralTeams' class='teamSel'>
					<ul class='checklistW'>
						<li class='divTitleW'><a id='cenTeamSel'>Central</a></li>";
		$i=0;
		foreach($arrTA_WC as $wc_team){
			if(in_array($wc_team,$arrCompTA)){
			$ch = 'checked="checked"';
			}
			else {$ch = '';}
			$out .="<li>
				<input type='checkbox' name='teamAgainst' value='$wc_team' $ch />
				<a class='checkbox-select' href='#'>$arrTA_WC_Names[$i]</a>
				<a class='checkbox-deselect' href='#'>$arrTA_WC_Names[$i]</a>
			</li>";
			$i++;
		}
		//next conf
		$out .="					</ul>
				</div>
				<div id='nwTeams' class='teamSel'>
					<ul class='checklistW'>
						<li class='divTitleW'><a id='nwTeamSel'>Northwest</a></li>";
		$i=0;
		$arrTA_WN = array('1000009','1000008','1000030','1000027','1000010');
		$arrTA_WN_Names = array('Flames','Avalanche','Oilers','Wild','Canucks');
		foreach($arrTA_WN as $wn_team){
			if(in_array($wn_team,$arrCompTA)){
			$ch = 'checked="checked"';
			}
			else {$ch = '';}
			$out .="<li>
				<input type='checkbox' name='teamAgainst' value='$wn_team' $ch />
				<a class='checkbox-select' href='#'>$arrTA_WN_Names[$i]</a>
				<a class='checkbox-deselect' href='#'>$arrTA_WN_Names[$i]</a>
			</li>";
			$i++;
		}
		//next conf
		$out .="</ul>
				</div>
				<div id='pacTeams' class='teamSel'>
					<ul class='checklistW'>
						<li class='divTitleW'><a id='pacTeamSel'>Pacific</a></li>";
		$i=0;
		$arrTA_WP = array('1000011','1000025','1000029','1000028','1000012');
		$arrTA_WP_Names = array('Ducks','Stars','Kings','Coyotes','Sharks');
		foreach($arrTA_WP as $wp_team){
			if(in_array($wp_team,$arrCompTA)){
			$ch = 'checked="checked"';
			}
			else {$ch = '';}
			$out .="<li>
				<input type='checkbox' name='teamAgainst' value='$wp_team' $ch />
				<a class='checkbox-select' href='#'>$arrTA_WP_Names[$i]</a>
				<a class='checkbox-deselect' href='#'>$arrTA_WP_Names[$i]</a>
			</li>";
			$i++;
		}
		//next conf
		$out .="</ul>
				</div>
			</div>
		<div id='eastTeams'>
			<div class='confTitle'><a id='eastConfSel'>Eastern Conference</a></div>
			<div id='atlTeams' class='teamSel'>
			<ul class='checklistE'>
				<li class='divTitleE'><a id='atlTeamSel'>Atlantic</a></li>";
		$i=0;
		$arrTA_EA = array('1000014','1000013','1000002','1000026','1000003');
		$arrTA_EA_Names = array('Devils','Islanders','Rangers','Flyers','Penguins');
		foreach($arrTA_EA as $ea_team){
			if(in_array($ea_team,$arrCompTA)){
			$ch = 'checked="checked"';
			}
			else {$ch = '';}
			$out .="<li>
				<input type='checkbox' name='teamAgainst' value='$ea_team' $ch />
				<a class='checkbox-select' href='#'>$arrTA_EA_Names[$i]</a>
				<a class='checkbox-deselect' href='#'>$arrTA_EA_Names[$i]</a>
			</li>";
			$i++;
		}
		//next conf
		$out .="</ul>
			</div>
			<div id='neTeams' class='teamSel'>
			<ul class='checklistE'>
				<li class='divTitleE'><a id='neTeamSel'>Northeast</a></li>";
		$i=0;
		$arrTA_EN = array('1000007','1000019','1000018','1000004','1000005');
		$arrTA_EN_Names = array('Briuns','Sabres','Canadiens','Senators','Maple Leafs');
		foreach($arrTA_EN as $en_team){
			if(in_array($en_team,$arrCompTA)){
			$ch = 'checked="checked"';
			}
			else {$ch = '';}
			$out .="<li>
				<input type='checkbox' name='teamAgainst' value='$en_team' $ch />
				<a class='checkbox-select' href='#'>$arrTA_EN_Names[$i]</a>
				<a class='checkbox-deselect' href='#'>$arrTA_EN_Names[$i]</a>
			</li>";
			$i++;
		}
		//next conf
		$out .="</ul>
			</div>
			<div id='seTeams' class='teamSel'>
			<ul class='checklistE'>
				<li class='divTitleE'><a id='seTeamSel'>Southeast</a></li>";
		$i=0;
		$arrTA_ES = array('1000021','1000017','1000016','1000001','1000020');
		$arrTA_ES_Names = array('Jets','Hurricanes','Panthers','Lightning','Capitals');
		foreach($arrTA_ES as $es_team){
			if(in_array($es_team,$arrCompTA)){
			$ch = 'checked="checked"';
			}
			else {$ch = '';}
			$out .="<li>
				<input type='checkbox' name='teamAgainst' value='$es_team' $ch />
				<a class='checkbox-select' href='#'>$arrTA_ES_Names[$i]</a>
				<a class='checkbox-deselect' href='#'>$arrTA_ES_Names[$i]</a>
			</li>";
			$i++;
		}
		$out .="			</ul>
			</div>
		</div>";
	return $out;
	}
	function buildStatPEN($adv='0',$comparePEN='0'){
		$value= '';
		if($adv=='1') $sqlAdvanced = " WHERE adv ='1' "; 
		else $sqlAdvanced = " WHERE adv ='0' "; 
		$comparePENArr = explode("-", $comparePEN);
		$sql = "SELECT * FROM new_penalty_mapping $sqlAdvanced order by penalty_name ";	
		$query = $this->db->query($sql);
		foreach ($query->result() as $row)
		{
			$id  = $row->penalty_id;
			//$id = substr($id, -2);
			$penalty_name  = $row->penalty_name;
			if($comparePEN == '0' || in_array($id,$comparePENArr)){
				$ch = "checked='checked'";
			}
			else $ch = '';
			$value .= "
				<li>
					<input type='checkbox' name='teamPenalty' value='$id' $ch />
					<a class='checkbox-select' href='#'>$penalty_name</a>
					<a class='checkbox-deselect' href='#'>$penalty_name</a>
				</li>
				\n";
		}
		return $value;
	}
	function buildStatES($compareES ='0',$adv=0){
		$out='';
		$arrES = array('pm','sog','ab','ms','sp','hg','gv','tk','bs','fw','fl','fp','np','ns');
		$arrESNames = array('Plus Minus','Shots on Goal','Attempts Blocked','Missed Shots','Shooting Percent','Hits Given','Giveaways','Takeaways','Blocked Shots','Faceoffs Won','Faceoffs Lost','Faceoff Percent','# of Penalities','Num Shifts');

		//go through column by col

		$out = "<div class='esTitle'>
			Event Stats - Choose one
		</div>";
		//Column One
		$arrES_COL1 = array('pm');
		$arrES_COL1_Names = array('Plus Minus');
		$i=0;
		$out .="<div id='esColOne' class='esSel'>
			  <div class='clearfix'>";
		foreach($arrES_COL1 as $es_opt){
			if($es_opt==$compareES){
			$ch = 'checked="checked"';
			}
			else {$ch = '';}
			$out .="
				<p>
				  <input name='esOpts' value='$arrES_COL1[$i]' type='radio' $ch/>
				  <a href='#' class='radio-select'>$arrES_COL1_Names[$i]</a>
				  <a href='#' class='radio-deselect'>$arrES_COL1_Names[$i]</a>
				</p>";
			$i++;
		}
		$out .= " </div></div>";
		//Column Two
		$arrES_COL2 = array('sog','ab','ms','sp');
		$arrES_COL2_Names = array('Shots on Goal','Attempts Blocked','Missed Shots','Shooting Percent');
		$i=0;
		$out .="<div id='esColTwo' class='esSel'>
			  <div class='clearfix'>";
		foreach($arrES_COL2 as $es_opt){
			if(($es_opt==$compareES) || ($es_opt == 'sp' && $compareES=='0')){
			$ch = 'checked="checked"';
			}
			else {$ch = '';}
			$out .="
				<p>
				  <input name='esOpts' value='$arrES_COL2[$i]' type='radio' $ch/>
				  <a href='#' class='radio-select'>$arrES_COL2_Names[$i]</a>
				  <a href='#' class='radio-deselect'>$arrES_COL2_Names[$i]</a>
				</p>";
			$i++;
		}
		$out .= " </div></div>";
		//Column Three
		$arrES_COL3 = array('hg');
		$arrES_COL3_Names = array('Hits Given');
		$i=0;
		$out .="<div id='esColThree' class='esSel'>
			  <div class='clearfix'>";
		foreach($arrES_COL3 as $es_opt){
			if($es_opt==$compareES){
			$ch = 'checked="checked"';
			}
			else {$ch = '';}
			$out .="
				<p>
				  <input name='esOpts' value='$arrES_COL3[$i]' type='radio' $ch/>
				  <a href='#' class='radio-select'>$arrES_COL3_Names[$i]</a>
				  <a href='#' class='radio-deselect'>$arrES_COL3_Names[$i]</a>
				</p>";
			$i++;
		}
		$out .= " </div></div>";
		//Column Four
		$arrES_COL4 = array('gv','tk','bs');
		$arrES_COL4_Names = array('Giveaways','Takeaways','Blocked Shots');
		$i=0;
		$out .="<div id='esColFour' class='esSel'>
			  <div class='clearfix'>";
		foreach($arrES_COL4 as $es_opt){
			if($es_opt==$compareES){
			$ch = 'checked="checked"';
			}
			else {$ch = '';}
			$out .="
				<p>
				  <input name='esOpts' value='$arrES_COL4[$i]' type='radio' $ch/>
				  <a href='#' class='radio-select'>$arrES_COL4_Names[$i]</a>
				  <a href='#' class='radio-deselect'>$arrES_COL4_Names[$i]</a>
				</p>";
			$i++;
		}
		$out .= " </div></div>";
		//Column Five
		$arrES_COL5 = array('fw','fl','fp');
		$arrES_COL5_Names = array('Faceoffs Won','Faceoffs Lost','Faceoff Percent');
		$i=0;
		$out .="<div id='esColFive' class='esSel'>
			  <div class='clearfix'>";
		foreach($arrES_COL5 as $es_opt){

			if($es_opt==$compareES){
			$ch = 'checked="checked"';
			}
			else {$ch = '';}
			$out .="
				<p>
				  <input name='esOpts' value='$arrES_COL5[$i]' type='radio' $ch/>
				  <a href='#' class='radio-select'>$arrES_COL5_Names[$i]</a>
				  <a href='#' class='radio-deselect'>$arrES_COL5_Names[$i]</a>
				</p>";
			$i++;
		}
		$out .= " </div></div>";
		//Column Six
		$arrES_COL6 = array('np','ns');
		$arrES_COL6_Names = array('# of Penalities','Num Shifts');
		$i=0;
		$out .="<div id='esColSix' class='esSel'>
			  <div class='clearfix'>";
		foreach($arrES_COL6 as $es_opt){
			if($es_opt==$compareES){
			$ch = 'checked="checked"';
			}
			else {$ch = '';}
			$out .="
				<p>
				  <input name='esOpts' value='$arrES_COL6[$i]' type='radio' $ch/>
				  <a href='#' class='radio-select'>$arrES_COL6_Names[$i]</a>
				  <a href='#' class='radio-deselect'>$arrES_COL6_Names[$i]</a>
				</p>";
			$i++;
		}
		$out .= " </div></div>";
		$out .="<div style='clear:both'></div>";
	return $out;
	}
	function buildStatES2($compareES ='0'){
		$out='';
		$arrES = array('pm','sog','ab','ms','sp','hg','gv','tk','bs','fw','fl','fp','np','ns');
		$arrESNames = array('Plus Minus','Shots on Goal','Attempts Blocked','Missed Shots','Shooting Percent','Hits Given','Giveaways','Takeaways','Blocked Shots','Faceoffs Won','Faceoffs Lost','Faceoff Percent','# of Penalities','Num Shifts');

		//go through column by col

		$out = "<div class='radiolist' id='allEventStats'>";
		//Column One
		$arrES_COL1 = array('ab','ms','sp','hg','bs','ns');
		$arrES_COL1_Names = array('Attempts Blocked','Missed Shots','Shooting Percent','Hits Given','Blocked Shots','Num Shifts');
		$i=0;
		$out .="<div class='esSel esColOne'>
			  <div class='clearfix'>";
		foreach($arrES_COL1 as $es_opt){
			if($es_opt==$compareES || ($es_opt == 'sp' && $compareES=='0')){
			$ch = 'checked="checked"';
			}
			else {$ch = '';}
			$out .="
				<p>
				  <a rel ='$arrES_COL1[$i]' href='javascript:return false;' class='radio-select-es'>$arrES_COL1_Names[$i]</a>
				</p>";
			$i++;
		}
		$out .= " </div></div>";

		//Column Four
		$arrES_COL4 = array('gv','tk','fw','fl','fp','np');
		$arrES_COL4_Names = array('Giveaways','Takeaways','Faceoffs Won','Faceoffs Lost','Faceoff Percent','# of Penalities');
		$i=0;
		$out .="<div class='esSel esColTwo'>
			  <div class='clearfix'>";
		foreach($arrES_COL4 as $es_opt){
			if($es_opt==$compareES){
			$ch = 'checked="checked"';
			}
			else {$ch = '';}
			$out .="
				<p>
				  <a rel ='$arrES_COL4[$i]' href='javascript:return false;' class='radio-select-es'>$arrES_COL4_Names[$i]</a>
				</p>";
			$i++;
		}
		$out .= " </div></div>";
		$out .="</div><div style='clear:both'></div>";
	return $out;
	}
	function getTeamList(){
		$sql = "SELECT * FROM `new_team` order by location";
		
		$query = $this->db->query($sql);
		$value = "<select class='dropElement' id='gambTeams' name='gambTeams' >\n";
		foreach ($query->result() as $row)
		{
			$location  = $row->location;
			$team_name  = $row->team_name;
			$teamId = $row->team_id;
			$value .= "<option value='$teamId'>$location $team_name</option>\n";
		}
		$value .= "</select>";
		return $value;
	}
	
	function getEsStat($postStat)
	{
			switch($postStat) {
			case 'pm':
				$sqlES = "plus_minus";
				break;
			case 'sog':
				$sqlES = "sog";
				break;
			case 'ab':
				$sqlES = "attempts_blocked";
				break;
			case 'ms':
				$sqlES = "missed_shots";
				break;
			case 'hg':
				$sqlES = "hits_given";
				break;
			case 'gv':
				$sqlES = "giveaways";
				break;
			case 'tk':
				$sqlES = "takeaways";
				break;
			case 'bs':
				$sqlES = "blocked_shots";
				break;
			case 'fw':
				$sqlES = "faceoffs_won";
				break;
			case 'fl':
				$sqlES = "faceoffs_lost";
				break;
			case 'np':
				$sqlES = "number_of_penalities";
				break;
			case 'ns':
				$sqlES = "num_shifts";
				break;
			case 'sp':
				$sqlES = "goals as returnStat2, sog";
				break;
			case 'fp':
				$sqlES = "faceoffs_won as returnStat2, faceoffs_lost+faceoffs_won ";
				break;
		}
		return $sqlES;
	
	}
	
	function getLeadersGoals($inputFields, $ascDesc="DESC")
	{
		$sqlStrength =$this->findSTR($inputFields['strength'],'g');
		$sqlDates = $this->findDates($inputFields['dates'], 'game');
		$sqlGametype = $this->findGT($inputFields['gameType'], 'game');
		$sqlLocation = $this->findLOC($inputFields['location']);
		$sqlPeriod = $this->findPeriod($inputFields['period'],'g');
		$SQLteamAgainst = $this->findTA($inputFields['teamAgainst'], 'es','team_against_id');

		//penality  build (01-67)
		if ($penalities!='0'){
			$SQLteamPenalty = ' AND type.id IN (';
			//$str = explode('-',$strength);
			$penalities = str_replace("-", ",", $penalities);
			$SQLteamPenalty .= $penalities.' ) ';
		}
		else $SQLteamPenalty ='';

		$sql = "SELECT SUM(goals) AS totalgoals, nhl_id, player_f_name, player_l_name FROM (
		SELECT game.id, game.game_date, COUNT(g.id) AS goals, game.home_team_id, game.away_team_id, game.player_l_name, game.player_f_name, game.player_id, game.nhl_id
		FROM new_goal g
		INNER JOIN new_player p ON g.player_id = p.id AND period NOT LIKE '%so%' 
		$sqlPeriod
		$sqlStrength 
		RIGHT JOIN (
			SELECT game.id, game.game_date, game.home_team_id, game.away_team_id, p.player_l_name, p.player_f_name, p.id AS player_id, p.nhl_id
			FROM new_game game
			INNER JOIN new_event_summary es ON game.id = es.game_id
			INNER JOIN new_player p ON p.id = es.player_id
			$sqlDates
			$SQLteamAgainst
			$sqlGametype
			$sqlLocation
			) AS game ON g.game_id = game.id AND game.player_id = p.id
		GROUP BY game.id, p.id       
		) AS summedgoals
		GROUP BY nhl_id
		ORDER BY totalgoals $ascDesc";
		$sql = trim(preg_replace('/\s\s+/', ' ', $sql));
		$query = $this->db->query($sql);
		if(count($query->result())>=1)
		{	
			$i=0;
			foreach ($query->result() as $row)
			{
				$ret[$i]['nhl_id'] = $row->nhl_id;
				$ret[$i]['player_f_name'] = $row->player_f_name;
				$ret[$i]['player_l_name'] = $row->player_l_name;
				$i++;
			}
			$outputString = "";
			for ($i = 0; $i <= 5; $i++) {
				$outputString .= $ret[$i]['player_f_name']." ".$ret[$i]['player_l_name'].":";
			}
			return $ret;
		}
		//else 
		return false;
	}
	function getLeadersGoalsTeams($inputFields, $ascDesc="DESC")
	{
		$sqlStrength =$this->findSTR($inputFields['strength'],'g');
		$sqlDates = $this->findDates($inputFields['dates'], 'game');
		$sqlGametype = $this->findGT($inputFields['gameType'], 'game');
		$sqlLocation = $this->findLOC($inputFields['location']);
		$sqlPeriod = $this->findPeriod($inputFields['period'],'g');
		$SQLteamAgainst = $this->findTA($inputFields['teamAgainst'], 'es','team_against_id');

		//penality  build (01-67)
		if ($penalities!='0'){
			$SQLteamPenalty = ' AND type.id IN (';
			//$str = explode('-',$strength);
			$penalities = str_replace("-", ",", $penalities);
			$SQLteamPenalty .= $penalities.' ) ';
		}
		else $SQLteamPenalty ='';

		$sql = "SELECT SUM(goals) AS totalgoals, summedgoals.team_id as team_id, team_name, location 
		FROM (
		SELECT p.team_id, game.id, game.game_date, COUNT(g.id) AS goals, game.home_team_id, game.away_team_id, game.player_l_name, game.player_f_name, game.player_id, game.nhl_id
		FROM new_goal g
		INNER JOIN new_player p ON g.player_id = p.id AND period NOT LIKE '%so%' 
		$sqlPeriod
		$sqlStrength 
		RIGHT JOIN (
			SELECT p.team_id, game.id, game.game_date, game.home_team_id, game.away_team_id, p.player_l_name, p.player_f_name, p.id AS player_id, p.nhl_id
			FROM new_game game
			INNER JOIN new_event_summary es ON game.id = es.game_id
			INNER JOIN new_player p ON p.id = es.player_id
			$sqlDates
			$SQLteamAgainst
			$sqlGametype
			$sqlLocation
			) AS game ON g.game_id = game.id AND game.player_id = p.id
		GROUP BY game.id, p.id       
		) AS summedgoals
		INNER JOIN new_team te ON te.team_id = summedgoals.team_id
		GROUP BY team_id
		ORDER BY totalgoals $ascDesc";
		$sql = trim(preg_replace('/\s\s+/', ' ', $sql));
		$query = $this->db->query($sql);
		if(count($query->result())>=1)
		{	
			$i=0;
			foreach ($query->result() as $row)
			{
				$ret[$i]['team_id'] = $row->team_id;
				//using the player_f_name and player_l_name as placeholders for the team names
				$ret[$i]['player_l_name'] = $row->team_name;
				$ret[$i]['player_f_name'] = $row->location;
				if ($row->team_name == "Canadiens") 
				{
					$ret[$i]['player_f_name'] = $row->team_name;
					$ret[$i]['player_l_name'] = $row->location;
				}
				$i++;
			}
			$outputString = "";
			for ($i = 0; $i <= 5; $i++) {
				$outputString .= $ret[$i]['team_name']." ".$ret[$i]['location'].":";
			}
			return $ret;
		}
		//else 
		return $false;	
	}

	function getLeadersAssists($inputFields, $ascDesc="DESC")
	{
		$sqlStrength =$this->findSTR($inputFields['strength'],'g');
		$sqlDates = $this->findDates($inputFields['dates'], 'game');
		$sqlGametype = $this->findGT($inputFields['gameType'], 'game');
		$sqlLocation = $this->findLOC($inputFields['location']);
		$sqlPeriod = $this->findPeriod($inputFields['period'],'g');
		$SQLteamAgainst = $this->findTA($inputFields['teamAgainst'], 'es','team_against_id');

		//penality  build (01-67)
		if ($penalities!='0'){
			$SQLteamPenalty = ' AND type.id IN (';
			//$str = explode('-',$strength);
			$penalities = str_replace("-", ",", $penalities);
			$SQLteamPenalty .= $penalities.' ) ';
		}
		else $SQLteamPenalty ='';

		$sql = "SELECT SUM(assists) AS totalassists, nhl_id, player_f_name, player_l_name FROM (
			SELECT game.id, game.game_date, COUNT(a.id) AS assists, game.home_team_id, game.away_team_id, game.player_l_name, game.player_f_name, game.player_id, game.nhl_id
			FROM new_assist a		
			INNER JOIN new_goal g ON a.goal = g.id
			INNER JOIN new_player p ON a.player_id = p.id AND g.period NOT LIKE '%so%' 
			$sqlPeriod
			$sqlStrength 
			RIGHT JOIN (
				SELECT game.id, game.game_date, game.home_team_id, game.away_team_id, p.player_l_name, p.player_f_name, p.id AS player_id, p.nhl_id
				FROM new_game game
				INNER JOIN new_event_summary es ON game.id = es.game_id
				INNER JOIN new_player p ON p.id = es.player_id
				$sqlDates
				$SQLteamAgainst
				$sqlGametype
				$sqlLocation
				) AS game ON g.game_id = game.id AND game.player_id = p.id
			GROUP BY game.id, p.id       
			) AS summedassists
		GROUP BY nhl_id
		ORDER BY totalassists $ascDesc";
		$sql = trim(preg_replace('/\s\s+/', ' ', $sql));
		$query = $this->db->query($sql);
		if(count($query->result())>=1)
		{	
			$i=0;
			foreach ($query->result() as $row)
			{
				$ret[$i]['nhl_id'] = $row->nhl_id;
				$ret[$i]['player_f_name'] = $row->player_f_name;
				$ret[$i]['player_l_name'] = $row->player_l_name;
				$i++;
			}
			$outputString = "";
			for ($i = 0; $i <= 5; $i++) {
				$outputString .= $ret[$i]['player_f_name']." ".$ret[$i]['player_l_name'].":";
			}
			return $ret;
		}
		//else 
		return false;
	}
	function getLeadersAssistsTeams($inputFields, $ascDesc="DESC")
	{
		$sqlStrength =$this->findSTR($inputFields['strength'],'g');
		$sqlDates = $this->findDates($inputFields['dates'], 'game');
		$sqlGametype = $this->findGT($inputFields['gameType'], 'game');
		$sqlLocation = $this->findLOC($inputFields['location']);
		$sqlPeriod = $this->findPeriod($inputFields['period'],'g');
		$SQLteamAgainst = $this->findTA($inputFields['teamAgainst'], 'es','team_against_id');

		//penality  build (01-67)
		if ($penalities!='0'){
			$SQLteamPenalty = ' AND type.id IN (';
			//$str = explode('-',$strength);
			$penalities = str_replace("-", ",", $penalities);
			$SQLteamPenalty .= $penalities.' ) ';
		}
		else $SQLteamPenalty ='';

		$sql = "SELECT SUM(assists) AS totalassists, summedassists.team_id, team_name, location FROM (
			SELECT team_id, game.id, game.game_date, COUNT(a.id) AS assists, game.home_team_id, game.away_team_id, game.player_l_name, game.player_f_name, game.player_id, game.nhl_id
			FROM new_assist a		
			INNER JOIN new_goal g ON a.goal = g.id
			INNER JOIN new_player p ON a.player_id = p.id AND g.period NOT LIKE '%so%' 
			$sqlPeriod
			$sqlStrength 
			RIGHT JOIN (
				SELECT game.id, game.game_date, game.home_team_id, game.away_team_id, p.player_l_name, p.player_f_name, p.id AS player_id, p.nhl_id
				FROM new_game game
				INNER JOIN new_event_summary es ON game.id = es.game_id
				INNER JOIN new_player p ON p.id = es.player_id
				$sqlDates
				$SQLteamAgainst
				$sqlGametype
				$sqlLocation
				) AS game ON g.game_id = game.id AND game.player_id = p.id
			GROUP BY game.id, p.id       
			) AS summedassists
		INNER JOIN new_team te ON te.team_id = summedassists.team_id
		GROUP BY team_id
		ORDER BY totalassists $ascDesc";
		$sql = trim(preg_replace('/\s\s+/', ' ', $sql));
		$query = $this->db->query($sql);
		if(count($query->result())>=1)
		{	
			$i=0;
			foreach ($query->result() as $row)
			{
				$ret[$i]['team_id'] = $row->team_id;
				//using the player_f_name and player_l_name as placeholders for the team names
				$ret[$i]['player_l_name'] = $row->team_name;
				$ret[$i]['player_f_name'] = $row->location;
				if ($row->team_name == "Canadiens") 
				{
					$ret[$i]['player_f_name'] = $row->team_name;
					$ret[$i]['player_l_name'] = $row->location;
				}
				$i++;
			}
			$outputString = "";
			for ($i = 0; $i <= 5; $i++) {
				$outputString .= $ret[$i]['team_name']." ".$ret[$i]['location'].":";
			}
			return $ret;
		}
		//else 
		return false;	
	}
	
	
	function getLeadersPoints($inputFields, $ascDesc="DESC")
	{
		$sqlStrength =$this->findSTR($inputFields['strength'],'g');
		$sqlDates = $this->findDates($inputFields['dates'], 'game');
		$sqlGametype = $this->findGT($inputFields['gameType'], 'game');
		$sqlLocation = $this->findLOC($inputFields['location']);
		$sqlPeriod = $this->findPeriod($inputFields['period'],'g');
		$SQLteamAgainst = $this->findTA($inputFields['teamAgainst'], 'es','team_against_id');

		//penality  build (01-67)
		if ($penalities!='0'){
			$SQLteamPenalty = ' AND type.id IN (';
			//$str = explode('-',$strength);
			$penalities = str_replace("-", ",", $penalities);
			$SQLteamPenalty .= $penalities.' ) ';
		}
		else $SQLteamPenalty ='';

		$sql = "
		SELECT SUM(points) AS totalpoints, nhl_id, player_f_name, player_l_name FROM (
		SELECT game.id, game.game_date, COUNT(g.id) AS points, game.home_team_id, game.away_team_id, game.player_l_name, game.player_f_name, game.player_id, game.nhl_id
		FROM new_goal g
		INNER JOIN new_player p ON g.player_id = p.id AND period NOT LIKE '%so%' 
		$sqlPeriod
		$sqlStrength 
		RIGHT JOIN (
			SELECT game.id, game.game_date, game.home_team_id, game.away_team_id, p.player_l_name, p.player_f_name, p.id AS player_id, p.nhl_id
			FROM new_game game
			INNER JOIN new_event_summary es ON game.id = es.game_id
			INNER JOIN new_player p ON p.id = es.player_id
			$sqlDates
			$SQLteamAgainst
			$sqlGametype
			$sqlLocation
			) AS game ON g.game_id = game.id AND game.player_id = p.id
			GROUP BY game.id, p.id       
		
			UNION ALL
			
			SELECT game.id, game.game_date, COUNT(a.id) AS points, game.home_team_id, game.away_team_id, game.player_l_name, game.player_f_name, game.player_id, game.nhl_id
			FROM new_assist a		
			INNER JOIN new_goal g ON a.goal = g.id
			INNER JOIN new_player p ON a.player_id = p.id AND g.period NOT LIKE '%so%' 
			$sqlPeriod
			$sqlStrength 
			RIGHT JOIN (
				SELECT game.id, game.game_date, game.home_team_id, game.away_team_id, p.player_l_name, p.player_f_name, p.id AS player_id, p.nhl_id
				FROM new_game game
				INNER JOIN new_event_summary es ON game.id = es.game_id
				INNER JOIN new_player p ON p.id = es.player_id
				$sqlDates
				$SQLteamAgainst
				$sqlGametype
				$sqlLocation
				) AS game ON g.game_id = game.id AND game.player_id = p.id
			GROUP BY game.id, p.id       
			) AS summedpoints
		GROUP BY nhl_id
		ORDER BY totalpoints $ascDesc";
		
		$sql = trim(preg_replace('/\s\s+/', ' ', $sql));
		$query = $this->db->query($sql);
		if(count($query->result())>=1)
		{	
			$i=0;
			foreach ($query->result() as $row)
			{
				$ret[$i]['nhl_id'] = $row->nhl_id;
				$ret[$i]['player_f_name'] = $row->player_f_name;
				$ret[$i]['player_l_name'] = $row->player_l_name;
				$i++;
			}
			$outputString = "";
			for ($i = 0; $i <= 5; $i++) {
				$outputString .= $ret[$i]['player_f_name']." ".$ret[$i]['player_l_name'].":";
			}
			return $ret;
		}
		//else 
		return false;
	}
	
	
	
	function getLeadersPointsTeams($inputFields, $ascDesc="DESC")
	{
		$sqlStrength =$this->findSTR($inputFields['strength'],'g');
		$sqlDates = $this->findDates($inputFields['dates'], 'game');
		$sqlGametype = $this->findGT($inputFields['gameType'], 'game');
		$sqlLocation = $this->findLOC($inputFields['location']);
		$sqlPeriod = $this->findPeriod($inputFields['period'],'g');
		$SQLteamAgainst = $this->findTA($inputFields['teamAgainst'], 'es','team_against_id');

		//penality  build (01-67)
		if ($penalities!='0'){
			$SQLteamPenalty = ' AND type.id IN (';
			//$str = explode('-',$strength);
			$penalities = str_replace("-", ",", $penalities);
			$SQLteamPenalty .= $penalities.' ) ';
		}
		else $SQLteamPenalty ='';

		$sql = "
		SELECT SUM(points) AS totalpoints, summedpoints.team_id, team_name, location FROM (
		SELECT team_id, game.id, game.game_date, COUNT(g.id) AS points, game.home_team_id, game.away_team_id, game.player_l_name, game.player_f_name, game.player_id, game.nhl_id
		FROM new_goal g
		INNER JOIN new_player p ON g.player_id = p.id AND period NOT LIKE '%so%' 
		$sqlPeriod
		$sqlStrength 
		RIGHT JOIN (
			SELECT game.id, game.game_date, game.home_team_id, game.away_team_id, p.player_l_name, p.player_f_name, p.id AS player_id, p.nhl_id
			FROM new_game game
			INNER JOIN new_event_summary es ON game.id = es.game_id
			INNER JOIN new_player p ON p.id = es.player_id
			$sqlDates
			$SQLteamAgainst
			$sqlGametype
			$sqlLocation
			) AS game ON g.game_id = game.id AND game.player_id = p.id
			GROUP BY game.id, team_id       
		
			UNION ALL
			
			SELECT team_id, game.id, game.game_date, COUNT(a.id) AS points, game.home_team_id, game.away_team_id, game.player_l_name, game.player_f_name, game.player_id, game.nhl_id
			FROM new_assist a		
			INNER JOIN new_goal g ON a.goal = g.id
			INNER JOIN new_player p ON a.player_id = p.id AND g.period NOT LIKE '%so%' 
			$sqlPeriod
			$sqlStrength 
			RIGHT JOIN (
				SELECT game.id, game.game_date, game.home_team_id, game.away_team_id, p.player_l_name, p.player_f_name, p.id AS player_id, p.nhl_id
				FROM new_game game
				INNER JOIN new_event_summary es ON game.id = es.game_id
				INNER JOIN new_player p ON p.id = es.player_id
				$sqlDates
				$SQLteamAgainst
				$sqlGametype
				$sqlLocation
				) AS game ON g.game_id = game.id AND game.player_id = p.id
			GROUP BY game.id, team_id       
			) AS summedpoints
		INNER JOIN new_team te ON te.team_id = summedpoints.team_id
		GROUP BY summedpoints.team_id
		ORDER BY totalpoints $ascDesc";
		
		$sql = trim(preg_replace('/\s\s+/', ' ', $sql));
		$query = $this->db->query($sql);
		if(count($query->result())>=1)
		{	
			$i=0;
			foreach ($query->result() as $row)
			{
				$ret[$i]['team_id'] = $row->team_id;
				//using the player_f_name and player_l_name as placeholders for the team names
				$ret[$i]['player_l_name'] = $row->team_name;
				$ret[$i]['player_f_name'] = $row->location;
				if ($row->team_name == "Canadiens") 
				{
					$ret[$i]['player_f_name'] = $row->team_name;
					$ret[$i]['player_l_name'] = $row->location;
				}
				$i++;
			}
			$outputString = "";
			for ($i = 0; $i <= 5; $i++) {
				$outputString .= $ret[$i]['team_name']." ".$ret[$i]['location'].":";
			}
			return $ret;
		}
		//else 
		return false;
	}	

	function getLeadersPims($inputFields, $ascDesc="DESC")
	{
		$sqlStrength =$this->findSTR($inputFields['strength'],'pim');
		$sqlDates = $this->findDates($inputFields['dates'], 'game');
		$sqlGametype = $this->findGT($inputFields['gameType'], 'game');
		$sqlLocation = $this->findLOC($inputFields['location']);
		$sqlPeriod = $this->findPeriod($inputFields['period'],'pim');
		$SQLteamAgainst = $this->findTA($inputFields['teamAgainst'], 'es','team_against_id');

		//penality  build (01-67)
		if ($inputFields['penalties']!='0'){
			$SQLPenalty = ' AND penalty_id IN (';
			//$str = explode('-',$strength);
			$penalities = str_replace("-", ",", $inputFields['penalties']);
			$SQLPenalty .= $penalities.' ) ';
		}
		else $SQLPenalty ='';

		$sql = "SELECT SUM(pims) AS totalpims, nhl_id, player_f_name, player_l_name FROM (
		SELECT game.id, game.game_date, sum(pim.pim) AS pims, game.home_team_id, game.away_team_id, game.player_l_name, game.player_f_name, game.player_id, game.nhl_id
		FROM new_penalty pim
		INNER JOIN new_player p ON pim.player_id = p.id AND period NOT LIKE '%so%' 
		$sqlPeriod
		$SQLPenalty
		RIGHT JOIN (
			SELECT game.id, game.game_date, game.home_team_id, game.away_team_id, p.player_l_name, p.player_f_name, p.id AS player_id, p.nhl_id
			FROM new_game game
			INNER JOIN new_event_summary es ON game.id = es.game_id
			INNER JOIN new_player p ON p.id = es.player_id
			$sqlDates
			$SQLteamAgainst
			$sqlGametype
			$sqlLocation
			) AS game ON pim.game_id = game.id AND game.player_id = p.id
		GROUP BY game.id, p.id       
		) AS summedpims
		GROUP BY nhl_id
		ORDER BY totalpims $ascDesc";
		$sql = trim(preg_replace('/\s\s+/', ' ', $sql));
		$query = $this->db->query($sql);
		if(count($query->result())>=1)
		{	
			$i=0;
			foreach ($query->result() as $row)
			{
				$ret[$i]['nhl_id'] = $row->nhl_id;
				$ret[$i]['player_f_name'] = $row->player_f_name;
				$ret[$i]['player_l_name'] = $row->player_l_name;
				$i++;
			}
			$outputString = "";
			for ($i = 0; $i <= 5; $i++) {
				$outputString .= $ret[$i]['player_f_name']." ".$ret[$i]['player_l_name'].":";
			}
			return $ret;
		}
		//else 
		return false;
	}
	function getLeadersPimsTeams($inputFields, $ascDesc="DESC")
	{
		$sqlStrength =$this->findSTR($inputFields['strength'],'pim');
		$sqlDates = $this->findDates($inputFields['dates'], 'game');
		$sqlGametype = $this->findGT($inputFields['gameType'], 'game');
		$sqlLocation = $this->findLOC($inputFields['location']);
		$sqlPeriod = $this->findPeriod($inputFields['period'],'pim');
		$SQLteamAgainst = $this->findTA($inputFields['teamAgainst'], 'es','team_against_id');

		//penality  build (01-67)
		if ($inputFields['penalties']!='0'){
			$SQLPenalty = ' AND penalty_id IN (';
			//$str = explode('-',$strength);
			$penalities = str_replace("-", ",", $inputFields['penalties']);
			$SQLPenalty .= $penalities.' ) ';
		}
		else $SQLPenalty ='';

		$sql = "SELECT SUM(pims) AS totalpims, summedpims.team_id, team_name, location FROM (
		SELECT team_id, game.id, game.game_date, sum(pim.pim) AS pims, game.home_team_id, game.away_team_id, game.player_l_name, game.player_f_name, game.player_id, game.nhl_id
		FROM new_penalty pim
		INNER JOIN new_player p ON pim.player_id = p.id AND period NOT LIKE '%so%' 
		$sqlPeriod
		$SQLPenalty
		RIGHT JOIN (
			SELECT game.id, game.game_date, game.home_team_id, game.away_team_id, p.player_l_name, p.player_f_name, p.id AS player_id, p.nhl_id
			FROM new_game game
			INNER JOIN new_event_summary es ON game.id = es.game_id
			INNER JOIN new_player p ON p.id = es.player_id
			$sqlDates
			$SQLteamAgainst
			$sqlGametype
			$sqlLocation
			) AS game ON pim.game_id = game.id AND game.player_id = p.id
		GROUP BY game.id, p.id       
		) AS summedpims
		INNER JOIN new_team te ON te.team_id = summedpims.team_id
		GROUP BY team_id
		ORDER BY totalpims $ascDesc";
		$sql = trim(preg_replace('/\s\s+/', ' ', $sql));
		$query = $this->db->query($sql);
		if(count($query->result())>=1)
		{	
			$i=0;
			foreach ($query->result() as $row)
			{
				$ret[$i]['team_id'] = $row->team_id;
				//using the player_f_name and player_l_name as placeholders for the team names
				$ret[$i]['player_l_name'] = $row->team_name;
				$ret[$i]['player_f_name'] = $row->location;
				if ($row->team_name == "Canadiens") 
				{
					$ret[$i]['player_f_name'] = $row->team_name;
					$ret[$i]['player_l_name'] = $row->location;
				}
				$i++;
			}
			$outputString = "";
			for ($i = 0; $i <= 5; $i++) {
				$outputString .= $ret[$i]['team_name']." ".$ret[$i]['location'].":";
			}
			return $ret;
		}
		//else 
		return false;	
	}

	function getLeadersEventstats($inputFields, $ascDesc="DESC")
	{
		$sqlStrength =$this->findSTR($inputFields['strength'],'g');
		$sqlDates = $this->findDates($inputFields['dates'], 'game');
		$sqlGametype = $this->findGT($inputFields['gameType'], 'game');
		$sqlLocation = $this->findLOC($inputFields['location']);
		$sqlPeriod = $this->findPeriod($inputFields['period'],'g');
		$SQLteamAgainst = $this->findTA($inputFields['teamAgainst'], 'es','team_against_id');
		//penality  build (01-67)
		if ($penalities!='0'){
			$SQLteamPenalty = ' AND type.id IN (';
			//$str = explode('-',$strength);
			$penalities = str_replace("-", ",", $penalities);
			$SQLteamPenalty .= $penalities.' ) ';
		}
		else $SQLteamPenalty ='';
		
		$sqlES = $this->getEsStat($inputFields['esStat']);

		if ($inputFields['esStat'] != "sp" and $inputFields['esStat'] != "fp")
		{
			$sql = "SELECT SUM($sqlES) AS totals, nhl_id, player_f_name, player_l_name FROM (
			SELECT game.id, game.game_date, SUM($sqlES) AS $sqlES, game.home_team_id, game.away_team_id, player_l_name, player_f_name, player_id, nhl_id
				FROM new_game game
				INNER JOIN new_event_summary es ON game.id = es.game_id
				INNER JOIN new_player p ON p.id = es.player_id
				$sqlDates
				$SQLteamAgainst
				$sqlGametype
				$sqlLocation
			GROUP BY game.id, p.id       
			) AS summedEs
			GROUP BY nhl_id
			ORDER BY totals $ascDesc";
		}
		elseif ($inputFields['esStat'] == "sp")
		{
			$sql = "SELECT SUM(goals)/SUM(shots) AS totals, shots, nhl_id, player_f_name, player_l_name FROM (
			SELECT game.id, game.game_date, SUM(sog) AS shots, SUM(goals) AS goals, game.home_team_id, game.away_team_id, player_l_name, player_f_name, player_id, nhl_id
				FROM new_game game
				INNER JOIN new_event_summary es ON game.id = es.game_id
				INNER JOIN new_player p ON p.id = es.player_id
				$sqlDates
				$SQLteamAgainst
				$sqlGametype
				$sqlLocation
			GROUP BY p.id       
			) AS summedEs
			Where shots > 50
			GROUP BY nhl_id
			ORDER BY totals $ascDesc";
		}
		elseif ($inputFields['esStat'] == "fp")
		{
		
			$sql = "SELECT SUM(faceoffs_won)/SUM(total) AS totals, total, nhl_id, player_f_name, player_l_name FROM (
			SELECT game.id, game.game_date, SUM(faceoffs_lost+faceoffs_won) AS total, SUM(faceoffs_won) AS faceoffs_won, game.home_team_id, game.away_team_id, player_l_name, player_f_name, player_id, nhl_id
				FROM new_game game
				INNER JOIN new_event_summary es ON game.id = es.game_id
				INNER JOIN new_player p ON p.id = es.player_id 
				$sqlDates
				$SQLteamAgainst
				$sqlGametype
				$sqlLocation
			GROUP BY p.id       
			) AS summedEs
			Where total > 50
			GROUP BY nhl_id
			ORDER BY totals $ascDesc";
		}
		$sql = trim(preg_replace('/\s\s+/', ' ', $sql));
		$query = $this->db->query($sql);
		if(count($query->result())>=1)
		{	
			$i=0;
			foreach ($query->result() as $row)
			{
				$ret[$i]['nhl_id'] = $row->nhl_id;
				$ret[$i]['player_f_name'] = $row->player_f_name;
				$ret[$i]['player_l_name'] = $row->player_l_name;
				$i++;
			}
			$outputString = "";
			for ($i = 0; $i <= 5; $i++) {
				$outputString .= $ret[$i]['player_f_name']." ".$ret[$i]['player_l_name'].":";
			}
			return $ret;
		}
		//else 
		return false;
	}
	function getLeadersEventstatsTeams($inputFields, $ascDesc="DESC")
	{
		$sqlStrength =$this->findSTR($inputFields['strength'],'g');
		$sqlDates = $this->findDates($inputFields['dates'], 'game');
		$sqlGametype = $this->findGT($inputFields['gameType'], 'game');
		$sqlLocation = $this->findLOC($inputFields['location']);
		$sqlPeriod = $this->findPeriod($inputFields['period'],'g');
		$SQLteamAgainst = $this->findTA($inputFields['teamAgainst'], 'es','team_against_id');
		//penality  build (01-67)
		if ($penalities!='0'){
			$SQLteamPenalty = ' AND type.id IN (';
			//$str = explode('-',$strength);
			$penalities = str_replace("-", ",", $penalities);
			$SQLteamPenalty .= $penalities.' ) ';
		}
		else $SQLteamPenalty ='';
		
		$sqlES = $this->getEsStat($inputFields['esStat']);

		if ($inputFields['esStat'] != "sp" and $inputFields['esStat'] != "fp")
		{
			$sql = "SELECT SUM($sqlES) AS totals, summedEs.team_id, team_name, location  FROM (
			SELECT p.team_id, game.id, game.game_date, SUM($sqlES) AS $sqlES, game.home_team_id, game.away_team_id, player_l_name, player_f_name, player_id, nhl_id
				FROM new_game game
				INNER JOIN new_event_summary es ON game.id = es.game_id
				INNER JOIN new_player p ON p.id = es.player_id
				$sqlDates
				$SQLteamAgainst
				$sqlGametype
				$sqlLocation
			GROUP BY game.id, p.id       
			) AS summedEs
			INNER JOIN new_team te ON te.team_id = summedEs.team_id
			GROUP BY team_id
			ORDER BY totals $ascDesc";
		}
		elseif ($inputFields['esStat'] == "sp")
		{
			$sql = "SELECT SUM(goals)/SUM(shots) AS totals, shots, summedEs.team_id, team_name, location FROM (
			SELECT p.team_id, game.id, game.game_date, SUM(sog) AS shots, SUM(goals) AS goals, game.home_team_id, game.away_team_id, player_l_name, player_f_name, player_id, nhl_id
				FROM new_game game
				INNER JOIN new_event_summary es ON game.id = es.game_id
				INNER JOIN new_player p ON p.id = es.player_id
				$sqlDates
				$SQLteamAgainst
				$sqlGametype
				$sqlLocation
			GROUP BY p.id       
			) AS summedEs
			INNER JOIN new_team te ON te.team_id = summedEs.team_id
			Where shots > 50
			GROUP BY team_id
			ORDER BY totals $ascDesc";
		}
		elseif ($inputFields['esStat'] == "fp")
		{
		
			$sql = "SELECT SUM(faceoffs_won)/SUM(total) AS totals, total, summedEs.team_id, team_name, location FROM (
			SELECT p.team_id, game.id, game.game_date, SUM(faceoffs_lost+faceoffs_won) AS total, SUM(faceoffs_won) AS faceoffs_won, game.home_team_id, game.away_team_id, player_l_name, player_f_name, player_id, nhl_id
				FROM new_game game
				INNER JOIN new_event_summary es ON game.id = es.game_id
				INNER JOIN new_player p ON p.id = es.player_id 
				$sqlDates
				$SQLteamAgainst
				$sqlGametype
				$sqlLocation
			GROUP BY p.id       
			) AS summedEs
			INNER JOIN new_team te ON te.team_id = summedEs.team_id
			Where total > 50
			GROUP BY team_id
			ORDER BY totals $ascDesc";
		}
		$sql = trim(preg_replace('/\s\s+/', ' ', $sql));
		$query = $this->db->query($sql);
		if(count($query->result())>=1)
		{	
			$i=0;
			foreach ($query->result() as $row)
			{
				$ret[$i]['team_id'] = $row->team_id;
				//using the player_f_name and player_l_name as placeholders for the team names
				$ret[$i]['player_l_name'] = $row->team_name;
				$ret[$i]['player_f_name'] = $row->location;
				if ($row->team_name == "Canadiens") 
				{
					$ret[$i]['player_f_name'] = $row->team_name;
					$ret[$i]['player_l_name'] = $row->location;
				}
				$i++;
			}
			$outputString = "";
			for ($i = 0; $i <= 5; $i++) {
				$outputString .= $ret[$i]['team_name']." ".$ret[$i]['location'].":";
			}
			return $ret;
		}
		//else 
		return false;
	}
	
}
?>