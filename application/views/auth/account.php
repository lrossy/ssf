<?php $this->load->view('header'); ?>

<h3>Account Information</h3>
<p><?php echo "Welcome ".$this->data['user']->email ?></p>
<p>You can change your password here.</p>
<p><?php include_once "change_password.php"?></p>
<?php $this->load->view('footer'); ?>