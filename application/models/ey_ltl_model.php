<?php
error_reporting(E_PARSE); 
class EY_LTL_Model extends CI_Model
{
    function __construct()
    {
        // Call the Model constructor
        parent::__construct();
    }

	function reload_points()
	{
		$this->db->query("SET @currentSum=0;");
		$this->db->query("SET @prevEYTeam='temp';");
		$this->db->query("SET @prevtotals=0;");
		$this->db->query("SET @place=0;");
		$this->db->query("SET @place2=0;");
		$this->db->query("SET @prev_ey_team2='temp';");
		$this->db->query("DROP TABLE IF EXISTS ey_ltl_temp;");
		$sql = "CREATE TABLE ey_ltl_temp AS (
				SELECT *, 
				IF(@prev_ey_team2 <> ey_team_name, @place:=@place+1,@place) AS ranking,
				IF((@prev_ey_team2 <> ey_team_name) AND (@prevtotals <> totalPoints), @place2:=@place,@place2) AS ranking2,
				@prev_ey_team2:=ey_team_name AS prevEy_team_name,
				@prevtotals:=totalPoints AS totPointsCount
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
								AND LEFT(es.id,9) = 201320143
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
								INNER JOIN new_game ga on ga.id = es.game_id
								AND LEFT(es.id,9) = 201320143
								AND ga.game_date between '2014-01-01' and '2015-01-01'
								GROUP BY up.nhl_id
								) AS points ON points.nhl_id = ey.nhl_id
						) AS details
						ON totals.ey_team_name = details.ey_team_name
						ORDER BY totalPoints DESC, ey_team_name, location, points DESC
					) everything
				)";
		$query = $this->db->query($sql);
	}

	function get_points_by_player()
	{
		$sql = "SELECT * FROM ey_ltl_temp ORDER BY totalPoints DESC";
		$query = $this->db->query($sql);
		$sqlText = "<table>";
		$sqlText .= "<tr><th>Rank</th><th>Team Name</th><th>Player Name</th><th>Team Name</th><th>Points</th></tr>";
			foreach ($query->result() as $row)
			{ 
				$prevName = $ey_team_name;
				$prevTotal = $curTotal;
				$ey_team_name = $row->ey_team_name;
				$player_name = $row->player_name;
				$points = $row->points;
				$curTotal = $row->totalPoints;
				$curTeam = $row->location;
				$curRanking = $row->ranking2;
				if ($prevName != $ey_team_name)
				{
					$sqlText .= "<tr><td /><td /><td><strong>$prevName</strong></td><td><strong>$prevTotal</strong></td></tr>";
				}
				$sqlText .= "<tr><td>$curRanking</td><td>$ey_team_name</td><td>$player_name</td><td>$curTeam</td><td>$points</td></tr>";
			}
			$sqlText .= "<tr><td /><td /><td><strong>$prevName</strong></td><td><strong>$prevTotal</strong></td></tr>";
		$sqlText .= "</table>";
		return $sqlText;
	}
	
}
?>