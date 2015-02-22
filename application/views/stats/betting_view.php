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
$statistic = $this->input->post('statistic');
//if($statistic) $print_r($_POST);
$url2= site_url("gambling/compare/");
$url_autocomplete= site_url("gambling/getACPlayerList/");
$url_checkplayer= site_url("gambling/checkPlayer/");
$url_embed= site_url("gambling/buildEmbed");
$url_save= site_url("gambling/save");
$url_saveAs= site_url("gambling/saveAs");
$url_Del= site_url("gambling/delete_graph");
$url_baseURL= site_url();
$sport = $this->uri->segment(2);
$sportUCase = ucfirst($sport);
$mainURI = $this->uri->segment(1);
if ($sport == 'baseball')
{
	$totalsV = "Runs";
}
else if ($sport == 'hockey')
{
	$totalsV = "Goals";
}
//$homeTeam="BOS";
//$awayTeam="CBJ";
//$homeStartMoneyLine = $g_GameIDsToday['home_team_moneyline'][$g_GameIDsToday['firstGameId']];
//$awayStartMoneyLine = $g_GameIDsToday['away_team_moneyline'][$g_GameIDsToday['firstGameId']];
//$homeStartSpread = $g_GameIDsToday['home_team_spread'][$g_GameIDsToday['firstGameId']];
//$awayStartSpread = $g_GameIDsToday['away_team_spread'][$g_GameIDsToday['firstGameId']];
//$gameStartTotal = $g_GameIDsToday['total_points'][$g_GameIDsToday['firstGameId']];
$out =<<<EOT


<noscript><div>Your browser does not support JavaScript!</div></noscript>	
<form name="statform" id="statform" action=''>

<script type="text/javascript" >
	var jsonDataToday = jQuery.parseJSON('$g_GameIDsToday[jsonString]');
	var jsonDataYest = jQuery.parseJSON('$g_GameIDsYest[jsonString]');
	var jsonDataTomorrow = jQuery.parseJSON('$g_GameIDsTomorrow[jsonString]');
	var menuBar;
	var menuModel_add;
	var chart;
	var dataProvider;
	var PlayerId = 1;
	var flashMovie;
	var PlayerId;
	var statistic;
	var Player1Team;
	var loading = 0;
	var datafile;
	var predatafile;
	var postdatafile;
	var sport = '$sport';
	var mainURI = '$mainURI';
	var URL_AUTOCOMPLETE = '$url_autocomplete';
	var URL_BASEURL= '$url_baseURL';
	var URL_CHECKPLAYER = '$url_checkplayer';
	var gamble=true;
  if(gamble){
      var ajaxPrefix = 'gambling'
  }
  else{
      var ajaxPrefix = 'stats'
  }

	$(document).ready(function() {
	  setupGambling();
	});

</script>

<div id='eastContainer'>
	<div id='statBlock2'>
		<div id="statSidePin">
			&nbsp;
		</div>
		<div id='statTypeSelect'>
			<div class='smStatButt' id='smStat'>
				<span id='currentStat'>$sportUCase Betting</span>
			</div>
		</div>
		<div id='smOptsContainer'>
			<div id='tabStats'>
				<div id = 'gamblingContainer'>
					<div id='smOptGamble'>
						<table class = 'gamblingOdds'>
							<tr>
								<td colspan="2">
									<span id="titleToolTip"><a class="tooltip" title=""> How does it work?</a></span>
								</td>
							</tr>
							<tr>
								<td><h3>Select Date</h3></td>
								<td><h3><select name='game' id='gameDate'>
										<option value="yesterday">Yesterday</option>
										<option value="today" selected>Today</option>
										<option value="tomorrow">Tomorrow</option>
									</select>
									<span id="dayToolTip"><a class="tooltip" title=""> ?</a></span></h3>
								</td>
							</tr>	
							<tr>
								<td><h3>Select Game</h3></td>
									<td><h3><div id="gameSelect">$g_GameIDsToday[select]
									<span id="gameSelectToolTip"><a class="tooltip" title=""> ?</a></span></h3></div></td>
							</tr>
							<tr>
								<td><h3>Bet Type</h3></td>
								<td><h3><select name='betType' id='betType' onChange='betTypeChange(this)'>
									  <option value="moneyline">Money Line</option>
									  <option value="puckline">Handicap</option>
									  <option value="gametotals">Total $totalsV</option>
									</select>
									<span id="betTypeToolTip"><a class="tooltip" title=""> ?</a></span></h3>
								<td>
							</tr>
							<tr>
								<td><h3>Bet Amount</h3></td>
								<td><h3 id="dollarSign">$<input type="text" name="betAmount" id="betAmount" size="4" value="100"/> <span id="dollarToolTip"><a class="tooltip" title="Enter the dollar amount that you would like to bet.">?</a></span></h3></td>
							</tr>
							<tr>
							<tr id='placeBet'>
								<td colspan="2">
									<h3>Where would you like to place your bet?</h3>
								</td>
							</tr>
							<tr id='homeAwayTitles'>
								<td class='leftCell'>
									<h3>Away</h3>
								</td>
								<td>
									<h3>Home</h3>
								</td>
							</tr>
							<tr>
								<td class='leftCell'><h3><input type="radio" name="betOnTeam" value="Away" id ='awayRadio' onclick='radioChange(this);'> <label for='awayRadio'><span id='awayTeamImg'><img src='/images/team/smallteam/bluejackets.png' class='teamsRadio' /></span><div class='totalsRadio bigFont'>Under <span class="gameTotalInputSpan"></span></div><span id="awayTeamToolTip"><a class="tooltip" title=""> ?</a></span></h3></label></td>
								<td><h3><input type="radio" name="betOnTeam" value="Home" id ='homeRadio' onclick='radioChange(this);' checked> <label for='homeRadio'><span id='homeTeamImg'><img src='/images/team/smallteam/bruins.png' class='teamsRadio' /></span><div class='totalsRadio bigFont'>Over <span class="gameTotalInputSpan"></span></div><input type="hidden" name="gameTotal" id="gameTotalInput" size="4" /><input type="hidden" name="gameTotalBetOnline" id="gameTotalBetOnline" /><span id="homeTeamToolTip"><a class="tooltip" title=""> ?</a></span></h3></label></td>
							</tr>
							<tr>
								<td class='leftCell'><div class ='padBottom bigFont'><h3><span class='center' id="awaySpreadSpan"></span><input type="hidden" name="awaySpread" id="awaySpread" /><input type="hidden" name="awaySpreadBetOnline" id="awaySpreadBetOnline" /><span id="awayOddsSpan"></span><input type="hidden" name="awayOdds" id="awayOdds"  /><input type="hidden" name="awayOddsBetOnline" id="awayOddsBetOnline"  size="3" /> </h3></div></td>
								<td><div class ='padBottom bigFont'><h3><span class='center' id="homeSpreadSpan"></span><input type="hidden" name="homeSpread" id="homeSpread" /><input type="hidden" name="homeSpreadBetOnline" id="homeSpreadBetOnline" /><span id="homeOddsSpan"></span><input type="hidden" name="homeOdds" id="homeOdds"  /><input type="hidden" name="homeOddsBetOnline" id="homeOddsBetOnline"  /></h3></div></td>
							</tr>
						    <tr>
								<td class='leftCell'><label for='opt3'>Include <span class='awayTeamShort'>$awayTeam</span> games (against the selected opponents)</label></td>	
								<td><label for='opt1'>Include <span class='homeTeamShort'>$homeTeam</span> games (against the selected opponents)</label></td>
							</tr>
							<tr>
								<td class='leftCell'>									
									<input type="checkbox" name="teamFilters[]" id = "opt3" value="opt3" onclick="checkBoxClick(this);"/>
									<div onclick="javascript:ShowDialogTO('away')" id='buttTAgamb'>
									<div id='atoTeamCnt'></div>
									<input type='hidden' value = '0' id = 'awayTeamOpponents' name = 'awayTeamOpponents'>
									</div>
									<h3 class="inlineH3"><span id="awayOpponentsTooltip"><a class="tooltip" title=""> ?</a></span></h3>
								</td>	
								<td>
									<input type="checkbox" name="teamFilters[]" id = "opt1" value="opt1" checked onclick="checkBoxClick(this);"/>
									<div onclick="javascript:ShowDialogTO('home')" id='buttTAgamb'>
									<div id='htoTeamCnt'></div>
									<input type='hidden' value = '0' id = 'homeTeamOpponents' name = 'homeTeamOpponents'>
									</div>
									<h3 class="inlineH3"><span id="homeOpponentsTooltip"><a class="tooltip" title=""> ?</a></span></h3>
								</td>		
							</tr>
							<tr>
								<td class='leftCell'>
									<table id='awayCell' class='gambNest'>
										<tr>
											<td>
												<input type="checkbox" name="awayTeamHomeGms" id = "awayTeamHomeGms" value="awayTeamHomeGms" onclick="checkBoxClick(this);"/><label for='awayTeamHomeGms'><span class='awayTeamShort'>$awayTeam</span> home games</label>
											</td>
											<td>
												<input type="checkbox" name="awayTeamAwayGms" id = "awayTeamAwayGms" value="awayTeamAwayGms" onclick="checkBoxClick(this);"/><label for='awayTeamAwayGms'><span class='awayTeamShort'>$awayTeam</span> away games</label>
											</td>
										</tr>
									</table>
								</td>
								<td>
									<table id='homeCell' class='gambNest'>
										<tr>
											<td>
												<input type="checkbox" name="homeTeamHomeGms" id = "homeTeamHomeGms" value="homeTeamHomeGms" checked onclick="checkBoxClick(this);"/><label for='homeTeamHomeGms'><span class='homeTeamShort'>$homeTeam</span> home games</label>
											</td>
											<td>
												<input type="checkbox" name="homeTeamAwayGms" id = "homeTeamAwayGms" value="homeTeamAwayGms" checked onclick="checkBoxClick(this);"/><label for='homeTeamAwayGms'><span class='homeTeamShort'>$homeTeam</span> away games</label>
											</td>
										</tr>
									</table>
								</td>
							</tr>
							<tr>
								<td colspan="2"><h3><label for='opt2'>Only consider games when <span class='awayTeamShort'>$awayTeam</span> plays vs. <span class='homeTeamShort'>$homeTeam</span></label><br /><input type="checkbox" id = "opt2" name="teamFilters[]" value="opt2" onclick="checkBoxClick(this);"/><span id="matchupTooltip"><a class="tooltip" title=""> ?</a></span></h3></td>
							</tr>
							<tr id = "datesTitleRow">
								<td><h3>Date From</h3></td><td><h3>Date To</h3></td>
							</tr>
							<tr id="datesRow">
								<td><h3><input type="text" name="startDate" size="11" class="datepicker" value="03/31/2013"/><span id="startDateToolTip"><a class="tooltip" title=""> ?</a></span></h3></td>
								<td><h3><input type="text" name="endDate" size="11" class="datepicker" value="$yesterday"/><span id="endDateToolTip"><a class="tooltip" title=""> ?</a></span></h3></td>
							</tr>
							<tr id = "gameTypeRow">
								<td><h3><label for="regSeason">Regular Season </label><input type="checkbox" name="regSeason" id="regSeason" checked="checked"/><span id="regSeasonToolTip"><a class="tooltip" title=""> ?</a></span></h3></td>
								<td><h3><label for="playoffs">Playoffs </label><input type="checkbox" name="playoffs" id="playoffs" checked="checked"/><span id="playoffToolTip"><a class="tooltip" title=""> ?</a></span></h3></td>
							</tr>
							<tr>
								<td colspan="2">
									<button type="button"  id='gambSubmit' name='gambSubmit' onclick='javascript:setupChart1()'>Run Analysis</button>
								</td>
							</tr>
						</table>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>


<div id='westContainer'>
	<div id='summaryStats'>
		<table id='summaryTable'>
			<tr>
				<th>Sports Book</th>
				<th>Odds</th>
				<th>Cumulative Profit</th>
				<th>Avg Profit Per Bet</th>
			</tr>
			<tr id="pinnacleRow">
				<td id='sportsBookLogo'><div id='pinnacleLogo'></div></td>
				<td id='oddsPinnacle'></td>
				<td id='cumProf'></td>
				<td id='avgProf'></td>
			</tr>
			<tr id="betOnlineRow">
				<td id='sportsBookLogo'><div id='betOnlineLogo'></div></td>
				<td id='oddsBetOnline'></td>
				<td id='cumProfBetOnline'></td>
				<td id='avgProfBetOnline'></td>
			</tr>
		</table>
	</div>
	<div style='width:600px;height:420px;display:block;background-color:#fff'>
		<div id="chart1-cont-gamb" style="width:600px; height:420px">
		</div>
	</div>
    <div id='homeTeamOpponent' style='display:none'>
		$g_StatHTO
	</div>
	<div id='awayTeamOpponent' style='display:none'>
		$g_StatATO
	</div>
	</div>

</div>
<div style='clear:both;'></div>

<span class='article_separator'> </span>

EOT;
echo $out;
?>
</form>

</div>

<script type="text/javascript">
  <!--

  function reorderP(){
    var order = $('#order').val().split(",");
    $('#curPlayers').reOrder(order, 'playerDiv');
  }
  function genEmbed(){
    flashMovie = document.getElementById("chart1flash");
    flashMovie.exportImage("<?=$url_embed?>" +'/'+ datafile);
  }

  (function($) {

    $.fn.reOrder = function(array, prefix) {
      return this.each(function() {
        prefix = prefix || "";
        if (array) {
          for(var i=0; i < array.length; i++)
            array[i] = $('#' + prefix + array[i]);

          $(this).empty();

          for(var i=0; i < array.length; i++)
            $(this).append(array[i]);
        }
      });
    }
  })(jQuery);

  //-->
</script>
<?php $this->load->view('footer'); ?>