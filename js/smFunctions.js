function setup(){
	var i =1;
	$.each($(".pn"), function(i,v) {
		var theTag = v.tagName;
		var theElement = $(v);
		var theValue = theElement.val();
		var theID =theElement.attr('id');

		if(theValue !='empty'){
			toAdd = theID.slice(0, -1);
			the_length=theID.length;
			last_char=theID.charAt(the_length-1);
			addPlayerDiv('stat_player_name'+last_char,theValue,0);
			//addPlayer(theValue+last_char)
		}
		i +=1;
	});
	//createChart();            
	var playerNameTitle = build_player_titles(6);
	var statistic = document.getElementById('statistic').value;


	setupChart1(0);
}
function startFantasy(){
	var exitLoop;
	exitLoop = false;
	$(".radioClass").buttonset();
	var ret = $.ajax({
		type: "POST",
		dataType:'json',
		url: URL_FANTASY_HOCKEY_SETUP,
		success: function(data)
		{
			setupFantasy();
			exitLoop = true;
		}
	});
}
//function to reload all the data in the league - deprecated to load things one at a time
function reloadLeague(){
	var exitLoop;
	exitLoop = false;
	var ret = $.ajax({
		type: "POST",
		data: $("#statform").serialize(),
		dataType:'json',
		url: URL_FANTASY_RELOAD,
		success: function(data)
		{
			setupFantasy();
			exitLoop = true;
			$("#loadingDiv").css("display","none");
		}
	});
	$("#loadingDiv").css("display","block");
}

function reloadLeaguePieces(){
	var exitLoop;
	exitLoop = false;
	$("#reload_league2").append("<br />Loading settings...");
	var ret = $.ajax({
		type: "POST",
		data: $("#statform").serialize(),
		dataType:'json',
		url: URL_FANTASY_RELOAD_SETTINGS,
		success: function(data)
		{
			$("#reload_league2").append("Done. <br />Loading Teams...");
			var ret2 = $.ajax({
				type: "POST",
				dataType:'json',
				url: URL_FANTASY_RELOAD_TEAMS,
				success: function(data)
				{
					$("#reload_league2").append("Done.");
					setupFantasy();
					$("#loadingDiv").css("display","none");
				}
			});
		}
	});
	//$("#loadingDiv").css("display","block");
}



function setupFantasy(){
	//make it so when a text box is selected, the whole text gets highlighted.
	$("input[type=text]").focus(function(){
		// Select field contents
		this.select();
	});
	setupChart1();
	setupPlayers(false);
	getSummaryData();
	$("#player_name").keydown(function(event){
    if(event.keyCode == 13){
        add_player_fantasy_team();
    }
});
}
function getSummaryData(){
	//get the data about what buttons there are at the bottom for options
	//get the data about what teams and matchups there are
	var ret = $.ajax({
		type: "POST",
		dataType:'json',
		url: URL_FANTASY_GET_SUMMARY,
		success: function(data)
		{
			$("#weeksDiv").html(data);
		}
	});

}
function logoff_yahoo(){
	var ret = $.ajax({
		type: "POST",
		dataType:'json',
		url: URL_FANTASY_LOGOFF_YAHOO,
		success: function(data)
		{
			//$("#outputTest").html(data);
			location.reload();
		}
	});

}
function logon_yahoo(){
	window.location = URL_FANTASY_LOGON_YAHOO;
}


function recalc_projections(){

	var ret = $.ajax({
		type: "POST",
		dataType:'json',
		data: $("#statform").serialize(),
		url: URL_RECALC,
		success: function(data)
		{
			setupFantasy();
		}
	});
}

//unhide dates for fantasy projections
function unhide_dates(){
	$("#hidden_row").css("display","inline");
}
function setupGambling(){
    $('#awayTeamOpponents').val(get_TeamOpponents_value('Away',0));
    $('#homeTeamOpponents').val(get_TeamOpponents_value('Home',0));
    //var sport = "baseball";

    var ppBuntton = '<form action="https://www.paypal.com/cgi-bin/webscr" method="post"> \
        <input type="hidden" name="cmd" value="_s-xclick"> \
        <input type="hidden" name="hosted_button_id" value="QHR5F85M8JEWG"> \
            <input type="image" src="https://www.paypalobjects.com/en_US/i/btn/btn_buynowCC_LG.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!"> \
                <img alt="" border="0" src="https://www.paypalobjects.com/en_US/i/scr/pixel.gif" width="1" height="1"> \
                </form>';
				

		$('#gameDate').change(function() {
			$.getJSON("/"+mainURI+"/getGames/"+sport+"/date/" + $(this).val(), function(json) {
				if(json.code == 1){
					$('#gameDate').val('yesterday');
					var $dialog = $('<div></div>')
						.html(ppBuntton)
						.dialog({
							autoOpen: true,
							title: 'Purchase an account to analyze today\'s and tomorrow\'s games.',
							modal: true
						});
				}else{
				  $("#gameSelect").html(json.htmlGames.select + '<span id="gameSelectToolTip"><a class="tooltip" title=""> ?</a></span></h3>');
				  changeTeamNames();
				  }
				
			});
		});
    $('#gameDate').val('today');
    tooltip2();
    changeTeamNames();
    $("#awayTeamHomeGms").button();
    $("#awayTeamAwayGms").button();
    $("#homeTeamHomeGms").button();
    $("#homeTeamAwayGms").button();
    setupChart1();

}
 $(document).ready(function() {
$(".radioClass").buttonset();
$('.radio-select').corner('round 5px');
$('.radio-select-es').corner('round 5px');
$('.radio-deselect').corner('round 5px');
$('.checkbox-deselect').corner('round 5px');
$('.checkbox-select').corner('round 5px');
	$("div#allTeams").dialog(
		{  autoOpen: false,
		   modal: true,
		   overlay: { opacity: 0.5, background: '#050505' },
		   buttons: {
					  "Save and Close" : function(){
									$("#chart1-cont").css("display","block") //Show Movie if dialog is closed
									$(this).dialog("close");
									setupChart1();
								}
					},
		   close: function(event, ui) {

									if ( $("#chart1-cont").is(':visible')) {
									  // your code
									}
									else {
									$("#chart1-cont").css("display","block") //Show Movie if dialog is closed
									setupChart1();
									}

					},
		   title: "Choose Opponents",
		   height: 320,
		   width: 820,
		   dialogClass: 'myDialog',
		   position: 'center'
		 }
	);
     $("div#awayTeamOpponent").dialog(
         {  autoOpen: false,
             modal: true,
             overlay: { opacity: 0.5, background: '#050505' },
             buttons: {
                 "Save and Close" : function(){
                     //$("#chart1-cont").css("display","block") //Show Movie if dialog is closed
                     $(this).dialog("close");
                     cntATO();
                     $('#awayTeamOpponents').val(get_TeamOpponents_value('Away',0));
                 }
             },
             close: function(event, ui) {

                 if ( $("#chart1-cont").is(':visible')) {
                     // your code
                 }
                 else {
                     //$("#chart1-cont").css("display","block") //Show Movie if dialog is closed
                     cntATO();
                     $('#awayTeamOpponents').val(get_TeamOpponents_value('Away',0));
                 }

             },
             title: "Choose Away Team Opponents",
             height: 320,
             width: 820,
             dialogClass: 'myDialog',
             position: 'center'
         }
     );
     $("div#homeTeamOpponent").dialog(
         {  autoOpen: false,
             modal: true,
             overlay: { opacity: 0.5, background: '#050505' },
             buttons: {
                 "Save and Close" : function(){
                     //$("#chart1-cont").css("display","block") //Show Movie if dialog is closed
                     $(this).dialog("close");
                     cntHTO();
                     $('#homeTeamOpponents').val(get_TeamOpponents_value('Home',0));
                 }
             },
             close: function(event, ui) {

                 if ( $("#chart1-cont").is(':visible')) {
                     // your code
                 }
                 else {
                     //$("#chart1-cont").css("display","block") //Show Movie if dialog is closed
                     cntHTO();
                     $('#homeTeamOpponents').val(get_TeamOpponents_value('Home',0));
                 }

             },
             title: "Choose Away Team Opponents",
             height: 320,
             width: 820,
             dialogClass: 'myDialog',
             position: 'center'
         }
     );
	$("div#contLogin").dialog(
		{  autoOpen: false,
		   modal: true,
		   overlay: { opacity: 0.5, background: '#050505' },
		  buttons: {
			   Cancel: function() {
				$(this).dialog('close');
			   }
			},
		   close: function(event, ui) {

									if ( $("#chart1-cont").is(':visible')) {
									  // your code
									}
									else {
									$("#chart1-cont").css("display","block") //Show Movie if dialog is closed
									setupChart1();
									}

					},
		   title: "Login",
		   height: 320,
		   width: 660,
		   dialogClass: 'myDialog',
		   position: 'center'
		 }
	);
	$("div#allPenalties").dialog(
		{  autoOpen: false,
		   modal: true,
		   overlay: { opacity: 0.5, background: '#050505' },
		   buttons: {
					  "Save and Close" : function(){
									$("#chart1-cont").css("display","block") //Show Movie if dialog is closed
									$(this).dialog("close");
									setupChart1();
								}
					},
		   close: function(event, ui) {

									if ( $("#chart1-cont").is(':visible')) {
									  // your code
									}
									else {
									$("#chart1-cont").css("display","block") //Show Movie if dialog is closed
									setupChart1();
									}

					},
		   title: "Choose Penalties",
		   height: 420,
		   width: 660,
		   dialogClass: 'myDialogPen',
		   position: 'center'
		 }
	);

    $('#smStat').click(function () {
			if($("#statPreMadeData").is(':hidden')){
				$('#statPreMadeData').stop(true, true).slideToggle('medium');
				$('.preMadeTitle').html('Select Statistic');
				$('.arrow2').toggleClass('aup');
			}
			else{
				//alert(1);
				$('#statPreMadeData').stop(true, true).slideToggle('medium');
				$('.preMadeTitle').html('Select Statistic');
				$('.arrow2').toggleClass('aup');
			}
    });
    $('#statPreMadeButton').click(function () {
			if($("#statPreMadeData").is(':hidden')){
				$('#statPreMadeData').stop(true, true).slideToggle('medium');
				$('.preMadeTitle').html('Select Statistic');
				$(this).find('.arrow2').toggleClass('aup');
			}
			else{
				//alert(1);
				$('#statPreMadeData').stop(true, true).slideToggle('medium');
				$('.preMadeTitle').html('Select Statistic');
				$(this).find('.arrow2').toggleClass('aup');
			}
    });
    $('#moreStats span').click(function () {
			if($("#allEventStats").is(':hidden')){
				$('#allEventStats').stop(true, true).slideToggle('medium');
				$('#moreStats span').html('Less Stats')
			}
			else{
				//alert(1);
				$('#allEventStats').stop(true, true).slideToggle('medium');
				$('#moreStats span').html('More Stats')

			}
    });
 });
 function ShowDialogTA()
{
	/*for Notice dialog */
	//$("#divDialog").css("display","block");
	$("#chart1-cont").css("display","none");
	$("div#allTeams").dialog("open");
	//$('#allTeams .checkbox-deselect').corner('round 5px');

}
function ShowDialogTO(homeoraway)
{
    /*for team opponent dialog */
    //$("#divDialog").css("display","block");
    //$("#chart1-cont").css("display","none");
    if(homeoraway =='away'){
        $("div#awayTeamOpponent").dialog("open");
    }
    else{
        $("div#homeTeamOpponent").dialog("open");
    }
    //$('#allTeams .checkbox-deselect').corner('round 5px');

}
 function shLogin()
{
	/*for Notice dialog */
	$("#chart1-cont").css("display","none");
	$("div#contLogin").dialog("open");
}
 function ShowDialogPEN()
{
	/*for Notice dialog */
	//$("#divDialog").css("display","block");
	$("#chart1-cont").css("display","none");
	$("div#allPenalties").dialog("open");
	//$('#allPenalties .checkbox-deselect').corner('round 5px');
}
 function ShowDialogES()
{
	/*for Notice dialog */
	//$("#divDialog").css("display","block");
	$("#chart1-cont").css("display","none");
	$("div#allEventStats").dialog("open");
	//$('#allEventStats .radio-deselect').corner('round 5px');

}


function getLeaders()
{
	statistic = document.getElementById('statistic').value;
	Strength = get_radio_value();
	if (Strength==''){Strength='0';}
	goalPeriods = get_period_value();	
	if (goalPeriods==''){goalPeriods='0';	}

	teamAgainstVals = get_teamAgainst_value() ;
	newTAVals = teamAgainstVals.split('|')
	$('#allTeamCnt').text(newTAVals[0]);
	$('#buttTA').corner('round 5px');

	if (newTAVals[0]==0){//teamAgainst='0';
		newTAVals[1] ='-1';
	}
	if(statistic=='tabPims'){
		//var array_of_penalties = $("#teamPenalty").multiselect("getChecked").map(function(){
		//	return this.value;
		//});
		teamPenaltiesVals = get_teamPenalty_value() ;
		newPENVals = teamPenaltiesVals.split('|')
		//$('#buttPEN').text(newPENVals[0]+' Selected');
		$('#allPenCnt').text(newPENVals[0]);
		//$('#buttPEN').corner('round 5px');
		if (newPENVals[0]==0){alert('You must have at least one value selected for Penalties');
			return false;
		}
		teamPenalties = newPENVals[1];
	}
	else teamPenalties ='0';
	var curSeason = get_season_dates();
	if(curSeason==''){curSeason='0';}
	gt_vals = get_gametype_value();
	if (gt_vals==''){gt_vals='0';}
	if(statistic=='tabEventstats'){
		var es_val = $("input#esOption").val();
	}
	else es_val ='0';
	Location = get_location_value();
	if (Location==''){Location='0';	}

	pvals = get_player_values();
	pvals2 = get_player_values(1);
	jsPVAL = get_js_player_vals();

	if(pvals==''){
		pvals='0';
	}

	var pvalsAJAX = pvals;
	//var pvalsAJAX = pvals.replace("'", "|");
//	var flashMovie = new SWFObject("/amline/amline.swf", "chart1flash", "650px", "420px", "8", "#FFFFFF");
	predatafile = statistic +  '/' + escape(curSeason) + '/';
	postdatafile = '/' + escape(Strength)+'/' + escape(goalPeriods)+'/' + escape(newTAVals[1])+'/'+gt_vals+'/'+teamPenalties+'/'+es_val+'/'+Location;

	datafile = statistic +  '/' + escape(curSeason) + '/' + escape(pvalsAJAX) + '/' + escape(Strength)+'/' + escape(goalPeriods)+'/' + escape(newTAVals[1])+'/'+gt_vals+'/'+teamPenalties+'/'+es_val+'/'+Location;
	getLeadersURL = URL_GETLEADERS +'/'+ datafile;
	var ret = $.ajax({
		type: "POST",
		dataType:'json',
		data: $("#statform").serialize(),
		url: getLeadersURL,
		success: function(data)
		{
			for (var i=0;i<6;i++)
			{
				remPlayer2(i+1);
				checkPlayer(data[i]['player_f_name'] + " " + data[i]['player_l_name'],0);
			}			
		}
	});
	
}

function checkPlayer(pname,curDiv){
var ret = $.ajax({
	type: "POST",
	url: URL_CHECKPLAYER,
	dataType:'json',
	data: 'term='+pname,
	success: function(msg){
		if(msg.name!= 'error'){
			if(curDiv ==0){
				$.each($(".pn"), function(i,v) {
					var theTag = v.tagName;
					var theElement = $(v);
					var theValue = theElement.val();
					var theID = theElement.attr('id');
					if(theValue =='empty'){
						var checkDup = isInList(msg.name);
						if(checkDup=='1') {
							$(v).val(msg.name)
							addPlayerDiv(theID,msg.name,1);
						}
						else{
							alert('Player is already in list');
						}
						//alert(theID);
						//now need to clone the div
						return false;
					}
					else{i++;}
					if(i==6)alert('Maximum eight (6) players, please remove one then try again');
				});
			}
			else{
				//$('#stat_player_name'+curDiv).val(msg.name);

				addPlayerDiv('stat_player_name'+curDiv,msg.name,0)
				return false;
			}
		}
		else {
			alert(msg.message);
		}
   }
 });
}
function saveGraph(pname){
var currentTime = new Date();
var month = currentTime.getMonth();
var day = currentTime.getDate();
var year = currentTime.getFullYear();
var hours = currentTime.getHours()
var minutes = currentTime.getMinutes()

temp= month + "-" + day + "-" + year + "-" + hours + "-" + minutes

//check if using a new graph or a loaded one
var theValue = $('#svGID').val();
if(theValue=='0'){
var name=prompt("Please enter a name","Graph_"+temp);


if (name!=null && name!="")
  {
	var ret = $.ajax({
		type: "GET",
		url: "$url_saveAs" +'/'+ datafile+'/'+name,
		success: function(msg){
			alert(msg);
			window.location.reload();
			}
	 });
  }
}
else {
var response=prompt("This will overwrite your previously saved data. Enter a new name:");

	if (response){
		var ret = $.ajax({
			type: "GET",
			url: "$url_save" +'/'+ datafile+'/'+response+'/'+$svGID,
			success: function(msg){
				alert(msg);
				window.location.reload();
				}
		 });
	}
}
}
function saveGraphAs(pname){
var currentTime = new Date();
var month = currentTime.getMonth();
var day = currentTime.getDate();
var year = currentTime.getFullYear();
var hours = currentTime.getHours()
var minutes = currentTime.getMinutes()

temp= month + "-" + day + "-" + year + "-" + hours + "-" + minutes


var name=prompt("Please enter a name","Graph_"+temp);

//update to redirect to newly saved graph
if (name!=null && name!="")
  {
	var ret = $.ajax({
		type: "GET",
		url: "$url_saveAs" +'/'+ datafile+'/'+name,
		success: function(msg){
			alert(msg);
			window.location.reload();
			}
	 });
  }
}
function delGraph(id){

	var answer = confirm("Are you sure you want to delete this item?")
	if (answer){
	var ret = $.ajax({
		type: "GET",
		url: "$url_Del" +'/'+ id,
		success: function(msg){
			//alert(msg);
			}
	 });
	 window.location = "http://statsmachine.ca/stats/";
	}
	
}
$(document).ready(function(){
	$( "#player_name" ).autocomplete({
		minLength: 2,
		source: function(request, response) {
		$.ajax({
		  url: URL_AUTOCOMPLETE,
		  data: request,
		  dataType:'json',
		  type: "POST",
		  success: function(data){
			  response(data);
		  }
		});
	  },
	select: function(event, ui) {
		//addPlayer(ui.item.value);
		}
	});
//$('input[name=Location]').click(function() {
//  setupChart1();
//});
	//$("#teamAgainst").multiselect();
	$("#teamPenalty").multiselect();
	$( "#startdate" ).datepicker({ dateFormat: 'yy-mm-dd' });
	$( "#enddate" ).datepicker({ dateFormat: 'yy-mm-dd' });
	setTimeout(function(){
		$("#infoMessage").slideUp();
	},3000);
})
    /*Date Select JS*/
function changeTeamNames()
{
    var selectedTeamString = $("select[id='betGame'] option:selected").text();
    var awayTeam = selectedTeamString.substring(0,selectedTeamString.indexOf("@")-1);
    var homeTeam = selectedTeamString.substring(selectedTeamString.indexOf("@")+2,selectedTeamString.length);
    if (sport == "hockey")
	{
		var lowCaseAway = "team/smallteam/" + getLowCaseTeamFromShortTeam(awayTeam);
		var lowCaseHome = "team/smallteam/" + getLowCaseTeamFromShortTeam(homeTeam);
    }
	else if (sport == "baseball")
	{
		var lowCaseAway = "mlbteam/" + getLowCaseTeamFromShortTeamMLB(awayTeam);
		var lowCaseHome = "mlbteam/" + getLowCaseTeamFromShortTeamMLB(homeTeam);
    }
	var awayToolTip;
    var homeToolTip;
    $(".homeTeamShort").html(homeTeam);
    $(".awayTeamShort").html(awayTeam);
    $("#awayTeamImg").html("<img src='/images/"+lowCaseAway+".png' class='teamsRadio' />");
    $("#homeTeamImg").html("<img src='/images/"+lowCaseHome+".png' class='teamsRadio' />");
    betTypeChange(document.getElementById("betType"));
    resetToolTips();
}
function resetToolTips()
{
    var selectedTeamString = $("select[id='betGame'] option:selected").text();
    var awayTeam = selectedTeamString.substring(0,selectedTeamString.indexOf("@")-1);
    var homeTeam = selectedTeamString.substring(selectedTeamString.indexOf("@")+2,selectedTeamString.length);
    if ($("#betType").val() == "gametotals"){
        awayToolTip = "Under";
        homeToolTip = "Over";
    }
    else
    {
        awayToolTip = awayTeam;
        homeToolTip = homeTeam;
    }
    $('#awayOddsTooltip').html('<a class="tooltip" title="Enter the odds to bet on '+awayToolTip+' from your sports book (e.g., -120)."> ?</a>');
    $('#homeOddsTooltip').html('<a class="tooltip" title="Enter the odds to bet on '+homeToolTip+' from your sports book (e.g., +130)."> ?</a>');
    $('#dollarToolTip').html('<a class="tooltip" title="Enter how much you are planning to bet."> ?</a>');
    $('#awayTeamToolTip').html('<a class="tooltip" title="Click here if you would like to bet on '+awayToolTip+'"> ?</a>');
    $('#homeTeamToolTip').html('<a class="tooltip" title="Click here if you would like to bet on '+homeToolTip+'"> ?</a>');
    $('#awayOpponentsTooltip').html('<a class="tooltip" title="Check this box to include games where '+awayTeam+' has played (against the selected teams). Keep in mind that you may want to include this even if you are betting on '+homeTeam+' because isn\'t betting on one team winning the same as betting on the other losing? Also, note that once selected, you can select whether '+awayTeam+' plays at home or away on the row below this."> ?</a>');
    $('#homeOpponentsTooltip').html('<a class="tooltip" title="Check this box to include games where '+homeTeam+' has played (against the selected teams). Keep in mind that you may want to include this even if you are betting on '+awayTeam+' because isn\'t betting on one team winning the same as betting on the other losing? Also, note that once selected, you can select whether '+homeTeam+' plays at home or away on the row below this."> ?</a>');
    $('#matchupTooltip').html('<a class="tooltip" title="Check this box to only consider games when '+homeTeam+' plays against '+awayTeam+'. There won\'t be too many data points, but it is still useful to consider"> ?</a>');
    $('#titleToolTip').html('<a class="tooltip" title="What you see in the graph on the right is a the profit (or loss) over time of a certain bet. Run your mouse over the line in the graph, and you will see all the games in the sample and whether each contributed to a win or a loss. At the top of the graph, we include some summary data including cumulative profit, average profit, and calculated odds. <br /><br />In the section on the left, below the title, you can select the game that you are interested in and what type of bet to consider - Money Line, Puck Line, or Game Totals. Then, below that, you get to filter the data. You can choose which of the teams you want to include in the sample, which of their opponenets, whether they are playing home or away, and which date range to consider. <br /><br />It is quite powerful, so play around with it - that is the best way to see what it can offer. Now go win some bets!!"> How does it work?</a>');
	$('#dayToolTip').html('<a class="tooltip" title="Select what day you would like to see games for? Yesterday, Today, or Tomorrow."> ?</a>');
	$('#betTypeToolTip').html('<a class="tooltip" title="Select the type of bet your are interested in? Money Line, Puck Line, or Game Totals."> ?</a>');
	$('#gameSelectToolTip').html('<a class="tooltip" title="Select which game you would like to bet on."> ?</a>');
	$('#startDateToolTip').html('<a class="tooltip" title="This is the date range of the games you would like to look at. Set the start of the date range here."> ?</a>');
	$('#endDateToolTip').html('<a class="tooltip" title="This is the date range of the games you would like to look at. Set the end of the date range here."> ?</a>');
	$('#playoffToolTip').html('<a class="tooltip" title="Check this box to include playoff games (but make sure your date range includes the playoffs from previous seasons)."> ?</a>');
	$('#regSeasonToolTip').html('<a class="tooltip" title="Check this box to include regular season games."> ?</a>');
	$('#gameTotalToolTip').html('<a class="tooltip" title="Enter the game total spread from your sports book website (e.g., 5.5)."> ?</a>');
	tooltip2();
}
$(function() {
    $( ".datepicker" ).datepicker();
});
function betTypeChange(obj)
{
    if (obj.value == "puckline") {
        $("#awaySpreadSpan").css("display","inline");
        $("#homeSpreadSpan").css("display","inline");
		//document.getElementById("gameTotal").style.display = "none";
        $("#homeAwayTitles").css("display","table-row");
        $(".teamsRadio").css("display","inline");
        $(".totalsRadio").css("display","none");
		$('#awayRadio').attr('checked',false);
        $('#homeRadio').attr('checked',true);
		$("#opt1").attr("checked",true);
        $("#opt2").attr("checked",false);
        $("#opt3").attr("checked",false);
		$('#homeTeamHomeGms').attr('checked',true);
		$('#homeTeamAwayGms').attr('checked',true);
		$('#awayTeamHomeGms').attr('checked',false);
		$('#awayTeamAwayGms').attr('checked',false);

    }
    else if (obj.value == "gametotals")
    {
        $("#awaySpreadSpan").css("display","none");
		$("#homeSpreadSpan").css("display","none");
        //document.getElementById("gameTotal").style.display = "table-row";
        $("#homeAwayTitles").css("display","none");
        $(".teamsRadio").css("display","none");
        $(".totalsRadio").css("display","inline");
        $("#opt1").attr("checked",true);
        $("#opt2").attr("checked",false);
        $("#opt3").attr("checked",true);
		$('#homeTeamHomeGms').attr('checked',true);
		$('#homeTeamAwayGms').attr('checked',true);
		$('#awayTeamHomeGms').attr('checked',true);
		$('#awayTeamAwayGms').attr('checked',true);
    }
    else
    {
        $("#awaySpreadSpan").css("display","none");
		$("#homeSpreadSpan").css("display","none");
        //document.getElementById("gameTotal").style.display = "none";
        $("#homeAwayTitles").css("display","table-row");
        $(".teamsRadio").css("display","inline");
        $(".totalsRadio").css("display","none");
		$('#awayRadio').attr('checked',false);
        $('#homeRadio').attr('checked',true);
        $("#opt1").attr("checked",true);
        $("#opt2").attr("checked",false);
        $("#opt3").attr("checked",false);
		$('#homeTeamHomeGms').attr('checked',true);
		$('#homeTeamAwayGms').attr('checked',true);
		$('#awayTeamHomeGms').attr('checked',false);
		$('#awayTeamAwayGms').attr('checked',false);
    }
    resetToolTips();
	refreshButtons();
	setOdds();
	//setPinnacleOdds(jsonArr);
}
function radioChange(obj)
{
    if ($(obj).attr('id') == "homeRadio" && $("#betType").val() != "gametotals")
    {
		$('#opt3').attr('checked',false);
        $('#opt1').attr('checked','checked');
        $('#opt2').attr('checked',false);
		$('#homeTeamHomeGms').attr('checked',true);
		$('#homeTeamAwayGms').attr('checked',true);
		$('#awayTeamHomeGms').attr('checked',false);
		$('#awayTeamAwayGms').attr('checked',false);
    }
    else if (obj.id == "awayRadio" && $("#betType").val() != "gametotals")
    {
		$('#opt3').attr('checked','checked');
        $('#opt1').attr('checked',false);
        $('#opt2').attr('checked',false);
		$('#awayTeamHomeGms').attr('checked',true);
		$('#awayTeamAwayGms').attr('checked',true);
		$('#homeTeamHomeGms').attr('checked',false);
		$('#homeTeamAwayGms').attr('checked',false);
    }
	refreshButtons();
}
function checkBoxClick(obj)
{
	if ($(obj).attr('id') == "opt3" && $(obj).attr('checked')=='checked')
    {
		$('#opt2').attr('checked',false);
		$('#awayTeamHomeGms').attr('checked',true);
		$('#awayTeamAwayGms').attr('checked',true);
		if ($("#opt1").attr('checked')!='checked')
		{
			$('#homeTeamHomeGms').attr('checked',false);
			$('#homeTeamAwayGms').attr('checked',false);	
		}
	}
	else if ($(obj).attr('id') == "opt3" && $(obj).attr('checked')!='checked')
    {
		$('#awayTeamHomeGms').attr('checked',false);
		$('#awayTeamAwayGms').attr('checked',false);
	}
	else if ($(obj).attr('id') == "opt1" && $(obj).attr('checked')=='checked')
    {
		$('#opt2').attr('checked',false);
		$('#homeTeamHomeGms').attr('checked',true);
		$('#homeTeamAwayGms').attr('checked',true);	
		/*if ($("#opt3").attr('checked')!='checked')
		{
			$('#awayTeamHomeGms').attr('checked',false);
			$('#awayTeamAwayGms').attr('checked',false);	
		}*/
	}
	else if ($(obj).attr('id') == "opt1" && $(obj).attr('checked')!='checked')
    {
		$('#homeTeamHomeGms').attr('checked',false);
		$('#homeTeamAwayGms').attr('checked',false);	
	}
	else if ($(obj).attr('id') == "opt2")
	{
		$('#opt1').attr('checked',false);
		$('#opt3').attr('checked',false);
		$('#awayTeamHomeGms').attr('checked',true);
		$('#awayTeamAwayGms').attr('checked',true);
		$('#homeTeamHomeGms').attr('checked',true);
		$('#homeTeamAwayGms').attr('checked',true);	
	}
	else if (($(obj).attr('id') == "homeTeamHomeGms" || $(obj).attr('id') == "homeTeamAwayGms") && $('#opt2').attr('checked')!='checked')
	{
		$('#opt1').attr('checked','checked');
	}
	else if (($(obj).attr('id') == "awayTeamHomeGms" || $(obj).attr('id') == "awayTeamAwayGms") && $('#opt2').attr('checked')!='checked')
	{
		$('#opt3').attr('checked','checked');
	}
	if (($(obj).attr('id') == "homeTeamHomeGms" || $(obj).attr('id') == "homeTeamAwayGms") && $('#homeTeamAwayGms').attr('checked')!='checked' && $('#homeTeamHomeGms').attr('checked')!='checked')
	{
		$('#opt1').attr('checked',false);
	}
	else if (($(obj).attr('id') == "awayTeamHomeGms" || $(obj).attr('id') == "awayTeamAwayGms") && $('#awayTeamAwayGms').attr('checked')!='checked' && $('#awayTeamHomeGms').attr('checked')!='checked')
	{
		$('#opt3').attr('checked',false);
	}
	if ($('#awayTeamAwayGms').attr('checked')!='checked' && $('#awayTeamHomeGms').attr('checked')!='checked' && $('#homeTeamAwayGms').attr('checked')!='checked' && $('#homeTeamHomeGms').attr('checked')!='checked')
	{
		$('#opt2').attr('checked',false);
	}
	
	refreshButtons();
}
function refreshButtons()
{
	$('#awayTeamHomeGms').button("refresh");
	$('#awayTeamAwayGms').button("refresh");
	$('#homeTeamHomeGms').button("refresh");
	$('#homeTeamAwayGms').button("refresh");
}
function setOdds()
{
	if ($('#gameDate option:selected').val() == 'yesterday')
	{
		setPinnacleOdds(jsonDataYest);
	}
	else if ($('#gameDate option:selected').val() == 'today')
	{
		setPinnacleOdds(jsonDataToday);
	}
	else if ($('#gameDate option:selected').val() == 'tomorrow')
	{
		setPinnacleOdds(jsonDataTomorrow);
	}
}

function setPinnacleOdds(arrayWithOdds)
{
	//first set things if it is set to money line
	if ($('#betType option:selected').val() == 'moneyline')
	{
		var homeOddsIndicator;
		homeOddsIndicator = "";
		var awayOddsIndicator;
		awayOddsIndicator = "";
		if (arrayWithOdds['Pinnacle']['home_team_moneyline'][$('#gameSelect option:selected').val()] > 0)
		{
			homeOddsIndicator = "+";
		}
		if (arrayWithOdds['Pinnacle']['away_team_moneyline'][$('#gameSelect option:selected').val()] > 0)
		{
			awayOddsIndicator = "+";
		}
		//set pinnacle
		$('#homeOdds').val(arrayWithOdds['Pinnacle']['home_team_moneyline'][$('#gameSelect option:selected').val()]);
		$('#homeOddsSpan').html(homeOddsIndicator+arrayWithOdds['Pinnacle']['home_team_moneyline'][$('#gameSelect option:selected').val()]);
		$('#awayOdds').val(arrayWithOdds['Pinnacle']['away_team_moneyline'][$('#gameSelect option:selected').val()]);
		$('#awayOddsSpan').html(awayOddsIndicator+arrayWithOdds['Pinnacle']['away_team_moneyline'][$('#gameSelect option:selected').val()]);
		
		//set bet online
		if (typeof arrayWithOdds['Bet Online'] != 'undefined')
		{
			$('#homeOddsBetOnline').val(arrayWithOdds['Bet Online']['home_team_moneyline'][$('#gameSelect option:selected').val()]);
			$('#awayOddsBetOnline').val(arrayWithOdds['Bet Online']['away_team_moneyline'][$('#gameSelect option:selected').val()]);
		}
	}
	//set things if it is puck line
	else if ($('#betType option:selected').val() == 'puckline')
	{
		var homeOddsIndicator;
		homeOddsIndicator = "";
		var awayOddsIndicator;
		awayOddsIndicator = "";
		if (arrayWithOdds['Pinnacle'].home_team_spread_adjust[$('#gameSelect option:selected').val()] > 0)
		{
			homeOddsIndicator = "+";
		}
		if (arrayWithOdds['Pinnacle'].away_team_spread_adjust[$('#gameSelect option:selected').val()] > 0)
		{
			awayOddsIndicator = "+";
		}
		var homeSpreadIndicator;
		homeSpreadIndicator = "";
		var awaySpreadIndicator;
		awaySpreadIndicator = "";
		if (arrayWithOdds['Pinnacle'].home_team_spread[$('#gameSelect option:selected').val()] > 0)
		{
			homeSpreadIndicator = "+";
		}
		if (arrayWithOdds['Pinnacle'].away_team_spread[$('#gameSelect option:selected').val()] > 0)
		{
			awaySpreadIndicator = "+";
		}
		$('#homeOdds').val(arrayWithOdds['Pinnacle'].home_team_spread_adjust[$('#gameSelect option:selected').val()]);
		
		$('#homeOddsSpan').html(homeOddsIndicator+arrayWithOdds['Pinnacle'].home_team_spread_adjust[$('#gameSelect option:selected').val()]);
		$('#awayOdds').val(arrayWithOdds['Pinnacle'].away_team_spread_adjust[$('#gameSelect option:selected').val()]);
		$('#awayOddsSpan').html(awayOddsIndicator+arrayWithOdds['Pinnacle'].away_team_spread_adjust[$('#gameSelect option:selected').val()]);
		$('#homeSpread').val(arrayWithOdds['Pinnacle'].home_team_spread[$('#gameSelect option:selected').val()]);
		$('#homeSpreadSpan').html(homeSpreadIndicator + arrayWithOdds['Pinnacle'].home_team_spread[$('#gameSelect option:selected').val()] +" ");
		$('#awaySpread').val(arrayWithOdds['Pinnacle'].away_team_spread[$('#gameSelect option:selected').val()]);	
		$('#awaySpreadSpan').html(awaySpreadIndicator + arrayWithOdds['Pinnacle'].away_team_spread[$('#gameSelect option:selected').val()] + " ");	

		if (typeof arrayWithOdds['Bet Online'] != 'undefined')
		{		
			$('#homeOddsBetOnline').val(arrayWithOdds['Bet Online'].home_team_spread_adjust[$('#gameSelect option:selected').val()]);
			$('#awayOddsBetOnline').val(arrayWithOdds['Bet Online'].away_team_spread_adjust[$('#gameSelect option:selected').val()]);
			$('#homeSpreadBetOnline').val(arrayWithOdds['Bet Online'].home_team_spread[$('#gameSelect option:selected').val()]);
			$('#awaySpreadBetOnline').val(arrayWithOdds['Bet Online'].away_team_spread[$('#gameSelect option:selected').val()]);	
		}
	}
	else if ($('#betType option:selected').val() == 'gametotals')
	{
		var homeOddsIndicator;
		homeOddsIndicator = "";
		var awayOddsIndicator;
		awayOddsIndicator = "";
		if (arrayWithOdds['Pinnacle'].total_over_adjust[$('#gameSelect option:selected').val()] > 0)
		{
			homeOddsIndicator = "+";
		}
		if (arrayWithOdds['Pinnacle'].total_under_adjust[$('#gameSelect option:selected').val()] > 0)
		{
			awayOddsIndicator = "+";
		}

		$('#homeOdds').val(arrayWithOdds['Pinnacle'].total_over_adjust[$('#gameSelect option:selected').val()]);
		$('#homeOddsSpan').html(homeOddsIndicator + arrayWithOdds['Pinnacle'].total_over_adjust[$('#gameSelect option:selected').val()]);
		$('#awayOdds').val(arrayWithOdds['Pinnacle'].total_under_adjust[$('#gameSelect option:selected').val()]);
		$('#awayOddsSpan').html(awayOddsIndicator + arrayWithOdds['Pinnacle'].total_under_adjust[$('#gameSelect option:selected').val()]);
		$('#gameTotalInput').val(arrayWithOdds['Pinnacle'].total_points[$('#gameSelect option:selected').val()]);
		$('.gameTotalInputSpan').html(arrayWithOdds['Pinnacle'].total_points[$('#gameSelect option:selected').val()]);
		
		if (typeof arrayWithOdds['Bet Online'] != 'undefined')
		{	
			$('#homeOddsBetOnline').val(arrayWithOdds['Bet Online'].total_over_adjust[$('#gameSelect option:selected').val()]);
			$('#awayOddsBetOnline').val(arrayWithOdds['Bet Online'].total_under_adjust[$('#gameSelect option:selected').val()]);
			$('#gameTotalBetOnline').val(arrayWithOdds['Bet Online'].total_points[$('#gameSelect option:selected').val()]);
		}
	}
}
this.tooltip2 = function(){	
	/* CONFIG */		
		xOffset = 10;
		yOffset = 20;		
		// these 2 variable determine popup's distance from the cursor
		// you might want to adjust to get the right result		
	/* END CONFIG */		
	$("a.tooltip").hover(function(e){											  
		this.t = this.title;
		this.title = "";									  
		$("body").append("<p id='tooltip'>"+ this.t +"</p>");
		$("#tooltip")
			.css("top",(e.pageY - xOffset) + "px")
			.css("left",(e.pageX + yOffset) + "px")
			.fadeIn("fast");		
    },
	function(){
		this.title = this.t;		
		$("#tooltip").remove();
    });	
	$("a.tooltip").mousemove(function(e){
		$("#tooltip")
			.css("top",(e.pageY - xOffset) + "px")
			.css("left",(e.pageX + yOffset) + "px");
	});			
};
function getLowCaseTeamFromShortTeam(shortTeam)
{
	var teamNames = new Object();
	teamNames['WSH'] = 'capitals';
	teamNames['PIT'] = 'penguins';
	teamNames['TBL'] = 'lightning';
	teamNames['OTT'] = 'senators';
	teamNames['NYR'] = 'rangers';
	teamNames['DET'] = 'redwings';
	teamNames['TOR'] = 'mapleleafs';
	teamNames['COL'] = 'avalanche';
	teamNames['BOS'] = 'bruins';
	teamNames['CGY'] = 'flames';
	teamNames['VAN'] = 'canucks';
	teamNames['SJS'] = 'sharks';
	teamNames['ANA'] = 'ducks';
	teamNames['NJD'] = 'devils';
	teamNames['NYI'] = 'islanders';
	teamNames['NYR'] = 'rangers';
	teamNames['CHI'] = 'blackhawks';
	teamNames['CAR'] = 'hurricanes';
	teamNames['FLA'] = 'panthers';
	teamNames['BUF'] = 'sabres';
	teamNames['MTL'] = 'canadiens';
	teamNames['WPG'] = 'jets';
	teamNames['STL'] = 'blues';
	teamNames['NSH'] = 'predators';
	teamNames['DAL'] = 'stars';
	teamNames['CBJ'] = 'bluejackets';
	teamNames['PHI'] = 'flyers';
	teamNames['MIN'] = 'wild';
	teamNames['PHX'] = 'coyotes';
	teamNames['LAK'] = 'kings';
	teamNames['EDM'] = 'oilers';
	return teamNames[shortTeam];

};
function getLowCaseTeamFromShortTeamMLB(shortTeam)
{
	var teamNames = new Object();
	teamNames['BAL'] = 'orioles';
	teamNames['TBR'] = 'rays';
	teamNames['TOR'] = 'bluejays';
	teamNames['BOS'] = 'redsox';
	teamNames['NYY'] = 'yankees';
	teamNames['DET'] = 'tigers';
	teamNames['CWS'] = 'whitesox';
	teamNames['KCR'] = 'royals';
	teamNames['CLE'] = 'indians';
	teamNames['MIN'] = 'twins';
	teamNames['SEA'] = 'mariners';
	teamNames['LAA'] = 'angels';
	teamNames['TEX'] = 'rangers';
	teamNames['OAK'] = 'athletics';
	teamNames['NYM'] = 'mets';
	teamNames['WSH'] = 'nationals';
	teamNames['PHI'] = 'phillies';
	teamNames['MIA'] = 'marlins';
	teamNames['ATL'] = 'braves';
	teamNames['STL'] = 'cardinals';
	teamNames['CIN'] = 'reds';
	teamNames['HOU'] = 'astros';
	teamNames['MIL'] = 'brewers';
	teamNames['PIT'] = 'pirates';
	teamNames['CHC'] = 'cubs';
	teamNames['LAD'] = 'dodgers';
	teamNames['ARI'] = 'd-backs';
	teamNames['COL'] = 'rockies';
	teamNames['SFG'] = 'giants';
	teamNames['SDP'] = 'padres';
	return teamNames[shortTeam];
};

function setupPlayers(reset_bool,callback,trade_bool)
{
	if (callback===undefined) {
		callback=function(){};
	}
	var ret = $.ajax({
		type: "POST",
		dataType:'json',
		data: 'reset='+reset_bool+'&trade='+trade_bool,
		url: URL_BASEURL +'/'+ mainURI + '/' + 'get_player_stats',
		success: function(data){
				//alert(data);
				$("#playersContainer").html("");
				for (i in data)
				{
					$("#playersContainer").append(data[i]['summary']);
					$("#playersContainer").append("<div class='team_div' id='team"+i+"'></div>");
					for (j in data[i]['players'])
					{
						$("#team"+i).append(data[i]['players'][j]);
					}
				}	
				$("#playersContainer").buttonset();
				$(".playerImageID").tooltip({
					position: 'bottom center'
				});
				callback();
		}
	 });
}
function compareTeams()
{
	$("#players_to_add").val(JSON.stringify(players_to_add_array));
	var ret = $.ajax({
		type: "POST",
		dataType:'json',
		data: $("#statform").serialize(),
		url: URL_BASEURL +'/'+ mainURI + '/' + 'compare_teams',
		success: function(data){
			graphData(data,function(){
				setupPlayers(false);
				removeAddPlayers();
			});
		}
	});
}

function add_player_fantasy_team()
{
	var pd_val ='';
	var toAdd;
	var i =0;
	var flag;
	toAdd = $("#player_name").val();
	checkPlayerFantasy(toAdd);
}
function checkPlayerFantasy(pname)
{
	var ret = $.ajax({
		type: "POST",
		url: URL_CHECKPLAYER,
		dataType:'json',
		data: 'term='+pname,
		success: function(msg){
			
			if(msg.name!= 'error')
			{
				var player_exists_boolean = false;
				for (i in players_to_add_array)
				{
					if (players_to_add_array[i] == msg.name)
					{
						player_exists_boolean = true;
					}
				}
				if (player_exists_boolean == false)
				{
					players_to_add_array.push(msg.name);
					var ret2 = $.ajax({
						type: "POST",
						url: URL_GETIMAGE,
						dataType:'json',
						data: 'term='+msg.name,
						success: function(msg2){
							$("#added_players").html($("#added_players").html()+msg2);
						}
						});
						$("#player_name").select();
					return true;
				}
				else
				{
					alert("Player is already in list");
					return false;
				}
				//$("#added_players").html($("#added_players").html()+" "+msg.name);
			}
			else
			{
				alert(msg.message);
				return false;
			}
		}
	});
}
function removeAddPlayers()
{
	//$("#add_player").html("<div id='reset_analysis_as_div' class='div_as_button' onclick='reset_team();'>Reset Team</div>");
	$("#reset_analysis_as_div").css("display","inline-block");
	$("#add_player").css("display","none");
	RESET_STATE_BOOL = true;
}
function reset_team(callback)
{
	if (callback===undefined) {
		callback=function(){};
	}
	//window.location = URL_FANTASY_HOCKEY;
	setupPlayers(true, callback());
	$("#reset_analysis_as_div").css("display","none");
	players_to_add_array = [];
	$("#added_players").html("");
	setupChart1();
}
function load_add_drop()
{
	setupPlayers(RESET_STATE_BOOL,function(){
		$("#reset_analysis_as_div").css("display","none");
		players_to_add_array = [];
		$("#added_players").html("");
		$("#add_player").css("display","block");
		$(".playerCheckboxDiv").css("display","block");
	});
}
function change_team_select(obj)
{
	change_team(obj.value);
}
function change_team(team_name)
{
	var ret = $.ajax({
		type: "POST",
		url: URL_CHANGE_TEAM,
		dataType:'json',
		data: 'term='+team_name,
		success: function(msg)
		{
			setupFantasy();
			players_to_add_array = [];
			$("#added_players").html("");
			$("#reset_analysis_as_div").css("display","none");
			$(".playerCheckboxDiv").css("display","none");
			$("#add_player").css("display","none");
			RESET_STATE_BOOL = false;
		}
	});
}
function load_make_trade(trade_team)
{
	if (trade_team===undefined) {
		trade_team="";
	}
	$("#add_player").css("display","none");
	if (RESET_STATE_BOOL == true)
	{
		reset_team();
	}
	var ret = $.ajax({
		type: "POST",
		url: URL_SETUP_TRADE,
		dataType:'json',
		data: 'term='+trade_team,
		success: function(data)
		{
			if ($('#tradeTeam').length == 0)
			{
				$("#playersContainer").append("<div id='tradeTeam'></div>");
			}
			for (i in data)
			{
				$("#tradeTeam").html(data[i]['summary']);
				$("#tradeTeam").append("<div class='team_div' id='team"+i+"'></div>");
				for (j in data[i]['players'])
				{
					$("#team"+i).append(data[i]['players'][j]);
				}
				$("#tradeTeam .playerImageID").tooltip({
					position: 'bottom center'
				});
			}
			$(".playerCheckboxDiv").css("display","block");
			//$("#tradeTeam label").text("Trade");
			//$("#tradeTeam").buttonset();
			$("#playersContainer").buttonset("destroy");
			$("#tradeTeam").append("<div id='analyze_trade_button' class='div_as_button' onclick='analyzing_trade();'>Analyze Trade</div>");
			$(".playerCheckboxDiv label").text("Trade");
			$("#playersContainer").buttonset();
			//$("#team0 .playerCheckboxDiv").css("display","block");
		}
	});
}
function change_trade_team(obj)
{
	load_make_trade(obj.value);
}
function analyzing_trade()
{
	var ret = $.ajax({
		type: "POST",
		url: URL_EXECUTE_TRADE,
		dataType:'json',
		data: $("#statform").serialize(),
		success: function(data){
			graphData(data,function(){
				setupPlayers(false, function(){}, true);
				removeAddPlayers();
				RESET_STATE_BOOL = true;
			});
		}
	});
}
function load_compare_teams()
{
	var ret = $.ajax({
		type: "POST",
		url: URL_COMPARE_ALL_TEAMS,
		dataType:'json',
		success: function(data){
			graphData(data,function(){
				setupPlayers(RESET_STATE_BOOL);
				removeAddPlayers();
				$("#reset_analysis_as_div").css("display","none");
			});
		}
	});
}