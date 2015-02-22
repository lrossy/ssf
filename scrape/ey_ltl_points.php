<?php  
error_reporting(E_ALL & ~E_NOTICE);

//Start, End, Debug, Gametype//
//include_once 'functions.php';
require_once 'Cache/Lite.php'; 

/*************************
Output standings for EY LTL 2012
*************************/
class Output{
	function get_points_by_player()
	{
		$this->dbConnect();
		mysql_query("SET @currentSum=0;");
		mysql_query("SET @prevEYTeam='temp';");
		mysql_query("SET @prevtotals=0;");
		mysql_query("SET @place=0;");
		$sql = "SELECT *, 
				IF(@prevtotals <> totalPoints, @place:=@place+1,@place) AS ranking,
				@prevtotals:=totalPoints AS prevTotPoints
				FROM (
					SELECT totals.ey_team_name,totalPoints,player_name, nhl_id, points, location
					 FROM
						(
						SELECT ey_team_name, MAX(currentTotal) AS totalPoints
						FROM (	
							SELECT ey.*,points,
							IF (@prevEYTeam NOT LIKE ey_team_name, @currentSum:=points, @currentSum:=@currentSum+points) AS currentTotal,
							IF (@prevEYTeam NOT LIKE ey_team_name, @prevEYTeam:=ey_team_name, @prevEYTeam:=@prevEYTeam) AS prevTeam
							FROM ey_ltl_2012 ey
							INNER JOIN (
								SELECT up.player_name, up.nhl_id, SUM(points) AS points FROM (
									SELECT DISTINCT player_name, nhl_id
									FROM ey_ltl_2012) AS up
								INNER JOIN new_player pl ON pl.nhl_id = up.nhl_id
								INNER JOIN new_event_summary es ON es.player_id = pl.id
								AND LEFT(es.id,9) = 201120122
								GROUP BY up.nhl_id
								) AS points ON points.nhl_id = ey.nhl_id
							) AS points
						GROUP BY ey_team_name
						ORDER BY totalPoints DESC, ey_team_name
						) AS totals
					INNER JOIN 
						(
							SELECT ey.*,points,points.location,
							IF (@prevEYTeam NOT LIKE ey_team_name, @currentSum:=points, @currentSum:=@currentSum+points) AS currentTotal,
							IF (@prevEYTeam NOT LIKE ey_team_name, @prevEYTeam:=ey_team_name, @prevEYTeam:=@prevEYTeam) AS prevTeam
							FROM ey_ltl_2012 ey
							INNER JOIN (
								SELECT up.player_name, up.nhl_id, nt.location, SUM(points) AS points FROM (
									SELECT DISTINCT player_name, nhl_id
									FROM ey_ltl_2012) AS up
								INNER JOIN new_player pl ON pl.nhl_id = up.nhl_id
								INNER JOIN new_event_summary es ON es.player_id = pl.id
								INNER JOIN new_team nt ON es.team_id = nt.team_id
								AND LEFT(es.id,9) = 201120122
								GROUP BY up.nhl_id
								) AS points ON points.nhl_id = ey.nhl_id
						) AS details
						ON totals.ey_team_name = details.ey_team_name
						ORDER BY totalPoints DESC, ey_team_name, location, points DESC
					) everything";
		$result = mysql_query($sql);
		echo "<table>";
		echo "<tr><th>Rank</th><th>Team Name</th><th>Player Name</th><th>Team Name</th><th>Points</th></tr>";
		if(count($result)>=1)
		{	//echo $sql;
			while ($row = mysql_fetch_assoc($result)) 
			{
				$prevName = $ey_team_name;
				$prevTotal = $curTotal;
				$ey_team_name = $row['ey_team_name'];
				$player_name = $row['player_name'];
				$points = $row['points'];
				$curTotal = $row['totalPoints'];
				$curTeam = $row['location'];
				$curRanking = $row['ranking'];
				if ($prevName != $ey_team_name)
				{
					echo "<tr><td /><td /><td><strong>$prevName</strong></td><td><strong>$prevTotal</strong></td></tr>";
				}
				echo "<tr><td>$curRanking</td><td>$ey_team_name</td><td>$player_name</td><td>$curTeam</td><td>$points</td></tr>";

			}
		}
		echo "</table>";
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
