/** 
 * Here Link
 * add class to the current page links
 * 
 * @author ykiwng
 */

!function( window, $ ){
	var loc = window.location.href;
	
	$.fn.here = function( clickable ){
		var $this = $( this );
		
		$this.each(function(){
			var $this = $( this ),
				href = this.href;
			
			if( 
				href === loc || 
				$this.hasClass( 'here-parent' ) && loc.replace( /[\?#]/, '/' ).indexOf( href.replace( /\/?$/, '/' ) ) == 0
			){
				$this.addClass( 'link-here' ).parent().addClass( 'here-contains' );
				clickable || $this.css( { cursor : "default" } ).click(function( e ){ e.preventDefault(); });
			}	
		});
		
		return $this;
	};
	
}( window, jQuery );
