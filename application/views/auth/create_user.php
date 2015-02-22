<?php $this->load->view('header'); ?>
<div class='mainInfo'>

	<h1>Create User</h1>
	<p>Please enter the users information below.</p>
	
	<div id="infoMessage"><?php echo $message;?></div>
	
    <?php echo form_open("auth/create_user");?>
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
            <td colspan='2'><?php echo form_submit('submit', 'Create User');?></td>
        </tr>
    </table>      
    <?php echo form_close();?>
</div>
<?php $this->load->view('footer'); ?>