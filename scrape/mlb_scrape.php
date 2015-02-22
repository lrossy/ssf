<?php  
error_reporting(E_ALL & ~E_NOTICE);

//Start, End, Debug, Gametype//
//include_once 'functions.php';
require_once 'Cache/Lite.php'; 

/*************************
Scrape games for the MLB
*************************/
class Scrape{
	function scrape_day($dateURL, $date)
	{

		$home_score = 0;
		$away_score = 0;
		$this->dbConnect();
		$xml = simplexml_load_file($dateURL);
		foreach ($xml->game as $game)
		{
			foreach ($game[0]->attributes() as $a => $b)
			{
				switch ($a){
					case "id":
						$game_id = $b;
						//echo $b;
						break;
					case "away_team_city":
						$away_city = $b;
						break;
					case "away_team_name":
						$away_name = $b;
						break;
					case "home_team_city":
						$home_city = $b;
						break;
					case "home_team_name":
						$home_name = $b;
						break;
					default:
						break;
				}
			}
			$postponedBool = false;
			foreach ($game->status[0]->attributes() as $a => $b)
			{
				switch ($a){
					case "status":
						$game_status = $b;
						echo $b;
						if ($b=="Postponed" || $b=="Cancelled")
						{
							$postponedBool = true;
						}
						break;
					case "inning":
						$game_innings = $b;
						break;
					default:
						break;
				}
			}
			if ($game_status == "Preview")
			{
				$home_team_id = $this->get_team_id_from_name($home_name);
				$away_team_id = $this->get_team_id_from_name($away_name);
				$sql = "REPLACE INTO mlb_schedule (id,home_team_id,away_team_id,date) values ('$game_id','$home_team_id','$away_team_id','$date');";
				echo $sql;
				$result = mysql_query($sql);
			}
			else
			{
				if ($postponedBool == false)
				{
					foreach ($game->linescore->r[0]->attributes() as $a => $b)
					{
						switch ($a){
							case "away":
								$away_score = $b;
								//echo $away_name.":".$away_score."<br />";
								break;
							case "home":
								$home_score = $b;
								//echo $home_name.":".$home_score."<br />";
								break;
							default:
								break;
						}
					}
					foreach ($game->linescore->h[0]->attributes() as $a => $b)
					{
						switch ($a){
							case "away":
								$away_hits = $b;
								//echo $away_name.":".$away_score."<br />";
								break;
							case "home":
								$home_hits = $b;
								//echo $home_name.":".$home_score."<br />";
								break;
							default:
								break;
						}
					}
					foreach ($game->linescore->e[0]->attributes() as $a => $b)
					{
						switch ($a){
							case "away":
								$away_errors = $b;
								//echo $away_name.":".$away_score."<br />";
								break;
							case "home":
								$home_errors = $b;
								//echo $home_name.":".$home_score."<br />";
								break;
							default:
								break;
						}
					}
					$home_team_id = $this->get_team_id_from_name($home_name);
					$away_team_id = $this->get_team_id_from_name($away_name);
					if ((int)$away_score > (int)$home_score)
					{
						$winner = $away_team_id;
						//echo "winner=".$winner;
					}
					elseif ((int)$home_score > (int)$away_score)
					{
						$winner = $home_team_id;
					}
					$sql = "REPLACE INTO mlb_game (id,home_score,away_score,game_date,number_of_innings,home_team_id,away_team_id,gametype,isFinal,winner,home_hits,away_hits,home_errors,away_errors) values ('$game_id','$home_score','$away_score','$date','$game_innings','$home_team_id','$away_team_id','2','$game_status','$winner','$home_hits','$away_hits','$home_errors','$away_errors')";
					echo $sql;
					$result = mysql_query($sql);
				}
			}
		}
	}
	function get_team_id_from_name($teamName)
	{
		$sql = "SELECT team_id from mlb_team where team_name like '$teamName'";
		$result = mysql_query($sql);
		if(count($result)>=1)
		{	//echo $sql;
			while ($row = mysql_fetch_assoc($result)) {
				
				$teamId = $row['team_id'];
			}
		}
		return $teamId;
	}
	
	
	function create_master_scoreboard_url_from_date($date)
	{
		$prefix = "http://gd2.mlb.com/components/game/mlb/";
		$suffix = "/master_scoreboard.xml";
		
		$yearString = "year_".date("Y", strtotime($date));
		$monthString = "month_".date("m", strtotime($date));
		$dayString = "day_".date("d", strtotime($date));
		
		$midString = $yearString."/".$monthString."/".$dayString;
		
		return $prefix.$midString.$suffix;
	}
	function scrape_date_range($startDate, $endDate)
	{
		$start_ts = strtotime($startDate);
		$end_ts = strtotime($endDate);
		$diff = $end_ts - $start_ts;
		$days = round($diff / 86400);
		for ($i = 0; $i < $days; $i++)
		{
			$curDate = date("Y-m-d",$start_ts + ($i*86400));
			$curURL = $this->create_master_scoreboard_url_from_date($curDate);
			//echo "$curDate:$curURL<br />";
			$this->scrape_day($curURL,$curDate);
		}
	}
	 
	function dbConnect(){
		$this->db_user="27_ssf432";
		$this->db_pass="l12321l";
		$this->database="27_ssf";
		$this->mysql_id =	mysql_connect('mysql.statsmachine.ca', $this->db_user, $this->db_pass) or die("Cannot connect to mySQL");
		mysql_select_db($this->database,	$this->mysql_id) or die("Can't find database:	".$this->database);
	}
}
?>
