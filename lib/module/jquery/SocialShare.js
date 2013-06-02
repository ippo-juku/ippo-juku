/** 
 * Social counter
 * 
 * @author ykiwng
 */

!function( window, document, $ ){
	var 
	jsonUrl = {
		twitter: 'http://urls.api.twitter.com/1/urls/count.json?url={url}&callback=?',
		facebook: 'https://api.facebook.com/method/fql.query?query=select total_count, share_count from link_stat where url="{url}"&format=json',
		feed: 'http://feedburner.google.com/api/awareness/1.0/GetFeedData?uri={url}',
		gplus: '/lib/api/socialcounter/gplus.php?id={url}',
		hatena: '/lib/api/socialcounter/hatena.php?id={url}'
	},
	popup = {
		twitter: function( info ){
			var open = 'https://twitter.com/intent/tweet?text={text}&url={url}&via={twitter}';
			
			open = bind( open, 'url', info.url );
			open = bind( open, 'text', info.title );
			open = bind( open, 'twitter', info.twitter );
			
			window.open(
				open, 
				'', 
				'toolbar=0, status=0, width=650, height=360'
			);
		},
		facebook: function( info ){
			var open = 'http://www.facebook.com/sharer.php?u={url}&t={text}';
			
			open = bind( open, 'url', info.url );
			open = bind( open, 'text', info.title + ' | ' + info.name );
			
			window.open( 
				open, 
				'', 
				'toolbar=0, status=0, width=900, height=500' 
			);
		},
		gplus: function( info ){
			var open = 'https://plusone.google.com/_/+1/confirm?hl={lang}&url={url}';
			
			open = bind( open, 'url', info.url );
			open = bind( open, 'lang', info.lang || 'ja' );
			
			window.open( 
				open, 
				'', 
				'toolbar=0, status=0, width=900, height=500' 
			);
		},
		hatena: function( info ){
			var open = 'http://b.hatena.ne.jp/entry/{url}';
			
			open = bind( open, 'url', info.url, true );
			
			window.open(
				open,
				'',
				''
			);
		},
		evernote: function( info ){
			window.Evernote && Evernote.doClip({
				providerName: info.name,
				url: info.url,
				title: info.title,
				contentId: 'main-content'
			});
		}
	},
	bind = function( tpl, key, val, raw ){
		return tpl.split( '{' + key + '}' ).join( raw ? val: encodeURIComponent( val ) );
	},
	bu = function( tpl, url ){
		return bind( tpl, 'url', url );
	},
	simulateClick = function( $counter ){
		$counter.text( parseInt( $counter.text() ) + 1 );
	};
	
	$.fn.SocialShare = function( sns, info ){
		var 
		$this = $( this ),
		$counter = $this.find( 'span.counter' ),
		$link = $this.find( 'a.share-link' );
		
		$link && $link.off( 'click' ).click(function( e ){
			e.preventDefault();
			popup[sns] && simulateClick( $counter ), popup[sns]( info );
		});
		
		switch( sns ){
			case 'twitter':
				$.getJSON( bu( jsonUrl[ sns ], info.url ), function( obj ){
					$counter.text( obj.count || '0' );
				});
				break;
			case 'facebook':
				$.getJSON( bu( jsonUrl[ sns ], info.url ), function( obj ){
					$counter.text( obj[0] ? obj[0].share_count: '0' );
				});
				break;
			case 'gplus':
				$.ajax({
					url: bu( jsonUrl[ sns ], info.url ),
					success: function( txt ){
						$counter.text( txt || '0' );
					}
				});
				break;
			case 'hatena':
				$.ajax({
					url: bu( jsonUrl[ sns ], info.url ),
					success: function( txt ){
						$counter.text( txt || '0' );
					}
				});
				break;
			case 'evernote':
				(function(){
					var s = document.getElementsByTagName( 'script' )[0],
						tag = document.createElement( 'script' );
					tag.type = 'text/javascript';
					tag.async = true;
					tag.src = 'http://static.evernote.com/noteit.js';
					s.parentNode.insertBefore( tag, s );
				})();
				break;
			case 'feed':
				$.ajax({
					url: bu( jsonUrl[ sns ], info.feed || info.url ),
					dataType: 'xml',
					success: function( xml ){
						$counter.text( '0' );
						$( xml ).find( 'entry' ).each(function(){
							$counter.text( $( this ).attr( 'circulation' ) || '0' );
						});
					}
				});
				break;
		}
		
		return $this;
	};
	    
}( window, document, jQuery );
