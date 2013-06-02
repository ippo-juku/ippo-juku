
(function(){
	var $TK = 'address|article|aside|blockquote|caption|col|colgroup|details|div|dl|embed|fieldset|figcaption|figure|footer|form|header|hgroup|input|legend|map|math|menu|nav|object|ol|object|param|pre|section|select|style|summary|table|tbody|tfoot|thead|tr|ul|wp_protect|wp_noautop';
	var $TD = 'a|audio|canvas|dd|del|dt|ins|label|li|map|noscript|script|style|td|th|video';
	var $TA = $TK + '|area|br|button|dd|dt|h[1-6]|hr|label|li|option|p|td|textarea|th|wp_br|wp_p';
	
	var $wp_protect = [];
	var $wp_protect_counter = 0;
	
	function enhanced_autop_format( $pee ){
		if( trim( $pee ) === '' ) return '';
		
		// cross-platform newlines
		$pee = str_replace( "\r\n", "\n", $pee ); 
		$pee = str_replace( "\r" , "\n", $pee );
		
		$pee = preg_replace( [ '<((pre|code)[^>]*)>([\\s\\S]*?)</\\2>' ], function( _0, _1, _2, _3 ){
			_3 = esc_html( _3 );
			return '<' + _1 + '>' + _3 + '</' + _2 + '>';
		}, $pee );
		
		$pee = protect( $pee, '<(pre|wp_noindent|wp_noautop)[^>]*>[\\s\\S]*?</\\1>', 0 );
		$pee = protect( $pee, '<(script|style)[^>]*>[\\s\\S]*?</\\1>', 1 );
		$pee = protect( $pee, '<\\?php[\\s\\S]*?\\?>', 1 );
		$pee = protect( $pee, '<!--[\\s\\S]*?-->', 2 );
		
		$pee = preg_replace( [ '\\s*<(/?(?:' + $TK + ')[^>]*)>\\s*' ], "\n\n<$1>\n\n", $pee );
		$pee = preg_replace( [ '(</(?:' + $TA + ')>)' ], "$1\n\n", $pee );
		$pee = preg_replace( [ '(<p[^>]*>[\\s\\S]*?</p>)' ], "\n\n$1\n\n", $pee );
		$pee = preg_replace( [ '<br />\\s*<br />' ], "\n\n", $pee );
		
		$pee = preg_replace( [ '\\n\\s*\\n' ], '<wp_dnl />', $pee );
		$pee = str_replace( '\n', '<wp_snl />', $pee );
		$pee = str_replace( '<wp_dnl />', "\n\n", $pee );
		
		$pee = preg_replace( [ '<((' + $TD + ')[^>]*)>([^\\n]*?)</\\2>' ], function( _0, _1, _2, _3 ){
			if( preg_match( [ '^\\s*<wp_snl />' ], _3 ) && preg_match( [ '<wp_snl />\\s*$' ], _3 ) )
				return _0;
			
			_3 = preg_replace( [ '<(/?)((' + $TD + ')[^>]*)>' ], '<$1wp_ni_$2>', _3 );
			_3 = preg_replace( [ '<(' + $TD + ')[\s>]' ], '<wp_ni_$1', _3 ); // a bug???
			return "<wp_ni_" + _1 + ">" + _3 + "</wp_ni_" + _2 + ">";
		}, $pee );
		
		$pee = preg_replace( [ '<(/?(' + $TD + ')[^>]*)>' ], "\n\n<$1>\n\n", $pee );
		$pee = preg_replace( [ '<(/?)wp_ni_([^>]+)>' ], '<$1$2>', $pee );
		$pee = str_replace( '<wp_snl />', "\n", $pee );
		$pee = preg_replace( [ "\\n\\n+" ], "\n\n", $pee ); // take care of duplicates
		
		$pee = preg_replace( [ '(\\s*<wp_protect id="wp-protect-(0|1)-\\d+"[^>]*>\\s*</wp_protect>)' ], "\n\n$1\n\n", $pee );
		
		var $pees = $pee.split( new RegExp( '\\n\\s*\\n', 'g' ) ); // make paragraphs, including one at the end
		$pee = '';
		
		var $i = 0, $tinkle;
		
		while( ( $tinkle = $pees[ $i++ ] ) != null ){
			$pee += '<wp_p>' + trim( $tinkle, "\\n" ) + "</wp_p>\n";
		}
		
		$pee = preg_replace( [ '<wp_p>\\s*</wp_p>\n*' ], '', $pee );
		$pee = preg_replace( [ '<wp_p>(\\s+)' ], '$1<wp_p>', $pee );
		$pee = preg_replace( [ '<wp_p>(<li.+?)</wp_p>' ], '$1', $pee ); // problem with nested lists
		$pee = preg_replace( [ '<wp_p>(</?(?:' + $TA + ')[^>]*>)' ], '$1', $pee );
		$pee = preg_replace( [ '(</?(?:' + $TA + ')[^>]*>)\\s*</wp_p>' ], '$1', $pee );
		$pee = preg_replace( [ '\\n(\\s*)(\\S)' ], "\n$1<wp_br />$2", $pee );
		$pee = preg_replace( [ '<br />\\n(\\s*)<wp_br />(\\S)' ], "\n$1$2", $pee );
		$pee = preg_replace( [ '<wp_br />(\\s*</?(?:' + $TA + ')[^>]*>)' ], '$1', $pee );
		
		$pee = preg_replace( [ '<wp_p>(</?(?:' + $TD + ')[^>]*>)</wp_p>' ], '$1', $pee );
		$pee = preg_replace( [ '(<(?:' + $TD + ')[^>]*>)</wp_p>' ], '$1', $pee );
		$pee = preg_replace( [ '<wp_p>(</?(?:' + $TD + ')[^>]*>)$', 'm' ], '$1', $pee );
		$pee = preg_replace( [ '<wp_br />(\\s*</(?:' + $TD + ')[^>]*>)(</wp_p>)?$', 'm' ], '$1', $pee );
		$pee = preg_replace( [ '(<(\\w+)[^>]*>\\s*)<wp_p>(<(?:img)[^>]*>)</wp_p>(\\s*</\\2>)' ], '$1$3$4', $pee );
		
		$pee = preg_replace( [ '<(/?)wp_(p|br)( /)?>' ], '<$1$2$3>', $pee );
		
		$pee = preg_replace( [ '<((\\w+)[^>]*)>\\s*</\\2>' ], '<$1></$2>', $pee );
		$pee = preg_replace( [ '^\\s+', 'm' ], '', $pee );
		
		$pee = protect_done( $pee, 0 );
		
		return $pee;
	
	}
	
	function enhanced_autop_clean( $pee ){
		if( trim( $pee ) === '' ) return '';
		
		$pee = str_replace( "\r\n", "\n", $pee ); // cross-platform newlines
		$pee = str_replace( "\r" , "\n", $pee ); // cross-platform newlines
		
		$pee = preg_replace( [ '<((pre|code)[^>]*)>([\\s\\S]*?)</\\2>' ], function( _0, _1, _2, _3 ){
			var trimmed = trim( _3, '\n' );
			
			if( trimmed.indexOf( '\n' ) >= 0 )
				_3 = preg_replace( [ '^\\n*' ], '\n', _3 );
			else
				_3 = trimmed;
			
			_3 = preg_replace( [ '<br\\s?/?>' ], '\n', _3 );
			_3 = unesc_html( _3 );
			
			return '<' + _1 + '>' + _3 + '</' + _2 + '>';
		}, $pee );
		
		$pee = protect( $pee, '<(pre|wp_noindent)[^>]*>[\\s\\S]*?</\\1>', 0 );
		
		$pee = preg_replace( [ '\\s*<(/?(?:' + $TK + ')[^>]*)>\\s*' ], "\n\n<$1>\n\n", $pee );
		$pee = preg_replace( [ '(</(?:' + $TA + ')>)' ], "$1\n\n", $pee );
		$pee = preg_replace( [ '(<p[^>]*>[\\s\\S]*?</p>)' ], "\n\n$1\n\n", $pee );
		$pee = preg_replace( [ '<br />\\s*<br />' ], "\n\n", $pee );
		
		$pee = preg_replace( [ '\\n\\s*\\n' ], '<wp_dnl />', $pee );
		$pee = str_replace( '\n', '<wp_snl />', $pee );
		$pee = str_replace( '<wp_dnl />', "\n\n", $pee );
		
		$pee = preg_replace( [ '<((' + $TD + ')[^>]*)>([^\\n]*?)</\\2>' ], function( _0, _1, _2, _3 ){
			if( preg_match( [ '^\\s*<wp_snl />' ], _3 ) && preg_match( [ '<wp_snl />\\s*$' ], _3 ) )
				return _0;
			
			_3 = preg_replace( [ '<(/?)((' + $TD + ')[^>]*)>' ], '<$1wp_ni_$2>', _3 );
			_3 = preg_replace( [ '<(' + $TD + ')[\s>]' ], '<wp_ni_$1', _3 ); // a bug???
			return "<wp_ni_" + _1 + ">" + _3 + "</wp_ni_" + _2 + ">";
		}, $pee );
		
		$pee = preg_replace( [ '<(/?(' + $TD + ')[^>]*)>' ], "\n\n<$1>\n\n", $pee );
		$pee = preg_replace( [ '<(/?)wp_ni_([^>]+)>' ], '<$1$2>', $pee );
		$pee = str_replace( '<wp_snl />', "\n", $pee );
		$pee = preg_replace( [ "\\n\\n+" ], "\n\n", $pee ); // take care of duplicates
		
		var $indent = -2;
		var $lines = $pee.split( '\n' );
		var $pee = '';
		var $i = 0, $tinkle;
		
		while( ( $tinkle = $lines[ $i++ ] ) != null ){
			$tinkle = trim( $tinkle );
			
			$m = preg_match( [ '^<(/?)(\\w+)[^>]*?(/?)>$' ], $tinkle );
			$block = !!$m;
			
			if( $block ){
				if( in_array( $m[2], [ 'br', 'hr', 'img', 'input', 'wp_noautop' ] ) ) // do not change indention level
					$m[3] = true;
				else if( $m[1] && !$m[3] )
					$indent--;
			}
			
			if( $indent > 0 )
				$pee += str_repeat( "\t", $indent );
				
			$pee += $tinkle + "\n";
			
			if( $block && !$m[1] && !$m[3] )
				$indent++;
				
		}
		
		$pee = preg_replace( [ '(\\s*<wp_protect id="wp-protect-(0|1)-\\d+"[^>]*>\\s*</wp_protect>)' ], "\n\n$1\n\n", $pee );
		
		// do not remove <p> with attributes
		$pee = preg_replace( [ '<(p [^>]+)>([\\s\\S]*?)</p>' ], "<$1>$2</wp_p>", $pee );
		
		$pee = preg_replace( [ '(\\s*)</?p>' ], "\n$1", $pee );
		$pee = preg_replace( [ '(\\s*)<br[ /]*>(\\s*\\S)' ], "$1\n$2", $pee );
		$pee = preg_replace( [ "\\n\\s*\\n" ], "\n\n", $pee );
		
		$pee = str_replace( '</wp_p>', '</p>', $pee );
		
		$pee = protect_done( $pee, 1 );
		$pee = preg_replace( [ '(\\t+)(<(script|style)[^>]*>[\\s\\S]+?</\\3>)' ], function( _0, _1, _2 ){
			_2 = preg_replace( [ '^', 'm' ], _1, _2 );
			return _2;
		}, $pee );
		
		$pee = protect_done( $pee, 0 );
		$pee = protect_done( $pee, 2 );
		
		$pee = preg_replace( [ '<((\\w+)[^>]*)>\\s*</\\2>' ], '<$1></$2>', $pee );
		
		return $pee;
	
	}
	
	function protect( $content, $pattern, $group ){
		if( !$wp_protect[ $group ] )
			$wp_protect[ $group ] = [];
		
		$content = preg_replace( [ $pattern , 'i' ], function( _0 ){
			$id = $wp_protect_counter++;
			$wp_protect[ $group ][ $id ] = _0;
			return '<wp_protect id="wp-protect-' + $group + '-' + $id + '" contenteditable="false"></wp_protect>';
		}, $content );
		
		return $content;
	}
	function protect_done( $content, $group ){
		$content = preg_replace(
			[ '<wp_protect id="wp-protect-' + $group + '-(\\d+)"[^>]*>\\s*</wp_protect>' ],
			function( _0, _1 ){
				return $wp_protect[ $group ][ parseInt( _1 ) ] || _0;
			}, 
			$content
		);
		
		//$wp_protect[ $group ] = [];
		return $content;
	}
	
	/*	PHP Like Function
	-----------------------------------------------*/
	function trim( a, b ){
		b = b || '\\s';
		a = preg_replace( [ '^' + b + '*(.+?)' + b + '*$' ], '$1', a );
		return a;
	}
	function str_replace( a, b, str ){
		return str.split( a ).join( b );
	}
	function preg_replace( a, b, c ){
		return c.replace( new RegExp( a[0], ( a[1] || '' ) + 'g' ), b );
	}
	function preg_match( a, b ){
		return new RegExp( a[0], ( a[1] || '' ) + 'g' ).exec( b );
	}
	function in_array( a, b ){
		return b.indexOf( a ) >= 0;
	}
	function str_repeat( a, b ){
		return new Array( b + 1 ).join( a );
	}
	
	/*	Simple HTML Escape ( escapes only '<', '>', '&' )
	-----------------------------------------------*/
	function esc_html( html ){
		html = str_replace( '<', '&lt;', html );
		html = str_replace( '>', '&gt;', html );
		html = str_replace( [ '&(?![#\\w]+;)' ], '&amp;', html );
		return html;
	}
	function unesc_html( html ){
		html = str_replace( '&lt;', '<', html );
		html = str_replace( '&gt;', '>', html );
		html = str_replace( '&amp;', '&', html );
		return html;
	}
	
	/*	Expose to the global
	-----------------------------------------------*/
	window[ 'enhanced_autop_format' ] = enhanced_autop_format;
	window[ 'enhanced_autop_clean' ] = enhanced_autop_clean;
	
	/*	IE Fix
	-----------------------------------------------*/
	document.createElement( 'wp_protect' );
	document.createElement( 'wp_noautop' );
	document.createElement( 'wp_noindent' );
})();

if( typeof switchEditors == 'undefined' )
	switchEditors = {};

switchEditors._wp_Nop = enhanced_autop_clean;
switchEditors._wp_Autop = enhanced_autop_format;

switchEditors.setupcontent_callback = function( editor_id, body ){
	var c = document.getElementById( editor_id ),
		formatted = switchEditors.wpautop( c.value );
	
	c.value = formatted;
    body.innerHTML = formatted;
};

