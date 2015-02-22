<?php $this->load->view('header'); ?>
<div class='mainInfo'>
	<h1>Forgot Password</h1>
	<p>Please enter your <?php echo $identity_human;?> so we can send you an email to reset your password.</p>

<?php if(!empty($message)){
    ?>
    <div id="infoMessage"><?php echo $message;?></div>
<?php
}?>
	<?php echo form_open("auth/forgot_password");?>

		  <p><?php echo $identity_human;?>:<br />
		  <?php echo form_input($identity);?>
		  </p>
		  
		  <p><?php echo form_submit('submit', 'Submit');?></p>
		  
	<?php echo form_close();?>

</div>
<?php $this->load->view('footer'); ?>