<?php
/*
Plugin Name: Faster with Stats
Plugin URI: http://www.easycpmods.com
Description: Faster with Stats is a lightweight plugin that will speed up your AppThemes installation by moving daily statistics data to a plugin table. Why? Because a large table with daily counters will make your site very slow. It works with <strong>Classipress, Jobroller</strong> and <strong>Clipper</strong>.
Author: Easy CP Mods
Version: 1.0.0
Author URI: http://www.easycpmods.com
*/

define('ECPM_DDC', 'ecpm-ddc');
define('DDC_NAME', '/faster-with-stats');

register_activation_hook( __FILE__, 'ecpm_ddc_activate');
register_deactivation_hook( __FILE__, 'ecpm_ddc_deactivate');

add_action('plugins_loaded', 'ecpm_ddc_plugins_loaded');
add_action('admin_init', 'ecpm_ddc_requires_version');
  
add_action('admin_menu', 'ecpm_ddc_create_menu_set');
  
function ddc_is_pro(){
  if ( file_exists( WP_PLUGIN_DIR . DDC_NAME . '/ddc-pro.php' ) ) {
    return true;
  }
  return false;
}

if ( ddc_is_pro() ) {
  add_action('admin_enqueue_scripts', 'ecpm_ddc_enqueuescripts');
  require_once( WP_PLUGIN_DIR . DDC_NAME . '/ddc-pro.php' );
} 

function ecpm_ddc_requires_version() {
  $allowed_apps = array('classipress', 'clipper', 'jobroller');
  
  if ( defined(APP_TD) && !in_array(APP_TD, $allowed_apps ) ) { 
	  $plugin = plugin_basename( __FILE__ );
    $plugin_data = get_plugin_data( __FILE__, false );
		
    if( is_plugin_active($plugin) ) {
			deactivate_plugins( $plugin );
			wp_die( "<strong>".$plugin_data['Name']."</strong> requires a AppThemes theme to be installed. Your Wordpress installation does not appear to have that installed. The plugin has been deactivated!<br />If this is a mistake, please contact plugin developer!<br /><br />Back to the WordPress <a href='".get_admin_url(null, 'plugins.php')."'>Plugins page</a>." );
		}
	}
}

function ecpm_ddc_activate() {
  ddc_table_create();
  
  $ecpm_ddc_installed = get_option('ecpm_ddc_installed');
  if ( $ecpm_ddc_installed != 'yes' ) {
    update_option( 'ecpm_ddc_leave_days', '1' );
    update_option( 'ecpm_ddc_record_threshold', '0' );
    update_option( 'ecpm_ddc_freq', 'manual' );
    update_option( 'ecpm_ddc_installed', 'yes' );
    update_option( 'ecpm_ddc_remove_data', '' );
    update_option( 'ecpm_ddc_move_back_data', '' );            
  }
}

function ecpm_ddc_deactivate() {                                   
  update_option( 'ecpm_ddc_freq', 'manual' );
  wp_clear_scheduled_hook('ecpm_faster_with_stats');
  
  $ecpm_ddc_move_back_data = get_option('ecpm_ddc_move_back_data');
  $ecpm_ddc_remove_data = get_option('ecpm_ddc_remove_data');
  
  if ($ecpm_ddc_move_back_data == 'on')
    $moved = ecpm_get_data('moveback');
    
  if ($ecpm_ddc_remove_data == 'on') {
     delete_option( 'ecpm_ddc_installed' );
     delete_option( 'ecpm_ddc_leave_days' );
     delete_option( 'ecpm_ddc_record_threshold' );
     delete_option( 'ecpm_ddc_freq' );
     delete_option( 'ecpm_ddc_installed' );
     delete_option( 'ecpm_ddc_remove_data' );
     delete_option( 'ecpm_ddc_move_back_data' );
     delete_option( 'ecpm_ddc_max_time' );
     delete_option( 'ecpm_ddc_min_time' );
     delete_option( 'ecpm_ddc_avg_hits' );
     
     global $table_prefix, $wpdb;
     $wpdb->query("DROP TABLE IF EXISTS ".$wpdb->prefix."ecpm_ddc");
     $wpdb->query("DROP TABLE IF EXISTS ".$wpdb->prefix."ecpm_ddc_speed");
  }  
}

function ecpm_ddc_plugins_loaded() {
	$dir = dirname(plugin_basename(__FILE__)).DIRECTORY_SEPARATOR.'languages'.DIRECTORY_SEPARATOR;
	load_plugin_textdomain(ECPM_DDC, false, $dir);
}


function ecpm_ddc_enqueuescripts()	{
  wp_enqueue_style('ecpm_ddc_style', plugins_url('ecpm-ddc.css', __FILE__));
}


function ecpm_get_data( $action = 'count' ) {
  global $wpdb;
    switch ( $action ) {
      case 'count':
        $return_count = count_daily($wpdb);
        break;

      case 'move':
        $ecpm_ddc_record_threshold = get_option('ecpm_ddc_record_threshold');
        $count = count_daily($wpdb);
   
        //if ( $count >= $ecpm_ddc_record_threshold  && $count > 0 ) {
          $return_count = move_daily($wpdb);
        //} else
        //  $return_count = 0;
        break;
      
      case 'moveback':
        $return_count = move_data_back($wpdb);
        break;  
    }
    return $return_count;
}

function get_app_daily_table($wpdb){
  switch (APP_TD) {
    case 'classipress':
      return $wpdb->cp_ad_pop_daily;
      break;
    case 'clipper':
      return $wpdb->clpr_pop_daily;
      break;
    case 'jobroller':
      return $wpdb->app_pop_daily;
      break;
    default:
      return false;
  }
}

function time_daily_table($wpdb) {
  $app_daily_table = get_app_daily_table($wpdb);

  $ecpm_ddc_max_time = get_option('ecpm_ddc_max_time');
  $ecpm_ddc_min_time = get_option('ecpm_ddc_min_time');
  
  $ad_ids = $wpdb->get_results( "SELECT ID FROM $wpdb->posts p WHERE p.post_type = '".APP_POST_TYPE."' and p.post_status = 'publish' ORDER BY RAND() LIMIT 30;" );
  $today_date = date( 'Y-m-d', current_time( 'timestamp' ) );

// time max value
  $start_time = microtime();
  
  foreach ( $ad_ids as $ad_id ) {
    $result = $wpdb->get_var( "SELECT postcount FROM ".$wpdb->prefix."ecpm_ddc WHERE postnum = $ad_id->ID and time = $today_date" );
  } 
  $exec_time = microtime() - $start_time;
  
  if ($exec_time > $ecpm_ddc_max_time) 
    update_option( 'ecpm_ddc_max_time', $exec_time );
  
  
// time min value
  $start_time = microtime();
  
  foreach ( $ad_ids as $ad_id ) {
    $result = $wpdb->get_var( "SELECT postcount FROM $app_daily_table WHERE postnum = $ad_id->ID and time < $today_date" );
  } 
  $exec_time = microtime() - $start_time;
  
  if ($exec_time < $ecpm_ddc_min_time || !$ecpm_ddc_min_time)
    update_option( 'ecpm_ddc_min_time', $exec_time );

// average hits
  //$time_to = appthemes_mysql_date( current_time( 'mysql' ) );
  //$time_from = appthemes_mysql_date( current_time( 'mysql' ), -90 );
  $sql = "SELECT AVG(total) AS tot FROM ( SELECT SUM(postcount) as total, time FROM ".$wpdb->prefix."ecpm_ddc WHERE time >= DATE_ADD(CURDATE(), INTERVAL -90 DAY) and time < CURDATE() GROUP BY DATE(time) ) sumtotal";
  $result = $wpdb->get_var( $sql );
  update_option( 'ecpm_ddc_avg_hits', round($result,0) );
}

function count_daily($wpdb) {
  $app_daily_table = get_app_daily_table($wpdb);
  
  $result = $wpdb->get_var( "SELECT COUNT(*) FROM $app_daily_table" );
  return $result;
}

function move_daily($wpdb) {
  $app_daily_table = get_app_daily_table($wpdb);
  $ecpm_ddc_leave_days = get_option('ecpm_ddc_leave_days');
  
  // copy data to plugin table for statistics
  $wpdb->query("INSERT INTO ".$wpdb->prefix."ecpm_ddc SELECT * FROM $app_daily_table adt WHERE adt.time <= DATE_ADD(CURDATE(), INTERVAL -$ecpm_ddc_leave_days DAY)");
  
  // delete data from original table
  $result = $wpdb->query("DELETE FROM $app_daily_table WHERE time <= DATE_ADD(CURDATE(), INTERVAL -$ecpm_ddc_leave_days DAY)");
  
  time_daily_table($wpdb);
  
  // Save time gained
  $time_gained = get_time_gained();
  $wpdb->query("INSERT IGNORE INTO ".$wpdb->prefix."ecpm_ddc_speed (day, time_gained) VALUES(CURDATE(), '$time_gained')");
  
  return $result;
}

function get_time_gained(){
  $time_diff = get_option('ecpm_ddc_max_time') - get_option('ecpm_ddc_min_time');
  $ecpm_ddc_avg_hits = get_option('ecpm_ddc_avg_hits');
  $ecpm_ddc_avg_time_saved = $ecpm_ddc_avg_hits * $time_diff;
  $sec = intval($ecpm_ddc_avg_time_saved);

  return strftime('%T', mktime(0, 0, $sec));
}

function move_data_back($wpdb) {
  $app_daily_table = get_app_daily_table($wpdb);
  // copy data to original table
  $wpdb->query("INSERT INTO $app_daily_table SELECT * FROM ".$wpdb->prefix."ecpm_ddc");
  
  // delete data from ecpm table
  $result = $wpdb->query("DELETE FROM ".$wpdb->prefix."ecpm_ddc");
  
  return $result;
}

function ddc_table_create () {
  global $wpdb;

  $table_name = $wpdb->prefix . "ecpm_ddc";
  $charset_collate = $wpdb->get_charset_collate();
  require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

  $sql = "CREATE TABLE IF NOT EXISTS $table_name (
          id mediumint(9) NOT NULL,
          time date NOT NULL DEFAULT '0000-00-00',
          postnum int(11) NOT NULL,
          postcount int(11) NOT NULL DEFAULT '0',
          PRIMARY KEY (id)
        ) $charset_collate;";
  
  dbDelta( $sql );
//
  $table_name = $wpdb->prefix . "ecpm_ddc_speed";
  $sql = "CREATE TABLE IF NOT EXISTS $table_name (
          day date NOT NULL DEFAULT '0000-00-00',
          time_gained time NOT NULL DEFAULT '00:00:00',
          UNIQUE KEY (day)
        ) $charset_collate;";
  
  dbDelta( $sql );  
  
}

function ecpm_ddc_event() {
  if ( ddc_is_pro() ){
    //$ecpm_connect 
    ecpm_get_data('move');
    //echo $ecpm_connect;
  } else {
    ecpm_ddc_deactivate();
  }
}

function prepare_array_time($in_array) {
  $out_array = array();
  
 	foreach ( (array) $in_array as $value ) {
		$the_day = date( 'Y-m-d', strtotime( $value->time ) );
    $out_array[ $the_day ] = hoursToMinutes($value->total);
	}
  $last_val = 0;
	for ( $i = 90; $i > 0; $i-- ) {
		$each_day = date( 'Y-m-d', strtotime( '-' . $i . ' days' ) );
		if ( ! in_array( $each_day, array_keys( $out_array ) ) ) {
			$out_array[ $each_day ] = $last_val;
		}
    $last_val = $out_array[ $each_day ];
	}
  
  ksort( $out_array );
  
  return $out_array;
}

// Transform hours like "1:45" into the total number of minutes, "105".
function hoursToMinutes($hours)
{
    $minutes = 0;
    if (strpos($hours, ':') !== false)
    {
        // Split hours and minutes.
        list($hours, $minutes, $seconds) = explode(':', $hours);
    }

    return $hours * 60 + $minutes + round(($seconds / 60), 2);
} 

function ecpm_ddc_create_menu_set() {
    add_options_page('Faster with Stats','Faster with Stats','manage_options', 'ecpm_ddc_settings_page','ecpm_ddc_settings_page_callback');;
}    
  
function ecpm_ddc_settings_page_callback() {
?>
	<div class="wrap">
	<?php
	
	if( isset( $_POST['ecpm_ddc_submit'] ) )
	{
		
    if ( !isset($_POST[ 'ecpm_ddc_remove_data' ]) )
      $ecpm_ddc_remove_data = '';
    else
      $ecpm_ddc_remove_data = $_POST[ 'ecpm_ddc_remove_data' ];
      
    if ( !isset($_POST[ 'ecpm_ddc_move_back_data' ]) )
      $ecpm_ddc_move_back_data = '';
    else
      $ecpm_ddc_move_back_data = $_POST[ 'ecpm_ddc_move_back_data' ];
    

    if ( !isset($_POST[ 'ecpm_ddc_leave_days' ]) )
      $ecpm_ddc_leave_days = '';
    else {
      $ecpm_ddc_leave_days = $_POST[ 'ecpm_ddc_leave_days' ];
      if ( $ecpm_ddc_leave_days  < 1 ) {
        $ecpm_ddc_leave_days = 1;
      }
    }   
    
    if ( !isset($_POST[ 'ecpm_ddc_record_threshold' ]) )
      $ecpm_ddc_record_threshold = '';
    else
      $ecpm_ddc_record_threshold = $_POST[ 'ecpm_ddc_record_threshold' ];  
      
    if ( !isset($_POST[ 'ecpm_ddc_freq' ]) )
      $ecpm_ddc_freq = '';
    else {
      $ecpm_ddc_freq = $_POST[ 'ecpm_ddc_freq' ];
      
      if ( $ecpm_ddc_freq == 'manual' || !ddc_is_pro() ) {
        wp_clear_scheduled_hook('ecpm_faster_with_stats');
        update_option( 'ecpm_ddc_freq' , 'manual' );
      } else {
          wp_schedule_event( current_time( 'timestamp' ), $ecpm_ddc_freq, 'ecpm_faster_with_stats');
          update_option( 'ecpm_ddc_freq' , $ecpm_ddc_freq );
      }
    }  
      
    update_option( 'ecpm_ddc_leave_days' , $ecpm_ddc_leave_days );
    //update_option( 'ecpm_ddc_freq' , $ecpm_ddc_freq );
    update_option( 'ecpm_ddc_record_threshold' , $ecpm_ddc_record_threshold );
    update_option( 'ecpm_ddc_move_back_data', $ecpm_ddc_move_back_data );
    update_option( 'ecpm_ddc_remove_data', $ecpm_ddc_remove_data );

    ?>
        <div id="message" class="updated">
            <p><strong><?php _e('Settings saved.') ?></strong></p>
        </div>
    <?php  
	}
  
  if( isset($_POST['ecpm_ddc_submit_move']) ) { 
    $moved = ecpm_get_data('move');
  ?>
      <div id="message" class="updated">
          <p><strong><?php echo sprintf( __('Records moved: %s', ECPM_DDC ), $moved ) ?></strong></p>
      </div>
  <?php  
  }
	
  ?>
		<div id="ddcsetting">
			<h2><?php echo _e('Faster with Stats', ECPM_DDC); ?></h2>
      <?php
      $active_tab = isset( $_GET[ 'tab' ] ) ? $_GET[ 'tab' ] : 'ddc_speed';
      ?>
      <h2 class="nav-tab-wrapper">
          <a href="?page=ecpm_ddc_settings_page&tab=ddc_speed" class="nav-tab <?php echo $active_tab == 'ddc_speed' ? 'nav-tab-active' : ''; ?>"><?php echo _e('Speed', ECPM_DDC); ?></a>
          <a href="?page=ecpm_ddc_settings_page&tab=ddc_hits" class="nav-tab <?php echo $active_tab == 'ddc_hits' ? 'nav-tab-active' : ''; ?>"><?php echo _e('Hits', ECPM_DDC); ?></a>
          <a href="?page=ecpm_ddc_settings_page&tab=ddc_posts" class="nav-tab <?php echo $active_tab == 'ddc_posts' ? 'nav-tab-active' : ''; ?>"><?php echo _e('Posts', ECPM_DDC); ?></a>
          <a href="?page=ecpm_ddc_settings_page&tab=ddc_users" class="nav-tab <?php echo $active_tab == 'ddc_users' ? 'nav-tab-active' : ''; ?>"><?php echo _e('Users', ECPM_DDC); ?></a>
          <a href="?page=ecpm_ddc_settings_page&tab=ddc_options" class="nav-tab <?php echo $active_tab == 'ddc_options' ? 'nav-tab-active' : ''; ?>"><?php echo _e('Options', ECPM_DDC); ?></a>
      </h2>
       
      <?php require_once( WP_PLUGIN_DIR . DDC_NAME . '/'.str_replace('_', '-', $active_tab).'.php' );?>

		</div>
	</div>
<?php
}
  
function show_move_form() {
?>
<form id='ddcmoveform' method="post" action="">
    <h3><?php echo sprintf( __( 'Records currently in the table: %s', ECPM_DDC ), '<font size=+2>'.ecpm_get_data('count').'</font>' );?></h3>
    <input type="submit" id="ecpm_ddc_submit_move" name="ecpm_ddc_submit_move" class="button-primary" value="<?php _e('Optimize table now', ECPM_DDC); ?>" />
  </form>
<?php  
}

function buy_pro_screenshot() {
?>
<p align="center"><img src="<?php echo plugins_url( 'images/screenshot-pro-'.$_GET[ 'tab' ].'.png', __FILE__ );?>"></p>
<h3 align="center"><font color="darkred">This is not live data from your site!</font></h3>
<h3 align="center">To see real statistics data,<br>please purchase a PRO version of this plugin.</h3>
<p align="center">
  <a href="http://www.easycpmods.com/plugin-faster-with-stats/" target="_blank"><img src="<?php echo plugins_url( 'images/pay-pal-paynow-button.png', __FILE__ );?>" border="0"></a>
</p>
<?php 
}
?>