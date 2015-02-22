<?php
error_reporting(E_PARSE); 
class Gambling_Model extends CI_Model
{
    function __construct()
    {
        // Call the Model constructor
        parent::__construct();
    }



	function moneyline($params)
	{
    /* Params
     *
     * [betType] => moneyline
    [game] => 20120912
    [betAmount] => 10
    [betOnTeam] => Home
    [homeOdds] => 100
    [awayOdds] => 100
    [teamFilters] => opt3
    [homeTeamOpponents] => 1000015-1000024-1000006-1000022-1000023-1000009-1000008-1000030-1000027-1000010-1000011-1000025-1000029-1000028-1000012-1000014-1000013-1000002-1000026-1000003-1000007-1000019-1000018-1000004-1000005-1000021-1000017-1000016-1000001-1000020
    [awayTeamOpponents] => 1000015-1000024-1000006-1000022-1000023-1000009-1000008-1000030-1000027-1000010-1000011-1000025-1000029-1000028-1000012-1000014-1000013-1000002-1000026-1000003-1000007-1000019-1000018-1000004-1000005-1000021-1000017-1000016-1000001-1000020
     */

	$homeTeamAgainst = '';
	
    $gameTypeLine = $this->getGameTypeLine($params);
	
	$homeTeamAgainst = $this->getTeamSQL($params);
    $teams = $this->getTeamsFromSchedual($params['game']);

    $awayTeamID = $this->getTeamIDfromSched($teams['away_team']);
    $homeTeamID = $this->getTeamIDfromSched($teams['home_team']);

    if($params['betOnTeam'] == 'Home'){
      $bookodds = $params['homeOdds'];
      $parsedOdds = ($params['homeOdds']>=0)?($params['homeOdds']/100):-(100/$params['homeOdds']);
      $betwinteam = $homeTeamID;
      $betloseteam = $awayTeamID;
    }
    else if ($params['betOnTeam'] == 'Away'){
      $bookodds = $params['awayOdds'];
      $parsedOdds = ($params['awayOdds']>=0)?($params['awayOdds']/100):-(100/$params['awayOdds']);
      $betwinteam = $awayTeamID;
      $betloseteam = $homeTeamID;
    }
  //echo "parsed odds: $parsedOdds";
  
 //set the all games versus home/away only indicator
 
	$homeAwaySql = $this->getHomeAwaySQL($params);
	
  //function to pull home team and away team based on gameID
	
	$sqlOut = 'SET @bet='.$params['betAmount'].';';
	$sqlOut .= 'SET @win=0'.';';
	$sqlOut .= 'SET @bookodds='.$bookodds.';';
	$sqlOut .= 'SET @odds='.$parsedOdds.';';
	$sqlOut .= 'SET @cummulativeProfit1=0'.';';
	$sqlOut .= 'SET @ProfitPercent=0'.';';
	$sqlOut .= 'SET @cummulativeWins1=0'.';';
	$sqlOut .= 'SET @cummulativeLosses1=0'.';';
	$sqlOut .= 'SET @hometeam='.$homeTeamID.';';
	$sqlOut .= 'SET @awayteam='.$awayTeamID.';';
	$sqlOut .= 'SET @betwinteam='.$betwinteam.';';
	$sqlOut .= 'SET @betloseteam='.$betloseteam.';';
	$sqlOut .= 'SET @i=0'.';';
	$sqlOut .= 'SET @Profit=0'.';';
	$sqlOut .= 'SET @startDate="'.date('Y-m-d',strtotime($params['startDate'])) . '"'.';';
	$sqlOut .= 'SET @endDate="'.date('Y-m-d',strtotime($params['endDate'])) . '"'.';';
	

    $this->db->query('SET @bet='.$params['betAmount']);
    $this->db->query('SET @win=0');
    $this->db->query('SET @bookodds='.$bookodds);
    $this->db->query('SET @odds='.$parsedOdds);
    $this->db->query('SET @cummulativeProfit1=0');
    $this->db->query('SET @ProfitPercent=0');
    $this->db->query('SET @cummulativeWins1=0');
    $this->db->query('SET @cummulativeLosses1=0');
    $this->db->query('SET @hometeam='.$homeTeamID);
    $this->db->query('SET @awayteam='.$awayTeamID);
    $this->db->query('SET @betwinteam='.$betwinteam);
    $this->db->query('SET @betloseteam='.$betloseteam);
    $this->db->query('SET @i=0');
    $this->db->query('SET @Profit=0');
	$this->db->query('SET @startDate="'.date('Y-m-d',strtotime($params['startDate'])) . '"');
	$this->db->query('SET @endDate="'.date('Y-m-d',strtotime($params['endDate'])) . '"');

		$sql = "SELECT
      @i:=@i+1 AS number,
      @win:=IF(winner = @betwinteam OR IF(home_team_id = winner, away_team_id, home_team_id)=@betloseteam,1,0) AS win,
      IF(@win=1, @cummulativeWins1:=@cummulativeWins1 + 1,@cummulativeWins1) AS cummulativeWins,
      IF(@win=0, @cummulativeLosses1:=@cummulativeLosses1 + 1,@cummulativeLosses1) AS cummulativeLosses,
      IF(@cummulativeLosses1/@cummulativeWins1 > 1, (@cummulativeLosses1/@cummulativeWins1*100), (-@cummulativeWins1/@cummulativeLosses1*100)) AS cummulativeOdds,
      IF(@win=1, @cummulativeProfit1:=@cummulativeProfit1 + (@odds * @bet), @cummulativeProfit1:=@cummulativeProfit1 - (1.0 * @bet)) AS cummulativeProfit,
      IF(@win=1, @Profit:=(@odds * @bet), @Profit:=- (1.0 * @bet)) AS Profit,
      @ProfitPercent:=@cummulativeProfit1/(@bet*@i) AS ProfitPercent,
      @loser:=IF(home_team_id = winner, away_team_id, home_team_id) AS loser,
      @bookodds AS bookodds,
      derived.*
	  FROM
      (
		  SELECT ga.* 
		  FROM new_game ga
		  INNER JOIN new_team ht ON ga.home_team_id = ht.team_id
		  INNER JOIN new_team awt ON ga.away_team_id = awt.team_id
		  WHERE ( $homeTeamAgainst)
		  AND ga.game_date BETWEEN @startDate AND @endDate
		  $gameTypeLine $homeAwaySql
		  ORDER BY game_date ASC
	  ) as derived
		";
		//echo $sqlOut.$sql;
		$query = $this->db->query($sql);
	
	//mike edits to pull in summary data
	foreach ($query->result() as $row){
		$arrGoals['cumProfit'] = $row->cummulativeProfit;
		$arrGoals['avgProfit'] = $row->ProfitPercent * $params['betAmount'];
		$arrGoals['calcOdds'] = $row->cummulativeOdds;
    }
	//end Mike edits
	
	
    $start_date = $this->getStartDate($dates,$gameType);

    if(count($query->result())==0){
      $arrGoals['data'][$start_date] = '0';
    }else{
      $arrGoals['data'] = $this->parseData($query,'Profit','hockey');
    }
	

	//print_r($arrGoals);
	return $arrGoals;
	}

	function moneylineBetOnline($params)
	{
    /* Params
     *
     * [betType] => moneyline
    [game] => 20120912
    [betAmount] => 10
    [betOnTeam] => Home
    [homeOdds] => 100
    [awayOdds] => 100
    [teamFilters] => opt3
    [homeTeamOpponents] => 1000015-1000024-1000006-1000022-1000023-1000009-1000008-1000030-1000027-1000010-1000011-1000025-1000029-1000028-1000012-1000014-1000013-1000002-1000026-1000003-1000007-1000019-1000018-1000004-1000005-1000021-1000017-1000016-1000001-1000020
    [awayTeamOpponents] => 1000015-1000024-1000006-1000022-1000023-1000009-1000008-1000030-1000027-1000010-1000011-1000025-1000029-1000028-1000012-1000014-1000013-1000002-1000026-1000003-1000007-1000019-1000018-1000004-1000005-1000021-1000017-1000016-1000001-1000020
     */

	$homeTeamAgainst = '';
	
    $gameTypeLine = $this->getGameTypeLine($params);
	
	$homeTeamAgainst = $this->getTeamSQL($params);
    $teams = $this->getTeamsFromSchedual($params['game']);

    $awayTeamID = $this->getTeamIDfromSched($teams['away_team']);
    $homeTeamID = $this->getTeamIDfromSched($teams['home_team']);

    if($params['betOnTeam'] == 'Home'){
      $bookodds = $params['homeOdds'];
      $parsedOdds = ($params['homeOdds']>=0)?($params['homeOdds']/100):-(100/$params['homeOdds']);
	  $betOnlineOdds = ($params['homeOddsBetOnline']>=0)?($params['homeOddsBetOnline']/100):-(100/$params['homeOddsBetOnline']);
      $betwinteam = $homeTeamID;
      $betloseteam = $awayTeamID;
	  $arrGoals['oddsPinnacle'] = $params['homeOdds'];
	  $arrGoals['oddsBetOnline'] = $params['homeOddsBetOnline'];
	  
    }
    else if ($params['betOnTeam'] == 'Away'){
      $bookodds = $params['awayOdds'];
      $parsedOdds = ($params['awayOdds']>=0)?($params['awayOdds']/100):-(100/$params['awayOdds']);
      $betOnlineOdds = ($params['awayOddsBetOnline']>=0)?($params['awayOddsBetOnline']/100):-(100/$params['awayOddsBetOnline']);
	  $betwinteam = $awayTeamID;
      $betloseteam = $homeTeamID;
	  $arrGoals['oddsPinnacle'] = $params['awayOdds'];
	  $arrGoals['oddsBetOnline'] = $params['awayOddsBetOnline'];
	 }
  //echo "parsed odds: $parsedOdds";
  
 //set the all games versus home/away only indicator
 
	$homeAwaySql = $this->getHomeAwaySQL($params);
	
  //function to pull home team and away team based on gameID
	
	$sqlOut = 'SET @ProfitBetOnline=0;';
	$sqlOut .= 'SET @cummulativeProfitBetOnline=0;';
	$sqlOut .= 'SET @bet='.$params['betAmount'].';';
	$sqlOut .= 'SET @win=0'.';';
	$sqlOut .= 'SET @bookodds='.$bookodds.';';
	$sqlOut .= 'SET @odds='.$parsedOdds.';';
	$sqlOut .= 'SET @cummulativeProfit1=0'.';';
	$sqlOut .= 'SET @ProfitPercent=0'.';';
	$sqlOut .= 'SET @cummulativeWins1=0'.';';
	$sqlOut .= 'SET @cummulativeLosses1=0'.';';
	$sqlOut .= 'SET @hometeam='.$homeTeamID.';';
	$sqlOut .= 'SET @awayteam='.$awayTeamID.';';
	$sqlOut .= 'SET @betwinteam='.$betwinteam.';';
	$sqlOut .= 'SET @betloseteam='.$betloseteam.';';
	$sqlOut .= 'SET @i=0'.';';
	$sqlOut .= 'SET @Profit=0'.';';
	$sqlOut .= 'SET @startDate="'.date('Y-m-d',strtotime($params['startDate'])) . '"'.';';
	$sqlOut .= 'SET @endDate="'.date('Y-m-d',strtotime($params['endDate'])) . '"'.';';
	
	$this->db->query('SET @ProfitBetOnline=0;');
	$this->db->query('SET @cummulativeProfitBetOnline=0;');
    $this->db->query('SET @bet='.$params['betAmount']);
    $this->db->query('SET @win=0');
    $this->db->query('SET @bookodds='.$bookodds);
    $this->db->query('SET @odds='.$parsedOdds);
	$this->db->query('SET @betOnlineOdds='.$betOnlineOdds);
    $this->db->query('SET @cummulativeProfit1=0');
    $this->db->query('SET @ProfitPercent=0');
    $this->db->query('SET @cummulativeWins1=0');
    $this->db->query('SET @cummulativeLosses1=0');
    $this->db->query('SET @hometeam='.$homeTeamID);
    $this->db->query('SET @awayteam='.$awayTeamID);
    $this->db->query('SET @betwinteam='.$betwinteam);
    $this->db->query('SET @betloseteam='.$betloseteam);
    $this->db->query('SET @i=0');
    $this->db->query('SET @Profit=0');
	$this->db->query('SET @startDate="'.date('Y-m-d',strtotime($params['startDate'])) . '"');
	$this->db->query('SET @endDate="'.date('Y-m-d',strtotime($params['endDate'])) . '"');

		$sql = "SELECT
      @i:=@i+1 AS number,
      @win:=IF(winner = @betwinteam OR IF(home_team_id = winner, away_team_id, home_team_id)=@betloseteam,1,0) AS win,
      IF(@win=1, @cummulativeWins1:=@cummulativeWins1 + 1,@cummulativeWins1) AS cummulativeWins,
      IF(@win=0, @cummulativeLosses1:=@cummulativeLosses1 + 1,@cummulativeLosses1) AS cummulativeLosses,
      IF(@cummulativeLosses1/@cummulativeWins1 > 1, (@cummulativeLosses1/@cummulativeWins1*100), (-@cummulativeWins1/@cummulativeLosses1*100)) AS cummulativeOdds,
      IF(@win=1, @cummulativeProfit1:=@cummulativeProfit1 + (@odds * @bet), @cummulativeProfit1:=@cummulativeProfit1 - (1.0 * @bet)) AS cummulativeProfit,
	  IF(@win=1, @cummulativeProfitBetOnline:=@cummulativeProfitBetOnline + (@betOnlineOdds * @bet), @cummulativeProfitBetOnline:=@cummulativeProfitBetOnline - (1.0 * @bet)) AS cummulativeProfitBetOnline,
      IF(@win=1, @Profit:=(@odds * @bet), @Profit:=- (1.0 * @bet)) AS Profit,
	  IF(@win=1, @ProfitBetOnline:=(@betOnlineOdds * @bet), @ProfitBetOnline:=- (1.0 * @bet)) AS ProfitBetOnline,	  
      @ProfitPercent:=@cummulativeProfit1/(@bet*@i) AS ProfitPercent,
	  @ProfitPercentBetOnline:=@cummulativeProfitBetOnline/(@bet*@i) AS ProfitPercentBetOnline,
      @loser:=IF(home_team_id = winner, away_team_id, home_team_id) AS loser,
      @bookodds AS bookodds,
      derived.*
	  FROM
      (
		  SELECT ga.* 
		  FROM new_game ga
		  INNER JOIN new_team ht ON ga.home_team_id = ht.team_id
		  INNER JOIN new_team awt ON ga.away_team_id = awt.team_id
		  WHERE ( $homeTeamAgainst)
		  AND ga.game_date BETWEEN @startDate AND @endDate
		  $gameTypeLine $homeAwaySql
		  ORDER BY game_date ASC
	  ) as derived
		";
		//echo $sqlOut.$sql;
		$query = $this->db->query($sql);
	
	//mike edits to pull in summary data --- this is inefficient, it should not need to loop through the whole array just to get last value
	foreach ($query->result() as $row){
		$arrGoals['cumProfit'] = $row->cummulativeProfit;
		$arrGoals['avgProfit'] = $row->ProfitPercent * $params['betAmount'];
		$arrGoals['calcOdds'] = $row->cummulativeOdds;
		$arrGoals['cumProfitBetOnline'] = $row->cummulativeProfitBetOnline;
		$arrGoals['avgProfitBetOnline'] = $row->ProfitPercentBetOnline * $params['betAmount'];
    }
	//end Mike edits

	
	
	
    $start_date = $this->getStartDate($dates,$gameType);

    if(count($query->result())==0){
      $arrGoals['data'][$start_date] = '0';
    }else{
      $arrGoals['data'] = $this->parseData($query,'Profit','hockey');
	  $arrGoals['dataBetOnline'] = $this->parseData($query,'ProfitBetOnline','hockey');
	  //print_r($arrGoals['dataBetOnline']);
	}
	

	//print_r($arrGoals);
	return $arrGoals;
	}
	function moneylineBetOnlineMLB($params)
	{
    /* Params
     *
     * [betType] => moneyline
    [game] => 20120912
    [betAmount] => 10
    [betOnTeam] => Home
    [homeOdds] => 100
    [awayOdds] => 100
    [teamFilters] => opt3
    [homeTeamOpponents] => 1000015-1000024-1000006-1000022-1000023-1000009-1000008-1000030-1000027-1000010-1000011-1000025-1000029-1000028-1000012-1000014-1000013-1000002-1000026-1000003-1000007-1000019-1000018-1000004-1000005-1000021-1000017-1000016-1000001-1000020
    [awayTeamOpponents] => 1000015-1000024-1000006-1000022-1000023-1000009-1000008-1000030-1000027-1000010-1000011-1000025-1000029-1000028-1000012-1000014-1000013-1000002-1000026-1000003-1000007-1000019-1000018-1000004-1000005-1000021-1000017-1000016-1000001-1000020
     */
	$seasonDates = $this->getRegSeasonPlayoffs($params);
	$homeTeamAgainst = '';
	
    //$gameTypeLine = $this->getGameTypeLine($params);
	
	$homeTeamAgainst = $this->getTeamSQL($params);
    $teams = $this->getTeamIDsfromGameMLB($params['game']);

    $awayTeamID = $teams['away'];
    $homeTeamID = $teams['home'];

    if($params['betOnTeam'] == 'Home'){
      $bookodds = $params['homeOdds'];
      $parsedOdds = ($params['homeOdds']>=0)?($params['homeOdds']/100):-(100/$params['homeOdds']);
	  $betOnlineOdds = ($params['homeOddsBetOnline']>=0)?($params['homeOddsBetOnline']/100):-(100/$params['homeOddsBetOnline']);
      $betwinteam = $homeTeamID;
      $betloseteam = $awayTeamID;
	  $arrGoals['oddsPinnacle'] = $params['homeOdds'];
	  $arrGoals['oddsBetOnline'] = $params['homeOddsBetOnline'];
	  
    }
    else if ($params['betOnTeam'] == 'Away'){
      $bookodds = $params['awayOdds'];
      $parsedOdds = ($params['awayOdds']>=0)?($params['awayOdds']/100):-(100/$params['awayOdds']);
      $betOnlineOdds = ($params['awayOddsBetOnline']>=0)?($params['awayOddsBetOnline']/100):-(100/$params['awayOddsBetOnline']);
	  $betwinteam = $awayTeamID;
      $betloseteam = $homeTeamID;
	  $arrGoals['oddsPinnacle'] = $params['awayOdds'];
	  $arrGoals['oddsBetOnline'] = $params['awayOddsBetOnline'];
	 }
  //echo "parsed odds: $parsedOdds";
  
 //set the all games versus home/away only indicator
 
	$homeAwaySql = $this->getHomeAwaySQL($params);
	//print_r($params);
  //function to pull home team and away team based on gameID
	
	$sqlOut = 'SET @ProfitBetOnline=0;';
	$sqlOut .= 'SET @cummulativeProfitBetOnline=0;';
	$sqlOut .= 'SET @bet='.$params['betAmount'].';';
	$sqlOut .= 'SET @win=0'.';';
	$sqlOut .= 'SET @bookodds='.$bookodds.';';
	$sqlOut .= 'SET @odds='.$parsedOdds.';';
	$sqlOut .= 'SET @cummulativeProfit1=0'.';';
	$sqlOut .= 'SET @ProfitPercent=0'.';';
	$sqlOut .= 'SET @cummulativeWins1=0'.';';
	$sqlOut .= 'SET @cummulativeLosses1=0'.';';
	$sqlOut .= 'SET @hometeam='.$homeTeamID.';';
	$sqlOut .= 'SET @awayteam='.$awayTeamID.';';
	$sqlOut .= 'SET @betwinteam='.$betwinteam.';';
	$sqlOut .= 'SET @betloseteam='.$betloseteam.';';
	$sqlOut .= 'SET @i=0'.';';
	$sqlOut .= 'SET @Profit=0'.';';
	$sqlOut .= 'SET @startDate="'.date('Y-m-d',strtotime($params['startDate'])) . '"'.';';
	$sqlOut .= 'SET @endDate="'.date('Y-m-d',strtotime($params['endDate'])) . '"'.';';
	
	$this->db->query('SET @ProfitBetOnline=0;');
	$this->db->query('SET @cummulativeProfitBetOnline=0;');
    $this->db->query('SET @bet='.$params['betAmount']);
    $this->db->query('SET @win=0');
    $this->db->query('SET @bookodds='.$bookodds);
    $this->db->query('SET @odds='.$parsedOdds);
	$this->db->query('SET @betOnlineOdds='.$betOnlineOdds);
    $this->db->query('SET @cummulativeProfit1=0');
    $this->db->query('SET @ProfitPercent=0');
    $this->db->query('SET @cummulativeWins1=0');
    $this->db->query('SET @cummulativeLosses1=0');
    $this->db->query('SET @hometeam='.$homeTeamID);
    $this->db->query('SET @awayteam='.$awayTeamID);
    $this->db->query('SET @betwinteam='.$betwinteam);
    $this->db->query('SET @betloseteam='.$betloseteam);
    $this->db->query('SET @i=0');
    $this->db->query('SET @Profit=0');
	$this->db->query('SET @startDate="'.date('Y-m-d',strtotime($params['startDate'])) . '"');
	$this->db->query('SET @endDate="'.date('Y-m-d',strtotime($params['endDate'])) . '"');

		$sql = "SELECT
      @i:=@i+1 AS number,
      @win:=IF(winner = @betwinteam OR IF(home_team_id = winner, away_team_id, home_team_id)=@betloseteam,1,0) AS win,
      IF(@win=1, @cummulativeWins1:=@cummulativeWins1 + 1,@cummulativeWins1) AS cummulativeWins,
      IF(@win=0, @cummulativeLosses1:=@cummulativeLosses1 + 1,@cummulativeLosses1) AS cummulativeLosses,
      IF(@cummulativeLosses1/@cummulativeWins1 > 1, (@cummulativeLosses1/@cummulativeWins1*100), (-@cummulativeWins1/@cummulativeLosses1*100)) AS cummulativeOdds,
      IF(@win=1, @cummulativeProfit1:=@cummulativeProfit1 + (@odds * @bet), @cummulativeProfit1:=@cummulativeProfit1 - (1.0 * @bet)) AS cummulativeProfit,
	  IF(@win=1, @cummulativeProfitBetOnline:=@cummulativeProfitBetOnline + (@betOnlineOdds * @bet), @cummulativeProfitBetOnline:=@cummulativeProfitBetOnline - (1.0 * @bet)) AS cummulativeProfitBetOnline,
      IF(@win=1, @Profit:=(@odds * @bet), @Profit:=- (1.0 * @bet)) AS Profit,
	  IF(@win=1, @ProfitBetOnline:=(@betOnlineOdds * @bet), @ProfitBetOnline:=- (1.0 * @bet)) AS ProfitBetOnline,	  
      @ProfitPercent:=@cummulativeProfit1/(@bet*@i) AS ProfitPercent,
	  @ProfitPercentBetOnline:=@cummulativeProfitBetOnline/(@bet*@i) AS ProfitPercentBetOnline,
      @loser:=IF(home_team_id = winner, away_team_id, home_team_id) AS loser,
      @bookodds AS bookodds,
      derived.*
	  FROM
      (
		  SELECT ga.* 
		  FROM mlb_game ga
		  INNER JOIN mlb_team ht ON ga.home_team_id = ht.team_id
		  INNER JOIN mlb_team awt ON ga.away_team_id = awt.team_id
		  WHERE ( $homeTeamAgainst)
		  AND ga.game_date BETWEEN @startDate AND @endDate
		  $seasonDates
		  $homeAwaySql
		  ORDER BY game_date ASC
	  ) as derived
		";
		//echo $sqlOut.$sql;
		$query = $this->db->query($sql);
	
	//mike edits to pull in summary data --- this is inefficient, it should not need to loop through the whole array just to get last value
	foreach ($query->result() as $row){
		$arrGoals['cumProfit'] = $row->cummulativeProfit;
		$arrGoals['avgProfit'] = $row->ProfitPercent * $params['betAmount'];
		$arrGoals['calcOdds'] = $row->cummulativeOdds;
		$arrGoals['cumProfitBetOnline'] = $row->cummulativeProfitBetOnline;
		$arrGoals['avgProfitBetOnline'] = $row->ProfitPercentBetOnline * $params['betAmount'];
    }
	//end Mike edits

	
	
	
    $start_date = $this->getStartDate($dates,$gameType);

    if(count($query->result())==0){
      $arrGoals['data'][$start_date] = '0';
    }else{
      $arrGoals['data'] = $this->parseData($query,'Profit','baseball');
	  $arrGoals['dataBetOnline'] = $this->parseData($query,'ProfitBetOnline','baseball');
	  //print_r($arrGoals['dataBetOnline']);
	}
	

	//print_r($arrGoals);
	return $arrGoals;
	}

	function puckline($params)
  {
	$homeTeamAgainst = $this->getTeamSQL($params);
	$teams = $this->getTeamsFromSchedual($params['game']);
    $awayTeamID = $this->getTeamIDfromSched($teams['away_team']);
    $homeTeamID = $this->getTeamIDfromSched($teams['home_team']);

    if($params['betOnTeam'] == 'Home'){
      $bookodds = $params['homeOdds'];
      $parsedOdds = ($params['homeOdds']>=0)?($params['homeOdds']/100):-(100/$params['homeOdds']);
      $betwinteam = $homeTeamID;
      $betloseteam = $awayTeamID;
	  $spread = $params['homeSpread'];
    }
    else{
      $bookodds = $params['awayOdds'];
      $parsedOdds = ($params['awayOdds']>=0)?($params['awayOdds']/100):-(100/$params['awayOdds']);
      $betwinteam = $awayTeamID;
      $betloseteam = $homeTeamID;
	  $spread = $params['awaySpread'];
    }
	
	//home away sql
/*	if ($params['homeAway'] == 'allTeams')
	{
		$homeAwaySql = "";
	}
	else
	{
		$homeAwaySql = "AND (ht.team_id = @hometeam OR awt.team_id = @awayteam)";
	}*/
	//home away set #2
	$homeAwaySql = $this->getHomeAwaySQL($params);
	$gameTypeLine = $this->getGameTypeLine($params);
	
	$sqlOut = 'SET @bet='.$params['betAmount'].';';
	$sqlOut .= 'SET @odds='.$parsedOdds.';';
	$sqlOut .= 'SET @spread='.$spread.';';
	$sqlOut .= 'SET @cummulativeProfit1=0'.';';
	$sqlOut .= 'SET @cummulativeWins1=0'.';';
	$sqlOut .= 'SET @cummulativeLosses1=0'.';';
	$sqlOut .= 'SET @ProfitPercent=0'.';';
	$sqlOut .= 'SET @win=0'.';';
	$sqlOut .= 'SET @loserid=0'.';';
	$sqlOut .= 'SET @hometeam='.$homeTeamID.';';
	$sqlOut .= 'SET @awayteam='.$awayTeamID.';';
	$sqlOut .= 'SET @betwinteam='.$betwinteam.';';
	$sqlOut .= 'SET @betloseteam='.$betloseteam.';';
	$sqlOut .= 'SET @i=0'.';';
	$sqlOut .= 'SET @Profit=0'.';';
	$sqlOut .= 'SET @startDate="'.date('Y-m-d',strtotime($params['startDate'])) . '"'.';';
	$sqlOut .= 'SET @endDate="'.date('Y-m-d',strtotime($params['endDate'])) . '"'.';';
	
	//echo $homeAwaySql;
    //print_r( $sqlStrength );
    $this->db->query('SET @bet='.$params['betAmount']);
    $this->db->query('SET @odds='.$parsedOdds);
    $this->db->query('SET @spread='.$spread);
    $this->db->query('SET @cummulativeProfit1=0');
    $this->db->query('SET @cummulativeWins1=0');
    $this->db->query('SET @cummulativeLosses1=0');
	$this->db->query('SET @ProfitPercent=0');
    $this->db->query('SET @win=0');
    $this->db->query('SET @loserid=0');
    $this->db->query('SET @hometeam='.$homeTeamID);
    $this->db->query('SET @awayteam='.$awayTeamID);
    $this->db->query('SET @betwinteam='.$betwinteam);
    $this->db->query('SET @betloseteam='.$betloseteam);
    $this->db->query('SET @i=0');
	$this->db->query('SET @Profit=0');
	$this->db->query('SET @startDate="'.date('Y-m-d',strtotime($params['startDate'])) . '"');
	$this->db->query('SET @endDate="'.date('Y-m-d',strtotime($params['endDate'])) . '"');

    $sql = "
      SELECT
      @i:=@i+1 AS number,
      @win:=IF(home_team_id = @betwinteam OR away_team_id = @betwinteam,
      IF(home_team_id = @betwinteam, IF(CONVERT(home_score,SIGNED) + @spread > CONVERT(away_score,SIGNED), 1, 0),IF(CONVERT(away_score,SIGNED) + @spread > CONVERT(home_score,SIGNED), 1, 0)),
      IF(home_team_id = @betloseteam, IF(CONVERT(away_score,SIGNED) + @spread > CONVERT(home_score,SIGNED), 1, 0),IF(CONVERT(home_score,SIGNED) + @spread> CONVERT(away_score,SIGNED), 1, 0))) AS win,
      IF(@win=1, @cummulativeWins1:=@cummulativeWins1 + 1,@cummulativeWins1) AS cummulativeWins,
      IF(@win=0, @cummulativeLosses1:=@cummulativeLosses1 + 1,@cummulativeLosses1) AS cummulativeLosses,
      IF(@cummulativeLosses1/@cummulativeWins1 >= 1, @cummulativeLosses1/@cummulativeWins1*100, -@cummulativeWins1/@cummulativeLosses1*100) AS cummulativeOdds,
      IF(@win=1, @cummulativeProfit1:=@cummulativeProfit1 + (@odds * @bet), @cummulativeProfit1:=@cummulativeProfit1 - (1.0 * @bet)) AS cummulativeProfit,
	  IF(@win=1, @Profit:=(@odds * @bet), @Profit:=- (1.0 * @bet)) AS Profit,
      @ProfitPercent:=@cummulativeProfit1/(@bet*@i) AS ProfitPercent,
      @loser:=IF(home_team_id = winner, away_team_id, home_team_id) AS loser,
      @bookodds AS bookodds,
      derived.*
	  FROM 
	  (
		  SELECT ga.* 
		  FROM new_game ga
		  INNER JOIN new_team ht ON ga.home_team_id = ht.team_id
		  INNER JOIN new_team awt ON ga.away_team_id = awt.team_id
		  WHERE ($homeTeamAgainst)
		  AND ga.game_date BETWEEN @startDate AND @endDate
		  $gameTypeLine $homeAwaySql
		  ORDER BY game_date ASC
	  ) as derived
		";
    //echo $sqlOut.$sql;
    $query = $this->db->query($sql);
	
	//mike edits to pull in summary data
	foreach ($query->result() as $row){
		$arrGoals['cumProfit'] = $row->cummulativeProfit;
		$arrGoals['avgProfit'] = $row->ProfitPercent * $params['betAmount'];
		$arrGoals['calcOdds'] = $row->cummulativeOdds;
    }
	//end Mike edits
	
	
    $start_date = $this->getStartDate($dates,$gameType);

    if(count($query->result())==0){
      $arrGoals['data'][$start_date] = '0';
    }else{
      $arrGoals['data'] = $this->parseData($query,'Profit','hockey');
    }
    //print_r($sql);
    //$arrGoals1 = $this->genData($arrGoals,'goals');
    return $arrGoals;
  }

	function pucklineBetOnline($params)
  {
	$homeTeamAgainst = $this->getTeamSQL($params);
	$teams = $this->getTeamsFromSchedual($params['game']);
    $awayTeamID = $this->getTeamIDfromSched($teams['away_team']);
    $homeTeamID = $this->getTeamIDfromSched($teams['home_team']);

    if($params['betOnTeam'] == 'Home'){
      $bookodds = $params['homeOdds'];
      $parsedOdds = ($params['homeOdds']>=0)?($params['homeOdds']/100):-(100/$params['homeOdds']);
      $betOnlineOdds = ($params['homeOddsBetOnline']>=0)?($params['homeOddsBetOnline']/100):-(100/$params['homeOddsBetOnline']);
	  $betwinteam = $homeTeamID;
      $betloseteam = $awayTeamID;
	  $spread = $params['homeSpread'];
	  $spreadBetOnline = $params['homeSpreadBetOnline'];
	  $arrGoals['oddsPinnacle'] = $params['homeOdds'];
	  $arrGoals['oddsBetOnline'] = $params['homeOddsBetOnline'];
    }
    else{
      $bookodds = $params['awayOdds'];
      $parsedOdds = ($params['awayOdds']>=0)?($params['awayOdds']/100):-(100/$params['awayOdds']);
      $betOnlineOdds = ($params['awayOddsBetOnline']>=0)?($params['awayOddsBetOnline']/100):-(100/$params['awayOddsBetOnline']);
	  $betwinteam = $awayTeamID;
      $betloseteam = $homeTeamID;
	  $spread = $params['awaySpread'];
	  $spreadBetOnline = $params['awaySpreadBetOnline'];
	  $arrGoals['oddsPinnacle'] = $params['awayOdds'];
	  $arrGoals['oddsBetOnline'] = $params['awayOddsBetOnline'];
	}
	
	//home away sql
/*	if ($params['homeAway'] == 'allTeams')
	{
		$homeAwaySql = "";
	}
	else
	{
		$homeAwaySql = "AND (ht.team_id = @hometeam OR awt.team_id = @awayteam)";
	}*/
	//home away set #2
	$homeAwaySql = $this->getHomeAwaySQL($params);
	$gameTypeLine = $this->getGameTypeLine($params);
	
	$sqlOut = 'SET @ProfitBetOnline=0;';
	$sqlOut .= 'SET @cummulativeProfitBetOnline=0;';
	$sqlOut .= 'SET @bet='.$params['betAmount'].';';
	$sqlOut .= 'SET @odds='.$parsedOdds.';';
	$sqlOut .= 'SET @oddsBetOnline='.$betOnlineOdds;
	$sqlOut .= 'SET @spread='.$spread.';';
	$sqlOut .= 'SET @spreadBetOnline='.$spreadBetOnline.';';
	$sqlOut .= 'SET @cummulativeProfit1=0'.';';
	$sqlOut .= 'SET @cummulativeWins1=0'.';';
	$sqlOut .= 'SET @cummulativeLosses1=0'.';';
	$sqlOut .= 'SET @ProfitPercent=0'.';';
	$sqlOut .= 'SET @win=0'.';';
	$sqlOut .= 'SET @loserid=0'.';';
	$sqlOut .= 'SET @hometeam='.$homeTeamID.';';
	$sqlOut .= 'SET @awayteam='.$awayTeamID.';';
	$sqlOut .= 'SET @betwinteam='.$betwinteam.';';
	$sqlOut .= 'SET @betloseteam='.$betloseteam.';';
	$sqlOut .= 'SET @i=0'.';';
	$sqlOut .= 'SET @Profit=0'.';';
	$sqlOut .= 'SET @startDate="'.date('Y-m-d',strtotime($params['startDate'])) . '"'.';';
	$sqlOut .= 'SET @endDate="'.date('Y-m-d',strtotime($params['endDate'])) . '"'.';';
	
	//echo $homeAwaySql;
    //print_r( $sqlStrength );
	$this->db->query('SET @ProfitBetOnline=0;');
	$this->db->query('SET @cummulativeProfitBetOnline=0;');
    $this->db->query('SET @bet='.$params['betAmount']);
    $this->db->query('SET @odds='.$parsedOdds);
	$this->db->query('SET @oddsBetOnline='.$betOnlineOdds);
    $this->db->query('SET @spread='.$spread);
	$this->db->query('SET @spread='.$spreadBetOnline);
    $this->db->query('SET @cummulativeProfit1=0');
    $this->db->query('SET @cummulativeWins1=0');
    $this->db->query('SET @cummulativeLosses1=0');
	$this->db->query('SET @ProfitPercent=0');
    $this->db->query('SET @win=0');
    $this->db->query('SET @loserid=0');
    $this->db->query('SET @hometeam='.$homeTeamID);
    $this->db->query('SET @awayteam='.$awayTeamID);
    $this->db->query('SET @betwinteam='.$betwinteam);
    $this->db->query('SET @betloseteam='.$betloseteam);
    $this->db->query('SET @i=0');
	$this->db->query('SET @Profit=0');
	$this->db->query('SET @startDate="'.date('Y-m-d',strtotime($params['startDate'])) . '"');
	$this->db->query('SET @endDate="'.date('Y-m-d',strtotime($params['endDate'])) . '"');

    $sql = "
      SELECT
      @i:=@i+1 AS number,
      @win:=IF(home_team_id = @betwinteam OR away_team_id = @betwinteam,
      IF(home_team_id = @betwinteam, IF(CONVERT(home_score,SIGNED) + @spread > CONVERT(away_score,SIGNED), 1, 0),IF(CONVERT(away_score,SIGNED) + @spread > CONVERT(home_score,SIGNED), 1, 0)),
      IF(home_team_id = @betloseteam, IF(CONVERT(away_score,SIGNED) + @spread > CONVERT(home_score,SIGNED), 1, 0),IF(CONVERT(home_score,SIGNED) + @spread> CONVERT(away_score,SIGNED), 1, 0))) AS win,
      IF(@win=1, @cummulativeWins1:=@cummulativeWins1 + 1,@cummulativeWins1) AS cummulativeWins,
      IF(@win=0, @cummulativeLosses1:=@cummulativeLosses1 + 1,@cummulativeLosses1) AS cummulativeLosses,
      IF(@cummulativeLosses1/@cummulativeWins1 >= 1, @cummulativeLosses1/@cummulativeWins1*100, -@cummulativeWins1/@cummulativeLosses1*100) AS cummulativeOdds,
      IF(@win=1, @cummulativeProfit1:=@cummulativeProfit1 + (@odds * @bet), @cummulativeProfit1:=@cummulativeProfit1 - (1.0 * @bet)) AS cummulativeProfit,
	  IF(@win=1, @cummulativeProfitBetOnline:=@cummulativeProfitBetOnline + (@oddsBetOnline * @bet), @cummulativeProfitBetOnline:=@cummulativeProfitBetOnline - (1.0 * @bet)) AS cummulativeProfitBetOnline,
	  IF(@win=1, @Profit:=(@odds * @bet), @Profit:=- (1.0 * @bet)) AS Profit,
      IF(@win=1, @Profit:=(@oddsBetOnline * @bet), @Profit:=- (1.0 * @bet)) AS ProfitBetOnline,
	  @ProfitPercent:=@cummulativeProfit1/(@bet*@i) AS ProfitPercent,
      @ProfitPercentBetOnline:=@cummulativeProfitBetOnline/(@bet*@i) AS ProfitPercentBetOnline,
	  @loser:=IF(home_team_id = winner, away_team_id, home_team_id) AS loser,
      @bookodds AS bookodds,
      derived.*
	  FROM 
	  (
		  SELECT ga.* 
		  FROM new_game ga
		  INNER JOIN new_team ht ON ga.home_team_id = ht.team_id
		  INNER JOIN new_team awt ON ga.away_team_id = awt.team_id
		  WHERE ($homeTeamAgainst)
		  AND ga.game_date BETWEEN @startDate AND @endDate
		  $gameTypeLine $homeAwaySql
		  ORDER BY game_date ASC
	  ) as derived
		";
    //echo $sqlOut.$sql;
    $query = $this->db->query($sql);
	
	//mike edits to pull in summary data
	foreach ($query->result() as $row){
		$arrGoals['cumProfit'] = $row->cummulativeProfit;
		$arrGoals['avgProfit'] = $row->ProfitPercent * $params['betAmount'];
		$arrGoals['calcOdds'] = $row->cummulativeOdds;
		$arrGoals['cumProfitBetOnline'] = $row->cummulativeProfitBetOnline;
		$arrGoals['avgProfitBetOnline'] = $row->ProfitPercentBetOnline * $params['betAmount'];
    }
	//end Mike edits
	
	
    $start_date = $this->getStartDate($dates,$gameType);

    if(count($query->result())==0){
      $arrGoals['data'][$start_date] = '0';
    }else{
      $arrGoals['data'] = $this->parseData($query,'Profit','hockey');
	  $arrGoals['dataBetOnline'] = $this->parseData($query,'ProfitBetOnline','hockey');
	}
    //print_r($sql);
    //$arrGoals1 = $this->genData($arrGoals,'goals');
    return $arrGoals;
  }
	function pucklineBetOnlineMLB($params)
  {
	$homeTeamAgainst = $this->getTeamSQL($params);
    $teams = $this->getTeamIDsfromGameMLB($params['game']);
    $awayTeamID = $teams['away'];
    $homeTeamID = $teams['home'];
	$seasonDates = $this->getRegSeasonPlayoffs($params);
	
    if($params['betOnTeam'] == 'Home'){
      $bookodds = $params['homeOdds'];
      $parsedOdds = ($params['homeOdds']>=0)?($params['homeOdds']/100):-(100/$params['homeOdds']);
      $betOnlineOdds = ($params['homeOddsBetOnline']>=0)?($params['homeOddsBetOnline']/100):-(100/$params['homeOddsBetOnline']);
	  $betwinteam = $homeTeamID;
      $betloseteam = $awayTeamID;
	  $spread = $params['homeSpread'];
	  $spreadBetOnline = $params['homeSpreadBetOnline'];
	  $arrGoals['oddsPinnacle'] = $params['homeOdds'];
	  $arrGoals['oddsBetOnline'] = $params['homeOddsBetOnline'];
    }
    else{
      $bookodds = $params['awayOdds'];
      $parsedOdds = ($params['awayOdds']>=0)?($params['awayOdds']/100):-(100/$params['awayOdds']);
      $betOnlineOdds = ($params['awayOddsBetOnline']>=0)?($params['awayOddsBetOnline']/100):-(100/$params['awayOddsBetOnline']);
	  $betwinteam = $awayTeamID;
      $betloseteam = $homeTeamID;
	  $spread = $params['awaySpread'];
	  $spreadBetOnline = $params['awaySpreadBetOnline'];
	  $arrGoals['oddsPinnacle'] = $params['awayOdds'];
	  $arrGoals['oddsBetOnline'] = $params['awayOddsBetOnline'];
	}
	
	//home away sql
/*	if ($params['homeAway'] == 'allTeams')
	{
		$homeAwaySql = "";
	}
	else
	{
		$homeAwaySql = "AND (ht.team_id = @hometeam OR awt.team_id = @awayteam)";
	}*/
	//home away set #2
	$homeAwaySql = $this->getHomeAwaySQL($params);
	$gameTypeLine = $this->getGameTypeLine($params);
	
	$sqlOut = 'SET @ProfitBetOnline=0;';
	$sqlOut .= 'SET @cummulativeProfitBetOnline=0;';
	$sqlOut .= 'SET @bet='.$params['betAmount'].';';
	$sqlOut .= 'SET @odds='.$parsedOdds.';';
	$sqlOut .= 'SET @oddsBetOnline='.$betOnlineOdds;
	$sqlOut .= 'SET @spread='.$spread.';';
	$sqlOut .= 'SET @spreadBetOnline='.$spreadBetOnline.';';
	$sqlOut .= 'SET @cummulativeProfit1=0'.';';
	$sqlOut .= 'SET @cummulativeWins1=0'.';';
	$sqlOut .= 'SET @cummulativeLosses1=0'.';';
	$sqlOut .= 'SET @ProfitPercent=0'.';';
	$sqlOut .= 'SET @win=0'.';';
	$sqlOut .= 'SET @loserid=0'.';';
	$sqlOut .= 'SET @hometeam='.$homeTeamID.';';
	$sqlOut .= 'SET @awayteam='.$awayTeamID.';';
	$sqlOut .= 'SET @betwinteam='.$betwinteam.';';
	$sqlOut .= 'SET @betloseteam='.$betloseteam.';';
	$sqlOut .= 'SET @i=0'.';';
	$sqlOut .= 'SET @Profit=0'.';';
	$sqlOut .= 'SET @startDate="'.date('Y-m-d',strtotime($params['startDate'])) . '"'.';';
	$sqlOut .= 'SET @endDate="'.date('Y-m-d',strtotime($params['endDate'])) . '"'.';';
	
	//echo $homeAwaySql;
    //print_r( $sqlStrength );
	$this->db->query('SET @ProfitBetOnline=0;');
	$this->db->query('SET @cummulativeProfitBetOnline=0;');
    $this->db->query('SET @bet='.$params['betAmount']);
    $this->db->query('SET @odds='.$parsedOdds);
	$this->db->query('SET @oddsBetOnline='.$betOnlineOdds);
    $this->db->query('SET @spread='.$spread);
	$this->db->query('SET @spread='.$spreadBetOnline);
    $this->db->query('SET @cummulativeProfit1=0');
    $this->db->query('SET @cummulativeWins1=0');
    $this->db->query('SET @cummulativeLosses1=0');
	$this->db->query('SET @ProfitPercent=0');
    $this->db->query('SET @win=0');
    $this->db->query('SET @loserid=0');
    $this->db->query('SET @hometeam='.$homeTeamID);
    $this->db->query('SET @awayteam='.$awayTeamID);
    $this->db->query('SET @betwinteam='.$betwinteam);
    $this->db->query('SET @betloseteam='.$betloseteam);
    $this->db->query('SET @i=0');
	$this->db->query('SET @Profit=0');
	$this->db->query('SET @startDate="'.date('Y-m-d',strtotime($params['startDate'])) . '"');
	$this->db->query('SET @endDate="'.date('Y-m-d',strtotime($params['endDate'])) . '"');

    $sql = "
      SELECT
      @i:=@i+1 AS number,
      @win:=IF(home_team_id = @betwinteam OR away_team_id = @betwinteam,
      IF(home_team_id = @betwinteam, IF(CONVERT(home_score,SIGNED) + @spread > CONVERT(away_score,SIGNED), 1, 0),IF(CONVERT(away_score,SIGNED) + @spread > CONVERT(home_score,SIGNED), 1, 0)),
      IF(home_team_id = @betloseteam, IF(CONVERT(away_score,SIGNED) + @spread > CONVERT(home_score,SIGNED), 1, 0),IF(CONVERT(home_score,SIGNED) + @spread> CONVERT(away_score,SIGNED), 1, 0))) AS win,
      IF(@win=1, @cummulativeWins1:=@cummulativeWins1 + 1,@cummulativeWins1) AS cummulativeWins,
      IF(@win=0, @cummulativeLosses1:=@cummulativeLosses1 + 1,@cummulativeLosses1) AS cummulativeLosses,
      IF(@cummulativeLosses1/@cummulativeWins1 >= 1, @cummulativeLosses1/@cummulativeWins1*100, -@cummulativeWins1/@cummulativeLosses1*100) AS cummulativeOdds,
      IF(@win=1, @cummulativeProfit1:=@cummulativeProfit1 + (@odds * @bet), @cummulativeProfit1:=@cummulativeProfit1 - (1.0 * @bet)) AS cummulativeProfit,
	  IF(@win=1, @cummulativeProfitBetOnline:=@cummulativeProfitBetOnline + (@oddsBetOnline * @bet), @cummulativeProfitBetOnline:=@cummulativeProfitBetOnline - (1.0 * @bet)) AS cummulativeProfitBetOnline,
	  IF(@win=1, @Profit:=(@odds * @bet), @Profit:=- (1.0 * @bet)) AS Profit,
      IF(@win=1, @Profit:=(@oddsBetOnline * @bet), @Profit:=- (1.0 * @bet)) AS ProfitBetOnline,
	  @ProfitPercent:=@cummulativeProfit1/(@bet*@i) AS ProfitPercent,
      @ProfitPercentBetOnline:=@cummulativeProfitBetOnline/(@bet*@i) AS ProfitPercentBetOnline,
	  @loser:=IF(home_team_id = winner, away_team_id, home_team_id) AS loser,
      @bookodds AS bookodds,
      derived.*
	  FROM 
	  (
		  SELECT ga.* 
		  FROM mlb_game ga
		  INNER JOIN mlb_team ht ON ga.home_team_id = ht.team_id
		  INNER JOIN mlb_team awt ON ga.away_team_id = awt.team_id
		  WHERE ($homeTeamAgainst)
		  AND ga.game_date BETWEEN @startDate AND @endDate
		  $seasonDates
		  $homeAwaySql
		  ORDER BY game_date ASC
	  ) as derived
		";
    //echo $sqlOut.$sql;
    $query = $this->db->query($sql);
	
	//mike edits to pull in summary data
	foreach ($query->result() as $row){
		$arrGoals['cumProfit'] = $row->cummulativeProfit;
		$arrGoals['avgProfit'] = $row->ProfitPercent * $params['betAmount'];
		$arrGoals['calcOdds'] = $row->cummulativeOdds;
		$arrGoals['cumProfitBetOnline'] = $row->cummulativeProfitBetOnline;
		$arrGoals['avgProfitBetOnline'] = $row->ProfitPercentBetOnline * $params['betAmount'];
    }
	//end Mike edits
	
	
    $start_date = $this->getStartDate($dates,$gameType);

    if(count($query->result())==0){
      $arrGoals['data'][$start_date] = '0';
    }else{
      $arrGoals['data'] = $this->parseData($query,'Profit','baseball');
	  $arrGoals['dataBetOnline'] = $this->parseData($query,'ProfitBetOnline','baseball');
	}
    //print_r($sql);
    //$arrGoals1 = $this->genData($arrGoals,'goals');
    return $arrGoals;
  }


  function gametotals($params)
  {
	$homeTeamAgainst = $this->getTeamSQL($params);
	$teams = $this->getTeamsFromSchedual($params['game']);
    $awayTeamID = $this->getTeamIDfromSched($teams['away_team']);
    $homeTeamID = $this->getTeamIDfromSched($teams['home_team']);

    if($params['betOnTeam'] == 'Home'){
      $bookodds = $params['homeOdds'];
      $parsedOdds = ($params['homeOdds']>=0)?($params['homeOdds']/100):-(100/$params['homeOdds']);
	  $greaterOrLessThan = ">";
    }
    else{
      $bookodds = $params['awayOdds'];
      $parsedOdds = ($params['awayOdds']>=0)?($params['awayOdds']/100):-(100/$params['awayOdds']);
      $greaterOrLessThan = "<";
    }
	//home away sql
	$homeAwaySql = $this->getHomeAwaySQL($params);
	$gameTypeLine = $this->getGameTypeLine($params);
    //print_r( $sqlStrength );
    $this->db->query('SET @bet='.$params['betAmount']);
    $this->db->query('SET @odds='.$parsedOdds);
    $this->db->query('SET @total='.$params['gameTotal']);
    $this->db->query('SET @cummulativeProfit1=0.0');
    $this->db->query('SET @cummulativeWins1=0');
    $this->db->query('SET @cummulativeLosses1=0');
    $this->db->query('SET @win=0');
    $this->db->query('SET @loserid=0');
    $this->db->query('SET @hometeam='.$homeTeamID);
    $this->db->query('SET @awayteam='.$awayTeamID);
    $this->db->query('SET @i=0');
	$this->db->query('SET @Profit=0');
	$this->db->query('SET @startDate="'.date('Y-m-d',strtotime($params['startDate'])) . '"');
	$this->db->query('SET @endDate="'.date('Y-m-d',strtotime($params['endDate'])) . '"');
	
    $sql = "SELECT
      @i:=@i+1 AS number,
      @win:=IF(home_score + away_score ".$greaterOrLessThan." @total,1,
      IF(home_score + away_score = @total, 0.5,0)) AS win,
      IF(@win=1.0, @cummulativeWins1:=@cummulativeWins1 + 1,@cummulativeWins1) AS cummulativeWins,
      IF(@win=0, @cummulativeLosses1:=@cummulativeLosses1 + 1,@cummulativeLosses1) AS cummulativeLosses,
      IF(@cummulativeLosses1/@cummulativeWins1 >= 1, @cummulativeLosses1/@cummulativeWins1*100, -@cummulativeWins1/@cummulativeLosses1*100) AS cummulativeOdds,
      IF(@win=1.0, @cummulativeProfit1:=@cummulativeProfit1 + (@odds * @bet),
      IF(@win=0.5, @cummulativeProfit1:=@cummulativeProfit1,
      @cummulativeProfit1:=@cummulativeProfit1 - (1.0 * @bet))) AS cummulativeProfit,
	  IF(@win=1.0, @Profit:=(@odds * @bet), IF(@win=0.5,@Profit:=0,@Profit:=- (1.0 * @bet))) AS Profit,
      @ProfitPercent:=@cummulativeProfit1/(@bet*@i) AS ProfitPercent,
      @loser:=IF(home_team_id = winner, away_team_id, home_team_id) AS loser,
      @bookodds AS bookodds,
      derived.*
	  FROM
	  (
		  SELECT ga.*
		  FROM new_game ga
		  INNER JOIN new_team ht ON ga.home_team_id = ht.team_id
		  INNER JOIN new_team awt ON ga.away_team_id = awt.team_id
		  WHERE ($homeTeamAgainst)
		  AND ga.game_date BETWEEN @startDate AND @endDate
		  $gameTypeLine $homeAwaySql
		  ORDER BY game_date ASC
	  ) as derived
		";

	//echo $sql;
	
    $query = $this->db->query($sql);
	
	foreach ($query->result() as $row){
		$arrGoals['cumProfit'] = $row->cummulativeProfit;
		$arrGoals['avgProfit'] = $row->ProfitPercent * $params['betAmount'];
		$arrGoals['calcOdds'] = $row->cummulativeOdds;
    }
	
    $start_date = $this->getStartDate($dates,$gameType);

    if(count($query->result())==0){
      $arrGoals['data'][$start_date] = '0';
    }else{
      $arrGoals['data'] = $this->parseData($query,'Profit','hockey');
    }
    //print_r($arrGoals);
    //$arrGoals1 = $this->genData($arrGoals,'goals');
    return $arrGoals;
  }

  function gametotalsBetOnline($params)
  {
	$homeTeamAgainst = $this->getTeamSQL($params);
	$teams = $this->getTeamsFromSchedual($params['game']);
    $awayTeamID = $this->getTeamIDfromSched($teams['away_team']);
    $homeTeamID = $this->getTeamIDfromSched($teams['home_team']);

    if($params['betOnTeam'] == 'Home'){
      $bookodds = $params['homeOdds'];
      $betOnlineOdds = ($params['homeOddsBetOnline']>=0)?($params['homeOddsBetOnline']/100):-(100/$params['homeOddsBetOnline']);
	  $parsedOdds = ($params['homeOdds']>=0)?($params['homeOdds']/100):-(100/$params['homeOdds']);
	  $greaterOrLessThan = ">";
	  $arrGoals['oddsPinnacle'] = $params['homeOdds'];
	  $arrGoals['oddsBetOnline'] = $params['homeOddsBetOnline'];
    }
    else{
      $bookodds = $params['awayOdds'];
      $betOnlineOdds = ($params['awayOddsBetOnline']>=0)?($params['awayOddsBetOnline']/100):-(100/$params['awayOddsBetOnline']);
	  $parsedOdds = ($params['awayOdds']>=0)?($params['awayOdds']/100):-(100/$params['awayOdds']);
      $greaterOrLessThan = "<";
	  $arrGoals['oddsPinnacle'] = $params['awayOdds'];
	  $arrGoals['oddsBetOnline'] = $params['awayOddsBetOnline'];
    }
	//home away sql
	$homeAwaySql = $this->getHomeAwaySQL($params);
	$gameTypeLine = $this->getGameTypeLine($params);
    //print_r( $sqlStrength );
	$sqlOut = 'SET @bet='.$params['betAmount'].';';
    $sqlOut .='SET @odds='.$parsedOdds.';';
	$sqlOut .='SET @oddsBetOnline='.$betOnlineOdds.';';
    $sqlOut .='SET @total='.$params['gameTotal'].';';
    $sqlOut .='SET @totalBetOnline='.$totalBetOnline.';';
	$sqlOut .='SET @cummulativeProfit1=0.0'.';';
    $sqlOut .='SET @cummulativeWins1=0'.';';
    $sqlOut .='SET @cummulativeLosses1=0'.';';
    $sqlOut .='SET @win=0'.';';
    $sqlOut .='SET @loserid=0'.';';
    $sqlOut .='SET @hometeam='.$homeTeamID.';';
    $sqlOut .='SET @awayteam='.$awayTeamID.';';
    $sqlOut .='SET @i=0'.';';
	$sqlOut .='SET @Profit=0'.';';
	$sqlOut .='SET @startDate="'.date('Y-m-d',strtotime($params['startDate'])) . '"'.';';
	$sqlOut .='SET @endDate="'.date('Y-m-d',strtotime($params['endDate'])) . '"'.';';
	
	$this->db->query('SET @ProfitBetOnline=0;');
	$this->db->query('SET @cummulativeProfitBetOnline=0;');
    $this->db->query('SET @bet='.$params['betAmount']);
    $this->db->query('SET @odds='.$parsedOdds);
	$this->db->query('SET @oddsBetOnline='.$betOnlineOdds);
    $this->db->query('SET @total='.$params['gameTotal']);
    //$this->db->query('SET @totalBetOnline='.$totalBetOnline);
	$this->db->query('SET @cummulativeProfit1=0.0');
    $this->db->query('SET @cummulativeWins1=0');
    $this->db->query('SET @cummulativeLosses1=0');
    $this->db->query('SET @win=0');
    $this->db->query('SET @loserid=0');
    $this->db->query('SET @hometeam='.$homeTeamID);
    $this->db->query('SET @awayteam='.$awayTeamID);
    $this->db->query('SET @i=0');
	$this->db->query('SET @Profit=0');
	$this->db->query('SET @startDate="'.date('Y-m-d',strtotime($params['startDate'])) . '"');
	$this->db->query('SET @endDate="'.date('Y-m-d',strtotime($params['endDate'])) . '"');
	
    $sql = "SELECT
      @i:=@i+1 AS number,
      @win:=IF(home_score + away_score ".$greaterOrLessThan." @total,1,
      IF(home_score + away_score = @total, 0.5,0)) AS win,
      IF(@win=1.0, @cummulativeWins1:=@cummulativeWins1 + 1,@cummulativeWins1) AS cummulativeWins,
      IF(@win=0, @cummulativeLosses1:=@cummulativeLosses1 + 1,@cummulativeLosses1) AS cummulativeLosses,
      IF(@cummulativeLosses1/@cummulativeWins1 >= 1, @cummulativeLosses1/@cummulativeWins1*100, -@cummulativeWins1/@cummulativeLosses1*100) AS cummulativeOdds,
      IF(@win=1.0, @cummulativeProfit1:=@cummulativeProfit1 + (@odds * @bet),
      IF(@win=0.5, @cummulativeProfit1:=@cummulativeProfit1,
      @cummulativeProfit1:=@cummulativeProfit1 - (1.0 * @bet))) AS cummulativeProfit,
      IF(@win=1.0, @cummulativeProfitBetOnline:=@cummulativeProfitBetOnline + (@oddsBetOnline * @bet),
      IF(@win=0.5, @cummulativeProfitBetOnline:=@cummulativeProfitBetOnline,
      @cummulativeProfitBetOnline:=@cummulativeProfitBetOnline - (1.0 * @bet))) AS cummulativeProfitBetOnline,
	  IF(@win=1.0, @Profit:=(@odds * @bet), IF(@win=0.5,@Profit:=0,@Profit:=- (1.0 * @bet))) AS Profit,
      IF(@win=1.0, @ProfitBetOnline:=(@oddsBetOnline * @bet), IF(@win=0.5,@ProfitBetOnline:=0,@ProfitBetOnline:=- (1.0 * @bet))) AS ProfitBetOnline,
	  @ProfitPercent:=@cummulativeProfit1/(@bet*@i) AS ProfitPercent,
      @ProfitPercentBetOnline:=@cummulativeProfitBetOnline/(@bet*@i) AS ProfitPercentBetOnline,
	  @loser:=IF(home_team_id = winner, away_team_id, home_team_id) AS loser,
      @bookodds AS bookodds,
      derived.*
	  FROM
	  (
		  SELECT ga.*
		  FROM new_game ga
		  INNER JOIN new_team ht ON ga.home_team_id = ht.team_id
		  INNER JOIN new_team awt ON ga.away_team_id = awt.team_id
		  WHERE ($homeTeamAgainst)
		  AND ga.game_date BETWEEN @startDate AND @endDate
		  $gameTypeLine $homeAwaySql
		  ORDER BY game_date ASC
	  ) as derived
		";

	//echo $sqlOut.$sql;
	
    $query = $this->db->query($sql);
	
	foreach ($query->result() as $row){
		$arrGoals['cumProfit'] = $row->cummulativeProfit;
		$arrGoals['avgProfit'] = $row->ProfitPercent * $params['betAmount'];
		$arrGoals['calcOdds'] = $row->cummulativeOdds;
		$arrGoals['cumProfitBetOnline'] = $row->cummulativeProfitBetOnline;
		$arrGoals['avgProfitBetOnline'] = $row->ProfitPercentBetOnline * $params['betAmount'];		
    }
	
    $start_date = $this->getStartDate($dates,$gameType);

    if(count($query->result())==0){
      $arrGoals['data'][$start_date] = '0';
    }else{
      $arrGoals['data'] = $this->parseData($query,'Profit','hockey');
	  $arrGoals['dataBetOnline'] = $this->parseData($query,'ProfitBetOnline','hockey');
    }
    //print_r($arrGoals);
    //$arrGoals1 = $this->genData($arrGoals,'goals');
    return $arrGoals;
  }
  function gametotalsBetOnlineMLB($params)
  {
	$homeTeamAgainst = $this->getTeamSQL($params);
    $teams = $this->getTeamIDsfromGameMLB($params['game']);
    $awayTeamID = $teams['away'];
    $homeTeamID = $teams['home'];
	$seasonDates = $this->getRegSeasonPlayoffs($params);

    if($params['betOnTeam'] == 'Home'){
      $bookodds = $params['homeOdds'];
      $betOnlineOdds = ($params['homeOddsBetOnline']>=0)?($params['homeOddsBetOnline']/100):-(100/$params['homeOddsBetOnline']);
	  $parsedOdds = ($params['homeOdds']>=0)?($params['homeOdds']/100):-(100/$params['homeOdds']);
	  $greaterOrLessThan = ">";
	  $arrGoals['oddsPinnacle'] = $params['homeOdds'];
	  $arrGoals['oddsBetOnline'] = $params['homeOddsBetOnline'];
    }
    else{
      $bookodds = $params['awayOdds'];
      $betOnlineOdds = ($params['awayOddsBetOnline']>=0)?($params['awayOddsBetOnline']/100):-(100/$params['awayOddsBetOnline']);
	  $parsedOdds = ($params['awayOdds']>=0)?($params['awayOdds']/100):-(100/$params['awayOdds']);
      $greaterOrLessThan = "<";
	  $arrGoals['oddsPinnacle'] = $params['awayOdds'];
	  $arrGoals['oddsBetOnline'] = $params['awayOddsBetOnline'];
    }
	//home away sql
	$homeAwaySql = $this->getHomeAwaySQL($params);
	$gameTypeLine = $this->getGameTypeLine($params);
    //print_r( $sqlStrength );
	$sqlOut = 'SET @bet='.$params['betAmount'].';';
    $sqlOut .='SET @odds='.$parsedOdds.';';
	$sqlOut .='SET @oddsBetOnline='.$betOnlineOdds.';';
    $sqlOut .='SET @total='.$params['gameTotal'].';';
    $sqlOut .='SET @totalBetOnline='.$totalBetOnline.';';
	$sqlOut .='SET @cummulativeProfit1=0.0'.';';
    $sqlOut .='SET @cummulativeWins1=0'.';';
    $sqlOut .='SET @cummulativeLosses1=0'.';';
    $sqlOut .='SET @win=0'.';';
    $sqlOut .='SET @loserid=0'.';';
    $sqlOut .='SET @hometeam='.$homeTeamID.';';
    $sqlOut .='SET @awayteam='.$awayTeamID.';';
    $sqlOut .='SET @i=0'.';';
	$sqlOut .='SET @Profit=0'.';';
	$sqlOut .='SET @startDate="'.date('Y-m-d',strtotime($params['startDate'])) . '"'.';';
	$sqlOut .='SET @endDate="'.date('Y-m-d',strtotime($params['endDate'])) . '"'.';';
	
	$this->db->query('SET @ProfitBetOnline=0;');
	$this->db->query('SET @cummulativeProfitBetOnline=0;');
    $this->db->query('SET @bet='.$params['betAmount']);
    $this->db->query('SET @odds='.$parsedOdds);
	$this->db->query('SET @oddsBetOnline='.$betOnlineOdds);
    $this->db->query('SET @total='.$params['gameTotal']);
    //$this->db->query('SET @totalBetOnline='.$totalBetOnline);
	$this->db->query('SET @cummulativeProfit1=0.0');
    $this->db->query('SET @cummulativeWins1=0');
    $this->db->query('SET @cummulativeLosses1=0');
    $this->db->query('SET @win=0');
    $this->db->query('SET @loserid=0');
    $this->db->query('SET @hometeam='.$homeTeamID);
    $this->db->query('SET @awayteam='.$awayTeamID);
    $this->db->query('SET @i=0');
	$this->db->query('SET @Profit=0');
	$this->db->query('SET @startDate="'.date('Y-m-d',strtotime($params['startDate'])) . '"');
	$this->db->query('SET @endDate="'.date('Y-m-d',strtotime($params['endDate'])) . '"');
	
    $sql = "SELECT
      @i:=@i+1 AS number,
      @win:=IF(home_score + away_score ".$greaterOrLessThan." @total,1,
      IF(home_score + away_score = @total, 0.5,0)) AS win,
      IF(@win=1.0, @cummulativeWins1:=@cummulativeWins1 + 1,@cummulativeWins1) AS cummulativeWins,
      IF(@win=0, @cummulativeLosses1:=@cummulativeLosses1 + 1,@cummulativeLosses1) AS cummulativeLosses,
      IF(@cummulativeLosses1/@cummulativeWins1 >= 1, @cummulativeLosses1/@cummulativeWins1*100, -@cummulativeWins1/@cummulativeLosses1*100) AS cummulativeOdds,
      IF(@win=1.0, @cummulativeProfit1:=@cummulativeProfit1 + (@odds * @bet),
      IF(@win=0.5, @cummulativeProfit1:=@cummulativeProfit1,
      @cummulativeProfit1:=@cummulativeProfit1 - (1.0 * @bet))) AS cummulativeProfit,
      IF(@win=1.0, @cummulativeProfitBetOnline:=@cummulativeProfitBetOnline + (@oddsBetOnline * @bet),
      IF(@win=0.5, @cummulativeProfitBetOnline:=@cummulativeProfitBetOnline,
      @cummulativeProfitBetOnline:=@cummulativeProfitBetOnline - (1.0 * @bet))) AS cummulativeProfitBetOnline,
	  IF(@win=1.0, @Profit:=(@odds * @bet), IF(@win=0.5,@Profit:=0,@Profit:=- (1.0 * @bet))) AS Profit,
      IF(@win=1.0, @ProfitBetOnline:=(@oddsBetOnline * @bet), IF(@win=0.5,@ProfitBetOnline:=0,@ProfitBetOnline:=- (1.0 * @bet))) AS ProfitBetOnline,
	  @ProfitPercent:=@cummulativeProfit1/(@bet*@i) AS ProfitPercent,
      @ProfitPercentBetOnline:=@cummulativeProfitBetOnline/(@bet*@i) AS ProfitPercentBetOnline,
	  @loser:=IF(home_team_id = winner, away_team_id, home_team_id) AS loser,
      @bookodds AS bookodds,
      derived.*
	  FROM
	  (
		  SELECT ga.*
		  FROM mlb_game ga
		  INNER JOIN mlb_team ht ON ga.home_team_id = ht.team_id
		  INNER JOIN mlb_team awt ON ga.away_team_id = awt.team_id
		  WHERE ($homeTeamAgainst)
		  AND ga.game_date BETWEEN @startDate AND @endDate
		  $seasonDates
		  $homeAwaySql
		  ORDER BY game_date ASC
	  ) as derived
		";

	//echo $sqlOut.$sql;
	
    $query = $this->db->query($sql);
	
	foreach ($query->result() as $row){
		$arrGoals['cumProfit'] = $row->cummulativeProfit;
		$arrGoals['avgProfit'] = $row->ProfitPercent * $params['betAmount'];
		$arrGoals['calcOdds'] = $row->cummulativeOdds;
		$arrGoals['cumProfitBetOnline'] = $row->cummulativeProfitBetOnline;
		$arrGoals['avgProfitBetOnline'] = $row->ProfitPercentBetOnline * $params['betAmount'];		
    }
	
    $start_date = $this->getStartDate($dates,$gameType);

    if(count($query->result())==0){
      $arrGoals['data'][$start_date] = '0';
    }else{
      $arrGoals['data'] = $this->parseData($query,'Profit','baseball');
	  $arrGoals['dataBetOnline'] = $this->parseData($query,'ProfitBetOnline','baseball');
    }
    //print_r($arrGoals);
    //$arrGoals1 = $this->genData($arrGoals,'goals');
    return $arrGoals;
  }
  
  
function getHomeAwaySQL($params){
	if ($params['awayTeamHomeGms']=='awayTeamHomeGms' && (in_array('opt3', $params['teamFilters'])||in_array('opt2', $params['teamFilters'])))
	{
		$homeAwaySql= " AND (ht.team_id = @awayteam";
	}
	if ($params['awayTeamAwayGms']=='awayTeamAwayGms' && (in_array('opt3', $params['teamFilters'])||in_array('opt2', $params['teamFilters'])))
	{
		if (isset($homeAwaySql))
		{
			$homeAwaySql .= " OR awt.team_id = @awayteam";
		}
		else
		{	
			$homeAwaySql = " AND (awt.team_id = @awayteam";
		}
	}
	if ($params['homeTeamAwayGms']=='homeTeamAwayGms' && (in_array('opt1', $params['teamFilters'])||in_array('opt2', $params['teamFilters'])))
	{
		if (isset($homeAwaySql))
		{
			$homeAwaySql .= " OR awt.team_id = @hometeam";
		}
		else
		{	
			$homeAwaySql = " AND (awt.team_id = @hometeam";
		}
	}
	if ($params['homeTeamHomeGms']=='homeTeamHomeGms' && (in_array('opt1', $params['teamFilters'])||in_array('opt2', $params['teamFilters'])))
	{
		if (isset($homeAwaySql))
		{
			$homeAwaySql .= " OR ht.team_id = @hometeam";
		}
		else
		{	
			$homeAwaySql = " AND (ht.team_id = @hometeam";
		}
	}
	$homeAwaySql .= ")";
	if (in_array('opt2', $params['teamFilters']))
	{
		$homeAwaySql .= " AND ((ht.team_id = @hometeam and awt.team_id = @awayteam) OR (awt.team_id = @hometeam and ht.team_id = @awayteam))";
	}
	return $homeAwaySql;
}
  function getTeamSQL($params){
		$homeTeamAgainst='';
      if(in_array('opt1', $params['teamFilters'])){
        $homeTeamAgainst = '((ht.team_id = @hometeam '.$this->findTA($params['homeTeamOpponents'],'awt','team_id').') OR (awt.team_id = @hometeam '.$this->findTA($params['homeTeamOpponents'],'ht','team_id').'))';
      }
	  if(in_array('opt1', $params['teamFilters']) && in_array('opt3', $params['teamFilters'])){ 
		$homeTeamAgainst .= ' OR ';
	  }
      if(in_array('opt3', $params['teamFilters'])){ 
        $homeTeamAgainst .= '((ht.team_id = @awayteam '.$this->findTA($params['awayTeamOpponents'],'awt','team_id').') OR (awt.team_id = @awayteam '.$this->findTA($params['awayTeamOpponents'],'ht','team_id').'))';
      }
      
	  if(in_array('opt2', $params['teamFilters'])){
        $homeTeamAgainst = '((ht.team_id = @hometeam AND awt.team_id = @awayteam) OR (awt.team_id = @hometeam AND ht.team_id = @awayteam))';
      }
	  return $homeTeamAgainst;
	}
	function getStartDate($arrSeason,$gameType){
	    $dates=explode('-',$arrSeason);
        switch($dates[0]){
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

	function findDates($dates = '20072008-20082009-20092010-20102011-2011-2012',$prefix = 'g', $f = 1){

		$dates = str_replace("-", " OR ", $dates);
		$dates = str_replace("20072008", " $prefix.game_date BETWEEN '2007-08-01' AND '2008-06-31' ", $dates);
		$dates = str_replace("20082009", " $prefix.game_date BETWEEN '2008-08-01' AND '2009-06-31' ", $dates);
		$dates = str_replace("20092010", " $prefix.game_date BETWEEN '2009-08-01' AND '2010-06-31' ", $dates);
		$dates = str_replace("20102011", " $prefix.game_date BETWEEN '2010-08-01' AND '2011-06-31' ", $dates);
		$dates = str_replace("20112012", " $prefix.game_date BETWEEN '2011-08-01' AND '2012-06-31' ", $dates);

		if($f){
			return 'WHERE ( '.$dates.' )';
		}
		else
			return 'IN ( '.$dates.' )';
	}

	function parseData($query, $field,$sport){
		$lastValue = '0';
		$arrData2 = array();
		if(count($query->result())>0){
		foreach ($query->result() as $row)
		{
      $value = 0;
      /*
       * Step One - Find Both Team Names from IDs
       *
    [number] => 1
    [win] => 0
    [cummulativeWins] => 0
    [cummulativeLosses] => 1
    [cummulativeOdds] => 100.0000
    [cummulativeProfit] => -10.0
    [Profit] => -10.0
    [ProfitPercent] => -1.000000000
    [loser] => 1000027
    [bookodds] => 100
    [id] => 2011201220560
    [home_score] => 2
    [away_score] => 4
    [game_date] => 2011-12-31
    [number_of_periods] => 3
    [home_team_id] => 1000027
    [away_team_id] => 1000028
    [gametype] => 2
    [isFinal] => Final
    [winner] => 1000028
       */
     // print_r($row);
	 if ($sport == 'hockey')
	 {
		$homeTeamImg = '/images/team/smallteam/'.str_replace(' ','', strtolower($this->getTeamName($row->home_team_id))).'.png';
		$awayTeamImg = '/images/team/smallteam/'.str_replace(' ','', strtolower($this->getTeamName($row->away_team_id))).'.png';
	 }
	 if ($sport == 'baseball')
	 {
		$homeTeamImg = '/images/mlbteam/'.str_replace(' ','', strtolower($this->getTeamNameMLB($row->home_team_id))).'.png';
		$awayTeamImg = '/images/mlbteam/'.str_replace(' ','', strtolower($this->getTeamNameMLB($row->away_team_id))).'.png';
	 }
	  if ($row->win == 1)
	  {
		$checkMark = '/images/checkmark.png';
	  }
	  else if ($row->win == 0.5)
	  {
		$checkMark ='';
	  }
	  else 
	  {
		$checkMark = '/images/redx.png';
	  }
	  if(!isset($arrData[$row->game_date])){
        $nightlyProfit = $row->Profit;
        $arrData[$row->game_date] = array($row->$field, date("d-M-y",strtotime($row->game_date))."<table><tr><td><img src='$awayTeamImg' /> $row->away_score </td><td> @ </td><td> $row->home_score <img src='$homeTeamImg' /></td><td width='20'><img src='$checkMark' /></td></tr>",$nightlyProfit);
      }
      else{
        $value = $arrData[$row->game_date][0] + $row->$field;
        $nightlyProfit = $arrData[$row->game_date][2] + $row->Profit;
        $html = $arrData[$row->game_date][1] ."<table><tr><td><img src='$awayTeamImg' /> $row->away_score </td><td> @ </td><td> $row->home_score <img src='$homeTeamImg' /></td><td width='20'><img src='$checkMark' /></td></tr>";
        $arrData[$row->game_date] = array($value, $html,$nightlyProfit);
      }

	  }
		//print_r( $arrData);
		$total=0;

		foreach ($arrData as $key => $value)
		{
			//$value = (empty($row->$field))?$lastValue:$row->$field;
			$total += $value[0];
			$value[2] = round($value[2],2);
      $fuckYou = "<tr><td colspan='3'>Nightly Profit: <b>\$".$value[2]."</b></td></tr></table>";
			$arrData2[$key] = "$total, $value[1]".$fuckYou;
			//$lastValue= $value;
		}
		//print_r($arrData2);
		return $arrData2;
		}
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
			$arrLines[0][] = date("d-M-y",strtotime($date));
			//$arrLines[0][] = $date;
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

		//return json_encode($arrLines);
		return $output2;
	}

	function _gameData($player_name,$date){
		$a = array(
		'Washington Capitals' => 'capitals',
		'Pittsburgh Penguins' => 'penguins',
		'Tampa Bay Lightning' => 'lightning',
		'Ottawa Senators' => 'senators',
		'Edmonton Oilers' => 'oilers',
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

	function buildStatTA($compareTA = '1000015-1000024-1000006-1000022-1000023-1000009-1000008-1000030-1000027-1000010-1000011-1000025-1000029-1000028-1000012-1000014-1000013-1000002-1000026-1000003-1000007-1000019-1000018-1000004-1000005-1000021-1000017-1000016-1000001-1000020', $postFix = 'Home'){
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
			<div class='confTitle'><a id='westConfSel$postFix'>Western Conference</a></div>
				<div id='centralTeams' class='teamSel'>
					<ul class='checklistW'>
						<li class='divTitleW$postFix'><a id='cenTeamSel$postFix'>Central</a></li>";
		$i=0;
		foreach($arrTA_WC as $wc_team){
			if(in_array($wc_team,$arrCompTA)){
			$ch = 'checked="checked"';
			}
			else {$ch = '';}
			$out .="<li>
				<input type='checkbox' name='teamOpponent$postFix' value='$wc_team' $ch />
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
						<li class='divTitleW$postFix'><a id='nwTeamSel$postFix'>Northwest</a></li>";
		$i=0;
		$arrTA_WN = array('1000009','1000008','1000030','1000027','1000010');
		$arrTA_WN_Names = array('Flames','Avalanche','Oilers','Wild','Canucks');
		foreach($arrTA_WN as $wn_team){
			if(in_array($wn_team,$arrCompTA)){
			$ch = 'checked="checked"';
			}
			else {$ch = '';}
			$out .="<li>
				<input type='checkbox' name='teamOpponent$postFix' value='$wn_team' $ch />
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
						<li class='divTitleW$postFix'><a id='pacTeamSel$postFix'>Pacific</a></li>";
		$i=0;
		$arrTA_WP = array('1000011','1000025','1000029','1000028','1000012');
		$arrTA_WP_Names = array('Ducks','Stars','Kings','Coyotes','Sharks');
		foreach($arrTA_WP as $wp_team){
			if(in_array($wp_team,$arrCompTA)){
			$ch = 'checked="checked"';
			}
			else {$ch = '';}
			$out .="<li>
				<input type='checkbox' name='teamOpponent$postFix' value='$wp_team' $ch />
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
			<div class='confTitle'><a id='eastConfSel$postFix'>Eastern Conference</a></div>
			<div id='atlTeams' class='teamSel'>
			<ul class='checklistE'>
				<li class='divTitleE$postFix'><a id='atlTeamSel$postFix'>Atlantic</a></li>";
		$i=0;
		$arrTA_EA = array('1000014','1000013','1000002','1000026','1000003');
		$arrTA_EA_Names = array('Devils','Islanders','Rangers','Flyers','Penguins');
		foreach($arrTA_EA as $ea_team){
			if(in_array($ea_team,$arrCompTA)){
			$ch = 'checked="checked"';
			}
			else {$ch = '';}
			$out .="<li>
				<input type='checkbox' name='teamOpponent$postFix' value='$ea_team' $ch />
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
				<li class='divTitleE$postFix'><a id='neTeamSel$postFix'>Northeast</a></li>";
		$i=0;
		$arrTA_EN = array('1000007','1000019','1000018','1000004','1000005');
		$arrTA_EN_Names = array('Briuns','Sabres','Canadiens','Senators','Maple Leafs');
		foreach($arrTA_EN as $en_team){
			if(in_array($en_team,$arrCompTA)){
			$ch = 'checked="checked"';
			}
			else {$ch = '';}
			$out .="<li>
				<input type='checkbox' name='teamOpponent$postFix' value='$en_team' $ch />
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
				<li class='divTitleE$postFix'><a id='seTeamSel$postFix'>Southeast</a></li>";
		$i=0;
		$arrTA_ES = array('1000021','1000017','1000016','1000001','1000020');
		$arrTA_ES_Names = array('Jets','Hurricanes','Panthers','Lightning','Capitals');
		foreach($arrTA_ES as $es_team){
			if(in_array($es_team,$arrCompTA)){
			$ch = 'checked="checked"';
			}
			else {$ch = '';}
			$out .="<li>
				<input type='checkbox' name='teamOpponent$postFix' value='$es_team' $ch />
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
  function getTeamsFromSchedual($gameID){
    $sql = "SELECT * FROM `nhl_schedual` where id = $gameID";
    $value = array();
    $query = $this->db->query($sql);
    foreach ($query->result() as $row){
      $value['away_team']  = str_replace(".","",$row->away_team);
      $value['home_team']  = str_replace(".","",$row->home_team);
    }
    return $value;
  }
   function getGameIDsFromDateinSchedual($date){
    $sql = "SELECT * 
			FROM `nhl_schedual` ns
			LEFT JOIN
				(
					SELECT od.*, ht.location AS homelocation, awt.location AS awaylocation
					FROM new_odds od
					INNER JOIN new_team ht ON CONCAT(ht.location,' ',ht.team_name) LIKE od.home_team_name
					INNER JOIN new_team awt ON CONCAT(awt.location,' ',awt.team_name) LIKE od.away_team_name
					WHERE game_date = '$date'
				) odds ON (ns.home_team LIKE odds.homelocation) or (ns.away_team LIKE odds.awaylocation)
			WHERE DATE = '$date'";
    $value['select'] = "<select name='game' id='betGame' onChange='changeTeamNames(this);'>";
    //echo $sql;
	$query = $this->db->query($sql);
	if(count($query->result())==0)
	{
		$value['select'] = "No Games";
	}
	else
	{
		$firstGameBool = false;
		$boolArray = array();
		foreach ($query->result() as $row){
		  if ($firstGameBool == false){
			$firstGameBool = true;
			$firstGameId = $row->id;
		  }
		  $game_id  = $row->id;
		  $shortTeamNames = array();
		  $shortTeamNames = $this->getTeamShortNamesFromGameID($game_id);
	//	  $home_team  = $row->home_team;
	//	  $away_team  = $row->away_team;
		  $sportsBook = $row->sportsbook_name;
		  //either sportsbook is null (in that there are no odds pulled yet) or it is pinnacle so that you don't duplicate rows
		  //echo $shortTeamNames['away_short_name'].$shortTeamNames['home_short_name'].":";
		  //echo $boolArray[$shortTeamNames['away_short_name'].$shortTeamNames['home_short_name']];
		  if (!isset($boolArray[$shortTeamNames['away_short_name'].$shortTeamNames['home_short_name']]))
		  {
			$value['select'] .= "<option value = '$game_id'>".$shortTeamNames['away_short_name']." @ ".$shortTeamNames['home_short_name']."</option>";
			$boolArray[$shortTeamNames['away_short_name'].$shortTeamNames['home_short_name']] = true;
		  }
		  $value['jsonString']["$sportsBook"]['home_team_moneyline'][$game_id] = $row->home_team_moneyline;
		  $value['jsonString']["$sportsBook"]['away_team_moneyline'][$game_id] = $row->away_team_moneyline;
		  $value['jsonString']["$sportsBook"]['home_team_spread'][$game_id] = $row->home_team_spread;
		  $value['jsonString']["$sportsBook"]['away_team_spread'][$game_id] = $row->away_team_spread;
		  $value['jsonString']["$sportsBook"]['home_team_spread_adjust'][$game_id] = $row->home_team_spread_adjust;
		  $value['jsonString']["$sportsBook"]['away_team_spread_adjust'][$game_id] = $row->away_team_spread_adjust;
		  $value['jsonString']["$sportsBook"]['total_points'][$game_id] = $row->total_points;
		  $value['jsonString']["$sportsBook"]['total_over_adjust'][$game_id] = $row->total_over_adjust;
		  $value['jsonString']["$sportsBook"]['total_under_adjust'][$game_id] = $row->total_under_adjust;
		}
		$value['jsonString'] = json_encode($value['jsonString']);
		//echo $value['jsonString'];
		$value['firstGameId'] = $firstGameId;
		$value['select'] .= "</select>";
	}
    return $value;
  }
  
   function getGameIDsFromDateinSchedualMLB($date){
    $sql = "SELECT *, sched.away_team_id as sched_away_team_id, sched.home_team_id as sched_home_team_id
			FROM `mlb_schedule` sched
			LEFT JOIN
				(
					SELECT od.*, ht.location AS homelocation, awt.location AS awaylocation
					FROM new_odds_MLB od
					INNER JOIN mlb_team ht ON ht.team_id = od.home_team_id
					INNER JOIN mlb_team awt ON awt.team_id = od.away_team_id
					WHERE game_date = '$date'
				) odds ON sched.home_team_id = odds.home_team_id
			WHERE DATE = '$date'";
    $value['select'] = "<select name='game' id='betGame' onChange='changeTeamNames(this);'>";
    //echo $sql;
	$query = $this->db->query($sql);
	//print_r($query->result());
	if(count($query->result())==0)
	{
		$value['select'] = "No Games";
	}
	else
	{
		$firstGameBool = false;
		$boolArray = array();
		foreach ($query->result() as $row){
		  if ($firstGameBool == false){
			$firstGameBool = true;
			$firstGameId = $row->id;
		  }
		  
		  $game_id  = $row->id;
		  //print_r($row);
		  $home_team_id = $row->sched_home_team_id;
		  $away_team_id = $row->sched_away_team_id;
		  $shortHomeName = $this->getTeamShortNamesFromTeamIDMLB($home_team_id);
		  $shortAwayName = $this->getTeamShortNamesFromTeamIDMLB($away_team_id);

	//	  $home_team  = $row->home_team;
	//	  $away_team  = $row->away_team;
		  
		  $sportsBook = $row->sportsbook_name;
		  //echo $sportsBook; 
		  //either sportsbook is null (in that there are no odds pulled yet) or it is pinnacle so that you don't duplicate rows
		  //echo "testing";
		  //echo $shortHomeName.$shortAwayName.":";
		  //echo $boolArray[$shortHomeName.$shortAwayName];
		  
		  //first set all the Pinnacle to default
			
			$value['jsonString']["Pinnacle"]['home_team_moneyline'][$game_id]  = "Not Available (default 100)";
			$value['jsonString']["Pinnacle"]['away_team_moneyline'][$game_id]  = "Not Available (default 100)";
			$value['jsonString']["Pinnacle"]['home_team_spread'][$game_id]  = "";
			$value['jsonString']["Pinnacle"]['away_team_spread'][$game_id]  = "";
			$value['jsonString']["Pinnacle"]['home_team_spread_adjust'][$game_id] = "Not Available (default 100)";
			$value['jsonString']["Pinnacle"]['away_team_spread_adjust'][$game_id] = "Not Available (default 100)";
			$value['jsonString']["Pinnacle"]['total_points'][$game_id] = "";
			$value['jsonString']["Pinnacle"]['total_over_adjust'][$game_id] = "Not Available (default 100)";
			$value['jsonString']["Pinnacle"]['total_under_adjust'][$game_id] = "Not Available (default 100)";
		  
		  if (!isset($boolArray[$shortHomeName.$shortAwayName]))
		  {
			$value['select'] .= "<option value = '$game_id'>$shortAwayName @ $shortHomeName</option>";
			$boolArray[$shortHomeName.$shortAwayName] = true;
		  }
		  //echo "test:".!isset($row->home_team_moneyline);
		  if (!isset($row->home_team_moneyline))
		  {
			$value['jsonString']["Pinnacle"]['home_team_moneyline'][$game_id] = "Not Available (default 100)";
		  }
		  else if ($row->home_team_moneyline == 0)
		  {
			$value['jsonString']["$sportsBook"]['home_team_moneyline'][$game_id] = "Not Available (default 100)";
		  }
		  else
		  {
			$value['jsonString']["$sportsBook"]['home_team_moneyline'][$game_id] = $row->home_team_moneyline;
		  }
		  
		  
		  if (!isset($row->away_team_moneyline))
		  {
			$value['jsonString']["Pinnacle"]['away_team_moneyline'][$game_id] = "Not Available (default 100)";
		  }
		  else if($row->away_team_moneyline == 0)
		  {
			$value['jsonString']["$sportsBook"]['away_team_moneyline'][$game_id] = "Not Available (default 100)";
		  }
		  else
		  {
			$value['jsonString']["$sportsBook"]['away_team_moneyline'][$game_id] = $row->away_team_moneyline;
		  }
		  
		  if (!isset($row->home_team_spread))
		  {
			$value['jsonString']["Pinnacle"]['home_team_spread'][$game_id] = "";
		  }
		  else if($row->home_team_spread == 0)
		  {
			$value['jsonString']["$sportsBook"]['home_team_spread'][$game_id] = "";
		  }
		  else
		  {
			$value['jsonString']["$sportsBook"]['home_team_spread'][$game_id] = $row->home_team_spread;
		  }

		  if (!isset($row->away_team_spread))
		  {
			$value['jsonString']["Pinnacle"]['away_team_spread'][$game_id] = "";
		  }
		  else if($row->away_team_spread == 0)
		  {
			$value['jsonString']["$sportsBook"]['away_team_spread'][$game_id] = "";
		  }
		  else
		  {
			$value['jsonString']["$sportsBook"]['away_team_spread'][$game_id] = $row->away_team_spread;
		  }
		  
		  if (!isset($row->home_team_spread_adjust))
		  {
			$value['jsonString']["Pinnacle"]['home_team_spread_adjust'][$game_id] = "Not Available (default 100)";
		  }
		  else if($row->home_team_spread_adjust == 0)
		  {
			$value['jsonString']["$sportsBook"]['home_team_spread_adjust'][$game_id] = "Not Available (default 100)";
		  }
		  else
		  {
			$value['jsonString']["$sportsBook"]['home_team_spread_adjust'][$game_id] = $row->home_team_spread_adjust;
		  }		  
		  
		  if (!isset($row->away_team_spread_adjust))
		  {
			$value['jsonString']["Pinnacle"]['away_team_spread_adjust'][$game_id] = "Not Available (default 100)";
		  }
		  else if($row->away_team_spread_adjust == 0)
		  {
			$value['jsonString']["$sportsBook"]['away_team_spread_adjust'][$game_id] = "Not Available (default 100)";
		  }
		  else
		  {
			$value['jsonString']["$sportsBook"]['away_team_spread_adjust'][$game_id] = $row->away_team_spread_adjust;
		  }	
		  
		  if (!isset($row->total_points))
		  {
			$value['jsonString']["Pinnacle"]['total_points'][$game_id] = "";
		  }
		  else if($row->total_points == 0)
		  {
			$value['jsonString']["$sportsBook"]['total_points'][$game_id] = "";
		  }
		  else
		  {
			$value['jsonString']["$sportsBook"]['total_points'][$game_id] = $row->total_points;
		  }	
		  
		  if (!isset($row->total_over_adjust))
		  {
			$value['jsonString']["Pinnacle"]['total_over_adjust'][$game_id] = "Not Available (default 100)";
		  }
		  else if($row->total_over_adjust == 0)
		  {
			$value['jsonString']["$sportsBook"]['total_over_adjust'][$game_id] = "Not Available (default 100)";
		  }
		  else
		  {
			$value['jsonString']["$sportsBook"]['total_over_adjust'][$game_id] = $row->total_over_adjust;
		  }	
		  
		  if (!isset($row->total_under_adjust))
		  {
			$value['jsonString']["Pinnacle"]['total_under_adjust'][$game_id] = "Not Available (default 100)";
		  }
		  else if($row->total_under_adjust == 0)
		  {
			$value['jsonString']["$sportsBook"]['total_under_adjust'][$game_id] = "Not Available (default 100)";
		  }
		  else
		  {
			$value['jsonString']["$sportsBook"]['total_under_adjust'][$game_id] = $row->total_under_adjust;
		  }	
		  
		}
		//echo $value['select'];
		$value['jsonString'] = json_encode($value['jsonString']);
		//echo $value['jsonString'];
		$value['firstGameId'] = $firstGameId;
		$value['select'] .= "</select>";
		//echo $value['select'];
	}
    return $value;
  }
  
  function getTeamShortNamesFromGameID($gameID){
	$value = array();
	$team_names = $this->getTeamsFromSchedual($gameID);
	//short_away_team
	$sql = "SELECT * FROM nhl_sched_team_map WHERE nhl_schedule_team LIKE '%".$team_names['away_team']."%'";
	//echo $sql;
	$query = $this->db->query($sql);
	foreach ($query->result() as $row){
      $value['team_id']  = $row->team_id;
	  $value['away_short_name'] = $row->short_name;
	  }
	//short_home_team
	$sql = "SELECT * FROM nhl_sched_team_map WHERE nhl_schedule_team LIKE '%".$team_names['home_team']."%'";
	//echo $sql;
	$query = $this->db->query($sql);
	foreach ($query->result() as $row){
      $value['team_id']  = $row->team_id;
	  $value['home_short_name'] = $row->short_name;
	  }
	return $value;
  }
  function getTeamShortNamesFromTeamIDMLB($teamID){
	//short_away_team
	$sql = "SELECT * FROM mlb_team where team_id = $teamID";
	//echo $sql;
	$query = $this->db->query($sql);
	foreach ($query->result() as $row){
      $value = $row->short_name;
	 }
	return $value;
  }

  function getLowerCaseTeamFromShortTeam($shortTeam){
		$a = array(
		'WSH' => 'capitals',
		'PIT' => 'penguins',
		'TBL' => 'lightning',
		'OTT' => 'senators',
		'NYR' => 'rangers',
		'DET' => 'redwings',
		'TOR' => 'mapleleafs',
		'COL' => 'avalanche',
		'BOS' => 'bruins',
		'CGY' => 'flames',
		'VAN' => 'canucks',
		'SJS' => 'sharks',
		'ANA' => 'mightyducks',
		'NJD' => 'devils',
		'NYI' => 'islanders',
		'NYR' => 'rangers',
		'CHI' => 'blackhawks',
		'CAR' => 'hurricanes',
		'FLA' => 'panthers',
		'BUF' => 'sabres',
		'MTL' => 'canadiens',
		'WPG' => 'winnipeg',
		'STL' => 'blues',
		'NSH' => 'predators',
		'DAL' => 'stars',
		'CBJ' => 'bluejackets',
		'PHI' => 'flyers',
		'MIN' => 'wild',
		'PHX' => 'coyotes',
		'LAK'=>'kings'
		);
		return $a[$shortTeam];
		}
  function getTeamID($location){
    $sql = "SELECT team_id FROM `new_team` where location = '$location'";
    $value = '';
    $query = $this->db->query($sql);
    foreach ($query->result() as $row){
      $value  = $row->team_id;
    }
    return $value;
  }
   function getTeamIDfromSched($location){
    $sql = "SELECT team_id FROM `nhl_sched_team_map` where nhl_schedule_team like '%$location%'";
    $value = '';
    $query = $this->db->query($sql);
    foreach ($query->result() as $row){
      $value  = $row->team_id;
    }
    return $value;
  }
  function getTeamName($id){
    $sql = "SELECT team_name FROM `new_team` where team_id = '$id'";
    $value = '';
    $query = $this->db->query($sql);
    foreach ($query->result() as $row){
      $value  = $row->team_name;
    }
    return $value;
  }
    function getTeamNameMLB($id){
    $sql = "SELECT team_name FROM `mlb_team` where team_id = '$id'";
    $value = '';
    $query = $this->db->query($sql);
    foreach ($query->result() as $row){
      $value  = $row->team_name;
    }
    return $value;
  }
  function getGameTypeLine ($params){
	$gameTypeLine = '';
	if (isset($params['playoffs']) && isset($params['regSeason']))
	{
		$gameTypeLine ='AND (ga.gametype = 2 OR ga.gametype = 3)';
	}
	else if (isset($params['playoffs']))
	{
		$gameTypeLine ='AND (ga.gametype = 3)';
	}
	else if (isset($params['regSeason']))
	{
		$gameTypeLine ='AND (ga.gametype = 2)';
	}
	else
	{
		$gameTypeLine ='AND (ga.gametype = 0)';
	}
	return $gameTypeLine;
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

	function trackRequest($params)
	{
		$paramsForSQL = mysql_real_escape_string(json_encode($params));
		$timeStamp = time();
		$userIP=$_SERVER['REMOTE_ADDR'];
		$sql = "INSERT INTO pinnacle_requests (request_time, json_request, user_ip) values ('$timeStamp','$paramsForSQL','$userIP')";
		$query = $this->db->query($sql);
		//$sql;
	}
/*	function getPinMLBidfromName($teamName)
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
		$query = $this->db->query($sql);
		foreach ($query->result() as $row)
		{
			$teamID = $row->team_id;
		}
		return $teamID;
	}/*
/*	function getBOMLBidfromName($teamName)
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
		$query = $this->db->query($sql);
		foreach ($query->result() as $row)
		{
			$teamID = $row->team_id;
		}
		return $teamID;
	}	*/
	function getTeamIDsfromGameMLB($gameID)
	{
		$sql = "Select * from mlb_schedule where id like '$gameID'";
		$query = $this->db->query($sql);
		foreach ($query->result() as $row)
		{
			$teamIDs['home'] = $row->home_team_id;
			$teamIDs['away'] = $row->away_team_id;
		}
		return $teamIDs;		
	}
  function getTeamMLB($divisionName, $leagueName, $all = false){
    if($all){
      $sql = "Select * from mlb_team";
    }
    else{
      $sql = "Select * from mlb_team where division_name like '$divisionName' and league_name like '$leagueName'";
    }
    $query = $this->db->query($sql);
    $team = array();
    foreach ($query->result() as $row)
    {
      $team[$row->team_id] = $row->team_name;
    }
    return $team;
  }
  function buildStatTA_mlb($arrCompTA = false, $postFix = 'Home'){
    if(!$arrCompTA){
      $arrCompTA = $this->getTeamMLB('', '', 1);
    }
    $out = '';
    //go through conf by conf
    $legacyContainer = array('taLeft','taRight');
    $leagueMLB = array('american','national');
    $divisionMLB = array('east','central','west');
    foreach($leagueMLB as $league){
      $restLeague = substr($league, 0, 3);
      $out .= "<div id='".$league."TeamList' class='".current($legacyContainer)."'>
                <div class='confTitle'><a id='".$restLeague."ConfSel$postFix'>$league</a></div>";
      foreach($divisionMLB as $division){
        $rest = substr($division, 0, 3);
        $out .= "<div id='".$division."TeamList' class='teamSel'>
        <ul class='checklist'>
        <li class='divTitleW$postFix'><a id='".$rest."TeamSel$postFix'>$division</a></li>";
        $arrTeams = $this->getTeamMLB($division, $league);
        foreach($arrTeams as $key => $team){
          if(array_key_exists($key,$arrCompTA)){
            $ch = 'checked="checked"';
          }
          else {$ch = '';}
          $out .="<li>
                    <input type='checkbox' name='teamOpponent$postFix' value='$key' $ch />
                    <a class='checkbox-select' href='#'>$team</a>
                    <a class='checkbox-deselect' href='#'>$team</a>
                    </li>";
        }
        $out .= '</ul></div>';
      }
      $out .="</div>";
      next($legacyContainer);
    }
    return $out;
  }
  function getRegSeasonPlayoffs($params)
  {
  	$seasonDates = "";
	if ($params['regSeason'] == "on" || $params['playoffs'] == "on")
	{
		$seasonDates = "AND (";
		if ($params['regSeason'] == "on")
		{
			$seasonDates .= "(ga.game_date BETWEEN '2013-03-31' AND '2013-09-29') OR (ga.game_date BETWEEN '2012-03-28' AND '2012-10-03') OR (ga.game_date BETWEEN '2011-03-31' AND '2011-09-28') OR (ga.game_date BETWEEN '2010-04-04' AND '2010-10-03') OR (ga.game_date BETWEEN '2009-04-05' AND '2010-10-06')";
			if ($params['playoffs'] == "on")
			{
				$seasonDates .= " OR ";
			}
		}
		if ($params['playoffs'] == "on")
		{
			$seasonDates .= "(ga.game_date BETWEEN '2010-10-04' AND '2012-10-28') OR(ga.game_date BETWEEN '2011-09-29' AND '2011-10-29') OR (ga.game_date BETWEEN '2010-10-04' AND '2010-11-02') OR (ga.game_date BETWEEN '2009-10-07' AND '2010-11-05')";
		}
		$seasonDates .= ")";
	}
	return $seasonDates;
  }
}
?>