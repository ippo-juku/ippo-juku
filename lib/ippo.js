
/*=== Require Settings
==============================================================================================*/
require.setPath( '/lib/', {
	'm': 'module/'
});

require.define({
	fancybox: [
		'~m/Fancybox/jquery.fancybox.css',
		'~m/Fancybox/helpers/jquery.fancybox-buttons.css',
		'~m/Fancybox/helpers/jquery.fancybox-thumbs.css',
		'~m/Fancybox/jquery.fancybox.pack.js',
		'~m/Fancybox/helpers/jquery.fancybox-buttons.js',
		'~m/Fancybox/helpers/jquery.fancybox-thumbs.js',
		'~m/Fancybox/fancybox.js'
	],
	form: [
		'~m/Form/form.css',
		'~m/Form/jquery.form.js'
	],
	carousel: [
		'~m/JCarousel/jquery.jcarousel.min.js'
	],
	tab: [
		'~m/Tabnav/tabnav.css',
		'~m/Tabnav/tabnav.js'
	]
});


/*=== Ready
==============================================================================================*/
require.ready(function(){
	
	$( 'a[href^=#]' ).click(function( e ){
		e.preventDefault();
		
		var $this = $( this ),
			id = $this.attr( 'href' ),
			$target = $( id );
		
		if( $target.length == 0 ) 
			return;
		
		$( 'html, body' ).animate({
			scrollTop: $target.offset().top
		},{
			duration: 600,
			easing: 'easeInCubic',
			complete: function(){
				window.location.hash = id;
			}
		});
	});
		
});


/*=== For Old Bitches
==============================================================================================*/
if( Device.ie && Device.version <= 8 ){
	// http://html5shiv.googlecode.com/svn/trunk/html5.js
	(function( document ){
		var tags = 'abbr article aside audio bb canvas datagrid datalist details dialog eventsource figure footer header hgroup mark menu meter nav output progress section time video';
		
		$.each( tags.split( ' ' ), function( i, tag ){
			document.createElement( tag );
		});
	})( document );
	
	//require( '~m/fix/selectivizr.js' );
}
