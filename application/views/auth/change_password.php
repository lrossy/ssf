<div>
<h1>Change Password</h1>

    <?php if($message!=''){
        ?>
		<div id="popupmsg"><?php echo $message;?></div>
		<script type="text/javascript">
		<!--
		$(document).ready(function() {
			$("#popupmsg").dialog({ buttons: { "Ok": function() { $(this).dialog("close"); } } });
		});
		//-->
		</script>

        
    <?php
    }?>

<?php echo form_open("auth/account_info");?>

      <p>Old Password:<br />
      <?php echo form_input($old_password);?>
      </p>
      
      <p>New Password:<br />
      <?php echo form_input($new_password);?>
      </p>
      
      <p>Confirm New Password:<br />
      <?php echo form_input($new_password_confirm);?>
      </p>
      
      <?php echo form_input($user_id);?>
      <p><?php echo form_submit('submit', 'Change');?></p>
      
<?php echo form_close();?>
</div>