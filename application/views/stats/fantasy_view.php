<?php $this->load->view('header'); ?>
<div id="container">
<?php if($message!=''){
  ?>
<div id="infoMessage"><?php echo $message;?></div>
  <?php
}?>
<?php
$today = date("Y-m-d");
$yesterday = strtotime('-1 day',strtotime($today));
$yesterday = date('m/d/Y', $yesterday);

//if($statistic) $print_r($_POST);
$url_baseURL= site_url();
$sport = $this->uri->segment(2);
$sportUCase = ucfirst($sport);
$mainURI = $this->uri->segment(1);
$url_autocomplete= site_url("stats/getACPlayerList/");
$url_checkplayer= site_url("stats/checkPlayer/");
$url_getimage= site_url("fantasy/get_player_image_URL_from_name/");
$url_fantasy_hockey= site_url("fantasy/hockey/");
$url_fantasy_hockey_setup= site_url("fantasy/setup_league/");
$url_change_team= site_url("fantasy/change_team/"); 
$url_setup_trade= site_url("fantasy/make_trade/");
$url_execute_trade= site_url("fantasy/execute_trade/");
$url_compare_all_teams= site_url("fantasy/compare_all_teams");
$url_recalc_fantasy= site_url("fantasy/recalculate_projections");
$url_get_fantasy_date = site_url("fantasy/get_cur_date");
$url_logoff_fantasy = site_url("fantasy/log_off_yahoo");
$url_fantasy_reload_league = site_url("fantasy/reload_league");
$url_logon_fantasy = site_url("fantasy/log_on_yahoo");
$url_get_summary = site_url("fantasy/get_summary");
$url_fantasy_reload_league_settings = site_url("fantasy/load_yahoo_settings");
$url_fantasy_reload_yahoo_teams = site_url("fantasy/load_yahoo_teams");
$leagueType = $_SESSION['leagueType'];
$outputTest = $_SESSION['outputTest'];
$out =<<<EOT


<noscript><div>Your browser does not support JavaScript!</div></noscript>
<form name="statform" id="statform" action=''>

<script type="text/javascript" >
	var sport = '$sport';
	var mainURI = '$mainURI';
	var URL_BASEURL= '$url_baseURL';
	var URL_AUTOCOMPLETE = '$url_autocomplete';
	var URL_CHECKPLAYER = '$url_checkplayer';
	var URL_GETIMAGE = '$url_getimage';
	var URL_FANTASY_HOCKEY = '$url_fantasy_hockey';
	var URL_FANTASY_HOCKEY_SETUP = '$url_fantasy_hockey_setup';
	var URL_FANTASY_RELOAD = '$url_fantasy_reload_league';
	var URL_FANTASY_RELOAD_SETTINGS = '$url_fantasy_reload_league_settings';
	var URL_FANTASY_RELOAD_TEAMS = '$url_fantasy_reload_yahoo_teams';
	var URL_FANTASY_LOGOFF_YAHOO = '$url_logoff_fantasy';
	var URL_FANTASY_LOGON_YAHOO = '$url_logon_fantasy';
	var URL_CHANGE_TEAM = '$url_change_team';
	var URL_SETUP_TRADE = '$url_setup_trade';
	var URL_EXECUTE_TRADE = '$url_execute_trade';
	var URL_COMPARE_ALL_TEAMS = '$url_compare_all_teams';
	var RESET_STATE_BOOL = false;
	var URL_RECALC = '$url_recalc_fantasy';
	var URL_FANTASY_GET_DATE = '$url_get_fantasy_date';
	var URL_FANTASY_GET_SUMMARY = '$url_get_summary';
	var players_to_add_array = [];
	var gamble=false;

</script>

<div id='eastContainer3'>
	<div id='statBlock3'>
		<div id="statSidePin">
			&nbsp;
		</div>
		<div id='statTypeSelect'>
			<div class='smStatButt' id='smStat'>
				<span id='currentStat'>$sportUCase Fantasy</span>
			</div>
		</div>
		<div id='smOptsContainer3'>
			<table>
				<tr><th class="topHeader">Base Projections On:</th></tr>
				<tr><td><input class="radioClass" id='7days' value='7days' name='projTimePeriod' type='radio' checked='checked'/><label for='7days'>Last Week</label></td></tr>
				<tr><td><input class="radioClass" id='30days' value='30days' name='projTimePeriod' type='radio' /><label for='30days'>Last 30 Days</label></td></tr>
				<tr><td><input class="radioClass" id='currentSeason' value='currentSeason' name='projTimePeriod' type='radio' /><label for='currentSeason'>Current Season</label></td></tr>
				<tr><td><input class="radioClass" id='lastSeason' value='lastSeason' name='projTimePeriod' type='radio' /><label for='lastSeason'>Last Season</label></td></tr>
				<tr><td><input class="radioClass" id='customPeriod' value='customPeriod' name='projTimePeriod' type='radio' onclick='javascript:unhide_dates();'/><label for='customPeriod'>Custom Period</label></td></tr>
				<tr id="hidden_row"><td><input type="text" name="startDate" size="11" class="datepicker" value="03/31/2013"/><input type="text" name="endDate" size="11" class="datepicker" value="$yesterday"/></td></tr>
				<tr><td style='text-align:center;'><div class='div_img_button' onclick='javascript:recalc_projections();'><img src='/images/recalculateProj.png' /></div></td></tr>
				<tr>
				<th>Analyze Your Fantasy Team:</th>
				</tr>
				<tr>
				<td><div id='fantasy_options'>
					<div id='add_drop_option' class='div_img_button' onclick='javascript:load_add_drop();'>
						<img src='/images/addDropPlayers.png'>
					</div>
					<div id='make_trade_option' class='div_img_button' onclick='javascript:load_make_trade();'>
						<img src='/images/analyzeTrade.png'>
					</div>
					<div id='compare_teams' class='div_img_button' onclick='javascript:load_compare_teams();'>
						<img src='/images/compareAllTeams.png'>
					</div>
					<div id='logon_yahoo' class='div_img_button' onclick='javascript:logon_yahoo();'>
						logon
					</div>
					<div id='logoff_yahoo' class='div_img_button' onclick='javascript:logoff_yahoo();'>
						logoff
					</div>
					<div id='logon_yahoo' class='div_img_button' onclick='javascript:startFantasy();'>
						start
					</div>
					<div id='reload_league' class='div_img_button' onclick='javascript:reloadLeaguePieces();'>
						reload league
					</div>
				</div>
				</td>
				</tr>
			</table>
		</div>
	</div>
</div>

<div id='westContainer3'>
	<div style='width:100%;height:100%px;display:block;background-color:#fff'>
		<div id="chart1-cont-fant" style="width:100%; height:450px">
			<div id ="loadingDiv"><img src='/images/ajax-loader.gif'><span id="loadingSpan"> Loading your fantasy league.... </span><img src='/images/ajax-loader.gif'></div>
			<div id ="startingScreenFantasy">
				<div id='logon_yahoo' class='div_img_button' onclick='javascript:startFantasy();'>
						$league_in_db
				</div>
				<div>
					$yahoo_logged_in
				</div>
			</div>
			<div id="analyzingDay"></div>
		</div>
	</div>
	<div id="weeksDiv">
		
	</div>
</div>

<div style='clear:both;'></div>

<div id='playersContainer'></div>

<div id='add_player'>
	<div id="add_player_title">
		<strong>Players To Add</strong>
		<br/>
		<input type="text" name="player_name" id="player_name" value="Enter Player Name" size='28'/>
		<div id='add_player_button_as_div' class='div_as_button' onclick='javascript:add_player_fantasy_team();'>Add Player</div>
		<input type="hidden" name="players_to_add" id="players_to_add"/>
	</div>
	<div id='added_players'></div>
	<div id='run_analysis_as_button' class='div_as_button' onclick='javascript:compareTeams();'>Run Analysis</div>
</div>
<div class='center_div'>
	<div id='reset_analysis_as_div' class='div_as_button' onclick='reset_team();'>Reset Team</div>
</div>
<div id="outputTest">
		$outputTest
	</div>

<span class='article_separator'> </span>

EOT;
echo $out;
?>
</form>

</div>

<script type="text/javascript">
  <!--
  //-->
</script>
<div id="outputText"></div>
<?php $this->load->view('footer'); ?>