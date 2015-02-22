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
        text: 'Player Comparison'
    },
    exporting: {
        enabled: false
    },

    credits: {
        text: '',
        href: ''
    },

    legend: {
        enabled: true,
        layout: 'vertical',
        backgroundColor: '#FFFFFF',
        align: 'left',
        verticalAlign: 'top',
        floating: true,
        x: 30,
        y: 45
    },

    yAxis: {
        title: {
            text: null
        }
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
						    y: 20 
						 },
						 
						tickmarkPlacement: 'on',
						title: {
							enabled: false
						}

					},
    yAxis: {
        title: {
            text: 'Goals'
        }
    },
    plotOptions: {
        series: {
            marker: {
                enabled:false
            }
        }
    },
    series: []
};
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
	return pd_val;
}
$(document).ready(function() {
	var playerNameTitle = 'test';
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
		options.xAxis.labels.enabled = false;
		options.yAxis.endOnTick=false;
		options.yAxis.max = 50;		
		options.yAxis.min = 0;
		options.yAxis.title.text = "";
		options.title.text = stat;
		//alert(dump(options.series));
		//alert(dump(options.yAxis.title.text));
		// Create the chart
		chart = new Highcharts.Chart(options);
	});
});