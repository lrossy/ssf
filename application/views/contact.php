<?php $this->load->view('header'); ?>
<?php echo validation_errors(); ?>
<div id="about">
	<h3>
		About Smart Sports Fan
	</h3>
	<p>
		With all the statistics available in sports today and the growing popularity of sports betting and fantasy, we thought that an analytics platform, like Google Finance but for sports statistics, would be useful for sports fans. Right now, our tools are available for the NHL, but expect to see more sports in the near future.
	</p>
	<p>
		On our platform, you can compare players and teams for all NHL statistics. You can slice and dice the data based on team strength (PP, SH, EV), type of game (regular season or playoffs), period, etc...  
	</p>
	<p>
		Our newest addition is the betting analytics platform which we are really excited about. You can analyze the three key types of hockey bets: Money Line, Puck Line, and Game Totals. It lets you perform sophisticated analyses through a simple interface - hopefully it will help you win a lot of money!
	</p>
	<p>
		We are really excited about the future of Smart Sports Fan. Before next hockey season, expect to see some really cool Fantasy Tools that we are currently working on.
	</p>
	<p>
		If there is anything on the site that doesn't work, or if there are any features that you would like see, we would really like to hear from you, so please do not hesitate to reach out. You can contact me directly at <a href="mailto:mike@statsmachine.ca" target="_blank">mike@statsmachine.ca</a> or feel free to use the below form.<br />Looking forward to hearing from you!
	</p>
	<p>
		-Mike
	</p>
</div>

<div>
	<h3>
		Contact Form
	</h3>
	<p />
<?php echo form_open('contact'); ?>
<table>
    <tr>
        <td><label for'first_name'>First Name:</label></td>
        <td><?php echo form_input($first_name);?></td>
    </tr>
        <tr>
            <td><label for'last_name'>Last Name:</label></td>
            <td><?php echo form_input($last_name);?></td>
        </tr>
    <tr>
        <td><label for'email'>Email:</label></td>
        <td><?php echo form_input($email);?></td>
    </tr>
    <tr>
        <td><label for'reason'>Reason:</label></td>
        <td><?php echo form_dropdown('reason',$reason);?></td>
    </tr>
    <tr>
        <td><label for'message'>Message:</label></td>
        <td><?php echo form_textarea($message);?></td>
    </tr>
    <tr>
    <td colspan='2' style = 'text-align:right;padding:10px 10px 0 0'><?php echo form_submit('submit', 'Submit');?></td>
    </tr>
</table>
<?php echo form_close();?>
</div>
<?php $this->load->view('footer'); ?>