
require.ready(function(){
	var normal = {
		openEffect : 'elastic',
		openEasing : 'easeOutCubic',
		openSpeed  : 400,
		
		closeEffect : 'elastic',
		closeEasing : 'easeOutCubic',
		closeSpeed  : 200,

		closeBtn  : true,
		closeClick : false,
		
		helpers : {
			title : {
				type : 'inside'
			},
			overlay : {
				speedIn : 600,
				opacity : 0.6,
				css: {
					cursor: 'default'
				}
			}
		}
	};
	
	var thumb = $.extend( {}, normal );
	$.extend( thumb, {
		openEffect  : 'fade',
		closeEffect : 'fade',
		
		prevEffect : 'none',
		nextEffect : 'none',

		arrows    : false,
		nextClick : true,
		helpers : {
			thumbs : {
				width  : 50,
				height : 50
			},
			title : {
				type : 'inside'
			},
			overlay : {
				speedIn : 600,
				opacity : 0.6,
				css: {
					cursor: 'default'
				}
			}
		}
	});
	
	var button = $.extend( {}, normal );
	$.extend( button, {
		openEffect  : 'fade',
		closeEffect : 'fade',

		prevEffect : 'none',
		nextEffect : 'none',

		closeBtn  : false,
		arrows    : false,
		nextClick : true,
		
		afterLoad : function(){
			this.title = ( this.title ? this.title + ' - ' : '') + ( this.index + 1 ) + '/' + this.group.length;
		},
		helpers : {
			buttons	: {},
			title : {
				type : 'inside'
			},
			overlay : {
				speedIn : 600,
				opacity : 0.6,
				css: {
					cursor: 'default'
				}
			}
		}
	});

	$('.fancybox').fancybox( normal );
	$('.fancybox-thumb').fancybox( thumb );
	$('.fancybox-button').fancybox( button );
	
});

