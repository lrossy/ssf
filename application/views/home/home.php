<?php $this->load->view('header'); ?>
<?php
//$statsPage = site_url('/stats/');
//$updYesterdayScores = site_url('/welcome/updYest');
//$updTodayScores = site_url('/welcome/updToday');
//$updGame = site_url('/welcome/gameInfo');
//$updGameNotPlayed = site_url('/welcome/gameInfoNotPlayed');
//$seasonLeaderboards = site_url('/welcome/getLeadSeason');
//$tonightLeaderboards = site_url('/welcome/getLeadTonight');
//$yesterdayLeaderboards = site_url('/welcome/getLeadYesterday');
//$ajaxTeamStandings = site_url('/welcome/getStandings');
//
$statsPage = '/stats/';
$updYesterdayScores = '/welcome/updYest';
$updTodayScores = '/welcome/updToday';
$updGame = '/welcome/gameInfo';
$updGameNotPlayed = '/welcome/gameInfoNotPlayed';
$seasonLeaderboards = '/welcome/getLeadSeason';
$tonightLeaderboards = '/welcome/getLeadTonight';
$yesterdayLeaderboards = '/welcome/getLeadYesterday';
$ajaxTeamStandings = '/welcome/getStandings';
?>
<SCRIPT LANGUAGE="JavaScript">
<!--
	var STATS_PAGE = '<?=$statsPage?>';
	var activegame = '<?=$feat?>';
	var sd = StatsDaily.init();
//-->
</SCRIPT>
<div style='height:370px;'>
	<div id='nightlyScores'>
		<div id='activeGameInfo'>
		<?=$gameInfo?>
		</div>
		<div id='gameList'>
			<h3>Scores</h3>
			<div id='todaysScores'>
			<h4>Today</h4>
				<div id='tdScoresCont'>
					No Games
				</div>
			</div>
			<div id='yestScores'>
			<h4>Yesterday</h4>
				<div id='tdYestScoresCont'>
					No Games
				</div>
			</div>
		</div>
		<div class='clear'></div>
	</div>
	<div id='statsMachine'>
		<div id='statsMachButtons'>
			<div class='statsMachButt' id='smB1'>
				<span>Leaderboards</span>
				<div class='arrow'></div>
			</div>
			<ul class="menu_sm" id='mLeaders'>
			<li><a href="<?=site_url('/stats/index/goals')?>">Goals</a></li>
			<li><a href="<?=site_url('/stats/index/assists')?>">Assists</a></li>
			<li><a href="<?=site_url('/stats/index/points')?>">Points</a></li>
			<li><a href="<?=site_url('/stats/index/plus_minus')?>">Plus / Minus</a></li>
			<li><a href="<?=site_url('/stats/index/pim')?>">Penalities</a></li>
			</ul>
			<div class='statsMachButt' id='smB2'>
				<span>Choose a Team</span>
				<div class='arrow'></div>
			<ul class="menu_sm" id='mTeams'>
			<li><a href="#">Team 1</a></li>
			<li><a href="#">Team 2</a></li>
			<li><a href="#">Team 3</a></li>
			<li><a href="#">Team 4</a></li>
			</ul>
			</div>
			<div class='statsMachButt' id='smB3'>
				<span>Custom Query</span>
				<div class='arrowNext'></div>
			</div>
		</div>
	</div>
	<div id='statsMachArrow'>
	&nbsp;
	</div>
</div>
<div style=''>
	<div id='teamStandings'>
		<div id='teamStandingsImg'>
		&nbsp;
		</div>
		<div id='teamStandingsOpts'>
			<div id='tsOptLeft'>
				<span id = 'standSelWest' class='button'>West</span> - <span id = 'standSelEast' class='act button'>East</span>
			</div>
			<div id='tsOptRight'>
				<div id = 'standSelConf' class='button'>CONF</div><div id = 'standSelDiv' class='sel button'>DIV</div>
			</div>
		</div>
		<div id='teamStandingsData'>
		</div>
		<input type="hidden" id="frm_standSelConf" value='Eastern'>
		<input type="hidden" id="frm_standSelDiv" value='1'>
	</div>
	<div id='teamStandingsSide'>
	&nbsp;
	</div>

	<div id='allLeaders'>
		<div id='playerLeaders'>
		<div id = 'plOpt'><span id='plOptPlayoffs' class='act button'>playoffs</span> | <span id='plOptSeason' class='button'>season</span> | <span id='plOptTonight' class='button'>today</span> | <span id='plOptYesterday' class='button'>yesterday</span></div>
		<div id = 'plLeaderData'>
		<table>
		<tr>
			<th>Points</th>
			<th>Goals</th>
			<th>Assists</th>
			<th>Penalty Min.</th>
			<th>Plus Minus</th>
		</tr>

		<tr id='playerLeaderBoards'>

			<td><div id='topLeaderPoints'></div></td>
			<td><div id='topLeaderGoals'></div></td>
			<td><div id='topLeaderAssists'></div></td>
			<td><div id='topLeaderPIM'></div></td>
			<td><div id='topLeaderP_M'></div></td>
		</tr>
		</table>
		</div>
		</div>
		<div id='goalieLeaders'>
		<div id = 'glOpt'><span id='glOptPlayoffs' class='act button'>playoffs</span> | <span id='glOptSeason' class='button'>season</span> | <span id='glOptTonight' class='button'>today</span> | <span id='glOptYesterday' class='button'>yesterday</span></div>

			<div id = 'glLeaderData'>
				<table>
				<tr>
					<th>Wins</th>
					<th>Save %</th>
					<th>GAA</th>
					<th>Shutouts</th>
				</tr>
				<tr id='playerLeaderBoards'>
					<td><div id='topLeaderWins'></div></td>
					<td><div id='topLeaderSVP'></div></td>
					<td><div id='topLeaderGAA'></div></td>
					<td><div id='topLeaderSO'></div></td>
				</tr>
				</table>
			</div>
		</div>
	</div>
</div>
<?php $this->load->view('footer'); ?>