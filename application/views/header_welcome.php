<?php
if( $this->router->method!='embed' && $this->router->fetch_class() != 'beta'){
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xmlns:fb="http://www.facebook.com/2008/fbml">

<head>
<meta http-equiv="content-type" content="text/html; charset=ISO-8859-1" />

<meta name="robots" content="all" />

<meta http-equiv="pragma" content="cache" />
<title>Smartsportsfan.com</title>

<script type="text/javascript">
<!--

// -->
</script>
<link  href="/css/reset.css" rel="stylesheet" type="text/css" />
<link  href="/css/bep_front_layout.css" rel="stylesheet" type="text/css" />
<link  href="/css/jquery.autocomplete.css" rel="stylesheet" type="text/css" />
<link  href="/css/jquery.multiselect.css" rel="stylesheet" type="text/css" />
<link  href="/css/jquery-ui-1.8.4.custom.css" rel="stylesheet" type="text/css" />
<!--[if IE 7]><link rel="stylesheet" type="text/css" href="/css/ie7.css" /><![endif]--> 
<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.6.1/jquery.min.js" type="text/javascript"></script>
<script src="/js/highcharts.js" type="text/javascript"></script>
<script src="/js/exporting.js" type="text/javascript"></script>
<script type="text/javascript" src="/js/jquery-ui-1.8.4.custom.min.js"></script>
<script type="text/javascript" src="/js/swfobject_js_f.js"></script>
<script type="text/javascript" src="/js/amcharts.js"></script>
<script type="text/javascript" src="/js/amfallback.js"></script>

<script type="text/javascript" src="/js/raphael.js"></script>
<script type="text/javascript" src="/js/jquery.multiselect.min.js"></script>
<script type="text/javascript" src="/js/statMachine_fallback.js"></script>
<script type="text/javascript" src="/js/jquery2.09.corner.js"></script>
<script type="text/javascript" src="/js/smFunctions.js"></script>
<script type="text/javascript" src="/js/smv2.js"></script>

</head>
<body>
<div id="header">
	<div id="logo">
		<ul id="mainmenu">
			<li><a href='<?=site_url()?>'>Home</a></li>
			<li><a href='<?=site_url('contact')?>'>Contact Us</a></li>
			<?php 
			if (!$this->ion_auth->logged_in())
			{
				$out = site_url('auth/login');
				$outText = "Login";
			}
			else{
				$out = site_url('auth/logout');
				$outText = 'Logout';
			}
			?>
			<li class='last'><a href='<?=$out?>'><?=$outText?></a></li>
		</ul>
	<div class='clear'></div>
	</div>
</div>
<div id="wrap" class="<?= $this->router->class?>">
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
<title>Smartsportsfan.com</title>

<script type="text/javascript">
<!--
var base_url = site_url();
var index_page = "";
// -->
</script>
<link  href="/css/beta_style.css" rel="stylesheet" type="text/css" />
</head>
<body>
 <div id="fb-root"></div>
    <script>
      window.fbAsyncInit = function() {
        FB.init({appId: '153770234646205', status: true, cookie: true, xfbml: true});
        
	            /* All the events registered */
	            FB.Event.subscribe('auth.login', function(response) {
	    			// do something with response
	                login();
	        	});
	
	            FB.Event.subscribe('auth.logout', function(response) {
	            // do something with response
	                logout();
	          	});
	            
	            
      };
      (function() {
        var e = document.createElement('script');
        e.type = 'text/javascript';
        e.src = document.location.protocol +
          '//connect.facebook.net/en_US/all.js';
        e.async = true;
        document.getElementById('fb-root').appendChild(e);
      }());
  
	function login(){
		document.location.href = "<?=site_url('auth/facebook')?>";
	}

	function logout(){
		document.location.href = "<?=site_url('auth/logout')?>";
		}
      
    </script>
<!--header -->
<div id="header">
	<div id="logo">
		<ul id="mainmenu">
			<li><a href='<?=site_url()?>'>Home</a></li>
			<li><a href='<?=site_url()?>'>SiteMap</a></li>
			<li><a href='<?=site_url()?>'>Conditions Of Use</a></li>
			<li><a href='<?=site_url('contact')?>'>Contact Us</a></li>
			<li class='last'><a href='<?=site_url()?>'>RSS</a></li>
		</ul>

	<div class='clear'></div>
	</div>
	</div>
<!--header ends-->
<?php
}

?>