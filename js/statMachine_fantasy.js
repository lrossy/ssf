var	colorArrayStatic = new Array();
	colorArrayStatic[1] = '#5EE6FF';
	colorArrayStatic[2] = '#98E460';
	colorArrayStatic[3] = '#D285EB';
	colorArrayStatic[4] = '#448AD9';
	colorArrayStatic[5] = '#FEDD00';
	colorArrayStatic[6] = '#FF2E78';
var chart;
var ajaxReq;
var options = {
    chart: {
        renderTo: 'chart1-cont-fant',
		defaultSeriesType: 'line'
    },
    title: {
        text: 'Fantasy Analysis',
        style:{
            fontSize: '24px'
        }
    },
    exporting: {
        enabled: false,
        filename: 'fantasyanalysis'
    },

    credits: {
        text: '',
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
        enabled: true,
        layout: 'vertical',
        backgroundColor: '#FFFFFF',
        align: 'left',
        verticalAlign: 'top',
        floating: true,
        x: 90,
        y: 45
    },
	/*colors: [
	'#448AD9', 
	'#98E460', 
	'#D285EB', 
	'#5EE6FF', 
	'#FEDD00', 
	'#FF2E78', 
	'#92A8CD', 
	'#A47D7C', 
	'#B5CA92'
	],*/
   // xAxis: {
   //     categories: []
    //},
	xAxis: {

						 tickInterval: null, //1 * 24 * 3600 * 1000, // one week
						 tickWidth: 0,
						 gridLineWidth: 1,
						 labels: {
						    align: 'right',
						    x: 0,
						    y: 20,
							rotation: -30,
						    style: {
						        fontSize: '14px'
						    }
						 },
						 
						tickmarkPlacement: 'on',
						title: {
							enabled: false
						},
						type: 'datetime',
						dateTimeLabelFormats: {
								second: '%H:%M:%S',
								minute: '%H:%M',
								hour: '%H:%M',
								day: '%e %b %y',
								week: '%e %b %y',
								month: '%b \'%y',
								year: '%Y'
						}
					},
    yAxis: {
        title: {
            text: 'Fantasy Points'
        },
        labels: {
			    formatter: function() {
                    return Highcharts.numberFormat(this.value, 0, '.', ',');
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
        pointFormat: '{point.name}</table><div>{series.name}: ' +
            '<span style="text-align: right"><b>{point.y}</b></span></div>',
        //footerFormat: '</table>',
        valueDecimals: 2
    }
};
var datafile;


function hc_get_line_vals(team_names){
    var pd_val = [];
    var subarr = [];

	for (i in team_names)
	{
		subarr[i*2] = team_names[i];
		subarr[i*2+1] = colorArrayStatic[i];
	}

    pd_val[0]=subarr;
    return subarr;
}
function setupChart1(chk){
    /**
     * PARAMS+_REQUIRED: GAMETYPE, GAMEID,BET_AMOUNT, BET_TEAM_ID, OPPONENT_ID, HOME_ODDS, AWAY_ODDS, HOME_TEAM_AGAINST_IDS, AWAY_TEAM_AGAINST_IDS, FORCE_HOME_AWAY_ONLY
     * FROM_DATE, TO_DATE, CUMULATIVE_PROFIT, AVG_PROFIT_PER_BET, CALC_ODDS, BOOK_ODDS
     *
     * MONEYLINE ONLY VARS - NONE
     * PUCKLINE ONLY VARS - HOME_SPREAD, AWAY_SPREAD
     * GAMETOTALS ONLY VARS - SPREAD
     *
     */

    console.log($('form').serialize());

	if(typeof(ajaxReq) !== 'undefined') {
	    ajaxReq.abort();
	}

    ajaxReq = $.ajax({
		type: "POST",
		url: URL_BASEURL+'/'+mainURI+'/run_analysis/',
		data: $('form').serialize(), 
		success: function(data) {
			data = jQuery.parseJSON(data);
		
		//graph all the data
			graphData(data,function(){});
		}
	});
	return true;
}

function graphData(data, callback){
	team_names = data[0];
	data = data[1];
	chart = null;
	options.series = [];
	var k = 0;
	var arrPlayers = hc_get_line_vals(team_names);
	var lines = data.split('\n');
	var i = 0;
	var count = 0;
	//options.xAxis.categories =[]
	// Iterate over the lines and add categories or series
	$.each(lines, function(lineNo, line) {
		var items = line.split(';');
		if (lineNo == 0) {
			//$.each(items, function(itemNo, item) {
			   //options.xAxis.categories.push(item);
			   //count++;
			//});
		}
		// the rest of the lines contain data with their name in the first position
		else {
			var series = {
				data: [],
				color: arrPlayers[2*i+1],
				name: arrPlayers[2*i]
			};
			i++;
			$.each(items, function(itemNo, item) {
				var y=item.replace(/\[/g,'');
				var z=y.replace(/\]/g,'');
				var subItem = z.split(',');
				var tmp = [];
				//console.log(subItem[2]);

				//series.data.push([parseFloat(subItem[0]),parseFloat(subItem[1])]);

				series.data.push({
					x: parseFloat(subItem[0])*1000, //convert strtotime to javascript time in milliseconds
					y: parseFloat(subItem[1]),
					name: subItem[2]
				});
				//series.data.id = subItem[2];
			});

			options.series.push(series);

		}
	});	
	
	//alert( dump( options.series[1]));
	//options.xAxis.tickInterval = Math.round(count/8);
	//options.xAxis.labels.rotation = -25;
	//options.xAxis.labels.align = 'right';
	//options.yAxis.title.text = 'Fantasy Points';
	//options.title.text = 'Fantasy Points';
	//alert(dump(options.series));
	//alert(dump(options.yAxis.title.text));
	// Create the chart
	chart = new Highcharts.Chart(options,function(){callback();});
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


$(document).ready(function() {
});


