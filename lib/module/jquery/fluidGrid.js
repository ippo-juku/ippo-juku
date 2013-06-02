
/** 
 * Fluid Grid
 * 
 * @author ykiwng
 * 
 * @param {Object} settigns
 * 		@key {Number} columns - maximum of column number
 * 		@key {Number} gutter - space between columns
 * 		@key {Number} minWidth - minimum of a column width
 */
(function( $, window ){
	
	$.fn.fluidGrid = function( settings ){
		var defaults = {
			columns: 100,
			gutter: 10,
			minWidth: 100
		};
		settings = $.extend( defaults, settings );
		
		var $boxes = $( this ),
			$parent = $boxes.parent(),
			count = $boxes.length,
			clear = '<' + $boxes.eq( 0 )[0].tagName + ' class="grid-clear" style="display: block; clear: both; padding: 0;" />';
		
		var now;
		
		var layout = function(){
			var col = settings.columns,
				gutter = settings.gutter,
				maxWidth = 0 | $parent.width( '100%' ).width(),
				width;
			
			// bug fix for Firefox and Chrome
			// the width of parent element need to be an integer
			$parent.width( maxWidth );
			
			col = Math.max( 1, Math.min( col, 0 | ( maxWidth + gutter ) / ( settings.minWidth + gutter ) ) );
			width = ( maxWidth + gutter ) / col - gutter;
			
			if( now == col ){
				$boxes.width( width );
				return;
			}
			
			// update & reset
			now = col;
			$parent.find( '.grid-clear' ).remove();
			
			var row;
			
			$boxes.each(function( i ){
				++i;
				
				var $this = $( this ).width( width ),
					isLast = ( i % col == 0 || i == count );
				
				$this.css({
					'marginRight': ( isLast ? 0 : gutter ), 
					'marginBottom': gutter 
				});
				
				isLast && $( clear ).insertAfter( $this );
			});
		};
		
		layout();
		$( window ).resize( layout );
		
		return $boxes;
	};
	
})( jQuery, window );