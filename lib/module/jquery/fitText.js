/**
* FitText.js
* 
* @ver 1.0
* @copyright Dave Rupert http://daverupert.com
* 
* @modified ykiwng
* 	-	supported dynamic changes of the window size,
*		now works with the orientation on mobile devices
*/
(function( $ ){
	$.fn.fitText = function( compressor, options ){
		var settings = {
			'minFontSize' : Number.NEGATIVE_INFINITY,
			'maxFontSize' : Number.POSITIVE_INFINITY
		};
		
		return this.each(function(){
			var $this = $( this );
			
			compressor = ( compressor || 1 ) * 10;
			
			options && $.extend( settings, options );
			
			var resizer = function(){
				$this.css( 'font-size', Math.max( Math.min( $this.width() / compressor, parseFloat( settings.maxFontSize ) ), parseFloat( settings.minFontSize ) ) );
			};
			
			resizer();
			$( window ).resize( resizer );
		});
	};
})( jQuery );
