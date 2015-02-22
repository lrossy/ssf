<?php $this->load->view('header'); ?>
<div class='mainInfo'>

	<h1>Register a new user</h1>
	<p>Please enter the your information below.</p>
	
<?php if(!empty($message)){
    ?>
    <div id="infoMessage"><?php echo $message;?></div>
<?php
}?>
    <?php echo form_open("auth/register");?>
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
            <td><label for'password_confirm'>Confirm Password:</label></td>
            <td><?php echo form_input($password_confirm);?></td>
        </tr>
        <tr>
            <td colspan='2' style = 'text-align:right;padding:10px 10px 0 0'><input type='image' src="/images/createAccount.png" alt="Submit Form" /></td>
        </tr>
    </table>      
    <?php echo form_close();?>

</div>
<?php $this->load->view('footer'); ?>