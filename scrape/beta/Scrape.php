<?php
ini_set('user_agent', "Mozilla/5.1 (compatible; Googlebot/2.1; +http://www.google.com/bot.html");
include('library/simple_html_dom.php');
ini_set('memory_limit', '128M');
$s = new Scrape;
$s->get(date("m/d/Y"),5,0);

class Scrape{

	var $debug = 0;
	var $scoresURL='http://www.nhl.com/ice/scores.htm?date=';

	function get($start, $days=0, $debug=1){
		$this->debug = $debug;
		$time = strtotime( $start );
		if(!$time) {
			die('not a date');
		}
		//get first date
		$gameDate = date( 'm/d/Y', $time );
		$this->parseDate($gameDate);

		//loop through rest of dates
		if($days){
			for($i=$days;$i>0;$i--){
				$gamedate = date("m/d/Y", strtotime($start)+($i*86400));
				//$gamedate = date( 'm/d/Y', $timestamp );
				//echo $gamedate;
				//var_dump($gameDate);
				$this->parseDate($gamedate);
			}
		}
			//var_dump($gameDate);
	}
	//old function need to rejig
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
	//connect to the nhl.com/scores page for a given date and return an array of gamedata
	function parseDate($date){
		$gameType = 3;
		$season = '20112012';

		$dbtime = strtotime( $date );
		$dbdate = date("Y-m-d", $dbtime);
		//TODO move this to its own function
		$db_user="27_ssf432";
		$db_pass="l12321l";
		$database =	"27_ssf";
		$host = 'mysql.statsmachine.ca';
		//$host = '127.0.0.1';
		$mysql_id =	mysql_connect($host, $db_user, $db_pass) or die("Cannot connect to mySQL");
		mysql_select_db($database,	$mysql_id) or die("Can't find database:	".$database);
		$url = $this->scoresURL.$date;
		/*
		$feed =  $this->_scrape($url);
		preg_match_all('#<div class="sbGame">(.*?)</tr>#', $feed, $gameChunk, PREG_SET_ORDER); 
		*/
		$html = file_get_html($url);

		
		foreach($html->find('div[class=sbGame]') as $article) {
			$a = array();
			//gameid
			$link = $article->find('div[class=gcLinks]',1);
			$anchor = $link->find('a',1)->href;
			$p = strpos($anchor, '=');
			$a['id'] = $season.substr($anchor, ($p+6));
			//gethome
			$awayTeam = $article->find('td[class=team]',0);
			$a['away'] = $awayTeam->find('a',0)->plaintext;

			//getAway
			$homeTeam = $article->find('td[class=team]',1);
			$a['home'] = $homeTeam->find('a',0)->plaintext;
				
			$a['time'] = $article->find('th',0)->plaintext;

			$sql="REPLACE INTO	nhl_schedual (id, date, time, gametype, away_team, home_team,season,full_id) VALUES ('$a[id]', '$dbdate', '$a[time]', '$gameType','$a[away]','$a[home]','$season','$gameID2')";

    		$z++;
    		if(!$this->debug){
				echo "inserting... \n $sql \n";
    			mysql_db_query($database, $sql) or die("A MySQL error has occurred.<br />Your Query:	" .	$sql . "<br/> Error: (" . mysql_errno() . ") "	. mysql_error());  
    		}
			else{var_dump($sql);}
		}

	}

}