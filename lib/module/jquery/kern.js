/**
 * Kern
 * kern letters defined by kerning pairs
 * 
 * @author ykiwng
 * @ver 1.0
 */
(function( $ ){
	var library = {};
	
	var kern = function( $elem, pairs ){
		// clean up
		$elem.find( 'span[data-kern]' ).replaceWith(function(){
			return $( this ).text();
		});
		
		var text = $elem.html();
		
		var offset = 0, pos = {}, delta =0;
		
		text = '\0' + text.replace( /\s+/g, ' ' );
		text = text.replace( /\<\/?[^\>]+\>/g, function( tag ){
			offset = text.indexOf( tag, offset ) + delta++;
			pos[ offset ] = tag;
			return tag + '\0';
		});
		
		var buffer = '';
		
		for( var i = 1, len = text.length; i < len; i++ ){
			var prev = text.charAt( i - 1 ),
				char = text.charAt( i ),
				kerning = 0;
			
			if( !!pos[ i ] ){
				char = pos[ i ];
				i += pos[ i ].length;
			}else{
				kerning = ( prev == '\0' ) ? pairs[ char ] : ( pairs[ prev + char ] || pairs[ '*' + char ] || pairs[ prev + '*' ] );
				
				if( kerning && kerning != 0 )
					char = '<span data-kern style="margin-left:' + kerning + 'em">' + char + '</span>';
			}
			
			buffer += char;
		}
		
		buffer = buffer.replace( /\0/g, '' );
		
		$elem.html( buffer );
	};
	
	$.kern = {
		define: function( name, pairs ){
			library[ name ] = pairs;
		}
	};
	
	$.fn.kern = function( pairs ){
		var $this = $( this );
		
		if( typeof pairs == 'string' )
			pairs = library[ pairs ];
		
		if( !$.isPlainObject( pairs ) )
			return $this;
		
		$this.each(function(){
			kern( $( this ), pairs );
		});
		
		return $this;
	};
	
})( jQuery );
