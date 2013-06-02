<?php
	header( 'Content-type: text/javascript' );
	
	$ques = array(
		'module/jquery-1.7.2.js',
		'module/jquery/require.min.js',
		'module/Device.min.js',
		'module/jquery/easing.js',
		//'module/fix/modernizr.js'
	);
	
	foreach( $ques as $q ){
		readfile( $q );
		echo "\n\n";
	}
?>