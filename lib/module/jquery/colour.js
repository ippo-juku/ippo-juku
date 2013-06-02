/** 
 * Smart Color Plugin
 * make colours brighter or darker
 * 
 * @see http://std.li/L7 (Craig Buckler)
 */
(function( $ ){
	$.colour = function( hex, lum, alpha, brightness ){
		// validate hex string
		hex = String( hex ).replace( /[^0-9a-f]/gi, '' );
		
		if( hex.length < 6 )
			hex = hex[0] + hex[0] + hex[1] + hex[1] + hex[2] + hex[2];
		
		lum = lum || 0;
		
		// convert to decimal and change luminosity
		var rgb = '#', c, i;
		for( i = 0; i < 3; i++ ){
			c = parseInt( hex.substr( i * 2, 2 ), 16 );
			c = Math.round( Math.min( Math.max( 0, c + c * lum ), 255) ).toString(16);
			rgb += ( '00' + c ).substr( c.length );
		}
		
		if( brightness == undefined ){
			if( alpha != true ){
				return rgb;
			}else{
				var result = /^#?([a-f\d]{2})([a-f\d]{2})([a-f\d]{2})$/i.exec( rgb );
				return parseInt( result[1], 16 ) + ',' + parseInt( result[2], 16 ) + ',' + parseInt( result[3], 16 );
			}
		}else{
			var r = parseInt( hex.substring( 0, 2 ), 16 ),
				g = parseInt( hex.substring( 2, 4 ), 16 ),
				b = parseInt( hex.substring( 4, 6 ), 16 );
			
			return r * 2 + g * 3 + b;
		}
	};
})( jQuery );
