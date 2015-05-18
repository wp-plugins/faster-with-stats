<?php
  if ( ddc_is_pro() ) { 
  
  echo '<div class="metabox-holder">
          <div class="postbox">';
  echo '    <h3>';

  switch (APP_TD) {
      case 'classipress':
        echo _e( 'No. of Ads', ECPM_DDC );  
        break;
      case 'clipper':
        echo _e( 'No. of Coupons', ECPM_DDC );
        break;
      case 'jobroller':
        echo _e( 'No. of Jobs', ECPM_DDC );
        break;
    }
  
  echo '    </h3>';
  echo '    <div class="inside">';
  echo '      <div class="charts-widget">';
  ecpm_count_ads();
  echo '</div></div></div></div>';  
   
 } 
 else 
    buy_pro_screenshot();
?>