<?php $this->load->view('header'); ?>
<div id="container">
<?php if($message!=''){
  ?>
<div id="infoMessage"><?php echo $message;?></div>
  <?php
}?>
<?php

$out =<<<EOT
$table

EOT;
echo $out;
?>


<script type="text/javascript">
  <!--

  function reorderP(){
    var order = $('#order').val().split(",");
    $('#curPlayers').reOrder(order, 'playerDiv');
  }
  function genEmbed(){
    flashMovie = document.getElementById("chart1flash");
    flashMovie.exportImage("<?=$url_embed?>" +'/'+ datafile);
  }

  (function($) {

    $.fn.reOrder = function(array, prefix) {
      return this.each(function() {
        prefix = prefix || "";
        if (array) {
          for(var i=0; i < array.length; i++)
            array[i] = $('#' + prefix + array[i]);

          $(this).empty();

          for(var i=0; i < array.length; i++)
            $(this).append(array[i]);
        }
      });
    }
  })(jQuery);

  //-->
</script>
<?php $this->load->view('footer'); ?>