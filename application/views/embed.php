<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.6.1/jquery.min.js" type="text/javascript"></script>
<script src="/js/highcharts.js" type="text/javascript"></script>
<script src="/js/exporting.js" type="text/javascript"></script>
<script type="text/javascript" src="/js/embed.js"></script>
<?=$playersToAdd?>
<script type="text/javascript">
var stat = '<?=substr($statistic,3)?>';
var players = '<?=$pvals?>';

var datafile = '/stats/compare/<?php echo $statistic.'/'.$seasons.'/'.$pvals.'/'.$Strength.'/'.$goalPeriods.'/'.$teamAgainst.'/'.$gt_vals.'/'.$es_stat.'/'.$teamPenalties.'/'.$location;?>';
</script>

<div style='width:600px;height:420px;display:block;background-color:#fff'>
	<div id="chart1-cont" style="width:600px; height:420px">
	    
	</div>
</div>