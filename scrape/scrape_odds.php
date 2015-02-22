<?php  
error_reporting(E_ALL & ~E_NOTICE);

//Start, End, Debug, Gametype//
//include_once 'functions.php';
require_once 'Cache/Lite.php'; 

/*************************
Scrape games for the MLB
*************************/
class Scrape{
	function getPinnacleOdds()
	{
		$this->dbConnect();
		$sql = "SELECT DISTINCT pinnacle_feedtime FROM new_odds ORDER BY pinnacle_feedtime DESC";
		$result = mysql_query($sql);
		if(count($result)>=1)
		{	//echo $sql;
			while ($row = mysql_fetch_assoc($result)) {
				
				$prevFeed = $row['pinnacle_feedtime'];
			}
		}
		$xml = simplexml_load_file("http://xml.pinnaclesports.com/pinnacleFeed.aspx?sportType=Hockey");
		//&last=$prevFeed&contest=no
		// add this back in when working ?sportType=Hockey&last=$prevFeed&contest=no
		//echo "http://xml.pinnaclesports.com/pinnacleFeed.aspx?sportType=Hockey&last=$prevFeed&contest=no";
		$hometeam = '';
		$awayteam = '';
		$gamenumber = 0;
		$timestamp = $xml->PinnacleFeedTime;
		$date = date("Y-m-d");
		foreach ($xml->events->event as $event) {
			if ($event->league == 'NHL OT Incl'){// && date("y-m-d",strtotime("-5 hours",strtotime($event->event_datetimeGMT)))==date("y-m-d")){
				$gamenumber = $event->gamenumber;
				$date = date("y-m-d",strtotime("-5 hours",strtotime($event->event_datetimeGMT)));
				$moneyline_visiting = '';
				$moneyline_home = '';
				$spread_visiting = '';
				$spread_home = '';
				$spread_adjust_visiting = '';
				$spread_adjust_home = '';
				$total_points = '';
				$total_over_adjust = '';
				$total_under_adjust = '';
				$writeOdds = false;
				foreach ($event->participants->participant as $participant){
					if ($participant->visiting_home_draw=='Visiting')
					{
						$awayteam = $participant->participant_name;
					}
					else 
					{
						$hometeam = $participant->participant_name;
					}
				}
				foreach ($event->periods->period as $period)
				{
					if ($period->period_number == '0'){
						$moneyline_visiting = $period->moneyline->moneyline_visiting;
						$moneyline_home = $period->moneyline->moneyline_home;
						$spread_visiting = $period->spread->spread_visiting;
						$spread_home = $period->spread->spread_home;
						$spread_adjust_visiting = $period->spread->spread_adjust_visiting;
						$spread_adjust_home = $period->spread->spread_adjust_home;
						$total_points = $period->total->total_points;
						$total_over_adjust = $period->total->over_adjust;
						$total_under_adjust = $period->total->under_adjust;
						$writeOdds = true;
					}
				}
				if ($writeOdds == true){
					$sql = "REPLACE INTO new_odds (pinnacle_game_id, game_date, home_team_name, away_team_name, home_team_moneyline, away_team_moneyline, home_team_spread, away_team_spread, home_team_spread_adjust, away_team_spread_adjust, total_points, total_over_adjust, total_under_adjust, pinnacle_feedtime, sportsbook_name)
					VALUES ('$gamenumber', '$date', '$hometeam', '$awayteam', '$moneyline_home','$moneyline_visiting','$spread_home','$spread_visiting','$spread_adjust_home','$spread_adjust_visiting','$total_points','$total_over_adjust','$total_under_adjust','$timestamp','Pinnacle')";
					$query = mysql_query($sql);
				}
				echo "$sql<br />";
			}
		}
		//print_r($xml);
	}	
	function getBetolineOdds()
	{
		$this->dbConnect();
		$a = array(
		'Washington Capitals' => '01',
		'Pittsburgh Penguins' => '02',
		'Tampa Bay Lightning' => '03',
		'Ottawa Senators' => '04',
		'New York Rangers' => '05',
		'Detroit Red Wings' => '06',
		'Toronto Maple Leafs' => '07',
		'Colorado Avalanche' => '08',
		'Boston Bruins' => '09',
		'Calgary Flames' => '10',
		'Vancouver Canucks' => '11',
		'San Jose Sharks' => '12',
		'Anaheim Ducks' => '13',
		'New Jersey Devils' => '14',
		'New York Islanders' => '15',
		'Edmonton Oilers' => '16',
		'Chicago Blackhawks' => '17',
		'Carolina Hurricanes' => '18',
		'Florida Panthers' => '19',
		'Buffalo Sabres' => '20',
		'Montreal Canadiens' => '21',
		'Winnipeg Jets' => '22',
		'St Louis Blues' => '23',
		'Nashville Predators' => '24',
		'Dallas Stars' => '25',
		'Columbus Blue Jackets' => '26',
		'Philadelphia Flyers' => '27',
		'Minnesota Wild' => '28',
		'Phoenix Coyotes' => '29',
		'Los Angeles Kings'=> '30'
		);
		$xml = simplexml_load_file("http://livelines.betonline.com/sys/LineXML/LiveLineObjXml.asp?sport=Hockey");
		$hometeam = '';
		$awayteam = '';
		$gamenumber = 0;
		$date = date("Y-m-d");
		$sportsBook = "Bet Online";
		$timestamp = 0;
		foreach ($xml->event as $event) {
			if ($event->league == "NHL Hockey")
			{
				$gamenumber = "";
				$gamenumber .= date("ymd",strtotime("-5 hours",strtotime($event->event_datetimeGMT)));
				$date = date("y-m-d",strtotime("-5 hours",strtotime($event->event_datetimeGMT)));
				$moneyline_visiting = '';
				$moneyline_home = '';
				$spread_visiting = '';
				$spread_home = '';
				$spread_adjust_visiting = '';
				$spread_adjust_home = '';
				$total_points = '';
				$total_over_adjust = '';
				$total_under_adjust = '';
				$writeOdds = false;
				if ($event->period->period_description == "Game")
				{
					foreach ($event->participant as $participant)
					{
						$writeOdds = true;
						if ($participant->visiting_home_draw == "Visiting")
						{
							$awayteam = $participant->participant_name;
							$moneyline_visiting = $participant->odds->moneyline;
							$gamenumber .= $a["$awayteam"];
							//for St Louis to  St. Louis
							if ($awayteam == "St Louis Blues")
							{
								$awayteam = "St. Louis Blues";
							}
						}
						else
						{
							$hometeam = $participant->participant_name;
							$moneyline_home = $participant->odds->moneyline;
							$gamenumber .= $a["$hometeam"];
							//for St Louis to  St. Louis
							
							if ($hometeam == "St Louis Blues")
							{
								$hometeam = "St. Louis Blues";
							}
						}
					}
					$spread_visiting = $event->period->spread->spread_visiting;
					$spread_home = $event->period->spread->spread_home;
					$spread_adjust_visiting = $event->period->spread->spread_adjust_visiting;
					$spread_adjust_home = $event->period->spread->spread_adjust_home;
					$total_points = $event->period->total->total_points;
					$total_over_adjust = $event->period->total->over_adjust;
					$total_under_adjust = $event->period->total->under_adjust;

					$sql = "REPLACE INTO new_odds (pinnacle_game_id, game_date, home_team_name, away_team_name, home_team_moneyline, away_team_moneyline, home_team_spread, away_team_spread, home_team_spread_adjust, away_team_spread_adjust, total_points, total_over_adjust, total_under_adjust, pinnacle_feedtime, sportsbook_name)
					VALUES ('$gamenumber', '$date', '$hometeam', '$awayteam', '$moneyline_home','$moneyline_visiting','$spread_home','$spread_visiting','$spread_adjust_home','$spread_adjust_visiting','$total_points','$total_over_adjust','$total_under_adjust','$timestamp','$sportsBook')";
					$query = mysql_query($sql);
					echo "$sql <br />";
				}
			}
		}
		//print_r($xml);
	}
	function getBetolineOddsMLB()
	{
		$xml = simplexml_load_file("http://livelines.betonline.com/sys/LineXML/LiveLineObjXml.asp?sport=Baseball");
		$hometeam = '';
		$awayteam = '';
		$gamenumber = 0;
		$date = date("Y-m-d");
		$sportsBook = "Bet Online";
		$timestamp = 0;
		foreach ($xml->event as $event) {
			if ($event->league == "MLB Baseball")
			{
				$gamenumber = "";
				$gamenumber .= date("ymd",strtotime("-5 hours",strtotime($event->event_datetimeGMT)));
				$date = date("y-m-d",strtotime("-5 hours",strtotime($event->event_datetimeGMT)));
				$moneyline_visiting = '';
				$moneyline_home = '';
				$spread_visiting = '';
				$spread_home = '';
				$spread_adjust_visiting = '';
				$spread_adjust_home = '';
				$total_points = '';
				$total_over_adjust = '';
				$total_under_adjust = '';
				$writeOdds = false;
				if ($event->period->period_description == "Game")
				{
					foreach ($event->participant as $participant)
					{
						$writeOdds = true;
						if ($participant->visiting_home_draw == "Visiting")
						{
							$awayteam = $participant->participant_name;
							$away_team_id = $this->getBOMLBidfromName($awayteam);
							$moneyline_visiting = $participant->odds->moneyline;
							$gamenumber .= $away_team_id;
						}
						else
						{
							$hometeam = $participant->participant_name;
							$home_team_id = $this->getBOMLBidfromName($hometeam);
							$moneyline_home = $participant->odds->moneyline;
							$gamenumber .= $home_team_id;

						}
					}
					$spread_visiting = $event->period->spread->spread_visiting;
					$spread_home = $event->period->spread->spread_home;
					$spread_adjust_visiting = $event->period->spread->spread_adjust_visiting;
					$spread_adjust_home = $event->period->spread->spread_adjust_home;
					$total_points = $event->period->total->total_points;
					$total_over_adjust = $event->period->total->over_adjust;
					$total_under_adjust = $event->period->total->under_adjust;

					$sql = "REPLACE INTO new_odds_MLB (pinnacle_game_id, game_date, home_team_id, away_team_id, home_team_moneyline, away_team_moneyline, home_team_spread, away_team_spread, home_team_spread_adjust, away_team_spread_adjust, total_points, total_over_adjust, total_under_adjust, pinnacle_feedtime, sportsbook_name)
					VALUES ('$gamenumber', '$date', '$home_team_id', '$away_team_id', '$moneyline_home','$moneyline_visiting','$spread_home','$spread_visiting','$spread_adjust_home','$spread_adjust_visiting','$total_points','$total_over_adjust','$total_under_adjust','$timestamp','$sportsBook')";
					$query = mysql_query($sql);
					echo "$sql <br />";
				}
			}
		}
	}
	function getBOMLBidfromName($teamName)
	{
		$a = array(
		'Baltimore Orioles' => 'Baltimore Orioles',
		'Tampa Bay Rays' => 'Tampa Bay Rays',
		'Toronto Blue Jays' => 'Toronto Blue Jays',
		'Boston Red Sox' => 'Boston Red Sox',
		'New York Yankees' => 'New York Yankees',
		'Detroit Tigers' => 'Detroit Tigers',
		'Chicago White Sox' => 'Chicago White Sox',
		'Kansas City Royals' => 'Kansas City Royals',
		'Cleveland Indians' => 'Cleveland Indians',
		'Minnesota Twins' => 'Minnesota Twins',
		'Seattle Mariners' => 'Seattle Mariners',
		'Los Angeles Angels' => 'Los Angeles Angels',
		'Texas Rangers' => 'Texas Rangers',
		'Oakland Athletics' => 'Oakland Athletics',
		'New York Mets' => 'New York Mets',
		'Washington Nationals' => 'Washington Nationals',
		'Philadelphia Phillies' => 'Philadelphia Phillies',
		'Miami Marlins' => 'Miami Marlins',
		'Atlanta Braves' => 'Atlanta Braves',
		'St. Louis Cardinals' => 'St. Louis Cardinals',
		'Cincinnati Reds' => 'Cincinnati Reds',
		'Houston Astros' => 'Houston Astros',
		'Milwaukee Brewers' => 'Milwaukee Brewers',
		'Pittsburgh Pirates' => 'Pittsburgh Pirates',
		'Chicago Cubs' => 'Chicago Cubs',
		'Los Angeles Dodgers' => 'Los Angeles Dodgers',
		'Arizona Diamondbacks' => 'Arizona D-backs',
		'Colorado Rockies' => 'Colorado Rockies',
		'San Francisco Giants' => 'San Francisco Giants',
		'San Diego Padres' => 'San Diego Padres',
		);
		$teamName = $a["$teamName"];
		$sql = "Select * from mlb_team 
				WHERE concat(location,' ',team_name) like '$teamName'";
		//echo $sql;
		$result = mysql_query($sql);
		if(count($result)>=1)
		{	//echo $sql;
			while ($row = mysql_fetch_assoc($result)) {
				
				$teamID = $row['team_id'];
			}
		}
		return $teamID;
	}	
	function getPinMLBidfromName($teamName)
	{
		$a = array(
		'Baltimore Orioles' => 'Baltimore Orioles',
		'Tampa Bay Rays' => 'Tampa Bay Rays',
		'Toronto Blue Jays' => 'Toronto Blue Jays',
		'Boston Red Sox' => 'Boston Red Sox',
		'New York Yankees' => 'New York Yankees',
		'Detroit Tigers' => 'Detroit Tigers',
		'Chicago White Sox' => 'Chicago White Sox',
		'Kansas City Royals' => 'Kansas City Royals',
		'Cleveland Indians' => 'Cleveland Indians',
		'Minnesota Twins' => 'Minnesota Twins',
		'Seattle Mariners' => 'Seattle Mariners',
		'LAA Angels' => 'Los Angeles Angels',
		'Texas Rangers' => 'Texas Rangers',
		'Oakland Athletics' => 'Oakland Athletics',
		'New York Mets' => 'New York Mets',
		'Washington Nationals' => 'Washington Nationals',
		'Philadelphia Phillies' => 'Philadelphia Phillies',
		'Miami Marlins' => 'Miami Marlins',
		'Atlanta Braves' => 'Atlanta Braves',
		'St. Louis Cardinals' => 'St. Louis Cardinals',
		'Cincinnati Reds' => 'Cincinnati Reds',
		'Houston Astros' => 'Houston Astros',
		'Milwaukee Brewers' => 'Milwaukee Brewers',
		'Pittsburgh Pirates' => 'Pittsburgh Pirates',
		'Chicago Cubs' => 'Chicago Cubs',
		'Los Angeles Dodgers' => 'Los Angeles Dodgers',
		'Arizona Diamondbacks' => 'Arizona D-backs',
		'Colorado Rockies' => 'Colorado Rockies',
		'San Francisco Giants' => 'San Francisco Giants',
		'San Diego Padres' => 'San Diego Padres',
		);
		$teamName = $a["$teamName"];
		$sql = "Select * from mlb_team 
				WHERE concat(location,' ',team_name) like '$teamName'";
		//echo $sql;
		$result = mysql_query($sql);
		if(count($result)>=1)
		{	//echo $sql;
			while ($row = mysql_fetch_assoc($result)) {
				
				$teamID = $row['team_id'];
			}
		}
		return $teamID;
	}


	function getPinnacleOddsMLB()
	{
		$this->dbConnect();
		$sql = "SELECT DISTINCT pinnacle_feedtime FROM new_odds_MLB WHERE sportsbook_name like 'Pinnacle' ORDER BY pinnacle_feedtime DESC";
		$result = mysql_query($sql);
		if(count($result)>=1)
		{	//echo $sql;
			while ($row = mysql_fetch_assoc($result)) {
				
				$prevFeed = $row['pinnacle_feedtime'];
			}
		}
		$xml = simplexml_load_file("http://xml.pinnaclesports.com/pinnacleFeed.aspx?sportType=Baseball");
		//&last=$prevFeed&contest=no
		//echo "http://xml.pinnaclesports.com/pinnacleFeed.aspx?sportType=Baseball&last=$prevFeed&contest=no";
		//print_r($xml);
		echo "http://xml.pinnaclesports.com/pinnacleFeed.aspx?sportType=Baseball";
		$hometeam = '';
		$awayteam = '';
		$gamenumber = 0;
		$timestamp = $xml->PinnacleFeedTime;
		$date = date("Y-m-d");
		foreach ($xml->events->event as $event) {
			if ($event->league == 'MLB'){// && date("y-m-d",strtotime("-5 hours",strtotime($event->event_datetimeGMT)))==date("y-m-d")){
				$gamenumber = $event->gamenumber;
				//echo $gamenumber;
				$date = date("y-m-d",strtotime("-5 hours",strtotime($event->event_datetimeGMT)));
				$moneyline_visiting = '';
				$moneyline_home = '';
				$spread_visiting = '';
				$spread_home = '';
				$spread_adjust_visiting = '';
				$spread_adjust_home = '';
				$total_points = '';
				$total_over_adjust = '';
				$total_under_adjust = '';
				$writeOdds = false;
				foreach ($event->participants->participant as $participant){
					if ($participant->visiting_home_draw=='Visiting')
					{
						$awayteam = $participant->participant_name;
						$awayID = $this->getPinMLBidfromName($awayteam);
					}
					else 
					{
						$hometeam = $participant->participant_name;
						$homeID = $this->getPinMLBidfromName($hometeam);
					}
				}
				foreach ($event->periods->period as $period)
				{
					if ($period->period_number == '0'){
						$moneyline_visiting = $period->moneyline->moneyline_visiting;
						$moneyline_home = $period->moneyline->moneyline_home;
						$spread_visiting = $period->spread->spread_visiting;
						$spread_home = $period->spread->spread_home;
						$spread_adjust_visiting = $period->spread->spread_adjust_visiting;
						$spread_adjust_home = $period->spread->spread_adjust_home;
						$total_points = $period->total->total_points;
						$total_over_adjust = $period->total->over_adjust;
						$total_under_adjust = $period->total->under_adjust;
						$writeOdds = true;
					}
				}
				if ($writeOdds == true){
					$sql = "REPLACE INTO new_odds_MLB (pinnacle_game_id, game_date, home_team_id, away_team_id, home_team_moneyline, away_team_moneyline, home_team_spread, away_team_spread, home_team_spread_adjust, away_team_spread_adjust, total_points, total_over_adjust, total_under_adjust, pinnacle_feedtime, sportsbook_name)
					VALUES ('$gamenumber', '$date', '$homeID', '$awayID', '$moneyline_home','$moneyline_visiting','$spread_home','$spread_visiting','$spread_adjust_home','$spread_adjust_visiting','$total_points','$total_over_adjust','$total_under_adjust','$timestamp','Pinnacle')";
					$query = mysql_query($sql);
				}
				echo "$sql <br />";
			}
		}
	}
	
	function getWilliamHillOdds()
	{
		$xml = simplexml_load_file("http://whdn.williamhill.com/pricefeed/openbet_cdn?action=template&template=getHierarchyByMarketType&classId=32&marketSort=HH&filterBIR=N");
		$xmltotals = simplexml_load_file("http://whdn.williamhill.com/pricefeed/openbet_cdn?action=template&template=getHierarchyByMarketType&classId=32&marketSort=HL&filterBIR=N");
		$xmlpuckline = simplexml_load_file("http://whdn.williamhill.com/pricefeed/openbet_cdn?action=template&template=getHierarchyByMarketType&classId=32&marketSort=WH&filterBIR=N");
		$hometeam = '';
		$awayteam = '';
		$gamenumber = 0;
		$date = date("Y-m-d");
		$sportsBook = "William Hill";
		$timestamp = 0;
		$arr[] = array();
		//print_r($xml);
		//echo $xml->response;
		
		
		//first get money line
		foreach ($xml->response->williamhill->class->type->market as $market) {
				
			$gamenumber = '';
			$hometeam ='';
			$awayteam ='';
			$date = '';
			$moneyline_visiting = '';
			$moneyline_home = '';
			$spread_visiting = '';
			$spread_home = '';
			$spread_adjust_visiting = '';
			$spread_adjust_home = '';
			$total_points = '';
			$total_over_adjust = '';
			$total_under_adjust = '';
			$moneyLineBool = false;
			foreach ($market[0]->attributes() as $a => $b)
			{
				//check to see if it is money line
					
				switch ($a){
					case "name":
						if (strpos($b,"Money Line") !== false)
						{
							$moneyLineBool = true;
						}
						break;
					case "id":
						$gamenumber = "$b";
						break;
					case "date":
						$date = "$b";
						break;
					default:
					break;
				}
			}
			$homeTeamBool = true;
			foreach ($market->participant as $participant)
			{
				foreach ($participant[0]->attributes() as $a => $b)
				{
					switch ($a){
						case "name":
							if ($homeTeamBool == true)
							{
								$hometeam = "$b";
							}
							else
							{
								$awayteam = "$b";
							}
							break;
						case "odds":
							if ($homeTeamBool == true)
							{
								$moneyline_home = "$b";
								$moneyline_home = $this->convert_american_odds($moneyline_home);
							}
							else
							{
								$moneyline_away = "$b";
								$moneyline_away = $this->convert_american_odds($moneyline_away);
							}
							break;
						default:
							break;
					}
				}
				$homeTeamBool = false;
			}
			if ($moneyLineBool)
			{
				
				$arr["$hometeam$date"]["gamenumber"] = $gamenumber;
				$arr["$hometeam$date"]["hometeam"] = $hometeam;
				$arr["$hometeam$date"]["awayteam"] = $awayteam;
				$arr["$hometeam$date"]["moneyline_home"] = $moneyline_home;
				$arr["$hometeam$date"]["moneyline_visiting"] = $moneyline_away;
				$arr["$hometeam$date"]["date"] = $date;
				//print_r($arr);
				
				//echo "$gamenumber:$hometeam:$awayteam:$moneyline_home:$moneyline_away";
			}
		}
		foreach ($xmltotals->response->williamhill->class->type->market as $market)
		{
			$gamenumber = '';
			$date = '';
			$total_points = '';
			$total_over_adjust = '';
			$total_under_adjust = '';
			$hometeam = '';
			$awayteam = '';
			$gameTotalBool = false;
			foreach ($market[0]->attributes() as $a => $b)
			{
				//check to see if it is money line
					
				switch ($a){
					case "name":
						if (strpos($b,"Total Goals") !== false)
						{
							$gameTotalBool = true;
							$awayteam = trim(substr($b,0,strpos($b,'@')-1));
							$hometeam = trim(substr($b,strpos($b,'@')+1,strpos($b,'-')-1 - strpos($b,'@')-1));
							//echo "$awayteam @ $hometeam";
						}
						break;
					case "id":
						$gamenumber = "$b";
						break;
					case "date":
						$date = "$b";
						break;
					default:
					break;
				}
			}
			$underBool = true;
			foreach ($market->participant as $participant)
			{
				foreach ($participant[0]->attributes() as $a => $b)
				{
					switch ($a){
						case "odds":
							if ($underBool == true)
							{
								$total_under_adjust = "$b";
								$total_under_adjust = $this->convert_american_odds($total_under_adjust);
								//echo "testig:$total_under_adjust";
							}
							else
							{
								$total_over_adjust = "$b";
								$total_over_adjust = $this->convert_american_odds($total_over_adjust);
							}
							break;
						case "handicap":
							$total_points = "$b";
							break;
						default:
							break;
					}
				}
				$underBool = false;
			}
			//echo "$hometeam$date totalunderadjust $total_under_adjust";
			$arr["$hometeam$date"]["total_under_adjust"] = $total_under_adjust;
			$arr["$hometeam$date"]["total_over_adjust"] = $total_over_adjust;
			$arr["$hometeam$date"]["total_points"] = $total_points;
		}
		foreach ($xmlpuckline->response->williamhill->class->type->market as $market)
		{
			$gamenumber = '';
			$date = '';
			$spread_visiting = '';
			$spread_home = '';
			$spread_adjust_visiting = '';
			$spread_adjust_home = '';
			$hometeam = '';
			$awayteam = '';
			$gameTotalBool = false;
			foreach ($market[0]->attributes() as $a => $b)
			{
				//check to see if it is money line
					
				switch ($a){
					case "name":
						if (strpos($b,"Puck Line") !== false)
						{
							$gameTotalBool = true;
							$awayteam = trim(substr($b,0,strpos($b,'@')-1));
							$hometeam = trim(substr($b,strpos($b,'@')+1,strpos($b,'-')-1 - strpos($b,'@')-1));
							//echo "$awayteam @ $hometeam";
						}
						break;
					case "id":
						$gamenumber = "$b";
						break;
					case "date":
						$date = "$b";
						break;
					default:
					break;
				}
			}
			$underBool = true;
			foreach ($market->participant as $participant)
			{
				foreach ($participant[0]->attributes() as $a => $b)
				{
					switch ($a){
						case "odds":
							if ($underBool == true)
							{
								$spread_adjust_home = "$b";
								$spread_adjust_home = $this->convert_american_odds($spread_adjust_home);
								//echo "testig:$total_under_adjust";
							}
							else
							{
								$spread_adjust_visiting = "$b";
								$spread_adjust_visiting= $this->convert_american_odds($spread_adjust_visiting);
							}
							break;
						case "handicap":
							if ($underBool == true)
							{
								$spread_home = "$b";
								//echo "testig:$total_under_adjust";
							}
							else
							{
								$spread_visiting = "$b";
							}
							break;
						default:
							break;
					}
				}
				$underBool = false;
			}
			//echo "$hometeam$date totalunderadjust $total_under_adjust";
			$arr["$hometeam$date"]["spread_adjust_home"] = $spread_adjust_home;
			$arr["$hometeam$date"]["spread_adjust_visiting"] = $spread_adjust_visiting;
			$arr["$hometeam$date"]["spread_visiting"] = $spread_visiting;
			$arr["$hometeam$date"]["spread_home"] = $spread_home;
		}

		//print_r($arr);
		foreach ($arr as $game)
		{
			foreach ($game as $key => $value)
			{
				switch ($key)
				{
					case "gamenumber":
						$gamenumber = $value;
						break;
					case "date":
						$date = $value;
						break;
					case "hometeam":
						$hometeam = $value;
						break;
					case "awayteam":
						$awayteam = $value;
						break;
					case "moneyline_home":
						$moneyline_home = $value;
						break;
					case "moneyline_visiting":
						$moneyline_visiting = $value;
						break;
					case "spread_home":
						$spread_home = $value;
						break;
					case "spread_visiting":
						$spread_visiting = $value;
						break;
					case "spread_adjust_home":
						$spread_adjust_home = $value;
						break;
					case "spread_adjust_visiting":
						$spread_adjust_visiting = $value;
						break;
					case "total_points":
						$total_points = $value;
						break;
					case "total_over_adjust":
						$total_over_adjust = $value;
						break;
					case "total_under_adjust":
						$total_under_adjust = $value;
						break;
					default:
						break;
				}
			}
		$sql = "REPLACE INTO new_odds (pinnacle_game_id, game_date, home_team_name, away_team_name, home_team_moneyline, away_team_moneyline, home_team_spread, away_team_spread, home_team_spread_adjust, away_team_spread_adjust, total_points, total_over_adjust, total_under_adjust, pinnacle_feedtime, sportsbook_name)
				VALUES ('$gamenumber', '$date', '$hometeam', '$awayteam', '$moneyline_home','$moneyline_visiting','$spread_home','$spread_visiting','$spread_adjust_home','$spread_adjust_visiting','$total_points','$total_over_adjust','$total_under_adjust','0','$sportsBook')";
				$query = mysql_query($sql);
				echo "$sql <br />";

		}
	}

	function convert_american_odds($odds)
	{
		//echo ($odds);
		if (strpos($odds,"/") !== false)
		{
			$numerator = trim(substr($odds,0,strpos($odds,'/')));
			//echo $numerator;
			$denominator = trim(substr($odds,strpos($odds,'/')+1,strlen($odds)-strpos($odds,'/')));
			//echo "den:$denominator";
			$amerOdds = (float)$numerator / (float)$denominator;
			//echo "$amerOdds: ";
			if ($amerOdds < 1)
			{
				$amerOdds = -100/$amerOdds;
			}
			else
			{
				$amerOdds *= 100;
			}
			//echo "$amerOdds<br />";
		}
		return $amerOdds;
	}

	function dbConnect(){
		$this->db_user="27_ssf432";
		$this->db_pass="l12321l";
		$this->database="27_ssf";
		$this->mysql_id =	mysql_connect('mysql.dev.fashion-public-relations.com', $this->db_user, $this->db_pass) or die("Cannot connect to mySQL");
		mysql_select_db($this->database,	$this->mysql_id) or die("Can't find database:	".$this->database);
	}
}
?>
