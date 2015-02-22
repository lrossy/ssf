var	colorArrayStatic = new Array();
	colorArrayStatic[1] = '#448AD9';
	colorArrayStatic[2] = '#98E460';
	colorArrayStatic[3] = '#D285EB';
	colorArrayStatic[4] = '#5EE6FF';
	colorArrayStatic[5] = '#FEDD00';
	colorArrayStatic[6] = '#FF2E78';
var chart;
var ajaxReq;
var options = {
    chart: {
        renderTo: 'chart1-cont',
		defaultSeriesType: 'line'
    },
    title: {
        text: 'Player Comparison',
        style:{
            fontSize: '24px'
        }
    },
    exporting: {
        enabled: true,
        filename: 'StatsMachine'
    },

    credits: {
        text: 'StatsMachine.ca',
        href: 'http://www.statsmachine.ca',
        position: {
            y: 0
        }
    },

    legend: {
        enabled: false,
        layout: 'vertical',
        backgroundColor: '#FFFFFF',
        align: 'left',
        verticalAlign: 'top',
        floating: true,
        x: 70,
        y: 45
    },


    tooltip: {
        crosshairs: true
    },
	colors: [
	'#448AD9', 
	'#98E460', 
	'#D285EB', 
	'#5EE6FF', 
	'#FEDD00', 
	'#FF2E78', 
	'#92A8CD', 
	'#A47D7C', 
	'#B5CA92'
	],
   // xAxis: {
   //     categories: []
    //},
	xAxis: {

						 //tickInterval: 1 * 24 * 3600 * 1000, // one week
						 tickWidth: 0,
						 gridLineWidth: 1,
						 labels: {
						    align: 'left',
						    x: 0,
						    y: 20,
						    style: {
						        fontSize: '11px'
						    }
						 },
						 
						tickmarkPlacement: 'on',
						title: {
							enabled: false
						},

						categories: []

					},
    yAxis: {
        title: {
            text: null
        },
        plotLines: [{
            color: '#888888',
            width: 2,
            value: 0
        }]
    },
    plotOptions: {
        series: {
            marker: {
                enabled: false
            }
        }
    },
    series: []
};
var datafile;

function loadGraph(id, s){
    if(!gamble){
        window.location.replace(URL_BASEURL+"/stats/index/"+id+"/"+s);
    }
    else{
        window.location.replace(URL_BASEURL+"/gamble/index/"+id+"/"+s);
    }
}
function loadPremade(type){
    if(!gamble){
        window.location.replace(URL_BASEURL+"/stats/index/"+type);
    }
    else{
        window.location.replace(URL_BASEURL+"/gamble/index/"+type);
    }
}
	function switchtabs(divId,butID, ft) {
		if(divId=='') divId= 'tabGoal';
		head = document.getElementById('statHeader');

		//	tabStats = document.getElementById('tabStats');		
		if(divId=='tabPims') {pimSelection()}
		else if(divId=='tabEventstats') {esSelection()}
		else if(divId=='tabGrandSalami') {gamb_GS_Selection()}
		else if(divId=='tabShots') {gamb_MS_Selection()}
		else if(divId=='tabGoalies') {alert('Under Construction');return false; }
		else GAPSelection();
		
		
		//$('ul li.statMenu').removeClass().addClass('statMenu');
		//$('#'+butID).addClass('current');
		document.getElementById('statistic').value = divId;
		//document.getElementById('currentStat').innerHTML = divId.substring(3);

		if(divId!='tabEventstats'){
			$('#statPreMadeData p').removeClass("selected");
			$('#'+butID).parent().addClass("selected");
			$('#currentStat').html($('#'+butID).attr('rel'));
		//$('#currentStat').html($(this).html());
		}
		else{
			$('#statPreMadeData p.selected').find("a").each(function(index, ele) {
				$('#currentStat').html($(this).html());
				
		});
		}
		//if(ft)
		 
		if(!ft){
			$('#smStat').click();
			setupChart1();
		}
	}
	function esSelection(){
		$("#cntESSel").show(); $('#cntStr').hide(); $('#cntPer').hide();  $("#cntPIMSel").hide();
	}
	function pimSelection(){
		$('#cntStr').hide();  $("#cntPIMSel").show();$("#cntESSel").hide();
	}
	function GAPSelection(){
		 $("#cntPIMSel").hide(); $("#cntESSel").hide(); $('#cntPer').show(); $('#cntStr').show();
	}
	function gamb_GS_Selection(){
		 //$("#cntPIMSel").hide(); $("#cntESSel").hide(); $('#cntPer').hide(); $('#cntStr').hide();
		//$("#chart1-players").hide(); $("#cntSeas").hide(); $("#cntGT").hide(); $("#cntGL").hide();$("#cntTA").hide();
	}
	function gamb_MS_Selection(){
		// $("#cntPIMSel").hide(); $("#cntESSel").hide(); $('#cntPer').hide(); $('#cntStr').hide();
		//$("#chart1-players").hide(); $("#cntSeas").show(); $("#cntGT").show(); $("#cntGL").show();$("#cntTA").show();
	}
	function addPlayer(playerName){
		var pd_val ='';
		var toAdd;
		var i =0;
		toAdd = $("#player_name").val();
		flag = checkPlayer(toAdd,0);
	}
	function addPlayerDiv(hiddenEle,playerName,refresh){
	//	alert(hiddenEle+' | '+playerName);
		var tmpSRC ='';
		var the_length=hiddenEle.length;
		var last_char=hiddenEle.charAt(the_length-1);

		var nextItemId = last_char;


		$('#genPlayerDiv').clone(true).removeAttr('id').attr('id','playerDiv'+nextItemId).removeClass('hidden').addClass('playerBox').appendTo( $('#curPlayers'));
			$('#playerDiv'+nextItemId).find("div").each(function(index, ele) {
			if ($(this).attr('id')=='genericName'){
				$(this).attr('id','playerName'+nextItemId);
			}
			else if	($(this).attr('id')=='genericPos'){
				$(this).attr('id','playerPos'+nextItemId);
			}
		});
			$('#playerDiv'+nextItemId).find("img").attr('id','playerMug'+nextItemId).click(function() {
				remPlayer2(nextItemId)
			});
			$('#playerDiv'+nextItemId).find(".plCurrentStat").click(function() {
				toggleGraphed(nextItemId)
			});
			$('#playerMug'+nextItemId).hover(
				  function () {
					tmpSRC = $(this).attr("src");
					$(this).stop(true, true).attr("src", "/images/pl_rem.png");
					  
				  },
				  function () {
					
					$(this).stop(true, true).attr("src", tmpSRC);
				  }
				);
		PlayerTitleId = playerName;
		if (PlayerTitleId!="empty"){
		var PlayerTitleIdAJAX = PlayerTitleId.replace("'", "|");
		gt_vals = get_gametype_value();
		curSeason = get_season_dates();
		setToggleGraphed(nextItemId);

	if (refresh =='1')
	{setupChart1();
	}
	}
	}
	function remPlayer2(pId){
 		//check if last player
		pvals = get_player_values();
		checkNumPlayers = pvals.split(":")
		//if(checkNumPlayers.length==1){
		//	alert('You must have at least one player selected');
		//	return false;
		//}
		//else{
		$('#stat_player_name'+pId).val('empty');
		$('#playerDiv'+pId).remove();

		setupChart1();
		//}
	}
	function remPlayer3(pId){
 		//check if last player
		pvals = get_player_values();
		checkNumPlayers = pvals.split(":")
		if(checkNumPlayers.length==1){
			alert('You must have at least one player selected');
			return false;
		}
		else{
		$('#stat_player_name'+pId).val('empty');
		$('#playerDiv'+pId).remove();

		setupChart1();
		}
	}
	function toggleGraphed(pId){
 		//check if last player
		if($('#graphed_player_name'+pId).val() == '1'){
			$('#graphed_player_name'+pId).val(0);
			$('#playerDiv'+pId).toggleClass("notGraphed");
		}
		else {
			$('#graphed_player_name'+pId).val(1);
			$('#playerDiv'+pId).toggleClass("notGraphed");

		}
		
		setupChart1();
	}
	function setToggleGraphed(pId){
 		//check if last player
		
		if($('#graphed_player_name'+pId).val()=='0'){
			$('#playerDiv'+pId).addClass("notGraphed");
		}
	}
	function updatePlStats(gt_vals,curSeason){
		var arrName='';
		$.each($(".pn"), function(i,v) {
			
			var theTag = v.tagName;

			var theElement = $(v);
			
			var the_id =theElement.attr('id');
			var theValue = theElement.val();
			if (theValue != 'empty')
			{
				curDiv = the_id.charAt(16);
				pname = theValue;
				//pname = pname.replace("'", "|");
				arrName = arrName + ':' + pname+curDiv;
			}
			//alert(arrName);
		});
		//alert(arrName);
		arrName = arrName.substr(1);
//"+URL_BASEURL+"/stats/playerData/ALEXANDER%20OVECHKIN1:PATRICK%20KANE2:EVGENI%20MALKIN3:SIDNEY%20CROSBY4:BRIAN%20GIONTA5:ALEX%20KOVALEV6/20102011/re
//"+URL_BASEURL+"/stats/playerData2/tabGoal/20102011/ALEXANDER%20OVECHKIN%3APATRICK%20KANE%3AEVGENI%20MALKIN%3ASIDNEY%20CROSBY%3ABRIAN%20GIONTA%3AALEX%20KOVALEV/EV-SH-PP/f-s-t-ot/1000015-1000024-1000006-1000022-1000023-1000009-1000008-1000030-1000027-1000010-1000011-1000025-1000029-1000028-1000012-1000014-1000013-1000002-1000026-1000003-1000007-1000019-1000018-1000004-1000005-1000021-1000017-1000016-1000001-1000020/re/0/0/hm-aw

		$.getJSON("/"+ajaxPrefix+"/playerData2/" + predatafile + arrName + postdatafile,
		function(data){

			for (var i=0; i <= data.length-1; i++){
				dataobj = data[i];
				//alert(dump(dataobj));
				Player1Team = dataobj.team;
				if (typeof(dataobj.statVal) !== 'undefined')
				{
					//$("#playerPos"+dataobj.curDivID).html( "<table class='curPlSt'><tr><td colspan = '8'><b>"+dataobj.gametypeHeader+" Totals</b></td></tr><tr><td>Season(s)</td><td>GP</td><td>G</td><td>A</td><td>PTS</td><td>+/-</td><td>PIM</td><td>SOG</td></tr><tr><td>"+dataobj.seasonText+"</td><td>"+dataobj.GP+"</td><td>"+dataobj.Goals+"</td><td>"+dataobj.Assists+"</td><td>"+dataobj.Points+"</td><td>"+dataobj.PlusMinus+"</td><td>"+dataobj.PIM+"</td><td>"+dataobj.SOG+"</td></tr></table>");
					//$("#playerPos"+dataobj.curDivID).html( "</tr><tr><td>"+dataobj.seasonText+"</td><td>"+dataobj.GP+"</td><td>"+dataobj.Goals+"</td><td>"+dataobj.Assists+"</td><td>"+dataobj.Points+"</td><td>"+dataobj.PlusMinus+"</td><td>"+dataobj.PIM+"</td><td>"+dataobj.SOG+"</td></tr></table>");
				
					$("#playerPos"+dataobj.curDivID).html( dataobj.statVal );
					if (typeof(dataobj.id) !== 'undefined'){
						$("#playerMug"+dataobj.curDivID).attr("src", "http://www.nhl.com/photos/mugs/"+dataobj.id+".jpg");
						$("#playerName"+dataobj.curDivID).html(dataobj.lname);
					}
					else{
						$("#playerMug"+dataobj.curDivID).attr("src", "/images/team/"+dataobj.team_id+".png").addClass('team');
						$("#playerName"+dataobj.curDivID).html(dataobj.lname).addClass('teamName');
					}
					//$("#playerName"+curDivID).html(data.fname + " " + data.lname + " ("+data.team+"-"+data.pos+")");
					
				}
			}
		});
	}


function get_radio_value(){
	var rad_val = '';

	for (var i=0; i < document.statform.Strength.length; i++)
   {
   if (document.statform.Strength[i].checked)
      {
		var rad_val = rad_val + '-' + document.statform.Strength[i].value;
	  }
   }
   return rad_val.substr(1);
}
function get_period_value(){
	var per_val = '';

	for (var i=0; i < document.statform.Period.length; i++)
   {
   if (document.statform.Period[i].checked)
      {
		var per_val = per_val + '-' + document.statform.Period[i].value;
	  }
   }
   return per_val.substr(1);
}
function get_location_value(){
	var loc_val = '';

	for (var i=0; i < document.statform.Location.length; i++)
   {
   if (document.statform.Location[i].checked)
      {
		var loc_val = loc_val + '-' + document.statform.Location[i].value;
	  }
   }
   return loc_val.substr(1);
}

function get_teamAgainst_value(){
	var ta_val = '';
	var theCount = 0;
	$.each($("input[name='teamAgainst']:checked"), function(i,v) {

			var theTag = v.tagName;
			var theElement = $(v);
			var theValue = theElement.val()
			ta_val = ta_val + '-' + theValue;
			theCount++;
		});	
	returnStr = theCount+'|'+ta_val.substr(1);
   return returnStr;
   
}
function get_teamPenalty_value(){
	var ta_val = '';
	var theCount = 0;
	$.each($("input[name='teamPenalty']:checked"), function(i,v) {

			var theTag = v.tagName;
			var theElement = $(v);
			var theValue = theElement.val()
			ta_val = ta_val + '-' + theValue;
			theCount++;
		});	
	returnStr = theCount+'|'+ta_val.substr(1);
   return returnStr;
   
}
function get_teamPenalty_value2(arrChecked){
	var pen_val = '';
	if(arrChecked.length ==67) return '0';
	for (var i=0; i < arrChecked.length; i++)
   {
		var pen_val = pen_val + '-' + arrChecked[i];
   }
   return pen_val.substr(1);
}
function get_player_values(set){
	var pd_val ='';
	$.each($(".pn"), function(i,v) {
		var theTag = v.tagName;
		var theElement = $(v);
		var theValue = theElement.val();
		var theId = theElement.attr('id');
		var playerNumber = theId.substring(theId.length-1);
		var graphedCheck = $('#graphed_player_name'+playerNumber).val();
		//alert(theId.length);
		//alert('#graphed_player_name'+playerNumber+'--'+ graphedCheck);
		if (set=='1')
		{
		var theName = theElement.attr('name');
			if(theValue !='empty' && graphedCheck=='1'){
				pd_val = pd_val + ':' + theValue + '|' + theName;
			}
		}
		else{
			if(theValue !='empty' && graphedCheck=='1'){
				pd_val = pd_val + ':' + theValue;
			}
		}
	});
	return pd_val.substr(1);
}
function hc_get_player_values(){
	var pd_val = [];
	var x = 1;
	colorArrayStatic
	$.each($(".pn"), function(i,v) {
		var subarr = [];
		var theTag = v.tagName;
		var theElement = $(v);
		var theValue = theElement.val();
		var theId = theElement.attr('id');
		var playerNumber = theId.substring(theId.length-1);
		var graphedCheck = $('#graphed_player_name'+playerNumber).val();
		//alert(theId.length);
		var theName = theElement.attr('name');
		if(theValue !='empty' && graphedCheck=='1'){
			subarr[0] = theValue;
			subarr[1] = theName;
			subarr[2] = colorArrayStatic[theName.charAt(theName.length-1)]
		
			pd_val[x]=subarr;
			x = x+1;
		}
		
	});
    console.log(pd_val);
	return pd_val;
}
function count_players(){
	var out =0;
	$.each($(".pn"), function(i,v) {
		var theTag = v.tagName;
		var theElement = $(v);
		var theValue = theElement.val();
		if(theValue !='empty'){
			out += 1;
		}
	});
	return out;
}
function get_js_player_vals(){
	var pd_val ='<graphs>';
	var i = 0;
	$.each($(".pn"), function(i,v) {
		var theTag = v.tagName;
		var theElement = $(v);
		var theValue = theElement.val();

		var theName = theElement.attr('name');
			if(theValue !='empty'){
				pd_val = pd_val + "<graph gid='"+i+"'><title>"+theValue+"</title><color>0D8ECF</color><color_hover>FF0F00</color_hover><selected>0</selected></graph><graph gid='1'><title>Smoothed</title><color>B0DE09</color><color_hover>FF0F00</color_hover><line_width>2</line_width><fill_alpha>30</fill_alpha><bullet>round</bullet><balloon_text><![CDATA[<div style='text-align:center'><b>{value}</b></div><br />"+theValue+"]]></balloon_text><visible_in_legend>false</visible_in_legend></graph>";
			}
		i +=1;
	});
	pd_val +='</graphs>';
	return pd_val;
}
function isInList(playerName){
var retList='1';
	$.each($(".pn"), function(i,v) {
		var theTag = v.tagName;
		var theElement = $(v);
		var theValue = theElement.val().toUpperCase();
		var playerNameUP = playerName.toUpperCase();
		if (theValue==playerNameUP)
		{
		retList ='0';
		return retList;
		}
	});
	return retList;
}
function build_player_titles(maxNum){
	var pd_val ='';
	var j = 0;
	$.each($(".pn"), function(i,v) {
		var theTag = v.tagName;
		var theElement = $(v);
		var theValue = theElement.val();
		if(theValue !='empty'){
			pd_val = pd_val + ' vs. ' + theValue;j++;
		}
	});
	if (j>=maxNum) return 'Multiple Players';
	else return pd_val.substr(5);
}
function get_gametype_value(){
	var gt_val = '';

	for (var i=0; i < document.statform.GameType.length; i++)
   {
   if (document.statform.GameType[i].checked)
      {
		var gt_val = gt_val + '-' + document.statform.GameType[i].value;
	  }
   }

   return gt_val.substr(1);
}
function get_season_dates(){
	var sd_val = '';

	for (var i=0; i < document.statform.seasonDateType.length; i++)
   {
   if (document.statform.seasonDateType[i].checked)
      {
		var sd_val = sd_val + '-' + document.statform.seasonDateType[i].value;
	  }
   }

   return sd_val.substr(1);
}
function dump(arr,level) {
	var dumped_text = "";
	if(!level) level = 0;
	
	//The padding given at the beginning of the line.
	var level_padding = "";
	for(var j=0;j<level+1;j++) level_padding += "    ";
	
	if(typeof(arr) == 'object') { //Array/Hashes/Objects 
		for(var item in arr) {
			var value = arr[item];
			
			if(typeof(value) == 'object') { //If it is an array,
				dumped_text += level_padding + "'" + item + "' ...\n";
				dumped_text += dump(value,level+1);
			} else {
				dumped_text += level_padding + "'" + item + "' => \"" + value + "\"\n";
			}
		}
	} else { //Stings/Chars/Numbers etc.
		dumped_text = "===>"+arr+"<===("+typeof(arr)+")";
	}
	return dumped_text;
}
function setupChart1(chk){
	chart = null;
	options.series = [];
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
	datafile = '/'+ajaxPrefix+'/compare/' + datafile;
	//var setting_file = "/amline/amline_settings2.php?n="+escape(pvals2)+"&s="+statistic+"&es="+es_val+"&t=TEST";
	//IF gamble, then add remaining gambling stats
	if(gamble && statistic=='tabShots'){

	}

	//alert(datafile);
	
	graph_infoFile = jsPVAL;
	var playerNameTitle = build_player_titles(6);
	if(typeof(ajaxReq) !== 'undefined') {
	    ajaxReq.abort();
	}
	
    ajaxReq = $.post(datafile, function(data) {
		var k = 1;
		var arrPlayers = hc_get_player_values();
		var lines = data.split('\n');
		var count = 0;
		options.xAxis.categories =[]
		// Iterate over the lines and add categories or series
		$.each(lines, function(lineNo, line) {
		    var items = line.split(';');
            if (lineNo == 0) {
                $.each(items, function(itemNo, item) {
                    
                   options.xAxis.categories.push(item);

                   count++;
                });
            }
            // the rest of the lines contain data with their name in the first position
            else {
                var series = {
                    data: [],
                    color: arrPlayers[k][2],
    				name: arrPlayers[k++][0]
                };
                $.each(items, function(itemNo, item) {
                    var y=item.replace(/\[/g,'');
                    var z=y.replace(/\]/g,'');
                    var subItem = z.split(',');
                    var tmp = [];

                   // alert(subItem[0]);
                   
                    series.data.push([parseFloat(subItem[0]),parseFloat(subItem[1])]);
                });
                
                options.series.push(series);
                //alert(options.series);
            }
		});
		//alert( dump( options.series[1]));
		options.xAxis.tickInterval = Math.round(count/8);
		options.xAxis.labels.rotation = -45;
		options.xAxis.labels.align = 'right';
		options.yAxis.title.text = $('#currentStat').html();
		options.title.text = $('#currentStat').html();
		//alert(dump(options.series));
		//alert(dump(options.yAxis.title.text));
		// Create the chart
		chart = new Highcharts.Chart(options);
	});
	/*ajaxReq = $.getJSON(datafile, function(data) {
		var k = 1;
		var arrPlayers = hc_get_player_values();
		// Iterate over the lines and add categories or series
		$.each(data, function(lineNo, line) {
			//alert(line);
			var series = {
				color: arrPlayers[k][2],
				name: arrPlayers[k][0],
				data: line
			};
			options.series.push(series);

			k++;
		});
		options.yAxis.title.text = $('#currentStat').html();
		options.title.text = $('#currentStat').html();
		//alert(dump(options.yAxis.title.text));
		// Create the chart
		chart = new Highcharts.Chart(options);
	});*/
	if(chk!=1){

	updatePlStats(gt_vals,curSeason);
	}
	//$('.playerBox').corner('round 5px');
	return true;
}

function buildColorArray(){
	var out =1;
	var arrayOut = new Array();
	$.each($(".pn"), function(i,v) {
		var theTag = v.tagName;
		var theElement = $(v);
		var theValue = theElement.val();
		var theElement = $(v);
		var theId = theElement.attr('id');
		if(theValue !='empty'){
			var the_length=theId.length;
			var last_char=theId.charAt(the_length-1);
			arrayOut[out] = last_char;
			out++;
		}

	});
	return arrayOut;
}

function jqCheckAll3( id, pID )
{

   $( "#" + pID + " :checkbox").attr('checked', $('#' + id).is(':checked'));
}

$(document).ready(function() {
conf = new Object;
conf.eastAct = 1;
conf.westAct = 1;
conf.enable = function(name) {
	if (name=='westTeams' & conf.westAct ==0){conf.westAct =1;
	//alert('activate '+name);
	westdiv.setActive('centralTeams');
	westdiv.setActive('pacTeams');
	westdiv.setActive('nwTeams');
	}
	if (name=='eastTeams' & conf.eastAct ==0){conf.eastAct =1;
	//alert('activate '+name);
	eastdiv.setActive('atlTeams');
	eastdiv.setActive('neTeams');
	eastdiv.setActive('seTeams');
	}
}
conf.setActive = function(name) {
	if (name=='westTeams' & conf.westAct ==0){conf.westAct =1;
	//alert('activate '+name);
	}
	if (name=='eastTeams' & conf.eastAct ==0){conf.eastAct =1;
	//alert('activate '+name);
	}
}
conf.setInactive = function(name) {
	if (name=='westTeams' & conf.westAct ==1){conf.westAct =0;
	//alert('deactivate '+name);
	}
	if (name=='eastTeams' & conf.eastAct ==1){conf.eastAct =0;
	//alert('deactivate '+name);
	}
}
conf.disable = function(name) {
	if (name=='westTeams' & conf.westAct ==1){conf.westAct =0;
	//alert('deactivate '+name);
	westdiv.setInactive('centralTeams');
	westdiv.setInactive('pacTeams');
	westdiv.setInactive('nwTeams');
	}
	if (name=='eastTeams' & conf.eastAct ==1){conf.eastAct =0;
	//alert('deactivate '+name);
	eastdiv.setInactive('atlTeams');
	eastdiv.setInactive('neTeams');
	eastdiv.setInactive('seTeams');
	}
}
conf.checkVar = function(theDiv) {
	if (theDiv=='westTeams'){return conf.westAct;}
	if (theDiv=='eastTeams'){return conf.eastAct;}
	}
westdiv= new Object;
westdiv.name='westTeams';
westdiv.cenAct = 1;
westdiv.nwAct = 1;
westdiv.pacAct = 1;
westdiv.checkVar = function(theDiv) {
	if (theDiv=='centralTeams'){return westdiv.cenAct;}
	if (theDiv=='nwTeams'){return westdiv.nwAct;}
	if (theDiv=='pacTeams'){return westdiv.pacAct;}
	}
westdiv.setActive = function(theDiv) {
	if (theDiv=='centralTeams' & westdiv.cenAct ==0){westdiv.cenAct = 1;
	//alert('activate '+theDiv);
	}
	if (theDiv=='nwTeams'& westdiv.nwAct ==0){westdiv.nwAct = 1;
	//alert('activate '+theDiv);
	}
	if (theDiv=='pacTeams'& westdiv.pacAct ==0){westdiv.pacAct = 1;
	//alert('activate '+theDiv);
	}
	if (westdiv.pacAct+westdiv.nwAct+westdiv.cenAct==3){ conf.setActive(westdiv.name);}
}
westdiv.setInactive = function(theDiv) {
	if (theDiv=='centralTeams' & westdiv.cenAct ==1){westdiv.cenAct = 0;
	//alert('deactivate '+theDiv);
	}
	if (theDiv=='nwTeams' & westdiv.nwAct ==1){westdiv.nwAct = 0;
	//alert('deactivate '+theDiv);
	}
	if (theDiv=='pacTeams' & westdiv.pacAct ==1){westdiv.pacAct = 0;
	//alert('deactivate '+theDiv);
	}
	if (westdiv.pacAct+westdiv.nwAct+westdiv.cenAct!=3){ conf.setInactive(westdiv.name);}
}
eastdiv= new Object;
eastdiv.name='eastTeams';
eastdiv.atlAct = 1;
eastdiv.neAct = 1;
eastdiv.secAct = 1;
eastdiv.checkVar = function(theDiv) {
	if (theDiv=='atlTeams'){return eastdiv.atlAct;}
	if (theDiv=='neTeams'){return eastdiv.neAct;}
	if (theDiv=='seTeams'){return eastdiv.secAct;}
	}
eastdiv.setActive = function(theDiv) {
	if (theDiv=='atlTeams' & eastdiv.atlAct ==0){eastdiv.atlAct = 1;
	//alert('activate '+theDiv);
	}
	if (theDiv=='neTeams'& eastdiv.neAct ==0){eastdiv.neAct = 1;
	//alert('activate '+theDiv);
	}
	if (theDiv=='seTeams'& eastdiv.secAct ==0){eastdiv.secAct = 1;
	//alert('activate '+theDiv);
	}
	if (eastdiv.atlAct+eastdiv.neAct+eastdiv.secAct==3){ conf.setActive(eastdiv.name);}
}
eastdiv.setInactive = function(theDiv) {
	if (theDiv=='atlTeams' & eastdiv.atlAct ==1){eastdiv.atlAct = 0;
	//alert('deactivate '+theDiv);
	}
	if (theDiv=='neTeams' & eastdiv.neAct ==1){eastdiv.neAct = 0;
	//alert('deactivate '+theDiv);
	}
	if (theDiv=='seTeams' & eastdiv.secAct ==1){eastdiv.secAct = 0;
	//alert('deactivate '+theDiv);
	}
	if (eastdiv.atlAct+eastdiv.neAct+eastdiv.secAct!=3){ conf.setInactive(eastdiv.name);}
}

$('#westConfSel').click(function () { 
	//container = $(this).parent().parent().attr('id');
	thecontainer = 'westTeams';
	var temp = conf.checkVar(thecontainer);

	//alert(container);
	$.each($("#westTeams input[name='teamAgainst']"), function(i,v) {
		if (temp ==1)
		{
			var theElement = $(v);
			$(v).parent().removeClass("selected");
			$(v).parent().find(":checkbox").removeAttr("checked");
		}
		else {
			var theElement = $(v);
			$(v).parent().addClass("selected");
			$(v).parent().find(":checkbox").attr("checked","checked");
		}
	});
	if (temp ==1){conf.disable(thecontainer);}
	else {conf.enable(thecontainer);}
}
);
$('#eastConfSel').click(function () { 
	//alert($(this).attr('id'));
	//container = $(this).parent().parent().attr('id');
	thecontainer = 'eastTeams';

	var temp = conf.checkVar(thecontainer);
	
	$.each($("#eastTeams input[name='teamAgainst']"), function(i,v) {
		if (temp ==1)
		{
			var theElement = $(v);
			$(v).parent().removeClass("selected");
			$(v).parent().find(":checkbox").removeAttr("checked");
		}
		else {
			var theElement = $(v);
			$(v).parent().addClass("selected");
			$(v).parent().find(":checkbox").attr("checked","checked");
		}
	});
	if (temp ==1){conf.disable(thecontainer);}
	else {conf.enable(thecontainer);}
}
);


$('.divTitleW a').click(function () { 

	westSubContainer = $(this).parent().parent().parent().attr('id');
	var temp = westdiv.checkVar(westSubContainer);
	$.each($("#"+westSubContainer+" input[name='teamAgainst']"), function(i,v) {
		if (temp ==1)
		{
			var theElement = $(v);
			$(v).parent().removeClass("selected");
			$(v).parent().find(":checkbox").removeAttr("checked");
			
		}
		else {
			var theElement = $(v);
			$(v).parent().addClass("selected");
			$(v).parent().find(":checkbox").attr("checked","checked");
		}
		});
	if (temp ==1)
	{		westdiv.setInactive(westSubContainer);
	}
	else {
		westdiv.setActive(westSubContainer);
	}
	}
);
$('.divTitleE a').click(function () { 

	eastSubcontainer = $(this).parent().parent().parent().attr('id');
	var temp = eastdiv.checkVar(eastSubcontainer);
		

	$.each($("#"+eastSubcontainer+" input[name='teamAgainst']"), function(i,v) {
		if (temp ==1)
		{
			var theElement = $(v);
			$(v).parent().removeClass("selected");
			$(v).parent().find(":checkbox").removeAttr("checked");
			
		}
		else {
			var theElement = $(v);
			$(v).parent().addClass("selected");
			$(v).parent().find(":checkbox").attr("checked","checked");
		}
		});
	if (temp ==1)
	{		eastdiv.setInactive(eastSubcontainer);
	}
	else {
		eastdiv.setActive(eastSubcontainer);
	}
	}
);

$("#westTeams input:checked").parent().addClass("selected");

$("#westTeams .checkbox-select").click(
	function(event) {
	
		event.preventDefault();
		$(this).parent().addClass("selected");
		$(this).parent().find(":checkbox").attr("checked","checked");
		var parDiv = $(this).parent().parent().parent().attr('id');
		n = $("#"+$(this).parent().parent().parent().attr('id')+ " input:checked").length;
		if (n==5){westdiv.setActive(parDiv)}
		//setupChart1();
		}
);

$("#westTeams .checkbox-deselect").click(
	function(event) {
		event.preventDefault();
		$(this).parent().removeClass("selected");
		$(this).parent().find(":checkbox").removeAttr("checked");
		var parDiv = $(this).parent().parent().parent().attr('id');
		n = $("#"+$(this).parent().parent().parent().attr('id')+ " input:checked").length;
		westdiv.setInactive(parDiv)
		//setupChart1();
	}
);
$("#eastTeams input:checked").parent().addClass("selected");

$("#eastTeams .checkbox-select").click(
	function(event) {
		event.preventDefault();
		$(this).parent().addClass("selected");
		$(this).parent().find(":checkbox").attr("checked","checked");
		var parDiv = $(this).parent().parent().parent().attr('id');
		n = $("#"+$(this).parent().parent().parent().attr('id')+ " input:checked").length;
		if (n==5){eastdiv.setActive(parDiv)}
		//setupChart1();
		}
);

$("#eastTeams .checkbox-deselect").click(
	function(event) {
		event.preventDefault();
		$(this).parent().removeClass("selected");
		$(this).parent().find(":checkbox").removeAttr("checked");
		var parDiv = $(this).parent().parent().parent().attr('id');
		n = $("#"+$(this).parent().parent().parent().attr('id')+ " input:checked").length;
	
		eastdiv.setInactive(parDiv)
		//setupChart1();
	}
);
$("#allPenalties input:checked").parent().addClass("selected");

$("#allPenalties .checkbox-select").click(
	function(event) {
		event.preventDefault();
		$(this).parent().addClass("selected");
		$(this).parent().find(":checkbox").attr("checked","checked");
		}
);

$("#allPenalties .checkbox-deselect").click(
	function(event) {
		event.preventDefault();
		$(this).parent().removeClass("selected");
		$(this).parent().find(":checkbox").removeAttr("checked");
	}
);
$(".checklist input:checked").parent().addClass("selected");

/* handle the user selections */
$(".checklist .checkbox-select").click(
	function(event) {
		event.preventDefault();
		$(this).parent().addClass("selected");
		$(this).parent().find(":checkbox").attr("checked","checked");
		setupChart1();
	}
);

$(".checklist .checkbox-deselect").click(
	function(event) {
		event.preventDefault();
		$(this).parent().find(":checkbox").removeAttr("checked");
		ret = setupChart1();
		//alert(ret);
		if(ret == true){
		$(this).parent().removeClass("selected");
		}
		else{
		$(this).parent().find(":checkbox").attr("checked","checked");
		}
	}
);
   /*notification dialog setup*/


//$('#tabStats .checkbox-deselect').corner('round 5px');

//$('.checkbox-select').corner('round 5px');
});

jQuery(document).ready(function($) {
   textboxes = $("#player_name");

   if ($.browser.mozilla) {
      $(textboxes).keypress(checkForEnter);
   } else {
      $(textboxes).keydown(checkForEnter);
   }

   function checkForEnter(event) {
      if (event.keyCode == 13) {
		addPlayer();
         event.preventDefault();
         return false;
      }
   }
$('#pnAdvTog').click(function() {
  $('#pnAdv').toggle('slow', function() {
    // Animation complete.
  });
});

$("#penListAll").click(function()
  {
   var checked_status = this.checked;
   $("input[name=teamPenalty]").each(function(i,v)
   {
	var theElement = $(v);
	$(v).parent().addClass("selected");
	$(v).parent().find(":checkbox").attr("checked","checked");
   });
  });
$("#penListNone").click(function()
  {
   var checked_status = this.checked;
   $("input[name=teamPenalty]").each(function(i,v)
   {
	var theElement = $(v);
	$(v).parent().removeClass("selected");
	$(v).parent().find(":checkbox").removeAttr("checked");
   });
  });
  $("input[name=esOpts]:radio:checked").parent().addClass("selected");
$(".radio-select-es").click(
  function(event) {
    event.preventDefault();
    
   // var $boxes = $(this).parent().parent().parent().parent().children();
	//alert($(this).parent().parent().parent().parent().attr('id'))
   $('#statPreMadeData p').removeClass("selected");
    $(this).parent().addClass("selected");

	//set input value
	$('#esOption').val($(this).attr('rel'));
	$('#statistic').val('tabEventstats');
	switchtabs("tabEventstats","eventBut");
   // $(this).parent().find(":radio").attr("checked","checked");
	//	setupChart1();

  }
);
    $("input").addClass("idle");
        $("input").focus(function(){
            $(this).addClass("activeField").removeClass("idle");
    }).blur(function(){
            $(this).removeClass("activeField").addClass("idle");
    });
});

