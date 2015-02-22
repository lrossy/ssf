<?php $this->load->view('header'); ?>
<div id="container">
<?php if($this->session->flashdata('message') !=''){
    ?>
    <div id="infoMessage"><?php echo $this->session->flashdata('message') ;?></div>
<?php
}?>
<?php

$statistic = $this->input->post('statistic');
//if($statistic) $print_r($_POST);
$url2= site_url("stats/compare/");
$url_autocomplete= site_url("stats/getACPlayerList/");
$url_checkplayer= site_url("stats/checkPlayer/");
$url_get_leaders= site_url("stats/getLeaders/");
$url_embed= site_url("stats/buildEmbed");
$url_save= site_url("stats/save");
$url_saveAs= site_url("stats/saveAs");
$url_Del= site_url("stats/delete_graph");
$url_baseURL= site_url();
$out =<<<EOT

<noscript><div>Your browser does not support JavaScript!</div></noscript>	
<form id="statform" name="statform" action=''>
<div>
<input type="hidden" name="statistic" id="statistic" value='tabGoal' />
<input type="hidden" name="svGID" id="svGID" value='$svGID' />
<input type="hidden" name="esOption" id="esOption" value='sp' />
$playersToAdd
</div>

<script type="text/javascript" >
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
	var URL_AUTOCOMPLETE = '$url_autocomplete';
	var URL_BASEURL= '$url_baseURL';
	var URL_CHECKPLAYER = '$url_checkplayer';
	var URL_GETLEADERS = '$url_get_leaders';
	var gamble=false;
  if(gamble){
      var ajaxPrefix = 'gambling'
  }
  else{
      var ajaxPrefix = 'stats'
  }
	$(document).ready(function() {
		$setCurTab
		setup();
	});

</script>



<div id='eastContainer'>
	<!-- <div id='graphOPTS'>

		<div class="menuDiv menSep"><div><img src ='/assets/images/actFile.png' /></div><div>File</div></div>
		<div class="menuDiv menSep"><div><img src ='/assets/images/actShare.png' /></div><div>Share</div></div>
		<div class="menuDiv"><div><img src ='/assets/images/actTools.png' /></div><div>Tools</div></div>
	</div> -->


	<div id='statBlock'>
		<div id="statSide">
			&nbsp;
		</div>
		<div id='statTypeSelect'>
			<div class='smStatButt' id='smStat'>
				<span id='currentStat'>Goals</span>
			</div>
		</div>
		<div id='smOptsContainer'>
			<div id="statPreMade">
				<div id="statPreMadeData">
					<div class='mainStatOpts'>
						<div class='esColOne'>
							<p class='selected'>
							  <a class="radio-select" href="javascript:return false;" id='goalBut' onclick='switchtabs("tabGoal","goalBut");' style="-moz-border-radius: 5px 5px 5px 5px;" rel='Goals'>Goals</a>
							</p>
							<p>
							  <a class="radio-select" href="javascript:return false;" id='assistBut' onclick='switchtabs("tabAssist","assistBut")' style="-moz-border-radius: 5px 5px 5px 5px;" rel='Assists'>Assists</a>
							</p>
							<p>
							  <a class="radio-select" href="javascript:return false;" id='pointsBut' onclick='switchtabs("tabPoints","pointsBut")' style="-moz-border-radius: 5px 5px 5px 5px;" rel='Points'>Points</a>
							</p>
						</div>
						<div class='esColTwo'>
    						<p>
    						  <a class="radio-select-es" href="javascript:return false;" rel='pm'>Plus / Minus</a>
    						</p>
    						<p>
    						  <a class="radio-select-es" href="javascript:return false;" rel="sog">Shots on Goal</a>
    						</p>
							<p>
							  <a class="radio-select" href="javascript:return false;" id='pimBut' onclick='switchtabs("tabPims","pimBut")' style="-moz-border-radius: 5px 5px 5px 5px;" rel='PIMs'>PIMs</a>
							</p>
						</div>
					</div>
					$g_StatES_adv
				</div>
				<div id="statPreMadeButton">
					<span class='preMadeTitle'>Select Statistic</span>
					<div class='arrow2'></div>
				</div>
			</div>
			<div id='tabStats'>
				<div id='smOptLeft'>
					<div>
						<h3>Season</h3>
						<ul class="checklist">
							$g_StatDates
						</ul>
					</div>
					<div>
						<h3>Game Type</h3>
							<ul class="checklist">
								$g_StatGT
							</ul>
					</div>
					<div>
						<h3>Game Location</h3>
							<ul class="checklist">
								$g_StatLoc
							</ul>
					</div>
				</div>
				<div id='smOptRight'>
					<div  id = 'cntStr'>
						<h3>Strength</h3>
							<ul class="checklist">
								$g_StatStr
							</ul>
					</div>
					<div  id = 'cntPer'>
						<h3>Period</h3>
							<ul class="checklist">
								$g_StatPer
							</ul>
					</div>
					<div  id = 'cntTA'>
						<h3>Opponents</h3>
						<div onclick="javascript:ShowDialogTA()" id='buttTA'>
						<div style='float:left;'>Teams Selected</div><div id='allTeamCnt'> </div>
						<div class='clear'></div>
						</div>
					</div>
					<div  id = 'cntPIMSel'>
						<h3>Penalties</h3>

						<div onclick="javascript:ShowDialogPEN()" id='buttPEN'>
						<div style='float:left;'>Pen. Selected</div><div id='allPenCnt'> </div>
						<div class='clear'></div>
						</div>
					</div>
					<div  id = 'cntLeadersDiv' >
						<h3>Leaders</h3>
							<ul class="checklist" id='cntLeadersTeamsPlayers'>
									<li>
										<input type='checkbox' name='leaderTeamsPlayers' value='players' checked='checked' />
										<a class='checkbox-select'></a>
										<a class='checkbox-deselect'></a>
									</li>
							</ul>
							<ul class="checklist" id='cntLeadersHighestLowest'>
									<li>
										<input type='checkbox' name='leaderAscDesc' value='Descending' checked='checked' />
										<a class='checkbox-select'></a>
										<a class='checkbox-deselect'></a>
									</li>
							</ul>
							<div id='getLeadersSubmit' onclick='javascript:getLeaders();'><img src="/images/leadersButton.png"></div>
					</div>
		
				</div>
			</div>
		</div>
	</div>
</div>


<div id='westContainer'>
	<div id="chart1-players" style='width: 100%;height:210px;'>
		<div style='height:30px'>

			<input type="text" name="player_name" id="player_name" value="Sidney Crosby" size='28' style="height:20px;"/>
			<div style = 'border:1px solid gray;font-size:1.1em;padding:5px;width:60px;display:inline;background-color:lightgrey;cursor:pointer;' onclick='javascript:addPlayer();'>Add Player / Team</div>
			<div style = 'display:inline;font-size:1.4em;margin-left:5px;'><a href='/ey_ltl_2012/'>Click for EY LTL!!</a></div>
		</div>
		<div class="playerBox hidden" id="genPlayerDiv">
			<div style="font-weight: bold;">
				<div class="plImage">
					<img border="0" name = "genericMug" id = "genericMug" class = "playerPic" width='65px' />
				</div>
				<div class="plCurrentStat">
					<div class="clearBoth playerName"  id="genericName">
					Loading Player...
					</div>
					<div id="genericPos" class='plStatValue'></div>
					<div id="genericIsGraphed" class='plGraphed'></div>
				</div>
			</div>
		</div>
		<div style="" id ='curPlayers'>
		</div>
	</div>
	<div style='width:600px;height:420px;display:block;background-color:#fff'>
		<div id="chart1-cont" style="width:600px; height:420px">
		</div>
	</div>

	<div id='allTeams' style='display:none'>
		$g_StatTA
	</div>
	<div id='allPenalties' style='display:none'>
		<div class="penTitle">
			Penalty List - Select: <a id='penListAll'>All</a> / <a id='penListNone'>None</a>
		</div>
		<div id='penGroupOne' class='penSel'>
			<div>
				<ul class="checklistPen">
					$g_StatPENADV
				</ul>
			</div>
			<div style='clear:both'></div>
			<div style='width:100%;border-bottom:1px solid #C0C0C0'>
				<div style='float:right'><a href='javascript:return false;' id='pnAdvTog'>Advanced</a></div>
				<div style='clear:both'></div>
			</div>
			<div id='pnAdv'>
				<ul class="checklistPen">
					$g_StatPEN
				</ul>
			</div>
		<div style='clear:both'></div>
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
function Func1Delay()
{
//setTimeout("hideMsg()", 5000);
//setTimeout("reorderP()", 1000);


}
function reorderP(){
	var order = $('#order').val().split(",");
	$('#curPlayers').reOrder(order, 'playerDiv');
	}
function genEmbed(){
	flashMovie = document.getElementById("chart1flash");
	flashMovie.exportImage("<?=$url_embed?>" +'/'+ datafile);
}
 $(document).ready(function() {

	Func1Delay();
	//setupChart1('1');
	//alert(order);
 });
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