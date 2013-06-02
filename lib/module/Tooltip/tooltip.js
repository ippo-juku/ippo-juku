/**
 * Tootip
 * 
 * @author ykiwng
 * 
 * @markup: 
 * <a href="/path" class="dyn-tooltip" title="description">text</a>
 * <a href="/path" class="dyn-tooltip" title="title :: description">text</a>
 * 
 */

!function(){
	var tipBox, tipTitle, tipDesc;
	
	function init(){
		if( $( '#tooltip' ).length == 0 )
			$( 'body' ).append( '<div id="tooltip"><span id="tip-title"/><span id="tip-desc"/></div>');
		
		tipBox = $( '#tooltip' );
		tipTitle = $( '#tip-title' );
		tipDesc = $( '#tip-desc' );
		
		tipTitle.hide();
	}
	
	function tooltip( $target ){
		var title = $target.attr( 'title' );
		
		if( !title ) return;
		
		title = title.replace( /\/\//g, '<br />' );
		
		if( title.indexOf( ' :: ' ) > 0 ){
			title = title.split( ' :: ' );
			$target.data( 'tip-title', title[0] )
			$target.data( 'tip-desc', title[1] );
		}else{
			$target.data( 'tip-desc', title );
		}
		
		$target.removeAttr( 'title' );
		
		$target.hover(
			function(){
				var $this = $( this ),
					title = $this.data( 'tip-title' );
				
				if( title ){
					tipTitle.text( title );
					tipTitle.show();
				}
				tipDesc.html( $this.data( 'tip-desc' ) );
				
				tipBox.animate( { opacity : 'show' }, 300 );
			},
			function(){
				tipBox.hide();
				tipTitle.hide().text( '' );
				tipDesc.text( '' );
			}
		).mousemove(function( e ){
			var offset = 14;
			tipBox.offset( { top : e.pageY + offset, left : e.pageX + offset } );
		});
	}
	
	$( document ).ready(function(){
		init();
		
		$( '.dyn-tooltip' ).each(function(){
			tooltip( $( this ) );
		});
	});
}();
