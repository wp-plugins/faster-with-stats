  <div class="metabox-holder">
    <div class="postbox">
      <h3><?php echo _e( 'Time gained with this plugin', ECPM_DDC );?></h3>
      <div class="inside">
        <div class="charts-widget">
          <?php ecpm_show_speed();?>
        </div>
      </div>  
    </div>
  </div>    
  
<?php
  
  $time_gained = get_time_gained();
?>
<hr>  
  <h3><?php echo sprintf( __( 'Average time gained for every visitor (in seconds): %s', ECPM_DDC ), '<font size=+2>'.round($time_gained[0], 4).'</font>' );?></h3>
  <h3><?php echo sprintf( __( 'Average time gained in one day (%s hits): %s', ECPM_DDC ), get_option('ecpm_ddc_avg_hits'), '<font size=+2>'.$time_gained[1].'</font>' );?></h3>
  <hr>
<?php
  show_move_form();


function ecpm_show_speed() {
	global $wpdb;
  $time_from = appthemes_mysql_date( current_time( 'mysql' ), -90 );
  
	$sql = "SELECT time_gained as total, day as time FROM ".$wpdb->prefix."ecpm_ddc_speed WHERE day > %s GROUP BY DATE(day) DESC";
	$results = $wpdb->get_results( $wpdb->prepare( $sql, $time_from ) );
	$speed = prepare_array_time($results);
//echo print_r($speed);  

?>

<div id="placeholder"></div>

<script language="javascript" type="text/javascript">
// <![CDATA[
jQuery(function() {

	var speed = [
		<?php
		foreach ( $speed as $day => $value ) {
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
			data: speed,
			label: "<?php _e( 'Time gained', ECPM_DDC ); ?>",
			symbol: ''
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
				showChartTooltip(item.pageX, item.pageY, xmonth + "/" + xday + " - <b>" + item.series.symbol + y + " minutes</b>");
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
