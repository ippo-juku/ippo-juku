<?php

/*=== Embed Youtube
==============================================================================================*/
add_shortcode( 'youtube', 'sh_youtube_func' );
function sh_youtube_func( $atts ){
	extract( shortcode_atts( array(
		'url' => '',
		'width' => '640',
		'height' => '360'
	), $atts ) );
	
	if( empty( $url ) )
		return '';
	
	$width = preg_replace( '/(\d+)(?!%|px)$/', '$1px', $width );
	$height = preg_replace( '/(\d+)(?!%|px)$/', '$1px', $height );
	
	$html = <<<HTML
		<div class="embed" style="width: {$width}; height: {$height};">
			<div class="embed-inner">
				<iframe src="http://www.youtube.com/embed/{$url}" frameborder="0" allowfullscreen></iframe>
			</div>
		</div>
HTML;
	
	return $html;
}


/*=== QR Code API
==============================================================================================*/
add_shortcode( 'qrcode', 'sh_qrcode_func' );
function sh_qrcode_func( $atts ){
	extract( shortcode_atts( array(
		'url' => 'http://www.creasty.com',
		'size' => '80',
	), $atts ) );
	
	$size = (int) $size;
	$url_u = esc_url( $url );
	$url_a = esc_attr( $url );
	
	return vsprintf( 
		'<img src="https://chart.googleapis.com/chart?chs=%dx%1&cht=qr&chl=%s&choe=UTF-8" alt="%s" />',
		$size,
		$url_u,
		$url_a
	);
}


/*=== Screenshot API
==============================================================================================*/
add_shortcode( 'screenshot', 'sh_screenshot_func' );
function sh_screenshot_func( $atts ){
	extract( shortcode_atts( array(
		'url' => 'http://www.creasty.com',
		'size' => '400',
	), $atts ) );
	
	$size = (int) $size;
	
	return vsprintf( 
		'<img src="http://s.wordpress.com/mshots/v1/%s?w=%d" alt="%s" />',
		urlencode( esc_url( $url ) ),
		$size,
		esc_attr( $url )
	);
}


/*=== Layout Columns
==============================================================================================*/
add_shortcode( 'col', 'sh_col_func' );
function sh_col_func( $atts, $content = '' ){
	$defaults = array(
		0 => 'a',
		1 => 'a',
		'class' => ''
	);
	
	$atts += $defaults;
	
	$len0 = strlen( $atts[0] );
	$len1 = strlen( $atts[1] );
	
	if( $len0 <= 1 || $len0 < $len1 )
		return $content;
	
	$content = do_shortcode( $content );
	
	$atts['class'] = trim( "col-{$atts[0]}-{$atts[1]} " . $atts['class'] );
	
	$attr = array2attr( $atts );
	
	$content = "<div$attr>\n$content\n</div>";
	
	if( substr( $atts[0], -$len1 ) == $atts[1] )
		$content .= "\n<br class=\"clear\" />\n";
	
	return $content;
}


?>
