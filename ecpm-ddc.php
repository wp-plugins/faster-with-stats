<?php
/*
Plugin Name: Faster with Stats
Plugin URI: http://www.easycpmods.com
Description: Faster with Stats is a lightweight plugin that will speed up your AppThemes installation by moving daily statistics data to a plugin table. Why? Because a large table with daily counters will make your site very slow. It works with <strong>Classipress, Jobroller</strong> and <strong>Clipper</strong>.
Author: Easy CP Mods
Version: 1.1.0
Author URI: http://www.easycpmods.com
*/

define('ECPM_DDC', 'ecpm-ddc');
define('DDC_NAME', '/faster-with-stats');
define('DDC_DB_VER', '1.2');

register_activation_hook( __FILE__, 'ecpm_ddc_activate');
register_deactivation_hook( __FILE__, 'ecpm_ddc_deactivate');
register_uninstall_hook( __FILE__, 'ecpm_ddc_uninstall');

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
			wp_die( "<strong>".$plugin_data['Name']."</strong> requires a AppThemes theme to be installed. Your WordPress installation does not appear to have that installed. The plugin has been deactivated!<br />If this is a mistake, please contact plugin developer!<br /><br />Back to the WordPress <a href='".get_admin_url(null, 'plugins.php')."'>Plugins page</a>." );
		}
	}
  
  $ddc_db_ver = get_option('ecpm_ddc_db_ver');
  if ( DDC_DB_VER != $ddc_db_ver ) {
    ddc_table_create();
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
    update_option( 'ecpm_ddc_db_ver', DDC_DB_VER );            
  }
}

function ecpm_ddc_deactivate() {                                   
  update_option( 'ecpm_ddc_freq', 'manual' );
  wp_clear_scheduled_hook('ecpm_faster_with_stats');
}

function ecpm_ddc_uninstall() {
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
     delete_option( 'ecpm_ddc_max_time_tot' );
     delete_option( 'ecpm_ddc_min_time_tot' );
     delete_option( 'ecpm_ddc_avg_hits' );
     delete_option( 'ecpm_ddc_db_ver' );
     
     global $table_prefix, $wpdb;
     $wpdb->query("DROP TABLE IF EXISTS ".$wpdb->prefix."ecpm_ddc");
     $wpdb->query("DROP TABLE IF EXISTS ".$wpdb->prefix."ecpm_ddc_total");
     $wpdb->query("DROP TABLE IF EXISTS ".$wpdb->prefix."ecpm_ddc_speed");
     $wpdb->query("DROP TABLE IF EXISTS ".$wpdb->prefix."ecpm_ddc_speed_total");
     $wpdb->query("DROP TABLE IF EXISTS ".$wpdb->prefix."ecpm_ddc_dead_users");
  }
}

function ecpm_ddc_plugins_loaded() {
	$dir = dirname(plugin_basename(__FILE__)).DIRECTORY_SEPARATOR.'languages'.DIRECTORY_SEPARATOR;
	load_plugin_textdomain(ECPM_DDC, false, $dir);
}


function ecpm_ddc_enqueuescripts()	{
  wp_enqueue_style('ecpm_ddc_style', plugins_url('ecpm-ddc.css', __FILE__));
}


function ecpm_get_data( $action = 'count_ddc', $table_type = 'daily' ) {
  global $wpdb;
    switch ( $action ) {
      case 'count_app':
        if ( $table_type == 'daily' )
          $return_count = count_daily($wpdb, 'app');
        else
          $return_count = count_total($wpdb, 'app');  
        break;

      case 'count_ddc':
        if ( $table_type == 'daily' )
          $return_count = count_daily($wpdb, 'ddc');
        else  
          $return_count = count_total($wpdb, 'ddc');
        break;

      case 'move':
        if ( $table_type == 'daily' )
          $return_count = move_daily($wpdb);
        else  
          $return_count = move_total($wpdb);
        break;
      
      case 'moveback':
        move_data_back($wpdb);
        $return_count = 0;
        break;  
    }
    return $return_count;
}

function get_app_table($wpdb, $table_type = 'daily'){
  switch (APP_TD) {
    case 'classipress':
      if ( $table_type == 'daily' )
        return $wpdb->cp_ad_pop_daily;
      else  
        return $wpdb->cp_ad_pop_total;
      break;
    case 'clipper':
      if ( $table_type == 'daily' )
        return $wpdb->clpr_pop_daily;
      else
        return $wpdb->clpr_pop_total;
      break;
    case 'jobroller':
      if ( $table_type == 'daily' )
        return $wpdb->app_pop_daily;
      else
        return $wpdb->app_pop_total;  
      break;
    default:
      return false;
  }
}

function time_daily_table($wpdb) {
  $app_daily_table = get_app_table($wpdb, 'daily');

  $ecpm_ddc_max_time = get_option('ecpm_ddc_max_time');
  $ecpm_ddc_min_time = get_option('ecpm_ddc_min_time');
  
  $ad_ids = $wpdb->get_results( "SELECT ID FROM $wpdb->posts p WHERE p.post_type = '".APP_POST_TYPE."' and p.post_status = 'publish' ORDER BY RAND() LIMIT 30;" );
  $today_date = date( 'Y-m-d', current_time( 'timestamp' ) );

// time max value
  $start_time = microtime(true);
  
  foreach ( $ad_ids as $ad_id ) {
    $result = $wpdb->get_var( "SELECT postcount FROM ".$wpdb->prefix."ecpm_ddc WHERE postnum = $ad_id->ID and time = $today_date" );
  } 
  $stop_time = microtime(true);
  if ( $stop_time > $start_time ) {
    $exec_max_time = $stop_time - $start_time;
    //if ($exec_max_time > $ecpm_ddc_max_time) 
      update_option( 'ecpm_ddc_max_time', $exec_max_time );
  }
  
// time min value
  $start_time = microtime(true);
  
  foreach ( $ad_ids as $ad_id ) {
    $result = $wpdb->get_var( "SELECT postcount FROM $app_daily_table WHERE postnum = $ad_id->ID and time < $today_date" );
  } 
  $stop_time = microtime(true);
  
  if ( $stop_time > $start_time ) {
    $exec_min_time = $stop_time - $start_time;
    if ( $exec_min_time > $exec_max_time ) 
      $exec_min_time = 0;
  
    //if ($exec_min_time < $ecpm_ddc_min_time || !$ecpm_ddc_min_time || $ecpm_ddc_min_time <= 0 )
      update_option( 'ecpm_ddc_min_time', $exec_min_time );
  }

// average hits
  $sql = "SELECT AVG(total) AS tot FROM ( SELECT SUM(postcount) as total, time FROM ".$wpdb->prefix."ecpm_ddc WHERE time >= DATE_ADD(CURDATE(), INTERVAL -90 DAY) and time < CURDATE() GROUP BY DATE(time) ) sumtotal";
  $result = $wpdb->get_var( $sql );
  update_option( 'ecpm_ddc_avg_hits', round($result,0) );
  
}

function time_total_table($wpdb) {
  $app_total_table = get_app_table($wpdb, 'total');

  $ecpm_ddc_max_time = get_option('ecpm_ddc_max_time_tot');
  $ecpm_ddc_min_time = get_option('ecpm_ddc_min_time_tot');
  
  $ad_ids = $wpdb->get_results( "SELECT ID FROM $wpdb->posts p WHERE p.post_type = '".APP_POST_TYPE."' and p.post_status = 'publish' ORDER BY RAND() LIMIT 30;" );
  $today_date = date( 'Y-m-d', current_time( 'timestamp' ) );

// time max value
  $start_time = microtime(true);
  
  foreach ( $ad_ids as $ad_id ) {
    $result = $wpdb->get_var( "SELECT postcount FROM ".$wpdb->prefix."ecpm_ddc_total WHERE postnum = $ad_id->ID" );
  } 
  $stop_time = microtime(true);
  if ( $stop_time > $start_time ) {
    $exec_max_time = $stop_time - $start_time;
    //if ($exec_max_time > $ecpm_ddc_max_time) 
      update_option( 'ecpm_ddc_max_time_tot', $exec_max_time );
  }
  
// time min value
  $start_time = microtime(true);
  
  foreach ( $ad_ids as $ad_id ) {
    $result = $wpdb->get_var( "SELECT postcount FROM $app_total_table WHERE postnum = $ad_id->ID" );
  } 
  $stop_time = microtime(true);
  
  if ( $stop_time > $start_time ) {
    $exec_min_time = $stop_time - $start_time;
    if ( $exec_min_time > $exec_max_time ) 
      $exec_min_time = 0;
  
    //if ($exec_min_time < $ecpm_ddc_min_time || !$ecpm_ddc_min_time || $ecpm_ddc_min_time <= 0 )
      update_option( 'ecpm_ddc_min_time_tot', $exec_min_time );
  }
}

function count_daily($wpdb, $mytable = 'app') {
  switch ($mytable){
    case 'ddc':
      $app_daily_table = $wpdb->prefix."ecpm_ddc";
      break;
    case 'app':
      $app_daily_table = get_app_table($wpdb, 'daily');
      break;
  }
    
  $result = $wpdb->get_var( "SELECT COUNT(*) FROM $app_daily_table" );
  return $result;
}

function count_total($wpdb, $mytable = 'app') {
  switch ($mytable){
    case 'ddc':
      $app_total_table = $wpdb->prefix."ecpm_ddc_total";
      $result = $wpdb->get_var( "SELECT COUNT(*) FROM $app_total_table" );
      break;
    case 'app':
      $app_total_table = get_app_table($wpdb, 'total');
      $result = $wpdb->get_var( "SELECT COUNT(*) FROM $app_total_table WHERE postnum NOT IN(SELECT ID FROM $wpdb->posts)" );
      break;
  }
  
  return $result;
}

function move_daily($wpdb) {
  $app_daily_table = get_app_table($wpdb, 'daily');
  $ecpm_ddc_leave_days = get_option('ecpm_ddc_leave_days');
  
  // copy data to plugin table for statistics
  $wpdb->query("INSERT INTO ".$wpdb->prefix."ecpm_ddc SELECT * FROM $app_daily_table adt WHERE adt.time <= DATE_ADD(CURDATE(), INTERVAL -$ecpm_ddc_leave_days DAY)");
  
  // delete data from original table
  $result = $wpdb->query("DELETE FROM $app_daily_table WHERE time <= DATE_ADD(CURDATE(), INTERVAL -$ecpm_ddc_leave_days DAY)");
  
  time_daily_table($wpdb);
  
  // Save time gained
  $daily_time_diff = ( get_option('ecpm_ddc_max_time') - get_option('ecpm_ddc_min_time') ) * get_option('ecpm_ddc_avg_hits');
  $daily_time_diff = strftime('%T', mktime(0, 0, intval($daily_time_diff)));
  $wpdb->query("INSERT IGNORE INTO ".$wpdb->prefix."ecpm_ddc_speed (day, time_gained) VALUES(CURDATE(), '$daily_time_diff')");
  
  // Save dead users
  if ( ddc_is_pro() ) {
    $dead_users = count_dead_users($wpdb);
    $wpdb->query("INSERT IGNORE INTO ".$wpdb->prefix."ecpm_ddc_dead_users (day, dead_users) VALUES(CURDATE(), '$dead_users')");
  }

  return $result;
}

function move_total($wpdb) {
  $app_total_table = get_app_table($wpdb, 'total');
  
  // copy data to plugin table for statistics
  $wpdb->query("INSERT INTO ".$wpdb->prefix."ecpm_ddc_total SELECT * FROM $app_total_table att WHERE att.postnum NOT IN(SELECT ID FROM $wpdb->posts)");
  
  // delete data from original table
  $result = $wpdb->query("DELETE FROM $app_total_table WHERE postnum NOT IN(SELECT ID FROM $wpdb->posts)");
  
  time_total_table($wpdb);
  
  // Save time gained
  $total_time_diff = ( get_option('ecpm_ddc_max_time_tot') - get_option('ecpm_ddc_min_time_tot') ) * get_option('ecpm_ddc_avg_hits');
  $total_time_diff = strftime('%T', mktime(0, 0, intval($total_time_diff)));
  $wpdb->query("INSERT IGNORE INTO ".$wpdb->prefix."ecpm_ddc_speed_total (day, time_gained) VALUES(CURDATE(), '$total_time_diff')");
  
  return $result;
}

function get_time_gained(){
  global $wpdb;
  
	$ecpm_ddc_avg_hits = get_option('ecpm_ddc_avg_hits');
  
  $sql = "SELECT AVG(TIME_TO_SEC(time_gained)) FROM ".$wpdb->prefix."ecpm_ddc_speed WHERE day > DATE_ADD(CURDATE(), INTERVAL -90 DAY)";
      
	$avg_time_diff = $wpdb->get_var( $sql );
  
  $sql = "SELECT AVG(TIME_TO_SEC(time_gained)) FROM ".$wpdb->prefix."ecpm_ddc_speed_total WHERE day > DATE_ADD(CURDATE(), INTERVAL -90 DAY)";
  
  $avg_time_diff_tot = $wpdb->get_var( $sql );
  
  $avg_time_diff += $avg_time_diff_tot;
  
  if ( $ecpm_ddc_avg_hits > 0 )
    $ecpm_ddc_avg_time_user = $avg_time_diff / $ecpm_ddc_avg_hits;
  else  
    $ecpm_ddc_avg_time_user = 0;
  
  $return_time[0] = $ecpm_ddc_avg_time_user;
  $return_time[1] = strftime('%T', mktime(0, 0, intval($avg_time_diff)));
  return $return_time;
}

function move_data_back($wpdb) {
  $app_daily_table = get_app_table($wpdb, $table_type);
  
  // copy data to original table
  $wpdb->query("INSERT INTO $app_daily_table SELECT * FROM ".$wpdb->prefix."ecpm_ddc");
  $wpdb->query("INSERT INTO $app_total_table SELECT * FROM ".$wpdb->prefix."ecpm_ddc_total");
  
  // delete data from ecpm table
  $result = $wpdb->query("DELETE FROM ".$wpdb->prefix."ecpm_ddc");
  $result = $wpdb->query("DELETE FROM ".$wpdb->prefix."ecpm_ddc_total");
}

function ddc_table_create () {
  global $wpdb;
  
  $charset_collate = $wpdb->get_charset_collate();
  require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

  $table_name = $wpdb->prefix . "ecpm_ddc";
  $sql = "CREATE TABLE IF NOT EXISTS $table_name (
          id mediumint(9) NOT NULL,
          time date NOT NULL DEFAULT '0000-00-00',
          postnum int(11) NOT NULL,
          postcount int(11) NOT NULL DEFAULT '0',
          PRIMARY KEY (id)
        ) $charset_collate;";
  
  dbDelta( $sql );
//
  $table_name = $wpdb->prefix . "ecpm_ddc_total";
  $sql = "CREATE TABLE IF NOT EXISTS $table_name (
          id mediumint(9) NOT NULL,
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
//
  $table_name = $wpdb->prefix . "ecpm_ddc_speed_total";
  $sql = "CREATE TABLE IF NOT EXISTS $table_name (
          day date NOT NULL DEFAULT '0000-00-00',
          time_gained time NOT NULL DEFAULT '00:00:00',
          UNIQUE KEY (day)
        ) $charset_collate;";
  
  dbDelta( $sql );

// dead users
  $table_name = $wpdb->prefix . "ecpm_ddc_dead_users";
  $sql = "CREATE TABLE IF NOT EXISTS $table_name (
          day date NOT NULL DEFAULT '0000-00-00',
          dead_users int(11) NOT NULL DEFAULT '0',
          UNIQUE KEY (day)
        ) $charset_collate;";
  
  dbDelta( $sql );
  update_option( 'ecpm_ddc_db_ver', DDC_DB_VER );      
  
}

function ecpm_ddc_event() {
  if ( ddc_is_pro() ){
    ecpm_get_data('move', 'daily');
    ecpm_get_data('move', 'total');
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
  
  if( isset($_POST['ecpm_ddc_submit_move_daily']) )
    $moved = ecpm_get_data('move', 'daily');
  
  if( isset($_POST['ecpm_ddc_submit_move_total']) ) 
    $moved = ecpm_get_data('move', 'total');

  if( isset($_POST['ecpm_ddc_submit_move_daily']) || isset($_POST['ecpm_ddc_submit_move_total']) ) {
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
  $count_ddc['daily'] = ecpm_get_data('count_ddc', 'daily');
  $count_app['daily'] = ecpm_get_data('count_app', 'daily');
  $app_class['daily'] = $ddc_class['daily'] = 'ddc_neutral';
  if ( $count_ddc['daily'] > $count_app['daily'])
    $ddc_class['daily'] = 'ddc_green';
  
  if ( $count_app['daily'] >= 2000 )
    $app_class['daily'] = 'ddc_red';
  elseif ($count_app['daily'] <= 500)
    $app_class['daily'] = 'ddc_green';
  elseif ($count_app['daily'] < 2000)
    $app_class['daily'] = 'ddc_orange';
//    
  $count_ddc['total'] = ecpm_get_data('count_ddc', 'total');
  $count_app['total'] = ecpm_get_data('count_app', 'total');
  $app_class['total'] = $ddc_class['total'] = 'ddc_neutral';
  
  if ( $count_app['total'] >= 100 )
    $app_class['total'] = 'ddc_red';
  elseif ($count_app['total'] == 0)
    $app_class['total'] = 'ddc_green';
  elseif ($count_app['total'] < 100)
    $app_class['total'] = 'ddc_orange';  
      
        
?>
<table width="100%" cellspacing="8" cellpadding="10" id="counters_table"><tr>
<td width="50%" align="center">
  <form id='ddcmoveform_daily' method="post" action="">
    <h3><?php echo _e( 'Daily counters table', ECPM_DDC );?></h3>
    <strong><?php echo sprintf( __( "Records in plugin's table: %s", ECPM_DDC ), "<font class=".$ddc_class['daily'].">".number_format_i18n( $count_ddc['daily'], 0)."</font>" );?></strong><br>
    <strong><?php echo sprintf( __( "Records in theme's table: %s", ECPM_DDC ), "<font class=".$app_class['daily'].">".number_format_i18n( $count_app['daily'], 0)."</font>" );?></strong><br><br>
    <input type="submit" id="ecpm_ddc_submit_move_daily" name="ecpm_ddc_submit_move_daily" class="button-primary" value="<?php _e('Optimize table now', ECPM_DDC); ?>" />
  </form>
</td><td width="50%" align="center">  
  <form id='ddcmoveform_total' method="post" action="">
    <h3><?php echo _e( 'Total counters table', ECPM_DDC );?></h3>
    <strong><?php echo sprintf( __( "Records in plugin's table: %s", ECPM_DDC ), "<font class=".$ddc_class['total'].">".number_format_i18n( $count_ddc['total'], 0)."</font>" );?></strong><br>
    <strong><?php echo sprintf( __( "Dead records in theme's table: %s", ECPM_DDC ), "<font class=".$app_class['total'].">".number_format_i18n( $count_app['total'], 0)."</font>" );?></strong><br><br>
    <input type="submit" id="ecpm_ddc_submit_move_total" name="ecpm_ddc_submit_move_total" class="button-primary" value="<?php _e('Optimize table now', ECPM_DDC); ?>" />
  </form>
</td>
</tr></table>  

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
<p align="center"><i>If you have already purchased a <a href="http://www.easycpmods.com/plugin-faster-with-stats/" target="_blank"><strong>PRO version</strong></a>, then please <a href="http://www.easycpmods.com/">visit your account</a> and download a newer version.</i><p>

<?php 
}
?>