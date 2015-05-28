<?php
  $ecpm_ddc_leave_days = get_option('ecpm_ddc_leave_days');
  $ecpm_ddc_record_threshold = get_option('ecpm_ddc_record_threshold');
  $ecpm_ddc_freq = get_option('ecpm_ddc_freq');
  
  $ecpm_ddc_move_back_data = get_option('ecpm_ddc_move_back_data');
  $ecpm_ddc_remove_data = get_option('ecpm_ddc_remove_data');

?>
<form id='ddcsettingform' method="post" action="">    
  <h3><?php echo _e('Move records older then (in days):', ECPM_DDC); ?>
  <Input type='text' size='3' Name ='ecpm_ddc_leave_days' value='<?php echo $ecpm_ddc_leave_days;?>'></h3>

  <h3><?php echo _e('Record threshold:', ECPM_DDC); ?>
  <Input type='text' size='5' Name ='ecpm_ddc_record_threshold' value='<?php echo $ecpm_ddc_record_threshold;?>'></h3>
  
  <h3><?php echo _e('Run this script:', ECPM_DDC); ?>
  <?php if ( ddc_is_pro() ) { ?>
    <select name="ecpm_ddc_freq">
      <option value="manual" <?php echo ($ecpm_ddc_freq == 'manual' ? 'selected':'') ;?>><?php _e('Manually', ECPM_DDC);?></option>
      <option value="daily" <?php echo ($ecpm_ddc_freq == 'daily' ? 'selected':'') ;?>><?php _e('Auto', ECPM_DDC);?></option>
    </select>
  <?php 
  } else { 
    echo _e('Manually', ECPM_DDC);
  } 
  ?></h3>
  <hr>
  <p><strong><?php echo _e('Plugin uninstall', ECPM_DDC ); ?></strong></p>
  <p>
  <Input type='checkbox' Name='ecpm_ddc_move_back_data' <?php echo ($ecpm_ddc_move_back_data == 'on' ? 'checked':'') ;?> >
  <?php echo _e('Put data back', ECPM_DDC); ?><br>
  <Input type='checkbox' Name='ecpm_ddc_remove_data' <?php echo ($ecpm_ddc_remove_data == 'on' ? 'checked':'') ;?> >
  <?php echo _e('Remove all plugin settings and tables', ECPM_DDC); ?>
  </p>
  <input type="submit" id="ecpm_ddc_submit" name="ecpm_ddc_submit" class="button-primary" value="<?php _e('Save settings', ECPM_DDC); ?>" />
</form>