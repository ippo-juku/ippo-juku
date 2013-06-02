/*!
 * Tap Event
 * 
 * @author ykiwng
 */

(function( document, $ ){
	$.event.special.tap = {
		setup: function( a, b ){
			var c = this,
				d = $(c);
			
			if( window.Touch ){
				d.bind( 'touchstart', jQuery.event.special.tap.onTouchStart );
				d.bind( 'touchmove', jQuery.event.special.tap.onTouchMove );
				d.bind( 'touchend', jQuery.event.special.tap.onTouchEnd );
			}else{
				d.bind( 'click', jQuery.event.special.tap.click );
			}
		},
		click: function( a ){
			a.type = 'tap';
			jQuery.event.handle.apply( this, arguments );
		},
		teardown: function( a ){
			if( window.Touch ){
				$elem.unbind( 'touchstart', jQuery.event.special.tap.onTouchStart );
				$elem.unbind( 'touchmove', jQuery.event.special.tap.onTouchMove );
				$elem.unbind( 'touchend', jQuery.event.special.tap.onTouchEnd );
			}else{
				$elem.unbind( 'click', jQuery.event.special.tap.click );
			}
		},
		onTouchStart: function( a ){
			this.moved = false;
		},
		onTouchMove: function( a ){
			this.moved = true;
		},
		onTouchEnd: function( a ){
			if( !this.moved ){
				a.type = 'tap';
				jQuery.event.handle.apply( this, arguments );
			}
		}
	};
	
	// simulate hover
	$( document ).ready(function(){
		$( 'a, input[type="button"], input[type="submit"], button' )
		.bind( 'touchstart, touchend', $.noop );
	});
})( document, jQuery );
