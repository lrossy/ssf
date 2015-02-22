<?php
if( $this->router->method!='embed'  && $this->router->fetch_class() != 'beta'){
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xmlns:fb="http://www.facebook.com/2008/fbml">

<head>
<meta http-equiv="content-type" content="text/html; charset=ISO-8859-1" />

<meta name="robots" content="all" />

<meta http-equiv="pragma" content="cache" />
<title>Stats Machine | SmartSportsFan </title>

<script type="text/javascript">
<!--

// -->
</script>
<link  href="/css/reset.css" rel="stylesheet" type="text/css" />
<link  href="/css/bep_front_layout.css?<?=filemtime(getcwd().'/css/bep_front_layout.css')?>" rel="stylesheet" type="text/css" />
<link  href="/css/jquery.autocomplete.css" rel="stylesheet" type="text/css" />
<link  href="/css/jquery.multiselect.css" rel="stylesheet" type="text/css" />
<link  href="/css/jquery-ui-1.8.4.custom.css" rel="stylesheet" type="text/css" />
<!--[if IE 7]><link rel="stylesheet" type="text/css" href="/css/ie7.css" /><![endif]--> 
<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.6.1/jquery.min.js" type="text/javascript"></script>
<script src="/js/highcharts.js" type="text/javascript"></script>
<script src="/js/exporting.js" type="text/javascript"></script>
<script type="text/javascript" src="/js/jquery-ui-1.8.4.custom.min.js?<?=filemtime(getcwd().'/js/jquery.multiselect.min.js')?>"></script>

<script type="text/javascript" src="/js/jquery.multiselect.min.js?<?=filemtime(getcwd().'/js/jquery.multiselect.min.js')?>"></script>
<? if($this->router->fetch_class() != 'gambling' && $this->router->fetch_class() != 'pinnacle' && $this->router->fetch_class() != 'betting' && $this->router->fetch_class() != 'fantasy'){
?>
  <script type="text/javascript" src="/js/statMachine_fallback.js?<?=filemtime(getcwd().'/js/statMachine_fallback.js')?>"></script>
<?
}
elseif ($this->router->fetch_class() == 'gambling'){
  ?>
  <script type="text/javascript" src="/js/statMachine_gambling.js?<?=filemtime(getcwd().'/js/statMachine_gambling.js')?>"></script>
  <?
}
elseif ($this->router->fetch_class() == 'fantasy'){
  ?>
  <script type="text/javascript" src="/js/statMachine_fantasy.js?<?=filemtime(getcwd().'/js/statMachine_fantasy.js')?>"></script>
  <?
}
else {
  ?>
  <script type="text/javascript" src="/js/statMachine_pinnacle.js?<?=filemtime(getcwd().'/js/statMachine_pinnacle.js')?>"></script>
  <?
}
  ?>
<script type="text/javascript" src="/js/jquery2.09.corner.js"></script>
<script type="text/javascript" src="/js/smFunctions.js?<?=filemtime(getcwd().'/js/smFunctions.js')?>"></script>
<script type="text/javascript" src="/js/smv2.js?<?=filemtime(getcwd().'/js/smv2.js')?>"></script>

</head>
<body>
<div id="wrap" class="<?=$class?>">
	<div id="content-wrap" class="two-col">
	<?php }
	elseif($this->router->fetch_class() == 'beta'){
		?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xmlns:fb="http://www.facebook.com/2008/fbml">

<head>
<meta http-equiv="content-type" content="text/html; charset=ISO-8859-1" />

<meta name="robots" content="all" />

<meta http-equiv="pragma" content="cache" />
<title>Statsmachine.ca</title>

<script type="text/javascript">
<!--
var base_url = site_url();
var index_page = "";
// -->
</script>
<link  href="/css/jquery-ui-1.8.4.custom.css?<?=filemtime(getcwd().'/css/jquery-ui-1.8.4.custom.css')?>" rel="stylesheet" type="text/css" />
<link  href="/css/beta_style.css?<?=filemtime(getcwd().'/css/beta_style.css')?>" rel="stylesheet" type="text/css" />
<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.6.1/jquery.min.js" type="text/javascript"></script>
<script type="text/javascript" src="/js/jquery-ui-1.8.4.custom.min.js"></script>


</head>
<body>
<!--header -->
<div id="header">
	<div id="logo">
	</div>
</div>
<!--header ends-->
<?php
}

?>