<?
//getNHLPlayers2(0);
getNHLSchedNEW(0);
//getNHLSchedualPlayed(0);
//updatePremade(0);
function getNHLSchedual($debug=1, $pred_start = 1){
	$db_user="27_ssf432";
	$db_pass="l12321l";
	$database =	"27_ssf";
	$mysql_id =	mysql_connect('mysql.statsmachine.ca', $db_user, $db_pass) or die("Cannot connect to mySQL");
	mysql_select_db($database,	$mysql_id) or die("Can't find database:	".$this->database);
	$i = '0';
	$z = $pred_start;
	//$url='http://www.nhl.com/nhl/app?service=page&page=SubseasonSchedule';
	//$url='http://www.nhl.com/schedules/20102011.html';
	//$url = 'http://www.nhl.com/ice/schedulebyseason.htm?season=20112012&gameType=2&team=&network=&venue=';
	$url='http://www.nhl.com/ice/schedulebymonth.htm';
	
	$feed =  _scrape($url);
	print_r($feed);
	$feed = str_replace("bignetwork",'network',$feed);

	
	preg_match_all('#<div class="skedDataRow" style="background-color: \#fff;">(.*?)</div></a></div></div>#', $feed, $schedual, PREG_SET_ORDER); 
	$i=0;
	foreach ($schedual as $gameday){

		preg_match_all('#<div class="skedDataRow date">(.*?)</div>#', $gameday[1], $datetime, PREG_SET_ORDER);
		preg_match_all('#<div id="skedStartTimeEST" style="display:block;">(.*?)</div>#', $gameday[1], $startTime, PREG_SET_ORDER);
		preg_match_all('#<div class="skedDataRow network">(.*?)</div>#', $gameday[1], $network, PREG_SET_ORDER);
		preg_match_all('#<a href="/ice/preview.htm\?id=201002(.*?)">#', $gameday[1], $gameId, PREG_SET_ORDER);
	
		$dateSub = explode('&nbsp;',$datetime[0][1]);

		$arrOut[$i]['date']=date('Y-m-d',strtotime($dateSub[1]));
		$arrOut[$i]['startTime']= $startTime[0][1];
		$arrOut[$i]['network']= $network[0][1];
		$arrOut[$i++]['gameId']= $gameId[0][1];
		
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
			$gameType = '2';
			$sql="REPLACE INTO	nhl_schedual (id, date, time, gametype) VALUES ('$gameID', '$date', '$time', '$gameType')";
		
			$z++;
			if(!$debug){
					//print_r($sql);
				mysql_db_query($database, $sql) or die("A MySQL error has occurred.<br />Your Query:	" .	$sql . "<br/> Error: (" . mysql_errno() . ") "	. mysql_error());  //do	the	query
			}
			
		}
		echo "\n Finished updating Schedual \n";
	}
}
function getNHLSchedualPlayed($debug=1, $pred_start = 1){
	$db_user="27_ssf432";
	$db_pass="l12321l";
	$database =	"27_ssf";
	$mysql_id =	mysql_connect('mysql.statsmachine.ca', $db_user, $db_pass) or die("Cannot connect to mySQL");
	mysql_select_db($database,	$mysql_id) or die("Can't find database:	".$this->database);
	$i = '0';
	$z = $pred_start;
	//$url='http://www.nhl.com/nhl/app?service=page&page=SubseasonSchedule';
		//$url='http://www.nhl.com/schedules/20102011.html';
	//$url = 'http://www.nhl.com/ice/schedulebyseason.htm?season=20112012&gameType=2&team=&network=&venue=';
	
	$url='http://www.nhl.com/ice/schedulebymonth.htm';
	
	$feed =  _scrape($url);
	$feed = str_replace("bignetwork",'network',$feed);
	$feed = str_replace("Premiere",'',$feed);
	$feed = str_replace("Face Off",'',$feed);
	$feed = str_replace("dateevent",'date',$feed);

	preg_match_all('#<div class="skedDataRow" style="background-color: \#E4E4E4;">(.*?)</div></a></div></div>#', $feed, $schedual, PREG_SET_ORDER); 
	$i=0;
	foreach ($schedual as $gameday){

		preg_match_all('#<div class="skedDataRow date">(.*?)</div>#', $gameday[1], $datetime, PREG_SET_ORDER);
		//print_r( $datetime );
		preg_match_all('#<div id="skedStartTimeEST" style="display:block;">(.*?)</div>#', $gameday[1], $startTime, PREG_SET_ORDER);
		preg_match_all('#<div class="skedDataRow network">(.*?)</div>#', $gameday[1], $network, PREG_SET_ORDER);
		preg_match_all('#<a href="/ice/recap.htm\?id=201002(.*?)">#', $gameday[1], $gameId, PREG_SET_ORDER);
	
		$dateSub = explode('&nbsp;',strip_tags($datetime[0][1]));

		$arrOut[$i]['date']=date('Y-m-d',strtotime($dateSub[1]));
		$arrOut[$i]['startTime']= $startTime[0][1];
		//$arrOut[$i]['network']= $network[0][1];
		$arrOut[$i++]['gameId']= $gameId[0][1];
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
			$gameType = '2';
			$sql="REPLACE INTO	nhl_schedual (id, date, time, gametype) VALUES ('$gameID', '$date', '$time', '$gameType')";
			print_r($sql);
			$z++;
			if(!$debug){
				mysql_db_query($database, $sql) or die("A MySQL error has occurred.<br />Your Query:	" .	$sql . "<br/> Error: (" . mysql_errno() . ") "	. mysql_error());  //do	the	query
			}
		}
	}
}

function getNHLPlayers2($debug=1){

	$db_user="27_ssf432";
	$db_pass="l12321l";
	$database =	"27_ssf";
	//$host = 'mysql.dev.fashion-public-relations.com';
	$host = '127.0.0.1';
	$mysql_id =	mysql_connect($host, $db_user, $db_pass) or die("Cannot connect to mySQL");
	mysql_select_db($database,	$mysql_id) or die("Can't find database:	".$database);
	$alphabet = array ("A","B","C","D","E","F","G","H","I","J","K","L","M","N","O","P","Q","R","S","T","U","V","W","X","Y","Z");
	//$alphabet = array ("B");
	for ( $k = 0; $k <= 25; $k ++) {
		$url="http://www.nhl.com/ice/playersearch.htm?letter=$alphabet[$k]";
		print_r($url);
		$feed =	implode('',	file($url));
		$feed=str_replace("\n",'',$feed );
		$feed=str_replace("\r",'',$feed );
		$feed=str_replace("\t",'',$feed );
		$feed=str_replace("<br />",'<br>',$feed );
		$feed = preg_replace( "{[ \t]+}", ' ', $feed );
		//check number of pages
		//print_r( $feed);
		preg_match_all('#<div class="resultCount">(.*?)</div>#', $feed, $resultDiv, PREG_SET_ORDER);
		preg_match_all('/of (.*?) results/s', $resultDiv[0][1], $results, PREG_SET_ORDER);
		
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
				//print_r($feed);
			//$splitFeed = (split('<!-- player search -->', $feed)); //Get only 2nd half of html source
			preg_match_all('#<table class="data playerSearch">(.*?)</table>#', $feed, $splitFeed, PREG_SET_ORDER);

			preg_match_all('#<tr>(.*?)</tr>#', $splitFeed[0][1], $splitFeed2, PREG_SET_ORDER);
			foreach($splitFeed2 as $player){
				$tmp = strip_tags($player[1],'<td><a>');
				$players[]=strip_attributes($tmp,'td');
			}
			$i=0;

			foreach($players as $player){
					preg_match_all('#<td>(.*?)</td>#', $player, $playerChunk, PREG_SET_ORDER);
	
					$playerStep2[$i][0] = $playerChunk[0][1];
					$playerStep2[$i][1] = strip_tags($playerChunk[1][1]);
					$playerStep2[$i][2] = $playerChunk[2][1];
					$playerStep2[$i][3] = $playerChunk[3][1];
					preg_match_all('/id=(.*?)\"/s', $playerStep2[$i][0], $playerId, PREG_SET_ORDER);
					preg_match_all('/\((.*?)\)/s', $playerStep2[$i][0], $pos, PREG_SET_ORDER);
					$playerStep2[$i][0] = strip_tags($playerStep2[$i][0]);
					$playerStep2[$i][0] = substr($playerStep2[$i][0], 0, -4); 
					$playerStep2[$i][6]=$pos[0][1];
					$playerStep2[$i][5]=$playerId[0][1];
					$i++;
					}

			
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
				$sql="INSERT IGNORE INTO	nhl_players (id, fname,lname,team, dob , homecity, pos,fullname) VALUES ('$nhlId', '$fname', '$lname', '$team', '$dob', '$city','$pos','$actFullname')\n";
				print_r($sql);
				if(!$debug){
					mysql_db_query($database, $sql) or die("A MySQL error has occurred.<br />Your Query:	" .	$sql . "<br/> Error: (" . mysql_errno() . ") "	. mysql_error());  //do	the	query
					}
					unset($playerStep2);unset($players);
				}
			}
		}
	}
}
function _scrape($url){
 	$useragent = "Mozilla/5.1 (compatible; Googlebot/2.1; +http://www.google.com/bot.html"; 
		echo "Adding url: $url \n";
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_USERAGENT, $useragent);
	curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 4);
	curl_setopt($ch, CURLOPT_TIMEOUT, 8);


	curl_setopt($ch, CURLOPT_URL, $url);
	$xml = curl_exec ($ch);
	
	if(!curl_errno($ch))
	{
		$info = curl_getinfo($ch);
		if ($info['http_code'] == 302) {
			$data = "notok";
			echo 'Time Out ' . $info['total_time'] . ' can not send request to ' . $info['url'] . "\n";
		//	$this->scrape($this->start,$this->end,$this->debug,$this->is_playoff);
		}
		echo 'Took ' . $info['total_time'] . ' seconds to send a request to ' . $info['url'] . "\n";
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
function updatePremade($debug=1){

	$db_user="27_ssf432";
	$db_pass="l12321l";
	$database =	"27_ssf";
	$mysql_id =	mysql_connect('localhost', $db_user, $db_pass) or die("Cannot connect to mySQL");
	mysql_select_db($database,	$mysql_id) or die("Can't find database:	".$this->database);

	$pvals='';
	//Top Goals
		$sqlGoals = "SELECT p.player_f_name as fname, p.player_l_name as lname, g.player_id, count(g.id) as GO 
		FROM (
					  SELECT g.*
					  FROM new_goal g
					  INNER JOIN new_game ga on g.game_id = ga.id
					  WHERE ga.game_date > '2010-09-01'
					  AND period !='SO'
		) as g

		INNER JOIN new_player p ON p.id = g.player_id
		group by player_id
		order by GO DESC
		LIMIT 0,8";
		if(!$debug){
			
			$result = mysql_db_query($database, $sqlGoals) or die("A MySQL error has occurred.<br />Your Query:	" .	$sql . "<br/> Error: (" . mysql_errno() . ") "	. mysql_error());  //do	the	query
			while ($row = mysql_fetch_assoc($result)) {
				$pvals .= $row['fname'].' '.$row['lname'].':';
			}
			$pvals = substr($pvals,0,-1); 
		}
		if (get_magic_quotes_gpc()) $pvals = stripslashes($pvals);
		$pvals = mysql_escape_string($pvals);
		$sql="UPDATE new_premade_graphs SET pvals = '$pvals' WHERE id = 1";
		//print_r($sql);
		$z++;
		if(!$debug){
				//print_r($sql);
			mysql_db_query($database, $sql) or die("A MySQL error has occurred.<br />Your Query:	" .	$sql . "<br/> Error: (" . mysql_errno() . ") "	. mysql_error());  //do	the	query
		}
	//Top Assists
		$pvals_assists='';
		$sqlAssists = "SELECT p.player_f_name as fname, p.player_l_name as lname, a.player_id, count(a.id) as A 
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
		if(!$debug){
			//print_r($sql);
			$result2 = mysql_db_query($database, $sqlAssists) or die("A MySQL error has occurred.<br />Your Query:	" .	$sql . "<br/> Error: (" . mysql_errno() . ") "	. mysql_error());  //do	the	query
			while ($row2 = mysql_fetch_assoc($result2)) {
				$pvals_assists .= $row2['fname'].' '.$row2['lname'].':';
			}
			$pvals_assists = substr($pvals_assists,0,-1); 
		}
		if (get_magic_quotes_gpc()) $pvals_assists = stripslashes($pvals_assists);
		$pvals_assists = mysql_escape_string($pvals_assists);
		$sql="UPDATE new_premade_graphs SET pvals = '$pvals_assists' WHERE id = 2";

		$z++;
		if(!$debug){
				//print_r($sql);
			mysql_db_query($database, $sql) or die("A MySQL error has occurred.<br />Your Query:	" .	$sql . "<br/> Error: (" . mysql_errno() . ") "	. mysql_error());  //do	the	query
		}
	//Top Points
		$pvals_points='';
		$sqlPoints = "SELECT p.player_f_name as fname, p.player_l_name as lname, es.player_id, sum(points) as P 
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
		if(!$debug){
			//print_r($sql);
			$result3 = mysql_db_query($database, $sqlPoints) or die("A MySQL error has occurred.<br />Your Query:	" .	$sql . "<br/> Error: (" . mysql_errno() . ") "	. mysql_error());  //do	the	query
			while ($row3 = mysql_fetch_assoc($result3)) {
				$pvals_points .= $row3['fname'].' '.$row3['lname'].':';
			}
			$pvals_points = substr($pvals_points,0,-1); 
		}
		if (get_magic_quotes_gpc()) $pvals_points = stripslashes($pvals_points);
		$pvals_points = mysql_escape_string($pvals_points);
		$sql="UPDATE new_premade_graphs SET pvals = '$pvals_points' WHERE id = 4";

		$z++;
		if(!$debug){
				//print_r($sql);
			mysql_db_query($database, $sql) or die("A MySQL error has occurred.<br />Your Query:	" .	$sql . "<br/> Error: (" . mysql_errno() . ") "	. mysql_error());  //do	the	query
		}
	//Top plus Minus
		$pvals_pm='';
		$sqlPM = "SELECT p.player_f_name as fname, p.player_l_name as lname, es.player_id, sum(plus_minus) as P_M 
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
		if(!$debug){
			//print_r($sql);
			$result4 = mysql_db_query($database, $sqlPM) or die("A MySQL error has occurred.<br />Your Query:	" .	$sql . "<br/> Error: (" . mysql_errno() . ") "	. mysql_error());  //do	the	query
			while ($row4 = mysql_fetch_assoc($result4)) {
				$pvals_pm .= $row4['fname'].' '.$row4['lname'].':';
			}
			$pvals_pm = substr($pvals_pm,0,-1); 
		}
		if (get_magic_quotes_gpc()) $pvals_pm = stripslashes($pvals_pm);
		$pvals_pm = mysql_escape_string($pvals_pm);
		$sql="UPDATE new_premade_graphs SET pvals = '$pvals_pm' WHERE id = 3";

		$z++;
		if(!$debug){
				//print_r($sql);
			mysql_db_query($database, $sql) or die("A MySQL error has occurred.<br />Your Query:	" .	$sql . "<br/> Error: (" . mysql_errno() . ") "	. mysql_error());  //do	the	query
		}
	//Top PIM
		$pvals_pim='';
		$sqlPIM = "SELECT p.player_f_name as fname, p.player_l_name as lname, pen.player_id, sum(pen.pim) as PIMs 
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
		if(!$debug){
			//print_r($sql);
			$result5 = mysql_db_query($database, $sqlPIM) or die("A MySQL error has occurred.<br />Your Query:	" .	$sql . "<br/> Error: (" . mysql_errno() . ") "	. mysql_error());  //do	the	query
			while ($row5 = mysql_fetch_assoc($result5)) {
				$pvals_pim .= $row5['fname'].' '.$row5['lname'].':';
			}
			$pvals_pim= substr($pvals_pim,0,-1); 
		}
		if (get_magic_quotes_gpc()) $pvals_pim = stripslashes($pvals_pim);
		$pvals_pim = mysql_escape_string($pvals_pim);
		$sql="UPDATE new_premade_graphs SET pvals = '$pvals_pim' WHERE id = 5";

		$z++;
		if(!$debug){
				//print_r($sql);
			mysql_db_query($database, $sql) or die("A MySQL error has occurred.<br />Your Query:	" .	$sql . "<br/> Error: (" . mysql_errno() . ") "	. mysql_error());  //do	the	query
		}
}
function getNHLSchedNEW($debug=1){

	$db_user="27_ssf432";
	$db_pass="l12321l";
	$database =	"27_ssf";
	$host = 'mysql.statsmachine.ca';
	//$host = '127.0.0.1';
	$mysql_id =	mysql_connect($host, $db_user, $db_pass) or die("Cannot connect to mySQL");
	mysql_select_db($database,	$mysql_id) or die("Can't find database:	".$database);
	$i = '0';
	$z = $pred_start;
	//$url='http://www.nhl.com/nhl/app?service=page&page=SubseasonSchedule';
	//$url='http://www.nhl.com/schedules/20102011.html';
	//$url = 'http://www.nhl.com/ice/schedulebyseason.htm?season=20112012&gameType=2&team=&network=&venue=';
	$url = 'http://www.nhl.com/ice/schedulebymonth.htm';
			$feed =  _scrape($url);
	
	preg_match_all('#<tr>(.*?)</tr>#', $feed, $schedual, PREG_SET_ORDER); 
	$i=0;
    
	foreach ($schedual as $gameday){
		$arrGameInfo=strip_attributes($gameday[1],'td');
		$arrGameInfo=strip_attributes($arrGameInfo,'div');
		
		preg_match_all('#<td>(.*?)</td>#', $arrGameInfo, $gameChunk, PREG_SET_ORDER); 
		if(count($gameChunk)==6){
			$gameList[] = $gameChunk;
		};
	}
	$i=0;
	//print_r($gameList);
	foreach ($gameList as $gameday){


		preg_match_all('#<div>(.*?)</div>#', $gameday[0][1], $gameDate, PREG_SET_ORDER);
		preg_match_all('#<div>(.*?)</div>#', $gameday[3][1], $gameTime, PREG_SET_ORDER);
		$awHTML=strip_attributes($gameday[1][1],'a');
		$hmHTML=strip_attributes($gameday[2][1],'a');
		preg_match_all('#<div><a>(.*?)</a></div>#', $awHTML, $awayTeam, PREG_SET_ORDER);
		preg_match_all('#<div><a>(.*?)</a></div>#', $hmHTML, $homeTeam, PREG_SET_ORDER);
		preg_match_all('#htm\?id=201103(.*?)">#', $gameday[5][1], $gameId, PREG_SET_ORDER);

		//print_r($gameday);
		$arrOut[$i]['date']=date('Y-m-d',strtotime($gameDate[0][1]));
		$arrOut[$i]['startTime']= $gameTime[0][1];
		$arrOut[$i]['network']= $gameday[4][1];
		$arrOut[$i]['awayTeam']= $awayTeam[0][1];
		$arrOut[$i]['homeTeam']= $homeTeam[0][1];
		$arrOut[$i++]['gameId']= $gameId[0][1];
		
	}
	
	$nl	= count($arrOut);
	//print_r( $arrOut );
	echo '<br>';
	if($nl == 0){
		echo "nothing found";
	}
	elseif ($nl	> 0){
		$x=1;
	foreach($arrOut as $gameDay){
	    if(!empty($gameDay['gameId'])){
    		$date		=	$gameDay['date'];
    		$tv_nat		=	$gameDay['network'];
    		$time		=	$gameDay['startTime'];
    		$gameID		=   $gameDay['gameId'];
    		$awayTeam		=	$gameDay['awayTeam'];
    		$homeTeam		=   $gameDay['homeTeam'];
    		$season = '20112012';
    		$gameID2 = $season.'3'.$gameID;
    		$gameType = '2';
    		$sql="REPLACE INTO	nhl_schedual (id, date, time, gametype, away_team, home_team,season,full_id) VALUES ('$gameID2', '$date', '$time', '$gameType','$awayTeam','$homeTeam','$season','$gameID2')";

    		$z++;
    		if(!$debug){
    			echo '<p>'.$x++.'</p>';

    			mysql_db_query($database, $sql) or die("A MySQL error has occurred.<br />Your Query:	" .	$sql . "<br/> Error: (" . mysql_errno() . ") "	. mysql_error());  //do	the	query
    			
    		}
			echo $x.' - '.$sql."\n";
	    }	
	}
	echo "\n Finished updating Schedual \n";
}

	//print_r($arrOut);
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
?>