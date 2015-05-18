<?php if ( ddc_is_pro() ) { ?>
  <div class="metabox-holder">
    <div class="postbox">
      <h3><?php echo _e( 'Users and posts', ECPM_DDC );?></h3>
      <div class="inside">
        <div class="charts-widget">
          <?php ecpm_count_users();?>
        </div>
      </div>  
    </div>
  </div>    
  
  <h3><?php echo _e( 'Top users', ECPM_DDC );?></h3>
  <div class="charts-widget">
    <?php ecpm_top_users();?>
  </div>  
  
<!-- top users - table -->    
<?php 
}
 else
   buy_pro_screenshot();
?>