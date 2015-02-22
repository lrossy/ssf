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
        renderTo: 'chart1-cont-gamb',
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
        filename: 'smartsportsfan-gambling'
    },

    credits: {
        text: 'SmartSportsFan.com/gambling',
        href: 'http://www.smartsportsfan.com/gambling/',
        position: {
			align: 'right',
			x: -10,
			verticalAlign: 'bottom',
			y: -66
        },
		style: {
			cursor: 'pointer',
			color: '#333333',
			fontSize: '15px'
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
						        fontSize: '12px'
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
        labels: {
			    formatter: function() {
                    return '$' + Highcharts.numberFormat(this.value, 0, '.', ',');
                },
				style: {
					fontSize: '14px'
				},
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
    series: [],
    tooltip: {
        crosshairs: true,
        useHTML: true,
       // headerFormat: '<div>{point.key}</div>',
        //pointFormat: '<div>{point.y}</div>'
        headerFormat: '',
            pointFormat: '{point.name}<div>{series.name}: ' +
            '<span style="text-align: right"><b>${point.y}</b></span></div>',
        footerFormat: '</table>',
        valueDecimals: 2
    }
};
var datafile;

function get_TeamOpponents_value(val,returnCount){
	var ta_val = '';
	var theCount = 0;
	$.each($("input[name='teamOpponent"+val+"']:checked"), function(i,v) {

			var theTag = v.tagName;
			var theElement = $(v);
			var theValue = theElement.val()
			ta_val = ta_val + '-' + theValue;
			theCount++;
		});
    if(returnCount){
        returnStr = theCount+'|'+ta_val.substr(1);
    }
	else{
        returnStr = ta_val.substr(1);
    }
   return returnStr;
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
function cntHTO(){
    homeTeamOppVals = get_TeamOpponents_value('Home',1) ;
    newHTOvals = homeTeamOppVals.split('|');
    $('#htoTeamCnt').text(newHTOvals[0]);
}
function cntATO(){
    awayTeamOppVals = get_TeamOpponents_value('Away',1) ;
    newATOvals = awayTeamOppVals.split('|')
    $('#atoTeamCnt').text(newATOvals[0]);
}
function hc_get_line_vals(){
    var pd_val = [];
    var subarr = [];

    subarr[0] = 'Cumulative Profit';
    subarr[1] = colorArrayStatic[1]

    pd_val[0]=subarr;
    return subarr;
}
function setupChart1(chk){
	chart = null;
	options.series = [];
    /**
     * PARAMS+_REQUIRED: GAMETYPE, GAMEID,BET_AMOUNT, BET_TEAM_ID, OPPONENT_ID, HOME_ODDS, AWAY_ODDS, HOME_TEAM_AGAINST_IDS, AWAY_TEAM_AGAINST_IDS, FORCE_HOME_AWAY_ONLY
     * FROM_DATE, TO_DATE, CUMULATIVE_PROFIT, AVG_PROFIT_PER_BET, CALC_ODDS, BOOK_ODDS
     *
     * MONEYLINE ONLY VARS - NONE
     * PUCKLINE ONLY VARS - HOME_SPREAD, AWAY_SPREAD
     * GAMETOTALS ONLY VARS - SPREAD
     *
     */

    homeTeamOppVals = get_TeamOpponents_value('Home',1) ;
    //console.log(homeTeamOppVals)
    newHTOvals = homeTeamOppVals.split('|');
    $('#htoTeamCnt').text(newHTOvals[0]);
    $('#buttTA').corner('round 5px');

    if (newHTOvals[0]==0){//teamAgainst='0';
        newHTOvals[1] ='-1';
    }

    awayTeamOppVals = get_TeamOpponents_value('Away',1) ;
    newATOvals = awayTeamOppVals.split('|')
    $('#atoTeamCnt').text(newATOvals[0]);
    //$('#buttTA').corner('round 5px');

    if (newATOvals[0]==0){//teamAgainst='0';
        newATOvals[1] ='-1';
    }

    console.log($('form').serialize());

	if(typeof(ajaxReq) !== 'undefined') {
	    ajaxReq.abort();
	}

    ajaxReq = $.post('index.php/gambling/compare',$('form').serialize(), function(data) {
		dataArr = jQuery.parseJSON(data);
		data = dataArr[0];
		$('#cumProf').html(dataArr[1]);
		$('#avgProf').html(dataArr[2]);
		$('#calcOdds').html(dataArr[3]);
		var k = 0;
		var arrPlayers = hc_get_line_vals();
        //console.log(arrPlayers);
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
                    color: arrPlayers[1],
    				name: arrPlayers[0]
                };
                $.each(items, function(itemNo, item) {
                    var y=item.replace(/\[/g,'');
                    var z=y.replace(/\]/g,'');
                    var subItem = z.split(',');
                    var tmp = [];
                    //console.log(subItem[2]);

                    //series.data.push([parseFloat(subItem[0]),parseFloat(subItem[1])]);

                    series.data.push({
                        x: parseFloat(subItem[0]),
                        y: parseFloat(subItem[1]),
                        name: subItem[2]
                    });
                    //series.data.id = subItem[2];
                });

                options.series.push(series);

            }
		});
		//alert( dump( options.series[1]));
		options.xAxis.tickInterval = Math.round(count/8);
		options.xAxis.labels.rotation = -25;
		options.xAxis.labels.align = 'right';
		options.yAxis.title.text = 'Cumulative Profit';
		options.title.text = $('#gameSelect option:selected').text() + ' - ' + $('#betType option:selected').text();
		//alert(dump(options.series));
		//alert(dump(options.yAxis.title.text));
		// Create the chart
		chart = new Highcharts.Chart(options);
	});
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

$('#westConfSelHome').click(function () {
	//container = $(this).parent().parent().attr('id');
	thecontainer = 'westTeams';
	var temp = conf.checkVar(thecontainer);

	//alert(container);
	$.each($("#westTeams input[name='teamOpponentHome']"), function(i,v) {
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
$('#eastConfSelHome').click(function () {
	//alert($(this).attr('id'));
	//container = $(this).parent().parent().attr('id');
	thecontainer = 'eastTeams';

	var temp = conf.checkVar(thecontainer);
	
	$.each($("#eastTeams input[name='teamOpponentHome']"), function(i,v) {
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

$('#westConfSelAway').click(function () {
        //container = $(this).parent().parent().attr('id');
        thecontainer = 'westTeams';
        var temp = conf.checkVar(thecontainer);

        //alert(container);
        $.each($("#westTeams input[name='teamOpponentAway']"), function(i,v) {
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
$('#eastConfSelAway').click(function () {
        //alert($(this).attr('id'));
        //container = $(this).parent().parent().attr('id');
        thecontainer = 'eastTeams';

        var temp = conf.checkVar(thecontainer);

        $.each($("#eastTeams input[name='teamOpponentAway']"), function(i,v) {
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

$('.divTitleWHome a').click(function () {

	westSubContainer = $(this).parent().parent().parent().attr('id');
	var temp = westdiv.checkVar(westSubContainer);
	$.each($("#"+westSubContainer+" input[name='teamOpponentHome']"), function(i,v) {
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
$('.divTitleEHome a').click(function () {

	eastSubcontainer = $(this).parent().parent().parent().attr('id');
	var temp = eastdiv.checkVar(eastSubcontainer);
		

	$.each($("#"+eastSubcontainer+" input[name='teamOpponentHome']"), function(i,v) {
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
$('.divTitleWAway a').click(function () {

        westSubContainer = $(this).parent().parent().parent().attr('id');
        var temp = westdiv.checkVar(westSubContainer);
        $.each($("#"+westSubContainer+" input[name='teamOpponentAway']"), function(i,v) {
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
$('.divTitleEAway a').click(function () {

        eastSubcontainer = $(this).parent().parent().parent().attr('id');
        var temp = eastdiv.checkVar(eastSubcontainer);


        $.each($("#"+eastSubcontainer+" input[name='teamOpponentAway']"), function(i,v) {
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
		//setupChart1();
	}
);

$(".checklist .checkbox-deselect").click(
	function(event) {
		event.preventDefault();
		$(this).parent().find(":checkbox").removeAttr("checked");
		$(this).parent().removeClass("selected");
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


