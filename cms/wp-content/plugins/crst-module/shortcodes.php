<?php

/*=== Syntax Highlighter
==============================================================================================*/
add_shortcode( 'code', 'sh_code_func' );
function sh_code_func( $attr, $content = '' ){
	$param = array();
	
	$param[] = 'class="syntax"';
	
	foreach( $attr as $key => $val ){
		if( 'lang' == $key ){
			$val = esc_attr( $val );
			$param[] = "data-language=\"$val\"";
			continue;
		}
		
		if( 'highlight' == $key ){
			$val = esc_attr( $val );
			$param[] = "data-highlight=\"$val\"";
			continue;
		}
		
		if( 'start' == $key ){
			$val = esc_attr( $val );
			$param[] = "data-start=\"$val\"";
			continue;
		}
	}
	$content = esc_html( $content );
	
	$html = '<code ' . implode( ' ', $param ) . '>' . $content . '</code>';
	
	if( strpos( $content, "\n" ) )
		$html = '<pre>' . $html . '</pre>';
		
	return $html;
}


/*=== Tabnav
==============================================================================================*/
add_shortcode( 'tabnav', 'sh_tabnav_func' );
function sh_tabnav_func( $atts, $content = '' ){
	$content = do_shortcode( $content );
	
	$atts[ 'class' ] = trim( 'dyn-tabnav tabnav-zen ' . $atts[ 'class' ] );
	
	$attr = array2attr( $atts );
	
	$content = "<div$attr>\n$content\n</div>";
	
	return $content;
}

?>