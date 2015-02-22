<?php $this->load->view('header'); ?>
<div id="wrapper">
    <?php if($message!=''){
        ?>
		<div id="popupmsg"><?php echo $message;?></div>
		<script type="text/javascript">
		<!--
		$(document).ready(function() {
			$("#popupmsg").dialog({ buttons: { "Ok": function() { $(this).dialog("close"); } }, modal:true });
		});
		//-->
		</script>

        
    <?php
    }?>
    
	<div id="innerWrapper">
		<div class="Banner_wrapper">
			<div class="topcurve"></div>
				<div class="middlecurve">
                    <div class="lftbanner">
                        <h1>NHL Stats Machine</h1>
						<div class="searchDiv">
<?php print form_open('auth/login',array('class'=>'vertical'))?>
    <fieldset>
        <ol>
            <li>
				<label for="email">Email:</label>
				<?php echo form_input($email);?>
            </li>
            <li>
				<label for="password">Password:</label>
				<?php echo form_input($password);?>
            </li>

            <li>
            	

			<div class='subButtons'>

            		<div class='betaLogL'>
            		    <input type='image' src="/images/loginButton.png" alt="Submit Form" />
            		</div>
					<div class='betaLogR'>
						<a href="<?php print site_url('auth/register') ?>"><img src="/images/registerButton.png" ></a>
					</div>
				</div>


            </li>
        </ol>
    </fieldset>
<?php print form_close()?>
</div>
					</div><!--  left banner-->
                        
                    <div class="banner_rht">
                        <iframe frameborder="no" height="255px" id="myiframe" scrolling="no" src="http://statsmachine.ca/stats/embed/CVajhK" width="380px"></iframe>
					</div><!--  left banner-->
				<div style="clear:both; height:0px;"> </div>
				</div><!-- middle curve -->
			<div class="bottomcurve"></div>
        </div><!-- End of menu_wrapper -->
        
		<div class="container_wrapper">
			<div class="main_cont">
	        	<div id="introbox">
	              	<h2>Welcome to Smart Sports Fan - NHL Edition!</h2>
	              	<p>
					Easy-to-use charts for NHL statistics that let you drill down into the details and compare players.
					</p>
					

				</div>
		                  
				<div style="clear:both;"> </div>
		              
                <div class="cont_lft_div">
                	<div class="con_box1">
                    	<div class="lft_con_box1">
                        	<p class="img"><a href="javascript:return false;"><img src="/images/beta/checkmark.png" width="56" height="70" alt=""></a></p>
                        </div>
                        <div class="rht_con_box1">
                        	<h2><a href="javascript:return false;">Easy-to-use and Interactive</a></h2>
                            <p>Our simple web interface lets you to filter statistics by turning switches on-and-off. </p>
                        </div>   
     			 	</div>
                                    
                                    
                    <div class="con_box2">
                    	<div class="lft_con_box1">
                        	<p class="img"><a href="javascript:return false;"><img src="/images/beta/checkmark.png" width="56" height="70" alt=""></a></p>
                        </div>
                        <div class="rht_con_box1">
                        	<h2><a href="javascript:return false;">Compare and Contrast Players</a></h2>
                            <p>See how players perform against each other in nearly any statistical category.</p>
                        </div>   
         			 </div>
                </div><!-- left div -->
                        
                <div class="cont_rht_div">
                	<div class="con_box1">
                    	<div class="lft_con_box1">
                        	<p class="img"><a href="javascript:return false;"><img src="/images/beta/checkmark.png" width="56" height="70" alt=""></a></p>
                        </div>
                        <div class="rht_con_box1">
                        	<h2><a href="javascript:return false;">Export as Images and PDFs</a></h2>
                            <p>Use them on your Facebook, Twitter, and even your Blog!</p>
                        </div>
     			 	</div>

                    <div class="con_box2">
                    	<div class="lft_con_box1">
                        	<p class="img"><a href="javascript:return false;"><img src="/images/beta/checkmark.png" width="56" height="70" alt=""></a></p>
                        </div>
                        <div class="rht_con_box1">
                        	<h2><a href="javascript:return false;">100% Free</a></h2>
                            <p>All our statistics are free to use - just sign up and have fun!</p>
                        </div>   
         			 </div>
				</div><!-- right div -->

            	<div class="clear"></div>
				<div>
				    <br />
					<br />
					<h2>New Sports Betting Analytics for Hockey and Baseball!</h2>
					<p>
						You can now analyze the profitability of bets based on real-time odds. 
					</p>
				</div>
			</div><!-- main_cont -->  
    	</div><!-- End Of container_wrapper -->          
   
    	<div class="clear">&nbsp;</div>
    </div><!-- End Of inner_wrapper -->
 
</div><!-- End Of Wrapper -->
<?php $this->load->view('footer'); ?>