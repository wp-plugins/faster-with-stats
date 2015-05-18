<?php

add_action('ecpm_faster_with_stats', 'ecpm_ddc_event');


function prepare_array($in_array) {
  $out_array = array();
  
 	foreach ( (array) $in_array as $value ) {
		$the_day = date( 'Y-m-d', strtotime( $value->time ) );
		$out_array[ $the_day ] = $value->total;
	}

	for ( $i = 1; $i < 90; $i++ ) {
		$each_day = date( 'Y-m-d', strtotime( '-' . $i . ' days' ) );
		if ( ! in_array( $each_day, array_keys( $out_array ) ) ) {
			$out_array[ $each_day ] = 0;
		}
	}
  
  ksort( $out_array );
  
  return $out_array;
}


function prepare_array_ads($in_array) {
  $out_array = $in_array;
  
	for ( $i = 1; $i < 90; $i++ ) {
		$each_day = date( 'Y-m-d', strtotime( '-' . $i . ' days' ) );
		if ( ! in_array( $each_day, array_keys( $out_array ) ) ) {
			$out_array[ $each_day ] = 0;
		}
	}
  
  ksort( $out_array );
  
  return $out_array;
}

/**
 * Counts the number of ad listings for the user.
 * Use only on admin Users page.
 *
 * @return array
 */
function ecpm_count_ads() {
	global $wpdb;

  $time_from = appthemes_mysql_date( current_time( 'mysql' ), -90 );
  $avail_statuses = "'publish', 'draft', 'pending'";                                                                      
  
  $sql = "SELECT ID, post_date, post_status FROM $wpdb->posts WHERE post_type = %s AND post_status IN ($avail_statuses) AND post_date > %s";

	$results = $wpdb->get_results( $wpdb->prepare( $sql, APP_POST_TYPE, $time_from ) );

  $expired_ads = array();
  $ended_ads = array();
  $offline_ads = array();
  $pending_pay_ads = array();
  $pending_mod_ads = array();
  $live_ads = array();
  
  foreach ( (array) $results as $ad ) {
    switch ( APP_TD ){
    case 'classipress':
      $ad_status = cp_get_listing_status_name($ad->ID);
      break;
    case 'clipper':
      $ad_status = get_clipper_post_status($ad->ID);
      break;
    default:
      $ad_status = $ad->post_status;
    }
    
    $ad_date = date( 'Y-m-d', strtotime( $ad->post_date ) );
    
    switch ($ad_status) {
      case 'live_expired':
         array_push($expired_ads, $ad_date);
      break;
      case 'ended':
        array_push($ended_ads, $ad_date);
      break;
      case 'offline':
      case 'draft':
        array_push($offline_ads, $ad_date);
      break;
      case 'pending_payment':
      case 'pending':
         array_push($pending_pay_ads, $ad_date);
      break;
      case 'pending_moderation':
        array_push($pending_mod_ads, $ad_date);
      break;
      case 'live':
      case 'publish':
        array_push($live_ads, $ad_date);
      break;
    }
  } 

  $sestevek = array_count_values($expired_ads);
  $expired_ads = prepare_array_ads($sestevek);
  
  $sestevek = array_count_values($ended_ads);
  $ended_ads = prepare_array_ads($sestevek);
  
  $sestevek = array_count_values($offline_ads);
  $offline_ads = prepare_array_ads($sestevek);
  
  $sestevek = array_count_values($pending_pay_ads);
  $pending_pay_ads = prepare_array_ads($sestevek);
  
  $sestevek = array_count_values($pending_mod_ads);
  $pending_mod_ads = prepare_array_ads($sestevek);
  
  $sestevek = array_count_values($live_ads);
  $live_ads = prepare_array_ads($sestevek);

//  echo "print3:".print_r($expired_ads);
	
?>

<div id="placeholder"></div>

<script language="javascript" type="text/javascript">
// <![CDATA[
jQuery(function() {

  var live_ads = [
		<?php
		foreach ( $live_ads as $day => $value ) {
			$sdate = strtotime( $day );
			$sdate = $sdate * 1000; // js timestamps measure milliseconds vs seconds
			$newoutput = "[$sdate, $value],\n";
			echo $newoutput;
		}
		?>
	];
  
	var expired_ads = [
		<?php
		foreach ( $expired_ads as $day => $value ) {
			$sdate = strtotime( $day );
			$sdate = $sdate * 1000; // js timestamps measure milliseconds vs seconds
			$newoutput = "[$sdate, $value],\n";
			echo $newoutput;
		}
		?>
	];
  
  var ended_ads = [
		<?php
		foreach ( $ended_ads as $day => $value ) {
			$sdate = strtotime( $day );
			$sdate = $sdate * 1000; // js timestamps measure milliseconds vs seconds
			$newoutput = "[$sdate, $value],\n";
			echo $newoutput;
		}
		?>
	];
  
  var offline_ads = [
		<?php
		foreach ( $offline_ads as $day => $value ) {
			$sdate = strtotime( $day );
			$sdate = $sdate * 1000; // js timestamps measure milliseconds vs seconds
			$newoutput = "[$sdate, $value],\n";
			echo $newoutput;
		}
		?>
	];
  
  var pending_pay_ads = [
		<?php
		foreach ( $pending_pay_ads as $day => $value ) {
			$sdate = strtotime( $day );
			$sdate = $sdate * 1000; // js timestamps measure milliseconds vs seconds
			$newoutput = "[$sdate, $value],\n";
			echo $newoutput;
		}
		?>
	];
  
  var pending_mod_ads = [
		<?php
		foreach ( $pending_mod_ads as $day => $value ) {
			$sdate = strtotime( $day );
			$sdate = $sdate * 1000; // js timestamps measure milliseconds vs seconds
			$newoutput = "[$sdate, $value],\n";
			echo $newoutput;
		}
		?>
	];
  
	var placeholder = jQuery("#placeholder");

	var output = [
		{
			data: live_ads,
			label: "<?php _e( 'Live', APP_TD ); ?>",
			symbol: '',
      yaxis: 1
		}, 
    {
			data: expired_ads,
			label: "<?php _e( 'Live-Expired', APP_TD ); ?>",
      yaxis: 1
		},
    {
			data: ended_ads,
			label: "<?php _e( 'Ended', APP_TD ); ?>",
			symbol: '',
			yaxis: 1
		},
    {
			data: offline_ads,
			label: "<?php _e( 'Offline', APP_TD ); ?>",
			symbol: '',
      yaxis: 1
		},
    {
			data: pending_pay_ads,
			label: "<?php _e( 'Awaiting payment', APP_TD ); ?>",
			symbol: '',
      yaxis: 1
		},
    {
			data: pending_mod_ads,
			label: "<?php _e( 'Awaiting approval', APP_TD ); ?>",
			symbol: '',
      yaxis: 1
		}
	];

	var options = {
		series: {
			lines: { show: true },
			points: { show: true }
		},
		grid: {
			tickColor:'#f4f4f4',
			hoverable: true,
			clickable: true,
			borderColor: '#f4f4f4',
			backgroundColor:'#FFFFFF'
		},
		xaxis: {
			mode: 'time',
			timeformat: "%m/%d"
		},
		yaxis: {
			min: 0
		},
    y2axis: {
			min: 0
		},
		legend: {
			position: 'nw'
		}
	};

	jQuery.plot(placeholder, output, options);

	// reload the plot when browser window gets resized
	jQuery(window).resize(function() {
		jQuery.plot(placeholder, output, options);
	});  
                   
	function showChartTooltip(x, y, contents) {
		jQuery('<div id="charttooltip">' + contents + '</div>').css( {
			position: 'absolute',
			display: 'none',
			top: y + 5,
			left: x + 5,
			opacity: 1
		} ).appendTo("body").fadeIn(200);
	}

	var previousPoint = null;
	jQuery("#placeholder").bind("plothover", function (event, pos, item) {
		jQuery("#x").text(pos.x.toFixed(2));
		jQuery("#y").text(pos.y.toFixed(2));
		if (item) {
			if (previousPoint != item.datapoint) {
				previousPoint = item.datapoint;

				jQuery("#charttooltip").remove();
				var x = new Date(item.datapoint[0]), y = item.datapoint[1];
				var xday = x.getDate(), xmonth = x.getMonth()+1; // jan = 0 so we need to offset month
				showChartTooltip(item.pageX, item.pageY, xmonth + "/" + xday + " - <b>" + item.series.symbol + y + "</b> " + item.series.label);
			}
		} else {
			jQuery("#charttooltip").remove();
			previousPoint = null;
		}
	});
});
// ]]>
</script>


<?php
} 


function get_clipper_post_status($listing_id = 0) {
  $listing_id = $listing_id ? $listing_id : get_the_ID();
	$listing = get_post( $listing_id );
 
  if ( $listing->post_status == 'publish' || $listing->post_status == 'unreliable' ) {

		$post_status = 'live';								
	} elseif ( $listing->post_status == 'pending' ) {

		if ( clpr_have_pending_payment( $listing->ID ) ) {
			$post_status = 'pending_payment';
		} else {
			$post_status = 'pending';
		}

	} elseif ( $listing->post_status == 'draft' ) {

		$expire_date = clpr_get_expire_date( $listing->ID, 'time' ) + ( 24 * 3600 ); // + 24h, coupons expire in the end of day

		if ( current_time( 'timestamp' ) > $expire_date ) {
			$post_status = 'ended';
		} else {
			$post_status = 'offline';
		}

	} else {
		$post_status = '&mdash;';
	}
  
  return $post_status; 

}

function ecpm_count_users() {
  global $wpdb;
  $time_from = appthemes_mysql_date( current_time( 'mysql' ), -90 );
  $avail_statuses = "'publish', 'draft', 'pending'";
  
	$sql = "SELECT COUNT(p.id) as total, p.post_date as time FROM $wpdb->posts p LEFT JOIN $wpdb->users u ON p.post_author = u.ID WHERE post_type = %s AND post_status IN ($avail_statuses) AND post_date > %s GROUP BY DATE(post_date) DESC";
  $results = $wpdb->get_results( $wpdb->prepare( $sql, APP_POST_TYPE, $time_from ) );
	$maxads = prepare_array($results);
  
  $sql = "SELECT COUNT(u.ID) as total, u.user_registered as time FROM $wpdb->users u WHERE u.user_registered > %s GROUP BY DATE(user_registered) DESC";
	$results = $wpdb->get_results( $wpdb->prepare( $sql, $time_from ) );
	$regusers = prepare_array($results);

?>

<div id="placeholder"></div>

<script language="javascript" type="text/javascript">
// <![CDATA[
jQuery(function() {

	var maxads = [
		<?php
		foreach ( $maxads as $day => $value ) {
			$sdate = strtotime( $day );
			$sdate = $sdate * 1000; // js timestamps measure milliseconds vs seconds
			$newoutput = "[$sdate, $value],\n";
			echo $newoutput;
		}
		?>
	];
  
  var regusers = [
		<?php
		foreach ( $regusers as $day => $value ) {
			$sdate = strtotime( $day );
			$sdate = $sdate * 1000; // js timestamps measure milliseconds vs seconds
			$newoutput = "[$sdate, $value],\n";
			echo $newoutput;
		}
		?>
	];

	var placeholder = jQuery("#placeholder");

	var output = [
		{
			data: maxads,
			label: "<?php _e( 'Maximum no. of posts per user', ECPM_DDC ); ?>",
			symbol: ''
		},
    {
			data: regusers,
			label: "<?php _e( 'No. of registered users', ECPM_DDC ); ?>",
			symbol: '',
			yaxis: 2
		} 
	];

	var options = {
		series: {
			lines: { show: true },
			points: { show: true }
		},
		grid: {
			tickColor:'#f4f4f4',
			hoverable: true,
			clickable: true,
			borderColor: '#f4f4f4',
			backgroundColor:'#FFFFFF'
		},
		xaxis: {
			mode: 'time',
			timeformat: "%m/%d"
		},
		yaxis: {
			min: 0
		},
    y2axis: {
			min: 0
		},
		legend: {
			position: 'nw'
		}
	};

	jQuery.plot(placeholder, output, options);

	// reload the plot when browser window gets resized
	jQuery(window).resize(function() {
		jQuery.plot(placeholder, output, options);
	});

	function showChartTooltip(x, y, contents) {
		jQuery('<div id="charttooltip">' + contents + '</div>').css( {
			position: 'absolute',
			display: 'none',
			top: y + 5,
			left: x + 5,
			opacity: 1
		} ).appendTo("body").fadeIn(200);
	}

	var previousPoint = null;
	jQuery("#placeholder").bind("plothover", function (event, pos, item) {
		jQuery("#x").text(pos.x.toFixed(2));
		jQuery("#y").text(pos.y.toFixed(2));
		if (item) {
			if (previousPoint != item.datapoint) {
				previousPoint = item.datapoint;

				jQuery("#charttooltip").remove();
				var x = new Date(item.datapoint[0]), y = item.datapoint[1];
				var xday = x.getDate(), xmonth = x.getMonth()+1; // jan = 0 so we need to offset month
				showChartTooltip(item.pageX, item.pageY, xmonth + "/" + xday + " - <b>" + item.series.symbol + y + "</b> " + item.series.label);
			}
		} else {
			jQuery("#charttooltip").remove();
			previousPoint = null;
		}
	});
});
// ]]>
</script>


<?php
}

function ecpm_top_users() {
// max posts by users
// average posts by users
  global $wpdb;
  $avail_statuses = "'publish', 'draft', 'pending'";
  
	$sql = "SELECT count(p.id) as total, u.ID as userid, u.user_nicename, p.post_status
          FROM $wpdb->posts p 
          LEFT JOIN $wpdb->users u ON p.post_author = u.ID
          WHERE post_type = %s 
          AND post_status IN ($avail_statuses) 
          GROUP BY post_author, post_status 
          ORDER BY total DESC 
          LIMIT 20";
  $maxposts = $wpdb->get_results( $wpdb->prepare( $sql, APP_POST_TYPE ) );
	//$maxads = prepare_array($results);
//echo print_r($maxposts);
  //$sql = "SELECT AVERAGE(p.id) as total, p.post_author FROM $wpdb->posts p LEFT JOIN $wpdb->users u ON p.post_author = u.ID WHERE post_type = %s AND post_status IN ($avail_statuses) GROUP BY post_author ORDER BY total LIMIT 15";
	//$avgposts = $wpdb->get_results( $wpdb->prepare( $sql, APP_POST_TYPE ) );
	//$avgads = prepare_array($results);
  
  echo '<table id="ecpm_top_users">
        <tr><th>#</th>
        <th width="100px">'. __( 'User', ECPM_DDC ).'</th>
        <th width="80px"><strong>'. __( 'Total', ECPM_DDC ).'</strong></th>
        <th width="80px">'. __( 'Published', ECPM_DDC ).'</th>
        <th width="80px">'. __( 'Pending', ECPM_DDC ).'</th>
        <th width="80px">'. __( 'Draft', ECPM_DDC ).'</th></tr>';
  
  $cntr = 0;
  foreach ( $maxposts as $post ) {
    $cntr++;
    if ( !isset($last_user) ) {
      $last_user = $post->userid;
      $curr_post = array('user' => '', 'total' => '', 'publish' => 0, 'pending' => 0, 'draft' => 0);
    }

    $curr_user = $post->userid;
    
    if ( $curr_user != $last_user ) {
      output_line($curr_post, $cntr);
      $curr_post = array('user' => '', 'total' => '', 'publish' => 0, 'pending' => 0, 'draft' => 0);
    }
    
    $curr_post['user'] = $post->user_nicename;
    $curr_post['total'] += $post->total; 
    $curr_post[$post->post_status] = $post->total;
    
    $last_user = $post->userid;

  }
  output_line($curr_post, $cntr);

  echo '</table>';
}

function output_line($curr_post, $cntr){
  echo '<tr align="center">';
  echo '<td>'.$cntr.'</td><td align="left">'.$curr_post['user'].'</td><td><strong>'.$curr_post['total'].'</strong></td><td>'.$curr_post['publish'].'</td><td>'.$curr_post['pending'].'</td><td>'.$curr_post['draft'].'</td>';
  echo '</tr>';
}


function ecpm_show_hits() {
	global $wpdb;
  $time_from = appthemes_mysql_date( current_time( 'mysql' ), -90 );
  
	$sql = "SELECT SUM(postcount) as total, time FROM ".$wpdb->prefix."ecpm_ddc WHERE time > %s GROUP BY DATE(time) DESC";
	$results = $wpdb->get_results( $wpdb->prepare( $sql, $time_from ) );
	$hits = prepare_array($results);
  
  $sql = "SELECT DISTINCT COUNT(id) as total, time FROM ".$wpdb->prefix."ecpm_ddc WHERE time > %s GROUP BY DATE(time) DESC";
	$results = $wpdb->get_results( $wpdb->prepare( $sql, $time_from ) );
	$seenads = prepare_array($results);

?>

<div id="placeholder"></div>

<script language="javascript" type="text/javascript">
// <![CDATA[
jQuery(function() {

	var hits = [
		<?php
		foreach ( $hits as $day => $value ) {
			$sdate = strtotime( $day );
			$sdate = $sdate * 1000; // js timestamps measure milliseconds vs seconds
			$newoutput = "[$sdate, $value],\n";
			echo $newoutput;
		}
		?>
	];
  
  var seenads = [
		<?php
		foreach ( $seenads as $day => $value ) {
			$sdate = strtotime( $day );
			$sdate = $sdate * 1000; // js timestamps measure milliseconds vs seconds
			$newoutput = "[$sdate, $value],\n";
			echo $newoutput;
		}
		?>
	]; 

	var placeholder = jQuery("#placeholder");

	var output = [
		{
			data: hits,
			label: "<?php _e( 'Total daily ad views', ECPM_DDC ); ?>",
			symbol: ''
		},
    {
			data: seenads,
			label: "<?php _e( 'Number of different ads seen', ECPM_DDC ); ?>",
			symbol: '',
			yaxis: 2
		} 
	];

	var options = {
		series: {
			lines: { show: true },
			points: { show: true }
		},
		grid: {
			tickColor:'#f4f4f4',
			hoverable: true,
			clickable: true,
			borderColor: '#f4f4f4',
			backgroundColor:'#FFFFFF'
		},
		xaxis: {
			mode: 'time',
			timeformat: "%m/%d"
		},
		yaxis: {
			min: 0
		},
    y2axis: {
			min: 0
		},
		legend: {
			position: 'nw'
		}
	};

	jQuery.plot(placeholder, output, options);

	// reload the plot when browser window gets resized
	jQuery(window).resize(function() {
		jQuery.plot(placeholder, output, options);
	});

	function showChartTooltip(x, y, contents) {
		jQuery('<div id="charttooltip">' + contents + '</div>').css( {
			position: 'absolute',
			display: 'none',
			top: y + 5,
			left: x + 5,
			opacity: 1
		} ).appendTo("body").fadeIn(200);
	}

	var previousPoint = null;
	jQuery("#placeholder").bind("plothover", function (event, pos, item) {
		jQuery("#x").text(pos.x.toFixed(2));
		jQuery("#y").text(pos.y.toFixed(2));
		if (item) {
			if (previousPoint != item.datapoint) {
				previousPoint = item.datapoint;

				jQuery("#charttooltip").remove();
				var x = new Date(item.datapoint[0]), y = item.datapoint[1];
				var xday = x.getDate(), xmonth = x.getMonth()+1; // jan = 0 so we need to offset month
				showChartTooltip(item.pageX, item.pageY, xmonth + "/" + xday + " - <b>" + item.series.symbol + y + "</b> " + item.series.label);
			}
		} else {
			jQuery("#charttooltip").remove();
			previousPoint = null;
		}
	});
});
// ]]>
</script>


<?php
}
?>