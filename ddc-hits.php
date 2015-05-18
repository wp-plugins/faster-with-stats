<?php if ( ddc_is_pro() ) { ?>
  <div class="metabox-holder">
    <div class="postbox">
      <h3><?php echo _e( 'Hits on posts', ECPM_DDC );?></h3>
      <div class="inside">
        <div class="charts-widget">
          <?php ecpm_show_hits();?>
        </div>
      </div>  
    </div>
  </div>  
<?php
  show_move_form(); 
}
 else
   buy_pro_screenshot();
?>