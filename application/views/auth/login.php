<?php $this->load->view('header'); ?>
<div class='mainInfo'>

	<div class="pageTitle">Login</div>
    <div class="pageTitleBorder"></div>
	<p>Please login with your email address and password below.</p>
	
<?php if(!empty($message)){
    ?>
    <div id="infoMessage"><?php echo $message;?></div>
<?php
}?>	
    <?php echo form_open("auth/login");?>
    <table>
        <tr>
            <td><label for'email'>Email:</label></td>
            <td><?php echo form_input($email);?></td>
        </tr>
        <tr>
            <td><label for'password'>Password:</label></td>
            <td><?php echo form_input($password);?></td>
        </tr>
        <tr>
            <td><label for'remember'>Remember Me:</label></td>
            <td><?php echo form_checkbox('remember', '1', FALSE);?></td>
        </tr>
        <tr>
            <td></td><td><input type='image' src="/images/loginButton.png" alt="Submit Form" />
				<a href="<?php print site_url('auth/register') ?>">
					<img src="/images/registerButton.png"></a>
			</td>
        </tr>
	  <tr><td></td><td>    <?php echo form_close();?>
      <a href="<?php print site_url('auth/forgot_password') ?>">
		Forgot Password</a></td>
		
	</tr>
    </table>

</div>
<?php $this->load->view('footer'); ?>