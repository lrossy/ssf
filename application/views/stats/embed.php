<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.6.1/jquery.min.js" type="text/javascript"></script>
<script src="/js/highcharts.js" type="text/javascript"></script>
<script src="/js/exporting.js" type="text/javascript"></script>
<script type="text/javascript" src="/js/embed.js"></script>
<?=$playersToAdd?>
<script type="text/javascript">
var stat = '<?=substr($statistic,3)?>s';
var players = '<?=$pvals?>';

var datafile = '/stats/compare/<?php echo $statistic.'/'.$seasons.'/'.$pvals.'/'.$Strength.'/'.$goalPeriods.'/'.$teamAgainst.'/'.$gt_vals.'/'.$es_stat.'/'.$teamPenalties.'/'.$location;?>';

</script>

<div style='width:380px;height:247px;display:block;background-color:#fff'>
	<div id="chart1-cont" style="width:380px; height:247px">
	    
	</div>
</div>